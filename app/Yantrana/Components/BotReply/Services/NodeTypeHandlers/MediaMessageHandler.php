<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

/**
 * Handler for media message type nodes
 */
class MediaMessageHandler extends BaseNodeHandler
{
    /**
     * Supported media types
     */
    const SUPPORTED_MEDIA_TYPES = [
        'image',
        'video',
        'document',
        'audio'
    ];

    /**
     * Handle media message node for flow integration
     *
     * @param array $nodeData
     * @param object $contact
     * @param string $userMessage
     * @param string $flowId
     * @param array $activeFlow
     * @return array|null
     */
    public function handle($nodeData, $contact, $userMessage, $flowId, $activeFlow)
    {
        \Log::info('Processing media message node', [
            'node_id' => $nodeData['id'] ?? 'unknown',
            'user' => $contact->wa_id,
            'flow_id' => $flowId
        ]);

        // Prepare context for processing
        $context = [
            'vendor_id' => getVendorId(),
            'contact' => $contact,
            'flow_id' => $flowId,
            'active_flow' => $activeFlow
        ];

        // Use the existing process method
        $result = $this->process($nodeData, $context);

        if (!$result) {
            \Log::error('Failed to process media message node', [
                'node_id' => $nodeData['id'] ?? 'unknown',
                'flow_id' => $flowId
            ]);
            return null;
        }

        \Log::info('Media message response prepared', [
            'node_id' => $nodeData['id'],
            'media_type' => $result['media_type'],
            'media_url' => $result['media_url'],
            'caption' => $result['caption'],
            'filename' => $result['filename']
        ]);

        return $result;
    }

