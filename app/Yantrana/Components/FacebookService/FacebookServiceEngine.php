<?php

namespace App\Yantrana\Components\FacebookService;

use App\Yantrana\Base\BaseEngine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookServiceEngine extends BaseEngine
{
    /**
     * Get Facebook conversations using Graph API
     *
     * @return array
     */
    public function getConversations()
    {
        try {
            $pageId = getVendorSettings('facebook_page_id');
            $accessToken = getVendorSettings('facebook_page_access_token');

            if (empty($pageId) || empty($accessToken)) {
                return $this->engineFailedResponse([], __tr('Facebook API not configured'));
            }

            // Get conversations using Facebook Graph API
            $response = Http::timeout(30)->get("https://graph.facebook.com/v18.0/{$pageId}/conversations", [
                'fields' => 'participants,updated_time,message_count,id',
                'access_token' => $accessToken
            ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                $errorMessage = 'Unknown Facebook API error';
                
                if (isset($errorData['error'])) {
                    $error = $errorData['error'];
                    $errorCode = $error['code'] ?? null;
                    $errorSubcode = $error['error_subcode'] ?? null;
                    $errorMsg = $error['message'] ?? 'Unknown error';
                    
                    Log::error('Facebook conversations API error', [
                        'code' => $errorCode,
                        'subcode' => $errorSubcode,
                        'message' => $errorMsg
                    ]);
                    
                    // Check for OAuth token invalidation errors
                    if ($errorCode == 190) {
                        if ($errorSubcode == 460) {
                            $errorMessage = 'Your Facebook access token has been invalidated. This usually happens when you change your Facebook password or Facebook invalidates the session for security reasons. Please reconnect your Facebook account in the settings to generate a new access token.';
                        } else {
                            $errorMessage = 'Facebook access token error: ' . $errorMsg . '. Please check your Facebook API configuration and reconnect if necessary.';
                        }
                    } else {
                        $errorMessage = 'Facebook API Error: ' . $errorMsg;
                    }
                } else {
                    $errorMessage = $errorData['error']['message'] ?? 'Unknown Facebook API error';
                }
                
                return $this->engineFailedResponse([], __tr($errorMessage));
            }

            $data = $response->json();
            $conversations = [];

            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $conversation) {
                    if (isset($conversation['participants']['data']) && count($conversation['participants']['data']) > 0) {
                        // Find the participant that's not the page itself
                        $participant = null;
                        foreach ($conversation['participants']['data'] as $p) {
                            if ($p['id'] !== $pageId) {
                                $participant = $p;
                                break;
                            }
                        }

                        if ($participant) {
                            $conversations[] = [
                                'id' => $conversation['id'],
                                'participant' => [
                                    'id' => $participant['id'],
                                    'name' => $participant['name'] ?? 'Facebook User',
                                ],
                                'updated_time' => $conversation['updated_time'] ?? now()->toISOString(),
                                'message_count' => $conversation['message_count'] ?? 0,
                                'uid' => $participant['id'] // For compatibility with existing code
                            ];
                        }
                    }
                }
            }

            return $this->engineSuccessResponse([
                'conversations' => $conversations
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook conversations error: ' . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Error fetching Facebook conversations: ') . $e->getMessage());
        }
    }

    /**
     * Get messages for a specific conversation using Graph API
     *
     * @param string $conversationId
     * @return array
     */
    public function getConversationMessages($conversationId)
    {
        try {
            $accessToken = getVendorSettings('facebook_page_access_token');
            $pageId = getVendorSettings('facebook_page_id');

            if (empty($accessToken) || empty($pageId)) {
                return $this->engineFailedResponse([], __tr('Facebook API not configured'));
            }

            // Get messages for the conversation using Facebook Graph API
            $response = Http::timeout(30)->get("https://graph.facebook.com/v18.0/{$conversationId}/messages", [
                'fields' => 'id,message,from,created_time,attachments',
                'access_token' => $accessToken
            ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                $errorMessage = 'Unknown Facebook API error';
                
                if (isset($errorData['error'])) {
                    $error = $errorData['error'];
                    $errorCode = $error['code'] ?? null;
                    $errorSubcode = $error['error_subcode'] ?? null;
                    $errorMsg = $error['message'] ?? 'Unknown error';
                    
                    Log::error('Facebook messages API error', [
                        'code' => $errorCode,
                        'subcode' => $errorSubcode,
                        'message' => $errorMsg
                    ]);
                    
                    // Check for OAuth token invalidation errors
                    if ($errorCode == 190) {
                        if ($errorSubcode == 460) {
                            $errorMessage = 'Your Facebook access token has been invalidated. Please reconnect your Facebook account in the settings.';
                        } else {
                            $errorMessage = 'Facebook access token error: ' . $errorMsg;
                        }
                    } else {
                        $errorMessage = 'Facebook API Error: ' . $errorMsg;
                    }
                } else {
                    $errorMessage = $errorData['error']['message'] ?? 'Unknown Facebook API error';
                }
                
                return $this->engineFailedResponse([], __tr($errorMessage));
            }

            $data = $response->json();
            $messages = [];

            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $message) {
                    $isIncoming = isset($message['from']['id']) && $message['from']['id'] !== $pageId;

                    $messages[] = [
                        'id' => $message['id'],
                        'message' => $message['message'] ?? '',
                        'from' => [
                            'id' => $message['from']['id'] ?? '',
                            'name' => $message['from']['name'] ?? 'Unknown'
                        ],
                        'created_time' => $message['created_time'] ?? now()->toISOString(),
                        'is_incoming_message' => $isIncoming,
                        'attachments' => $message['attachments']['data'] ?? []
                    ];
                }
            }

            // Sort messages by created_time (oldest first)
            usort($messages, function($a, $b) {
                return strtotime($a['created_time']) - strtotime($b['created_time']);
            });

            return $this->engineSuccessResponse([
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook messages error: ' . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Error fetching Facebook messages: ') . $e->getMessage());
        }
    }

    /**
     * Send message to Facebook user via Messenger
     * Note: This requires pages_messaging permission and the user must have initiated conversation
     *
     * @param string $recipientId
     * @param string $message
     * @return array
     */
    public function sendMessage($recipientId, $message)
    {
        try {
            $pageId = getVendorSettings('facebook_page_id');
            $accessToken = getVendorSettings('facebook_page_access_token');

            if (empty($pageId) || empty($accessToken)) {
                return $this->engineFailedResponse([], __tr('Facebook API not configured'));
            }

            // Use the correct Facebook Messenger Send API
            $response = Http::timeout(30)->post("https://graph.facebook.com/v18.0/me/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $message],
                'access_token' => $accessToken
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->engineSuccessResponse([
                    'message_id' => $data['message_id'] ?? null,
                    'recipient_id' => $data['recipient_id'] ?? $recipientId,
                    'message' => __tr('Message sent successfully')
                ]);
            }

            $errorData = $response->json();
            $errorMessage = __tr('Failed to send Facebook message');

            if (isset($errorData['error'])) {
                $error = $errorData['error'];
                $errorMessage = $error['message'] ?? $errorMessage;

                // Provide specific error messages
                if ($error['code'] == 10) {
                    $errorMessage = __tr('Cannot send message to this user. They may need to message your page first.');
                } elseif ($error['code'] == 200) {
                    $errorMessage = __tr('Insufficient permissions. Please check your page access token permissions.');
                }
            }

            Log::error('Facebook send message error: ' . json_encode($errorData));
            return $this->engineFailedResponse([], $errorMessage);

        } catch (\Exception $e) {
            Log::error('Facebook send message exception: ' . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Error sending Facebook message: ') . $e->getMessage());
        }
    }

    /**
     * Test Facebook API connection
     *
     * @return array
     */
    public function testConnection()
    {
        try {
            $pageId = getVendorSettings('facebook_page_id');
            $accessToken = getVendorSettings('facebook_page_access_token');

            if (empty($pageId) || empty($accessToken)) {
                return $this->engineFailedResponse([], __tr('Facebook credentials not configured. Please enter both Page ID and Access Token.'));
            }

            $pageId = trim($pageId);
            $accessToken = trim($accessToken);

            // Make the exact same API call that works in Postman
            $url = "https://graph.facebook.com/v18.0/{$pageId}";
            $params = [
                'fields' => 'id,name,category',
                'access_token' => $accessToken
            ];

            // Build full URL for logging (like Postman)
            $fullUrl = $url . '?' . http_build_query($params);
            Log::info('Facebook API Test - Making request to: ' . $fullUrl);

            $response = Http::timeout(30)->get($url, $params);

            // Log the response
            Log::info('Facebook API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Check if we got valid response data (like Postman response)
                if (isset($data['id']) && isset($data['name'])) {
                    return $this->engineSuccessResponse([
                        'page_info' => [
                            'id' => $data['id'],
                            'name' => $data['name'],
                            'category' => $data['category'] ?? 'Unknown'
                        ],
                        'connection_status' => 'success',
                        'message' => 'Facebook API connection successful! Connected to: ' . $data['name']
                    ]);
                } else {
                    return $this->engineFailedResponse([], 'Invalid response from Facebook API: ' . json_encode($data));
                }
            }

            $errorData = $response->json();
            $errorMessage = __tr('Facebook API connection failed');

            if (isset($errorData['error'])) {
                $error = $errorData['error'];
                $errorMessage = $error['message'] ?? $errorMessage;

                if ($error['code'] == 190) {
                    $errorSubcode = $error['error_subcode'] ?? null;
                    if ($errorSubcode == 460) {
                        $errorMessage = __tr('Your Facebook access token has been invalidated. This usually happens when you change your Facebook password or Facebook invalidates the session for security reasons. Please reconnect your Facebook account in the settings to generate a new access token.');
                    } else {
                        $errorMessage = __tr('Invalid access token. Please check your Facebook Page Access Token.');
                    }
                } elseif ($error['code'] == 803) {
                    $errorMessage = __tr('Invalid Page ID. Please check your Facebook Page ID.');
                } elseif ($error['code'] == 100) {
                    $errorMessage = __tr('Invalid parameter. Please check your Page ID format.');
                }
            }

            return $this->engineFailedResponse([], $errorMessage);

        } catch (\Exception $e) {
            Log::error('Facebook test connection error: ' . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Error testing Facebook connection: ') . $e->getMessage());
        }
    }
}
