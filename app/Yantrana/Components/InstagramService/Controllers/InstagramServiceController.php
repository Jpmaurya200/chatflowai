<?php

/**
 * InstagramServiceController.php - Controller file
 *
 * This file is part of the Instagram Service component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\InstagramService\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\InstagramService\InstagramServiceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstagramServiceController extends BaseController
{
    /**
     * @var InstagramServiceEngine
     */
    protected $instagramServiceEngine;

    /**
     * Constructor
     */
    public function __construct(InstagramServiceEngine $instagramServiceEngine)
    {
        $this->instagramServiceEngine = $instagramServiceEngine;
    }

    /**
     * Instagram webhook verification and message handling
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        // Handle webhook verification (GET request)
        if ($request->isMethod('GET')) {
            return $this->verifyWebhook($request);
        }

        // Handle incoming messages (POST request)
        if ($request->isMethod('POST')) {
            return $this->handleIncomingMessage($request);
        }

        return response('Method not allowed', 405);
    }

    /**
     * Verify Instagram webhook
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    protected function verifyWebhook(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $verifyToken = getVendorSettings('instagram_app_secret');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            // Update webhook verification status
            updateVendorSettings('instagram_webhook_verified_at', now()->toDateTimeString());
            
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming Instagram message
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    protected function handleIncomingMessage(Request $request)
    {
        $webhookData = $request->all();
        
        // Process the webhook
        $processedData = $this->instagramServiceEngine->processIncomingWebhook($webhookData);

        if ($processedData->success()) {
            return response('OK', 200);
        }

        return response('Error processing webhook', 500);
    }

    /**
     * Send Instagram message
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|integer|exists:contacts,_id',
            'message' => 'required|string|max:2000',
            'reply_to_message_id' => 'nullable|string',
        ]);

        $options = [];
        if ($request->has('reply_to_message_id')) {
            $options['reply_to_message_id'] = $request->get('reply_to_message_id');
        }

        $processedData = $this->instagramServiceEngine->sendMessage(
            $request->get('contact_id'),
            $request->get('message'),
            $options
        );

        return $this->processResponse($processedData, [], [], true);
    }

    /**
     * Send Instagram media message
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMediaMessage(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|integer|exists:contacts,_id',
            'media_url' => 'required|url',
            'media_type' => 'required|in:image,video,audio,file',
            'reply_to_message_id' => 'nullable|string',
        ]);

        $options = [];
        if ($request->has('reply_to_message_id')) {
            $options['reply_to_message_id'] = $request->get('reply_to_message_id');
        }

        $processedData = $this->instagramServiceEngine->sendMediaMessage(
            $request->get('contact_id'),
            $request->get('media_url'),
            $request->get('media_type'),
            $options
        );

        return $this->processResponse($processedData, [], [], true);
    }

    /**
     * Get contact messages for chat interface
     *
     * @param Request $request
     * @param int $contactId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContactMessages(Request $request, $contactId)
    {
        $page = $request->get('page', 1);
        
        $processedData = $this->instagramServiceEngine->getContactMessages($contactId, $page);

        return $this->processResponse($processedData, [], [], true);
    }

    /**
     * Get Instagram contacts with recent messages
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContacts(Request $request)
    {
        $limit = $request->get('limit', 50);
        
        $processedData = $this->instagramServiceEngine->getContactsWithMessages($limit);

        return $this->processResponse($processedData, [], [], true);
    }

    /**
     * Mark contact messages as read
     *
     * @param Request $request
     * @param int $contactId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markMessagesAsRead(Request $request, $contactId)
    {
        $processedData = $this->instagramServiceEngine->markMessagesAsRead($contactId);

        return $this->processResponse($processedData, [], [], true);
    }

    /**
     * Get Instagram message statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessageStats(Request $request)
    {
        $period = $request->get('period', 'today');
        
        $processedData = $this->instagramServiceEngine->getMessageStats($period);

        return $this->processResponse($processedData, [], [], true);
    }

    /**
     * Test Instagram API connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            // Check if required settings are configured
            $accountId = getVendorSettings('instagram_account_id');
            $accessToken = getVendorSettings('instagram_access_token');

            if (empty($accountId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instagram Account ID is not configured'
                ]);
            }

            if (empty($accessToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instagram Access Token is not configured'
                ]);
            }

            // Direct API call to Instagram Graph API
            $url = "https://graph.instagram.com/{$accountId}";
            $params = [
                'fields' => 'id,username,media_count,account_type,profile_picture_url',
                'access_token' => $accessToken
            ];

            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false, // Disable SSL verification for local development
                'timeout' => 30,
            ])->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['id'])) {
                    // Update account name in settings if available
                    if (!empty($data['username'])) {
                        $vendorSettingsEngine = app(\App\Yantrana\Components\Vendor\VendorSettingsEngine::class);
                        $vendorSettingsEngine->updateProcess('instagram_api_setup', ['instagram_account_name' => $data['username']]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Instagram connection successful!',
                        'data' => [
                            'id' => $data['id'],
                            'username' => $data['username'] ?? '',
                            'media_count' => $data['media_count'] ?? 0,
                            'account_type' => $data['account_type'] ?? '',
                            'profile_picture_url' => $data['profile_picture_url'] ?? '',
                        ]
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Instagram account information. Status: ' . $response->status() . '. Response: ' . $response->body()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Instagram configuration status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfigurationStatus()
    {
        $requiredSettings = [
            'instagram_access_token',
            'instagram_account_id',
        ];

        $configStatus = [];
        $allConfigured = true;

        foreach ($requiredSettings as $setting) {
            $value = getVendorSettings($setting);
            $configStatus[$setting] = [
                'configured' => !empty($value),
                'value' => !empty($value) ? '***' : ''
            ];

            if (empty($value)) {
                $allConfigured = false;
            }
        }

        // Check if access token is valid and account ID is valid
        $configStatus['access_token_valid'] = !empty(getVendorSettings('instagram_access_token'));
        $configStatus['account_id_valid'] = !empty(getVendorSettings('instagram_account_id'));
        $configStatus['connection_active'] = $allConfigured;
        $configStatus['messaging_enabled'] = getVendorSettings('enable_instagram_messaging', false);
        $configStatus['all_configured'] = $allConfigured;

        return response()->json([
            'success' => true,
            'data' => $configStatus
        ]);
    }

    /**
     * Update Instagram configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateConfiguration(Request $request)
    {
        $request->validate([
            'instagram_account_id' => 'nullable|string',
            'instagram_account_name' => 'nullable|string',
            'instagram_access_token' => 'nullable|string',
            'enable_instagram_messaging' => 'boolean',
        ]);

        $settings = $request->only([
            'instagram_account_id',
            'instagram_account_name',
            'instagram_access_token',
            'enable_instagram_messaging',
        ]);

        // Use the vendor settings engine to update settings
        $vendorSettingsEngine = app(\App\Yantrana\Components\Vendor\VendorSettingsEngine::class);

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                $vendorSettingsEngine->updateProcess('instagram_api_setup', [$key => $value]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __tr('Instagram configuration updated successfully')
        ]);
    }

    /**
     * Get Instagram page information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPageInfo()
    {
        try {
            // Check if required settings are configured
            $accountId = getVendorSettings('instagram_account_id');
            $accessToken = getVendorSettings('instagram_access_token');

            if (empty($accountId) || empty($accessToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instagram configuration is incomplete'
                ]);
            }

            // Direct API call to Instagram Graph API
            $url = "https://graph.instagram.com/{$accountId}";
            $params = [
                'fields' => 'id,username,media_count,account_type,profile_picture_url',
                'access_token' => $accessToken
            ];

            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false, // Disable SSL verification for local development
                'timeout' => 30,
            ])->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['id'])) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'id' => $data['id'],
                            'username' => $data['username'] ?? '',
                            'media_count' => $data['media_count'] ?? 0,
                            'account_type' => $data['account_type'] ?? '',
                            'profile_picture_url' => $data['profile_picture_url'] ?? '',
                        ]
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Instagram account information'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving page info: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Load Instagram Chat View
     *
     * @param string $contactUid
     * @return view
     */
    public function chatView($contactUid = null)
    {
        validateVendorAccess('messaging');

        // Check if Instagram messaging is enabled
        if (!getVendorSettings('enable_instagram_messaging', false)) {
            return $this->loadView('instagram.chat', [
                'contactUid' => $contactUid,
                'instagram_enabled' => false,
                'contacts' => [],
                'messages' => [],
                'currentContact' => null,
            ]);
        }

        // Get Instagram conversations
        $conversationsResult = $this->instagramServiceEngine->getConversations();
        $conversations = [];

        if ($conversationsResult->success()) {
            $data = $conversationsResult->data();
            $conversations = $data['conversations'] ?? [];
        }

        // Get current contact and messages if contactUid is provided
        $currentContact = null;
        $messages = [];

        if ($contactUid && !empty($conversations)) {
            // Find the contact by conversation ID or contact UID
            foreach ($conversations as $conversation) {
                if ($conversation['_uid'] === $contactUid || $conversation['conversation_id'] === $contactUid) {
                    $currentContact = $conversation;

                    // Get messages for this conversation
                    $messagesResult = $this->instagramServiceEngine->getConversationMessages($conversation['conversation_id']);
                    if ($messagesResult->success()) {
                        $messagesData = $messagesResult->data();
                        $messages = $messagesData['messages'] ?? [];
                    }
                    break;
                }
            }
        }

        $chatData = [
            'contactUid' => $contactUid,
            'instagram_enabled' => true,
            'contacts' => $conversations,
            'messages' => $messages,
            'currentContact' => $currentContact,
        ];

        if (request()->ajax()) {
            return $this->processResponse(1, [], $chatData);
        }

        // Load the Instagram chat view
        return $this->loadView('instagram.chat', $chatData);
    }

    /**
     * Get Instagram conversations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversations()
    {
        try {
            $conversationsResult = $this->instagramServiceEngine->getConversations();

            if ($conversationsResult->success()) {
                $data = $conversationsResult->data();
                $conversations = $data['conversations'] ?? [];

                return response()->json([
                    'success' => true,
                    'data' => $conversations,
                    'paging' => $data['paging'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $conversationsResult->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving conversations: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get messages for a specific conversation
     *
     * @param string $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversationMessages($conversationId)
    {
        try {
            $messagesResult = $this->instagramServiceEngine->getConversationMessages($conversationId);

            if ($messagesResult->success()) {
                $data = $messagesResult->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['messages'] ?? [],
                    'paging' => $data['paging'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $messagesResult->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving messages: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send message to a specific conversation
     *
     * @param Request $request
     * @param string $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendConversationMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'reply_to_message_id' => 'nullable|string',
        ]);

        $options = [];
        if ($request->has('reply_to_message_id')) {
            $options['reply_to_message_id'] = $request->get('reply_to_message_id');
        }

        try {
            $processedData = $this->instagramServiceEngine->sendConversationMessage(
                $conversationId,
                $request->get('message'),
                $options
            );

            if ($processedData->success()) {
                return response()->json([
                    'success' => true,
                    'data' => $processedData->data(),
                    'message' => $processedData->message()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $processedData->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Upload media for Instagram messaging
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadMedia(Request $request)
    {
        $request->validate([
            'media_file' => 'required|file|max:10240', // 10MB max
            'media_type' => 'required|in:image,video,audio,file',
        ]);

        // Process file upload using existing media engine
        $mediaEngine = app(\App\Yantrana\Components\Media\MediaEngine::class);
        
        $uploadResult = $mediaEngine->processUpload([
            'filepond' => $request->file('media_file')
        ], 'instagram_media');

        if ($uploadResult->failed()) {
            return response()->json([
                'success' => false,
                'message' => $uploadResult->message()
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'media_url' => $uploadResult->data('path'),
                'media_type' => $request->get('media_type')
            ],
            'message' => __tr('Media uploaded successfully')
        ]);
    }

    /**
     * Get Instagram media posts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMediaPosts()
    {
        try {
            $processedData = $this->instagramServiceEngine->getMediaPosts();

            if ($processedData->success()) {
                return response()->json([
                    'success' => true,
                    'data' => $processedData->data(),
                    'message' => $processedData->message()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $processedData->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving media posts: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get comments for a specific media post
     *
     * @param string $mediaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMediaComments($mediaId)
    {
        try {
            $processedData = $this->instagramServiceEngine->getMediaComments($mediaId);

            if ($processedData->success()) {
                return response()->json([
                    'success' => true,
                    'data' => $processedData->data(),
                    'message' => $processedData->message()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $processedData->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving comments: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send reply to comment author
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendCommentReply(Request $request)
    {
        try {
            $request->validate([
                'comment_id' => 'required|string',
                'message' => 'required|string|max:2000',
            ]);

            Log::info('Comment reply request received', [
                'comment_id' => $request->get('comment_id'),
                'message_length' => strlen($request->get('message'))
            ]);

            $processedData = $this->instagramServiceEngine->sendCommentReply(
                $request->get('comment_id'),
                $request->get('message')
            );

            Log::info('Comment reply engine response', [
                'success' => $processedData->success(),
                'message' => $processedData->message(),
                'data' => $processedData->data()
            ]);

            // Always return a proper JSON response
            return response()->json([
                'success' => $processedData->success(),
                'message' => $processedData->message(),
                'data' => $processedData->data()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Comment reply validation error', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_flatten($e->errors()))
            ]);

        } catch (\Exception $e) {
            Log::error('Comment reply controller error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending comment reply: ' . $e->getMessage()
            ]);
        }
    }
}
