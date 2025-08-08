<?php

/**
 * FacebookChatServiceController.php - Controller file for Facebook chat
 *
 * This file is part of the Facebook Chat Service component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\FacebookChatService\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\FacebookChatService\FacebookChatServiceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookChatServiceController extends BaseController
{
    /**
     * @var FacebookChatServiceEngine
     */
    protected $facebookChatServiceEngine;

    /**
     * Constructor
     */
    public function __construct(FacebookChatServiceEngine $facebookChatServiceEngine)
    {
        $this->facebookChatServiceEngine = $facebookChatServiceEngine;
    }

    /**
     * Test Facebook API connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            $result = $this->facebookChatServiceEngine->testConnection();

            if ($result->success()) {
                $data = $result->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['page_info'] ?? null,
                    'message' => 'Facebook API connection successful'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing Facebook connection: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Facebook page information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPageInfo()
    {
        try {
            $result = $this->facebookChatServiceEngine->getPageInfo();

            if ($result->success()) {
                $data = $result->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['page_info'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving page info: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Facebook conversations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversations()
    {
        try {
            $conversationsResult = $this->facebookChatServiceEngine->getConversations();

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
            $messagesResult = $this->facebookChatServiceEngine->getConversationMessages($conversationId);

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
     * Send message to a Facebook conversation
     *
     * @param Request $request
     * @param string $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendConversationMessage(Request $request, $conversationId)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000'
            ]);

            $message = $request->input('message');
            
            $result = $this->facebookChatServiceEngine->sendConversationMessage($conversationId, $message);

            if ($result->success()) {
                $data = $result->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['message_data'] ?? null,
                    'message' => 'Message sent successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result->message()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Facebook chat statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChatStats()
    {
        try {
            $conversationsResult = $this->facebookChatServiceEngine->getConversations();
            
            $stats = [
                'total_conversations' => 0,
                'unread_count' => 0,
                'enabled' => true
            ];

            if ($conversationsResult->success()) {
                $data = $conversationsResult->data();
                $stats['total_conversations'] = count($data['conversations'] ?? []);
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving chat stats: ' . $e->getMessage(),
                'data' => [
                    'total_conversations' => 0,
                    'unread_count' => 0,
                    'enabled' => false
                ]
            ]);
        }
    }

    /**
     * Get Facebook posts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPosts()
    {
        try {
            $postsResult = $this->facebookChatServiceEngine->getPosts();

            if ($postsResult->success()) {
                $data = $postsResult->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['posts'] ?? [],
                    'paging' => $data['paging'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $postsResult->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving posts: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get comments for a specific post
     *
     * @param string $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostComments($postId)
    {
        try {
            $commentsResult = $this->facebookChatServiceEngine->getPostComments($postId);

            if ($commentsResult->success()) {
                $data = $commentsResult->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['comments'] ?? [],
                    'paging' => $data['paging'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $commentsResult->message()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving comments: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reply to a Facebook comment privately (via DM)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function replyToComment(Request $request)
    {
        try {
            $request->validate([
                'comment_id' => 'required|string',
                'message' => 'required|string|max:2000'
            ]);

            $commentId = $request->input('comment_id');
            $message = $request->input('message');

            $result = $this->facebookChatServiceEngine->replyToComment($commentId, $message);

            if ($result->success()) {
                $data = $result->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['reply_data'] ?? null,
                    'reply_type' => $data['reply_type'] ?? 'private',
                    'message' => 'Private reply sent successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result->message()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending private reply: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reply to a Facebook comment publicly (under the post)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function replyToCommentPublicly(Request $request)
    {
        try {
            $request->validate([
                'comment_id' => 'required|string',
                'message' => 'required|string|max:2000'
            ]);

            $commentId = $request->input('comment_id');
            $message = $request->input('message');

            $result = $this->facebookChatServiceEngine->replyToCommentPublicly($commentId, $message);

            if ($result->success()) {
                $data = $result->data();
                return response()->json([
                    'success' => true,
                    'data' => $data['reply_data'] ?? null,
                    'reply_type' => $data['reply_type'] ?? 'public',
                    'message' => 'Public reply posted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result->message()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error posting public reply: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check if Facebook chat service is configured
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkConfiguration()
    {
        try {
            $isConfigured = $this->facebookChatServiceEngine->isConfigured();

            return response()->json([
                'success' => true,
                'data' => [
                    'configured' => $isConfigured,
                    'enabled' => getVendorSettings('enable_facebook_messaging', false)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking configuration: ' . $e->getMessage(),
                'data' => [
                    'configured' => false,
                    'enabled' => false
                ]
            ]);
        }
    }
}
