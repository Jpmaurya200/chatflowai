<?php

namespace App\Yantrana\Components\BotReply\Services;

use App\Yantrana\Components\BotReply\Services\FlowExecutionService;
use App\Yantrana\Components\BotReply\Services\FlowNodeService;
use App\Yantrana\Components\BotReply\Models\BotFlowModel;
use App\Models\UserActiveFlow;
use Illuminate\Support\Facades\Log;

/**
 * Service to integrate new node-based flows with existing WhatsApp flow system
 */
class FlowIntegrationService
{
    /**
     * @var FlowExecutionService
     */
    private $flowExecutionService;

    /**
     * @var FlowNodeService
     */
    private $flowNodeService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->flowExecutionService = new FlowExecutionService();
        $this->flowNodeService = new FlowNodeService();
    }

    /**
     * Process bot flow using new node-based system if available
     *
     * @param object $contact
     * @param string $messageBody
     * @param object $botReply
     * @param array $options
     * @return array|null
     */
    public function processNodeBasedFlow($contact, $messageBody, $botReply, $options = [])
    {
        // Check if this bot reply belongs to a flow
        if (!$botReply->bot_flows__id) {
            return null;
        }

        $botFlow = BotFlowModel::find($botReply->bot_flows__id);
        if (!$botFlow) {
            return null;
        }

        // Prioritize new flow structure, fallback to legacy if needed
        $flowData = $botFlow->getFlowNodesData();
        if (!$flowData && $botFlow->usesLegacyFlowStructure()) {
            // Convert legacy to new format on-the-fly for processing
            $legacyData = $botFlow->getFlowBuilderData();
            if ($legacyData) {
                $botFlowEngine = app(\App\Yantrana\Components\BotReply\BotFlowEngine::class);
                $flowData = $botFlowEngine->convertToNewFlowStructure($legacyData, $botFlow->_uid);

                Log::info('Using converted legacy flow data', [
                    'flow_id' => $botFlow->_uid,
                    'node_count' => count($flowData['nodes'] ?? [])
                ]);
            }
        }

        if (!$flowData || empty($flowData['nodes'])) {
            Log::warning('No valid flow data found', [
                'flow_id' => $botFlow->_uid,
                'has_legacy' => $botFlow->usesLegacyFlowStructure(),
                'has_new' => $botFlow->usesNewFlowStructure()
            ]);
            return null;
        }

        Log::info('Processing node-based flow', [
            'user' => $contact->wa_id,
            'flow_id' => $flowData['flow_id'],
            'message' => $messageBody,
            'trigger' => $botReply->reply_trigger,
            'node_count' => count($flowData['nodes'] ?? []),
            'first_node_id' => $flowData['nodes'][0]['id'] ?? 'none',
            'bot_reply_id' => $botReply->_uid
        ]);

        // Get or create active flow context
        $activeFlow = UserActiveFlow::getActiveFlow($contact->wa_id);
        $context = $this->getFlowContext($activeFlow, $contact);

        // Check if this is a flow start trigger (always check, even if there's an active flow)
        $isFlowStartTrigger = false;
        $triggerType = $botFlow->trigger_type ?? 'is';

        // For welcome and new_message triggers, we don't need a start_trigger set
        if (in_array($triggerType, ['welcome', 'new_message'])) {
            $isFlowStartTrigger = $this->isFlowStartTrigger($messageBody, $botReply, $botFlow, $contact);
        } elseif ($botFlow->start_trigger) {
            // For other trigger types, check the start_trigger
            $isFlowStartTrigger = $this->isFlowStartTrigger($messageBody, $botReply, $botFlow, $contact);
        } else {
            Log::info('Flow has no start_trigger set and is not a special trigger type', [
                'user' => $contact->wa_id,
                'flow_id' => $botFlow->_uid,
                'trigger_type' => $triggerType,
                'message' => $messageBody
            ]);
        }

        // Check if we have an active flow waiting for input
        $isWaitingForInput = $activeFlow && isset($activeFlow->__data['waiting_for_input']) && $activeFlow->__data['waiting_for_input'];

        Log::info('Flow trigger analysis', [
            'user' => $contact->wa_id,
            'is_start_trigger' => $isFlowStartTrigger,
            'message' => $messageBody,
            'reply_trigger' => $botReply->reply_trigger,
            'flow_start_trigger' => $botFlow->start_trigger ?? 'none',
            'trigger_type' => $triggerType,
            'has_active_flow' => !is_null($activeFlow),
            'waiting_for_input' => $isWaitingForInput,
            'active_flow_id' => $activeFlow ? $activeFlow->flow_id : null,
            'current_flow_id' => $botReply->bot_flows__id,
            'all_button_texts' => $this->getAllButtonTextsFromFlow($flowData)
        ]);

        // PRIORITY 1: If this is a flow start trigger, restart the flow
        // Start triggers should always restart the flow, even if user is waiting for input
        // This allows users to restart a flow by sending the trigger again
        if ($isFlowStartTrigger) {
            // Clear existing flow (whether same or different)
            if ($activeFlow) {
                if ($activeFlow->flow_id == $botReply->bot_flows__id) {
                    Log::info('Restarting flow from start trigger (same flow, resetting)', [
                        'user' => $contact->wa_id,
                        'trigger' => $messageBody,
                        'flow_id' => $activeFlow->flow_id,
                        'was_waiting_for_input' => $isWaitingForInput
                    ]);
                } else {
                    Log::info('Starting new flow from trigger (different flow)', [
                        'user' => $contact->wa_id,
                        'trigger' => $messageBody,
                        'old_flow_id' => $activeFlow->flow_id,
                        'new_flow_id' => $botReply->bot_flows__id
                    ]);
                }
                // Clear existing flow
                UserActiveFlow::where('phone_number', $contact->wa_id)->delete();
                $activeFlow = null;
            } else {
                Log::info('Starting new flow from trigger (no active flow)', [
                    'user' => $contact->wa_id,
                    'trigger' => $messageBody,
                    'flow_id' => $botReply->bot_flows__id
                ]);
            }

            // Refresh context after clearing active flow (to get fresh context)
            $context = $this->getFlowContext(null, $contact);

            // Create new active flow
            $activeFlow = UserActiveFlow::setActiveFlow(
                $contact->_id,
                $botReply->bot_flows__id,
                $contact->wa_id
            );

            // Find the first node in the flow
            $firstNode = $this->flowExecutionService->getFirstNode($flowData);
            if (!$firstNode) {
                Log::error('No first node found in flow', [
                    'user' => $contact->wa_id,
                    'flow_id' => $flowData['flow_id']
                ]);
                return null;
            }

            $currentNodeId = $firstNode['id'];

            // Initialize flow context for new node structure
            $activeFlowData = $activeFlow->__data ?? [];
            $activeFlowData['current_node_id'] = $currentNodeId;
            $activeFlowData['flow_context'] = $context;
            $activeFlowData['waiting_for_input'] = false;
            $activeFlow->__data = $activeFlowData;
            $activeFlow->save();

            // Start flow execution from first node
            $result = $this->flowExecutionService->executeFlow(
                $flowData,
                $currentNodeId,
                $context
            );
            
            Log::info('Flow restarted and executed', [
                'user' => $contact->wa_id,
                'flow_id' => $botReply->bot_flows__id,
                'first_node_id' => $currentNodeId,
                'response_count' => count($result['responses'] ?? []),
                'has_responses' => !empty($result['responses']),
                'response_types' => array_column($result['responses'] ?? [], 'type'),
                'is_complete' => $result['is_complete'] ?? false
            ]);
        }
        // PRIORITY 2: Check if this is user input for an active flow waiting for input
        // Only process as input if it's NOT a start trigger (already handled above)
        elseif ($isWaitingForInput && $activeFlow && $activeFlow->flow_id == $botReply->bot_flows__id) {
            Log::info('Processing user input for active flow', [
                'user' => $contact->wa_id,
                'input' => $messageBody,
                'current_node' => $activeFlow->__data['current_node_id'] ?? 'unknown'
            ]);

            // This is user input - use the FlowExecutionService to continue the flow with input
            $currentNodeId = $activeFlow->__data['current_node_id'];
            
            // Get updated context from active flow
            $context = $this->getFlowContext($activeFlow, $contact);
            
            Log::info('About to continue flow with user input', [
                'user' => $contact->wa_id,
                'current_node' => $currentNodeId,
                'input' => $messageBody,
                'context_keys' => array_keys($context),
                'flow_id' => $flowData['flow_id'] ?? 'unknown'
            ]);

            // Use continueFlowWithInput to process input and continue execution
            $result = $this->flowExecutionService->continueFlowWithInput(
                $flowData,
                $currentNodeId,
                $messageBody,
                $context
            );

            Log::info('Flow continued after user input', [
                'user' => $contact->wa_id,
                'current_node' => $currentNodeId,
                'next_node' => $result['current_node_id'] ?? 'none',
                'response_count' => count($result['responses'] ?? []),
                'has_error' => isset($result['error']),
                'is_complete' => $result['is_complete'] ?? false
            ]);

            // Check for error - but only return null if there are no responses
            // (Interactive nodes may return responses even on error to re-send the message)
            if (isset($result['error']) && empty($result['responses'])) {
                Log::error('Error processing user input with no responses', [
                    'user' => $contact->wa_id,
                    'current_node' => $currentNodeId,
                    'input' => $messageBody,
                    'error' => $result['error']
                ]);
                return null;
            }
            
            // If there's an error but responses exist (e.g., re-sending interactive message), log it but continue
            if (isset($result['error']) && !empty($result['responses'])) {
                Log::warning('Error processing user input, but responses available', [
                    'user' => $contact->wa_id,
                    'current_node' => $currentNodeId,
                    'input' => $messageBody,
                    'error' => $result['error'],
                    'response_count' => count($result['responses'])
                ]);
            }

            Log::info('Successfully processed user input and continued flow', [
                'user' => $contact->wa_id,
                'current_node' => $currentNodeId,
                'input' => $messageBody,
                'new_current_node' => $result['current_node_id'],
                'response_count' => count($result['responses'] ?? []),
                'is_complete' => $result['is_complete'] ?? false
            ]);
        } else {
            // If we reach here, it means:
            // 1. Not a flow start trigger
            // 2. Not waiting for input in active flow (or different flow)
            // Therefore, we should NOT process this message as a flow
            Log::info('Message does not qualify for flow processing', [
                'user' => $contact->wa_id,
                'message' => $messageBody,
                'reason' => 'Not a start trigger and not waiting for input',
                'flow_start_trigger' => $botFlow->start_trigger ?? 'not_set',
                'has_active_flow' => !is_null($activeFlow),
                'waiting_for_input' => $isWaitingForInput
            ]);

            return null;
        }

        // Update active flow state
        $this->updateActiveFlowState($activeFlow, $result, $contact);

        // Check if flow is complete and clean up
        if ($result['is_complete'] ?? false) {
            Log::info('Flow completed, cleaning up session', [
                'user' => $contact->wa_id,
                'flow_id' => $flowData['flow_id'],
                'final_node' => $result['current_node_id'],
                'response_count' => count($result['responses'] ?? [])
            ]);

            // Delete the active flow session
            $deletedCount = UserActiveFlow::where('phone_number', $contact->wa_id)->delete();

            Log::info('Active flow session cleaned up', [
                'user' => $contact->wa_id,
                'deleted_sessions' => $deletedCount
            ]);
        } else {
            Log::info('Flow continuing, session maintained', [
                'user' => $contact->wa_id,
                'current_node' => $result['current_node_id'],
                'waiting_for_input' => !empty($result['responses']) && ($result['responses'][0]['requires_input'] ?? false)
            ]);
        }

        return $this->formatFlowResult($result, $contact, $options);
    }

    /**
     * Get flow context from active flow and contact
     *
     * @param object|null $activeFlow
     * @param object $contact
     * @return array
     */
    private function getFlowContext($activeFlow, $contact)
    {
        // Start with basic context
        $context = [
            'contact' => $contact,
            'contact_id' => $contact->_id,
            'wa_id' => $contact->wa_id,
            'vendor_id' => $contact->vendors__id,
            'timestamp' => time(),
            'debug_info' => []
        ];

        // Add existing context from active flow if available
        if ($activeFlow && isset($activeFlow->__data['flow_context'])) {
            $existingContext = $activeFlow->__data['flow_context'];
            $context = array_merge($existingContext, $context);
            
            // Log context retrieval for debugging
            Log::info('Retrieved existing flow context', [
                'user' => $contact->wa_id,
                'flow_id' => $activeFlow->flow_id,
                'context_keys' => array_keys($context),
                'has_stored_variables' => !empty(array_diff(array_keys($context), ['contact', 'contact_id', 'wa_id', 'vendor_id', 'timestamp', 'debug_info'])),
                'has_active_interactive_nodes' => isset($context['active_interactive_nodes']),
                'active_nodes_count' => count($context['active_interactive_nodes'] ?? [])
            ]);
        }

        return $context;
    }

    /**
     * Determine current node ID based on flow state
     *
     * @param object|null $activeFlow
     * @param array $flowData
     * @param string $messageBody
     * @param object $botReply
     * @return string|null
     */
    private function getCurrentNodeId($activeFlow, $flowData, $messageBody, $botReply)
    {
        // If there's an active flow with current node, use it
        if ($activeFlow && isset($activeFlow->__data['current_node_id'])) {
            return $activeFlow->__data['current_node_id'];
        }

        // If this is a flow start, find the first node
        $firstNode = $this->flowExecutionService->getFirstNode($flowData);
        return $firstNode ? $firstNode['id'] : null;
    }

    /**
     * Find the node that should be triggered by the user's message
     *
     * @param array $flowData
     * @param string $messageBody
     * @param object|null $activeFlow
     * @return string|null
     */
    private function findNodeByUserInput($flowData, $messageBody, $activeFlow)
    {
        $nodes = $flowData['nodes'] ?? [];
        $messageBody = trim($messageBody);

        // If we have an active flow and waiting for input, find the next node based on user selection
        if ($activeFlow && isset($activeFlow->__data['current_node_id']) && isset($activeFlow->__data['waiting_for_input'])) {
            $currentNodeId = $activeFlow->__data['current_node_id'];
            $currentNode = $this->flowNodeService->findNodeById($flowData, $currentNodeId);
            
            Log::info('Finding next node based on user input', [
                'current_node' => $currentNodeId,
                'user_input' => $messageBody,
                'node_type' => $currentNode['type'] ?? 'unknown'
            ]);

            // For interactive nodes with buttons
            if ($currentNode && $currentNode['type'] === 'interactive' && isset($currentNode['payload']['buttons'])) {
                $buttons = $currentNode['payload']['buttons'];
                
                Log::info('Checking button matches', [
                    'user_input' => $messageBody,
                    'available_buttons' => array_column($buttons, 'title'),
                    'button_count' => count($buttons)
                ]);
                
                foreach ($buttons as $button) {
                    // Match by title (case insensitive) or by button ID
                    $titleMatch = strtolower(trim($button['title'])) === strtolower(trim($messageBody));
                    $idMatch = (string)$button['id'] === (string)$messageBody;
                    
                    // Also check for partial matches or numeric selection
                    $numericMatch = false;
                    if (is_numeric($messageBody) && isset($buttons[intval($messageBody)-1])) {
                        $numericMatch = true;
                        $button = $buttons[intval($messageBody)-1];
                    }

                    if ($titleMatch || $idMatch || $numericMatch) {
                        Log::info('Button match found', [
                            'matched_button' => $button,
                            'next_node' => $button['next_node'],
                            'match_type' => $titleMatch ? 'title' : ($idMatch ? 'id' : 'numeric')
                        ]);
                        return $button['next_node'] ?? null;
                    }
                }

                Log::warning('No button match found', [
                    'user_input' => $messageBody,
                    'available_buttons' => array_column($buttons, 'title'),
                    'button_ids' => array_column($buttons, 'id'),
                    'current_node' => $currentNodeId
                ]);
            }
        }
        
        return null;
    }

    /**
     * Check if message is user input for current flow
     *
     * @param string $messageBody
     * @param object|null $activeFlow
     * @return bool
     */
    private function isUserInput($messageBody, $activeFlow)
    {
        return $activeFlow && 
               isset($activeFlow->__data['waiting_for_input']) && 
               $activeFlow->__data['waiting_for_input'] === true;
    }

    /**
     * Update active flow state with execution result
     *
     * @param object|null $activeFlow
     * @param array $result
     * @param object $contact
     * @return void
     */
    private function updateActiveFlowState($activeFlow, $result, $contact)
    {
        if (!$activeFlow) {
            return;
        }

        $activeFlowData = $activeFlow->__data ?? [];

        // Update context
        if (isset($result['context'])) {
            $activeFlowData['flow_context'] = $result['context'];

            // Log context update for debugging
            Log::info('Updating flow context in active flow', [
                'user' => $contact->wa_id,
                'context_keys' => array_keys($result['context']),
                'has_active_interactive_nodes' => isset($result['context']['active_interactive_nodes']),
                'active_nodes_count' => count($result['context']['active_interactive_nodes'] ?? [])
            ]);
        }

        // Update current node
        if (isset($result['current_node_id'])) {
            $activeFlowData['current_node_id'] = $result['current_node_id'];
        }

        // Set waiting for input flag
        $waitingForInput = false;
        if (isset($result['responses'])) {
            foreach ($result['responses'] as $response) {
                // Check for requires_input, expects_input, or wait_for_input flags
                if (($response['requires_input'] ?? false) || 
                    ($response['expects_input'] ?? false) || 
                    ($response['wait_for_input'] ?? false)) {
                    $waitingForInput = true;
                    break;
                }
                
                // Also check for question type responses
                if (($response['type'] ?? '') === 'question') {
                    $waitingForInput = true;
                    break;
                }
            }
        }
        $activeFlowData['waiting_for_input'] = $waitingForInput;

        // Log the state update for debugging
        Log::info('Updated active flow state', [
            'user' => $contact->wa_id,
            'current_node_id' => $activeFlowData['current_node_id'] ?? 'none',
            'waiting_for_input' => $waitingForInput,
            'response_count' => count($result['responses'] ?? []),
            'is_complete' => $result['is_complete'] ?? false
        ]);

        // If flow is complete, mark for deletion
        if ($result['is_complete'] ?? false) {
            $activeFlowData['completed'] = true;
            $activeFlowData['completed_at'] = now();
        }

        $activeFlow->__data = $activeFlowData;
        $activeFlow->save();
    }

    /**
     * Format flow execution result for WhatsApp response
     *
     * @param array $result
     * @param object $contact
     * @param array $options
     * @return array
     */
    private function formatFlowResult($result, $contact, $options = [])
    {
        if (!is_array($result)) {
            Log::warning('formatFlowResult: result is not an array', [
                'user' => $contact->wa_id ?? 'unknown',
                'result_type' => gettype($result)
            ]);
            return [
                'responses' => [],
                'is_complete' => true,
                'current_node_id' => null,
                'processed_by_new_flow' => true
            ];
        }

        $responses = $result['responses'] ?? [];
        $formattedResponses = [];
        
        Log::info('formatFlowResult: Formatting responses', [
            'user' => $contact->wa_id ?? 'unknown',
            'response_count' => count($responses),
            'is_complete' => $result['is_complete'] ?? false
        ]);

        foreach ($responses as $response) {
            if (!is_array($response) || !isset($response['type'])) {
                continue;
            }

            $formattedResponse = [
                'type' => $response['type'],
                'node_id' => $response['node_id'] ?? null
            ];

            // Handle different response types
            switch ($response['type']) {
                case 'message':
                    $formattedResponse['text'] = $response['text'] ?? '';
                    break;

                case 'media_message':
                    $formattedResponse['media_type'] = $response['media_type'] ?? 'image';
                    $formattedResponse['media_url'] = $response['media_url'] ?? '';
                    $formattedResponse['caption'] = $response['caption'] ?? '';
                    $formattedResponse['filename'] = $response['filename'] ?? '';
                    break;

                case 'interactive':
                    $formattedResponse['text'] = $response['text'] ?? '';
                    
                    // Handle list type interactive messages
                    if (isset($response['list_data'])) {
                        $formattedResponse['interaction_type'] = 'list';
                        $formattedResponse['list_data'] = [
                            'button_text' => $response['list_data']['button_text'] ?? 'Select an option',
                            'sections' => $response['list_data']['sections'] ?? []
                        ];
                        
                        // Add header/footer if present
                        if (!empty($response['header_text'])) {
                            $formattedResponse['header_text'] = $response['header_text'];
                        }
                        if (!empty($response['footer_text'])) {
                            $formattedResponse['footer_text'] = $response['footer_text'];
                        }
                    }
                    // Handle button type interactive messages
                    elseif (!empty($response['buttons'])) {
                        $formattedResponse['interaction_type'] = 'button';
                        $formattedResponse['buttons'] = $response['buttons'];
                        // Ensure body_text is set for button messages (used by WhatsAppServiceEngine)
                        $formattedResponse['body_text'] = $response['text'] ?? $response['body_text'] ?? '';
                    }
                    break;

                case 'question':
                    $formattedResponse['text'] = $response['text'] ?? '';
                    $formattedResponse['expects_input'] = true;
                    $formattedResponse['variable_name'] = $response['variable_name'] ?? null;
                    break;

                default:
                    // Fallback for unknown types
                    $formattedResponse['text'] = $response['text'] ?? '';
                    break;
            }

            $formattedResponses[] = $formattedResponse;
        }

        $formattedResult = [
            'responses' => $formattedResponses,
            'is_complete' => $result['is_complete'] ?? false,
            'current_node_id' => $result['current_node_id'] ?? null,
            'processed_by_new_flow' => true
        ];
        
        Log::info('formatFlowResult: Formatted result', [
            'user' => $contact->wa_id ?? 'unknown',
            'formatted_response_count' => count($formattedResponses),
            'response_types' => array_column($formattedResponses, 'type'),
            'has_interactive' => !empty(array_filter($formattedResponses, function($r) { return ($r['type'] ?? '') === 'interactive'; }))
        ]);

        return $formattedResult;
    }

    /**
     * Check if a bot flow uses the new node structure
     *
     * @param int $flowId
     * @return bool
     */
    public function usesNewFlowStructure($flowId)
    {
        $botFlow = BotFlowModel::find($flowId);
        return $botFlow && $botFlow->usesNewFlowStructure();
    }

    /**
     * Get flow data for a bot flow
     *
     * @param int $flowId
     * @return array|null
     */
    public function getFlowData($flowId)
    {
        $botFlow = BotFlowModel::find($flowId);
        return $botFlow ? $botFlow->getFlowNodesData() : null;
    }

    /**
     * Get available buttons for a node (for debugging)
     *
     * @param array $flowData
     * @param string $nodeId
     * @return array
     */
    private function getAvailableButtons($flowData, $nodeId)
    {
        $node = $this->flowNodeService->findNodeById($flowData, $nodeId);
        if ($node && $node['type'] === 'interactive') {
            return $node['payload']['buttons'] ?? [];
        }
        return [];
    }

    /**
     * Check if the message matches the flow start_trigger ONLY
     *
     * @param string $messageBody
     * @param object $botReply
     * @param object $botFlow
     * @param object $contact
     * @return bool
     */
    private function isFlowStartTrigger($messageBody, $botReply, $botFlow, $contact = null)
    {
        $triggerType = $botFlow->trigger_type ?? 'is';

        // Handle welcome trigger type
        if ($triggerType === 'welcome') {
            // Welcome triggers should fire for first-time contacts or when explicitly triggered
            // Check if this is a first message from this contact
            if ($contact) {
                // Check if this contact has any previous incoming messages
                $hasIncomingMessages = \App\Yantrana\Components\WhatsAppService\Models\WhatsAppMessageLogModel::where([
                    'contacts__id' => $contact->_id,
                    'is_incoming_message' => 1
                ])->exists();

                Log::info('Welcome trigger check', [
                    'user' => $contact->wa_id,
                    'contact_id' => $contact->_id,
                    'has_incoming_messages' => $hasIncomingMessages,
                    'flow_id' => $botFlow->_uid,
                    'will_trigger' => !$hasIncomingMessages
                ]);

                // Trigger welcome flow for first-time contacts
                if (!$hasIncomingMessages) {
                    return true;
                }
            }
            return false;
        }

        // Handle new_message trigger type
        if ($triggerType === 'new_message') {
            // New message triggers fire for any message when there's no active flow
            $activeFlow = \App\Models\UserActiveFlow::getActiveFlow($contact->wa_id ?? '');

            Log::info('New message trigger check', [
                'user' => $contact->wa_id,
                'has_active_flow' => !is_null($activeFlow),
                'active_flow_id' => $activeFlow ? $activeFlow->flow_id : null,
                'flow_id' => $botFlow->_uid,
                'will_trigger' => !$activeFlow
            ]);

            return !$activeFlow; // Only trigger if no active flow
        }

        if (!$botFlow->start_trigger) {
            return false;
        }

        // Split start_trigger by commas and trim each trigger word
        $triggerWords = array_map('trim', explode(',', $botFlow->start_trigger));
        $messageBody = strtolower(trim($messageBody));

        // Check if message matches any of the trigger words based on trigger type
        foreach ($triggerWords as $trigger) {
            $trigger = strtolower(trim($trigger));
            $isMatch = false;

            switch ($triggerType) {
                case 'is':
                    $isMatch = $trigger === $messageBody;
                    break;
                case 'starts_with':
                    $isMatch = str_starts_with($messageBody, $trigger);
                    break;
                case 'ends_with':
                    $isMatch = str_ends_with($messageBody, $trigger);
                    break;
                case 'contains_word':
                    // Match whole words only
                    $isMatch = preg_match('/\b' . preg_quote($trigger, '/') . '\b/i', $messageBody);
                    break;
                case 'contains':
                    $isMatch = str_contains($messageBody, $trigger);
                    break;
                case 'stop_promotional':
                    // Handle stop promotional logic if needed
                    $isMatch = $trigger === $messageBody;
                    break;
                default:
                    $isMatch = $trigger === $messageBody;
                    break;
            }

            if ($isMatch) {
                Log::info('Message matches flow start_trigger', [
                    'message' => $messageBody,
                    'matched_trigger' => $trigger,
                    'trigger_type' => $triggerType,
                    'all_triggers' => $triggerWords,
                    'flow_id' => $botFlow->_uid
                ]);
                return true;
            }
        }

        Log::info('Message does not match any flow start_trigger', [
            'message' => $messageBody,
            'trigger_type' => $triggerType,
            'available_triggers' => $triggerWords,
            'flow_id' => $botFlow->_uid
        ]);

        return false;
    }

    /**
     * Check if a message is a button text from any node in the flow
     *
     * @param string $messageBody
     * @param array $flowData
     * @return bool
     */
    private function isButtonTextFromFlow($messageBody, $flowData)
    {
        if (!$flowData || !isset($flowData['nodes'])) {
            return false;
        }

        foreach ($flowData['nodes'] as $node) {
            if ($node['type'] === 'interactive' && isset($node['payload']['buttons'])) {
                foreach ($node['payload']['buttons'] as $button) {
                    if (strtolower(trim($button['title'])) === strtolower(trim($messageBody))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get all button texts from the flow (for debugging)
     *
     * @param array $flowData
     * @return array
     */
    private function getAllButtonTextsFromFlow($flowData)
    {
        $buttonTexts = [];

        if (!$flowData || !isset($flowData['nodes'])) {
            return $buttonTexts;
        }

        foreach ($flowData['nodes'] as $node) {
            if ($node['type'] === 'interactive' && isset($node['payload']['buttons'])) {
                foreach ($node['payload']['buttons'] as $button) {
                    $buttonTexts[] = $button['title'];
                }
            }
        }

        return array_unique($buttonTexts);
    }
}
