<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessBotFlowNode;
use App\Models\UserActiveFlow;

/**
 * Handler for wait/delay type nodes
 */
class WaitNodeHandler extends BaseNodeHandler
{
    /**
     * Process the node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        // Extract wait time from different possible sources in the flow data
        $waitTime = $this->extractWaitTime($node);
        $waitMessage = $this->extractWaitMessage($node);
        $nextNodeId = $node['payload']['next_node'] ?? null;
        
        Log::info('Processing wait node', [
            'node_id' => $node['id'],
            'wait_time' => $waitTime,
            'wait_message' => $waitMessage,
            'next_node' => $nextNodeId,
            'context_keys' => array_keys($context)
        ]);

        // If there's a next node and wait time > 0, schedule delayed processing
        if ($nextNodeId && $waitTime > 0) {
            $this->scheduleDelayedProcessing($node, $context, $waitTime, $nextNodeId);
            
            Log::info('Scheduled delayed processing for wait node', [
                'node_id' => $node['id'],
                'wait_time' => $waitTime,
                'next_node' => $nextNodeId,
                'delay_seconds' => $waitTime
            ]);
        } else {
            Log::warning('Wait node has no next node or invalid wait time', [
                'node_id' => $node['id'],
                'wait_time' => $waitTime,
                'next_node' => $nextNodeId
            ]);
        }

        // Return the wait message to be sent immediately
        return [
            'type' => 'wait',
            'text' => $waitMessage,
            'wait_time' => $waitTime,
            'requires_input' => false,
            'next_node' => null, // Don't continue immediately
            'node_id' => $node['id'],
            'is_wait_node' => true, // Flag to indicate this is a wait node
            'scheduled_next_node' => $nextNodeId // For debugging
        ];
    }

    /**
     * Extract wait time from node data
     *
     * @param array $node
     * @return int
     */
    private function extractWaitTime($node)
    {
        $payload = $node['payload'] ?? [];
        
        // Try different possible sources for wait time
        $waitTime = 0;
        
        // Method 1: Direct wait_time in payload
        if (isset($payload['wait_time'])) {
            $waitTime = $payload['wait_time'];
        }
        // Method 2: Check if it's in wait_delay_seconds (from bot reply data)
        elseif (isset($payload['wait_delay_seconds'])) {
            $waitTime = $payload['wait_delay_seconds'];
        }
        // Method 3: Try to get from bot reply text parsing (fallback)
        else {
            $text = $payload['text'] ?? '';
            if (preg_match('/(\d+)\s*(?:second|sec)/i', $text, $matches)) {
                $waitTime = (int)$matches[1];
            }
        }
        
        // Parse and validate the wait time
        $waitTime = $this->parseWaitTime($waitTime);
        
        Log::info('Extracted wait time', [
            'node_id' => $node['id'] ?? 'unknown',
            'raw_payload' => $payload,
            'extracted_wait_time' => $waitTime
        ]);
        
        return $waitTime;
    }

    /**
     * Extract wait message from node data
     *
     * @param array $node
     * @return string
     */
    private function extractWaitMessage($node)
    {
        $payload = $node['payload'] ?? [];
        
        // Try different sources for wait message
        $waitMessage = '';
        
        // Method 1: Direct wait_message in payload
        if (!empty($payload['wait_message'])) {
            $waitMessage = $payload['wait_message'];
        }
        // Method 2: Use general text field
        elseif (!empty($payload['text'])) {
            $waitMessage = $payload['text'];
        }
        // Method 3: Get from bot reply data using node ID
        else {
            $waitMessage = $this->getBotReplyText($node['id'], []);
        }
        
        // Fallback message
        if (empty($waitMessage)) {
            $waitMessage = 'Please wait...';
        }
        
        return $waitMessage;
    }

