<?php

/**
 * Instagram Service Routes
 *
 * This file contains all routes related to Instagram messaging functionality
 *-----------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Route;
use App\Yantrana\Components\InstagramService\Controllers\InstagramServiceController;
use App\Yantrana\Components\InstagramService\Controllers\InstagramWebhookController;
use App\Yantrana\Components\LiveChat\Controllers\LiveChatController;

/*
|--------------------------------------------------------------------------
| Instagram Webhook Routes (Public)
|--------------------------------------------------------------------------
| These routes handle Instagram webhook events and don't require authentication
*/

// Instagram webhook endpoint for receiving messages and events
Route::match(['GET', 'POST'], '/instagram/webhook/{vendorUid}', [InstagramWebhookController::class, 'handle'])
    ->name('instagram.webhook');

// Instagram webhook field verification
Route::get('/instagram/webhook/{vendorUid}/field/{field}', [InstagramWebhookController::class, 'verifyField'])
    ->name('instagram.webhook.field');

/*
|--------------------------------------------------------------------------
| Vendor Instagram Routes (Authenticated)
|--------------------------------------------------------------------------
| These routes require vendor authentication and are used for Instagram management
*/

Route::middleware([
    App\Http\Middleware\Authenticate::class,
    App\Http\Middleware\VendorAccessCheckpost::class,
])->prefix('vendor-console')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Instagram Configuration Routes
    |--------------------------------------------------------------------------
    */
    
    // Get Instagram configuration status
    Route::get('/instagram/config/status', [InstagramServiceController::class, 'getConfigurationStatus'])
        ->name('vendor.instagram.config.status');

    // Update Instagram configuration
    Route::post('/instagram/config/update', [InstagramServiceController::class, 'updateConfiguration'])
        ->name('vendor.instagram.config.update');

    // Test Instagram API connection
    Route::post('/instagram/test-connection', [InstagramServiceController::class, 'testConnection'])
        ->name('vendor.instagram.test_connection');

    // Get Instagram page information
    Route::get('/instagram/page-info', [InstagramServiceController::class, 'getPageInfo'])
        ->name('vendor.instagram.page_info');

    // Get Instagram conversations
    Route::get('/instagram/conversations', [InstagramServiceController::class, 'getConversations'])
        ->name('vendor.instagram.conversations');

    // Get Instagram conversation messages
    Route::get('/instagram/conversation/{conversationId}/messages', [InstagramServiceController::class, 'getConversationMessages'])
        ->name('vendor.instagram.conversation.messages');

    // Send message to conversation
    Route::post('/instagram/conversation/{conversationId}/send', [InstagramServiceController::class, 'sendConversationMessage'])
        ->name('vendor.instagram.conversation.send');

    // Instagram media and comments routes
    Route::get('/instagram/media', [InstagramServiceController::class, 'getMediaPosts'])
        ->name('vendor.instagram.media');

    Route::get('/instagram/media/{mediaId}/comments', [InstagramServiceController::class, 'getMediaComments'])
        ->name('vendor.instagram.media.comments');

    Route::post('/instagram/comment/reply', [InstagramServiceController::class, 'sendCommentReply'])
        ->name('vendor.instagram.comment.reply');

    // Test Instagram API directly
    Route::get('/instagram/test-api', [InstagramServiceController::class, 'testInstagramApi'])
        ->name('vendor.instagram.test_api');

    /*
    |--------------------------------------------------------------------------
    | Instagram Messaging Routes
    |--------------------------------------------------------------------------
    */
    
    // Send Instagram message
    Route::post('/instagram/send-message', [InstagramServiceController::class, 'sendMessage'])
        ->name('vendor.instagram.send_message');

    // Send Instagram media message
    Route::post('/instagram/send-media-message', [InstagramServiceController::class, 'sendMediaMessage'])
        ->name('vendor.instagram.send_media_message');

    // Upload media for Instagram messaging
    Route::post('/instagram/upload-media', [InstagramServiceController::class, 'uploadMedia'])
        ->name('vendor.instagram.upload_media');

    /*
    |--------------------------------------------------------------------------
    | Instagram Contact and Message Management Routes
    |--------------------------------------------------------------------------
    */
    
    // Get Instagram contacts
    Route::get('/instagram/contacts', [InstagramServiceController::class, 'getContacts'])
        ->name('vendor.instagram.contacts');

    // Get contact messages
    Route::get('/instagram/contact/{contactId}/messages', [InstagramServiceController::class, 'getContactMessages'])
        ->name('vendor.instagram.contact.messages');

    // Mark contact messages as read
    Route::post('/instagram/contact/{contactId}/mark-read', [InstagramServiceController::class, 'markMessagesAsRead'])
        ->name('vendor.instagram.contact.mark_read');

    // Get Instagram message statistics
    Route::get('/instagram/stats', [InstagramServiceController::class, 'getMessageStats'])
        ->name('vendor.instagram.stats');

    /*
    |--------------------------------------------------------------------------
    | Instagram Chat Interface Routes
    |--------------------------------------------------------------------------
    */
    
    // Instagram chat view (similar to WhatsApp chat)
    Route::get('/instagram/chat/{contactUid?}', [InstagramServiceController::class, 'chatView'])
        ->name('vendor.instagram_chat.contact.view');

    // Instagram contact chat view (consistent with WhatsApp pattern)
    Route::get('/instagram/contact/chat/{contactUid?}', [InstagramServiceController::class, 'chatView'])
        ->name('vendor.instagram.contact.chat.view');

    // Instagram contact chat data
    Route::get('/instagram/contact/chat-data/{contactUid}/{way?}', [InstagramServiceController::class, 'getContactMessages'])
        ->name('vendor.instagram.contact.chat.data');

    // Send Instagram chat message
    Route::post('/instagram/contact/chat/send', [InstagramServiceController::class, 'sendMessage'])
        ->name('vendor.instagram.contact.chat.send');

    // Send Instagram media message
    Route::post('/instagram/contact/chat/send-media', [InstagramServiceController::class, 'sendMediaMessage'])
        ->name('vendor.instagram.contact.chat.send_media');

    // Prepare Instagram media upload
    Route::get('/instagram/contact/chat/prepare-send-media/{mediaType?}', [InstagramServiceController::class, 'uploadMedia'])
        ->name('vendor.instagram.contact.chat.prepare_media');

    // Clear Instagram chat history
    Route::post('/instagram/contact/chat/clear-history/{contactUid}', [InstagramServiceController::class, 'markMessagesAsRead'])
        ->name('vendor.instagram.contact.chat.clear_history');

    /*
    |--------------------------------------------------------------------------
    | Instagram Webhook Management Routes
    |--------------------------------------------------------------------------
    */

    // Get webhook status
    Route::get('/instagram/webhook/status/{vendorUid}', [InstagramWebhookController::class, 'getStatus'])
        ->name('vendor.instagram.webhook.status');

    // Test webhook connectivity
    Route::post('/instagram/webhook/test/{vendorUid}', [InstagramWebhookController::class, 'testWebhook'])
        ->name('vendor.instagram.webhook.test');

    /*
    |--------------------------------------------------------------------------
    | Unified Live Chat Routes
    |--------------------------------------------------------------------------
    */

    // Unified live chat interface
    Route::get('/live-chat', [LiveChatController::class, 'index'])
        ->name('vendor.live_chat.index');

    // Get contacts for live chat (unified from both platforms)
    Route::get('/live-chat/contacts', [LiveChatController::class, 'getContacts'])
        ->name('vendor.live_chat.contacts');

    // Get live chat statistics
    Route::get('/live-chat/stats', [LiveChatController::class, 'getStats'])
        ->name('vendor.live_chat.stats');

    // Get contact messages (unified endpoint)
    Route::get('/live-chat/contact/{contactUid}/messages', [LiveChatController::class, 'getContactMessages'])
        ->name('vendor.live_chat.contact.messages');

    // Send message (unified endpoint)
    Route::post('/live-chat/send-message', [LiveChatController::class, 'sendMessage'])
        ->name('vendor.live_chat.send_message');

    // Mark messages as read (unified endpoint)
    Route::post('/live-chat/contact/{contactUid}/mark-read', [LiveChatController::class, 'markMessagesAsRead'])
        ->name('vendor.live_chat.contact.mark_read');
});

