<?php

namespace App\Yantrana\Components\FacebookService\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\FacebookService\FacebookServiceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookServiceController extends BaseController
{
    /**
     * @var FacebookServiceEngine
     */
    protected $facebookServiceEngine;

    /**
     * Constructor
     *
     * @param FacebookServiceEngine $facebookServiceEngine
     */
    public function __construct(FacebookServiceEngine $facebookServiceEngine)
    {
        $this->facebookServiceEngine = $facebookServiceEngine;
    }

    /**
     * Load Facebook Chat View
     *
     * @param string $contactUid
     * @return view
     */
    public function chatView($contactUid = null)
    {
        validateVendorAccess('messaging');

        // Check if Facebook messaging is enabled
        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->loadView('facebook.chat', [
                'contactUid' => $contactUid,
                'facebook_enabled' => false,
                'facebook_configured' => false,
                'contacts' => [],
                'messages' => [],
                'currentContact' => null,
                'error_message' => __tr('Facebook messaging is not enabled. Please enable it in settings.')
            ]);
        }

        // Check if Facebook is properly configured
        $requiredSettings = ['facebook_page_id', 'facebook_page_access_token'];
        $missingSettings = [];

        foreach ($requiredSettings as $setting) {
            if (empty(getVendorSettings($setting))) {
                $missingSettings[] = $setting;
            }
        }

        if (!empty($missingSettings)) {
            return $this->loadView('facebook.chat', [
                'contactUid' => $contactUid,
                'facebook_enabled' => true,
                'facebook_configured' => false,
                'contacts' => [],
                'messages' => [],
                'currentContact' => null,
                'error_message' => __tr('Facebook API is not properly configured. Please configure your Facebook credentials in settings.')
            ]);
        }

        // Use mock data for now to display the chat UI
        $contacts = [
            [
                'uid' => 'fb_user_001',
                'name' => 'John Smith',
                'wa_id' => 'fb_user_001',
                'last_message_at' => now()->subMinutes(5)->toISOString(),
                'unread_count' => 2,
                'platform' => 'facebook'
            ],
            [
                'uid' => 'fb_user_002',
                'name' => 'Sarah Johnson',
                'wa_id' => 'fb_user_002',
                'last_message_at' => now()->subHours(2)->toISOString(),
                'unread_count' => 0,
                'platform' => 'facebook'
            ],
            [
                'uid' => 'fb_user_003',
                'name' => 'Mike Wilson',
                'wa_id' => 'fb_user_003',
                'last_message_at' => now()->subDays(1)->toISOString(),
                'unread_count' => 1,
                'platform' => 'facebook'
            ]
        ];

        $messages = [];
        $currentContact = null;

        // If a contact is selected, set current contact and mock messages
        if ($contactUid) {
            foreach ($contacts as $contact) {
                if ($contact['uid'] === $contactUid) {
                    $currentContact = $contact;
                    break;
                }
            }

            // Mock messages for the selected contact
            if ($currentContact) {
                $messages = [
                    [
                        'id' => 'msg_001',
                        'message' => 'Hello! I have a question about your services.',
                        'from' => [
                            'id' => $contactUid,
                            'name' => $currentContact['name']
                        ],
                        'created_time' => now()->subHours(2)->toISOString(),
                        'is_incoming_message' => true,
                        'attachments' => []
                    ],
                    [
                        'id' => 'msg_002',
                        'message' => 'Hi there! I\'d be happy to help you. What would you like to know?',
                        'from' => [
                            'id' => 'page_id',
                            'name' => 'Our Page'
                        ],
                        'created_time' => now()->subHours(2)->addMinutes(5)->toISOString(),
                        'is_incoming_message' => false,
                        'attachments' => []
                    ],
                    [
                        'id' => 'msg_003',
                        'message' => 'I\'m interested in your pricing plans. Can you provide more details?',
                        'from' => [
                            'id' => $contactUid,
                            'name' => $currentContact['name']
                        ],
                        'created_time' => now()->subMinutes(30)->toISOString(),
                        'is_incoming_message' => true,
                        'attachments' => []
                    ],
                    [
                        'id' => 'msg_004',
                        'message' => 'Of course! Let me share our current pricing options with you.',
                        'from' => [
                            'id' => 'page_id',
                            'name' => 'Our Page'
                        ],
                        'created_time' => now()->subMinutes(25)->toISOString(),
                        'is_incoming_message' => false,
                        'attachments' => []
                    ]
                ];
            }
        }