    /**
     * Schedule delayed processing of the next node
     *
     * @param array $node
     * @param array $context
     * @param int $waitTime
     * @param string $nextNodeId
     * @return void
     */
    private function scheduleDelayedProcessing($node, $context, $waitTime, $nextNodeId)
    {
        try {
            // Get phone number from context
            $phoneNumber = $context['wa_id'] ?? $context['contact']['wa_id'] ?? null;
            if (!$phoneNumber) {
                Log::error('No phone number found in context for wait node scheduling', [
                    'node_id' => $node['id'],
                    'context_keys' => array_keys($context)
                ]);
                return;
            }

            // Get flow ID from context or try to determine it
            $flowId = $context['flow_id'] ?? null;
            if (!$flowId) {
                // Try to get flow ID from active flow
                $activeFlow = UserActiveFlow::getActiveFlow($phoneNumber);
                if ($activeFlow) {
                    // Get the flow UID from the bot flow
                    $botFlow = \App\Yantrana\Components\BotReply\Models\BotFlowModel::find($activeFlow->flow_id);
                    $flowId = $botFlow ? $botFlow->_uid : null;
                }
            }

            if (!$flowId) {
                Log::error('No flow ID found for wait node scheduling', [
                    'node_id' => $node['id'],
                    'phone_number' => $phoneNumber,
                    'context_keys' => array_keys($context)
                ]);
                return;
            }

            // Update active flow session to preserve state during wait
            $this->updateActiveFlowForWait($phoneNumber, $node['id'], $nextNodeId, $context);

            // Try multiple approaches to ensure delayed execution works
            try {
                // Method 1: Try database queue with delay
                ProcessBotFlowNode::dispatch($phoneNumber, $flowId, $nextNodeId, $context)
                    ->delay(Carbon::now()->addSeconds($waitTime))
                    ->onQueue('default')
                    ->onConnection('database');
                
                Log::info('Scheduled job using database queue with delay', [
                    'node_id' => $node['id'],
                    'wait_time' => $waitTime,
                    'method' => 'database_queue_delay'
                ]);
            } catch (\Exception $e) {
                Log::warning('Database queue failed, trying alternative method', [
                    'node_id' => $node['id'],
                    'error' => $e->getMessage()
                ]);
                
                // Method 2: Fallback to immediate dispatch with internal delay handling
                ProcessBotFlowNode::dispatch($phoneNumber, $flowId, $nextNodeId, array_merge($context, [
                    'wait_until' => Carbon::now()->addSeconds($waitTime)->timestamp,
                    'is_delayed_execution' => true
                ]))->onQueue('default');
                
                Log::info('Scheduled job using immediate dispatch with delay handling', [
                    'node_id' => $node['id'],
                    'wait_until' => Carbon::now()->addSeconds($waitTime)->toDateTimeString(),
                    'method' => 'immediate_with_delay_check'
                ]);
            }
            
            Log::info('Successfully scheduled delayed job for wait node', [
                'node_id' => $node['id'],
                'phone_number' => $phoneNumber,
                'flow_id' => $flowId,
                'next_node' => $nextNodeId,
                'wait_time' => $waitTime,
                'scheduled_at' => Carbon::now()->addSeconds($waitTime)->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to schedule delayed processing for wait node', [
                'node_id' => $node['id'],
                'next_node' => $nextNodeId,
                'wait_time' => $waitTime,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update active flow session to preserve state during wait
     *
     * @param string $phoneNumber
     * @param string $waitNodeId
     * @param string $nextNodeId
     * @param array $context
     * @return void
     */
    private function updateActiveFlowForWait($phoneNumber, $waitNodeId, $nextNodeId, $context)
    {
        try {
            $activeFlow = UserActiveFlow::getActiveFlow($phoneNumber);
            if ($activeFlow) {
                $activeFlowData = $activeFlow->__data ?? [];
                
                // Mark that we're waiting at this node
                $activeFlowData['waiting_at_node'] = $waitNodeId;
                $activeFlowData['scheduled_next_node'] = $nextNodeId;
                $activeFlowData['wait_scheduled_at'] = now();
                $activeFlowData['flow_context'] = $context;
                $activeFlowData['waiting_for_input'] = false; // Not waiting for user input, waiting for time
                
                // Update the current node to the wait node
                $activeFlow->current_node_uid = $waitNodeId;
                $activeFlow->__data = $activeFlowData;
                $activeFlow->save();
                
                Log::info('Updated active flow session for wait node', [
                    'phone_number' => $phoneNumber,
                    'wait_node_id' => $waitNodeId,
                    'scheduled_next_node' => $nextNodeId,
                    'wait_scheduled_at' => now()->toDateTimeString()
                ]);
            } else {
                Log::warning('No active flow found to update for wait node', [
                    'phone_number' => $phoneNumber,
                    'wait_node_id' => $waitNodeId
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update active flow for wait node', [
                'phone_number' => $phoneNumber,
                'wait_node_id' => $waitNodeId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Parse and validate wait time
     *
     * @param mixed $waitTime
     * @return int
     */
    protected function parseWaitTime($waitTime)
    {
        $waitTime = (int) $waitTime;
        
        // Ensure wait time is between 1 second and 1 week (in seconds)
        return max(1, min($waitTime, 604800));
    }

    /**
     * Validate wait node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['wait_time'])) {
            $errors[] = 'Wait time is required';
        } elseif (!is_numeric($payload['wait_time']) || $payload['wait_time'] <= 0) {
            $errors[] = 'Wait time must be a positive number';
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
        return false;
    }

    /**
     * Handle standard WhatsApp flow scenarios
     * Override to prevent no_input and no_match scenarios for wait nodes
     *
     * @param array $node
     * @param string|null $userInput
     * @param array $context
     * @return array|null
     */
    public function handleStandardFlowScenarios($node, $userInput, $context = [])
    {
        // Wait nodes don't need to handle standard scenarios
        return null;
    }

    /**
     * Get node type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'wait';
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
     * Get display information for wait node
     *
     * @param array $node
     * @return array
     */
    public function getDisplayInfo($node)
    {
        $waitTime = $this->parseWaitTime($node['payload']['wait_time'] ?? 0);
        $waitMessage = $node['payload']['wait_message'] ?? 'Please wait...';
        
        return [
            'title' => 'Wait',
            'description' => 'Wait for ' . $this->formatWaitTime($waitTime),
            'message' => $waitMessage,
            'icon' => 'clock',
            'color' => '#6c757d',
        ];
    }

    /**
     * Format wait time in human readable format
     *
     * @param int $seconds
     * @return string
     */
    protected function formatWaitTime($seconds)
    {
        $periods = [
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1
        ];

        $parts = [];
        
        foreach ($periods as $name => $divisor) {
            if ($seconds >= $divisor) {
                $value = floor($seconds / $divisor);
                $parts[] = $value . ' ' . $name . ($value > 1 ? 's' : '');
                $seconds %= $divisor;
            }
        }

        return implode(', ', $parts) ?: '0 seconds';
    }
}