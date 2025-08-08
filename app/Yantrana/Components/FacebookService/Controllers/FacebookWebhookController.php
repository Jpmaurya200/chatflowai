<?php

namespace App\Yantrana\Components\FacebookService\Controllers;

use App\Yantrana\Base\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends BaseController
{
    /**
     * Handle Facebook webhook requests
     *
     * @param Request $request
     * @param string $vendorUid
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, $vendorUid)
    {
        // Verify webhook for GET requests (Facebook verification)
        if ($request->isMethod('GET')) {
            return $this->verifyWebhook($request, $vendorUid);
        }

        // Process webhook events for POST requests
        if ($request->isMethod('POST')) {
            return $this->processWebhook($request, $vendorUid);
        }

        return response('Method not allowed', 405);
    }

    /**
     * Verify Facebook webhook
     *
     * @param Request $request
     * @param string $vendorUid
     * @return \Illuminate\Http\Response
     */
    protected function verifyWebhook(Request $request, $vendorUid)
    {
        try {
            // Get vendor by UID
            $vendor = \App\Yantrana\Components\Vendor\Models\VendorModel::where('_uid', $vendorUid)->first();
            
            if (!$vendor) {
                Log::error("Facebook webhook verification failed: Vendor not found for UID: {$vendorUid}");
                return response('Vendor not found', 404);
            }

            // Set vendor context
            setVendorId($vendor->_id);

            $mode = $request->get('hub_mode');
            $token = $request->get('hub_verify_token');
            $challenge = $request->get('hub_challenge');

            // Get the expected verify token from vendor settings
            $expectedToken = getVendorSettings('facebook_webhook_verify_token');

            if ($mode === 'subscribe' && $token === $expectedToken) {
                Log::info("Facebook webhook verified successfully for vendor: {$vendorUid}");
                
                // Update webhook verification status
                $vendorSettingsEngine = app(\App\Yantrana\Components\Vendor\VendorSettingsEngine::class);
                $vendorSettingsEngine->updateProcess('facebook_api_setup', [
                    'facebook_webhook_verified_at' => now()
                ], $vendor->_id);

                return response($challenge, 200);
            }

            Log::error("Facebook webhook verification failed for vendor: {$vendorUid}. Mode: {$mode}, Token match: " . ($token === $expectedToken ? 'yes' : 'no'));
            return response('Forbidden', 403);

        } catch (\Exception $e) {
            Log::error("Facebook webhook verification error: " . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Process Facebook webhook events
     *
     * @param Request $request
     * @param string $vendorUid
     * @return \Illuminate\Http\Response
     */
    protected function processWebhook(Request $request, $vendorUid)
    {
        try {
            // Get vendor by UID
            $vendor = \App\Yantrana\Components\Vendor\Models\VendorModel::where('_uid', $vendorUid)->first();
            
            if (!$vendor) {
                Log::error("Facebook webhook processing failed: Vendor not found for UID: {$vendorUid}");
                return response('Vendor not found', 404);
            }

            // Set vendor context
            setVendorId($vendor->_id);

            // Verify webhook signature
            if (!$this->verifySignature($request)) {
                Log::error("Facebook webhook signature verification failed for vendor: {$vendorUid}");
                return response('Forbidden', 403);
            }

            $data = $request->json()->all();
            
            Log::info("Facebook webhook received for vendor: {$vendorUid}", ['data' => $data]);

            // Process each entry in the webhook
            foreach ($data['entry'] ?? [] as $entry) {
                $this->processEntry($entry, $vendor);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error("Facebook webhook processing error: " . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Verify webhook signature
     *
     * @param Request $request
     * @return bool
     */
    protected function verifySignature(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');
        
        if (!$signature) {
            return false;
        }

        $appSecret = getVendorSettings('facebook_app_secret');
        
        if (!$appSecret) {
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process a single webhook entry
     *
     * @param array $entry
     * @param object $vendor
     * @return void
     */
    protected function processEntry($entry, $vendor)
    {
        try {
            // Process messaging events
            foreach ($entry['messaging'] ?? [] as $messaging) {
                $this->processMessagingEvent($messaging, $vendor);
            }

        } catch (\Exception $e) {
            Log::error("Error processing Facebook webhook entry: " . $e->getMessage(), ['entry' => $entry]);
        }
    }

    /**
     * Process a messaging event
     *
     * @param array $messaging
     * @param object $vendor
     * @return void
     */
    protected function processMessagingEvent($messaging, $vendor)
    {
        try {
            $senderId = $messaging['sender']['id'] ?? null;
            $recipientId = $messaging['recipient']['id'] ?? null;
            $timestamp = $messaging['timestamp'] ?? null;

            // Skip if this is a message sent by the page (outgoing)
            $pageId = getVendorSettings('facebook_page_id');
            if ($senderId === $pageId) {
                return;
            }

            // Process incoming message
            if (isset($messaging['message'])) {
                $this->processIncomingMessage($messaging, $vendor);
            }

            // Process delivery confirmation
            if (isset($messaging['delivery'])) {
                $this->processDeliveryConfirmation($messaging, $vendor);
            }

            // Process read confirmation
            if (isset($messaging['read'])) {
                $this->processReadConfirmation($messaging, $vendor);
            }

        } catch (\Exception $e) {
            Log::error("Error processing Facebook messaging event: " . $e->getMessage(), ['messaging' => $messaging]);
        }
    }

    /**
     * Process incoming message
     *
     * @param array $messaging
     * @param object $vendor
     * @return void
     */
    protected function processIncomingMessage($messaging, $vendor)
    {
        $message = $messaging['message'];
        $senderId = $messaging['sender']['id'];
        
        Log::info("Facebook incoming message from {$senderId}: " . ($message['text'] ?? 'Media message'));

        // Here you can add logic to:
        // 1. Store the message in database
        // 2. Trigger real-time updates to the chat interface
        // 3. Process chatbot responses
        // 4. Send notifications to agents
        
        // For now, just log the message
        // In a full implementation, you would integrate with your chat system
    }

    /**
     * Process delivery confirmation
     *
     * @param array $messaging
     * @param object $vendor
     * @return void
     */
    protected function processDeliveryConfirmation($messaging, $vendor)
    {
        $delivery = $messaging['delivery'];
        Log::info("Facebook message delivery confirmed", ['delivery' => $delivery]);
    }

    /**
     * Process read confirmation
     *
     * @param array $messaging
     * @param object $vendor
     * @return void
     */
    protected function processReadConfirmation($messaging, $vendor)
    {
        $read = $messaging['read'];
        Log::info("Facebook message read confirmed", ['read' => $read]);
    }
}
