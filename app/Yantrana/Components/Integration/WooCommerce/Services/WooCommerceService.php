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
        ContactRepository $contactRepository,
        WooCommerceIntegrationRepository $wooCommerceIntegrationRepository,
        WooCommerceOrderRepository $wooCommerceOrderRepository,
        WooCommerceOrderNotificationRepository $wooCommerceOrderNotificationRepository,
        WhatsAppTemplateRepository $whatsAppTemplateRepository
    ) {
        $this->whatsAppServiceEngine = $whatsAppServiceEngine;
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
        try {
            \Log::info('WooCommerceService connect started', [
                'vendor_id' => $vendorId,
                'has_credentials' => !empty($credentials)
            ]);

            $siteUrl = $credentials['site_url'] ?? null;
            $consumerKey = $credentials['consumer_key'] ?? null;
            $consumerSecret = $credentials['consumer_secret'] ?? null;

            if (!$siteUrl || !$consumerKey || !$consumerSecret) {
                throw new Exception('Site URL, Consumer Key, and Consumer Secret are required');
            }

            // Remove trailing slash from site URL
            $siteUrl = rtrim($siteUrl, '/');

            \Log::info('Validating WooCommerce credentials', ['site_url' => $siteUrl]);

            // Validate WooCommerce credentials
            $validationResult = $this->validateWooCommerceCredentials($siteUrl, $consumerKey, $consumerSecret);

            if (!$validationResult['valid']) {
                throw new Exception($validationResult['error']);
            }

            // Check if integration already exists
            $existingIntegration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            if ($existingIntegration) {
                // Update existing integration
                $this->wooCommerceIntegrationRepository->update($existingIntegration->_id, [
                    'site_url' => $siteUrl,
                    'consumer_key' => $consumerKey,
                    'consumer_secret' => $consumerSecret,
                    'is_active' => true,
                    'connected_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            } else {
                            // Create new integration
            $this->wooCommerceIntegrationRepository->create([
                'vendors__id' => $vendorId,
                'site_url' => $siteUrl,
                'consumer_key' => $consumerKey,
                'consumer_secret' => $consumerSecret,
                'is_active' => true,
                'connected_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            }

            // Setup webhooks
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);
            $this->setupWebhooks($integration);

            return [
                'success' => true,
                'message' => 'WooCommerce integration connected successfully',
                'data' => [
                    'site_url' => $siteUrl,
                    'connected_at' => Carbon::now()
                ]
            ];

        } catch (Exception $e) {
            \Log::error('WooCommerceService connect failed', [
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

            \Log::info('WooCommerce webhook received', [
                'topic' => $topic,
                'vendor_id' => $vendorId,
                'payload_keys' => array_keys($payload)
            ]);

            // If vendor_id is null, try to find integration by webhook URL or other means
            if (!$vendorId) {
                // Check if this is a webhook verification or test request
                if ($request->has('challenge') || $request->header('X-WC-Webhook-Topic') === 'test') {
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

            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            if (!$integration || !$integration->is_active) {
                throw new Exception('WooCommerce integration not found or inactive');
            }

            // Verify webhook signature
            if (!$this->verifyWebhookSignature($request, $integration)) {
                throw new Exception('Invalid webhook signature');
            }

            // Only handle allowed webhook topics
            $allowedTopics = ['order.created', 'order.updated'];
            
            if (!in_array($topic, $allowedTopics)) {
                \Log::warning('WooCommerce webhook received with unsupported topic', [
                    'topic' => $topic,
                    'vendor_id' => $vendorId,
                    'allowed_topics' => $allowedTopics
                ]);
                return ['success' => false, 'message' => 'Unsupported webhook topic'];
            }

            // Handle different webhook topics
            switch ($topic) {
                case 'order.created':
                    return $this->processOrderCreated($payload, $integration);
                case 'order.updated':
                    return $this->processOrderUpdated($payload, $integration);
                default:
                    \Log::warning('Unhandled WooCommerce webhook topic', ['topic' => $topic]);
                    return ['success' => true, 'message' => 'Webhook processed (no action required)'];
            }

        } catch (Exception $e) {
            \Log::error('WooCommerce webhook processing failed', [
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
                            // Create new order
            $order = $this->wooCommerceOrderRepository->create([
                'vendors__id' => $vendorId,
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
        \Log::info('Processing WooCommerce order created', ['order_id' => $payload['id']]);

        $order = $this->wooCommerceOrderRepository->getByWooCommerceId($payload['id'], $integration->vendors__id);

        if (!$order) {
            // Create new order
            $order = $this->wooCommerceOrderRepository->create([
                'vendors__id' => $integration->vendors__id,
                'woocommerce_order_id' => $payload['id'],
                'order_number' => $payload['number'],
                'status' => $payload['status'],
                'total' => $payload['total'],
                'currency' => $payload['currency'],
                'customer_data' => $payload['billing'] ?? [],
                'order_data' => $payload,
                'created_at' => Carbon::parse($payload['date_created']),
                'updated_at' => Carbon::parse($payload['date_modified'])
            ]);
        }

        // Send order confirmation notification
        $this->sendNotification($order, 'order_confirmation', $integration);

        return ['success' => true, 'message' => 'Order created processed'];
    }

    /**
     * Process order updated webhook
     */
    protected function processOrderUpdated(array $payload, WooCommerceIntegrationModel $integration)
    {
        \Log::info('Processing WooCommerce order updated', ['order_id' => $payload['id']]);

        $order = $this->wooCommerceOrderRepository->getByWooCommerceId($payload['id'], $integration->vendors__id);

        if ($order) {
            $this->wooCommerceOrderRepository->update($order->_id, [
                'status' => $payload['status'],
                'total' => $payload['total'],
                'order_data' => $payload,
                'updated_at' => Carbon::parse($payload['date_modified'])
            ]);
        }

        return ['success' => true, 'message' => 'Order updated processed'];
    }



    /**
     * Send notification
     */
    protected function sendNotification(WooCommerceOrderModel $order, string $notificationType, WooCommerceIntegrationModel $integration)
    {
        try {
            // Get or create contact
            $contact = $this->getOrCreateContact($order->customer_data, $integration->vendors__id);

            if (!$contact) {
                throw new Exception('Failed to create or get contact');
            }

            // Get template
            $template = $this->getTemplateForNotificationType($notificationType, $integration->vendors__id);

            if (!$template) {
                throw new Exception("No template found for notification type: {$notificationType}");
            }

            // Prepare template variables
            $variables = $this->prepareTemplateVariables($order, $notificationType, $integration);

            // Send WhatsApp message
            $response = $this->whatsAppServiceEngine->sendTemplateMessage(
                $contact->phone,
                $template->template_name,
                $variables,
                $integration->vendors__id
            );

            // Create notification record
            $this->wooCommerceOrderNotificationRepository->create([
                'vendors__id' => $integration->vendors__id,
                'woocommerce_orders__id' => $order->_id,
                'notification_type' => $notificationType,
                'contacts__id' => $contact->_id,
                'whatsapp_templates__id' => $template->_id,
                'variables' => $variables,
                'status' => $response['success'] ? 'sent' : 'failed',
                'response' => $response,
                'sent_at' => $response['success'] ? Carbon::now() : null,
                'created_at' => Carbon::now()
            ]);

            return [
                'success' => $response['success'],
                'message' => $response['message'] ?? 'Notification sent successfully'
            ];

        } catch (Exception $e) {
            \Log::error('WooCommerce notification sending failed', [
                'order_id' => $order->_id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage()
            ]);

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
     * Get template for notification type
     */
    protected function getTemplateForNotificationType(string $notificationType, int $vendorId)
    {
        $templateName = $this->getTemplateName($notificationType);
        
        return $this->whatsAppTemplateRepository->getByVendorAndName($vendorId, $templateName);
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
            'order_trashed' => 'woocommerce_order_cancelled'
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

        if (!$phone && !$email) {
            return null;
        }

        // Try to find existing contact
        $contact = null;
        
        if ($phone) {
            $contact = $this->contactRepository->getByPhone($phone, $vendorId);
        }
        
        if (!$contact && $email) {
            $contact = $this->contactRepository->getByEmail($email, $vendorId);
        }

        if ($contact) {
            // Update contact with latest data
            $this->contactRepository->update($contact->_id, [
                'first_name' => $customerData['first_name'] ?? $contact->first_name,
                'last_name' => $customerData['last_name'] ?? $contact->last_name,
                'email' => $email ?? $contact->email,
                'phone' => $phone ?? $contact->phone,
                'updated_at' => Carbon::now()
            ]);
        } else {
            // Create new contact
            $contact = $this->contactRepository->create([
                'vendor_id' => $vendorId,
                'first_name' => $customerData['first_name'] ?? '',
                'last_name' => $customerData['last_name'] ?? '',
                'email' => $email,
                'phone' => $phone,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        return $contact;
    }

    /**
     * Validate WooCommerce credentials
     */
    protected function validateWooCommerceCredentials(string $siteUrl, string $consumerKey, string $consumerSecret): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)
            ])->get($siteUrl . '/wp-json/wc/v3/system_status');

            if ($response->successful()) {
                return [
                    'valid' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'valid' => false,
                    'error' => 'Invalid WooCommerce credentials'
                ];
            }
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => 'Failed to validate WooCommerce credentials: ' . $e->getMessage()
            ];
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