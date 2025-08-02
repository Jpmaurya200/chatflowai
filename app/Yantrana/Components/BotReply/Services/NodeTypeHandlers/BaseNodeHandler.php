<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use App\Yantrana\Components\BotReply\Repositories\BotReplyRepository;
use Illuminate\Support\Facades\Log;

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
            // Support both {variable} and {{variable}} formats
            $text = str_replace("{{$key}}", $value, $text);  // Replace {{variable}}
            $text = str_replace("{$key}", $value, $text);    // Replace {variable}
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
     * Get contact variables for replacement
     *
     * @param array $context
     * @return array
     */
    protected function getContactVariables($context)
    {
        $variables = [];
        
        if (isset($context['contact'])) {
            $contact = $context['contact'];
            
            // Handle both object and array formats
            if (is_object($contact)) {
                $variables['first_name'] = $contact->first_name ?? '';
                $variables['last_name'] = $contact->last_name ?? '';
                $variables['full_name'] = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
                $variables['phone_number'] = $contact->wa_id ?? '';
                $variables['email'] = $contact->email ?? '';
                $variables['country'] = $contact->country->name ?? '';
                $variables['language_code'] = $contact->language_code ?? '';
            } elseif (is_array($contact)) {
                $variables['first_name'] = $contact['first_name'] ?? '';
                $variables['last_name'] = $contact['last_name'] ?? '';
                $variables['full_name'] = trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''));
                $variables['phone_number'] = $contact['wa_id'] ?? '';
                $variables['email'] = $contact['email'] ?? '';
                $variables['country'] = $contact['country']['name'] ?? $contact['country'] ?? '';
                $variables['language_code'] = $contact['language_code'] ?? '';
            }
        }
        
        return $variables;
    }

    /**
     * Get all variables for replacement (contact + user responses)
     *
     * @param array $context
     * @return array
     */
    protected function getAllVariables($context)
    {
        $contactVariables = $this->getContactVariables($context);
        $userResponses = $this->getUserResponses($context);
        
        // Merge contact variables and user responses
        // User responses take precedence over contact variables
        return array_merge($contactVariables, $userResponses);
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
                Log::warning('No vendor ID available for fetching bot reply text', [
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
                Log::info('Successfully fetched bot reply text', [
                    'node_id' => $nodeId,
                    'vendor_id' => $vendorId,
                    'reply_text_length' => strlen($botReply->reply_text)
                ]);
                return $botReply->reply_text;
            } else {
                Log::info('No bot reply found or empty reply text', [
                    'node_id' => $nodeId,
                    'vendor_id' => $vendorId,
                    'bot_reply_found' => !empty($botReply),
                    'reply_text_empty' => empty($botReply->reply_text ?? '')
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't break the flow
            Log::warning('Failed to fetch bot reply text for node: ' . $nodeId, [
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

    /**
     * Get next node ID for standard WhatsApp flow outputs
     *
     * @param array $node
     * @param string $outputType - 'no_input', 'no_match', or 'delivery_failed'
     * @param array $context
     * @return string|null
     */
    protected function getStandardOutputNextNode($node, $outputType, $context = [])
    {
        // For now, return null as the flow connections are handled through the node-based structure
        // This method is kept for backward compatibility but doesn't use BotFlowConnectionModel
        Log::info('Standard output connection requested', [
            'node_id' => $node['id'] ?? 'unknown',
            'output_type' => $outputType,
            'note' => 'Using node-based flow structure, no separate connections table needed'
        ]);

        return null;
    }

    /**
     * Handle standard WhatsApp flow scenarios
     *
     * @param array $node
     * @param string|null $userInput
     * @param array $context
     * @return array|null Returns array with next_node if standard scenario applies, null otherwise
     */
    protected function handleStandardFlowScenarios($node, $userInput, $context = [])
    {
        // Handle no input scenario
        if ($userInput === null || trim($userInput) === '') {
            $nextNode = $this->getStandardOutputNextNode($node, 'no_input', $context);
            if ($nextNode) {
                return [
                    'context' => $context,
                    'next_node' => $nextNode,
                    'scenario' => 'no_input',
                    'processed_input' => $userInput
                ];
            }
        }

        // Handle delivery failed scenario (this would be set by external systems)
        if (isset($context['delivery_failed']) && $context['delivery_failed']) {
            $nextNode = $this->getStandardOutputNextNode($node, 'delivery_failed', $context);
            if ($nextNode) {
                return [
                    'context' => $context,
                    'next_node' => $nextNode,
                    'scenario' => 'delivery_failed',
                    'processed_input' => $userInput
                ];
            }
        }

        return null;
    }

    /**
     * Handle no match scenario for nodes that expect specific input
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array|null
     */
    protected function handleNoMatchScenario($node, $userInput, $context = [])
    {
        $nextNode = $this->getStandardOutputNextNode($node, 'no_match', $context);
        if ($nextNode) {
            return [
                'context' => $context,
                'next_node' => $nextNode,
                'scenario' => 'no_match',
                'processed_input' => $userInput
            ];
        }

        return null;
    }
}