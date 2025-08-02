<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

/**
 * Handler for message type nodes
 */
class MessageNodeHandler extends BaseNodeHandler
{
    /**
     * Process the message node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'];

        // Get the actual reply text from the bot_replies table using node ID
        $text = $this->getBotReplyText($node['id'], $context);

        // If no reply text found in database, fallback to payload text
        if (empty($text)) {
            $text = $payload['text'] ?? '';
        }

        // Process dynamic variables including contact variables
        $variables = $this->getAllVariables($context);
        $processedText = $this->processDynamicVariables($text, $variables);

        $isTerminal = empty($payload['next_node']);

        return [
            'type' => 'message',
            'text' => $processedText,
            'requires_input' => false,
            'next_node' => $payload['next_node'] ?? null,
            'node_id' => $node['id'],
            'is_terminal' => $isTerminal
        ];
    }

    /**
     * Validate message node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['text'])) {
            $errors[] = 'Message text is required';
        }
        
        return $errors;
    }

    /**
     * Get the next node ID
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        return $node['payload']['next_node'] ?? null;
    }

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
    public function getType()
    {
        return 'message';
    }

    /**
     * Check if this is a terminal node (end of flow)
     *
     * @param array $node
     * @return bool
     */
    public function isTerminal($node)
    {
        return empty($node['payload']['next_node']);
    }
}