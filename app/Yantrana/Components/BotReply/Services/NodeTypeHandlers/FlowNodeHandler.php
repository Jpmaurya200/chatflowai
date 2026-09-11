<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use App\Yantrana\Components\Flows\Services\WhatsAppFlowService;
use Illuminate\Support\Facades\Log;

class FlowNodeHandler extends BaseNodeHandler
{
    /**
     * @var WhatsAppFlowService
     */
    protected $flowService;

    public function __construct()
    {
        parent::__construct();
        $this->flowService = app(WhatsAppFlowService::class);
    }

    /**
     * Process the flow node.
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'] ?? [];
        $flowId = $payload['whatsapp_flow_id'] ?? null;

        $contact = $context['contact'] ?? null;
        $waId = $contact->wa_id ?? ($context['wa_id'] ?? null);

        $result = [
            'type' => 'flow',
            'node_id' => $node['id'] ?? null,
            'requires_input' => false,
            'next_node' => $payload['next_node'] ?? null,
            'failed_next_node' => $payload['failed_next_node'] ?? null,
            'flow_response' => null,
            'whatsapp_flow_id' => $flowId,
        ];

        if (empty($flowId) || empty($waId)) {
            $result['error'] = 'Missing flow configuration or contact destination.';
            Log::warning('FlowNodeHandler: missing flow id or recipient', [
                'node_id' => $node['id'] ?? null,
                'flow_id' => $flowId,
                'wa_id' => $waId,
            ]);

            $result['next_node'] = $payload['failed_next_node'] ?? null;

            return $result;
        }

        $options = [
            'header_text' => $payload['header_text'] ?? '',
            'body_text' => $payload['body_text'] ?? '',
            'footer_text' => $payload['footer_text'] ?? '',
        ];

        $sendResult = $this->flowService->sendFlowMessage($flowId, $waId, $options);
        $result['flow_response'] = $sendResult;

        if (!$sendResult['success']) {
            $result['error'] = $sendResult['error'] ?? 'Failed to send WhatsApp flow.';
            $result['next_node'] = $payload['failed_next_node'] ?? null;
        }

        return $result;
    }

    /**
     * Validate payload.
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];

        if (empty($payload['whatsapp_flow_id'])) {
            $errors[] = 'WhatsApp flow ID is required';
        }

        return $errors;
    }

    /**
     * Determine next node.
     *
     * @param array       $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        $payload = $node['payload'] ?? [];

        if ($userInput === 'delivery_failed') {
            return $payload['failed_next_node'] ?? null;
        }

        return $payload['next_node'] ?? null;
    }

    /**
     * Node type identifier.
     *
     * @return string
     */
    public function getType()
    {
        return 'flow';
    }
}

