<?php

/**
 * InstagramWebhookController.php - Webhook Controller file
 *
 * This file is part of the Instagram Service component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\InstagramService\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\InstagramService\InstagramServiceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstagramWebhookController extends BaseController
{
    /**
     * @var InstagramServiceEngine
     */
    protected $instagramServiceEngine;

    /**
     * Constructor
     */
    public function __construct(InstagramServiceEngine $instagramServiceEngine)
    {
        $this->instagramServiceEngine = $instagramServiceEngine;
    }

    /**
     * Handle Instagram webhook requests
     * This endpoint handles both verification (GET) and message events (POST)
     *
     * @param Request $request
     * @param string $vendorUid
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, $vendorUid)
    {
        // Set vendor context for this request
        $this->setVendorContext($vendorUid);

        // Handle webhook verification (GET request)
        if ($request->isMethod('GET')) {
            return $this->verifyWebhook($request);
        }

        // Handle incoming messages and events (POST request)
        if ($request->isMethod('POST')) {
            return $this->handleWebhookEvent($request);
        }

        return response('Method not allowed', 405);
    }

    /**
     * Verify Instagram webhook subscription
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    protected function verifyWebhook(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        Log::info('Instagram webhook verification attempt', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge,
            'vendor_id' => getVendorId()
        ]);

        // Get the verify token from vendor settings
        $verifyToken = getVendorSettings('instagram_app_secret');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            // Update webhook verification status
            updateVendorSettings('instagram_webhook_verified_at', now()->toDateTimeString());
            
            Log::info('Instagram webhook verified successfully', [
                'vendor_id' => getVendorId()
            ]);
            
            return response($challenge, 200);
        }

        Log::warning('Instagram webhook verification failed', [
            'mode' => $mode,
            'expected_token' => $verifyToken,
            'received_token' => $token,
            'vendor_id' => getVendorId()
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook events
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    protected function handleWebhookEvent(Request $request)
    {
        $webhookData = $request->all();
        
        Log::info('Instagram webhook event received', [
            'vendor_id' => getVendorId(),
            'data' => $webhookData
        ]);

        try {
            // Verify webhook signature if configured
            if (!$this->verifyWebhookSignature($request)) {
                Log::warning('Instagram webhook signature verification failed', [
                    'vendor_id' => getVendorId()
                ]);
                return response('Unauthorized', 401);
            }

            // Process the webhook data
            $processedData = $this->instagramServiceEngine->processIncomingWebhook($webhookData);

            if ($processedData->success()) {
                Log::info('Instagram webhook processed successfully', [
                    'vendor_id' => getVendorId()
                ]);
                return response('OK', 200);
            } else {
                Log::error('Instagram webhook processing failed', [
                    'vendor_id' => getVendorId(),
                    'error' => $processedData->message()
                ]);
                return response('Error processing webhook', 500);
            }

        } catch (\Exception $e) {
            Log::error('Instagram webhook exception', [
                'vendor_id' => getVendorId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Internal server error', 500);
        }
    }

    /**
     * Verify webhook signature for security
     *
     * @param Request $request
     * @return bool
     */
    protected function verifyWebhookSignature(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');
        
        if (!$signature) {
            // If no signature header, check if signature verification is required
            // For development, you might want to skip this
            return true;
        }

        $appSecret = getVendorSettings('instagram_app_secret');
        
        if (!$appSecret) {
            Log::warning('Instagram app secret not configured for signature verification', [
                'vendor_id' => getVendorId()
            ]);
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Instagram webhook signature mismatch', [
                'vendor_id' => getVendorId(),
                'expected' => $expectedSignature,
                'received' => $signature
            ]);
            return false;
        }

        return true;
    }

    /**
     * Set vendor context for the request
     *
     * @param string $vendorUid
     * @return void
     */
    protected function setVendorContext($vendorUid)
    {
        // Find vendor by UID and set context
        $vendor = \App\Yantrana\Components\Vendor\Models\VendorModel::where('_uid', $vendorUid)->first();
        
        if (!$vendor) {
            Log::error('Vendor not found for Instagram webhook', [
                'vendor_uid' => $vendorUid
            ]);
            abort(404, 'Vendor not found');
        }

        // Set vendor context in session or global state
        // This ensures getVendorId() returns the correct vendor ID
        session(['vendor_id' => $vendor->_id]);
        
        Log::info('Vendor context set for Instagram webhook', [
            'vendor_uid' => $vendorUid,
            'vendor_id' => $vendor->_id
        ]);
    }

    /**
     * Handle webhook field subscription verification
     * This is called when subscribing to specific webhook fields
     *
     * @param Request $request
     * @param string $vendorUid
     * @param string $field
     * @return \Illuminate\Http\Response
     */
    public function verifyField(Request $request, $vendorUid, $field)
    {
        $this->setVendorContext($vendorUid);

        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        Log::info('Instagram webhook field verification', [
            'field' => $field,
            'mode' => $mode,
            'vendor_id' => getVendorId()
        ]);

        $verifyToken = getVendorSettings('instagram_app_secret');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            // Update field-specific verification status
            $settingKey = "instagram_webhook_{$field}_field_verified_at";
            updateVendorSettings($settingKey, now()->toDateTimeString());
            
            Log::info('Instagram webhook field verified', [
                'field' => $field,
                'vendor_id' => getVendorId()
            ]);
            
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Get webhook status and configuration
     *
     * @param string $vendorUid
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus($vendorUid)
    {
        $this->setVendorContext($vendorUid);

        $status = [
            'webhook_verified' => !empty(getVendorSettings('instagram_webhook_verified_at')),
            'webhook_verified_at' => getVendorSettings('instagram_webhook_verified_at'),
            'messages_field_verified' => !empty(getVendorSettings('instagram_webhook_messages_field_verified_at')),
            'messages_field_verified_at' => getVendorSettings('instagram_webhook_messages_field_verified_at'),
            'webhook_url' => route('instagram.webhook', ['vendorUid' => $vendorUid]),
            'verify_token' => getVendorSettings('instagram_app_secret') ? '***' : null,
            'app_configured' => !empty(getVendorSettings('instagram_app_id')) && 
                              !empty(getVendorSettings('instagram_app_secret')) &&
                              !empty(getVendorSettings('instagram_access_token')) &&
                              !empty(getVendorSettings('instagram_page_id')),
        ];

        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * Test webhook connectivity
     *
     * @param string $vendorUid
     * @return \Illuminate\Http\JsonResponse
     */
    public function testWebhook($vendorUid)
    {
        $this->setVendorContext($vendorUid);

        // Test if Instagram API is properly configured
        $testResult = $this->instagramServiceEngine->testConnection();

        if ($testResult->failed()) {
            return response()->json([
                'success' => false,
                'message' => $testResult->message()
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __tr('Instagram webhook is ready to receive events'),
            'data' => [
                'webhook_url' => route('instagram.webhook', ['vendorUid' => $vendorUid]),
                'verify_token_configured' => !empty(getVendorSettings('instagram_app_secret')),
                'api_connection' => 'working'
            ]
        ]);
    }
}
