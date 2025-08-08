<?php

/**
 * FacebookChatApiService.php - Facebook Graph API service for chat
 *
 * This file handles Facebook Graph API interactions for chat functionality
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\FacebookChatService\Services;

use App\Yantrana\Base\BaseEngine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookChatApiService extends BaseEngine
{
    /**
     * Facebook Graph API base URL
     */
    protected $baseApiUrl = 'https://graph.facebook.com/v18.0/';

    /**
     * Vendor ID for configuration
     */
    protected $vendorId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vendorId = getVendorId();
    }

    /**
     * Get service configuration
     *
     * @param string $key
     * @return mixed
     */
    protected function getServiceConfiguration($key)
    {
        return getVendorSettings($key, null, null, $this->vendorId);
    }

    /**
     * Test Facebook API connection
     *
     * @return array
     */
    public function testConnection()
    {
        $pageId = $this->getServiceConfiguration('facebook_page_id');
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($pageId) || empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            $url = "https://graph.facebook.com/v18.0/{$pageId}";
            $params = [
                'fields' => 'id,name,category',
                'access_token' => $accessToken
            ];

            Log::info('Facebook Chat Test Connection API Request', [
                'url' => $url,
                'params' => $params
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Chat Test Connection API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Facebook API connection successful'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to Facebook API. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Test Connection Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error testing Facebook connection: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Facebook conversations
     *
     * @return array
     */
    public function getConversations()
    {
        $pageId = $this->getServiceConfiguration('facebook_page_id');
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($pageId) || empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            $url = "https://graph.facebook.com/v18.0/{$pageId}/conversations";
            $params = [
                'access_token' => $accessToken
            ];

            Log::info('Facebook Chat Conversations API Request', [
                'url' => $url,
                'params' => $params
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Chat Conversations API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                        'paging' => $data['paging'] ?? null,
                        'message' => 'Conversations retrieved successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to retrieve conversations. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Conversations Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving conversations: ' . $e->getMessage()
            ];
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
        $pageId = $this->getServiceConfiguration('facebook_page_id');
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($pageId) || empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            $url = "https://graph.facebook.com/v18.0/{$conversationId}/messages";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'id,message,from,created_time'
            ];

            Log::info('Facebook Chat Conversation Messages API Request', [
                'url' => $url,
                'params' => $params,
                'conversation_id' => $conversationId
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Chat Conversation Messages API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                        'paging' => $data['paging'] ?? null,
                        'message' => 'Messages retrieved successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to retrieve messages. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Conversation Messages Error', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving messages: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send message to Facebook conversation using Messenger API
     *
     * @param string $conversationId
     * @param string $message
     * @return array
     */
    public function sendConversationMessage($conversationId, $message)
    {
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            // First, get the recipient ID from the conversation
            $recipientId = $this->getRecipientIdFromConversation($conversationId);

            if (!$recipientId) {
                return [
                    'success' => false,
                    'message' => 'Could not determine recipient ID from conversation'
                ];
            }

            // Use Facebook Messenger API endpoint
            $url = "https://graph.facebook.com/v18.0/me/messages";
            $data = [
                'recipient' => [
                    'id' => $recipientId
                ],
                'message' => [
                    'text' => $message
                ]
            ];

            $params = [
                'access_token' => $accessToken
            ];

            Log::info('Facebook Chat Send Message API Request', [
                'url' => $url,
                'data' => $data,
                'params' => $params,
                'recipient_id' => $recipientId
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->post($url . '?' . http_build_query($params), $data);

            Log::info('Facebook Chat Send Message API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Message sent successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send message. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Send Message Error', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId
            ]);

            return [
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get recipient ID from conversation messages
     *
     * @param string $conversationId
     * @return string|null
     */
    protected function getRecipientIdFromConversation($conversationId)
    {
        try {
            $accessToken = $this->getServiceConfiguration('facebook_page_access_token');
            $pageId = $this->getServiceConfiguration('facebook_page_id');

            if (empty($accessToken) || empty($pageId)) {
                return null;
            }

            // Get messages from the conversation to find the recipient ID
            $url = "https://graph.facebook.com/v18.0/{$conversationId}/messages";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'from,to',
                'limit' => 1
            ];

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                $messages = $data['data'] ?? [];

                if (!empty($messages)) {
                    $message = $messages[0];

                    // Check the 'to' field for recipient data
                    if (isset($message['to']['data']) && is_array($message['to']['data'])) {
                        foreach ($message['to']['data'] as $recipient) {
                            // Return the first recipient that is not the page
                            if (isset($recipient['id']) && $recipient['id'] !== $pageId) {
                                return $recipient['id'];
                            }
                        }
                    }

                    // Fallback: check 'from' field if it's not from the page
                    if (isset($message['from']['id']) && $message['from']['id'] !== $pageId) {
                        return $message['from']['id'];
                    }
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting recipient ID from conversation', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId
            ]);
            return null;
        }
    }

    /**
     * Get Facebook posts
     *
     * @return array
     */
    public function getPosts()
    {
        $pageId = $this->getServiceConfiguration('facebook_page_id');
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($pageId) || empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            $url = "https://graph.facebook.com/v18.0/{$pageId}/posts";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'id,message,story,created_time'
            ];

            Log::info('Facebook Chat Posts API Request', [
                'url' => $url,
                'params' => $params
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Chat Posts API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                        'paging' => $data['paging'] ?? null,
                        'message' => 'Posts retrieved successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to retrieve posts. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Posts Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving posts: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get comments for a specific post including nested replies
     *
     * @param string $postId
     * @return array
     */
    public function getPostComments($postId)
    {
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            $url = "https://graph.facebook.com/v18.0/{$postId}/comments";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'message,from,created_time,comments{message,from,created_time}'
            ];

            Log::info('Facebook Chat Post Comments API Request', [
                'url' => $url,
                'params' => $params,
                'post_id' => $postId
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Chat Post Comments API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                        'paging' => $data['paging'] ?? null,
                        'message' => 'Comments with replies retrieved successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to retrieve comments. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Post Comments Error', [
                'error' => $e->getMessage(),
                'post_id' => $postId
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving comments: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reply to a Facebook comment via private message using comment_id
     *
     * @param string $commentId
     * @param string $message
     * @return array
     */
    public function replyToComment($commentId, $message)
    {
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            // Use the Messenger API to send a private message using comment_id
            $url = "https://graph.facebook.com/v18.0/me/messages";

            $data = [
                'recipient' => [
                    'comment_id' => $commentId
                ],
                'message' => [
                    'text' => $message
                ]
            ];

            $params = [
                'access_token' => $accessToken
            ];

            Log::info('Facebook Chat Comment Private Reply API Request', [
                'url' => $url,
                'data' => $data,
                'params' => $params,
                'comment_id' => $commentId
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->post($url . '?' . http_build_query($params), $data);

            Log::info('Facebook Chat Comment Private Reply API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Private reply sent successfully',
                    'type' => 'private'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send private reply. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Comment Private Reply Error', [
                'error' => $e->getMessage(),
                'comment_id' => $commentId
            ]);

            return [
                'success' => false,
                'message' => 'Error sending private reply: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reply to a Facebook comment publicly under the post
     *
     * @param string $commentId
     * @param string $message
     * @return array
     */
    public function replyToCommentPublicly($commentId, $message)
    {
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            // Use the Graph API to reply to a comment publicly
            $url = "https://graph.facebook.com/v18.0/{$commentId}/comments";

            $data = [
                'message' => $message
            ];

            $params = [
                'access_token' => $accessToken
            ];

            Log::info('Facebook Chat Comment Public Reply API Request', [
                'url' => $url,
                'data' => $data,
                'params' => $params,
                'comment_id' => $commentId
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->post($url . '?' . http_build_query($params), $data);

            Log::info('Facebook Chat Comment Public Reply API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Public reply posted successfully',
                    'type' => 'public'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to post public reply. Status: ' . $response->status() . '. Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Chat Comment Public Reply Error', [
                'error' => $e->getMessage(),
                'comment_id' => $commentId
            ]);

            return [
                'success' => false,
                'message' => 'Error posting public reply: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get comment data
     *
     * @param string $commentId
     * @return array
     */
    protected function getCommentData($commentId)
    {
        $accessToken = $this->getServiceConfiguration('facebook_page_access_token');

        if (empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            $url = "https://graph.facebook.com/v18.0/{$commentId}";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'from,message,created_time'
            ];

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get comment data'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting comment data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if Facebook API is properly configured
     *
     * @return bool
     */
    public function isConfigured()
    {
        $requiredSettings = [
            'facebook_page_access_token',
            'facebook_page_id'
        ];

        foreach ($requiredSettings as $setting) {
            if (empty($this->getServiceConfiguration($setting))) {
                return false;
            }
        }

        return true;
    }
}
