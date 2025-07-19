<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use App\Yantrana\Components\BotReply\Repositories\BotReplyRepository;

/**
 * Base class for all node type handlers
 */
abstract class BaseNodeHandler
{
    /**
     * @var BotReplyRepository
     */
    protected $botReplyRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->botReplyRepository = new BotReplyRepository();
    }
    /**
     * Process the node and return the response
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    abstract public function process($node, $context = []);

    /**
     * Validate node payload
     *
     * @param array $payload
     * @return array
     */
    abstract public function validatePayload($payload);

    /**
     * Get the next node ID based on user input
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    abstract public function getNextNodeId($node, $userInput = null);

    /**
     * Check if this node type requires user input
     *
     * @return bool
     */
    public function requiresUserInput()
    {
        return false;
    }

    /**
     * Get node type identifier
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Process dynamic variables in text
     *
     * @param string $text
     * @param array $variables
     * @return string
     */
    protected function processDynamicVariables($text, $variables = [])
    {
        if (empty($variables)) {
            return $text;
        }

        foreach ($variables as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }

        return $text;
    }

    /**
     * Store user response in context
     *
     * @param array $context
     * @param string $variableName
     * @param mixed $value
     * @return array
     */
    protected function storeUserResponse($context, $variableName, $value)
    {
        if (!isset($context['user_responses'])) {
            $context['user_responses'] = [];
        }

        $context['user_responses'][$variableName] = $value;
        return $context;
    }

    /**
     * Get stored user responses
     *
     * @param array $context
     * @return array
     */
    protected function getUserResponses($context)
    {
        return $context['user_responses'] ?? [];
    }

    /**
     * Get bot reply text for a node
     *
     * @param string $nodeId
     * @param array $context
     * @return string
     */
    protected function getBotReplyText($nodeId, $context = [])
    {
        try {
            // Get vendor ID from context or fallback to global function
            $vendorId = $context['vendor_id'] ?? getVendorId();

            if (!$vendorId) {
                \Log::warning('No vendor ID available for fetching bot reply text', [
                    'node_id' => $nodeId,
                    'context_keys' => array_keys($context)
                ]);
                return '';
            }

            // The node ID corresponds to the _uid field in bot_replies table
            $botReply = $this->botReplyRepository->fetchIt([
                '_uid' => $nodeId,
                'vendors__id' => $vendorId
            ]);

            if ($botReply && !empty($botReply->reply_text)) {
                \Log::info('Successfully fetched bot reply text', [
                    'node_id' => $nodeId,
                    'vendor_id' => $vendorId,
                    'reply_text_length' => strlen($botReply->reply_text)
                ]);
                return $botReply->reply_text;
            } else {
                \Log::info('No bot reply found or empty reply text', [
                    'node_id' => $nodeId,
                    'vendor_id' => $vendorId,
                    'bot_reply_found' => !empty($botReply),
                    'reply_text_empty' => empty($botReply->reply_text ?? '')
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't break the flow
            \Log::warning('Failed to fetch bot reply text for node: ' . $nodeId, [
                'error' => $e->getMessage(),
                'vendor_id' => $context['vendor_id'] ?? 'not_set'
            ]);
        }

        return '';
    }

    /**
     * Process user input for this node (default implementation)
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        return [
            'context' => $context,
            'next_node' => $this->getNextNodeId($node, $userInput),
            'processed_input' => $userInput
        ];
    }
}
