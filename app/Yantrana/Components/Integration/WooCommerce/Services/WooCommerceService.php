<?php
/**
 * WooCommerceService.php - Service file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Yantrana\Components\WhatsAppService\WhatsAppServiceEngine;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppApiService;
use App\Yantrana\Components\Contact\Repositories\ContactRepository;
use App\Yantrana\Components\Integration\WooCommerce\Repositories\WooCommerceIntegrationRepository;
use App\Yantrana\Components\Integration\WooCommerce\Repositories\WooCommerceOrderRepository;
use App\Yantrana\Components\Integration\WooCommerce\Repositories\WooCommerceOrderNotificationRepository;
use App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceIntegrationModel;
use App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceOrderModel;
use App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceOrderNotificationModel;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository;

class WooCommerceService
{
    /**
     * @var WhatsAppServiceEngine - WhatsApp Service Engine
     */
    protected $whatsAppServiceEngine;

    /**
     * @var WhatsAppApiService - WhatsApp API Service
     */
    protected $whatsAppApiService;

    /**
     * @var ContactRepository - Contact Repository
     */
    protected $contactRepository;

    /**
     * @var WooCommerceIntegrationRepository - WooCommerce Integration Repository
     */
    protected $wooCommerceIntegrationRepository;

    /**
     * @var WooCommerceOrderRepository - WooCommerce Order Repository
     */
    protected $wooCommerceOrderRepository;

    /**
     * @var WooCommerceOrderNotificationRepository - WooCommerce Order Notification Repository
     */
    protected $wooCommerceOrderNotificationRepository;

    /**
     * @var WhatsAppTemplateRepository - WhatsApp Template Repository
     */
    protected $whatsAppTemplateRepository;

    /**
     * Constructor
     */
    public function __construct(
        WhatsAppServiceEngine $whatsAppServiceEngine,
        WhatsAppApiService $whatsAppApiService,
        ContactRepository $contactRepository,
        WooCommerceIntegrationRepository $wooCommerceIntegrationRepository,
        WooCommerceOrderRepository $wooCommerceOrderRepository,
        WooCommerceOrderNotificationRepository $wooCommerceOrderNotificationRepository,
        WhatsAppTemplateRepository $whatsAppTemplateRepository
    ) {
        $this->whatsAppServiceEngine = $whatsAppServiceEngine;
        $this->whatsAppApiService = $whatsAppApiService;
        $this->contactRepository = $contactRepository;
        $this->wooCommerceIntegrationRepository = $wooCommerceIntegrationRepository;
        $this->wooCommerceOrderRepository = $wooCommerceOrderRepository;
        $this->wooCommerceOrderNotificationRepository = $wooCommerceOrderNotificationRepository;
        $this->whatsAppTemplateRepository = $whatsAppTemplateRepository;
    }

    /**
     * Connect WooCommerce integration
     */
    public function connect(array $credentials, $vendorId = null): array
    {
        \Log::info('=== WOOCOMMERCE CONNECT START ===');
        try {
            \Log::info('WooCommerceService connect started', [
                'vendor_id' => $vendorId,
                'has_credentials' => !empty($credentials),
                'credentials_keys' => array_keys($credentials)
            ]);

            $siteUrl = $credentials['site_url'] ?? null;
            $consumerKey = $credentials['consumer_key'] ?? null;
            $consumerSecret = $credentials['consumer_secret'] ?? null;

            \Log::info('Extracted credentials', [
                'site_url' => $siteUrl,
                'consumer_key_length' => $consumerKey ? strlen($consumerKey) : 0,
                'consumer_secret_length' => $consumerSecret ? strlen($consumerSecret) : 0
            ]);

            if (!$siteUrl || !$consumerKey || !$consumerSecret) {
                \Log::error('Missing required credentials', [
                    'has_site_url' => !empty($siteUrl),
                    'has_consumer_key' => !empty($consumerKey),
                    'has_consumer_secret' => !empty($consumerSecret)
                ]);
                throw new Exception('Site URL, Consumer Key, and Consumer Secret are required');
            }

            // Remove trailing slash from site URL
            $siteUrl = rtrim($siteUrl, '/');

            \Log::info('Validating WooCommerce credentials', ['site_url' => $siteUrl]);

            // Validate WooCommerce credentials
            $validationResult = $this->validateWooCommerceCredentials($siteUrl, $consumerKey, $consumerSecret);

            \Log::info('Credential validation result', $validationResult);

            if (!$validationResult['valid']) {
                throw new Exception($validationResult['error']);
            }

            // Check if integration already exists
            \Log::info('Checking for existing integration', ['vendor_id' => $vendorId]);
            $existingIntegration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            \Log::info('Existing integration check result', [
                'integration_exists' => !is_null($existingIntegration),
                'integration_id' => $existingIntegration ? $existingIntegration->_id : null
            ]);

            if ($existingIntegration) {
                \Log::info('Updating existing integration');
                // Update existing integration
                $updateData = [
                    'site_url' => $siteUrl,
                    'consumer_key' => $consumerKey,
                    'consumer_secret' => $consumerSecret,
                    'is_active' => true,
                    'connected_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
                
                \Log::info('Integration update data', $updateData);
                
                $this->wooCommerceIntegrationRepository->update($existingIntegration->_id, $updateData);
                
                \Log::info('Integration updated successfully');
            } else {
                \Log::info('Creating new integration');
                // Create new integration
                $createData = [
                    'vendors__id' => $vendorId,
                    'site_url' => $siteUrl,
                    'consumer_key' => $consumerKey,
                    'consumer_secret' => $consumerSecret,
                    'is_active' => true,
                    'connected_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
                
                \Log::info('Integration create data', $createData);
                
                $this->wooCommerceIntegrationRepository->create($createData);
                
                \Log::info('Integration created successfully');
            }

            // Setup webhooks
            \Log::info('Setting up webhooks');
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);
            
            \Log::info('Retrieved integration for webhook setup', [
                'integration_id' => $integration->_id,
                'site_url' => $integration->site_url
            ]);
            
            $this->setupWebhooks($integration);
            
            \Log::info('Webhook setup completed');

            $result = [
                'success' => true,
                'message' => 'WooCommerce integration connected successfully',
                'data' => [
                    'site_url' => $siteUrl,
                    'connected_at' => Carbon::now()
                ]
            ];
            
            \Log::info('=== WOOCOMMERCE CONNECT END ===', $result);
            return $result;

        } catch (Exception $e) {
            \Log::error('WooCommerceService connect failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Disconnect WooCommerce integration
     */
    public function disconnect($vendorId = null): array
    {
        try {
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            if (!$integration) {
                return [
                    'success' => false,
                    'message' => 'No WooCommerce integration found'
                ];
            }

            // Remove webhooks
            $this->removeWebhooks($integration);

            // Update integration status
            $this->wooCommerceIntegrationRepository->update($integration->_id, [
                'is_active' => false,
                'disconnected_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return [
                'success' => true,
                'message' => 'WooCommerce integration disconnected successfully'
            ];

        } catch (Exception $e) {
            \Log::error('WooCommerceService disconnect failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get integration status
     */
    public function getIntegrationStatus($vendorId = null): array
    {
        try {
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            if (!$integration) {
                return [
                    'connected' => false,
                    'message' => 'No WooCommerce integration found'
                ];
            }

            return [
                'connected' => $integration->is_active,
                'site_url' => $integration->site_url,
                'connected_at' => $integration->connected_at,
                'last_sync' => $integration->last_sync_at
            ];

        } catch (Exception $e) {
            \Log::error('WooCommerceService getIntegrationStatus failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook
     */
    public function processWebhook($request, $vendorId = null)
    {
        try {
            $payload = $request->all();
            $topic = $request->header('X-WC-Webhook-Topic');
            $signature = $request->header('X-WC-Webhook-Signature');

            \Log::info('=== WOOCOMMERCE WEBHOOK DEBUG START ===');
            \Log::info('WooCommerce webhook received', [
                'topic' => $topic,
                'vendor_id' => $vendorId,
                'payload_keys' => array_keys($payload),
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'payload_sample' => array_slice($payload, 0, 3) // First 3 items for debugging
            ]);

            // If vendor_id is null, try to find integration by webhook URL or other means
            if (!$vendorId) {
                \Log::warning('WooCommerce webhook received without vendor_id', [
                    'request_url' => $request->fullUrl(),
                    'has_challenge' => $request->has('challenge'),
                    'topic' => $topic
                ]);
                
                // Check if this is a webhook verification or test request
                if ($request->has('challenge') || $request->header('X-WC-Webhook-Topic') === 'test') {
                    \Log::info('Processing webhook verification/test request');
                    return ['success' => true, 'message' => 'Webhook verification/test processed'];
                }
                
                // For actual order webhooks, we need vendor_id
                throw new Exception('Vendor ID is required for order webhook processing');
            }

            // Check if this is a webhook creation confirmation
            if (isset($payload['webhook_id']) && count($payload) === 1) {
                \Log::info('WooCommerce webhook creation confirmation received', [
                    'webhook_id' => $payload['webhook_id'],
                    'vendor_id' => $vendorId
                ]);
                return ['success' => true, 'message' => 'Webhook creation confirmation processed'];
            }

            \Log::info('Looking up WooCommerce integration', ['vendor_id' => $vendorId]);
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            \Log::info('Integration lookup result', [
                'integration_found' => !is_null($integration),
                'integration_active' => $integration ? $integration->is_active : false,
                'integration_id' => $integration ? $integration->_id : null
            ]);

            if (!$integration || !$integration->is_active) {
                \Log::error('WooCommerce integration not found or inactive', [
                    'vendor_id' => $vendorId,
                    'integration_exists' => !is_null($integration),
                    'integration_active' => $integration ? $integration->is_active : false
                ]);
                throw new Exception('WooCommerce integration not found or inactive');
            }

            // Verify webhook signature
            \Log::info('Verifying webhook signature');
            $signatureValid = $this->verifyWebhookSignature($request, $integration);
            \Log::info('Webhook signature verification result', ['signature_valid' => $signatureValid]);
            
            if (!$signatureValid) {
                \Log::error('Invalid webhook signature', [
                    'user_agent' => $request->header('User-Agent'),
                    'vendor_id' => $vendorId
                ]);
                throw new Exception('Invalid webhook signature');
            }

            // Only handle allowed webhook topics
            $allowedTopics = ['order.created', 'order.updated'];
            
            \Log::info('Checking webhook topic', [
                'received_topic' => $topic,
                'allowed_topics' => $allowedTopics,
                'topic_allowed' => in_array($topic, $allowedTopics)
            ]);
            
            if (!in_array($topic, $allowedTopics)) {
                \Log::warning('WooCommerce webhook received with unsupported topic', [
                    'topic' => $topic,
                    'vendor_id' => $vendorId,
                    'allowed_topics' => $allowedTopics
                ]);
                return ['success' => false, 'message' => 'Unsupported webhook topic'];
            }

            // Handle different webhook topics
            \Log::info('Processing webhook topic', ['topic' => $topic]);
            
            switch ($topic) {
                case 'order.created':
                    \Log::info('Processing order.created webhook');
                    $result = $this->processOrderCreated($payload, $integration);
                    \Log::info('Order created processing result', $result);
                    return $result;
                case 'order.updated':
                    \Log::info('Processing order.updated webhook');
                    $result = $this->processOrderUpdated($payload, $integration);
                    \Log::info('Order updated processing result', $result);
                    return $result;
                default:
                    \Log::warning('Unhandled WooCommerce webhook topic', ['topic' => $topic]);
                    return ['success' => true, 'message' => 'Webhook processed (no action required)'];
            }

        } catch (Exception $e) {
            \Log::error('WooCommerce webhook processing failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } finally {
            \Log::info('=== WOOCOMMERCE WEBHOOK DEBUG END ===');
        }
    }

    /**
     * Send order notification
     */
    public function sendOrderNotification(array $orderData, string $notificationType, $vendorId = null)
    {
        try {
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            if (!$integration || !$integration->is_active) {
                throw new Exception('WooCommerce integration not found or inactive');
            }

            // Get or create order
            $order = $this->wooCommerceOrderRepository->getByWooCommerceId($orderData['id'], $vendorId);

            if (!$order) {
                \Log::info('Creating new order in sendOrderNotification', [
                    'vendor_id' => $vendorId,
                    'integration_id' => $integration->_id,
                    'order_id' => $orderData['id']
                ]);
                
                // Create new order
                $order = $this->wooCommerceOrderRepository->create([
                    'vendors__id' => $vendorId,
                    'woocommerce_integrations__id' => $integration->_id,
                    'woocommerce_order_id' => $orderData['id'],
                    'order_number' => $orderData['number'],
                    'status' => $orderData['status'],
                    'total' => $orderData['total'],
                    'currency' => $orderData['currency'],
                    'customer_data' => $orderData['billing'] ?? [],
                    'order_data' => $orderData,
                    'created_at' => Carbon::parse($orderData['date_created']),
                    'updated_at' => Carbon::parse($orderData['date_modified'])
                ]);
            } else {
                // Update existing order
                $this->wooCommerceOrderRepository->update($order->_id, [
                    'status' => $orderData['status'],
                    'total' => $orderData['total'],
                    'order_data' => $orderData,
                    'updated_at' => Carbon::parse($orderData['date_modified'])
                ]);
            }

            // Send notification
            return $this->sendNotification($order, $notificationType, $integration);

        } catch (Exception $e) {
            \Log::error('WooCommerce order notification failed', [
                'notification_type' => $notificationType,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process order created webhook
     */
    protected function processOrderCreated(array $payload, WooCommerceIntegrationModel $integration)
    {
        \Log::info('=== PROCESSING ORDER CREATED START ===');
        \Log::info('Processing WooCommerce order created', [
            'order_id' => $payload['id'],
            'order_number' => $payload['number'] ?? 'N/A',
            'vendor_id' => $integration->vendors__id
        ]);

        // Validate order data
        if (!$this->validateOrderData($payload)) {
            \Log::error('Invalid order data received', [
                'order_id' => $payload['id'] ?? 'unknown',
                'payload_keys' => array_keys($payload)
            ]);
            return ['success' => false, 'message' => 'Invalid order data'];
        }

        \Log::info('Looking up existing order', [
            'woocommerce_order_id' => $payload['id'],
            'vendor_id' => $integration->vendors__id
        ]);

        $order = $this->wooCommerceOrderRepository->getByWooCommerceId($payload['id'], $integration->vendors__id);

        \Log::info('Order lookup result', [
            'order_found' => !is_null($order),
            'order_id' => $order ? $order->_id : null
        ]);

        if (!$order) {
            \Log::info('Creating new order record');
            // Create new order
            $orderData = [
                'vendors__id' => $integration->vendors__id,
                'woocommerce_integrations__id' => $integration->_id,
                'woocommerce_order_id' => $payload['id'],
                'order_number' => $payload['number'],
                'status' => $payload['status'],
                'total' => $payload['total'],
                'currency' => $payload['currency'],
                'customer_data' => $payload['billing'] ?? [],
                'order_data' => $payload,
                'created_at' => Carbon::parse($payload['date_created']),
                'updated_at' => Carbon::parse($payload['date_modified'])
            ];
            
            \Log::info('Order data for creation', $orderData);
            \Log::info('Integration ID being used', ['integration_id' => $integration->_id]);
            
            $order = $this->wooCommerceOrderRepository->create($orderData);
            
            \Log::info('Order created successfully', [
                'order_id' => $order->_id,
                'woocommerce_order_id' => $order->woocommerce_order_id
            ]);
        } else {
            \Log::info('Order already exists, skipping creation');
        }

        // Send order confirmation notification
        \Log::info('Sending order confirmation notification');
        $notificationResult = $this->sendNotification($order, 'order_confirmation', $integration);
        
        \Log::info('Notification result', $notificationResult);
        \Log::info('=== PROCESSING ORDER CREATED END ===');

        return ['success' => true, 'message' => 'Order created processed'];
    }

    /**
     * Process order updated webhook
     */
    protected function processOrderUpdated(array $payload, WooCommerceIntegrationModel $integration)
    {
        \Log::info('=== PROCESSING ORDER UPDATED START ===');
        \Log::info('Processing WooCommerce order updated', [
            'order_id' => $payload['id'],
            'order_number' => $payload['number'] ?? 'N/A',
            'vendor_id' => $integration->vendors__id,
            'new_status' => $payload['status']
        ]);

        // Validate order data
        if (!$this->validateOrderData($payload)) {
            \Log::error('Invalid order data received for update', [
                'order_id' => $payload['id'] ?? 'unknown',
                'payload_keys' => array_keys($payload)
            ]);
            return ['success' => false, 'message' => 'Invalid order data'];
        }

        \Log::info('Looking up existing order for update', [
            'woocommerce_order_id' => $payload['id'],
            'vendor_id' => $integration->vendors__id
        ]);

        $order = $this->wooCommerceOrderRepository->getByWooCommerceId($payload['id'], $integration->vendors__id);

        \Log::info('Order lookup result for update', [
            'order_found' => !is_null($order),
            'order_id' => $order ? $order->_id : null,
            'old_status' => $order ? $order->status : null
        ]);

        if ($order) {
            $oldStatus = $order->status;
            $newStatus = $payload['status'];
            
            \Log::info('Updating existing order', [
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            $updateData = [
                'status' => $newStatus,
                'total' => $payload['total'],
                'order_data' => $payload,
                'updated_at' => Carbon::parse($payload['date_modified'])
            ];
            
            \Log::info('Order update data', $updateData);
            
            $this->wooCommerceOrderRepository->update($order->_id, $updateData);
            
            \Log::info('Order updated successfully');
            
            // Check if we should send a notification for this status change
            if ($this->shouldSendNotificationForStatus($oldStatus, $newStatus)) {
                $notificationType = $this->getNotificationTypeForStatus($newStatus);
                if ($notificationType) {
                    \Log::info('Sending status update notification', [
                        'notification_type' => $notificationType,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus
                    ]);
                    $notificationResult = $this->sendNotification($order, $notificationType, $integration);
                    \Log::info('Status update notification result', $notificationResult);
                } else {
                    \Log::info('No notification type found for status', ['status' => $newStatus]);
                }
            } else {
                \Log::info('Skipping notification for status change', [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
            }
        } else {
            \Log::warning('Order not found for update', [
                'woocommerce_order_id' => $payload['id'],
                'vendor_id' => $integration->vendors__id
            ]);
            
            // If order doesn't exist, create it and send confirmation
            \Log::info('Creating order from update webhook');
            $orderData = [
                'vendors__id' => $integration->vendors__id,
                'woocommerce_integrations__id' => $integration->_id,
                'woocommerce_order_id' => $payload['id'],
                'order_number' => $payload['number'],
                'status' => $payload['status'],
                'total' => $payload['total'],
                'currency' => $payload['currency'],
                'customer_data' => $payload['billing'] ?? [],
                'order_data' => $payload,
                'created_at' => Carbon::parse($payload['date_created']),
                'updated_at' => Carbon::parse($payload['date_modified'])
            ];
            
            $order = $this->wooCommerceOrderRepository->create($orderData);
            
            // Send order confirmation notification
            $notificationResult = $this->sendNotification($order, 'order_confirmation', $integration);
            \Log::info('Order creation notification result', $notificationResult);
        }

        \Log::info('=== PROCESSING ORDER UPDATED END ===');
        return ['success' => true, 'message' => 'Order updated processed'];
    }

    /**
     * Get notification type based on order status
     */
    protected function getNotificationTypeForStatus(string $status): ?string
    {
        $statusMap = [
            'processing' => 'payment_confirmation',
            'completed' => 'delivery_confirmation',
            'on-hold' => 'order_confirmation',
            'pending' => 'order_confirmation',
            'failed' => 'order_cancelled',
            'cancelled' => 'order_cancelled',
            'refunded' => 'order_cancelled',
            'shipped' => 'shipment_tracking'
        ];

        return $statusMap[$status] ?? null;
    }

    /**
     * Check if notification should be sent for status change
     */
    protected function shouldSendNotificationForStatus(string $oldStatus, string $newStatus): bool
    {
        // Don't send notifications for the same status
        if ($oldStatus === $newStatus) {
            return false;
        }

        // Don't send notifications for certain status transitions
        $skipTransitions = [
            'pending' => ['on-hold'],
            'on-hold' => ['pending'],
            'processing' => ['on-hold'],
            'on-hold' => ['processing']
        ];

        if (isset($skipTransitions[$oldStatus]) && in_array($newStatus, $skipTransitions[$oldStatus])) {
            return false;
        }

        return true;
    }

    /**
     * Validate order data from webhook
     */
    protected function validateOrderData(array $payload): bool
    {
        $requiredFields = ['id', 'number', 'status', 'total', 'currency'];
        
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                \Log::error('Missing required field in order payload', [
                    'missing_field' => $field,
                    'payload_keys' => array_keys($payload)
                ]);
                return false;
            }
        }

        // Validate billing data exists
        if (!isset($payload['billing']) || empty($payload['billing'])) {
            \Log::warning('Order payload missing billing data', [
                'order_id' => $payload['id'],
                'has_billing' => isset($payload['billing']),
                'billing_data' => $payload['billing'] ?? null
            ]);
        }

        return true;
    }

    /**
     * Send notification
     */
    protected function sendNotification(WooCommerceOrderModel $order, string $notificationType, WooCommerceIntegrationModel $integration)
    {
        \Log::info('=== SEND NOTIFICATION START ===');
        \Log::info('Sending notification', [
            'order_id' => $order->_id,
            'notification_type' => $notificationType,
            'vendor_id' => $integration->vendors__id
        ]);
        
        try {
            // Get or create contact
            \Log::info('Getting or creating contact');
            $contact = $this->getOrCreateContact($order->customer_data, $integration->vendors__id);

            \Log::info('Contact lookup/creation result', [
                'contact_found' => !is_null($contact),
                'contact_id' => $contact ? $contact->_id : null,
                'contact_phone' => $contact ? $contact->wa_id : null
            ]);

            if (!$contact) {
                \Log::error('Failed to create or get contact', [
                    'customer_data' => $order->customer_data
                ]);
                throw new Exception('Failed to create or get contact - no valid phone or email');
            }

            // Check if contact has a valid phone number
            if (!$contact->wa_id) {
                \Log::error('Contact has no WhatsApp ID', [
                    'contact_id' => $contact->_id,
                    'contact_email' => $contact->email
                ]);
                throw new Exception('Contact has no valid WhatsApp phone number');
            }

            // Get template
            \Log::info('Getting template for notification type', ['notification_type' => $notificationType]);
            $template = $this->getTemplateForNotificationType($notificationType, $integration->vendors__id);

            \Log::info('Template lookup result', [
                'template_found' => !is_null($template),
                'template_id' => $template ? $template->_id : null,
                'template_name' => $template ? $template->template_name : null
            ]);

            if (!$template) {
                \Log::error('No template found for notification type', [
                    'notification_type' => $notificationType,
                    'vendor_id' => $integration->vendors__id
                ]);
                throw new Exception("No WhatsApp template found for notification type: {$notificationType}. Please create a template with name 'woocommerce_{$notificationType}' or 'woocommerce_generic_notification'");
            }

            // Prepare template variables
            \Log::info('Preparing template variables');
            $variables = $this->prepareTemplateVariables($order, $notificationType, $integration);
            
            \Log::info('Template variables prepared', [
                'variables_count' => count($variables),
                'variables' => $variables
            ]);

            // Send WhatsApp message
            \Log::info('Sending WhatsApp template message', [
                'contact_phone' => $contact->wa_id,
                'template_name' => $template->template_name,
                'vendor_id' => $integration->vendors__id
            ]);
            
            // Prepare components for template message
            $components = [];
            if (!empty($variables)) {
                // Check if template has body parameters
                $templateData = $template->__data ?? [];
                $templateComponents = $templateData['template']['components'] ?? [];
                $hasBodyParameters = false;
                $templateVariableNumbers = [];
                
                foreach ($templateComponents as $component) {
                    if ($component['type'] === 'BODY' && !empty($component['text'])) {
                        // Check if body text contains variables like {{1}}, {{2}}, etc.
                        if (preg_match_all('/\{\{(\d+)\}\}/', $component['text'], $matches)) {
                            $hasBodyParameters = true;
                            $templateVariableNumbers = $matches[1]; // Extract the numbers
                            break;
                        }
                    }
                }
                
                if ($hasBodyParameters) {
                    $parameters = [];
                    $validParameters = [];
                    
                    // Sort variables by their numeric keys to ensure correct order
                    $sortedVariables = [];
                    foreach ($variables as $key => $value) {
                        if (is_numeric($key)) {
                            $sortedVariables[(int)$key] = $value;
                        }
                    }
                    ksort($sortedVariables);
                    
                    // Ensure we have parameters for all template variables
                    $maxVariableNumber = max($templateVariableNumbers);
                    for ($i = 1; $i <= $maxVariableNumber; $i++) {
                        $value = $sortedVariables[$i] ?? null;
                        
                        // Skip empty values and provide fallbacks
                        if (empty($value) || $value === '') {
                            // Provide fallback values for empty fields
                            $fallbackValue = $this->getTemplateVariableFallback($i, $variables);
                            $value = $fallbackValue;
                        }
                        
                        // Ensure we have a valid text value
                        if (!empty($value) && $value !== '') {
                            $parameters[] = [
                                'type' => 'text',
                                'text' => $value
                            ];
                        } else {
                            // If still empty, provide a default fallback
                            $parameters[] = [
                                'type' => 'text',
                                'text' => 'N/A'
                            ];
                        }
                    }
                    
                    // Only add body component if we have parameters
                    if (!empty($parameters)) {
                        // Final validation: ensure all parameters have valid text values
                        foreach ($parameters as $param) {
                            if (isset($param['text']) && !empty($param['text']) && $param['text'] !== '') {
                                $validParameters[] = $param;
                            } else {
                                // Replace empty text with fallback
                                $validParameters[] = [
                                    'type' => 'text',
                                    'text' => 'N/A'
                                ];
                            }
                        }
                        
                        $components[] = [
                            'type' => 'body',
                            'parameters' => $validParameters
                        ];
                    }
                }
                
                \Log::info('Template components prepared', [
                    'template_name' => $template->template_name,
                    'template_language' => $template->language,
                    'variables_count' => count($variables),
                    'variables' => $variables,
                    'has_body_parameters' => $hasBodyParameters,
                    'template_variable_numbers' => $templateVariableNumbers,
                    'max_variable_number' => $hasBodyParameters ? max($templateVariableNumbers) : null,
                    'parameters_count' => count($parameters ?? []),
                    'parameters' => $parameters ?? [],
                    'final_parameters_count' => count($validParameters ?? []),
                    'final_parameters' => $validParameters ?? [],
                    'components' => $components,
                    'template_components_count' => count($templateComponents),
                    'template_components_types' => array_map(function($comp) { return $comp['type']; }, $templateComponents)
                ]);
            }
            
            try {
                // Validate components before sending
                if (empty($components)) {
                    \Log::warning('No components to send, skipping WhatsApp API call', [
                        'template_name' => $template->template_name,
                        'contact_phone' => $contact->wa_id,
                        'vendor_id' => $integration->vendors__id
                    ]);
                    
                    $whatsAppResult = [
                        'error' => [
                            'message' => 'No valid components to send'
                        ]
                    ];
                } else {
                    $whatsAppResult = $this->whatsAppApiService->sendTemplateMessage(
                        $template->template_name,
                        $template->language,
                        $contact->wa_id,
                        $components,
                        $integration->vendors__id
                    );
                }

                \Log::info('WhatsApp service response', $whatsAppResult);
            } catch (Exception $e) {
                \Log::error('WhatsApp API call failed', [
                    'error' => $e->getMessage(),
                    'template_name' => $template->template_name,
                    'contact_phone' => $contact->wa_id,
                    'vendor_id' => $integration->vendors__id,
                    'components_count' => count($components),
                    'components' => $components
                ]);
                
                $whatsAppResult = [
                    'error' => [
                        'message' => $e->getMessage()
                    ]
                ];
            }

            // Create notification record
            \Log::info('Creating notification record');
            $notificationData = [
                'vendors__id' => $integration->vendors__id,
                'woocommerce_orders__id' => $order->_id,
                'notification_type' => $notificationType,
                'contacts__id' => $contact->_id,
                'whatsapp_templates__id' => $template->_id,
                'variables' => $variables,
                'status' => isset($whatsAppResult['error']) ? 'failed' : 'sent',
                'response' => $whatsAppResult,
                'sent_at' => !isset($whatsAppResult['error']) ? Carbon::now() : null,
                'created_at' => Carbon::now()
            ];
            
            \Log::info('Notification record data', $notificationData);
            
            try {
                $notificationRecord = $this->wooCommerceOrderNotificationRepository->create($notificationData);
                
                \Log::info('Notification record created', [
                    'notification_id' => $notificationRecord->_id,
                    'status' => $notificationRecord->status
                ]);
            } catch (Exception $e) {
                \Log::error('Failed to create notification record', [
                    'error' => $e->getMessage(),
                    'notification_data' => $notificationData
                ]);
                // Don't fail the entire process if notification record creation fails
            }

            $result = [
                'success' => !isset($whatsAppResult['error']),
                'message' => isset($whatsAppResult['error']) ? $whatsAppResult['error']['message'] ?? 'Failed to send notification' : 'Notification sent successfully',
                'contact_phone' => $contact->wa_id,
                'template_name' => $template->template_name
            ];
            
            \Log::info('=== SEND NOTIFICATION END ===', $result);
            return $result;

        } catch (Exception $e) {
            \Log::error('WooCommerce notification sending failed', [
                'order_id' => $order->_id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Create failed notification record
            try {
                $failedNotificationData = [
                    'vendors__id' => $integration->vendors__id,
                    'woocommerce_orders__id' => $order->_id,
                    'notification_type' => $notificationType,
                    'contacts__id' => null,
                    'whatsapp_templates__id' => null,
                    'variables' => [],
                    'status' => 'failed',
                    'response' => ['error' => $e->getMessage()],
                    'sent_at' => null,
                    'created_at' => Carbon::now()
                ];
                
                $this->wooCommerceOrderNotificationRepository->create($failedNotificationData);
            } catch (Exception $recordException) {
                \Log::error('Failed to create failed notification record', [
                    'error' => $recordException->getMessage()
                ]);
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Prepare template variables
     */
    protected function prepareTemplateVariables(WooCommerceOrderModel $order, string $notificationType, WooCommerceIntegrationModel $integration): array
    {
        $orderData = $this->getOrderDataForVariables($order);
        
        // Get stored variable mappings for this notification type
        $variableMappings = $integration->getVariableMappings($notificationType);
        
        \Log::info('Preparing template variables with mappings', [
            'notification_type' => $notificationType,
            'variable_mappings' => $variableMappings,
            'order_data_keys' => array_keys($orderData),
            'integration_settings' => $integration->settings ?? []
        ]);
        
        $variables = [];
        
        // If we have stored variable mappings, use them
        if (!empty($variableMappings)) {
            foreach ($variableMappings as $templateVariable => $woocommerceVariable) {
                if (!empty($woocommerceVariable) && isset($orderData[$woocommerceVariable])) {
                    $variables[$templateVariable] = $orderData[$woocommerceVariable];
                } else {
                    // Provide fallback value
                    $fallbackValue = $this->getFallbackValue($woocommerceVariable, $orderData);
                    $variables[$templateVariable] = $fallbackValue;
                    
                    \Log::warning('Missing template variable, using fallback', [
                        'template_variable' => $templateVariable,
                        'woocommerce_variable' => $woocommerceVariable,
                        'fallback_value' => $fallbackValue
                    ]);
                }
            }
        } else {
            // Fallback to default variables
            $variables = [
                'customer_name' => $orderData['customer_name'] ?? 'Customer',
                'order_number' => $orderData['order_number'],
                'order_total' => $orderData['order_total'],
                'order_status' => $orderData['order_status'],
                'order_date' => $orderData['order_date'],
                'payment_method' => $orderData['payment_method'],
                'shipping_address' => $orderData['shipping_address'],
                'billing_address' => $orderData['billing_address']
            ];

            // Add notification-specific variables
            switch ($notificationType) {
                case 'order_confirmation':
                    $variables['confirmation_message'] = 'Your order has been confirmed and is being processed.';
                    break;
                case 'payment_confirmation':
                    $variables['payment_message'] = 'Payment for your order has been confirmed.';
                    break;
                case 'shipment_tracking':
                    $variables['tracking_number'] = $this->getTrackingNumber($order);
                    $variables['tracking_company'] = $this->getTrackingCompany($order);
                    $variables['tracking_url'] = $this->getTrackingUrl($order);
                    break;
                case 'delivery_confirmation':
                    $variables['delivery_message'] = 'Your order has been delivered successfully.';
                    $variables['delivery_date'] = $this->getDeliveryDate($order);
                    break;
                case 'cod_verification':
                    $variables['cod_amount'] = $orderData['order_total'];
                    $variables['cod_message'] = 'Please have the exact amount ready for delivery.';
                    break;
                case 'order_restored':
                    $variables['confirmation_message'] = 'Your order has been restored and is being processed.';
                    break;
                case 'order_deleted':
                case 'order_trashed':
                    $variables['cancellation_message'] = 'Your order has been cancelled.';
                    break;
            }
        }
        
        \Log::info('Template variables prepared', [
            'variables_count' => count($variables),
            'variables' => $variables
        ]);

        return $variables;
    }

    /**
     * Get order data for template variables
     */
    protected function getOrderDataForVariables(WooCommerceOrderModel $order): array
    {
        $orderData = $order->order_data;
        $customerData = $order->customer_data;

        return [
            'customer_name' => $customerData['first_name'] . ' ' . $customerData['last_name'],
            'order_number' => $order->order_number,
            'order_total' => $order->currency . ' ' . number_format($order->total, 2),
            'order_status' => ucfirst($order->status),
            'order_date' => $order->created_at->format('M d, Y'),
            'payment_method' => $orderData['payment_method_title'] ?? 'N/A',
            'shipping_address' => $this->formatAddress($orderData['shipping'] ?? []),
            'billing_address' => $this->formatAddress($orderData['billing'] ?? [])
        ];
    }

    /**
     * Format address for display
     */
    protected function formatAddress(array $address): string
    {
        $parts = [];
        
        if (!empty($address['address_1'])) $parts[] = $address['address_1'];
        if (!empty($address['address_2'])) $parts[] = $address['address_2'];
        if (!empty($address['city'])) $parts[] = $address['city'];
        if (!empty($address['state'])) $parts[] = $address['state'];
        if (!empty($address['postcode'])) $parts[] = $address['postcode'];
        if (!empty($address['country'])) $parts[] = $address['country'];

        return implode(', ', $parts);
    }

    /**
     * Get tracking number
     */
    protected function getTrackingNumber(WooCommerceOrderModel $order): string
    {
        $orderData = $order->order_data;
        return $orderData['meta_data']['_tracking_number'] ?? 'N/A';
    }

    /**
     * Get tracking company
     */
    protected function getTrackingCompany(WooCommerceOrderModel $order): string
    {
        $orderData = $order->order_data;
        return $orderData['meta_data']['_tracking_company'] ?? 'N/A';
    }

    /**
     * Get tracking URL
     */
    protected function getTrackingUrl(WooCommerceOrderModel $order): string
    {
        $orderData = $order->order_data;
        return $orderData['meta_data']['_tracking_url'] ?? '#';
    }

    /**
     * Get delivery date
     */
    protected function getDeliveryDate(WooCommerceOrderModel $order): string
    {
        $orderData = $order->order_data;
        $deliveryDate = $orderData['meta_data']['_delivery_date'] ?? null;
        
        if ($deliveryDate) {
            return Carbon::parse($deliveryDate)->format('M d, Y');
        }
        
        return 'N/A';
    }

    /**
     * Get fallback value for missing template variables
     */
    protected function getFallbackValue(string $woocommerceVariable, array $orderData): string
    {
        switch ($woocommerceVariable) {
            case 'customer_name':
                return $orderData['customer_name'] ?? 'Customer';
            case 'order_number':
                return $orderData['order_number'] ?? 'Unknown';
            case 'order_total':
                return $orderData['order_total'] ?? '0.00';
            case 'order_status':
                return $orderData['order_status'] ?? 'Unknown';
            case 'order_date':
                return $orderData['order_date'] ?? 'N/A';
            case 'payment_method':
                return $orderData['payment_method'] ?? 'N/A';
            case 'shipping_address':
                return $orderData['shipping_address'] ?? 'N/A';
            case 'billing_address':
                return $orderData['billing_address'] ?? 'N/A';
            case 'tracking_number':
                return 'N/A';
            case 'tracking_company':
                return 'N/A';
            case 'tracking_url':
                return '#';
            case 'delivery_date':
                return 'N/A';
            case 'cod_amount':
                return $orderData['order_total'] ?? '0.00';
            default:
                return 'N/A';
        }
    }

    /**
     * Get fallback value for template variable keys
     */
    protected function getTemplateVariableFallback(string $variableKey, array $variables): string
    {
        // Map common template variable keys to fallback values
        switch ($variableKey) {
            case '1':
            case 'customer_name':
                return $variables['customer_name'] ?? 'Customer';
            case '2':
            case 'shipping_address':
                return $variables['shipping_address'] ?? 'N/A';
            case '3':
            case 'tracking_company':
                return $variables['tracking_company'] ?? 'N/A';
            case '4':
            case 'cod_amount':
                return $variables['cod_amount'] ?? $variables['order_total'] ?? '0.00';
            case '5':
            case 'tracking_url':
                return $variables['tracking_url'] ?? '#';
            case 'order_number':
                return $variables['order_number'] ?? 'Unknown';
            case 'order_total':
                return $variables['order_total'] ?? '0.00';
            case 'order_status':
                return $variables['order_status'] ?? 'Unknown';
            case 'order_date':
                return $variables['order_date'] ?? 'N/A';
            case 'payment_method':
                return $variables['payment_method'] ?? 'N/A';
            case 'billing_address':
                return $variables['billing_address'] ?? 'N/A';
            case 'tracking_number':
                return $variables['tracking_number'] ?? 'N/A';
            case 'delivery_date':
                return $variables['delivery_date'] ?? 'N/A';
            default:
                return 'N/A';
        }
    }

    /**
     * Get template for notification type
     */
    protected function getTemplateForNotificationType(string $notificationType, int $vendorId)
    {
        // Get integration to check for stored template mappings
        $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);
        
        if ($integration) {
            // Check if there's a stored template mapping for this notification type
            $templateUid = $integration->getTemplateUid($notificationType);
            
            if ($templateUid) {
                \Log::info('Looking up mapped template', [
                    'template_uid' => $templateUid,
                    'notification_type' => $notificationType,
                    'vendor_id' => $vendorId
                ]);
                
                $template = $this->whatsAppTemplateRepository->fetchIt($templateUid);
                
                \Log::info('Mapped template lookup result', [
                    'template_found' => !is_null($template),
                    'template_id' => $template ? $template->_id : null,
                    'template_name' => $template ? $template->template_name : null
                ]);
                
                if ($template) {
                    return $template;
                }
            }
        }
        
        // Fallback to the old method
        $templateName = $this->getTemplateName($notificationType);
        
        \Log::info('Looking up WhatsApp template (fallback)', [
            'template_name' => $templateName,
            'vendor_id' => $vendorId,
            'notification_type' => $notificationType
        ]);
        
        // First try to find the specific template
        $template = $this->whatsAppTemplateRepository->fetchIt([
            'vendors__id' => $vendorId,
            'template_name' => $templateName,
            'status' => 'APPROVED'
        ]);
        
        \Log::info('Specific template lookup result', [
            'template_found' => !is_null($template),
            'template_id' => $template ? $template->_id : null,
            'template_name' => $template ? $template->template_name : null
        ]);
        
        // If specific template not found, try to find a generic WooCommerce template
        if (!$template) {
            \Log::info('Specific template not found, looking for generic WooCommerce template');
            $template = $this->whatsAppTemplateRepository->fetchIt([
                'vendors__id' => $vendorId,
                'template_name' => 'woocommerce_generic_notification',
                'status' => 'APPROVED'
            ]);
            
            \Log::info('Generic template lookup result', [
                'template_found' => !is_null($template),
                'template_id' => $template ? $template->_id : null,
                'template_name' => $template ? $template->template_name : null
            ]);
        }
        
        // If still not found, try to find any approved template for this vendor
        if (!$template) {
            \Log::info('No WooCommerce templates found, looking for any approved template');
            $template = $this->whatsAppTemplateRepository->fetchIt([
                'vendors__id' => $vendorId,
                'status' => 'APPROVED'
            ]);
            
            \Log::info('Any template lookup result', [
                'template_found' => !is_null($template),
                'template_id' => $template ? $template->_id : null,
                'template_name' => $template ? $template->template_name : null
            ]);
        }
        
        return $template;
    }

    /**
     * Get template name for notification type
     */
    protected function getTemplateName(string $notificationType): string
    {
        $templateMap = [
            'order_confirmation' => 'woocommerce_order_confirmation',
            'payment_confirmation' => 'woocommerce_payment_confirmation',
            'shipment_tracking' => 'woocommerce_shipment_tracking',
            'delivery_confirmation' => 'woocommerce_delivery_confirmation',
            'cod_verification' => 'woocommerce_cod_verification',
            'order_restored' => 'woocommerce_order_confirmation',
            'order_deleted' => 'woocommerce_order_cancelled',
            'order_trashed' => 'woocommerce_order_cancelled',
            'order_cancelled' => 'woocommerce_order_cancelled',
            'order_processing' => 'woocommerce_order_confirmation',
            'order_completed' => 'woocommerce_delivery_confirmation',
            'order_on_hold' => 'woocommerce_order_confirmation',
            'order_pending' => 'woocommerce_order_confirmation',
            'order_failed' => 'woocommerce_order_cancelled',
            'order_refunded' => 'woocommerce_order_cancelled',
            'order_shipped' => 'woocommerce_shipment_tracking'
        ];

        return $templateMap[$notificationType] ?? 'woocommerce_generic_notification';
    }

    /**
     * Get or create contact
     */
    protected function getOrCreateContact(array $customerData, int $vendorId)
    {
        $phone = $customerData['phone'] ?? null;
        $email = $customerData['email'] ?? null;

        \Log::info('Getting or creating contact', [
            'phone' => $phone,
            'email' => $email,
            'vendor_id' => $vendorId,
            'customer_data_keys' => array_keys($customerData)
        ]);

        if (!$phone && !$email) {
            \Log::warning('No phone or email provided for contact creation', [
                'customer_data' => $customerData
            ]);
            return null;
        }

        // Try to find existing contact
        $contact = null;
        
        if ($phone) {
            // Clean and format phone number
            $phone = $this->formatPhoneNumber($phone);
            
            if ($phone) {
                \Log::info('Looking up contact by phone', ['phone' => $phone]);
                $contact = $this->contactRepository->getVendorContactByWaId($phone, $vendorId);
                
                \Log::info('Contact lookup by phone result', [
                    'contact_found' => !is_null($contact),
                    'contact_id' => $contact ? $contact->_id : null
                ]);
            } else {
                \Log::warning('Invalid phone number format', ['original_phone' => $customerData['phone']]);
            }
        }
        
        if (!$contact && $email) {
            \Log::info('Looking up contact by email', ['email' => $email]);
            // Try to find by email using the model directly
            $contact = $this->contactRepository->fetchIt([
                'vendors__id' => $vendorId,
                'email' => $email
            ]);
            
            \Log::info('Contact lookup by email result', [
                'contact_found' => !is_null($contact),
                'contact_id' => $contact ? $contact->_id : null
            ]);
        }

        if ($contact) {
            \Log::info('Updating existing contact', ['contact_id' => $contact->_id]);
            // Update contact with latest data
            $updateData = [
                'first_name' => $customerData['first_name'] ?? $contact->first_name,
                'last_name' => $customerData['last_name'] ?? $contact->last_name,
                'email' => $email ?? $contact->email,
                'wa_id' => $phone ?? $contact->wa_id,
                'updated_at' => Carbon::now()
            ];
            
            \Log::info('Contact update data', $updateData);
            
            $this->contactRepository->updateIt($contact, $updateData);
            $contact = $contact->fresh();
        } else {
            \Log::info('Creating new contact');
            // Create new contact
            $contactData = [
                'vendors__id' => $vendorId,
                'first_name' => $customerData['first_name'] ?? '',
                'last_name' => $customerData['last_name'] ?? '',
                'email' => $email,
                'wa_id' => $phone,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            
            \Log::info('Contact creation data', $contactData);
            
            try {
                $contact = $this->contactRepository->storeContact($contactData, $vendorId);
                
                \Log::info('Contact created successfully', [
                    'contact_id' => $contact->_id,
                    'contact_uid' => $contact->_uid ?? null
                ]);
            } catch (Exception $e) {
                \Log::error('Failed to create contact', [
                    'error' => $e->getMessage(),
                    'contact_data' => $contactData
                ]);
                return null;
            }
        }

        return $contact;
    }

    /**
     * Format phone number for WhatsApp
     */
    protected function formatPhoneNumber(string $phone): ?string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If it starts with +, keep it
        if (str_starts_with($phone, '+')) {
            // Remove any extra + signs
            $phone = '+' . ltrim($phone, '+');
        } else {
            // If no +, add it
            $phone = '+' . $phone;
        }
        
        // Ensure it has at least 10 digits (excluding +)
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) < 10) {
            \Log::warning('Phone number too short', [
                'original' => $phone,
                'digits_count' => strlen($digits)
            ]);
            return null;
        }
        
        return $phone;
    }

    /**
     * Validate WooCommerce credentials
     */
    protected function validateWooCommerceCredentials(string $siteUrl, string $consumerKey, string $consumerSecret): array
    {
        \Log::info('=== VALIDATE WOOCOMMERCE CREDENTIALS START ===');
        \Log::info('Validating credentials', [
            'site_url' => $siteUrl,
            'consumer_key_length' => strlen($consumerKey),
            'consumer_secret_length' => strlen($consumerSecret)
        ]);
        
        try {
            $apiUrl = $siteUrl . '/wp-json/wc/v3/system_status';
            \Log::info('Making API request', ['api_url' => $apiUrl]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)
            ])->get($apiUrl);

            \Log::info('API response received', [
                'status_code' => $response->status(),
                'response_body_length' => strlen($response->body()),
                'response_headers' => $response->headers()
            ]);

            if ($response->successful()) {
                \Log::info('Credentials validation successful');
                return [
                    'valid' => true,
                    'data' => $response->json()
                ];
            } else {
                \Log::error('Credentials validation failed', [
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return [
                    'valid' => false,
                    'error' => 'Invalid WooCommerce credentials'
                ];
            }
        } catch (Exception $e) {
            \Log::error('Credentials validation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'valid' => false,
                'error' => 'Failed to validate WooCommerce credentials: ' . $e->getMessage()
            ];
        } finally {
            \Log::info('=== VALIDATE WOOCOMMERCE CREDENTIALS END ===');
        }
    }

    /**
     * Setup webhooks
     */
    protected function setupWebhooks(WooCommerceIntegrationModel $integration)
    {
        // Get available webhook topics from WooCommerce
        $availableTopics = $this->getAvailableWebhookTopics($integration);
        
        $webhookTopics = [
            'order.created',
            'order.updated'
        ];

        foreach ($webhookTopics as $topic) {
            // Only create webhook if topic is available
            if (in_array($topic, $availableTopics)) {
                try {
                    \Log::info('Attempting to create webhook for topic', [
                        'topic' => $topic,
                        'vendor_id' => $integration->vendors__id
                    ]);
                    $this->createWebhook($integration, $topic);
                } catch (Exception $e) {
                    \Log::error('Failed to create WooCommerce webhook for topic', [
                        'topic' => $topic,
                        'vendor_id' => $integration->vendors__id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::info('Skipping webhook creation for unavailable topic', [
                    'topic' => $topic,
                    'vendor_id' => $integration->vendors__id
                ]);
            }
        }
    }

    /**
     * Create webhook
     */
    protected function createWebhook(WooCommerceIntegrationModel $integration, string $topic)
    {
        try {
            // Validate topic
            if (empty($topic)) {
                throw new Exception('Webhook topic cannot be empty');
            }
            
            $webhookUrl = route('webhook.woocommerce.vendor', ['vendorId' => $integration->vendors__id]);
            
            // Validate webhook URL
            if (empty($webhookUrl)) {
                throw new Exception('Failed to generate webhook URL');
            }
            
            \Log::info('Creating WooCommerce webhook', [
                'topic' => $topic,
                'vendor_id' => $integration->vendors__id,
                'webhook_url' => $webhookUrl,
                'site_url' => $integration->site_url,
                'route_name' => 'webhook.woocommerce.vendor'
            ]);
            
            $webhookData = [
                'name' => 'OMX Flow - ' . ucfirst(str_replace('.', ' ', $topic)),
                'topic' => $topic,
                'delivery_url' => $webhookUrl,
                'status' => 'active'
            ];
            
            // Validate webhook data
            if (empty($webhookData['topic'])) {
                throw new Exception('Webhook topic cannot be empty');
            }
            
            \Log::info('WooCommerce webhook data', [
                'webhook_data' => $webhookData
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($integration->consumer_key . ':' . $integration->consumer_secret)
            ])->post($integration->site_url . '/wp-json/wc/v3/webhooks', $webhookData);
            
            \Log::info('WooCommerce webhook response', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'topic' => $topic
            ]);

            if ($response->successful()) {
                \Log::info('WooCommerce webhook created', [
                    'topic' => $topic,
                    'vendor_id' => $integration->vendors__id
                ]);
            } else {
                \Log::error('Failed to create WooCommerce webhook', [
                    'topic' => $topic,
                    'vendor_id' => $integration->vendors__id,
                    'response' => $response->body(),
                    'status_code' => $response->status(),
                    'headers' => $response->headers()
                ]);
                
                // Don't throw exception, just log the error and continue
                // Some webhook topics might not be available in all WooCommerce installations
            }
        } catch (Exception $e) {
            \Log::error('WooCommerce webhook creation failed', [
                'topic' => $topic,
                'vendor_id' => $integration->vendors__id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove webhooks
     */
    protected function removeWebhooks(WooCommerceIntegrationModel $integration)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($integration->consumer_key . ':' . $integration->consumer_secret)
            ])->get($integration->site_url . '/wp-json/wc/v3/webhooks');

            if ($response->successful()) {
                $webhooks = $response->json();
                
                foreach ($webhooks as $webhook) {
                    if (str_contains($webhook['name'], 'OMX Flow')) {
                        $this->deleteWebhook($integration, $webhook['id']);
                    }
                }
            }
        } catch (Exception $e) {
            \Log::error('WooCommerce webhook removal failed', [
                'vendor_id' => $integration->vendors__id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete webhook
     */
    protected function deleteWebhook(WooCommerceIntegrationModel $integration, int $webhookId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($integration->consumer_key . ':' . $integration->consumer_secret)
            ])->delete($integration->site_url . '/wp-json/wc/v3/webhooks/' . $webhookId);

            if ($response->successful()) {
                \Log::info('WooCommerce webhook deleted', [
                    'webhook_id' => $webhookId,
                    'vendor_id' => $integration->vendors__id
                ]);
            }
        } catch (Exception $e) {
            \Log::error('WooCommerce webhook deletion failed', [
                'webhook_id' => $webhookId,
                'vendor_id' => $integration->vendors__id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get available webhook topics from WooCommerce
     */
    protected function getAvailableWebhookTopics(WooCommerceIntegrationModel $integration): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($integration->consumer_key . ':' . $integration->consumer_secret)
            ])->get($integration->site_url . '/wp-json/wc/v3/webhooks/topics');

            if ($response->successful()) {
                $topics = $response->json();
                \Log::info('Available WooCommerce webhook topics', [
                    'topics' => $topics,
                    'vendor_id' => $integration->vendors__id
                ]);
                return $topics;
            } else {
                \Log::warning('Failed to get available webhook topics', [
                    'vendor_id' => $integration->vendors__id,
                    'response' => $response->body()
                ]);
                // Return default topics if API call fails
                return ['order.created', 'order.updated'];
            }
        } catch (Exception $e) {
            \Log::error('Error getting available webhook topics', [
                'vendor_id' => $integration->vendors__id,
                'error' => $e->getMessage()
            ]);
            // Return default topics if API call fails
            return ['order.created', 'order.updated'];
        }
    }

    /**
     * Verify webhook signature
     */
    protected function verifyWebhookSignature($request, WooCommerceIntegrationModel $integration): bool
    {
        // WooCommerce doesn't use HMAC signatures like Shopify
        // Instead, we can verify the webhook by checking if it's from our known site
        $userAgent = $request->header('User-Agent');
        
        if (str_contains($userAgent, 'WooCommerce')) {
            return true;
        }

        return false;
    }
} 