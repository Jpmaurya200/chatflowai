<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use Illuminate\Support\Facades\Log;

/**
 * Handler for stay in session type nodes
 * This node prevents the flow from ending and keeps all interactive nodes in the flow active
 */
class StayInSessionNodeHandler extends BaseNodeHandler
{
    /**
     * Process the stay in session node
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
            $text = $payload['text'] ?? 'Session will remain active...';
        }

        // Process dynamic variables including contact variables
        $variables = $this->getAllVariables($context);
        $processedText = $this->processDynamicVariables($text, $variables);

        Log::info('Processing stay in session node', [
            'node_id' => $node['id'],
            'text' => $processedText,
            'context_keys' => array_keys($context)
        ]);

        return [
            'type' => 'stay_in_session',
            'text' => $processedText,
            'requires_input' => true, // This keeps the session active and waiting for input
            'next_node' => null, // No next node - this prevents flow termination
            'node_id' => $node['id'],
            'is_terminal' => false, // Explicitly not terminal
            'stay_in_session' => true, // Flag to indicate this node keeps session active
            'session_message' => $processedText
        ];
    }

    /**
     * Validate stay in session node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        // Text is optional for stay in session nodes
        // If not provided, a default message will be used
        
        return $errors;
    }

    /**
     * Get the next node ID
     * For stay in session nodes, this always returns null to prevent flow termination
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        // Always return null to prevent flow from ending
        return null;
    }

    /**
     * Process user input for stay in session node
     * This method handles any user input while keeping the session active
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        Log::info('Processing user input for stay in session node', [
            'node_id' => $node['id'],
            'user_input' => $userInput,
            'context_keys' => array_keys($context)
        ]);

        // Store the user input in context for potential use by other parts of the system
        $context['stay_in_session_inputs'] = $context['stay_in_session_inputs'] ?? [];
        $context['stay_in_session_inputs'][] = [
            'input' => $userInput,
            'timestamp' => now()->toDateTimeString(),
            'node_id' => $node['id']
        ];

        // Check for special commands that might trigger other flows or actions
        $specialCommandResult = $this->handleSpecialCommands($userInput, $context);
        if ($specialCommandResult) {
            return $specialCommandResult;
        }

        // Check if the input matches any active interactive nodes in the session
        $interactiveNodeResult = $this->checkActiveInteractiveNodes($userInput, $context);
        if ($interactiveNodeResult) {
            return $interactiveNodeResult;
        }

        // Default behavior: acknowledge input but stay in session
        return [
            'context' => $context,
            'next_node' => null, // Stay at this node
            'processed_input' => $userInput,
            'stay_in_session' => true,
            'acknowledgment' => 'Input received. Session remains active.',
            'requires_input' => true // Continue waiting for more input
        ];
    }

    /**
     * Handle special commands that might be entered while in stay-in-session mode
     *
     * @param string $userInput
     * @param array $context
     * @return array|null
     */
    private function handleSpecialCommands($userInput, $context)
    {
        $input = strtolower(trim($userInput));

        // Handle common exit commands
        if (in_array($input, ['exit', 'quit', 'end', 'stop', 'bye', 'goodbye'])) {
            Log::info('Exit command received in stay in session node', [
                'command' => $input,
                'context_keys' => array_keys($context)
            ]);

            return [
                'context' => $context,
                'next_node' => null,
                'processed_input' => $userInput,
                'stay_in_session' => false, // Allow session to end
                'force_end_session' => true,
                'acknowledgment' => 'Session ended by user request.',
                'requires_input' => false
            ];
        }

        // Handle restart/reset commands
        if (in_array($input, ['restart', 'reset', 'start over', 'begin'])) {
            Log::info('Restart command received in stay in session node', [
                'command' => $input,
                'context_keys' => array_keys($context)
            ]);

            // Clear session context but keep basic flow info
            $cleanContext = [
                'phone_number' => $context['phone_number'] ?? null,
                'user_id' => $context['user_id'] ?? null,
                'flow_id' => $context['flow_id'] ?? null,
                'restarted_at' => now()->toDateTimeString()
            ];

            return [
                'context' => $cleanContext,
                'next_node' => null, // Could be modified to redirect to start node
                'processed_input' => $userInput,
                'stay_in_session' => true,
                'acknowledgment' => 'Session context reset. You can continue...',
                'requires_input' => true
            ];
        }

        return null;
    }

