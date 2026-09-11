<?php

/**
 * FacebookApiService.php - Facebook Graph API service
 *
 * This file handles Facebook Graph API interactions
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\FacebookService\Services;

use App\Yantrana\Base\BaseEngine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookApiService extends BaseEngine
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
        $accessToken = $this->getServiceConfiguration('facebook_access_token');

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

            Log::info('Facebook Test Connection API Request', [
                'url' => $url,
                'params' => $params
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Test Connection API Response', [
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
            Log::error('Facebook Test Connection Error', [
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
        $accessToken = $this->getServiceConfiguration('facebook_access_token');

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

            Log::info('Facebook Conversations API Request', [
                'url' => $url,
                'params' => $params
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Conversations API Response', [
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

            // Parse error response to provide better error messages
            $errorData = $response->json();
            $errorMessage = 'Failed to retrieve conversations';
            
            if (isset($errorData['error'])) {
                $error = $errorData['error'];
                $errorCode = $error['code'] ?? null;
                $errorSubcode = $error['error_subcode'] ?? null;
                $errorMsg = $error['message'] ?? 'Unknown error';
                
                Log::error('Facebook Conversations API Error', [
                    'code' => $errorCode,
                    'subcode' => $errorSubcode,
                    'message' => $errorMsg,
                    'type' => $error['type'] ?? null
                ]);
                
                // Check for OAuth token invalidation errors
                if ($errorCode == 190) {
                    if ($errorSubcode == 460) {
                        $errorMessage = 'Your Facebook access token has been invalidated. This usually happens when you change your Facebook password or Facebook invalidates the session for security reasons. Please reconnect your Facebook account in the settings to generate a new access token.';
                    } else {
                        $errorMessage = 'Facebook access token error: ' . $errorMsg . '. Please check your Facebook API configuration and reconnect if necessary.';
                    }
                } else {
                    $errorMessage = 'Facebook API Error: ' . $errorMsg . ' (Code: ' . $errorCode . ')';
                }
            } else {
                $errorMessage = 'Failed to retrieve conversations. Status: ' . $response->status() . '. Response: ' . $response->body();
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $errorCode ?? null,
                'error_subcode' => $errorSubcode ?? null,
                'requires_reconnect' => isset($errorCode) && $errorCode == 190
            ];

        } catch (\Exception $e) {
            Log::error('Facebook Conversations Error', [
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
        $accessToken = $this->getServiceConfiguration('facebook_access_token');

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

            Log::info('Facebook Conversation Messages API Request', [
                'url' => $url,
                'params' => $params,
                'conversation_id' => $conversationId
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->get($url, $params);

            Log::info('Facebook Conversation Messages API Response', [
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
            Log::error('Facebook Conversation Messages Error', [
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
     * Send message to Facebook conversation
     *
     * @param string $conversationId
     * @param string $message
     * @return array
     */
    public function sendConversationMessage($conversationId, $message)
    {
        $pageId = $this->getServiceConfiguration('facebook_page_id');
        $accessToken = $this->getServiceConfiguration('facebook_access_token');

        if (empty($pageId) || empty($accessToken)) {
            return [
                'success' => false,
                'message' => 'Facebook API not configured'
            ];
        }

        try {
            $url = "https://graph.facebook.com/v18.0/{$pageId}/messages";
            $data = [
                'recipient' => ['id' => $conversationId],
                'message' => ['text' => $message],
                'access_token' => $accessToken
            ];

            Log::info('Facebook Send Message API Request', [
                'url' => $url,
                'data' => $data
            ]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->post($url, $data);

            Log::info('Facebook Send Message API Response', [
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
            Log::error('Facebook Send Message Error', [
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
     * Check if Facebook API is properly configured
     *
     * @return bool
     */
    public function isConfigured()
    {
        $requiredSettings = [
            'facebook_access_token',
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
