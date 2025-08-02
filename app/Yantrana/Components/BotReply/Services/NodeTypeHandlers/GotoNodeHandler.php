<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

/**
 * Handler for goto type nodes (redirects to another node)
 */
class GotoNodeHandler extends BaseNodeHandler
{
    /**
     * Process the goto node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'];

        return [
            'type' => 'goto',
            'requires_input' => false,
            'redirect_to_node' => $payload['redirect_to_node'] ?? null,
            'node_id' => $node['id'],
            'is_redirect' => true
        ];
    }

    /**
     * Validate goto node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];

        if (empty($payload['redirect_to_node'])) {
            $errors[] = 'Redirect target node is required for goto nodes';
        }

        // Additional validation: check if target node exists
        if (!empty($payload['redirect_to_node'])) {
            $targetNodeExists = $this->validateTargetNodeExists($payload['redirect_to_node'], $payload['bot_flow_id'] ?? null);
            if (!$targetNodeExists) {
                $errors[] = 'Selected target node does not exist or is not accessible';
            }
        }

        return $errors;
    }

    /**
     * Get available nodes for selection in the current bot flow
     *
     * @param int|null $botFlowId
     * @param string|null $currentNodeId
     * @return array
     */
    public function getAvailableNodes($botFlowId = null, $currentNodeId = null)
    {
        if (!$botFlowId) {
            return [];
        }

        // Get all bot replies in the current flow
        $botReplies = $this->botReplyRepository->fetchItAll([
            'bot_flows__id' => $botFlowId,
            'vendors__id' => getVendorId(),
        ]);

        $availableNodes = [];
        foreach ($botReplies as $botReply) {
            // Don't include the current node to prevent self-reference
            if ($currentNodeId && $botReply->_uid === $currentNodeId) {
                continue;
            }

            $availableNodes[] = [
                'id' => $botReply->_uid,
                'name' => $botReply->name,
                'type' => $this->getNodeTypeFromBotReply($botReply)
            ];
        }

        return $availableNodes;
    }

    /**
     * Validate if target node exists
     *
     * @param string $targetNodeId
     * @param int|null $botFlowId
     * @return bool
     */
    private function validateTargetNodeExists($targetNodeId, $botFlowId = null)
    {
        if (!$botFlowId) {
            return false;
        }

        $targetNode = $this->botReplyRepository->fetchIt([
            '_uid' => $targetNodeId,
            'bot_flows__id' => $botFlowId,
            'vendors__id' => getVendorId(),
        ]);

        return !__isEmpty($targetNode);
    }

    /**
     * Get node type from bot reply data
     *
     * @param object $botReply
     * @return string
     */
    private function getNodeTypeFromBotReply($botReply)
    {
        $data = $botReply->__data ?? [];

        if (isset($data['question_message'])) {
            return 'question';
        } elseif (isset($data['interaction_message'])) {
            return 'interactive';
        } elseif (isset($data['media_message'])) {
            return 'media';
        } elseif (isset($data['goto_message'])) {
            return 'goto';
        } elseif (isset($data['stay_in_session_message'])) {
            return 'stay_in_session';
        } else {
            return 'message';
        }
    }

    /**
     * Get the next node ID (redirect target)
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        return $node['payload']['redirect_to_node'] ?? null;
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
        return 'goto';
    }

    /**
     * Check if this node should be processed immediately (no display)
     *
     * @return bool
     */
    public function isImmediate()
    {
        return true;
    }

    /**
     * Get display information for goto node
     *
     * @param array $node
     * @return array
     */
    public function getDisplayInfo($node)
    {
        $payload = $node['payload'];
        $targetNodeId = $payload['redirect_to_node'] ?? null;
        $targetNodeName = 'Unknown Node';

        if ($targetNodeId) {
            $targetNode = $this->botReplyRepository->fetchIt([
                '_uid' => $targetNodeId,
                'vendors__id' => getVendorId(),
            ]);

            if (!__isEmpty($targetNode)) {
                $targetNodeName = $targetNode->name;
            }
        }

        return [
            'type' => 'goto',
            'target_node_id' => $targetNodeId,
            'target_node_name' => $targetNodeName,
            'description' => "Redirects to: {$targetNodeName}"
        ];
    }

    /**
     * Create default payload for goto node
     *
     * @param array $options
     * @return array
     */
    public function createDefaultPayload($options = [])
    {
        return [
            'redirect_to_node' => $options['redirect_to_node'] ?? null,
            'bot_flow_id' => $options['bot_flow_id'] ?? null
        ];
    }
}