<?php

namespace App\Yantrana\Components\BotReply\Services;

use App\Yantrana\Components\BotReply\Services\NodeHandlerFactory;
use Illuminate\Support\Facades\Log;
use App\Yantrana\Components\BotReply\Services\FlowNodeService;

/**
 * Service for executing node-based flows
 */
class FlowExecutionService
{
    /**
     * @var FlowNodeService
     */
    private $flowNodeService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->flowNodeService = new FlowNodeService();
    }

    /**
     * Execute a flow starting from a specific node
     *
     * @param array $flowData
     * @param string $startNodeId
     * @param array $context
     * @return array
     */
    public function executeFlow($flowData, $startNodeId, $context = [])
    {
        $currentNodeId = $startNodeId;
        $responses = [];
        $maxIterations = 50; // Prevent infinite loops
        $iterations = 0;

        // Add flow data to context for node type checking
        $context['flow_data'] = $flowData;

        while ($currentNodeId && $iterations < $maxIterations) {
            $iterations++;
            
            $node = $this->flowNodeService->findNodeById($flowData, $currentNodeId);
            if (!$node) {
                break;
            }

            $response = $this->executeNode($node, $context);

            Log::info('Executed node in flow', [
                'node_id' => $node['id'],
                'node_type' => $node['type'],
                'requires_input' => $response['requires_input'] ?? false,
                'next_node' => $response['next_node'] ?? null,
                'is_wait_node' => $response['is_wait_node'] ?? false,
                'stay_in_session' => $response['stay_in_session'] ?? false,
                'iteration' => $iterations
            ]);

            // Track interactive nodes that connect to stay_in_session nodes
            if ($node['type'] === 'interactive' && $this->connectsToStayInSession($node, $flowData)) {
                $context = $this->addToActiveInteractiveNodes($node, $context);
                Log::info('Added interactive node to active session nodes', [
                    'node_id' => $node['id'],
                    'active_nodes_count' => count($context['active_interactive_nodes'] ?? [])
                ]);
            }

            // Also track interactive nodes when they require input (they might connect to stay_in_session later)
            if ($node['type'] === 'interactive' && ($response['requires_input'] ?? false)) {
                // Check if any of the buttons/options lead to stay_in_session nodes
                $hasStayInSessionConnections = $this->hasStayInSessionConnections($node, $flowData);
                Log::info('Checking interactive node for stay_in_session connections', [
                    'node_id' => $node['id'],
                    'has_connections' => $hasStayInSessionConnections,
                    'buttons_count' => count($node['payload']['buttons'] ?? [])
                ]);

                if ($hasStayInSessionConnections) {
                    $context = $this->addToActiveInteractiveNodes($node, $context);
                    Log::info('Added interactive node with stay_in_session connections to active nodes', [
                        'node_id' => $node['id'],
                        'active_nodes_count' => count($context['active_interactive_nodes'] ?? [])
                    ]);
                } else {
                    // TEMPORARY FIX: Add all interactive nodes that require input to active list
                    // This ensures they're available for stay_in_session nodes to process
                    $context = $this->addToActiveInteractiveNodes($node, $context);
                    Log::info('Added interactive node to active nodes (temporary fix)', [
                        'node_id' => $node['id'],
                        'active_nodes_count' => count($context['active_interactive_nodes'] ?? [])
                    ]);
                }
            }

            // If this is a goto node, immediately redirect
            if ($node['type'] === 'goto') {
                $currentNodeId = $response['redirect_to_node'] ?? null;
                continue;
            }

            $responses[] = $response;

            // If this is a wait node, stop execution here as the next node will be processed later
            if ($response['is_wait_node'] ?? false) {
                Log::info('Wait node encountered - stopping flow execution', [
                    'node_id' => $node['id'],
                    'node_type' => $node['type'],
                    'wait_time' => $response['wait_time'] ?? 0,
                    'scheduled_next_node' => $response['scheduled_next_node'] ?? null
                ]);
                // Keep the current node ID for wait nodes so flow doesn't get marked as complete
                // The next node will be processed by the delayed job
                break;
            }

            // If node requires user input, stop here and wait for input
            if ($response['requires_input'] ?? false) {
                Log::info('Node requires input - stopping flow execution', [
                    'node_id' => $node['id'],
                    'node_type' => $node['type'],
                    'variable_name' => $response['variable_name'] ?? null
                ]);
                break;
            }

            // Move to next node
            $currentNodeId = $response['next_node'] ?? null;

            Log::info('Moving to next node', [
                'current_node' => $node['id'],
                'next_node' => $currentNodeId
            ]);
        }

        // Check if flow is complete
        $isComplete = empty($currentNodeId);

        // Special handling for wait nodes and stay_in_session nodes - they should not mark the flow as complete
        if (!empty($responses)) {
            $lastResponse = end($responses);

            // If the last response is a wait node, the flow is NOT complete
            if ($lastResponse['is_wait_node'] ?? false) {
                $isComplete = false;
                // Keep the current node as the wait node for session tracking
                $currentNodeId = $lastResponse['node_id'] ?? $currentNodeId;

                Log::info('Flow paused at wait node - not marking as complete', [
                    'wait_node_id' => $currentNodeId,
                    'scheduled_next_node' => $lastResponse['scheduled_next_node'] ?? null,
                    'wait_time' => $lastResponse['wait_time'] ?? 0
                ]);
            }
            // If the last response is a stay_in_session node, the flow is NOT complete
            elseif ($lastResponse['stay_in_session'] ?? false) {
                $isComplete = false;
                // Keep the current node as the stay_in_session node for session tracking
                $currentNodeId = $lastResponse['node_id'] ?? $currentNodeId;

                Log::info('Flow staying in session - not marking as complete', [
                    'stay_in_session_node_id' => $currentNodeId,
                    'session_message' => $lastResponse['session_message'] ?? null
                ]);
            }
            // Check if the last response was a terminal message node
            elseif ($lastResponse['type'] === 'message' && ($lastResponse['is_terminal'] ?? false)) {
                $isComplete = true;
                $currentNodeId = null; // Clear current node for terminal nodes
            }
        }

        return [
            'responses' => $responses,
            'current_node_id' => $currentNodeId,
            'context' => $context,
            'is_complete' => $isComplete,
            'iterations' => $iterations
        ];
    }

    /**
     * Execute a single node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function executeNode($node, $context = [])
    {
        try {
            return NodeHandlerFactory::processNode($node, $context);
        } catch (\Exception $e) {
            return [
                'type' => 'error',
                'text' => 'Error processing node: ' . $e->getMessage(),
                'requires_input' => false,
                'next_node' => null,
                'node_id' => $node['id'] ?? 'unknown'
            ];
        }
    }

    /**
     * Process user input for a specific node
     *
     * @param array $flowData
     * @param string $nodeId
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($flowData, $nodeId, $userInput, $context = [])
    {
        // Add flow data to context for node type checking
        $context['flow_data'] = $flowData;

        $node = $this->flowNodeService->findNodeById($flowData, $nodeId);
        if (!$node) {
            return [
                'error' => 'Node not found',
                'context' => $context,
                'next_node' => null
            ];
        }

        try {
            $handler = NodeHandlerFactory::getHandlerForNode($node);
            
            // Process the input based on node type
            if (method_exists($handler, 'processUserInput')) {
                $result = $handler->processUserInput($node, $userInput, $context);
            } else {
                $result = [
                    'context' => $context,
                    'next_node' => $handler->getNextNodeId($node, $userInput),
                    'processed_input' => $userInput
                ];
            }

            return $result;
        } catch (\Exception $e) {
            return [
                'error' => 'Error processing user input: ' . $e->getMessage(),
                'context' => $context,
                'next_node' => null
            ];
        }
    }

    /**
     * Continue flow execution with user input
     *
     * @param array $flowData
     * @param string $nodeId
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function continueFlowWithInput($flowData, $nodeId, $userInput, $context = [])
    {
        Log::info('Continuing flow with input', [
            'node_id' => $nodeId,
            'user_input' => $userInput,
            'context_keys' => array_keys($context)
        ]);

        // Process the user input for the current node
        $inputResult = $this->processUserInput($flowData, $nodeId, $userInput, $context);
        
        if (isset($inputResult['type']) && $inputResult['type'] === 'validation_error') {
            // Add error message as a chat message
            $responses = [
                [
                    'type' => 'message',
                    'text' => $inputResult['error'],
                    'node_id' => $nodeId
                ],
                [
                    'type' => 'question',
                    'text' => $inputResult['question_text'],
                    'node_id' => $nodeId,
                    'expects_input' => true
                ]
            ];
            return [
                'responses' => $responses,
                'current_node_id' => $nodeId,
                'context' => $inputResult['context'],
                'is_complete' => false
            ];
        }

        if (isset($inputResult['error'])) {
            Log::error('Error processing user input', [
                'node_id' => $nodeId,
                'user_input' => $userInput,
                'error' => $inputResult['error']
            ]);
            
            // If this is an interactive node and input doesn't match, re-send the interactive message
            $node = $this->flowNodeService->findNodeById($flowData, $nodeId);
            
            Log::info('Checking node for re-sending interactive message', [
                'node_id' => $nodeId,
                'node_found' => !is_null($node),
                'node_type' => $node['type'] ?? 'not_found',
                'is_interactive' => ($node && $node['type'] === 'interactive')
            ]);
            
            if ($node && $node['type'] === 'interactive') {
                Log::info('Re-sending interactive message due to invalid input', [
                    'node_id' => $nodeId,
                    'user_input' => $userInput,
                    'error' => $inputResult['error']
                ]);
                
                // Re-execute the interactive node to re-send the message
                $nodeResponse = $this->executeNode($node, $context);
                
                Log::info('Re-executed interactive node', [
                    'node_id' => $nodeId,
                    'response_type' => $nodeResponse['type'] ?? 'unknown',
                    'requires_input' => $nodeResponse['requires_input'] ?? false
                ]);
                
                // Add an error message before re-sending the interactive message
                $responses = [
                    [
                        'type' => 'message',
                        'text' => $inputResult['error'] . ' Please select one of the available options.',
                        'node_id' => $nodeId
                    ],
                    $nodeResponse // Re-send the interactive message
                ];
                
                Log::info('Returning responses with re-sent interactive message', [
                    'node_id' => $nodeId,
                    'response_count' => count($responses),
                    'first_response_type' => $responses[0]['type'] ?? 'unknown',
                    'second_response_type' => $responses[1]['type'] ?? 'unknown'
                ]);
                
                return [
                    'responses' => $responses,
                    'current_node_id' => $nodeId,
                    'context' => $context,
                    'is_complete' => false
                ];
            }
            
            Log::warning('Not re-sending interactive message - node not found or not interactive', [
                'node_id' => $nodeId,
                'node_found' => !is_null($node),
                'node_type' => $node['type'] ?? 'not_found'
            ]);
            
            return [
                'error' => $inputResult['error'],
                'current_node_id' => $nodeId,
                'context' => $context,
                'responses' => []
            ];
        }

        $updatedContext = $inputResult['context'];
        $nextNodeId = $inputResult['next_node'];

        Log::info('Input processed, moving to next node', [
            'current_node' => $nodeId,
            'next_node' => $nextNodeId,
            'has_next_node' => !is_null($nextNodeId),
            'user_input' => $userInput
        ]);

        // Continue execution from the next node
        if ($nextNodeId) {
            $executionResult = $this->executeFlow($flowData, $nextNodeId, $updatedContext);

            // Merge the input processing result with execution result
            $mergedResult = array_merge($executionResult, [
                'input_processed' => $inputResult['processed_input'] ?? $userInput,
                'previous_node_id' => $nodeId
            ]);

            // Preserve active interactive nodes in the merged result context
            if (isset($updatedContext['active_interactive_nodes'])) {
                $mergedResult['context']['active_interactive_nodes'] = $updatedContext['active_interactive_nodes'];
            }

            return $mergedResult;
        }

        // No next node means flow is complete
        return [
            'responses' => [],
            'current_node_id' => null,
            'context' => $updatedContext,
            'is_complete' => true,
            'input_processed' => $inputResult['processed_input'] ?? $userInput,
            'previous_node_id' => $nodeId
        ];
    }

    /**
     * Get the first node in a flow
     *
     * @param array $flowData
     * @return array|null
     */
    public function getFirstNode($flowData)
    {
        $nodes = $flowData['nodes'] ?? [];

        if (empty($nodes)) {
            return null;
        }

        // PRIORITY 1: Check if flow has legacy flow_builder_data with start links
        if (isset($flowData['flow_builder_data']['links'])) {
            $links = $flowData['flow_builder_data']['links'];
            
            // Find links that start from 'start' operator
            foreach ($links as $link) {
                if ($link['fromOperator'] === 'start') {
                    $startNodeId = $link['toOperator'];
                    $startNode = $this->flowNodeService->findNodeById($flowData, $startNodeId);
                    if ($startNode) {
                        // Prioritize list type nodes
                        if ($startNode['type'] === 'interactive' && 
                            isset($startNode['payload']['list_data'])) {
                            Log::info('Found list type node as first node', [
                                'first_node_id' => $startNodeId,
                                'node_type' => 'interactive_list',
                                'node_text' => $startNode['payload']['text'] ?? 'unknown'
                            ]);
                            return $startNode;
                        }
                        
                        // Store non-list node to use as fallback
                        $fallbackStartNode = $startNode;
                    }
                }
            }
            
            // Return fallback start node if found
            if (isset($fallbackStartNode)) {
                Log::info('Using fallback start node', [
                    'first_node_id' => $fallbackStartNode['id'],
                    'node_type' => $fallbackStartNode['type'],
                    'node_text' => $fallbackStartNode['payload']['text'] ?? 'unknown'
                ]);
                return $fallbackStartNode;
            }
        }

        // PRIORITY 2: Find list type nodes that are not referenced by others
        $referencedNodeIds = $this->flowNodeService->getReferencedNodeIds($flowData);
        $nodeIds = array_column($nodes, 'id');
        $entryNodes = array_diff($nodeIds, $referencedNodeIds);

        if (!empty($entryNodes)) {
            // First look for list type nodes among entry nodes
            foreach ($nodes as $node) {
                if (in_array($node['id'], $entryNodes) && 
                    $node['type'] === 'interactive' && 
                    isset($node['payload']['list_data'])) {
                    Log::info('Found list type entry node', [
                        'first_node_id' => $node['id'],
                        'node_type' => 'interactive_list',
                        'node_text' => $node['payload']['text'] ?? 'unknown'
                    ]);
                    return $node;
                }
            }

            // If no list nodes found, use first entry node
            $firstNodeId = reset($entryNodes);
            $firstNode = $this->flowNodeService->findNodeById($flowData, $firstNodeId);
            if ($firstNode) {
                Log::info('Using first entry node', [
                    'first_node_id' => $firstNodeId,
                    'node_type' => $firstNode['type'],
                    'node_text' => $firstNode['payload']['text'] ?? 'unknown'
                ]);
                return $firstNode;
            }
        }

        // PRIORITY 3: Look for list type nodes by position
        foreach ($nodes as $node) {
            if ($node['type'] === 'interactive' && 
                isset($node['payload']['list_data'])) {
                Log::info('Found list type node by scanning', [
                    'first_node_id' => $node['id'],
                    'node_type' => 'interactive_list',
                    'node_text' => $node['payload']['text'] ?? 'unknown'
                ]);
                return $node;
            }
        }

        // FALLBACK: Use topmost node
        $firstNode = null;
        $minY = PHP_INT_MAX;

        foreach ($nodes as $node) {
            $y = $node['position']['y'] ?? 0;
            if ($y < $minY) {
                $minY = $y;
                $firstNode = $node;
            }
        }

        if ($firstNode) {
            Log::info('Using topmost node as fallback', [
                'first_node_id' => $firstNode['id'],
                'node_type' => $firstNode['type'],
                'node_text' => $firstNode['payload']['text'] ?? 'unknown',
                'y_position' => $minY
            ]);
        }

        return $firstNode;
    }

    /**
     * Validate entire flow structure
     *
     * @param array $flowData
     * @return array
     */
    public function validateFlow($flowData)
    {
        $errors = [];
        
        // Basic structure validation
        $structureErrors = $this->flowNodeService->validateFlowStructure($flowData);
        $errors = array_merge($errors, $structureErrors);

        // Validate each node with its specific handler
        $nodes = $flowData['nodes'] ?? [];
        foreach ($nodes as $index => $node) {
            $nodeErrors = NodeHandlerFactory::validateNode($node);
            foreach ($nodeErrors as $error) {
                $errors[] = "Node $index: $error";
            }
        }

        // Check for orphaned nodes (nodes that are never referenced)
        $referencedIds = $this->flowNodeService->getReferencedNodeIds($flowData);
        $nodeIds = array_column($nodes, 'id');
        $orphanedNodes = array_diff($nodeIds, $referencedIds);
        
        // Remove the first node from orphaned check (it's the entry point)
        $firstNode = $this->getFirstNode($flowData);
        if ($firstNode) {
            $orphanedNodes = array_diff($orphanedNodes, [$firstNode['id']]);
        }

        foreach ($orphanedNodes as $orphanedId) {
            $errors[] = "Node $orphanedId is never referenced and may be unreachable";
        }

        return $errors;
    }

    /**
     * Check if an interactive node connects to a stay_in_session node
     *
     * @param array $node
     * @param array $flowData
     * @return bool
     */
    private function connectsToStayInSession($node, $flowData)
    {
        if ($node['type'] !== 'interactive') {
            return false;
        }

        $payload = $node['payload'] ?? [];
        $buttons = $payload['buttons'] ?? [];
        $sections = $payload['sections'] ?? [];

        // Check button connections
        foreach ($buttons as $button) {
            $nextNodeId = $button['next_node'] ?? null;
            if ($nextNodeId && $this->isStayInSessionNodeId($nextNodeId, $flowData)) {
                return true;
            }
        }

        // Check list section connections
        foreach ($sections as $section) {
            foreach ($section['rows'] ?? [] as $row) {
                $nextNodeId = $row['next_node'] ?? null;
                if ($nextNodeId && $this->isStayInSessionNodeId($nextNodeId, $flowData)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if an interactive node has any connections that eventually lead to stay_in_session nodes
     *
     * @param array $node
     * @param array $flowData
     * @return bool
     */
    private function hasStayInSessionConnections($node, $flowData)
    {
        if ($node['type'] !== 'interactive') {
            return false;
        }

        $payload = $node['payload'] ?? [];
        $buttons = $payload['buttons'] ?? [];
        $sections = $payload['sections'] ?? [];

        // Check button connections (including indirect connections)
        foreach ($buttons as $button) {
            $nextNodeId = $button['next_node'] ?? null;
            if ($nextNodeId && $this->eventuallyLeadsToStayInSession($nextNodeId, $flowData, [])) {
                return true;
            }
        }

        // Check list section connections (including indirect connections)
        foreach ($sections as $section) {
            foreach ($section['rows'] ?? [] as $row) {
                $nextNodeId = $row['next_node'] ?? null;
                if ($nextNodeId && $this->eventuallyLeadsToStayInSession($nextNodeId, $flowData, [])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a node eventually leads to a stay_in_session node (with cycle detection)
     *
     * @param string $nodeId
     * @param array $flowData
     * @param array $visited
     * @return bool
     */
    private function eventuallyLeadsToStayInSession($nodeId, $flowData, $visited = [])
    {
        // Prevent infinite loops
        if (in_array($nodeId, $visited)) {
            return false;
        }
        $visited[] = $nodeId;

        Log::info('Checking if node eventually leads to stay_in_session', [
            'node_id' => $nodeId,
            'visited_count' => count($visited)
        ]);

        // Check if this node is a stay_in_session node
        if ($this->isStayInSessionNodeId($nodeId, $flowData)) {
            Log::info('Found stay_in_session node', ['node_id' => $nodeId]);
            return true;
        }

        // Get the node data
        $operators = $flowData['operators'] ?? [];
        $operator = $operators[$nodeId] ?? null;

        if (!$operator) {
            Log::info('Node not found in operators', ['node_id' => $nodeId]);
            return false;
        }

        $nodeData = $operator['data'] ?? [];

        Log::info('Analyzing node data for connections', [
            'node_id' => $nodeId,
            'has_question_message' => isset($nodeData['__data']['question_message']),
            'has_interaction_message' => isset($nodeData['__data']['interaction_message']),
            'has_stay_in_session_message' => isset($nodeData['__data']['stay_in_session_message']),
            'has_next_node' => isset($nodeData['next_node']),
            'data_keys' => array_keys($nodeData['__data'] ?? [])
        ]);

        // Check next node connections based on node type
        if (isset($nodeData['__data']['question_message'])) {
            // Question node - check next_node
            $nextNode = $nodeData['__data']['question_message']['next_node'] ?? null;
            Log::info('Question node next_node', ['current' => $nodeId, 'next' => $nextNode]);
            if ($nextNode && $this->eventuallyLeadsToStayInSession($nextNode, $flowData, $visited)) {
                return true;
            }
        } elseif (isset($nodeData['__data']['interaction_message'])) {
            // Interactive node - check all button/option connections
            $buttons = $nodeData['__data']['interaction_message']['buttons'] ?? [];
            Log::info('Interactive node buttons', ['current' => $nodeId, 'buttons_count' => count($buttons)]);
            foreach ($buttons as $button) {
                $nextNode = $button['next_node'] ?? null;
                Log::info('Checking button connection', ['button' => $button['title'] ?? 'unknown', 'next_node' => $nextNode]);
                if ($nextNode && $this->eventuallyLeadsToStayInSession($nextNode, $flowData, $visited)) {
                    return true;
                }
            }

            $sections = $nodeData['__data']['interaction_message']['sections'] ?? [];
            foreach ($sections as $section) {
                foreach ($section['rows'] ?? [] as $row) {
                    $nextNode = $row['next_node'] ?? null;
                    if ($nextNode && $this->eventuallyLeadsToStayInSession($nextNode, $flowData, $visited)) {
                        return true;
                    }
                }
            }
        } else {
            // Simple message node or other types - check next_node
            $nextNode = $nodeData['next_node'] ?? null;
            Log::info('Simple/other node next_node', ['current' => $nodeId, 'next' => $nextNode]);
            if ($nextNode && $this->eventuallyLeadsToStayInSession($nextNode, $flowData, $visited)) {
                return true;
            }
        }

        Log::info('Node does not lead to stay_in_session', ['node_id' => $nodeId]);
        return false;
    }

    /**
     * Check if a node ID refers to a stay_in_session node
     *
     * @param string $nodeId
     * @param array $flowData
     * @return bool
     */
    private function isStayInSessionNodeId($nodeId, $flowData)
    {
        $operators = $flowData['operators'] ?? [];
        $operator = $operators[$nodeId] ?? null;

        if (!$operator) {
            Log::info('Node not found in operators for stay_in_session check', ['node_id' => $nodeId]);
            return false;
        }

        $isStayInSession = isset($operator['data']['__data']['stay_in_session_message']);

        Log::info('Checking if node is stay_in_session', [
            'node_id' => $nodeId,
            'is_stay_in_session' => $isStayInSession,
            'data_keys' => array_keys($operator['data']['__data'] ?? [])
        ]);

        return $isStayInSession;
    }

    /**
     * Add an interactive node to the active interactive nodes list
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    private function addToActiveInteractiveNodes($node, $context)
    {
        $context['active_interactive_nodes'] = $context['active_interactive_nodes'] ?? [];

        Log::info('Adding interactive node to active list', [
            'node_id' => $node['id'],
            'node_type' => $node['type'],
            'current_active_count' => count($context['active_interactive_nodes']),
            'buttons_count' => count($node['payload']['buttons'] ?? [])
        ]);

        // Check if node is already in the list
        foreach ($context['active_interactive_nodes'] as $key => $existingNode) {
            if (($existingNode['node']['id'] ?? null) === $node['id']) {
                // Node already exists, update timestamp
                $context['active_interactive_nodes'][$key]['last_accessed'] = now()->toDateTimeString();
                Log::info('Updated existing active interactive node', [
                    'node_id' => $node['id'],
                    'total_active_count' => count($context['active_interactive_nodes'])
                ]);
                return $context;
            }
        }

        // Add new node to active list
        $context['active_interactive_nodes'][] = [
            'node' => $node,
            'added_at' => now()->toDateTimeString(),
            'last_accessed' => now()->toDateTimeString()
        ];

        Log::info('Added new interactive node to active list', [
            'node_id' => $node['id'],
            'total_active_count' => count($context['active_interactive_nodes'])
        ]);

        return $context;
    }
}