    /**
     * Process the media message node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'];

        // Get the actual reply data from the bot_replies table using node ID
        $botReplyData = $this->getBotReplyData($node['id'], $context);

        // Extract and validate media information
        $mediaData = $this->extractMediaData($payload, $botReplyData);
        
        if (!$mediaData['is_valid']) {
            \Log::error('Invalid media data for node', [
                'node_id' => $node['id'],
                'errors' => $mediaData['errors']
            ]);
            return null;
        }

        // Process dynamic variables in caption including contact variables
        $variables = $this->getAllVariables($context);
        $processedCaption = $this->processDynamicVariables($mediaData['caption'], $variables);

        $isTerminal = empty($payload['next_node']);

        return [
            'type' => 'media_message',
            'media_type' => $mediaData['media_type'],
            'media_url' => $mediaData['media_url'],
            'caption' => $processedCaption,
            'filename' => $mediaData['filename'],
            'requires_input' => false,
            'next_node' => $payload['next_node'] ?? null,
            'node_id' => $node['id'],
            'is_terminal' => $isTerminal
        ];
    }

    /**
     * Extract and validate media data from payload and bot reply data
     *
     * @param array $payload
     * @param array $botReplyData
     * @return array
     */
    /**
     * Detect media type from file extension
     *
     * @param string $url
     * @return string|null
     */
    protected function detectMediaTypeFromExtension($url)
    {
        $extension = strtolower($this->getMediaExtension($url) ?? '');
        
        $extensionTypes = [
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', '3gp'],
            'audio' => ['mp3', 'wav', 'aac', 'ogg', 'm4a']
        ];

        foreach ($extensionTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return null;
    }

    protected function extractMediaData($payload, $botReplyData)
    {
        \Log::debug('Extracting media data', [
            'has_payload' => !empty($payload),
            'has_bot_reply_data' => !empty($botReplyData)
        ]);

        $result = [
            'is_valid' => false,
            'errors' => [],
            'media_type' => null,
            'media_url' => null,
            'caption' => '',
            'filename' => ''
        ];

        // Try to get media URL from all possible sources
        $mediaUrl = null;
        $urlFields = ['media_url', 'media_link', 'link', 'url'];
        
        // First check payload
        foreach ($urlFields as $field) {
            if (!empty($payload[$field]) && $this->validateMediaUrl($payload[$field])) {
                $mediaUrl = $payload[$field];
                \Log::info('Found valid media URL in payload', ['field' => $field, 'url' => $mediaUrl]);
                break;
            }
        }

        // Then check botReplyData if URL not found in payload
        if (empty($mediaUrl)) {
            foreach ($urlFields as $field) {
                if (!empty($botReplyData[$field]) && $this->validateMediaUrl($botReplyData[$field])) {
                    $mediaUrl = $botReplyData[$field];
                    \Log::info('Found valid media URL in botReplyData', ['field' => $field, 'url' => $mediaUrl]);
                    break;
                }
            }
        }

        // Try to get media type from various sources, prioritizing header_type from botReplyData
        $mediaType = null;
        
        // First check header_type from botReplyData (most reliable)
        if (!empty($botReplyData['header_type'])) {
            $mediaType = $botReplyData['header_type'];
            \Log::info('Using media type from botReplyData header_type', ['type' => $mediaType]);
        } 
        // Then check media_type from botReplyData
        else if (!empty($botReplyData['media_type'])) {
            $mediaType = $botReplyData['media_type'];
            \Log::info('Using media type from botReplyData media_type', ['type' => $mediaType]);
        }
        // Then try payload media_type (less reliable, may be default)
        else if (!empty($payload['media_type'])) {
            $mediaType = $payload['media_type'];
            \Log::info('Using media type from payload', ['type' => $mediaType]);
        }
        // Finally default to image
        else {
            $mediaType = 'image';
            \Log::info('Using default media type', ['type' => $mediaType]);
        }

        // Override type to document if URL has document extension
        if (!empty($mediaUrl)) {
            $extension = strtolower($this->getMediaExtension($mediaUrl) ?? '');
            if (in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx'])) {
                $mediaType = 'document';
                \Log::info('Overriding to document type based on extension', ['extension' => $extension]);
            }
        }

        // If still no media type, try to detect from URL using comprehensive method
        if (empty($mediaType) && !empty($mediaUrl)) {
            $mediaType = $this->detectMediaTypeFromExtension($mediaUrl);
            if ($mediaType) {
                \Log::info('Detected media type from file extension', [
                    'url' => $mediaUrl,
                    'detected_type' => $mediaType
                ]);
            }
        }

        // If still no media type, check filename as last resort
        if (empty($mediaType)) {
            $filename = $payload['filename'] ?? $botReplyData['file_name'] ?? '';
            if (!empty($filename)) {
                $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $detectedType = $this->detectMediaTypeFromExtension($filename);
                if ($detectedType) {
                    $mediaType = $detectedType;
                    \Log::info('Detected media type from filename', ['filename' => $filename, 'type' => $mediaType]);
                }
            }
        }

        // Validate media type if found
        if (!empty($mediaType) && !in_array($mediaType, self::SUPPORTED_MEDIA_TYPES)) {
            $result['errors'][] = "Unsupported media type: $mediaType";
            return $result;
        }

        if (empty($mediaUrl)) {
            \Log::warning('No media URL found in any field', [
                'payload_fields' => array_keys($payload),
                'bot_reply_fields' => array_keys($botReplyData)
            ]);
            $result['errors'][] = "Missing media URL";
            return $result;
        }

        if (!$this->validateMediaUrl($mediaUrl)) {
            \Log::warning('Media URL validation failed', [
                'url' => $mediaUrl,
                'media_type' => $mediaType
            ]);
            $result['errors'][] = "Invalid media URL";
            return $result;
        }

        // Get caption from various possible fields
        $caption = $payload['caption'] ?? 
                  $payload['body'] ?? 
                  $payload['text'] ?? 
                  $botReplyData['caption'] ?? 
                  $botReplyData['body'] ?? 
                  $botReplyData['text'] ?? 
                  '';

        // Get filename from various possible fields
        $filename = $payload['filename'] ?? 
                   $payload['file_name'] ?? 
                   $botReplyData['filename'] ?? 
                   $botReplyData['file_name'] ?? 
                   $this->generateFilename($mediaUrl, $mediaType);

        $result = [
            'is_valid' => true,
            'errors' => [],
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
            'caption' => $caption,
            'filename' => $filename
        ];

        \Log::info('Successfully extracted media data', $result);
        return $result;
    }

    /**
     * Generate a filename based on media URL and type
     *
     * @param string $url
     * @param string $mediaType
     * @return string
     */
    protected function generateFilename($url, $mediaType)
    {
        $extension = $this->getMediaExtension($url) ?? $this->getDefaultExtension($mediaType);
        return 'media_' . time() . '.' . $extension;
    }

    /**
     * Get default extension for media type
     *
     * @param string $mediaType
     * @return string
     */
    protected function getDefaultExtension($mediaType)
    {
        $extensions = [
            'image' => 'jpg',
            'video' => 'mp4',
            'audio' => 'mp3',
            'document' => 'pdf'
        ];
        
        return $extensions[$mediaType] ?? 'bin';
    }

    /**
     * Validate media message node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        // Check if media type is provided and supported
        if (empty($payload['media_type'])) {
            $errors[] = 'Media type is required';
        } elseif (!in_array($payload['media_type'], self::SUPPORTED_MEDIA_TYPES)) {
            $errors[] = 'Unsupported media type. Supported types: ' . implode(', ', self::SUPPORTED_MEDIA_TYPES);
        }

        // Check if media URL is provided
        if (empty($payload['media_url'])) {
            $errors[] = 'Media URL is required';
        } elseif (!filter_var($payload['media_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid media URL format';
        }

        // Validate filename for document type
        if (($payload['media_type'] ?? '') === 'document' && empty($payload['filename'])) {
            $errors[] = 'Filename is required for document media type';
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
        return 'media_message';
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

    /**
     * Get bot reply data including media information for a node
     *
     * @param string $nodeId
     * @param array $context
     * @return array
     */
    protected function getBotReplyData($nodeId, $context = [])
    {
        try {
            // Get vendor ID from context or fallback to global function
            $vendorId = $context['vendor_id'] ?? getVendorId();

            if (!$vendorId) {
                \Log::warning('No vendor ID available for fetching bot reply data', [
                    'node_id' => $nodeId,
                    'context_keys' => array_keys($context)
                ]);
                return [];
            }

            // The node ID corresponds to the _uid field in bot_replies table
            $botReply = $this->botReplyRepository->fetchIt([
                '_uid' => $nodeId,
                'vendors__id' => $vendorId
            ]);

            if (!$botReply) {
                \Log::info('No bot reply found for media node', [
                    'node_id' => $nodeId,
                    'vendor_id' => $vendorId
                ]);
                return [];
            }

            $data = [];
            
            // First try to get media data from the payload in __data
            if (isset($botReply->__data['payload']) && is_array($botReply->__data['payload'])) {
                $payload = $botReply->__data['payload'];
                $data = $this->extractMediaDataFromPayload($payload);
                if ($data['is_valid']) {
                    \Log::info('Found valid media data in payload', [
                        'node_id' => $nodeId,
                        'media_type' => $data['media_type'],
                        'source' => 'payload'
                    ]);
                    return $data;
                }
            }

            // Then try media_message structure
            if (isset($botReply->__data['media_message']) && is_array($botReply->__data['media_message'])) {
                $mediaMessageData = $botReply->__data['media_message'];
                $data = $this->extractMediaDataFromMediaMessage($mediaMessageData);
                if ($data['is_valid']) {
                    \Log::info('Found valid media data in media_message', [
                        'node_id' => $nodeId,
                        'media_type' => $data['media_type'],
                        'source' => 'media_message'
                    ]);
                    return $data;
                }
            }

            // Finally try direct properties
            $data = $this->extractMediaDataFromProperties($botReply);
            if ($data['is_valid']) {
                \Log::info('Found valid media data in direct properties', [
                    'node_id' => $nodeId,
                    'media_type' => $data['media_type'],
                    'source' => 'direct_properties'
                ]);
                return $data;
            }

            \Log::warning('No valid media data found in any location', [
                'node_id' => $nodeId,
                'vendor_id' => $vendorId,
                'has_payload' => isset($botReply->__data['payload']),
                'has_media_message' => isset($botReply->__data['media_message']),
                'has_direct_properties' => !empty($botReply->media_url)
            ]);

            return $data;

        } catch (\Exception $e) {
            \Log::error('Error fetching bot reply media data', [
                'node_id' => $nodeId,
                'error' => $e->getMessage(),
                'vendor_id' => $context['vendor_id'] ?? 'not_set',
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'is_valid' => false,
                'errors' => ['Internal error processing media data']
            ];
        }
    }

    /**
     * Extract media data from payload structure
     *
     * @param array $payload
     * @return array
     */
    protected function extractMediaDataFromPayload($payload)
    {
        $mediaType = $payload['media_type'] ?? null;
        $mediaUrl = $payload['media_url'] ?? null;
        $caption = $payload['caption'] ?? '';
        $filename = $payload['filename'] ?? '';

        if (!$mediaType || !$mediaUrl) {
            return [
                'is_valid' => false,
                'errors' => ['Missing required media data in payload']
            ];
        }

        if (!$this->validateMediaUrl($mediaUrl)) {
            return [
                'is_valid' => false,
                'errors' => ['Invalid media URL in payload']
            ];
        }

        return [
            'is_valid' => true,
            'errors' => [],
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
            'caption' => $caption,
            'filename' => $filename
        ];
    }

    /**
     * Extract media data from media_message structure
     *
     * @param array $mediaMessageData
     * @return array
     */
    protected function extractMediaDataFromMediaMessage($mediaMessageData)
    {
        \Log::debug('Extracting media data from media_message', [
            'data' => $mediaMessageData
        ]);

        // Get media type, strictly preserving header_type if present
        $mediaType = null;
        if (!empty($mediaMessageData['header_type'])) {
            $mediaType = $mediaMessageData['header_type'];
            \Log::info('Using media type from header_type', ['type' => $mediaType]);
        } else if (!empty($mediaMessageData['type'])) {
            $mediaType = $mediaMessageData['type'];
            \Log::info('Using media type from type field', ['type' => $mediaType]);
        } else {
            $mediaType = 'image';  // Default fallback
            \Log::info('Using default media type', ['type' => $mediaType]);
        }

        // Check all possible media URL fields
        $mediaUrl = null;
        $urlFields = ['media_url', 'media_link', 'link', 'url'];
        foreach ($urlFields as $field) {
            if (!empty($mediaMessageData[$field])) {
                $mediaUrl = $mediaMessageData[$field];
                \Log::info('Found media URL in field', ['field' => $field, 'url' => $mediaUrl]);
                break;
            }
        }

        $caption = $mediaMessageData['caption'] ?? 
                  $mediaMessageData['body'] ?? 
                  $mediaMessageData['text'] ?? 
                  '';

        $filename = $mediaMessageData['file_name'] ?? 
                   $mediaMessageData['filename'] ?? 
                   '';

        if (empty($mediaUrl)) {
            \Log::warning('No media URL found in any field', [
                'available_fields' => array_keys($mediaMessageData)
            ]);
            return [
                'is_valid' => false,
                'errors' => ['Missing media URL in media_message']
            ];
        }

        if (!$this->validateMediaUrl($mediaUrl)) {
            \Log::warning('Media URL validation failed', [
                'url' => $mediaUrl,
                'media_type' => $mediaType
            ]);
            return [
                'is_valid' => false,
                'errors' => ['Invalid media URL in media_message']
            ];
        }

        $result = [
            'is_valid' => true,
            'errors' => [],
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
            'caption' => $caption,
            'filename' => $filename
        ];

        \Log::info('Successfully extracted media data', $result);
        return $result;
    }

    /**
     * Extract media data from direct properties
     *
     * @param object $botReply
     * @return array
     */
    protected function extractMediaDataFromProperties($botReply)
    {
        \Log::debug('Extracting media data from properties', [
            'media_type' => $botReply->media_type ?? 'none',
            'has_media_url' => !empty($botReply->media_url),
            'has_reply_text' => !empty($botReply->reply_text)
        ]);

        // Try to get media URL from various properties
        $mediaUrl = $botReply->media_url ?? 
                   $botReply->media_link ?? 
                   $botReply->link ?? 
                   null;

        if (empty($mediaUrl) && !empty($botReply->__data)) {
            // Try to find URL in __data
            $data = is_array($botReply->__data) ? $botReply->__data : json_decode($botReply->__data, true);
            if (is_array($data)) {
                foreach (['media_url', 'media_link', 'link', 'url'] as $field) {
                    if (!empty($data[$field])) {
                        $mediaUrl = $data[$field];
                        \Log::info('Found media URL in __data', ['field' => $field]);
                        break;
                    }
                }
            }
        }

        $mediaType = $botReply->media_type ?? 'image';
        $caption = $botReply->reply_text ?? 
                  $botReply->caption ?? 
                  '';
        $filename = $botReply->filename ?? 
                   $botReply->file_name ?? 
                   '';

        if (empty($mediaUrl)) {
            \Log::warning('No media URL found in properties', [
                'available_properties' => get_object_vars($botReply)
            ]);
            return [
                'is_valid' => false,
                'errors' => ['Missing media URL in properties']
            ];
        }

        if (!$this->validateMediaUrl($mediaUrl)) {
            \Log::warning('Media URL validation failed for properties', [
                'url' => $mediaUrl,
                'media_type' => $mediaType
            ]);
            return [
                'is_valid' => false,
                'errors' => ['Invalid media URL in properties']
            ];
        }

        $result = [
            'is_valid' => true,
            'errors' => [],
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
            'caption' => $caption,
            'filename' => $filename
        ];

        \Log::info('Successfully extracted media data from properties', $result);
        return $result;
    }

    /**
     * Get supported media types
     *
     * @return array
     */
    public static function getSupportedMediaTypes()
    {
        return self::SUPPORTED_MEDIA_TYPES;
    }

    /**
     * Validate media URL accessibility and format
     *
     * @param string $url
     * @return bool
     */
    public function validateMediaUrl($url)
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            \Log::warning('Invalid URL format', ['url' => $url]);
            return false;
        }

        // Check URL scheme (must be http or https)
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'])) {
            \Log::warning('Invalid URL scheme', ['url' => $url, 'scheme' => $scheme]);
            return false;
        }