    /**
     * Check if user input matches any active interactive nodes in the session
     *
     * @param string $userInput
     * @param array $context
     * @return array|null
     */
    private function checkActiveInteractiveNodes($userInput, $context)
    {
        // Get active interactive nodes from context
        $activeInteractiveNodes = $context['active_interactive_nodes'] ?? [];

        Log::info('Checking active interactive nodes in stay_in_session', [
            'user_input' => $userInput,
            'active_nodes_count' => count($activeInteractiveNodes),
            'context_keys' => array_keys($context),
            'has_active_nodes' => !empty($activeInteractiveNodes)
        ]);

        if (empty($activeInteractiveNodes)) {
            Log::info('No active interactive nodes found in context');
            return null;
        }

        Log::info('Checking user input against active interactive nodes', [
            'user_input' => $userInput,
            'active_nodes_count' => count($activeInteractiveNodes)
        ]);

        foreach ($activeInteractiveNodes as $nodeData) {
            $node = $nodeData['node'] ?? null;
            if (!$node || $node['type'] !== 'interactive') {
                continue;
            }

            // Use the InteractiveNodeHandler to process the input
            $interactiveHandler = new InteractiveNodeHandler();

            try {
                $result = $interactiveHandler->processUserInput($node, $userInput, $context);

                // If the interactive node successfully processed the input
                if (!isset($result['error']) && isset($result['next_node'])) {
                    Log::info('User input matched interactive node', [
                        'node_id' => $node['id'],
                        'user_input' => $userInput,
                        'next_node' => $result['next_node']
                    ]);

                    // If the next node is a stay_in_session node, stay in session but acknowledge the action
                    if ($this->isStayInSessionNode($result['next_node'], $context)) {
                        return [
                            'context' => $result['context'] ?? $context,
                            'next_node' => null, // Stay at stay_in_session node
                            'processed_input' => $userInput,
                            'stay_in_session' => true,
                            'interactive_action_processed' => true,
                            'selected_option' => $result['selected_button'] ?? $result['selected_option'] ?? null,
                            'source_interactive_node' => $node['id'],
                            'acknowledgment' => 'Option selected. Session remains active.',
                            'requires_input' => true
                        ];
                    } else {
                        // If next node is not stay_in_session, proceed normally
                        return [
                            'context' => $result['context'] ?? $context,
                            'next_node' => $result['next_node'],
                            'processed_input' => $userInput,
                            'stay_in_session' => false,
                            'interactive_action_processed' => true,
                            'selected_option' => $result['selected_button'] ?? $result['selected_option'] ?? null,
                            'source_interactive_node' => $node['id'],
                            'requires_input' => false
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error processing interactive node input', [
                    'node_id' => $node['id'],
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        return null;
    }

    /**
     * Check if a node ID refers to a stay_in_session node
     *
     * @param string|null $nodeId
     * @param array $context
     * @return bool
     */
    private function isStayInSessionNode($nodeId, $context)
    {
        if (!$nodeId) {
            return false;
        }

        // Get flow data from context to check node type
        $flowData = $context['flow_data'] ?? null;
        if (!$flowData) {
            return false;
        }

        // Find the node in flow data
        foreach ($flowData['operators'] ?? [] as $operatorId => $operator) {
            if ($operatorId === $nodeId) {
                // Check if this is a stay_in_session node
                return isset($operator['data']['__data']['stay_in_session_message']);
            }
        }

        return false;
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
        return 'stay_in_session';
    }

    /**
     * Check if this is a terminal node (end of flow)
     * Stay in session nodes are never terminal
     *
     * @param array $node
     * @return bool
     */
    public function isTerminal($node)
    {
        return false;
    }

    /**
     * Get configuration options for this node type
     *
     * @return array
     */
    public static function getConfigurationOptions()
    {
        return [
            'name' => 'Stay in Session',
            'description' => 'Prevents the flow from ending and keeps all interactive nodes active',
            'icon' => 'fas fa-infinity',
            'category' => 'Flow Control',
            'fields' => [
                [
                    'name' => 'text',
                    'label' => 'Session Message',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'Optional message to display when session stays active',
                    'help' => 'This message will be shown to indicate the session is staying active'
                ]
            ],
            'outputs' => [], // No outputs since this node doesn't connect to other nodes
            'properties' => [
                'prevents_flow_termination' => true,
                'keeps_interactive_nodes_active' => true,
                'requires_user_input' => true,
                'is_terminal' => false
            ]
        ];
    }
}