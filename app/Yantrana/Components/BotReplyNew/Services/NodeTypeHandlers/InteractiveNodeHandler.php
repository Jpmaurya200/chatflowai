<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

/**
 * Handler for interactive type nodes (with buttons)
 */
class InteractiveNodeHandler extends BaseNodeHandler
{
    /**
     * Process the interactive node
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

        $buttons = $payload['buttons'] ?? [];

        // Process dynamic variables
        $userResponses = $this->getUserResponses($context);
        $processedText = $this->processDynamicVariables($text, $userResponses);

        // Process button titles for dynamic variables
        $processedButtons = [];
        foreach ($buttons as $button) {
            $processedButtons[] = [
                'id' => $button['id'],
                'title' => $this->processDynamicVariables($button['title'], $userResponses),
                'next_node' => $button['next_node'] ?? null
            ];
        }

        return [
            'type' => 'interactive',
            'text' => $processedText,
            'buttons' => $processedButtons,
            'requires_input' => true,
            'node_id' => $node['id']
        ];
    }

    /**
     * Validate interactive node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['text'])) {
            $errors[] = 'Interactive text is required';
        }
        
        if (empty($payload['buttons']) || !is_array($payload['buttons'])) {
            $errors[] = 'Buttons array is required for interactive nodes';
        } else {
            foreach ($payload['buttons'] as $index => $button) {
                if (empty($button['id'])) {
                    $errors[] = "Button $index: ID is required";
                }
                if (empty($button['title'])) {
                    $errors[] = "Button $index: Title is required";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Get the next node ID based on button selection
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        if (!$userInput) {
            return null;
        }

        $buttons = $node['payload']['buttons'] ?? [];

        // Find button by ID or title (case insensitive)
        foreach ($buttons as $button) {
            $titleMatch = strtolower(trim($button['title'])) === strtolower(trim($userInput));
            $idMatch = (string)$button['id'] === (string)$userInput;

            if ($titleMatch || $idMatch) {
                return $button['next_node'] ?? null;
            }
        }

        return null;
    }

    /**
     * Check if this node type requires user input
     *
     * @return bool
     */
    public function requiresUserInput()
    {
        return true;
    }

    /**
     * Get node type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'interactive';
    }

    /**
     * Process user input for this interactive node
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        $buttons = $node['payload']['buttons'] ?? [];
        $selectedButton = null;

        // Find the selected button (case insensitive matching)
        foreach ($buttons as $button) {
            $titleMatch = strtolower(trim($button['title'])) === strtolower(trim($userInput));
            $idMatch = (string)$button['id'] === (string)$userInput;

            if ($titleMatch || $idMatch) {
                $selectedButton = $button;
                break;
            }
        }

        if (!$selectedButton) {
            return [
                'error' => 'Invalid button selection: ' . $userInput,
                'context' => $context,
                'next_node' => null,
                'available_options' => array_column($buttons, 'title')
            ];
        }

        return [
            'context' => $context,
            'next_node' => $selectedButton['next_node'] ?? null,
            'selected_button' => $selectedButton,
            'processed_input' => $userInput
        ];
    }

    /**
     * Get available button options for user
     *
     * @param array $node
     * @return array
     */
    public function getButtonOptions($node)
    {
        $buttons = $node['payload']['buttons'] ?? [];
        $options = [];
        
        foreach ($buttons as $button) {
            $options[] = [
                'id' => $button['id'],
                'title' => $button['title'],
                'value' => $button['id'] // Use ID as the value to send back
            ];
        }
        
        return $options;
    }
}
