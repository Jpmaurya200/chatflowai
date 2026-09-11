<?php

namespace App\Yantrana\Components\Flows\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppFlowService
{
    /**
     * List WhatsApp flows for the current vendor.
     *
     * @param  bool       $forceRefresh
     * @param  int|null   $vendorId
     * @return array      ['flows' => [], 'error' => null]
     */
    public function listFlows(bool $forceRefresh = false, ?int $vendorId = null): array
    {
        $vendorId = $vendorId ?? getVendorId();

        if (empty($vendorId)) {
            return [
                'flows' => [],
                'error' => __tr('Unable to determine current vendor.'),
            ];
        }

        $cacheKey = "vendor:{$vendorId}:whatsapp_flows_list";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($vendorId) {
            try {
                $wabaId = getVendorSettings('whatsapp_business_account_id', null, null, $vendorId);

                if (empty($wabaId)) {
                    return [
                        'flows' => [],
                        'error' => __tr('WhatsApp Business Account ID is not configured.'),
                    ];
                }

                $response = $this->callWhatsAppApi("{$wabaId}/flows", [], 'GET', $vendorId);

                if (!empty($response) && isset($response['data'])) {
                    return [
                        'flows' => $response['data'],
                        'error' => null,
                    ];
                }

                $errorMessage = $response['error']['message'] ?? __tr('Failed to fetch WhatsApp flows.');

                return [
                    'flows' => [],
                    'error' => $errorMessage,
                ];
            } catch (\Exception $e) {
                Log::error('WhatsAppFlowService:listFlows exception', [
                    'vendor_id' => $vendorId,
                    'message' => $e->getMessage(),
                ]);

                return [
                    'flows' => [],
                    'error' => __tr('Unable to fetch WhatsApp flows. Please try again later.'),
                ];
            }
        });
    }

    /**
     * Fetch a specific flow detail.
     *
     * @param  string     $flowId
     * @param  int|null   $vendorId
     * @return array|null
     */
    public function getFlowDetails(string $flowId, ?int $vendorId = null): ?array
    {
        if (empty($flowId)) {
            return null;
        }

        try {
            $response = $this->callWhatsAppApi($flowId, [], 'GET', $vendorId);

            return !empty($response) && !isset($response['error'])
                ? $response
                : null;
        } catch (\Exception $e) {
            Log::error('WhatsAppFlowService:getFlowDetails exception', [
                'flow_id' => $flowId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Send a WhatsApp flow message to a single phone number.
     *
     * @param  string     $flowId
     * @param  string     $phoneNumber
     * @param  array      $options ['header_text', 'body_text', 'footer_text']
     * @param  int|null   $vendorId
     * @return array      ['success' => bool, 'response' => mixed, 'error' => string|null]
     */
    public function sendFlowMessage(string $flowId, string $phoneNumber, array $options = [], ?int $vendorId = null): array
    {
        $vendorId = $vendorId ?? getVendorId();
        $accessToken = getVendorSettings('whatsapp_access_token', null, null, $vendorId);
        $phoneNumberId = getVendorSettings('current_phone_number_id', null, null, $vendorId);

        if (empty($vendorId) || empty($accessToken) || empty($phoneNumberId)) {
            return [
                'success' => false,
                'response' => null,
                'error' => __tr('WhatsApp API credentials are missing.'),
            ];
        }

        try {
            $flowDetails = $this->getFlowDetails($flowId, $vendorId) ?? [];
            $isDraft = ($flowDetails['status'] ?? '') === 'DRAFT';
            $payload = $this->buildFlowMessagePayload($flowId, $phoneNumber, $options, $isDraft, $vendorId, $flowDetails);

            $endpoint = "https://graph.facebook.com/v17.0/{$phoneNumberId}/messages";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                    'error' => null,
                ];
            }

            Log::error('WhatsAppFlowService:sendFlowMessage error', [
                'vendor_id' => $vendorId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'success' => false,
                'response' => $response->json(),
                'error' => $response->json()['error']['message'] ?? __tr('Failed to send WhatsApp flow.'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsAppFlowService:sendFlowMessage exception', [
                'vendor_id' => $vendorId,
                'flow_id' => $flowId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'response' => null,
                'error' => __tr('Unexpected error occurred while sending the flow.'),
            ];
        }
    }

    /**
     * Build WhatsApp flow message payload.
     *
     * @param  string   $flowId
     * @param  string   $phoneNumber
     * @param  array    $options
     * @param  bool     $isDraft
     * @param  int      $vendorId
     * @param  array    $flowDetails
     * @return array
     */
    protected function buildFlowMessagePayload(string $flowId, string $phoneNumber, array $options, bool $isDraft, int $vendorId, array $flowDetails): array
    {
        $headerText = $options['header_text'] ?? 'Hi!';
        $bodyText = $options['body_text'] ?? 'Please review the flow.';
        $footerText = $options['footer_text'] ?? 'Tap to continue.';

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'recipient_type' => 'individual',
            'type' => 'interactive',
            'interactive' => [
                'type' => 'flow',
                'header' => [
                    'type' => 'text',
                    'text' => $headerText,
                ],
                'body' => [
                    'text' => $bodyText,
                ],
                'footer' => [
                    'text' => $footerText,
                ],
                'action' => [
                    'name' => 'flow',
                    'parameters' => [
                        'flow_message_version' => '3',
                        'flow_action' => 'navigate',
                        'flow_id' => $flowId,
                        'flow_cta' => $options['cta_text'] ?? __tr('Open Flow'),
                    ],
                ],
            ],
        ];

        if ($isDraft) {
            $previewToken = $this->getPreviewToken($flowId, $vendorId);

            $payload['interactive']['header']['text'] = __tr('Flow preview');
            $payload['interactive']['body']['text'] = __tr('Preview mode - not visible to customers');
            $payload['interactive']['footer']['text'] = __tr('Draft mode');

            $payload['interactive']['action']['parameters']['mode'] = 'draft';
            $payload['interactive']['action']['parameters']['flow_token'] = $previewToken;
        }

        return $payload;
    }

    /**
     * Fetch preview token for draft flows.
     *
     * @param  string     $flowId
     * @param  int|null   $vendorId
     * @return string|null
     */
    public function getPreviewToken(string $flowId, ?int $vendorId = null): ?string
    {
        try {
            $response = $this->callWhatsAppApi($flowId, [
                'fields' => 'preview.invalidate(true)',
            ], 'GET', $vendorId);

            $previewUrl = $response['preview']['preview_url'] ?? null;

            if (!$previewUrl) {
                return null;
            }

            $parsedUrl = parse_url($previewUrl);
            if (!isset($parsedUrl['query'])) {
                return null;
            }

            parse_str($parsedUrl['query'], $queryParams);

            return $queryParams['preview_token'] ?? $queryParams['token'] ?? null;
        } catch (\Exception $e) {
            Log::error('WhatsAppFlowService:getPreviewToken exception', [
                'flow_id' => $flowId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Low-level WhatsApp API caller.
     *
     * @param  string     $endpoint
     * @param  array      $params
     * @param  string     $method
     * @param  int|null   $vendorId
     * @return array|null
     */
    protected function callWhatsAppApi(string $endpoint, array $params = [], string $method = 'GET', ?int $vendorId = null): ?array
    {
        $vendorId = $vendorId ?? getVendorId();
        $accessToken = getVendorSettings('whatsapp_access_token', null, null, $vendorId);

        if (empty($vendorId) || empty($accessToken)) {
            Log::warning('WhatsAppFlowService:callWhatsAppApi missing credentials', [
                'vendor_id' => $vendorId,
            ]);

            return null;
        }

        $baseUrl = 'https://graph.facebook.com/v17.0/';
        $fullUrl = $this->buildEndpointUrl($baseUrl, $endpoint);

        Log::info('WhatsAppFlowService:callWhatsAppApi request', [
            'vendor_id' => $vendorId,
            'endpoint' => $endpoint,
            'full_url' => $fullUrl,
            'method' => $method,
        ]);

        $http = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ]);

        if ($method === 'POST') {
            $http->withHeaders(['Content-Type' => 'application/json']);
            $response = $http->post($fullUrl, $params);
        } elseif ($method === 'DELETE') {
            $response = $http->delete($fullUrl, $params);
        } else {
            $response = $http->get($fullUrl, $params);
        }

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('WhatsAppFlowService:callWhatsAppApi error', [
            'vendor_id' => $vendorId,
            'status' => $response->status(),
            'endpoint' => $fullUrl,
            'body' => $response->json(),
        ]);

        return $response->json();
    }

    /**
     * Helper to build endpoint url safely.
     *
     * @param  string $baseUrl
     * @param  string $endpoint
     * @return string
     */
    protected function buildEndpointUrl(string $baseUrl, string $endpoint): string
    {
        if (empty($endpoint)) {
            return rtrim($baseUrl, '/');
        }

        if (str_starts_with($endpoint, 'http')) {
            return $endpoint;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Clear cached flow list.
     *
     * @param  int|null $vendorId
     * @return void
     */
    public function clearCachedFlows(?int $vendorId = null): void
    {
        $vendorId = $vendorId ?? getVendorId();

        if ($vendorId) {
            Cache::forget("vendor:{$vendorId}:whatsapp_flows_list");
        }
    }
}

