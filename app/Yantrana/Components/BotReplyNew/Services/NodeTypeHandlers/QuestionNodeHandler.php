<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use Illuminate\Support\Facades\Log;
use App\Yantrana\Components\Contact\Models\ContactModel;

/**
 * Handler for question type nodes
 */
class QuestionNodeHandler extends BaseNodeHandler
{
    /**
     * Process the question node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'] ?? [];

        // Get the actual reply text from the bot_replies table using node ID
        $text = $this->getBotReplyText($node['id'], $context);

        // If no reply text found in database, fallback to payload text
        if (empty($text)) {
            $text = $payload['text'] ?? '';
        }

        // Get question settings from database
        $questionData = $this->getQuestionData($node['id'], $context);
        $variableName = $questionData['variable_name'] ?? $payload['variable_name'] ?? null;

        // Process dynamic variables
        $userResponses = $this->getUserResponses($context);
        $processedText = $this->processDynamicVariables($text, $userResponses);

        Log::info('Processing question node', [
            'node_id' => $node['id'],
            'question' => $processedText,
            'variable_name' => $variableName ?? 'unknown'
        ]);

        return [
            'type' => 'question',
            'text' => $processedText,
            'requires_input' => true,
            'variable_name' => $variableName,
            'node_id' => $node['id'],
            'wait_for_input' => true, // Indicates this node waits for user input
            'auto_proceed' => true    // Indicates automatic progression after input
        ];
    }

    /**
     * Validate question node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];

        if (empty($payload['text'])) {
            $errors[] = 'Question text is required';
        }

        if (empty($payload['variable_name'])) {
            $errors[] = 'Variable name is required for question nodes';
        } elseif (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $payload['variable_name'])) {
            $errors[] = 'Variable name must be a valid identifier';
        }

        return $errors;
    }

    /**
     * Get the next node ID based on user input and conditional flows
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        $payload = $node['payload'] ?? [];

        // Get question data from database, fallback to payload
        $questionData = $this->getQuestionData($node['id']);

        // Merge database data with payload data (payload takes precedence for flow structure)
        $conditionalFlows = $payload['conditional_flows'] ?? $questionData['conditional_flows'] ?? [];
        $defaultNext = $payload['default_next_node'] ?? $questionData['default_next_node'] ?? $payload['next_node'] ?? null;

        // If no user input provided, return default next node
        if ($userInput === null) {
            return $defaultNext;
        }

        // Check conditional flows
        foreach ($conditionalFlows as $flow) {
            if ($this->evaluateCondition($userInput, $flow)) {
                Log::info('Question node condition matched', [
                    'node_id' => $node['id'],
                    'user_input' => $userInput,
                    'condition' => $flow,
                    'target_node' => $flow['target_node']
                ]);
                return $flow['target_node'];
            }
        }

        // No conditions matched, return default next node
        Log::info('Question node using default next node', [
            'node_id' => $node['id'],
            'user_input' => $userInput,
            'default_next' => $defaultNext,
            'conditional_flows_count' => count($conditionalFlows)
        ]);

        return $defaultNext;
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
        return 'question';
    }

    /**
     * Process user input for this question
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        $payload = $node['payload'] ?? [];

        // Get question settings from database, fallback to payload
        $questionData = $this->getQuestionData($node['id'], $context);
        $variableName = $payload['variable_name'] ?? $questionData['variable_name'] ?? null;

        if (!$variableName) {
            Log::error('No variable name specified for question node', [
                'node_id' => $node['id'],
                'payload' => $payload,
                'question_data' => $questionData
            ]);

            return [
                'error' => 'No variable name specified for question',
                'context' => $context,
                'next_node' => null
            ];
        }

        // Store the user's answer in context using base class method
        $context = $this->storeUserResponse($context, $variableName, $userInput);

        // Store in database if contact is available
        if (isset($context['contact']) && $context['contact'] instanceof ContactModel) {
            $this->storeUserResponseInDatabase($context['contact'], $variableName, $userInput);
        }

        Log::info('Stored user response for question', [
            'variable_name' => $variableName,
            'user_input' => $userInput,
            'contact_id' => $context['contact']->_id ?? 'unknown'
        ]);

        return [
            'context' => $context,
            'next_node' => $this->getNextNodeId($node, $userInput),
            'stored_variable' => $variableName,
            'stored_value' => $userInput,
            'processed_input' => $userInput
        ];
    }

    /**
     * Evaluate a condition against user input
     *
     * @param string $userInput
     * @param array $condition
     * @return bool
     */
    private function evaluateCondition($userInput, $condition)
    {
        $conditionType = $condition['condition_type'] ?? 'equals';
        $conditionValue = $condition['condition_value'] ?? '';

        // Normalize user input for comparison
        $normalizedInput = trim(strtolower($userInput));
        $normalizedValue = trim(strtolower($conditionValue));

        switch ($conditionType) {
            case 'equals':
                return $normalizedInput === $normalizedValue;

            case 'contains':
                return strpos($normalizedInput, $normalizedValue) !== false;

            case 'starts_with':
                return strpos($normalizedInput, $normalizedValue) === 0;

            case 'regex':
                try {
                    return preg_match('/' . $conditionValue . '/i', $userInput) === 1;
                } catch (\Exception $e) {
                    Log::warning('Invalid regex pattern in question condition', [
                        'pattern' => $conditionValue,
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }

            case 'number_range':
                if (!is_numeric($userInput)) {
                    return false;
                }

                $number = (float) $userInput;

                // Parse range like "1-10" or ">=5" or "<100"
                if (preg_match('/^(\d+(?:\.\d+)?)-(\d+(?:\.\d+)?)$/', $conditionValue, $matches)) {
                    $min = (float) $matches[1];
                    $max = (float) $matches[2];
                    return $number >= $min && $number <= $max;
                } elseif (preg_match('/^>=(\d+(?:\.\d+)?)$/', $conditionValue, $matches)) {
                    return $number >= (float) $matches[1];
                } elseif (preg_match('/^<=(\d+(?:\.\d+)?)$/', $conditionValue, $matches)) {
                    return $number <= (float) $matches[1];
                } elseif (preg_match('/^>(\d+(?:\.\d+)?)$/', $conditionValue, $matches)) {
                    return $number > (float) $matches[1];
                } elseif (preg_match('/^<(\d+(?:\.\d+)?)$/', $conditionValue, $matches)) {
                    return $number < (float) $matches[1];
                } elseif (is_numeric($conditionValue)) {
                    return $number == (float) $conditionValue;
                }

                return false;

            default:
                Log::warning('Unknown condition type in question node', [
                    'condition_type' => $conditionType,
                    'condition_value' => $conditionValue
                ]);
                return false;
        }
    }

    /**
     * Get question data from database
     *
     * @param string $nodeId
     * @param array $context
     * @return array
     */
    private function getQuestionData($nodeId, $context = [])
    {
        try {
            // Get vendor ID from context or fallback to global function
            $vendorId = $context['vendor_id'] ?? getVendorId();

            if (!$vendorId) {
                return [];
            }

            // The node ID corresponds to the _uid field in bot_replies table
            $botReply = $this->botReplyRepository->fetchIt([
                '_uid' => $nodeId,
                'vendors__id' => $vendorId
            ]);

            if (__isEmpty($botReply)) {
                return [];
            }

            return $botReply->__data['question_message'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get question data', [
                'node_id' => $nodeId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Store user response in database
     *
     * @param ContactModel $contact
     * @param string $variableName
     * @param string $userInput
     * @return void
     */
    private function storeUserResponseInDatabase($contact, $variableName, $userInput)
    {
        try {
            // Get existing custom fields or create new array
            $customFields = $contact->__data['custom_fields'] ?? [];

            // Store the response
            $customFields[$variableName] = $userInput;

            // Update contact
            $contact->__data = array_merge($contact->__data ?? [], [
                'custom_fields' => $customFields
            ]);

            $contact->save();

            Log::info('User response stored in contact custom fields', [
                'contact_id' => $contact->_id,
                'variable_name' => $variableName,
                'value' => $userInput
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store user response', [
                'contact_id' => $contact->_id,
                'variable_name' => $variableName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate node configuration
     *
     * @param array $node
     * @return array
     */
    public function validateNode($node)
    {
        $errors = [];
        $payload = $node['payload'] ?? [];

        if (empty($payload['text'])) {
            $errors[] = 'Question text is required';
        }

        if (empty($payload['variable_name'])) {
            $errors[] = 'Variable name is required';
        } elseif (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $payload['variable_name'])) {
            $errors[] = 'Variable name must be a valid identifier';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
