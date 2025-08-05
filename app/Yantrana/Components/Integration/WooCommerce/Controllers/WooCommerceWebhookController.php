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
            Log::info('WooCommerce webhook received', [
                'vendor_id' => $vendorId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
                'user_agent' => $request->header('User-Agent'),
                'x_wc_webhook_topic' => $request->header('X-WC-Webhook-Topic'),
                'x_wc_webhook_signature' => $request->header('X-WC-Webhook-Signature'),
                'x_wc_webhook_id' => $request->header('X-WC-Webhook-Id'),
            ]);

            // If vendor_id is null, try to extract it from the webhook URL or payload
            if (!$vendorId) {
                // Check if this is a webhook verification or test request
                if ($request->has('challenge') || $request->header('X-WC-Webhook-Topic') === 'test') {
                    Log::info('WooCommerce webhook verification/test request', [
                        'challenge' => $request->get('challenge'),
                        'topic' => $request->header('X-WC-Webhook-Topic')
                    ]);
                    return response()->json(['success' => true], 200);
                }
                
                // For actual order webhooks, we need vendor_id
                Log::error('WooCommerce webhook received without vendor_id', [
                    'url' => $request->fullUrl(),
                    'payload' => $request->all()
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

            // Check if this is a test webhook
            if ($request->header('X-WC-Webhook-Topic') === 'test') {
                Log::info('WooCommerce test webhook received', [
                    'vendor_id' => $vendorId,
                    'payload' => $request->all()
                ]);
                return response()->json(['success' => true], 200);
            }

            // Process webhook
            $result = $this->integrationEngine->processIntegrationWebhook('woocommerce', $request, $vendorId);

            if ($result['success']) {
                Log::info('WooCommerce webhook processed successfully', [
                    'vendor_id' => $vendorId,
                    'topic' => $request->header('X-WC-Webhook-Topic'),
                    'result' => $result
                ]);
                return response()->json(['success' => true], 200);
            } else {
                Log::error('WooCommerce webhook processing failed', [
                    'vendor_id' => $vendorId,
                    'topic' => $request->header('X-WC-Webhook-Topic'),
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

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Request $request, $vendorId = null)
    {
        Log::info('WooCommerce webhook test endpoint called', [
            'vendor_id' => $vendorId,
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook test endpoint is accessible',
            'vendor_id' => $vendorId,
            'timestamp' => now()->toISOString()
        ]);
    }
} 