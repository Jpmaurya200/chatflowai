<?php

namespace App\Yantrana\Components\BotReply\Services;

use Illuminate\Support\Facades\Log;

class FlowDebugService
{
    /**
     * Log flow execution details
     *
     * @param string $stage
     * @param array $data
     * @return void
     */
    public static function log($stage, $data)
    {
        Log::channel('flow_debug')->info($stage, $data);
    }
    
    /**
     * Trace a complete flow execution
     *
     * @param string $flowId
     * @param string $contactId
     * @return array
     */
    public static function traceFlow($flowId, $contactId)
    {
        // Get all logs for this flow and contact
        $logs = self::getFlowLogs($flowId, $contactId);
        
        return [
            'flow_id' => $flowId,
            'contact_id' => $contactId,
            'logs' => $logs,
            'node_sequence' => self::extractNodeSequence($logs)
        ];
    }
    
    /**
     * Extract node execution sequence from logs
     *
     * @param array $logs
     * @return array
     */
    private static function extractNodeSequence($logs)
    {
        $sequence = [];
        
        foreach ($logs as $log) {
            if (isset($log['node_id'])) {
                $sequence[] = [
                    'node_id' => $log['node_id'],
                    'timestamp' => $log['timestamp'],
                    'type' => $log['type'] ?? 'unknown'
                ];
            }
        }
        
        return $sequence;
    }
}