<?php
/**
 * ShopifyWebhookController.php - Controller file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Controllers;

use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Integration\IntegrationEngine;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends BaseController
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
     * Handle Shopify webhook
     */
    public function handleWebhook(Request $request, $vendorId = null)
    {
        try {
            Log::info('Shopify webhook received', [
                'vendor_id' => $vendorId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
                'user_agent' => $request->header('User-Agent'),
                'x_shopify_topic' => $request->header('X-Shopify-Topic'),
                'x_shopify_shop_domain' => $request->header('X-Shopify-Shop-Domain'),
                'x_shopify_webhook_id' => $request->header('X-Shopify-Webhook-Id'),
            ]);

            // Check if this is a webhook verification request
            if ($request->has('challenge')) {
                $challenge = $request->get('challenge');
                Log::info('Shopify webhook verification challenge', [
                    'challenge' => $challenge,
                    'vendor_id' => $vendorId
                ]);
                return response($challenge, 200, ['Content-Type' => 'text/plain']);
            }

            // Check if this is a test webhook
            if ($request->header('X-Shopify-Topic') === 'test') {
                Log::info('Shopify test webhook received', [
                    'vendor_id' => $vendorId,
                    'payload' => $request->all()
                ]);
                return response()->json(['success' => true], 200);
            }

            // Process webhook
            $result = $this->integrationEngine->processIntegrationWebhook('shopify', $request, $vendorId);

            if ($result['success']) {
                Log::info('Shopify webhook processed successfully', [
                    'vendor_id' => $vendorId,
                    'topic' => $request->header('X-Shopify-Topic'),
                    'result' => $result
                ]);
                return response()->json(['success' => true], 200);
            } else {
                Log::error('Shopify webhook processing failed', [
                    'vendor_id' => $vendorId,
                    'topic' => $request->header('X-Shopify-Topic'),
                    'error' => $result['message'] ?? 'Unknown error',
                    'result' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Webhook processing failed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Shopify webhook exception', [
                'vendor_id' => $vendorId,
                'topic' => $request->header('X-Shopify-Topic'),
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
        // Shopify sends a verification request when setting up webhooks
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
        Log::info('Shopify webhook test endpoint called', [
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