        return $this->loadView('facebook.chat', [
            'contactUid' => $contactUid,
            'facebook_enabled' => true,
            'facebook_configured' => true,
            'contacts' => $contacts,
            'messages' => $messages,
            'currentContact' => $currentContact,
            'error_message' => null
        ]);
    }

    /**
     * Facebook chat view JSON response (for API/AJAX calls)
     *
     * @param string|null $contactUid
     * @return \Illuminate\Http\JsonResponse
     */
    public function chatViewJson($contactUid = null)
    {
        try {
            // Use mock data for now to display the chat UI
            $contacts = [
                [
                    'uid' => 'fb_user_001',
                    'name' => 'John Smith',
                    'wa_id' => 'fb_user_001',
                    'last_message_at' => date('c', strtotime('-5 minutes')),
                    'unread_count' => 2,
                    'platform' => 'facebook'
                ],
                [
                    'uid' => 'fb_user_002',
                    'name' => 'Sarah Johnson',
                    'wa_id' => 'fb_user_002',
                    'last_message_at' => date('c', strtotime('-2 hours')),
                    'unread_count' => 0,
                    'platform' => 'facebook'
                ],
                [
                    'uid' => 'fb_user_003',
                    'name' => 'Mike Wilson',
                    'wa_id' => 'fb_user_003',
                    'last_message_at' => date('c', strtotime('-1 day')),
                    'unread_count' => 1,
                    'platform' => 'facebook'
                ]
            ];

            $messages = [];
            $currentContact = null;

            // If a contact is selected, set current contact and mock messages
            if ($contactUid) {
                foreach ($contacts as $contact) {
                    if ($contact['uid'] === $contactUid) {
                        $currentContact = $contact;
                        break;
                    }
                }

                // Mock messages for the selected contact
                if ($currentContact) {
                    $messages = [
                        [
                            'id' => 'msg_001',
                            'message' => 'Hello! I have a question about your services.',
                            'from' => [
                                'id' => $contactUid,
                                'name' => $currentContact['name']
                            ],
                            'created_time' => date('c', strtotime('-2 hours')),
                            'is_incoming_message' => true,
                            'attachments' => []
                        ],
                        [
                            'id' => 'msg_002',
                            'message' => 'Hi there! I\'d be happy to help you. What would you like to know?',
                            'from' => [
                                'id' => 'page_id',
                                'name' => 'Our Page'
                            ],
                            'created_time' => date('c', strtotime('-2 hours +5 minutes')),
                            'is_incoming_message' => false,
                            'attachments' => []
                        ],
                        [
                            'id' => 'msg_003',
                            'message' => 'I\'m interested in your pricing plans. Can you provide more details?',
                            'from' => [
                                'id' => $contactUid,
                                'name' => $currentContact['name']
                            ],
                            'created_time' => date('c', strtotime('-30 minutes')),
                            'is_incoming_message' => true,
                            'attachments' => []
                        ],
                        [
                            'id' => 'msg_004',
                            'message' => 'Of course! Let me share our current pricing options with you.',
                            'from' => [
                                'id' => 'page_id',
                                'name' => 'Our Page'
                            ],
                            'created_time' => date('c', strtotime('-25 minutes')),
                            'is_incoming_message' => false,
                            'attachments' => []
                        ]
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'contactUid' => $contactUid,
                    'facebook_enabled' => true,
                    'facebook_configured' => true,
                    'contacts' => $contacts,
                    'messages' => $messages,
                    'currentContact' => $currentContact,
                    'error_message' => null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error loading Facebook chat: ' . $e->getMessage(),
                'data' => [
                    'contactUid' => $contactUid,
                    'facebook_enabled' => true,
                    'facebook_configured' => true,
                    'contacts' => [],
                    'messages' => [],
                    'currentContact' => null,
                    'error_message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Get Facebook contacts
     *
     * @return json
     */
    public function getContacts()
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        // Use mock data for contacts
        $contacts = [
            [
                'uid' => 'fb_user_001',
                'name' => 'John Smith',
                'wa_id' => 'fb_user_001',
                'last_message_at' => now()->subMinutes(5)->toISOString(),
                'unread_count' => 2,
                'platform' => 'facebook'
            ],
            [
                'uid' => 'fb_user_002',
                'name' => 'Sarah Johnson',
                'wa_id' => 'fb_user_002',
                'last_message_at' => now()->subHours(2)->toISOString(),
                'unread_count' => 0,
                'platform' => 'facebook'
            ],
            [
                'uid' => 'fb_user_003',
                'name' => 'Mike Wilson',
                'wa_id' => 'fb_user_003',
                'last_message_at' => now()->subDays(1)->toISOString(),
                'unread_count' => 1,
                'platform' => 'facebook'
            ]
        ];

        return $this->processResponse(1, [], [
            'contacts' => $contacts
        ]);
    }

    /**
     * Get contact messages
     *
     * @param string $contactId
     * @return json
     */
    public function getContactMessages($contactId)
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        // Mock messages for the contact
        $messages = [
            [
                'id' => 'msg_001',
                'message' => 'Hello! I have a question about your services.',
                'from' => [
                    'id' => $contactId,
                    'name' => 'Facebook User'
                ],
                'created_time' => now()->subHours(2)->toISOString(),
                'is_incoming_message' => true,
                'attachments' => []
            ],
            [
                'id' => 'msg_002',
                'message' => 'Hi there! I\'d be happy to help you. What would you like to know?',
                'from' => [
                    'id' => 'page_id',
                    'name' => 'Our Page'
                ],
                'created_time' => now()->subHours(2)->addMinutes(5)->toISOString(),
                'is_incoming_message' => false,
                'attachments' => []
            ],
            [
                'id' => 'msg_003',
                'message' => 'I\'m interested in your pricing plans. Can you provide more details?',
                'from' => [
                    'id' => $contactId,
                    'name' => 'Facebook User'
                ],
                'created_time' => now()->subMinutes(30)->toISOString(),
                'is_incoming_message' => true,
                'attachments' => []
            ],
            [
                'id' => 'msg_004',
                'message' => 'Of course! Let me share our current pricing options with you.',
                'from' => [
                    'id' => 'page_id',
                    'name' => 'Our Page'
                ],
                'created_time' => now()->subMinutes(25)->toISOString(),
                'is_incoming_message' => false,
                'attachments' => []
            ]
        ];

        return $this->processResponse(1, [], [
            'messages' => $messages
        ]);
    }

    /**
     * Send message to Facebook contact
     *
     * @param Request $request
     * @return json
     */
    public function sendMessage(Request $request)
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        $request->validate([
            'recipient_id' => 'required|string',
            'message' => 'required|string'
        ]);

        // Mock successful message sending
        return $this->processResponse(1, [], [
            'message_id' => 'mock_msg_' . time(),
            'status' => 'sent',
            'recipient_id' => $request->recipient_id,
            'message' => $request->message,
            'sent_at' => now()->toISOString()
        ]);
    }

    /**
     * Test Facebook API connection (following Instagram pattern)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {

        try {
            // validateVendorAccess('administrative');

            // Check if required settings are configured
            $pageId = getVendorSettings('facebook_page_id');
            $accessToken = getVendorSettings('facebook_page_access_token');
Log::info("", [
                'pageId' => $pageId,
                'accessToken' => $accessToken
            ]);

            if (empty($pageId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facebook Page ID is not configured'
                ]);
            }

            if (empty($accessToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facebook Page Access Token is not configured'
                ]);
            }

            // Test the connection using the engine
            $result = $this->facebookServiceEngine->testConnection();

            if ($result->success()) {
                $data = $result->data();
                return response()->json([
                    'success' => true,
                    'message' => 'Facebook connection successful!',
                    'data' => [
                        'page_info' => $data['page_info'] ?? null,
                        'page_name' => $data['page_info']['name'] ?? null,
                        'page_id' => $data['page_info']['id'] ?? null,
                        'category' => $data['page_info']['category'] ?? null
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result->message() ?? 'Facebook connection test failed'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Facebook test connection controller error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error in test connection: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Facebook message statistics
     *
     * @return json
     */
    public function getMessageStats()
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(1, [], [
                'total_contacts' => 0,
                'unread_count' => 0,
                'enabled' => false
            ]);
        }

        $conversationsResult = $this->facebookServiceEngine->getConversations();
        
        $stats = [
            'total_contacts' => 0,
            'unread_count' => 0,
            'enabled' => true
        ];

        if ($conversationsResult->success()) {
            $data = $conversationsResult->data();
            $stats['total_contacts'] = count($data['conversations'] ?? []);
        }

        return $this->processResponse(1, [], $stats);
    }

    /**
     * Send media message to Facebook contact
     *
     * @param Request $request
     * @return json
     */
    public function sendMediaMessage(Request $request)
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        // For now, return not implemented
        return $this->processResponse(22, [
            22 => __tr('Facebook media messaging not yet implemented')
        ], [], true);
    }

    /**
     * Upload media for Facebook messaging
     *
     * @param Request $request
     * @return json
     */
    public function uploadMedia(Request $request)
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        // For now, return not implemented
        return $this->processResponse(22, [
            22 => __tr('Facebook media upload not yet implemented')
        ], [], true);
    }

    /**
     * Get Facebook page information
     *
     * @return json
     */
    public function getPageInfo()
    {
        validateVendorAccess('messaging');

        $result = $this->facebookServiceEngine->testConnection();

        if ($result->success()) {
            $data = $result->data();
            return $this->processResponse(1, [], [
                'page_info' => $data['page_info'] ?? null
            ]);
        }

        return $this->processResponse($result);
    }

    /**
     * Get Facebook conversations
     *
     * @return json
     */
    public function getConversations()
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        $result = $this->facebookServiceEngine->getConversations();

        return $this->processResponse($result);
    }

    /**
     * Get specific conversation details
     *
     * @param string $conversationId
     * @return json
     */
    public function getConversation($conversationId)
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        $result = $this->facebookServiceEngine->getConversationMessages($conversationId);

        return $this->processResponse($result);
    }

    /**
     * Send message to specific conversation
     *
     * @param Request $request
     * @param string $conversationId
     * @return json
     */
    public function sendConversationMessage(Request $request, $conversationId)
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        $request->validate([
            'message' => 'required|string'
        ]);

        // For conversation messages, we need to get the recipient ID from the conversation
        // This is a simplified implementation
        $result = $this->facebookServiceEngine->sendMessage(
            $request->recipient_id ?? $conversationId,
            $request->message
        );

        return $this->processResponse($result);
    }

    /**
     * Mark messages as read
     *
     * @param string $contactId
     * @return json
     */
    public function markMessagesAsRead($contactId)
    {
        validateVendorAccess('messaging');

        if (!getVendorSettings('enable_facebook_messaging', false)) {
            return $this->processResponse(22, [
                22 => __tr('Facebook messaging is not enabled')
            ], [], true);
        }

        // For now, just return success
        // In a full implementation, you would call Facebook API to mark as read
        return $this->processResponse(1, [], [
            'marked_as_read' => true
        ]);
    }
}
