<?php
/**
 * WooCommerceWebhookController.php - Controller file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Controllers;

use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Integration\IntegrationEngine;
use Illuminate\Support\Facades\Log;

class WooCommerceWebhookController extends BaseController
{
    /**
     * @var IntegrationEngine - Integration Engine
     */
    protected $integrationEngine;

    /**
     * Allowed webhook topics
     */
    private const ALLOWED_TOPICS = [
        'order.created',
        'order.updated'
    ];

    /**
     * Constructor
     */
    public function __construct(IntegrationEngine $integrationEngine)
    {
        $this->integrationEngine = $integrationEngine;
    }

    /**
     * Handle WooCommerce webhook
     */
    public function handleWebhook(Request $request, $vendorId = null)
    {
        try {
            $webhookTopic = $request->header('X-WC-Webhook-Topic');
            
            Log::info('WooCommerce webhook received', [
                'vendor_id' => $vendorId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'topic' => $webhookTopic,
                'payload' => $request->all(),
            ]);

            // If vendor_id is null, return error
            if (!$vendorId) {
                Log::error('WooCommerce webhook received without vendor_id', [
                    'url' => $request->fullUrl(),
                    'topic' => $webhookTopic
                ]);
                return response()->json(['error' => 'Vendor ID is required'], 400);
            }

            // Check if this is a webhook verification request
            if ($request->has('challenge')) {
                $challenge = $request->get('challenge');
                Log::info('WooCommerce webhook verification challenge', [
                    'challenge' => $challenge,
                    'vendor_id' => $vendorId
                ]);
                return response($challenge, 200, ['Content-Type' => 'text/plain']);
            }

            // Validate webhook topic
            if (!in_array($webhookTopic, self::ALLOWED_TOPICS)) {
                Log::warning('WooCommerce webhook received with unsupported topic', [
                    'vendor_id' => $vendorId,
                    'topic' => $webhookTopic,
                    'allowed_topics' => self::ALLOWED_TOPICS
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported webhook topic'
                ], 400);
            }

            // Process webhook
            $result = $this->integrationEngine->processIntegrationWebhook('woocommerce', $request, $vendorId);

            if ($result['success']) {
                Log::info('WooCommerce webhook processed successfully', [
                    'vendor_id' => $vendorId,
                    'topic' => $webhookTopic,
                    'result' => $result
                ]);
                return response()->json(['success' => true], 200);
            } else {
                Log::error('WooCommerce webhook processing failed', [
                    'vendor_id' => $vendorId,
                    'topic' => $webhookTopic,
                    'error' => $result['message'] ?? 'Unknown error',
                    'result' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Webhook processing failed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('WooCommerce webhook exception', [
                'vendor_id' => $vendorId,
                'topic' => $request->header('X-WC-Webhook-Topic'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Handle webhook with vendor ID in URL
     */
    public function handleWebhookWithVendor(Request $request, $vendorId)
    {
        return $this->handleWebhook($request, $vendorId);
    }

    /**
     * Verify webhook endpoint
     */
    public function verifyWebhook(Request $request)
    {
        // WooCommerce sends a verification request when setting up webhooks
        $challenge = $request->get('challenge');
        
        if ($challenge) {
            return response($challenge, 200, ['Content-Type' => 'text/plain']);
        }

        return response()->json(['success' => true]);
    }
} 