/*
|--------------------------------------------------------------------------
| API Routes for Instagram (Optional)
|--------------------------------------------------------------------------
| These routes can be used for API access to Instagram functionality
*/

Route::group([
    'prefix' => 'api/v1/instagram',
    'middleware' => ['api.vendor.authenticate'],
    'as' => 'api.instagram.'
], function () {
    
    // Send Instagram message via API
    Route::post('/send-message', [InstagramServiceController::class, 'sendMessage'])
        ->name('send_message');
    
    // Get Instagram contacts via API
    Route::get('/contacts', [InstagramServiceController::class, 'getContacts'])
        ->name('contacts');
    
    // Get Instagram message stats via API
    Route::get('/stats', [InstagramServiceController::class, 'getMessageStats'])
        ->name('stats');
});

/*
|--------------------------------------------------------------------------
| Admin Routes for Instagram Management
|--------------------------------------------------------------------------
| These routes are for system administrators to manage Instagram settings
*/

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth', 'system.admin'],
    'as' => 'admin.'
], function () {
    
    // Instagram system configuration
    Route::get('/instagram/system-config', function() {
        return view('admin.instagram.system-config');
    })->name('instagram.system_config');
    
    // Instagram webhook logs
    Route::get('/instagram/webhook-logs', function() {
        return view('admin.instagram.webhook-logs');
    })->name('instagram.webhook_logs');
});

