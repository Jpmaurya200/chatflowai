<?php

namespace App\Yantrana\Components\BotReply\Services;

use Illuminate\Support\Str;

/**
 * FlowNodeService - Handles the new node-based flow structure
 */
class FlowNodeService
{
    /**
     * Node types supported by the flow system
     */
    const NODE_TYPES = [
        'question' => 'question',
        'interactive' => 'interactive',
        'message' => 'message',
        'goto' => 'goto',
        'webhook' => 'webhook',
        'custom_field' => 'custom_field',
        'stay_in_session' => 'stay_in_session',
        'flow' => 'flow'
    ];

    /**
     * Validate flow structure
     *
     * @param array $flowData
     * @return array
     */
    public function validateFlowStructure($flowData)
    {
        $errors = [];
        
        if (!isset($flowData['flow_id'])) {
            $errors[] = 'Flow ID is required';
        }
        
        if (!isset($flowData['nodes']) || !is_array($flowData['nodes'])) {
            $errors[] = 'Nodes array is required';
        } else {
            foreach ($flowData['nodes'] as $index => $node) {
                $nodeErrors = $this->validateNode($node, $index);
                $errors = array_merge($errors, $nodeErrors);
            }
        }
        
        return $errors;
    }

    /**
     * Validate individual node
     *
     * @param array $node
     * @param int $index
     * @return array
     */
    private function validateNode($node, $index)
    {
        $errors = [];
        $prefix = "Node $index: ";
        
        if (!isset($node['id'])) {
            $errors[] = $prefix . 'ID is required';
        }
        
        if (!isset($node['type']) || !in_array($node['type'], self::NODE_TYPES)) {
            $errors[] = $prefix . 'Valid type is required (' . implode(', ', self::NODE_TYPES) . ')';
        }
        
        if (!isset($node['payload']) || !is_array($node['payload'])) {
            $errors[] = $prefix . 'Payload is required';
        } else {
            $payloadErrors = $this->validateNodePayload($node['type'], $node['payload'], $prefix);
            $errors = array_merge($errors, $payloadErrors);
        }
        
        if (!isset($node['position']) || !is_array($node['position'])) {
            $errors[] = $prefix . 'Position is required';
        } elseif (!isset($node['position']['x']) || !isset($node['position']['y'])) {
            $errors[] = $prefix . 'Position must have x and y coordinates';
        }
        
        return $errors;
    }

    /**
     * Validate node payload based on type
     *
     * @param string $type
     * @param array $payload
     * @param string $prefix
     * @return array
     */
    private function validateNodePayload($type, $payload, $prefix)
    {
        $errors = [];
        
        switch ($type) {
            case 'question':
                if (!isset($payload['text'])) {
                    $errors[] = $prefix . 'Question text is required';
                }
                if (!isset($payload['variable_name'])) {
                    $errors[] = $prefix . 'Variable name is required for question nodes';
                }
                break;
                
            case 'interactive':
                if (!isset($payload['text'])) {
                    $errors[] = $prefix . 'Interactive text is required';
                }
                if (!isset($payload['buttons']) || !is_array($payload['buttons']) || empty($payload['buttons'])) {
                    $errors[] = $prefix . 'Buttons array is required for interactive nodes';
                } else {
                    foreach ($payload['buttons'] as $buttonIndex => $button) {
                        if (!isset($button['id'])) {
                            $errors[] = $prefix . "Button $buttonIndex: ID is required";
                        }
                        if (!isset($button['title'])) {
                            $errors[] = $prefix . "Button $buttonIndex: Title is required";
                        }
                    }
                }
                break;
                
            case 'message':
                if (!isset($payload['text'])) {
                    $errors[] = $prefix . 'Message text is required';
                }
                break;
                
            case 'goto':
                if (!isset($payload['redirect_to_node'])) {
                    $errors[] = $prefix . 'Redirect target node is required for goto nodes';
                }
                break;

            case 'custom_field':
                if (!isset($payload['custom_field_id'])) {
                    $errors[] = $prefix . 'Custom field selection is required';
                }
                if (!isset($payload['question_text'])) {
                    $errors[] = $prefix . 'Question text is required';
                }
                break;
            case 'flow':
                if (empty($payload['whatsapp_flow_id'])) {
                    $errors[] = $prefix . 'WhatsApp flow ID is required';
                }
                break;
        }
        
        return $errors;
    }

    /**
     * Generate a new node ID
     *
     * @return string
     */
    public function generateNodeId()
    {
        return (string) Str::uuid();
    }

