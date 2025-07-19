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
        // Only check for start trigger if the flow has one set
        if ($botFlow->start_trigger) {
            $isFlowStartTrigger = $this->isFlowStartTrigger($messageBody, $botReply, $botFlow);
        } else {
            Log::info('Flow has no start_trigger set, skipping flow processing', [
                'user' => $contact->wa_id,
                'flow_id' => $botFlow->_uid,
                'message' => $messageBody
            ]);
        }

        Log::info('Flow trigger analysis', [
            'user' => $contact->wa_id,
            'is_start_trigger' => $isFlowStartTrigger,
            'message' => $messageBody,
            'reply_trigger' => $botReply->reply_trigger,
            'flow_start_trigger' => $botFlow->start_trigger ?? 'none',
            'has_active_flow' => !is_null($activeFlow),
            'waiting_for_input' => $activeFlow ? ($activeFlow->__data['waiting_for_input'] ?? false) : false,
            'all_button_texts' => $this->getAllButtonTextsFromFlow($flowData)
        ]);

        // If this is a flow start trigger, reset any existing flow and start fresh
        if ($isFlowStartTrigger) {
            Log::info('Starting new flow from trigger', [
                'user' => $contact->wa_id,
                'trigger' => $messageBody
            ]);

            // Clear any existing active flow
            if ($activeFlow) {
                UserActiveFlow::where('phone_number', $contact->wa_id)->delete();
                $activeFlow = null;
            }

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
        }
        // Check if this is user input for an active flow
        elseif ($activeFlow && isset($activeFlow->__data['waiting_for_input']) && $activeFlow->__data['waiting_for_input']) {
            Log::info('Processing user input for active flow', [
                'user' => $contact->wa_id,
                'input' => $messageBody,
                'current_node' => $activeFlow->__data['current_node_id'] ?? 'unknown'
            ]);

            // This is user input - find the next node based on the input
            $currentNodeId = $activeFlow->__data['current_node_id'];
            $nextNodeId = $this->findNodeByUserInput($flowData, $messageBody, $activeFlow);

            if ($nextNodeId) {
                Log::info('Found next node for user input, executing flow', [
                    'user' => $contact->wa_id,
                    'current_node' => $currentNodeId,
                    'next_node' => $nextNodeId,
                    'user_input' => $messageBody
                ]);

                // Execute flow starting from the next node
                $result = $this->flowExecutionService->executeFlow(
                    $flowData,
                    $nextNodeId,
                    $context
                );

                // Mark that we processed user input
                $result['input_processed'] = $messageBody;
                $result['previous_node_id'] = $currentNodeId;
            } else {
                Log::warning('No next node found for user input', [
                    'user' => $contact->wa_id,
                    'current_node' => $currentNodeId,
                    'input' => $messageBody,
                    'available_buttons' => $this->getAvailableButtons($flowData, $currentNodeId)
                ]);
                return null;
            }
        } else {
            // If we reach here, it means:
            // 1. Not a flow start trigger
            // 2. No active flow waiting for input
            // Therefore, we should NOT process this message as a flow

            Log::info('Message does not qualify for flow processing', [
                'user' => $contact->wa_id,
                'message' => $messageBody,
                'reason' => 'Not a start trigger and no active flow waiting for input',
                'flow_start_trigger' => $botFlow->start_trigger ?? 'not_set'
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
        $context = [
            'contact' => [
                'id' => $contact->_id,
                'uid' => $contact->_uid,
                'wa_id' => $contact->wa_id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
            ],
            'vendor_id' => $contact->vendors__id, // Add vendor ID to context
            'user_responses' => []
        ];

        if ($activeFlow && isset($activeFlow->__data['flow_context'])) {
            $context = array_merge($context, $activeFlow->__data['flow_context']);
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

        // If we have an active flow and waiting for input, find the next node based on user selection
        if ($activeFlow && isset($activeFlow->__data['current_node_id']) && isset($activeFlow->__data['waiting_for_input'])) {
            $currentNodeId = $activeFlow->__data['current_node_id'];
            $currentNode = $this->flowNodeService->findNodeById($flowData, $currentNodeId);

            if ($currentNode) {
                // Handle interactive nodes (buttons)
                if ($currentNode['type'] === 'interactive') {
                    // Find the button that matches the user's input
                    $buttons = $currentNode['payload']['buttons'] ?? [];

                    Log::info('Searching for button match', [
                        'user_input' => $messageBody,
                        'current_node' => $currentNodeId,
                        'available_buttons' => $buttons
                    ]);

                    foreach ($buttons as $button) {
                        // Match by title (case insensitive) or by button ID
                        $titleMatch = strtolower(trim($button['title'])) === strtolower(trim($messageBody));
                        $idMatch = (string)$button['id'] === (string)$messageBody;

                        if ($titleMatch || $idMatch) {
                            Log::info('Button match found', [
                                'matched_button' => $button,
                                'next_node' => $button['next_node'],
                                'match_type' => $titleMatch ? 'title' : 'id'
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
                // Handle question nodes
                elseif ($currentNode['type'] === 'question') {
                    Log::info('Processing user input for question node', [
                        'user_input' => $messageBody,
                        'current_node' => $currentNodeId,
                        'variable_name' => $currentNode['payload']['variable_name'] ?? 'unknown'
                    ]);

                    // For question nodes, any user input is valid and we proceed to the next node
                    $nextNodeId = $currentNode['payload']['next_node'] ?? null;

                    if ($nextNodeId) {
                        Log::info('Question answered, proceeding to next node', [
                            'user_input' => $messageBody,
                            'current_node' => $currentNodeId,
                            'next_node' => $nextNodeId
                        ]);
                        return $nextNodeId;
                    } else {
                        Log::warning('Question node has no next_node defined', [
                            'current_node' => $currentNodeId,
                            'user_input' => $messageBody
                        ]);
                    }
                }
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
        }

        // Update current node
        if (isset($result['current_node_id'])) {
            $activeFlowData['current_node_id'] = $result['current_node_id'];
        }

        // Set waiting for input flag
        $waitingForInput = false;
        if (isset($result['responses'])) {
            foreach ($result['responses'] as $response) {
                if ($response['requires_input'] ?? false) {
                    $waitingForInput = true;
                    break;
                }
            }
        }
        $activeFlowData['waiting_for_input'] = $waitingForInput;

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
        $responses = $result['responses'] ?? [];
        $formattedResponses = [];

        foreach ($responses as $response) {
            $formattedResponse = [
                'type' => $response['type'],
                'text' => $response['text'] ?? '',
                'node_id' => $response['node_id'] ?? null
            ];

            // Handle interactive responses (buttons)
            if ($response['type'] === 'interactive' && isset($response['buttons'])) {
                $formattedResponse['buttons'] = $response['buttons'];
                $formattedResponse['interaction_type'] = 'button';
            }

            // Handle question responses
            if ($response['type'] === 'question') {
                $formattedResponse['expects_input'] = true;
                $formattedResponse['variable_name'] = $response['variable_name'] ?? null;
            }

            // Handle media responses
            if ($response['type'] === 'media' && isset($response['media_message_data'])) {
                $formattedResponse['media_message_data'] = $response['media_message_data'];
                $formattedResponse['is_media'] = true;
            }

            $formattedResponses[] = $formattedResponse;
        }

        return [
            'responses' => $formattedResponses,
            'is_complete' => $result['is_complete'] ?? false,
            'current_node_id' => $result['current_node_id'] ?? null,
            'processed_by_new_flow' => true
        ];
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
     * @return bool
     */
    private function isFlowStartTrigger($messageBody, $botReply, $botFlow)
    {
        // ONLY check if message matches the flow start_trigger
        if ($botFlow->start_trigger && strtolower(trim($botFlow->start_trigger)) === strtolower(trim($messageBody))) {
            Log::info('Message matches flow start_trigger', [
                'message' => $messageBody,
                'start_trigger' => $botFlow->start_trigger,
                'flow_id' => $botFlow->_uid
            ]);
            return true;
        }

        Log::info('Message does not match flow start_trigger', [
            'message' => $messageBody,
            'start_trigger' => $botFlow->start_trigger ?? 'not_set',
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
