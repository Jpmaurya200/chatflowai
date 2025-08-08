<?php

/**
 * Facebook Chat Routes
 *
 * This file contains routes for Facebook chat functionality
 *-----------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Route;
use App\Yantrana\Components\FacebookChatService\Controllers\FacebookChatServiceController;

/*
|--------------------------------------------------------------------------
| Facebook Chat Service Routes (Authenticated)
|--------------------------------------------------------------------------
| These routes handle Facebook chat functionality and require authentication
*/

Route::group([
    'prefix' => 'vendor',
    'middleware' => ['vendor.authenticate'],
    'as' => 'vendor.'
], function () {

    // Test Facebook API connection
    Route::post('/facebook-chat/test-connection', [FacebookChatServiceController::class, 'testConnection'])
        ->name('facebook_chat.test_connection');

    // Get Facebook page information
    Route::get('/facebook-chat/page-info', [FacebookChatServiceController::class, 'getPageInfo'])
        ->name('facebook_chat.page_info');

    // Get Facebook conversations
    Route::get('/facebook-chat/conversations', [FacebookChatServiceController::class, 'getConversations'])
        ->name('facebook_chat.conversations');

    // Get Facebook conversation messages
    Route::get('/facebook-chat/conversation/{conversationId}/messages', [FacebookChatServiceController::class, 'getConversationMessages'])
        ->name('facebook_chat.conversation.messages');

    // Send message to conversation
    Route::post('/facebook-chat/conversation/{conversationId}/send', [FacebookChatServiceController::class, 'sendConversationMessage'])
        ->name('facebook_chat.conversation.send');

    // Get Facebook chat statistics
    Route::get('/facebook-chat/stats', [FacebookChatServiceController::class, 'getChatStats'])
        ->name('facebook_chat.stats');

    // Check Facebook chat configuration
    Route::get('/facebook-chat/configuration', [FacebookChatServiceController::class, 'checkConfiguration'])
        ->name('facebook_chat.configuration');

});

/*
|--------------------------------------------------------------------------
| Facebook Chat API Routes (API Authentication)
|--------------------------------------------------------------------------
| These routes are for API access to Facebook chat functionality
*/

Route::group([
    'prefix' => 'api/v1/facebook-chat',
    'middleware' => ['api.vendor.authenticate'],
    'as' => 'api.facebook_chat.'
], function () {
    
    // Get Facebook conversations via API
    Route::get('/conversations', [FacebookChatServiceController::class, 'getConversations'])
        ->name('conversations');
    
    // Get Facebook conversation messages via API
    Route::get('/conversation/{conversationId}/messages', [FacebookChatServiceController::class, 'getConversationMessages'])
        ->name('conversation.messages');
    
    // Send Facebook message via API
    Route::post('/conversation/{conversationId}/send', [FacebookChatServiceController::class, 'sendConversationMessage'])
        ->name('conversation.send');
    
    // Get Facebook chat stats via API
    Route::get('/stats', [FacebookChatServiceController::class, 'getChatStats'])
        ->name('stats');

    // Test Facebook connection via API
    Route::post('/test-connection', [FacebookChatServiceController::class, 'testConnection'])
        ->name('test_connection');

    // Check Facebook configuration via API
    Route::get('/configuration', [FacebookChatServiceController::class, 'checkConfiguration'])
        ->name('configuration');
});
