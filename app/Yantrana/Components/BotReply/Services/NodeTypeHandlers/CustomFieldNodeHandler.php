<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use App\Yantrana\Components\Contact\Models\ContactModel;
use App\Yantrana\Components\Contact\Repositories\ContactCustomFieldRepository;
use Illuminate\Support\Facades\Log;

/**
 * Handler for custom field type nodes
 */
class CustomFieldNodeHandler extends BaseNodeHandler
{
    /**
     * @var ContactCustomFieldRepository
     */
    protected $contactCustomFieldRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->contactCustomFieldRepository = new ContactCustomFieldRepository();
    }

    /**
     * Process the custom field node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'];
        
        // Get the custom field data from the node
        $customFieldId = $payload['custom_field_id'] ?? null;
        $customFieldName = $payload['custom_field_name'] ?? null;
        $questionText = $payload['question_text'] ?? 'Please provide your information:';

        // Get the actual reply text from the bot_replies table using node ID
        $text = $this->getBotReplyText($node['id'], $context);

        // If no reply text found in database, fallback to question text
        if (empty($text)) {
            $text = $questionText;
        }

        // Process dynamic variables including contact variables
        $variables = $this->getAllVariables($context);
        $processedText = $this->processDynamicVariables($text, $variables);

        Log::info('Processing custom field node', [
            'node_id' => $node['id'],
            'custom_field_id' => $customFieldId,
            'custom_field_name' => $customFieldName,
            'question' => $processedText
        ]);

        return [
            'type' => 'custom_field',
            'text' => $processedText,
            'requires_input' => true,
            'custom_field_id' => $customFieldId,
            'custom_field_name' => $customFieldName,
            'node_id' => $node['id'],
            'expects_input' => true,
            'wait_for_input' => true,
            'auto_proceed' => true
        ];
    }

    /**
     * Validate custom field node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['custom_field_id'])) {
            $errors[] = 'Custom field selection is required';
        }
        
        if (empty($payload['question_text'])) {
            $errors[] = 'Question text is required';
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
        return true;
    }

    /**
     * Process user input for custom field node
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        $payload = $node['payload'];
        $customFieldId = $payload['custom_field_id'] ?? null;
        $customFieldName = $payload['custom_field_name'] ?? null;

        Log::info('Processing user input for custom field node', [
            'node_id' => $node['id'],
            'custom_field_id' => $customFieldId,
            'custom_field_name' => $customFieldName,
            'user_input' => $userInput,
            'contact_id' => $context['contact']->_id ?? 'unknown'
        ]);

        if (!$customFieldId || !$customFieldName) {
            Log::error('No custom field specified for custom field node', [
                'node_id' => $node['id'],
                'payload' => $payload
            ]);

            return [
                'error' => 'No custom field specified',
                'context' => $context,
                'next_node' => null
            ];
        }

        // Process input (simplified - just store as text)
        $processedInput = trim($userInput);

        // Store in database if contact is available
        if (isset($context['contact']) && $context['contact'] instanceof ContactModel) {
            $this->storeUserResponseInCustomField($context['contact'], $customFieldId, $processedInput);
        }

        // Get next node using flow connections
        $nextNode = $this->getNextNodeId($node, $processedInput);
        
        Log::info('Completed processing user input for custom field', [
            'node_id' => $node['id'],
            'custom_field_id' => $customFieldId,
            'custom_field_name' => $customFieldName,
            'raw_input' => $userInput,
            'processed_input' => $processedInput,
            'next_node' => $nextNode,
            'contact_id' => $context['contact']->_id ?? 'unknown'
        ]);

        return [
            'context' => $context,
            'next_node' => $nextNode,
            'stored_field_id' => $customFieldId,
            'stored_field_name' => $customFieldName,
            'stored_value' => $processedInput,
            'processed_input' => $processedInput
        ];
    }

    /**
     * Store user response in contact's custom field
     *
     * @param ContactModel $contact
     * @param string $customFieldId
     * @param string $userInput
     * @return void
     */
    private function storeUserResponseInCustomField($contact, $customFieldId, $userInput)
    {
        try {
            // Get the custom field to validate it exists
            $customField = $this->contactCustomFieldRepository->fetchIt([
                '_id' => $customFieldId,
                'vendors__id' => getVendorId()
            ]);

            if (!$customField) {
                Log::error('Custom field not found', [
                    'custom_field_id' => $customFieldId,
                    'vendor_id' => getVendorId()
                ]);
                return;
            }

            // Prepare data for storing custom field value
            $customFieldData = [
                'contacts__id' => $contact->_id,
                'contact_custom_fields__id' => $customFieldId,
                'field_value' => $userInput,
                'vendors__id' => getVendorId()
            ];

            // Store the custom field value
            $result = $this->contactCustomFieldRepository->storeCustomValues([$customFieldData]);

            Log::info('User response stored in custom field', [
                'contact_id' => $contact->_id,
                'custom_field_id' => $customFieldId,
                'custom_field_name' => $customField->input_name,
                'value' => $userInput,
                'result' => $result ? 'success' : 'failed'
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing user response in custom field', [
                'contact_id' => $contact->_id,
                'custom_field_id' => $customFieldId,
                'value' => $userInput,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get node type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'custom_field';
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
     * Get display information for custom field node
     *
     * @param array $node
     * @return array
     */
    public function getDisplayInfo($node)
    {
        $customFieldName = $node['payload']['custom_field_name'] ?? 'Custom Field';
        $questionText = $node['payload']['question_text'] ?? 'Please provide your information:';
        
        return [
            'title' => 'Custom Field',
            'description' => 'Collect: ' . $customFieldName,
            'question' => $questionText,
            'icon' => 'form',
            'color' => '#17a2b8',
        ];
    }
}