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
            $payload = $request->all();
            $topic = $request->header('X-Shopify-Topic');
            $shopDomain = $request->header('X-Shopify-Shop-Domain');

            Log::info('Shopify webhook received', [
                'vendor_id' => $vendorId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
                'user_agent' => $request->header('User-Agent'),
                'x_shopify_topic' => $topic,
                'x_shopify_shop_domain' => $shopDomain,
                'x_shopify_webhook_id' => $request->header('X-Shopify-Webhook-Id'),
            ]);

            // Enhanced customer data logging for debugging
            if (isset($payload['customer'])) {
                Log::info('Customer data found in webhook payload', [
                    'customer_id' => $payload['customer']['id'] ?? 'N/A',
                    'customer_email' => $payload['customer']['email'] ?? 'N/A',
                    'customer_phone' => $payload['customer']['phone'] ?? 'N/A',
                    'customer_first_name' => $payload['customer']['first_name'] ?? 'N/A',
                    'customer_last_name' => $payload['customer']['last_name'] ?? 'N/A',
                    'customer_keys' => array_keys($payload['customer'])
                ]);
            } else {
                Log::warning('No customer data found in webhook payload', [
                    'payload_keys' => array_keys($payload),
                    'order_id' => $payload['id'] ?? 'N/A',
                    'order_number' => $payload['order_number'] ?? $payload['name'] ?? 'N/A'
                ]);
            }

            // Check billing and shipping address data
            if (isset($payload['billing_address'])) {
                Log::info('Billing address data found', [
                    'billing_phone' => $payload['billing_address']['phone'] ?? 'N/A',
                    'billing_email' => $payload['billing_address']['email'] ?? 'N/A',
                    'billing_first_name' => $payload['billing_address']['first_name'] ?? 'N/A',
                    'billing_last_name' => $payload['billing_address']['last_name'] ?? 'N/A'
                ]);
            }

            if (isset($payload['shipping_address'])) {
                Log::info('Shipping address data found', [
                    'shipping_phone' => $payload['shipping_address']['phone'] ?? 'N/A',
                    'shipping_email' => $payload['shipping_address']['email'] ?? 'N/A',
                    'shipping_first_name' => $payload['shipping_address']['first_name'] ?? 'N/A',
                    'shipping_last_name' => $payload['shipping_address']['last_name'] ?? 'N/A'
                ]);
            }

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
            if ($topic === 'test') {
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
                    'topic' => $topic,
                    'result' => $result
                ]);
                return response()->json(['success' => true], 200);
            } else {
                Log::error('Shopify webhook processing failed', [
                    'vendor_id' => $vendorId,
                    'topic' => $topic,
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

    /**
     * Debug customer data endpoint
     */
    public function debugCustomerData(Request $request, $vendorId = null)
    {
        $payload = $request->all();
        
        Log::info('Debug customer data request', [
            'vendor_id' => $vendorId,
            'payload' => $payload
        ]);

        $customerData = [
            'order_id' => $payload['id'] ?? 'N/A',
            'order_number' => $payload['order_number'] ?? $payload['name'] ?? 'N/A',
            'customer_exists' => isset($payload['customer']),
            'customer_id' => $payload['customer']['id'] ?? 'N/A',
            'customer_email' => $payload['customer']['email'] ?? 'N/A',
            'customer_phone' => $payload['customer']['phone'] ?? 'N/A',
            'customer_first_name' => $payload['customer']['first_name'] ?? 'N/A',
            'customer_last_name' => $payload['customer']['last_name'] ?? 'N/A',
            'order_email' => $payload['email'] ?? 'N/A',
            'order_phone' => $payload['phone'] ?? 'N/A',
            'billing_address_exists' => isset($payload['billing_address']),
            'billing_phone' => $payload['billing_address']['phone'] ?? 'N/A',
            'billing_email' => $payload['billing_address']['email'] ?? 'N/A',
            'shipping_address_exists' => isset($payload['shipping_address']),
            'shipping_phone' => $payload['shipping_address']['phone'] ?? 'N/A',
            'shipping_email' => $payload['shipping_address']['email'] ?? 'N/A',
        ];

        return response()->json([
            'success' => true,
            'customer_data' => $customerData,
            'vendor_id' => $vendorId,
            'timestamp' => now()->toISOString()
        ]);
    }
} 