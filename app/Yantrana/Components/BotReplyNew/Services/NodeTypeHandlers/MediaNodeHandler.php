<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

/**
 * Handler for media type nodes
 */
class MediaNodeHandler extends BaseNodeHandler
{
    /**
     * Process the media node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'] ?? [];
        $data = $node['__data'] ?? [];
        $mediaMessage = $data['media_message'] ?? [];

        // Get media details
        $mediaLink = $mediaMessage['media_link'] ?? '';
        $headerType = $mediaMessage['header_type'] ?? '';
        $caption = $mediaMessage['caption'] ?? '';
        $fileName = $mediaMessage['file_name'] ?? '';

        // Process dynamic variables in caption
        $userResponses = $this->getUserResponses($context);
        $processedCaption = $this->processDynamicVariables($caption, $userResponses);

        $isTerminal = empty($payload['next_node']);

        // Format response for WhatsApp media message
        return [
            'type' => 'media',
            'media_message_data' => [
                'media_link' => $mediaLink,
                'header_type' => $headerType,
                'caption' => $processedCaption,
                'file_name' => $fileName
            ],
            'requires_input' => false,
            'next_node' => $payload['next_node'] ?? null,
            'node_id' => $node['id'],
            'is_terminal' => $isTerminal
        ];
    }

    /**
     * Validate media node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['media_message']['media_link'])) {
            $errors[] = 'Media file is required';
        }

        if (empty($payload['media_message']['header_type'])) {
            $errors[] = 'Media type is required';
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
     * Get node type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'media';
    }
}
