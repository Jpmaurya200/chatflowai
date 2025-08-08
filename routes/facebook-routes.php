<?php

/**
 * Facebook Service Routes
 * 
 * This file contains all routes related to Facebook messaging functionality
 * including chat interface, API endpoints, and webhook handling.
 */

use Illuminate\Support\Facades\Route;
use App\Yantrana\Components\FacebookService\Controllers\FacebookServiceController;
use App\Yantrana\Components\FacebookService\Controllers\FacebookWebhookController;
use App\Yantrana\Components\FacebookChatService\Controllers\FacebookChatServiceController;
use App\Yantrana\Components\LiveChat\Controllers\LiveChatController;

/*
|--------------------------------------------------------------------------
| Facebook Webhook Routes (Public)
|--------------------------------------------------------------------------
| These routes handle Facebook webhook events and don't require authentication
*/

// Facebook webhook endpoint for receiving messages and events
Route::match(['GET', 'POST'], '/facebook/webhook/{vendorUid}', [FacebookWebhookController::class, 'handle'])
    ->name('facebook.webhook');

/*
|--------------------------------------------------------------------------
| Vendor Facebook Routes (Authenticated)
|--------------------------------------------------------------------------
| These routes require vendor authentication and are used for the Facebook
| messaging interface and API endpoints
*/