    /**
     * Create a new node with default structure
     *
     * @param string $type
     * @param array $payload
     * @param array $position
     * @return array
     */
    public function createNode($type, $payload = [], $position = ['x' => 100, 'y' => 100])
    {
        return [
            'id' => $this->generateNodeId(),
            'type' => $type,
            'payload' => $payload,
            'position' => $position
        ];
    }

    /**
     * Find node by ID in flow
     *
     * @param array $flowData
     * @param string $nodeId
     * @return array|null
     */
    public function findNodeById($flowData, $nodeId)
    {
        $nodes = $flowData['nodes'] ?? [];
        
        foreach ($nodes as $node) {
            if ($node['id'] === $nodeId) {
                return $node;
            }
        }
        
        return null;
    }

    /**
     * Get next node ID from current node
     *
     * @param array $node
     * @param string|null $buttonId
     * @return string|null
     */
    public function getNextNodeId($node, $buttonId = null)
    {
        $payload = $node['payload'] ?? [];
        
        switch ($node['type']) {
            case 'question':
            case 'message':
                return $payload['next_node'] ?? null;

            case 'interactive':
                if ($buttonId && isset($payload['buttons'])) {
                    foreach ($payload['buttons'] as $button) {
                        if ($button['id'] === $buttonId) {
                            return $button['next_node'] ?? null;
                        }
                    }
                }
                return null;

            case 'goto':
                return $payload['redirect_to_node'] ?? null;

            case 'flow':
                return $payload['next_node'] ?? null;

            case 'stay_in_session':
                // Stay in session nodes always return null to prevent flow termination
                return null;

            default:
                return null;
        }
    }

    /**
     * Update node in flow data
     *
     * @param array $flowData
     * @param string $nodeId
     * @param array $updatedNode
     * @return array
     */
    public function updateNodeInFlow($flowData, $nodeId, $updatedNode)
    {
        $nodes = $flowData['nodes'] ?? [];
        
        foreach ($nodes as $index => $node) {
            if ($node['id'] === $nodeId) {
                $nodes[$index] = $updatedNode;
                break;
            }
        }
        
        $flowData['nodes'] = $nodes;
        return $flowData;
    }

    /**
     * Remove node from flow data
     *
     * @param array $flowData
     * @param string $nodeId
     * @return array
     */
    public function removeNodeFromFlow($flowData, $nodeId)
    {
        $nodes = $flowData['nodes'] ?? [];
        
        $nodes = array_filter($nodes, function($node) use ($nodeId) {
            return $node['id'] !== $nodeId;
        });
        
        $flowData['nodes'] = array_values($nodes);
        return $flowData;
    }

    /**
     * Get all node IDs referenced as next_node or redirect_to_node
     *
     * @param array $flowData
     * @return array
     */
    public function getReferencedNodeIds($flowData)
    {
        $referencedIds = [];
        $nodes = $flowData['nodes'] ?? [];

        foreach ($nodes as $node) {
            $payload = $node['payload'] ?? [];

            // Check direct next_node references
            if (isset($payload['next_node']) && !empty($payload['next_node'])) {
                $referencedIds[] = $payload['next_node'];
            }

            if (isset($payload['failed_next_node']) && !empty($payload['failed_next_node'])) {
                $referencedIds[] = $payload['failed_next_node'];
            }

            // Check redirect_to_node references
            if (isset($payload['redirect_to_node']) && !empty($payload['redirect_to_node'])) {
                $referencedIds[] = $payload['redirect_to_node'];
            }

            // Check button next_node references
            if (isset($payload['buttons'])) {
                foreach ($payload['buttons'] as $button) {
                    if (isset($button['next_node']) && !empty($button['next_node'])) {
                        $referencedIds[] = $button['next_node'];
                    }
                }
            }
        }

        return array_unique(array_filter($referencedIds));
    }

    /**
     * Check if a node is a terminal node (has no next nodes)
     *
     * @param array $node
     * @return bool
     */
    public function isTerminalNode($node)
    {
        $payload = $node['payload'] ?? [];

        // Check if it has a next_node
        if (!empty($payload['next_node'])) {
            return false;
        }

        // Check if it has redirect_to_node
        if (!empty($payload['redirect_to_node'])) {
            return false;
        }

        // Check if any buttons have next_node
        if (isset($payload['buttons'])) {
            foreach ($payload['buttons'] as $button) {
                if (!empty($button['next_node'])) {
                    return false;
                }
            }
        }

        return true;
    }
}