        // Check for common media file extensions (optional - many URLs don't have extensions)
        $mediaExtensions = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', '3gp'],
            'audio' => ['mp3', 'wav', 'aac', 'ogg', 'm4a'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']
        ];

        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        
        // If there's an extension, validate it
        if (!empty($extension)) {
            $isValidExtension = false;
            foreach ($mediaExtensions as $type => $extensions) {
                if (in_array($extension, $extensions)) {
                    $isValidExtension = true;
                    break;
                }
            }

            if (!$isValidExtension) {
                \Log::warning('URL has invalid media extension', [
                    'url' => $url,
                    'extension' => $extension
                ]);
                return false;
            }
        } else {
            // No extension found - this is common for dynamic URLs, so we'll allow it
            \Log::info('URL has no file extension - allowing as it may be a dynamic URL', [
                'url' => $url
            ]);
        }

        // Check if URL domain is accessible (basic check)
        $urlDomain = parse_url($url, PHP_URL_HOST);
        if (empty($urlDomain)) {
            \Log::warning('URL has no valid domain', ['url' => $url]);
            return false;
        }

        // Log successful validation
        \Log::info('Media URL validation passed', [
            'url' => $url,
            'domain' => $urlDomain,
            'extension' => $extension ?: 'none'
        ]);

        return true;
    }

    /**
     * Get media file extension from URL
     *
     * @param string $url
     * @return string|null
     */
    public function getMediaExtension($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        return $path ? pathinfo($path, PATHINFO_EXTENSION) : null;
    }

    /**
     * Validate media type against file extension
     *
     * @param string $mediaType
     * @param string $url
     * @return bool
     */
    public function validateMediaTypeExtension($mediaType, $url)
    {
        $extension = strtolower($this->getMediaExtension($url) ?? '');
        
        $validExtensions = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', '3gp'],
            'audio' => ['mp3', 'wav', 'aac', 'ogg', 'm4a'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt']
        ];

        return isset($validExtensions[$mediaType]) && 
               in_array($extension, $validExtensions[$mediaType]);
    }
}