/*
|--------------------------------------------------------------------------
| Development/Testing Routes (Remove in production)
|--------------------------------------------------------------------------
| These routes are for development and testing purposes
*/

if (app()->environment(['local', 'staging'])) {
    Route::group([
        'prefix' => 'dev/instagram',
        'middleware' => ['auth'],
        'as' => 'dev.instagram.'
    ], function () {
        
        // Test Instagram webhook simulation
        Route::post('/simulate-webhook/{vendorUid}', function($vendorUid) {
            $sampleWebhookData = [
                'object' => 'instagram',
                'entry' => [
                    [
                        'id' => 'PAGE_ID',
                        'time' => time(),
                        'messaging' => [
                            [
                                'sender' => ['id' => 'USER_ID'],
                                'recipient' => ['id' => 'PAGE_ID'],
                                'timestamp' => time() * 1000,
                                'message' => [
                                    'mid' => 'test_message_id_' . uniqid(),
                                    'text' => 'Test message from development',
                                    'timestamp' => time() * 1000
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            
            $controller = app(InstagramWebhookController::class);
            $request = request();
            $request->merge($sampleWebhookData);
            
            return $controller->handle($request, $vendorUid);
        })->name('simulate_webhook');
        
        // Test Instagram API calls
        Route::get('/test-api/{vendorUid}', function($vendorUid) {
            // Set vendor context
            $vendor = \App\Yantrana\Components\Vendor\Models\VendorModel::where('_uid', $vendorUid)->first();
            session(['vendor_id' => $vendor->_id]);
            
            $instagramService = app(\App\Yantrana\Components\InstagramService\Services\InstagramApiService::class);
            
            return response()->json([
                'configured' => $instagramService->isConfigured(),
                'connection_test' => $instagramService->testConnection(),
                'page_info' => $instagramService->getPageInfo()
            ]);
        })->name('test_api');
    });
}
