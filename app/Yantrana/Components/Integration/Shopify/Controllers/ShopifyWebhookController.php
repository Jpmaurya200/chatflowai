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

            // Extract phone number from various sources
            $customerPhone = $this->extractPhoneNumber($payload, $vendorId);
            $customerEmail = $this->extractEmail($payload, $vendorId);
            $customerName = $this->extractCustomerName($payload, $vendorId);

            // Enhanced customer data logging for debugging
            if (isset($payload['customer'])) {
                Log::info('Customer data found in webhook payload', [
                    'customer_id' => $payload['customer']['id'] ?? 'N/A',
                    'customer_email' => $payload['customer']['email'] ?? 'N/A',
                    'customer_phone' => $payload['customer']['phone'] ?? 'N/A',
                    'customer_first_name' => $payload['customer']['first_name'] ?? 'N/A',
                    'customer_last_name' => $payload['customer']['last_name'] ?? 'N/A',
                    'extracted_phone' => $customerPhone,
                    'extracted_email' => $customerEmail,
                    'extracted_name' => $customerName,
                    'customer_keys' => array_keys($payload['customer'])
                ]);
            } else {
                Log::warning('No customer data found in webhook payload', [
                    'payload_keys' => array_keys($payload),
                    'order_id' => $payload['id'] ?? 'N/A',
                    'order_number' => $payload['order_number'] ?? $payload['name'] ?? 'N/A',
                    'extracted_phone' => $customerPhone,
                    'extracted_email' => $customerEmail,
                    'extracted_name' => $customerName
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

            // Add extracted customer data to payload for processing
            $payload['_extracted_customer_data'] = [
                'phone' => $customerPhone,
                'email' => $customerEmail,
                'name' => $customerName,
            ];

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
                    'result' => $result,
                    'customer_phone' => $customerPhone
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
     * Get complete customer data including phone number
     */
    private function getCompleteCustomerData($customerId, $vendorId)
    {
        try {
            // Get the Shopify service from the integration engine
            $shopifyService = $this->integrationEngine->getShopifyService();
            
            if (!$shopifyService) {
                Log::warning('Shopify service not available', ['vendor_id' => $vendorId]);
                return null;
            }

            // Use the fetchCustomerFromShopify method from ShopifyService
            $customer = $shopifyService->fetchCustomerFromShopify($customerId, $vendorId);
            
            if ($customer && is_array($customer)) {
                Log::info('Complete customer data retrieved', [
                    'customer_id' => $customerId,
                    'phone' => $customer['phone'] ?? 'N/A',
                    'email' => $customer['email'] ?? 'N/A',
                    'first_name' => $customer['first_name'] ?? 'N/A',
                    'last_name' => $customer['last_name'] ?? 'N/A'
                ]);
                
                return $customer;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve complete customer data', [
                'customer_id' => $customerId,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Extract phone number from various sources
     */
    private function extractPhoneNumber($payload, $vendorId = null)
    {
        // Check order level phone
        if (!empty($payload['phone'])) {
            Log::info('Phone found at order level', ['phone' => $payload['phone']]);
            return $payload['phone'];
        }
        
        // Check customer phone
        if (!empty($payload['customer']['phone'])) {
            Log::info('Phone found in customer data', ['phone' => $payload['customer']['phone']]);
            return $payload['customer']['phone'];
        }
        
        // Check billing address phone
        if (!empty($payload['billing_address']['phone'])) {
            Log::info('Phone found in billing address', ['phone' => $payload['billing_address']['phone']]);
            return $payload['billing_address']['phone'];
        }
        
        // Check shipping address phone
        if (!empty($payload['shipping_address']['phone'])) {
            Log::info('Phone found in shipping address', ['phone' => $payload['shipping_address']['phone']]);
            return $payload['shipping_address']['phone'];
        }

        // Check note attributes for phone
        if (!empty($payload['note_attributes'])) {
            foreach ($payload['note_attributes'] as $attribute) {
                if (in_array(strtolower($attribute['name']), ['phone', 'telephone', 'mobile', 'contact_phone'])) {
                    Log::info('Phone found in note attributes', ['phone' => $attribute['value']]);
                    return $attribute['value'];
                }
            }
        }
        
        // If no phone found in webhook, try API call
        if (isset($payload['customer']['id']) && $vendorId) {
            Log::info('Phone not found in webhook, attempting API call', [
                'customer_id' => $payload['customer']['id']
            ]);
            
            $completeCustomer = $this->getCompleteCustomerData($payload['customer']['id'], $vendorId);
            
            if ($completeCustomer && !empty($completeCustomer['phone'])) {
                Log::info('Phone retrieved via API call', ['phone' => $completeCustomer['phone']]);
                return $completeCustomer['phone'];
            }
            
            // Check default address from API response
            if ($completeCustomer && !empty($completeCustomer['default_address']['phone'])) {
                Log::info('Phone found in default address via API', ['phone' => $completeCustomer['default_address']['phone']]);
                return $completeCustomer['default_address']['phone'];
            }
        }
        
        Log::warning('No phone number found anywhere', [
            'customer_id' => $payload['customer']['id'] ?? 'N/A',
            'order_id' => $payload['id'] ?? 'N/A'
        ]);
        
        return null;
    }

    /**
     * Extract email from various sources
     */
    private function extractEmail($payload, $vendorId = null)
    {
        // Check order level email
        if (!empty($payload['email'])) {
            return $payload['email'];
        }
        
        // Check customer email
        if (!empty($payload['customer']['email'])) {
            return $payload['customer']['email'];
        }
        
        // Check billing address email
        if (!empty($payload['billing_address']['email'])) {
            return $payload['billing_address']['email'];
        }
        
        // Check shipping address email
        if (!empty($payload['shipping_address']['email'])) {
            return $payload['shipping_address']['email'];
        }

        // Check contact email
        if (!empty($payload['contact_email'])) {
            return $payload['contact_email'];
        }
        
        // If no email found in webhook, try API call
        if (isset($payload['customer']['id']) && $vendorId) {
            $completeCustomer = $this->getCompleteCustomerData($payload['customer']['id'], $vendorId);
            
            if ($completeCustomer && !empty($completeCustomer['email'])) {
                return $completeCustomer['email'];
            }
        }
        
        return null;
    }

    /**
     * Extract customer name from various sources
     */
    private function extractCustomerName($payload, $vendorId = null)
    {
        $firstName = '';
        $lastName = '';
        
        // Check customer name
        if (!empty($payload['customer']['first_name']) || !empty($payload['customer']['last_name'])) {
            $firstName = $payload['customer']['first_name'] ?? '';
            $lastName = $payload['customer']['last_name'] ?? '';
        }
        // Check billing address name
        elseif (!empty($payload['billing_address']['first_name']) || !empty($payload['billing_address']['last_name'])) {
            $firstName = $payload['billing_address']['first_name'] ?? '';
            $lastName = $payload['billing_address']['last_name'] ?? '';
        }
        // Check shipping address name
        elseif (!empty($payload['shipping_address']['first_name']) || !empty($payload['shipping_address']['last_name'])) {
            $firstName = $payload['shipping_address']['first_name'] ?? '';
            $lastName = $payload['shipping_address']['last_name'] ?? '';
        }
        // Try API call if no name found
        elseif (isset($payload['customer']['id']) && $vendorId) {
            $completeCustomer = $this->getCompleteCustomerData($payload['customer']['id'], $vendorId);
            
            if ($completeCustomer) {
                $firstName = $completeCustomer['first_name'] ?? '';
                $lastName = $completeCustomer['last_name'] ?? '';
            }
        }
        
        return trim($firstName . ' ' . $lastName);
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

        // Extract all customer data using our methods
        $extractedPhone = $this->extractPhoneNumber($payload, $vendorId);
        $extractedEmail = $this->extractEmail($payload, $vendorId);
        $extractedName = $this->extractCustomerName($payload, $vendorId);

        $customerData = [
            'order_id' => $payload['id'] ?? 'N/A',
            'order_number' => $payload['order_number'] ?? $payload['name'] ?? 'N/A',
            'customer_exists' => isset($payload['customer']),
            'customer_id' => $payload['customer']['id'] ?? 'N/A',
            
            // Raw webhook data
            'webhook_customer_email' => $payload['customer']['email'] ?? 'N/A',
            'webhook_customer_phone' => $payload['customer']['phone'] ?? 'N/A',
            'webhook_customer_first_name' => $payload['customer']['first_name'] ?? 'N/A',
            'webhook_customer_last_name' => $payload['customer']['last_name'] ?? 'N/A',
            'webhook_order_email' => $payload['email'] ?? 'N/A',
            'webhook_order_phone' => $payload['phone'] ?? 'N/A',
            
            // Extracted data (our methods)
            'extracted_phone' => $extractedPhone ?? 'N/A',
            'extracted_email' => $extractedEmail ?? 'N/A',
            'extracted_name' => $extractedName ?? 'N/A',
            
            // Address data
            'billing_address_exists' => isset($payload['billing_address']),
            'billing_phone' => $payload['billing_address']['phone'] ?? 'N/A',
            'billing_email' => $payload['billing_address']['email'] ?? 'N/A',
            'billing_first_name' => $payload['billing_address']['first_name'] ?? 'N/A',
            'billing_last_name' => $payload['billing_address']['last_name'] ?? 'N/A',
            'shipping_address_exists' => isset($payload['shipping_address']),
            'shipping_phone' => $payload['shipping_address']['phone'] ?? 'N/A',
            'shipping_email' => $payload['shipping_address']['email'] ?? 'N/A',
            'shipping_first_name' => $payload['shipping_address']['first_name'] ?? 'N/A',
            'shipping_last_name' => $payload['shipping_address']['last_name'] ?? 'N/A',
            
            // Note attributes check
            'note_attributes_exists' => isset($payload['note_attributes']) && !empty($payload['note_attributes']),
            'note_attributes' => $payload['note_attributes'] ?? [],
        ];

        return response()->json([
            'success' => true,
            'customer_data' => $customerData,
            'vendor_id' => $vendorId,
            'timestamp' => now()->toISOString(),
            'summary' => [
                'phone_available' => !is_null($extractedPhone),
                'email_available' => !is_null($extractedEmail),
                'name_available' => !empty($extractedName),
                'phone_source' => $this->identifyPhoneSource($payload),
                'email_source' => $this->identifyEmailSource($payload),
            ]
        ]);
    }

    /**
     * Identify where phone number is coming from
     */
    private function identifyPhoneSource($payload)
    {
        if (!empty($payload['phone'])) return 'order_level';
        if (!empty($payload['customer']['phone'])) return 'customer';
        if (!empty($payload['billing_address']['phone'])) return 'billing_address';
        if (!empty($payload['shipping_address']['phone'])) return 'shipping_address';
        
        if (!empty($payload['note_attributes'])) {
            foreach ($payload['note_attributes'] as $attribute) {
                if (in_array(strtolower($attribute['name']), ['phone', 'telephone', 'mobile', 'contact_phone'])) {
                    return 'note_attributes';
                }
            }
        }
        
        return 'api_call_required';
    }

    /**
     * Identify where email is coming from
     */
    private function identifyEmailSource($payload)
    {
        if (!empty($payload['email'])) return 'order_level';
        if (!empty($payload['customer']['email'])) return 'customer';
        if (!empty($payload['billing_address']['email'])) return 'billing_address';
        if (!empty($payload['shipping_address']['email'])) return 'shipping_address';
        if (!empty($payload['contact_email'])) return 'contact_email';
        
        return 'api_call_required';
    }

    /**
     * Get extracted customer data for use in other parts of your application
     */
    public function getExtractedCustomerData($payload, $vendorId = null)
    {
        return [
            'phone' => $this->extractPhoneNumber($payload, $vendorId),
            'email' => $this->extractEmail($payload, $vendorId),
            'name' => $this->extractCustomerName($payload, $vendorId),
            'customer_id' => $payload['customer']['id'] ?? null,
        ];
    }
}