Route::group([
    'prefix' => 'vendor-console',
    'middleware' => ['web', 'vendor.authenticate'],
    'as' => 'vendor.'
], function () {
    
    /*
    |--------------------------------------------------------------------------
    | Facebook Chat Interface Routes
    |--------------------------------------------------------------------------
    */
    
    // Facebook chat view - Simple view return
    Route::get('/facebook/chat/{contactUid?}', function($contactUid = null) {
        // Mock data for the view
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

        return view('facebook.chat', [
            'contactUid' => $contactUid,
            'facebook_enabled' => true,
            'facebook_configured' => true,
            'contacts' => $contacts,
            'messages' => $messages,
            'currentContact' => $currentContact,
            'error_message' => null
        ]);
    })->name('facebook_chat.contact.view');

    // Facebook contact chat view (consistent with Instagram and WhatsApp pattern)
    Route::get('/facebook/contact/chat/{contactUid?}', function($contactUid = null) {
        // Mock data for the view
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
                        'message' => 'Hi! Thanks for reaching out. How can I help you today?',
                        'from' => [
                            'id' => 'page_' . getVendorSettings('facebook_page_id'),
                            'name' => 'Your Business'
                        ],
                        'created_time' => date('c', strtotime('-1 hour')),
                        'is_incoming_message' => false,
                        'attachments' => []
                    ],
                    [
                        'id' => 'msg_003',
                        'message' => 'I\'m interested in your pricing plans.',
                        'from' => [
                            'id' => $contactUid,
                            'name' => $currentContact['name']
                        ],
                        'created_time' => date('c', strtotime('-30 minutes')),
                        'is_incoming_message' => true,
                        'attachments' => []
                    ]
                ];
            }
        }

        return view('facebook.chat', [
            'contactUid' => $contactUid,
            'facebook_enabled' => true,
            'facebook_configured' => true,
            'contacts' => $contacts,
            'messages' => $messages,
            'currentContact' => $currentContact,
            'error_message' => null
        ]);
    })->name('facebook.contact.chat.view');

    // Facebook contact chat data
    Route::get('/facebook/contact/chat-data/{contactUid}/{way?}', [FacebookServiceController::class, 'getContactMessages'])
        ->name('facebook.contact.chat.data');

    // Send Facebook chat message
    Route::post('/facebook/contact/chat/send', [FacebookServiceController::class, 'sendMessage'])
        ->name('facebook.contact.chat.send');

    // Send Facebook media message
    Route::post('/facebook/contact/chat/send-media', [FacebookServiceController::class, 'sendMediaMessage'])
        ->name('facebook.contact.chat.send_media');

    // Prepare Facebook media upload
    Route::get('/facebook/contact/chat/prepare-send-media/{mediaType?}', [FacebookServiceController::class, 'uploadMedia'])
        ->name('facebook.contact.chat.prepare_media');

    // Clear Facebook chat history
    Route::post('/facebook/contact/chat/clear-history/{contactUid}', [FacebookServiceController::class, 'markMessagesAsRead'])
        ->name('facebook.contact.chat.clear_history');

    // Get Facebook contacts
    Route::get('/facebook/contacts', [FacebookServiceController::class, 'getContacts'])
        ->name('facebook.contacts');

    // Get contact messages
    Route::get('/facebook/contact/{contactId}/messages', [FacebookServiceController::class, 'getContactMessages'])
        ->name('facebook.contact.messages');

    // Send message to contact
    Route::post('/facebook/send-message', [FacebookServiceController::class, 'sendMessage'])
        ->name('facebook.send_message');

    // Mark contact messages as read
    Route::post('/facebook/contact/{contactId}/mark-read', [FacebookServiceController::class, 'markMessagesAsRead'])
        ->name('facebook.contact.mark_read');

    // Get Facebook message statistics
    Route::get('/facebook/stats', [FacebookServiceController::class, 'getMessageStats'])
        ->name('facebook.stats');

    // Debug Facebook settings
    Route::get('/facebook/debug-settings', function() {
        try {
            $pageId = getVendorSettings('facebook_page_id');
            $accessToken = getVendorSettings('facebook_page_access_token');
            $appId = getVendorSettings('facebook_app_id');
            $appSecret = getVendorSettings('facebook_app_secret');

            return response()->json([
                'reaction' => 1,
                'data' => [
                    'page_id_set' => !empty($pageId),
                    'page_id_length' => strlen($pageId ?? ''),
                    'token_set' => !empty($accessToken),
                    'token_length' => strlen($accessToken ?? ''),
                    'app_id_set' => !empty($appId),
                    'app_secret_set' => !empty($appSecret),
                    'vendor_id' => getVendorId(),
                    'message' => 'Settings debug info'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'reaction' => 2,
                'data' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    })->name('facebook.debug_settings');

    // Test Facebook API connection (GET for debugging)
    Route::get('/facebook/test-connection-get', [FacebookServiceController::class, 'testConnection'])
        ->name('facebook.test_connection_get');

    // Test Facebook API connection (POST like Instagram)
    Route::post('/facebook/test-connection', [FacebookServiceController::class, 'testConnection'])
        ->name('facebook.test_connection');

    /*
    |--------------------------------------------------------------------------
    | Facebook Media and Attachment Routes
    |--------------------------------------------------------------------------
    */
    
    // Send Facebook media message
    Route::post('/facebook/send-media-message', [FacebookServiceController::class, 'sendMediaMessage'])
        ->name('facebook.send_media_message');

    // Upload media for Facebook messaging
    Route::post('/facebook/upload-media', [FacebookServiceController::class, 'uploadMedia'])
        ->name('facebook.upload_media');

    /*
    |--------------------------------------------------------------------------
    | Facebook Page Management Routes
    |--------------------------------------------------------------------------
    */
    
    // Get Facebook page information
    Route::get('/facebook/page-info', [FacebookServiceController::class, 'getPageInfo'])
        ->name('facebook.page_info');

    // Get Facebook page conversations
    Route::get('/facebook/conversations', [FacebookServiceController::class, 'getConversations'])
        ->name('facebook.conversations');

    // Get specific conversation details
    Route::get('/facebook/conversation/{conversationId}', [FacebookServiceController::class, 'getConversation'])
        ->name('facebook.conversation.details');

    // Send message to conversation
    Route::post('/facebook/conversation/{conversationId}/send', [FacebookServiceController::class, 'sendConversationMessage'])
        ->name('facebook.conversation.send');

    /*
    |--------------------------------------------------------------------------
    | Facebook Chat Service Routes (New Implementation)
    |--------------------------------------------------------------------------
    | These routes handle Facebook chat functionality using the new service
    */

    // Test Facebook Chat API connection
    Route::post('/facebook-chat/test-connection', [FacebookChatServiceController::class, 'testConnection'])
        ->name('facebook_chat.test_connection');

    // Get Facebook Chat page information
    Route::get('/facebook-chat/page-info', [FacebookChatServiceController::class, 'getPageInfo'])
        ->name('facebook_chat.page_info');

    // Get Facebook Chat conversations
    Route::get('/facebook-chat/conversations', [FacebookChatServiceController::class, 'getConversations'])
        ->name('facebook_chat.conversations');

    // Get Facebook Chat conversation messages
    Route::get('/facebook-chat/conversation/{conversationId}/messages', [FacebookChatServiceController::class, 'getConversationMessages'])
        ->name('facebook_chat.conversation.messages');

    // Send message to Facebook Chat conversation
    Route::post('/facebook-chat/conversation/{conversationId}/send', [FacebookChatServiceController::class, 'sendConversationMessage'])
        ->name('facebook_chat.conversation.send');

    // Get Facebook Chat statistics
    Route::get('/facebook-chat/stats', [FacebookChatServiceController::class, 'getChatStats'])
        ->name('facebook_chat.stats');

    // Check Facebook Chat configuration
    Route::get('/facebook-chat/configuration', [FacebookChatServiceController::class, 'checkConfiguration'])
        ->name('facebook_chat.configuration');

    // Get Facebook posts
    Route::get('/facebook-chat/posts', [FacebookChatServiceController::class, 'getPosts'])
        ->name('facebook_chat.posts');

    // Get Facebook post comments
    Route::get('/facebook-chat/post/{postId}/comments', [FacebookChatServiceController::class, 'getPostComments'])
        ->name('facebook_chat.post.comments');

    // Reply to Facebook comment privately (DM)
    Route::post('/facebook-chat/comment/reply', [FacebookChatServiceController::class, 'replyToComment'])
        ->name('facebook_chat.comment.reply');

    // Reply to Facebook comment publicly (under post)
    Route::post('/facebook-chat/comment/reply-public', [FacebookChatServiceController::class, 'replyToCommentPublicly'])
        ->name('facebook_chat.comment.reply_public');
});

/*
|--------------------------------------------------------------------------
| Facebook API Routes (External API Access)
|--------------------------------------------------------------------------
| These routes provide API access for external applications
*/

Route::group([
    'prefix' => 'api/v1/facebook',
    'middleware' => ['api.vendor.authenticate'],
    'as' => 'api.facebook.'
], function () {
    
    // Send Facebook message via API
    Route::post('/send-message', [FacebookServiceController::class, 'sendMessage'])
        ->name('send_message');
    
    // Get Facebook contacts via API
    Route::get('/contacts', [FacebookServiceController::class, 'getContacts'])
        ->name('contacts');
    
    // Get Facebook message stats via API
    Route::get('/stats', [FacebookServiceController::class, 'getMessageStats'])
        ->name('stats');

    // Get Facebook conversations via API
    Route::get('/conversations', [FacebookServiceController::class, 'getConversations'])
        ->name('conversations');

    // Get Facebook conversation messages via API
    Route::get('/conversation/{conversationId}/messages', [FacebookServiceController::class, 'getConversationMessages'])
        ->name('conversation.messages');
});

/*
|--------------------------------------------------------------------------
| Facebook SSO API Routes (Single Sign-On Integration)
|--------------------------------------------------------------------------
| These routes are for SSO integration with Facebook messaging
*/

Route::group([
    'prefix' => 'api/sso',
    'middleware' => ['api.sso.authenticate'],
    'as' => 'api.sso.'
], function () {
    
    // Facebook Service Routes
    Route::prefix('/facebook')->group(function () {
        Route::post('/send-message/{vendorUid}', [
            FacebookServiceController::class,
            'apiSendMessage',
        ])->name('facebook.send_message');
        
        Route::post('/send-media-message/{vendorUid}', [
            FacebookServiceController::class,
            'apiSendMediaMessage',
        ])->name('facebook.send_media_message');
        
        Route::get('/contacts-data/{contactUid?}', [
            FacebookServiceController::class,
            'getContactsData',
        ])->name('facebook.contacts.data.read');

        Route::get('/conversations/{vendorUid}', [
            FacebookServiceController::class,
            'getConversationsData',
        ])->name('facebook.conversations.data.read');
    });
});
