<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * Handler for webhook type nodes
 */
class WebhookNodeHandler extends BaseNodeHandler
{
    /**
     * Process the webhook node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        try {
            $payload = $node['payload'] ?? [];
            $webhookUrl = $payload['webhook_url'] ?? '';
            $httpMethod = strtoupper($payload['http_method'] ?? 'POST');
            $requestBody = $payload['request_body'] ?? '{}';
            $responseMapping = $payload['response_mapping'] ?? [];
            $timeoutSeconds = $payload['timeout'] ?? 30;

            Log::info('Processing webhook node', [
                'node_id' => $node['id'] ?? 'unknown',
                'webhook_url' => $webhookUrl,
                'http_method' => $httpMethod,
                'contact_uid' => $context['contact_uid'] ?? 'unknown'
            ]);

            // Validate webhook URL
            if (empty($webhookUrl) || !filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
                // Get failed next node from payload (set during flow conversion)
                $failedNextNode = $payload['failed_next_node'] ?? null;

                Log::error('Invalid webhook URL', [
                    'node_id' => $node['id'] ?? 'unknown',
                    'webhook_url' => $webhookUrl,
                    'failed_next_node' => $failedNextNode
                ]);

                return [
                    'type' => 'webhook',
                    'requires_input' => false,
                    'next_node' => $failedNextNode,
                    'node_id' => $node['id'] ?? 'unknown',
                    'webhook_error' => 'Invalid webhook URL',
                    'webhook_result' => 'delivery_failed',
                    'is_terminal' => $failedNextNode === null
                ];
            }

            // Process dynamic variables in request body
            $variables = $this->getAllVariables($context);

            // Add additional webhook-specific variables
            $variables['user_input'] = $context['user_input'] ?? $context['last_user_input'] ?? '';
            $variables['contact_name'] = $variables['full_name'] ?? '';
            $variables['contact_phone'] = $variables['phone_number'] ?? '';

            Log::debug('Webhook variables available', [
                'node_id' => $node['id'] ?? 'unknown',
                'variables' => array_keys($variables),
                'context_keys' => array_keys($context)
            ]);

            $processedRequestBody = $this->processDynamicVariables($requestBody, $variables);

            // Prepare request data
            $requestData = [];
            if (!empty($processedRequestBody)) {
                $requestData = json_decode($processedRequestBody, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // If JSON decode fails, treat as plain text
                    $requestData = ['message' => $processedRequestBody];
                }
            }

            // Add context data to request
            $requestData['context'] = [
                'contact_uid' => $context['contact_uid'] ?? null,
                'contact_phone' => $context['contact_phone'] ?? null,
                'vendor_id' => $context['vendor_id'] ?? null,
                'flow_id' => $context['flow_id'] ?? null,
                'node_id' => $node['id'] ?? null,
                'timestamp' => now()->toISOString()
            ];

            // Make webhook request
            $response = $this->makeWebhookRequest($webhookUrl, $httpMethod, $requestData, $timeoutSeconds);

            if ($response['success']) {
                // Process response and update context
                $updatedContext = $this->processWebhookResponse($response['data'], $responseMapping, $context);

                // Get next node from payload (set during flow conversion)
                $successNextNode = $payload['next_node'] ?? $payload['failed_next_node'] ?? null;

                Log::info('Webhook executed successfully', [
                    'node_id' => $node['id'] ?? 'unknown',
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response['status_code'] ?? 'unknown',
                    'success_next_node' => $successNextNode
                ]);

                return [
                    'type' => 'webhook',
                    'requires_input' => false,
                    'next_node' => $successNextNode,
                    'node_id' => $node['id'] ?? 'unknown',
                    'webhook_response' => $response['data'],
                    'webhook_result' => 'success',
                    'context' => $updatedContext,
                    'is_terminal' => $successNextNode === null
                ];
            } else {
                // Get failed next node from payload (set during flow conversion)
                $failedNextNode = $payload['failed_next_node'] ?? null;

                Log::error('Webhook execution failed', [
                    'node_id' => $node['id'] ?? 'unknown',
                    'webhook_url' => $webhookUrl,
                    'error' => $response['error'],
                    'failed_next_node' => $failedNextNode
                ]);

                return [
                    'type' => 'webhook',
                    'requires_input' => false,
                    'next_node' => $failedNextNode,
                    'node_id' => $node['id'] ?? 'unknown',
                    'webhook_error' => $response['error'],
                    'webhook_result' => 'delivery_failed',
                    'is_terminal' => $failedNextNode === null
                ];
            }

        } catch (Exception $e) {
            // Get failed next node from payload (set during flow conversion)
            $failedNextNode = $payload['failed_next_node'] ?? null;

            Log::error('Error processing webhook node', [
                'node_id' => $node['id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'failed_next_node' => $failedNextNode
            ]);

            return [
                'type' => 'webhook',
                'requires_input' => false,
                'next_node' => $failedNextNode,
                'node_id' => $node['id'] ?? 'unknown',
                'webhook_error' => $e->getMessage(),
                'webhook_result' => 'delivery_failed',
                'is_terminal' => $failedNextNode === null
            ];
        }
    }

    /**
     * Make HTTP request to webhook URL
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param int $timeout
     * @return array
     */
    private function makeWebhookRequest($url, $method, $data, $timeout = 30)
    {
        try {
            $httpClient = Http::timeout($timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'WhatsApp-Bot-Webhook/1.0'
                ]);

            $response = null;
            switch ($method) {
                case 'GET':
                    $response = $httpClient->get($url, $data);
                    break;
                case 'POST':
                    $response = $httpClient->post($url, $data);
                    break;
                case 'PUT':
                    $response = $httpClient->put($url, $data);
                    break;
                case 'PATCH':
                    $response = $httpClient->patch($url, $data);
                    break;
                case 'DELETE':
                    $response = $httpClient->delete($url, $data);
                    break;
                default:
                    throw new Exception("Unsupported HTTP method: {$method}");
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json() ?? $response->body(),
                    'status_code' => $response->status(),
                    'headers' => $response->headers()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}: {$response->body()}",
                    'status_code' => $response->status()
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => null
            ];
        }
    }

    /**
     * Process webhook response and map to context variables
     *
     * @param mixed $responseData
     * @param array $responseMapping
     * @param array $context
     * @return array
     */
    private function processWebhookResponse($responseData, $responseMapping, $context)
    {
        if (empty($responseMapping) || !is_array($responseMapping)) {
            // If no mapping provided, store entire response
            $context['webhook_response'] = $responseData;
            return $context;
        }

        // Process response mapping
        foreach ($responseMapping as $mapping) {
            $sourcePath = $mapping['source_path'] ?? '';
            $targetVariable = $mapping['target_variable'] ?? '';

            if (empty($sourcePath) || empty($targetVariable)) {
                continue;
            }

            // Extract value from response using dot notation
            $value = $this->extractValueFromPath($responseData, $sourcePath);
            
            if ($value !== null) {
                $context[$targetVariable] = $value;
            }
        }

        return $context;
    }

    /**
     * Extract value from nested array/object using dot notation
     *
     * @param mixed $data
     * @param string $path
     * @return mixed
     */
    private function extractValueFromPath($data, $path)
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_array($current) && isset($current[$key])) {
                $current = $current[$key];
            } elseif (is_object($current) && isset($current->$key)) {
                $current = $current->$key;
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Get next node ID based on webhook result
     *
     * @param array $node
     * @param string|null $userInput
     * @param array $context
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null, $context = [])
    {
        // Check if webhook result is available in context
        $webhookResult = $context['webhook_result'] ?? null;
        
        if ($webhookResult) {
            return $this->getNextNodeForOutput($node, $webhookResult, $context);
        }
        
        // Fallback to default next_node for backward compatibility
        return $node['payload']['next_node'] ?? null;
    }

    /**
     * Get next node for specific output type
     *
     * @param array $node
     * @param string $outputType
     * @param array $context
     * @return string|null
     */
    private function getNextNodeForOutput($node, $outputType, $context = [])
    {
        $payload = $node['payload'] ?? [];
        
        Log::info('Getting next node for webhook output', [
            'node_id' => $node['id'] ?? 'unknown',
            'output_type' => $outputType,
            'payload_keys' => array_keys($payload)
        ]);
        
        // Handle different output types using simple payload-based approach
        switch ($outputType) {
            case 'success':
                // For success, use the main next_node (this is the primary flow connection)
                $nextNode = $payload['next_node'] ?? null;
                Log::info('Using next_node for success output', [
                    'node_id' => $node['id'] ?? 'unknown',
                    'next_node' => $nextNode
                ]);
                return $nextNode;
                
            case 'delivery_failed':
                // For delivery_failed, use failed_next_node if configured, otherwise end flow
                $nextNode = $payload['failed_next_node'] ?? null;
                Log::info('Using failed_next_node for delivery_failed output', [
                    'node_id' => $node['id'] ?? 'unknown',
                    'next_node' => $nextNode
                ]);
                return $nextNode;
                
            default:
                Log::warning('Unknown webhook output type', [
                    'node_id' => $node['id'] ?? 'unknown',
                    'output_type' => $outputType
                ]);
                return null;
        }
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
        return 'webhook';
    }

    /**
     * Check if this is a terminal node (end of flow)
     *
     * @param array $node
     * @return bool
     */
    public function isTerminal($node)
    {
        $payload = $node['payload'] ?? [];
        $outputs = $payload['outputs'] ?? [];
        
        // Check if any output has a next_node configured
        foreach ($outputs as $output) {
            if (!empty($output['next_node'])) {
                return false;
            }
        }
        
        // Fallback to checking default next_node for backward compatibility
        return empty($payload['next_node']);
    }

    /**
     * Get display information for webhook node
     *
     * @param array $node
     * @return array
     */
    public function getDisplayInfo($node)
    {
        $webhookUrl = $node['payload']['webhook_url'] ?? 'Not configured';
        $httpMethod = $node['payload']['http_method'] ?? 'POST';
        $payload = $node['payload'] ?? [];
        $outputs = $payload['outputs'] ?? [];
        
        // Build description with output information
        $description = "{$httpMethod} {$webhookUrl}";
        if (!empty($outputs)) {
            $outputLabels = [];
            foreach ($outputs as $outputKey => $output) {
                $outputLabels[] = $output['label'] ?? ucfirst($outputKey);
            }
            $description .= " → " . implode(', ', $outputLabels);
        }
        
        return [
            'title' => 'Webhook',
            'description' => $description,
            'message' => 'Calls external webhook URL with conditional routing',
            'icon' => 'globe',
            'color' => '#17a2b8',
            'outputs' => [
                'success' => [
                    'label' => 'Success',
                    'label_id' => '25ef1f90-989e-4e7a-929a-d4d1db3e7184'
                ],
                'delivery_failed' => [
                    'label' => 'Failed',
                    'label_id' => 'a10517e2-29d0-4d72-a401-403f71709a63'
                ]
            ]
        ];
    }

    /**
     * Validate webhook node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['webhook_url'])) {
            $errors[] = 'Webhook URL is required';
        } elseif (!filter_var($payload['webhook_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid webhook URL format';
        }

        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $httpMethod = strtoupper($payload['http_method'] ?? 'POST');
        if (!in_array($httpMethod, $allowedMethods)) {
            $errors[] = 'Invalid HTTP method. Allowed: ' . implode(', ', $allowedMethods);
        }

        return $errors;
    }
}