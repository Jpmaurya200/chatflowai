<?php

/**
 * InstagramServiceEngine.php - Main service file
 *
 * This file is part of the Instagram Service component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\InstagramService;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\InstagramService\Services\InstagramApiService;
use App\Yantrana\Components\InstagramService\Repositories\InstagramMessageLogRepository;
use App\Yantrana\Components\Contact\Repositories\ContactRepository;
use App\Yantrana\Components\Media\MediaEngine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InstagramServiceEngine extends BaseEngine
{
    /**
     * @var InstagramApiService
     */
    protected $instagramApiService;

    /**
     * @var InstagramMessageLogRepository
     */
    protected $instagramMessageLogRepository;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * @var MediaEngine
     */
    protected $mediaEngine;

    /**
     * Constructor
     */
    public function __construct(
        InstagramApiService $instagramApiService,
        InstagramMessageLogRepository $instagramMessageLogRepository,
        ContactRepository $contactRepository,
        MediaEngine $mediaEngine
    ) {
        $this->instagramApiService = $instagramApiService;
        $this->instagramMessageLogRepository = $instagramMessageLogRepository;
        $this->contactRepository = $contactRepository;
        $this->mediaEngine = $mediaEngine;
    }

    /**
     * Process incoming Instagram webhook
     *
     * @param array $webhookData
     * @return array
     */
    public function processIncomingWebhook($webhookData)
    {
        try {
            if (!isset($webhookData['entry'])) {
                return $this->engineFailedResponse([], __tr('Invalid webhook data'));
            }

            foreach ($webhookData['entry'] as $entry) {
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messagingEvent) {
                        $this->processMessagingEvent($messagingEvent);
                    }
                }
            }

            return $this->engineSuccessResponse([], __tr('Webhook processed successfully'));
        } catch (\Exception $e) {
            return $this->engineFailedResponse([], $e->getMessage());
        }
    }

    /**
     * Process individual messaging event
     *
     * @param array $messagingEvent
     * @return void
     */
    protected function processMessagingEvent($messagingEvent)
    {
        $senderId = $messagingEvent['sender']['id'] ?? null;
        $recipientId = $messagingEvent['recipient']['id'] ?? null;
        
        if (!$senderId || !$recipientId) {
            return;
        }

        // Find or create contact
        $contact = $this->findOrCreateContact($senderId, $messagingEvent);
        
        if (!$contact) {
            return;
        }

        // Process different message types
        if (isset($messagingEvent['message'])) {
            $this->processIncomingMessage($messagingEvent, $contact);
        } elseif (isset($messagingEvent['delivery'])) {
            $this->processDeliveryReceipt($messagingEvent);
        } elseif (isset($messagingEvent['read'])) {
            $this->processReadReceipt($messagingEvent);
        }
    }

    /**
     * Process incoming message
     *
     * @param array $messagingEvent
     * @param object $contact
     * @return void
     */
    protected function processIncomingMessage($messagingEvent, $contact)
    {
        $message = $messagingEvent['message'];
        $messageId = $message['mid'] ?? $message['id'] ?? null;
        
        if (!$messageId) {
            return;
        }

        // Check if message already exists
        if ($this->instagramMessageLogRepository->getByMessageId($messageId, getVendorId())) {
            return;
        }

        // Extract sender ID from the 'from' object as per user's example
        $senderId = null;
        if (isset($messagingEvent['sender']['id'])) {
            $senderId = $messagingEvent['sender']['id'];
        } elseif (isset($messagingEvent['from']['id'])) {
            $senderId = $messagingEvent['from']['id'];
        }

        $messageData = [
            'vendor_id' => getVendorId(),
            'contact_id' => $contact->_id,
            'page_id' => $messagingEvent['recipient']['id'] ?? getVendorSettings('instagram_account_id'),
            'sender_id' => $senderId,
            'message_id' => $messageId,
            'message' => $message['text'] ?? $message['message'] ?? '',
            'message_type' => $this->determineMessageType($message),
            'timestamp' => $message['timestamp'] ?? time(),
            'webhook_data' => $messagingEvent,
            'media_data' => $this->extractMediaData($message),
            'reply_data' => $this->extractReplyData($message),
            'story_data' => $this->extractStoryData($message),
        ];

        Log::info('Instagram incoming message processed', [
            'message_id' => $messageId,
            'sender_id' => $senderId,
            'contact_id' => $contact->_id,
            'message_text' => $messageData['message']
        ]);

        $this->instagramMessageLogRepository->storeIncomingMessage($messageData);
    }

    /**
     * Send text message to Instagram user
     *
     * @param int $contactId
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendMessage($contactId, $message, $options = [])
    {
        $contact = $this->contactRepository->fetchIt($contactId);
        
        if (!$contact || $contact->platform !== 'instagram') {
            return $this->engineFailedResponse([], __tr('Invalid Instagram contact'));
        }

        if (empty($contact->instagram_id)) {
            return $this->engineFailedResponse([], __tr('Contact Instagram ID not found'));
        }

        // Send message via API
        $apiResponse = $this->instagramApiService->sendMessage(
            $contact->instagram_id,
            $message,
            $options
        );

        if (!$apiResponse['success']) {
            return $this->engineFailedResponse([], $apiResponse['message']);
        }

        // Store outgoing message
        $messageData = [
            'vendor_id' => getVendorId(),
            'contact_id' => $contactId,
            'page_id' => getVendorSettings('instagram_page_id'),
            'recipient_id' => $contact->instagram_id,
            'message_id' => $apiResponse['data']['message_id'] ?? uniqid(),
            'message' => $message,
            'message_type' => 'text',
            'status' => 'sent',
            'api_response' => $apiResponse['data'],
            'options' => $options,
        ];

        $messageLog = $this->instagramMessageLogRepository->storeOutgoingMessage($messageData);

        return $this->engineSuccessResponse([
            'message_log' => $messageLog,
            'api_response' => $apiResponse
        ], __tr('Message sent successfully'));
    }

    /**
     * Send media message
     *
     * @param int $contactId
     * @param string $mediaUrl
     * @param string $mediaType
     * @param array $options
     * @return array
     */
    public function sendMediaMessage($contactId, $mediaUrl, $mediaType, $options = [])
    {
        $contact = $this->contactRepository->fetchIt($contactId);
        
        if (!$contact || $contact->platform !== 'instagram') {
            return $this->engineFailedResponse([], __tr('Invalid Instagram contact'));
        }

        // Send media message via API
        $apiResponse = $this->instagramApiService->sendMediaMessage(
            $contact->instagram_id,
            $mediaUrl,
            $mediaType,
            $options
        );

        if (!$apiResponse['success']) {
            return $this->engineFailedResponse([], $apiResponse['message']);
        }

        // Store outgoing message
        $messageData = [
            'vendor_id' => getVendorId(),
            'contact_id' => $contactId,
            'page_id' => getVendorSettings('instagram_page_id'),
            'recipient_id' => $contact->instagram_id,
            'message_id' => $apiResponse['data']['message_id'] ?? uniqid(),
            'message' => '',
            'message_type' => $mediaType,
            'status' => 'sent',
            'api_response' => $apiResponse['data'],
            'media_data' => [
                'url' => $mediaUrl,
                'type' => $mediaType
            ],
            'options' => $options,
        ];

        $messageLog = $this->instagramMessageLogRepository->storeOutgoingMessage($messageData);

        return $this->engineSuccessResponse([
            'message_log' => $messageLog,
            'api_response' => $apiResponse
        ], __tr('Media message sent successfully'));
    }

    /**
     * Get contact messages for chat interface
     *
     * @param int $contactId
     * @param int $page
     * @return array
     */
    public function getContactMessages($contactId, $page = 1)
    {
        $contact = $this->contactRepository->fetchIt($contactId);
        
        if (!$contact || $contact->platform !== 'instagram') {
            return $this->engineFailedResponse([], __tr('Invalid Instagram contact'));
        }

        $messages = $this->instagramMessageLogRepository->getMessagesForChat($contactId, $page);

        return $this->engineSuccessResponse([
            'messages' => $messages,
            'contact' => $contact
        ]);
    }

    /**
     * Get Instagram contacts with recent messages
     *
     * @param int $limit
     * @return array
     */
    public function getContactsWithMessages($limit = 50)
    {
        $contacts = $this->contactRepository->fetchDataTableSource([
            'platform' => 'instagram',
            'vendor_id' => getVendorId(),
            'with_messages' => true,
            'limit' => $limit
        ]);

        return $this->engineSuccessResponse(['contacts' => $contacts]);
    }

    /**
     * Mark contact messages as read
     *
     * @param int $contactId
     * @return array
     */
    public function markMessagesAsRead($contactId)
    {
        $result = $this->instagramMessageLogRepository->markContactMessagesAsRead($contactId, getVendorId());
        
        if ($result) {
            return $this->engineSuccessResponse([], __tr('Messages marked as read'));
        }
        
        return $this->engineFailedResponse([], __tr('Failed to mark messages as read'));
    }

    /**
     * Get Instagram message statistics
     *
     * @param string $period
     * @return array
     */
    public function getMessageStats($period = 'today')
    {
        $stats = $this->instagramMessageLogRepository->getMessageStats(getVendorId(), $period);
        
        return $this->engineSuccessResponse(['stats' => $stats]);
    }

    /**
     * Test Instagram API connection
     *
     * @return array
     */
    public function testConnection()
    {
        $result = $this->instagramApiService->testConnection();

        if ($result['success']) {
            return $this->engineSuccessResponse($result['data'], __tr('Instagram API connection successful'));
        }

        return $this->engineFailedResponse([], $result['message']);
    }

    /**
     * Get Instagram media posts
     *
     * @return array
     */
    public function getMediaPosts()
    {
        try {
            $result = $this->instagramApiService->getMediaPosts();

            if ($result['success']) {
                return $this->engineSuccessResponse([
                    'media' => $result['data'],
                    'paging' => $result['paging'] ?? null
                ], __tr('Media posts retrieved successfully'));
            }

            return $this->engineFailedResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Instagram media posts error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->engineFailedResponse([], 'Error retrieving media posts: ' . $e->getMessage());
        }
    }

    /**
     * Get comments for a specific media post
     *
     * @param string $mediaId
     * @return array
     */
    public function getMediaComments($mediaId)
    {
        try {
            $result = $this->instagramApiService->getMediaComments($mediaId);

            if ($result['success']) {
                // Process comments data to match frontend format
                $processedComments = $this->processCommentsData($result['data']);

                return $this->engineSuccessResponse([
                    'comments' => $processedComments,
                    'media_id' => $mediaId,
                    'paging' => $result['paging'] ?? null
                ], __tr('Comments retrieved successfully'));
            }

            return $this->engineFailedResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Instagram media comments error', [
                'media_id' => $mediaId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->engineFailedResponse([], 'Error retrieving comments: ' . $e->getMessage());
        }
    }

    /**
     * Send message to comment author
     *
     * @param string $commentId
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendCommentReply($commentId, $message, $options = [])
    {
        try {
            Log::info('Instagram comment reply engine called', [
                'comment_id' => $commentId,
                'message_length' => strlen($message)
            ]);

            // Send message using comment ID as recipient
            $apiResponse = $this->instagramApiService->sendCommentReply(
                $commentId,
                $message,
                $options
            );

            Log::info('Instagram comment reply API response', [
                'success' => $apiResponse['success'],
                'message' => $apiResponse['message'] ?? 'No message',
                'has_data' => isset($apiResponse['data'])
            ]);

            if (!$apiResponse['success']) {
                return $this->engineFailedResponse([], $apiResponse['message'] ?? 'API call failed');
            }

            return $this->engineSuccessResponse([
                'message_id' => $apiResponse['data']['message_id'] ?? uniqid('comment_reply_'),
                'comment_id' => $commentId,
                'api_response' => $apiResponse
            ], 'Reply sent successfully');

        } catch (\Exception $e) {
            Log::error('Instagram comment reply error', [
                'comment_id' => $commentId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->engineFailedResponse([], 'Error sending reply: ' . $e->getMessage());
        }
    }

    /**
     * Get Instagram conversations
     *
     * @return array
     */
    public function getConversations()
    {
        try {
            $result = $this->instagramApiService->getConversations();

            if ($result['success']) {
                // Process conversations data to match the expected format
                $processedConversations = $this->processConversationsData($result['data']);

                Log::info('Instagram conversations processed', [
                    'raw_count' => count($result['data']),
                    'processed_count' => count($processedConversations),
                    'processed_data' => $processedConversations
                ]);

                return $this->engineSuccessResponse([
                    'conversations' => $processedConversations,
                    'paging' => $result['paging'] ?? null
                ], __tr('Conversations retrieved successfully'));
            }

            return $this->engineFailedResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Instagram conversations error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->engineFailedResponse([], 'Error processing conversations: ' . $e->getMessage());
        }
    }

    /**
     * Get messages for a specific conversation
     *
     * @param string $conversationId
     * @return array
     */
    public function getConversationMessages($conversationId)
    {
        $result = $this->instagramApiService->getConversationMessages($conversationId);

        if ($result['success']) {
            // Process messages data to match the expected format
            $processedMessages = $this->processMessagesData($result['data']);

            return $this->engineSuccessResponse([
                'messages' => $processedMessages,
                'paging' => $result['paging'] ?? null
            ], __tr('Messages retrieved successfully'));
        }

        return $this->engineFailedResponse([], $result['message']);
    }

    /**
     * Send message to a specific conversation
     *
     * @param string $conversationId
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendConversationMessage($conversationId, $message, $options = [])
    {
        try {
            // Get messages from the conversation to find the recipient ID
            $messagesResult = $this->instagramApiService->getConversationMessages($conversationId);

            if (!$messagesResult['success']) {
                return $this->engineFailedResponse([], 'Failed to get conversation messages: ' . $messagesResult['message']);
            }

            $messages = $messagesResult['data'];
            $recipientId = null;

            // Get business account information from settings
            $businessAccountId = getVendorSettings('instagram_account_id');
            $businessAccountName = getVendorSettings('instagram_account_name');

            // Create arrays to track all possible business account identifiers
            $businessAccountIds = array_filter([$businessAccountId]); // Remove empty values
            $businessAccountNames = array_filter([$businessAccountName]); // Remove empty values

            Log::info('Instagram conversation messages debug', [
                'conversation_id' => $conversationId,
                'business_account_id' => $businessAccountId,
                'business_account_name' => $businessAccountName,
                'business_account_ids_to_check' => $businessAccountIds,
                'business_account_names_to_check' => $businessAccountNames,
                'messages_count' => count($messages),
                'first_few_messages' => array_slice($messages, 0, 3)
            ]);

            // Find the recipient ID from the messages (the user who is not the business account)
            foreach ($messages as $messageData) {
                if (isset($messageData['from']['id'])) {
                    $fromId = $messageData['from']['id'];
                    $fromUsername = $messageData['from']['username'] ?? 'unknown';

                    // Skip if this is from any of the business accounts (by ID or username)
                    if (!in_array($fromId, $businessAccountIds) && !in_array($fromUsername, $businessAccountNames)) {
                        $recipientId = $fromId;
                        Log::info('Found recipient ID', [
                            'recipient_id' => $recipientId,
                            'from_username' => $fromUsername
                        ]);
                        break;
                    }
                }
            }

            if (!$recipientId) {
                Log::error('No recipient found in messages', [
                    'conversation_id' => $conversationId,
                    'business_account_id' => $businessAccountId,
                    'business_account_ids_checked' => $businessAccountIds,
                    'messages_sample' => array_slice($messages, 0, 2)
                ]);
                return $this->engineFailedResponse([], 'Recipient not found in conversation messages. Business account: ' . $businessAccountId);
            }

            Log::info('Instagram send conversation message', [
                'conversation_id' => $conversationId,
                'recipient_id' => $recipientId,
                'business_account_id' => $businessAccountId,
                'message_text' => $message,
                'message_type' => gettype($message)
            ]);

            // Send message using the existing sendMessage method via API
            $apiResponse = $this->instagramApiService->sendMessage(
                $recipientId,
                $message,
                $options
            );

            if (!$apiResponse['success']) {
                return $this->engineFailedResponse([], $apiResponse['message']);
            }

            return $this->engineSuccessResponse([
                'message_id' => $apiResponse['data']['message_id'] ?? uniqid(),
                'recipient_id' => $recipientId,
                'conversation_id' => $conversationId,
                'api_response' => $apiResponse
            ], __tr('Message sent successfully'));

        } catch (\Exception $e) {
            Log::error('Instagram send conversation message error', [
                'conversation_id' => $conversationId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->engineFailedResponse([], 'Error sending message: ' . $e->getMessage());
        }
    }

    /**
     * Process conversations data to match frontend format
     *
     * @param array $conversations
     * @return array
     */
    private function processConversationsData($conversations)
    {
        $processedConversations = [];

        foreach ($conversations as $conversation) {
            $participants = $conversation['participants']['data'] ?? [];

            // Get the other participant (not the business account)
            $businessAccountName = getVendorSettings('instagram_account_name');
            $otherParticipant = null;
            foreach ($participants as $participant) {
                // Skip the business account
                if ($participant['username'] !== $businessAccountName) {
                    $otherParticipant = $participant;
                    break;
                }
            }

            if (!$otherParticipant) {
                continue; // Skip if no other participant found
            }

            $processedConversations[] = [
                '_uid' => $conversation['id'],
                'conversation_id' => $conversation['id'],
                'instagram_id' => $otherParticipant['id'],
                'instagram_username' => $otherParticipant['username'] ?? null,
                'full_name' => $otherParticipant['username'] ?? 'Instagram User',
                'name_initials' => $this->getNameInitials($otherParticipant['username'] ?? 'IU'),
                'unread_messages_count' => 0,
                'last_message' => null, // Will be populated when we get messages
                'messaged_at' => date('Y-m-d H:i:s') // Current time as fallback
            ];
        }

        return $processedConversations;
    }

    /**
     * Process messages data to match frontend format
     *
     * @param array $messages
     * @return array
     */
    private function processMessagesData($messages)
    {
        $processedMessages = [];

        // Get business account details from settings
        $businessAccountId = getVendorSettings('instagram_account_id');
        $businessAccountName = getVendorSettings('instagram_account_name');

        // Create arrays of business account identifiers
        $businessAccountIds = array_filter([$businessAccountId]);
        $businessAccountNames = array_filter([$businessAccountName, 'cherriq.ai']); // Add known business name

        Log::info('Processing Instagram messages', [
            'business_account_id' => $businessAccountId,
            'business_account_name' => $businessAccountName,
            'business_account_ids' => $businessAccountIds,
            'business_account_names' => $businessAccountNames,
            'total_messages' => count($messages)
        ]);

        foreach ($messages as $message) {
            // Determine if message is incoming (from user) or outgoing (from business)
            $isIncoming = true; // Default to incoming
            $fromId = null;
            $fromUsername = null;

            if (isset($message['from'])) {
                $fromData = $message['from'];

                if (is_array($fromData)) {
                    $fromId = $fromData['id'] ?? null;
                    $fromUsername = $fromData['username'] ?? null;
                } else {
                    $fromId = $fromData;
                }

                // Check if message is from business account (outgoing)
                $isFromBusinessById = $fromId && in_array($fromId, $businessAccountIds);
                $isFromBusinessByName = $fromUsername && in_array($fromUsername, $businessAccountNames);

                // If from business account, it's outgoing (not incoming)
                if ($isFromBusinessById || $isFromBusinessByName) {
                    $isIncoming = false;
                }
            }

            Log::info('Message processing', [
                'message_id' => $message['id'] ?? 'unknown',
                'from_id' => $fromId,
                'from_username' => $fromUsername,
                'is_from_business_by_id' => $isFromBusinessById ?? false,
                'is_from_business_by_name' => $isFromBusinessByName ?? false,
                'is_incoming_message' => $isIncoming,
                'message_preview' => substr($message['message'] ?? '', 0, 30)
            ]);

            $processedMessages[] = [
                '_uid' => $message['id'],
                'id' => $message['id'],
                'message' => $message['message'] ?? '',
                'formatted_message' => $message['message'] ?? '',
                'is_incoming_message' => $isIncoming,
                'time_ago' => 'just now',
                'formatted_date' => date('M j, Y g:i A'),
                'from' => $message['from'] ?? null,
                'status' => $message['status'] ?? null
            ];
        }

        return array_reverse($processedMessages); // Show oldest first
    }

    /**
     * Process comments data to match frontend format
     *
     * @param array $comments
     * @return array
     */
    private function processCommentsData($comments)
    {
        $processedComments = [];

        foreach ($comments as $comment) {
            // Extract username from 'from' object (Instagram API structure)
            $username = 'Unknown User';
            if (isset($comment['from']['username'])) {
                $username = $comment['from']['username'];
            } elseif (isset($comment['username'])) {
                // Fallback for direct username field if present
                $username = $comment['username'];
            }

            $processedComments[] = [
                '_uid' => $comment['id'],
                'id' => $comment['id'],
                'text' => $comment['text'] ?? '',
                'username' => $username,
                'timestamp' => $comment['timestamp'] ?? '',
                'formatted_date' => isset($comment['timestamp']) ?
                    date('M j, Y g:i A', strtotime($comment['timestamp'])) :
                    'Unknown date',
                'time_ago' => isset($comment['timestamp']) ?
                    $this->calculateTimeAgo($comment['timestamp']) :
                    'Unknown time'
            ];
        }

        return $processedComments;
    }

    /**
     * Calculate time ago from timestamp
     *
     * @param string $timestamp
     * @return string
     */
    private function calculateTimeAgo($timestamp)
    {
        $time = time() - strtotime($timestamp);

        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . 'm ago';
        if ($time < 86400) return floor($time/3600) . 'h ago';
        if ($time < 2592000) return floor($time/86400) . 'd ago';

        return date('M j, Y', strtotime($timestamp));
    }

    /**
     * Get name initials
     *
     * @param string $name
     * @return string
     */
    private function getNameInitials($name)
    {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
                if (strlen($initials) >= 2) break;
            }
        }

        return $initials ?: 'IG';
    }

    /**
     * Format message text for display
     *
     * @param string $message
     * @return string
     */
    private function formatMessageText($message)
    {
        if (empty($message)) {
            return '<em>Media message</em>';
        }

        return htmlspecialchars($message);
    }

    /**
     * Get time ago format
     *
     * @param string $datetime
     * @return string
     */
    private function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);

        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . 'm ago';
        if ($time < 86400) return floor($time/3600) . 'h ago';
        if ($time < 2592000) return floor($time/86400) . 'd ago';

        return date('M j, Y', strtotime($datetime));
    }

    /**
     * Find or create contact from Instagram user
     *
     * @param string $instagramId
     * @param array $messagingEvent
     * @return object|null
     */
    protected function findOrCreateContact($instagramId, $messagingEvent)
    {
        // Try to find existing contact
        $contact = $this->contactRepository->fetchIt([
            'instagram_id' => $instagramId,
            'vendors__id' => getVendorId(),
            'platform' => 'instagram'
        ]);

        if ($contact) {
            Log::info('Instagram contact found', [
                'contact_id' => $contact->_id,
                'instagram_id' => $instagramId
            ]);
            return $contact;
        }

        // Extract username from messaging event if available
        $username = '';
        if (isset($messagingEvent['from']['username'])) {
            $username = $messagingEvent['from']['username'];
        } elseif (isset($messagingEvent['sender']['username'])) {
            $username = $messagingEvent['sender']['username'];
        }

        // Get user profile from Instagram API
        $profileResponse = $this->instagramApiService->getUserProfile($instagramId);
        
        $profileData = $profileResponse['success'] ? $profileResponse['data'] : [];

        // Use username from messaging event if profile data doesn't have it
        if (empty($profileData['username']) && !empty($username)) {
            $profileData['username'] = $username;
        }

        // Create new contact
        $contactData = [
            'vendors__id' => getVendorId(),
            'platform' => 'instagram',
            'instagram_id' => $instagramId,
            'instagram_username' => $profileData['username'] ?? $username,
            'first_name' => $profileData['name'] ?? $username,
            'last_name' => '',
            'full_name' => $profileData['name'] ?? $username,
            'countries__id' => 1, // Default country
            'disable_ai_bot' => false,
            '__data' => [
                'profile_data' => $profileData,
                'first_contact_data' => $messagingEvent,
            ]
        ];

        Log::info('Instagram contact created', [
            'instagram_id' => $instagramId,
            'username' => $profileData['username'] ?? $username,
            'contact_data' => $contactData
        ]);

        return $this->contactRepository->storeIt($contactData);
    }

    /**
     * Determine message type from Instagram message data
     *
     * @param array $message
     * @return string
     */
    protected function determineMessageType($message)
    {
        if (isset($message['attachments'])) {
            $attachment = $message['attachments'][0];
            return $attachment['type'] ?? 'attachment';
        }
        
        if (isset($message['reply_to'])) {
            return 'reply';
        }
        
        if (isset($message['story'])) {
            return 'story_reply';
        }
        
        return 'text';
    }

    /**
     * Extract media data from message
     *
     * @param array $message
     * @return array
     */
    protected function extractMediaData($message)
    {
        if (!isset($message['attachments'])) {
            return [];
        }

        $attachment = $message['attachments'][0];
        
        return [
            'type' => $attachment['type'] ?? '',
            'url' => $attachment['payload']['url'] ?? '',
            'attachment_id' => $attachment['payload']['attachment_id'] ?? '',
        ];
    }

    /**
     * Extract reply data from message
     *
     * @param array $message
     * @return array
     */
    protected function extractReplyData($message)
    {
        if (!isset($message['reply_to'])) {
            return [];
        }

        return [
            'message_id' => $message['reply_to']['mid'] ?? '',
        ];
    }

    /**
     * Extract story data from message
     *
     * @param array $message
     * @return array
     */
    protected function extractStoryData($message)
    {
        if (!isset($message['story'])) {
            return [];
        }

        return [
            'story_id' => $message['story']['id'] ?? '',
            'story_url' => $message['story']['url'] ?? '',
        ];
    }

    /**
     * Process delivery receipt
     *
     * @param array $messagingEvent
     * @return void
     */
    protected function processDeliveryReceipt($messagingEvent)
    {
        $delivery = $messagingEvent['delivery'];
        $messageIds = $delivery['mids'] ?? [];

        foreach ($messageIds as $messageId) {
            $this->instagramMessageLogRepository->updateMessageStatus($messageId, 'delivered', $delivery);
        }
    }

    /**
     * Process read receipt
     *
     * @param array $messagingEvent
     * @return void
     */
    protected function processReadReceipt($messagingEvent)
    {
        $read = $messagingEvent['read'];
        $messageIds = $read['mids'] ?? [];

        foreach ($messageIds as $messageId) {
            $this->instagramMessageLogRepository->updateMessageStatus($messageId, 'read', $read);
        }
    }
}
