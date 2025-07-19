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
                'iteration' => $iterations
            ]);

            // If this is a goto node, immediately redirect
            if ($node['type'] === 'goto') {
                $currentNodeId = $response['redirect_to_node'] ?? null;
                continue;
            }

            $responses[] = $response;

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

        // Also check if the last response was a terminal message node
        if (!$isComplete && !empty($responses)) {
            $lastResponse = end($responses);
            if ($lastResponse['type'] === 'message' && ($lastResponse['is_terminal'] ?? false)) {
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
     * Continue flow execution after user input
     *
     * @param array $flowData
     * @param string $nodeId
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function continueFlowWithInput($flowData, $nodeId, $userInput, $context = [])
    {
        // Process the user input first
        $inputResult = $this->processUserInput($flowData, $nodeId, $userInput, $context);
        
        if (isset($inputResult['error'])) {
            return $inputResult;
        }

        $updatedContext = $inputResult['context'];
        $nextNodeId = $inputResult['next_node'];

        // Continue execution from the next node
        if ($nextNodeId) {
            $executionResult = $this->executeFlow($flowData, $nextNodeId, $updatedContext);

            // Merge the input processing result with execution result
            return array_merge($executionResult, [
                'input_processed' => $inputResult['processed_input'] ?? $userInput,
                'previous_node_id' => $nodeId
            ]);
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

        // First, try to find a node that is not referenced by any other node (entry point)
        $referencedNodeIds = $this->flowNodeService->getReferencedNodeIds($flowData);
        $nodeIds = array_column($nodes, 'id');
        $entryNodes = array_diff($nodeIds, $referencedNodeIds);

        if (!empty($entryNodes)) {
            // Find the entry node with the smallest Y position
            $firstNodeId = null;
            $minY = PHP_INT_MAX;

            foreach ($nodes as $node) {
                if (in_array($node['id'], $entryNodes)) {
                    $y = $node['position']['y'] ?? 0;
                    if ($y < $minY) {
                        $minY = $y;
                        $firstNodeId = $node['id'];
                    }
                }
            }

            if ($firstNodeId) {
                return $this->flowNodeService->findNodeById($flowData, $firstNodeId);
            }
        }

        // Fallback: Find the node with the smallest Y position (topmost)
        $firstNode = null;
        $minY = PHP_INT_MAX;

        foreach ($nodes as $node) {
            $y = $node['position']['y'] ?? 0;
            if ($y < $minY) {
                $minY = $y;
                $firstNode = $node;
            }
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
}
