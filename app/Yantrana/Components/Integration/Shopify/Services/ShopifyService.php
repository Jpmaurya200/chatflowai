<?php
/**
 * ShopifyService.php - Service file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Yantrana\Components\WhatsAppService\WhatsAppServiceEngine;
use App\Yantrana\Components\Contact\Repositories\ContactRepository;
use App\Yantrana\Components\Integration\Shopify\Repositories\ShopifyIntegrationRepository;
use App\Yantrana\Components\Integration\Shopify\Repositories\ShopifyOrderRepository;
use App\Yantrana\Components\Integration\Shopify\Repositories\ShopifyOrderNotificationRepository;
use App\Yantrana\Components\Integration\Shopify\Models\ShopifyIntegrationModel;
use App\Yantrana\Components\Integration\Shopify\Models\ShopifyOrderModel;
use App\Yantrana\Components\Integration\Shopify\Models\ShopifyOrderNotificationModel;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository;

class ShopifyService
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
     * @var ShopifyIntegrationRepository - Shopify Integration Repository
     */
    protected $shopifyIntegrationRepository;

    /**
     * @var ShopifyOrderRepository - Shopify Order Repository
     */
    protected $shopifyOrderRepository;

    /**
     * @var ShopifyOrderNotificationRepository - Shopify Order Notification Repository
     */
    protected $shopifyOrderNotificationRepository;

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
        ShopifyIntegrationRepository $shopifyIntegrationRepository,
        ShopifyOrderRepository $shopifyOrderRepository,
        ShopifyOrderNotificationRepository $shopifyOrderNotificationRepository,
        WhatsAppTemplateRepository $whatsAppTemplateRepository
    ) {
        $this->whatsAppServiceEngine = $whatsAppServiceEngine;
        $this->contactRepository = $contactRepository;
        $this->shopifyIntegrationRepository = $shopifyIntegrationRepository;
        $this->shopifyOrderRepository = $shopifyOrderRepository;
        $this->shopifyOrderNotificationRepository = $shopifyOrderNotificationRepository;
        $this->whatsAppTemplateRepository = $whatsAppTemplateRepository;
    }

    /**
     * Connect Shopify integration
     */
    public function connect(array $credentials, $vendorId = null): array
    {
        try {
            \Log::info('ShopifyService connect started', [
                'vendor_id' => $vendorId,
                'has_credentials' => !empty($credentials)
            ]);

            $shopDomain = $credentials['shop_domain'] ?? null;
            $accessToken = $credentials['access_token'] ?? null;

            // Always append .myshopify.com if not present
            if ($shopDomain && !str_ends_with($shopDomain, '.myshopify.com')) {
                $shopDomain = $shopDomain . '.myshopify.com';
            }

            if (!$shopDomain || !$accessToken) {
                throw new Exception('Shop domain and access token are required');
            }

            \Log::info('Validating Shopify credentials', ['shop_domain' => $shopDomain]);

            // Validate Shopify credentials
            $shopData = $this->validateShopifyCredentials($shopDomain, $accessToken);

            \Log::info('Shopify credentials validated', ['shop_data' => $shopData]);

            // Create or update integration
            $integrationData = [
                'vendors__id' => $vendorId,
                'shop_domain' => $shopDomain,
                'access_token' => $accessToken,
                'is_active' => true,
                'connected_at' => now(),
                'webhook_url' => route('shopify.webhook', ['vendorId' => $vendorId]),
            ];

            \Log::info('Creating/updating integration', $integrationData);

            $integration = $this->shopifyIntegrationRepository->createOrUpdate($integrationData);
            $integration->setShopData($shopData);

            \Log::info('Integration created/updated', ['integration_id' => $integration->_id]);

            // Setup webhooks
            $this->setupWebhooks($integration);

            \Log::info('Shopify connection completed successfully');

            return [
                'success' => true,
                'message' => 'Shopify integration connected successfully',
                'data' => [
                    'shop_domain' => $shopDomain,
                    'shop_name' => $shopData['name'] ?? '',
                    'webhook_configured' => true,
                ]
            ];

        } catch (Exception $e) {
            Log::error('Shopify connection failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect Shopify: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Disconnect Shopify integration
     */
    public function disconnect($vendorId = null): array
    {
        try {
            $integration = $this->shopifyIntegrationRepository->getByVendorId($vendorId);

            if (!$integration) {
                return [
                    'success' => false,
                    'message' => 'No Shopify integration found for this vendor',
                ];
            }

            // Remove webhooks
            $this->removeWebhooks($integration);

            // Update integration status
            $this->shopifyIntegrationRepository->updateStatus($vendorId, false);

            return [
                'success' => true,
                'message' => 'Shopify integration disconnected successfully',
            ];

        } catch (Exception $e) {
            Log::error('Shopify disconnection failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to disconnect Shopify: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get integration status
     */
    public function getIntegrationStatus($vendorId = null): array
    {
        try {
            return $this->shopifyIntegrationRepository->getStatistics($vendorId);
        } catch (Exception $e) {
            Log::error('Failed to get Shopify integration status', [
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
     * Process Shopify webhook
     */
    public function processWebhook($request, $vendorId = null)
    {
        try {
            $payload = $request->all();
            $topic = $request->header('X-Shopify-Topic');
            $shopDomain = $request->header('X-Shopify-Shop-Domain');

            Log::info('Shopify webhook received', [
                'topic' => $topic,
                'shop_domain' => $shopDomain,
                'vendor_id' => $vendorId
            ]);

            // Verify webhook signature
            if (!$this->verifyWebhookSignature($request)) {
                throw new Exception('Invalid webhook signature');
            }

            // Get integration
            $integration = null;
            if ($shopDomain) {
                $integration = $this->shopifyIntegrationRepository->getByShopDomain($shopDomain);
            } elseif ($vendorId) {
                $integration = $this->shopifyIntegrationRepository->getActiveByVendorId($vendorId);
            }
            
            if (!$integration || !$integration->isActive()) {
                throw new Exception('No active integration found for shop: ' . ($shopDomain ?? 'unknown') . ' or vendor: ' . $vendorId);
            }

            // Process based on topic
            switch ($topic) {
                case 'orders/create':
                    return $this->processOrderCreated($payload, $integration);
                case 'orders/updated':
                    return $this->processOrderUpdated($payload, $integration);
                case 'orders/paid':
                    return $this->processOrderPaid($payload, $integration);
                case 'orders/fulfilled':
                    return $this->processOrderFulfilled($payload, $integration);
                case 'orders/cancelled':
                    return $this->processOrderCancelled($payload, $integration);
                case 'refunds/create':
                    return $this->processRefundCreated($payload, $integration);
                default:
                    Log::info('Unhandled Shopify webhook topic', ['topic' => $topic]);
                    return ['success' => true, 'message' => 'Webhook processed'];
            }

        } catch (Exception $e) {
            Log::error('Shopify webhook processing failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Webhook processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send order notification
     */
    public function sendOrderNotification(array $orderData, string $notificationType, $vendorId = null)
    {
        try {
            \Log::info('Starting order notification process', [
                'notification_type' => $notificationType,
                'vendor_id' => $vendorId,
                'order_id' => $orderData['id'] ?? 'unknown',
                'order_data_keys' => array_keys($orderData),
                'customer_data_exists' => isset($orderData['customer']),
                'customer_id' => $orderData['customer']['id'] ?? 'no_customer_id',
                'has_billing_address' => isset($orderData['billing_address']),
                'has_shipping_address' => isset($orderData['shipping_address']),
                'order_email' => $orderData['email'] ?? 'N/A',
                'order_phone' => $orderData['phone'] ?? 'N/A'
            ]);

            $integration = $this->shopifyIntegrationRepository->getActiveByVendorId($vendorId);
            if (!$integration) {
                throw new Exception('No active Shopify integration found');
            }

            // Get or create contact
            $contact = $this->getOrCreateContact($orderData, $vendorId);

            // Create or update order
            $orderDataForStorage = [
                'shopify_integrations__id' => $integration->_id,
                'vendors__id' => $vendorId,
                'contacts__id' => $contact->_id,
                'shopify_order_id' => $orderData['id'],
                'order_number' => $orderData['order_number'] ?? $orderData['name'],
                'name' => $orderData['name'],
                'email' => $orderData['email'] ?? '',
                'phone' => $orderData['phone'] ?? '',
                'currency' => $orderData['currency'] ?? 'USD',
                'financial_status' => $orderData['financial_status'] ?? 'pending',
                'fulfillment_status' => $orderData['fulfillment_status'] ?? 'unfulfilled',
                'total_price' => $orderData['total_price'] ?? 0,
                'subtotal_price' => $orderData['subtotal_price'] ?? 0,
                'total_tax' => $orderData['total_tax'] ?? 0,
                'total_discounts' => $orderData['total_discounts'] ?? 0,
                'total_weight' => $orderData['total_weight'] ?? 0,
                'total_items' => $orderData['total_items'] ?? 0,
                'tags' => $orderData['tags'] ?? '',
                'note' => $orderData['note'] ?? '',
                'status' => $orderData['status'] ?? 'open',
                'created_at_shopify' => $orderData['created_at'] ?? now(),
                'updated_at_shopify' => $orderData['updated_at'] ?? now(),
                '__data' => [
                    'shopify_order_data' => $orderData,
                    'customer_data' => $orderData['customer'] ?? [],
                    'billing_address' => $orderData['billing_address'] ?? [],
                    'shipping_address' => $orderData['shipping_address'] ?? [],
                    'line_items' => $orderData['line_items'] ?? [],
                    'fulfillments' => $orderData['fulfillments'] ?? [],
                    'refunds' => $orderData['refunds'] ?? [],
                ]
            ];

            \Log::info('Storing order data', [
                'order_id' => $orderData['id'],
                'line_items_count' => count($orderData['line_items'] ?? []),
                'line_items_data' => $orderData['line_items'] ?? [],
                '__data_line_items_count' => count($orderDataForStorage['__data']['line_items']),
            ]);

            $order = $this->shopifyOrderRepository->createOrUpdate($orderDataForStorage);

            // Send notification
            return $this->sendNotification($order, $notificationType, $integration);

        } catch (Exception $e) {
            Log::error('Failed to send order notification', [
                'notification_type' => $notificationType,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process order created webhook
     */
    protected function processOrderCreated(array $payload, ShopifyIntegrationModel $integration)
    {
        $orderData = $payload['order'] ?? $payload;
        
        // Check if order confirmation is enabled
        $notificationTypes = $integration->getNotificationTypes();
        
        // If no notification types are configured, enable default ones
        if (empty($notificationTypes)) {
            $notificationTypes = ['order_confirmation', 'payment_confirmation', 'shipment_tracking'];
            \Log::info('No notification types configured, using defaults', [
                'integration_id' => $integration->_id,
                'vendor_id' => $integration->vendors__id,
                'default_types' => $notificationTypes
            ]);
        }
        
        \Log::info('Processing order created webhook', [
            'integration_id' => $integration->_id,
            'vendor_id' => $integration->vendors__id,
            'notification_types' => $notificationTypes,
            'has_order_confirmation' => in_array('order_confirmation', $notificationTypes),
            'order_id' => $orderData['id'] ?? 'unknown'
        ]);
        
        if (in_array('order_confirmation', $notificationTypes)) {
            $this->sendOrderNotification($orderData, 'order_confirmation', $integration->vendors__id);
        } else {
            \Log::info('Order confirmation notification not enabled, skipping', [
                'integration_id' => $integration->_id,
                'vendor_id' => $integration->vendors__id
            ]);
        }

        return ['success' => true, 'message' => 'Order created processed'];
    }

    /**
     * Process order paid webhook
     */
    protected function processOrderPaid(array $payload, ShopifyIntegrationModel $integration)
    {
        $orderData = $payload['order'] ?? $payload;
        
        // Check if payment confirmation is enabled
        $notificationTypes = $integration->getNotificationTypes();
        
        // If no notification types are configured, enable default ones
        if (empty($notificationTypes)) {
            $notificationTypes = ['order_confirmation', 'payment_confirmation', 'shipment_tracking'];
        }
        
        if (in_array('payment_confirmation', $notificationTypes)) {
            $this->sendOrderNotification($orderData, 'payment_confirmation', $integration->vendors__id);
        }

        return ['success' => true, 'message' => 'Order paid processed'];
    }

    /**
     * Process order fulfilled webhook
     */
    protected function processOrderFulfilled(array $payload, ShopifyIntegrationModel $integration)
    {
        $orderData = $payload['order'] ?? $payload;
        
        // Check if shipment tracking is enabled
        $notificationTypes = $integration->getNotificationTypes();
        
        // If no notification types are configured, enable default ones
        if (empty($notificationTypes)) {
            $notificationTypes = ['order_confirmation', 'payment_confirmation', 'shipment_tracking'];
        }
        
        if (in_array('shipment_tracking', $notificationTypes)) {
            $this->sendOrderNotification($orderData, 'shipment_tracking', $integration->vendors__id);
        }

        return ['success' => true, 'message' => 'Order fulfilled processed'];
    }

    /**
     * Process order cancelled webhook
     */
    protected function processOrderCancelled(array $payload, ShopifyIntegrationModel $integration)
    {
        $orderData = $payload['order'] ?? $payload;
        
        // Check if order cancelled notification is enabled
        $notificationTypes = $integration->getNotificationTypes();
        
        // If no notification types are configured, enable default ones
        if (empty($notificationTypes)) {
            $notificationTypes = ['order_confirmation', 'payment_confirmation', 'shipment_tracking', 'order_cancelled'];
        }
        
        if (in_array('order_cancelled', $notificationTypes)) {
            $this->sendOrderNotification($orderData, 'order_cancelled', $integration->vendors__id);
        }

        return ['success' => true, 'message' => 'Order cancelled processed'];
    }

    /**
     * Process order updated webhook
     */
    protected function processOrderUpdated(array $payload, ShopifyIntegrationModel $integration)
    {
        $orderData = $payload['order'] ?? $payload;
        
        // Update order in database
        $this->shopifyOrderRepository->createOrUpdate([
            'shopify_integrations__id' => $integration->_id,
            'vendors__id' => $integration->vendors__id,
            'shopify_order_id' => $orderData['id'],
            'order_number' => $orderData['order_number'] ?? $orderData['name'],
            'name' => $orderData['name'],
            'email' => $orderData['email'] ?? '',
            'phone' => $orderData['phone'] ?? '',
            'currency' => $orderData['currency'] ?? 'USD',
            'financial_status' => $orderData['financial_status'] ?? 'pending',
            'fulfillment_status' => $orderData['fulfillment_status'] ?? 'unfulfilled',
            'total_price' => $orderData['total_price'] ?? 0,
            'subtotal_price' => $orderData['subtotal_price'] ?? 0,
            'total_tax' => $orderData['total_tax'] ?? 0,
            'total_discounts' => $orderData['total_discounts'] ?? 0,
            'total_weight' => $orderData['total_weight'] ?? 0,
            'total_items' => $orderData['total_items'] ?? 0,
            'tags' => $orderData['tags'] ?? '',
            'note' => $orderData['note'] ?? '',
            'status' => $orderData['status'] ?? 'open',
            'updated_at_shopify' => $orderData['updated_at'] ?? now(),
            '__data' => [
                'shopify_order_data' => $orderData,
                'customer_data' => $orderData['customer'] ?? [],
                'billing_address' => $orderData['billing_address'] ?? [],
                'shipping_address' => $orderData['shipping_address'] ?? [],
                'line_items' => $orderData['line_items'] ?? [],
                'fulfillments' => $orderData['fulfillments'] ?? [],
                'refunds' => $orderData['refunds'] ?? [],
            ]
        ]);

        return ['success' => true, 'message' => 'Order updated processed'];
    }

    /**
     * Process refund created webhook
     */
    protected function processRefundCreated(array $payload, ShopifyIntegrationModel $integration)
    {
        $orderData = $payload['order'] ?? $payload;
        
        // Check if refund notification is enabled
        $notificationTypes = $integration->getNotificationTypes();
        
        // If no notification types are configured, enable default ones
        if (empty($notificationTypes)) {
            $notificationTypes = ['order_confirmation', 'payment_confirmation', 'shipment_tracking', 'order_cancelled', 'refund_processed'];
        }
        
        if (in_array('refund_processed', $notificationTypes)) {
            $this->sendOrderNotification($orderData, 'refund_processed', $integration->vendors__id);
        }

        return ['success' => true, 'message' => 'Refund created processed'];
    }

    /**
     * Send notification via WhatsApp
     */
    protected function sendNotification(ShopifyOrderModel $order, string $notificationType, ShopifyIntegrationModel $integration)
    {
        try {
            \Log::info('=== SEND NOTIFICATION START ===');
            \Log::info('Sending notification', [
                'order_id' => $order->_id,
                'notification_type' => $notificationType,
                'vendor_id' => $integration->vendors__id
            ]);

            // Create notification record
            $notification = $this->shopifyOrderNotificationRepository->createNotification([
                'shopify_orders__id' => $order->_id,
                'vendors__id' => $order->vendors__id,
                'notification_type' => $notificationType,
                'status' => 'pending'
            ]);

            // Get or create contact
            $contact = $this->getOrCreateContact($order->__data, $order->vendors__id);
            
            if (!$contact) {
                \Log::error('No contact found for order', [
                    'order_id' => $order->_id,
                    'vendor_id' => $order->vendors__id
                ]);
                
                $notification->markAsFailed('No contact found for order');
                $notification->save();
                
                return [
                    'success' => false,
                    'message' => 'No contact found for order'
                ];
            }

            // Get template for this notification type
            $template = $this->getTemplateForNotificationType($notificationType, $order->vendors__id);
            
            if (!$template) {
                \Log::error('No template found for notification type', [
                    'notification_type' => $notificationType,
                    'vendor_id' => $integration->vendors__id
                ]);
                throw new Exception("No WhatsApp template found for notification type: {$notificationType}. Please create a template with name 'shopify_{$notificationType}' or 'shopify_generic_notification'");
            }

            // Prepare template variables using mappings
            $templateVariables = $this->prepareTemplateVariables($order, $notificationType, $integration);

            // Send via WhatsApp
            $whatsAppResult = $this->whatsAppServiceEngine->sendTemplateMessageProcess(
                [
                    'contact_uid' => $order->contact->uid,
                    'template_uid' => $template->_uid,
                    'template_variables' => $templateVariables,
                ],
                $order->contact,
                false,
                null,
                $order->vendors__id
            );

            if ($whatsAppResult->success()) {
                $notification->markAsSent($whatsAppResult->data('message_id') ?? null);
                $notification->setWhatsAppMessageData($whatsAppResult->data() ?? []);
                $notification->save();

                \Log::info('=== SEND NOTIFICATION END ===', [
                    'success' => true,
                    'notification_id' => $notification->_id,
                    'message' => 'Notification sent successfully'
                ]);

                return [
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'notification_id' => $notification->_id,
                ];
            } else {
                $notification->markAsFailed($whatsAppResult->message() ?? 'WhatsApp sending failed');
                $notification->save();

                \Log::error('Shopify notification sending failed', [
                    'order_id' => $order->_id,
                    'notification_type' => $notificationType,
                    'error' => $whatsAppResult->message() ?? 'Unknown error'
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send notification: ' . ($whatsAppResult->message() ?? 'Unknown error'),
                ];
            }

        } catch (Exception $e) {
            Log::error('Failed to send notification', [
                'order_id' => $order->_id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage()
            ]);

            // Create failed notification record if not already created
            if (isset($notification)) {
                $notification->markAsFailed($e->getMessage());
                $notification->save();
            }

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare template variables using mappings
     */
    protected function prepareTemplateVariables(ShopifyOrderModel $order, string $notificationType, ShopifyIntegrationModel $integration): array
    {
        try {
            $variables = [];
            $variableMappings = $integration->getVariableMappings($notificationType);
            
            // Get order data as array for easy access
            $orderData = $this->getOrderDataForVariables($order);
            
            \Log::info('Preparing template variables', [
                'order_id' => $order->_id,
                'notification_type' => $notificationType,
                'variable_mappings' => $variableMappings,
                'order_data_keys' => array_keys($orderData)
            ]);
            
            // Map template variables to Shopify data
            foreach ($variableMappings as $templateVariable => $shopifyVariable) {
                if (!empty($shopifyVariable)) {
                    if (isset($orderData[$shopifyVariable])) {
                        $variables[$templateVariable] = $orderData[$shopifyVariable];
                    } else {
                        // Provide fallback values for missing data
                        $fallbackValue = $this->getFallbackValue($shopifyVariable, $order);
                        $variables[$templateVariable] = $fallbackValue;
                        
                        \Log::warning('Missing template variable, using fallback', [
                            'template_variable' => $templateVariable,
                            'shopify_variable' => $shopifyVariable,
                            'fallback_value' => $fallbackValue
                        ]);
                    }
                }
            }
            
            \Log::info('Template variables prepared successfully', [
                'order_id' => $order->_id,
                'variables_count' => count($variables),
                'variables' => array_keys($variables)
            ]);
            
            return $variables;
            
        } catch (\Exception $e) {
            \Log::error('Error preparing template variables', [
                'order_id' => $order->_id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return basic variables as fallback
            return [
                'order_number' => $order->order_number ?? 'Unknown',
                'customer_name' => $order->getCustomerName(),
                'total_price' => $order->formatted_total_price ?? '0.00'
            ];
        }
    }

    /**
     * Get fallback value for missing template variables
     */
    protected function getFallbackValue(string $shopifyVariable, ShopifyOrderModel $order): string
    {
        switch ($shopifyVariable) {
            case 'customer_first_name':
                return $order->getCustomerFirstName() ?: 'Customer';
            case 'customer_last_name':
                return $order->getCustomerLastName() ?: '';
            case 'customer_name':
                return $order->getCustomerName() ?: 'Customer';
            case 'customer_email':
                return $order->getCustomerEmail() ?: 'N/A';
            case 'customer_phone':
                return $order->getCustomerPhone() ?: 'N/A';
            case 'order_number':
                return $order->order_number ?: 'Unknown';
            case 'total_price':
                return $order->formatted_total_price ?: '0.00';
            case 'currency':
                return $order->currency ?: 'USD';
            default:
                return 'N/A';
        }
    }

    /**
     * Get order data formatted for template variables
     */
    protected function getOrderDataForVariables(ShopifyOrderModel $order): array
    {
        try {
            $data = [
                // Order Information
                'order_number' => $order->order_number ?? 'Unknown',
                'order_name' => $order->name ?? 'Unknown',
                'order_id' => $order->shopify_order_id ?? 'Unknown',
                'total_price' => $order->formatted_total_price ?? '0.00',
                'subtotal_price' => number_format($order->subtotal_price ?? 0, 2),
                'total_tax' => number_format($order->total_tax ?? 0, 2),
                'total_discounts' => number_format($order->total_discounts ?? 0, 2),
                'currency' => $order->currency ?? 'USD',
                'financial_status' => $order->financial_status_label ?? 'Unknown',
                'fulfillment_status' => $order->fulfillment_status_label ?? 'Unknown',
                'order_date' => $order->created_at_shopify ? $order->created_at_shopify->format('M d, Y') : '',
                'processed_at' => $order->processed_at ? $order->processed_at->format('M d, Y') : '',
                
                // Customer Information
                'customer_name' => $order->getCustomerName() ?: 'Customer',
                'customer_email' => $order->email ?? 'N/A',
                'customer_phone' => $order->phone ?? 'N/A',
                'customer_first_name' => $order->getCustomerFirstName() ?: 'Customer',
                'customer_last_name' => $order->getCustomerLastName() ?: '',
                
                // Address Information
                'shipping_address_name' => $order->getShippingAddress()['name'] ?? '',
                'shipping_address_company' => $order->getShippingAddress()['company'] ?? '',
                'shipping_address_address1' => $order->getShippingAddress()['address1'] ?? '',
                'shipping_address_address2' => $order->getShippingAddress()['address2'] ?? '',
                'shipping_address_city' => $order->getShippingAddress()['city'] ?? '',
                'shipping_address_province' => $order->getShippingAddress()['province'] ?? '',
                'shipping_address_country' => $order->getShippingAddress()['country'] ?? '',
                'shipping_address_zip' => $order->getShippingAddress()['zip'] ?? '',
                'shipping_address_phone' => $order->getShippingAddress()['phone'] ?? '',
                
                'billing_address_name' => $order->getBillingAddress()['name'] ?? '',
                'billing_address_company' => $order->getBillingAddress()['company'] ?? '',
                'billing_address_address1' => $order->getBillingAddress()['address1'] ?? '',
                'billing_address_address2' => $order->getBillingAddress()['address2'] ?? '',
                'billing_address_city' => $order->getBillingAddress()['city'] ?? '',
                'billing_address_province' => $order->getBillingAddress()['province'] ?? '',
                'billing_address_country' => $order->getBillingAddress()['country'] ?? '',
                'billing_address_zip' => $order->getBillingAddress()['zip'] ?? '',
                'billing_address_phone' => $order->getBillingAddress()['phone'] ?? '',
                
                // Line Items
                'line_items_summary' => $order->getFormattedLineItems(),
                'total_items' => $order->total_items ?? 0,
                'total_weight' => $order->total_weight ?? 0,
                
                // Fulfillment
                'tracking_number' => $this->getTrackingNumber($order),
                'tracking_company' => $this->getTrackingCompany($order),
                'tracking_url' => $this->getTrackingUrl($order),
                'fulfillment_date' => $this->getFulfillmentDate($order),
                
                // Additional
                'note' => $order->note ?? '',
                'tags' => $order->tags ?? '',
                'shop_domain' => $order->integration->shop_domain ?? '',
            ];
            
            return $data;
            
        } catch (\Exception $e) {
            \Log::error('Error getting order data for variables', [
                'order_id' => $order->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return basic data as fallback
            return [
                'order_number' => $order->order_number ?? 'Unknown',
                'customer_name' => $order->getCustomerName() ?: 'Customer',
                'total_price' => $order->formatted_total_price ?? '0.00',
                'currency' => $order->currency ?? 'USD'
            ];
        }
    }

    /**
     * Get tracking number from fulfillments
     */
    protected function getTrackingNumber(ShopifyOrderModel $order): string
    {
        $fulfillments = $order->getFulfillments();
        if (!empty($fulfillments)) {
            foreach ($fulfillments as $fulfillment) {
                if (!empty($fulfillment['tracking_number'])) {
                    return $fulfillment['tracking_number'];
                }
            }
        }
        return '';
    }

    /**
     * Get tracking company from fulfillments
     */
    protected function getTrackingCompany(ShopifyOrderModel $order): string
    {
        $fulfillments = $order->getFulfillments();
        if (!empty($fulfillments)) {
            foreach ($fulfillments as $fulfillment) {
                if (!empty($fulfillment['tracking_company'])) {
                    return $fulfillment['tracking_company'];
                }
            }
        }
        return '';
    }

    /**
     * Get tracking URL from fulfillments
     */
    protected function getTrackingUrl(ShopifyOrderModel $order): string
    {
        $fulfillments = $order->getFulfillments();
        if (!empty($fulfillments)) {
            foreach ($fulfillments as $fulfillment) {
                if (!empty($fulfillment['tracking_url'])) {
                    return $fulfillment['tracking_url'];
                }
            }
        }
        return '';
    }

    /**
     * Get fulfillment date
     */
    protected function getFulfillmentDate(ShopifyOrderModel $order): string
    {
        $fulfillments = $order->getFulfillments();
        if (!empty($fulfillments)) {
            foreach ($fulfillments as $fulfillment) {
                if (!empty($fulfillment['created_at'])) {
                    return date('M d, Y', strtotime($fulfillment['created_at']));
                }
            }
        }
        return '';
    }

    /**
     * Get template for notification type
     */
    protected function getTemplateForNotificationType(string $notificationType, int $vendorId)
    {
        // Get the integration to check for configured templates
        $integration = $this->shopifyIntegrationRepository->getByVendorId($vendorId);
        
        if ($integration) {
            // Check if there's a configured template for this notification type
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

        // If specific template not found, try to find a generic Shopify template
        if (!$template) {
            \Log::info('Specific template not found, looking for generic Shopify template');
            $template = $this->whatsAppTemplateRepository->fetchIt([
                'vendors__id' => $vendorId,
                'template_name' => 'shopify_generic_notification',
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
            \Log::info('No Shopify templates found, looking for any approved template');
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
            'order_confirmation' => 'shopify_order_confirmation',
            'payment_confirmation' => 'shopify_payment_confirmation',
            'shipment_tracking' => 'shopify_shipment_tracking',
            'delivery_confirmation' => 'shopify_delivery_confirmation',
            'cod_verification' => 'shopify_cod_verification',
            'order_cancelled' => 'shopify_order_cancelled',
            'refund_processed' => 'shopify_refund_processed',
        ];

        return $templateMap[$notificationType] ?? 'shopify_generic_notification';
    }

    /**
     * Get or create contact
     */
    protected function getOrCreateContact(array $orderData, int $vendorId)
    {
        \Log::info('Starting getOrCreateContact method', [
            'order_id' => $orderData['id'] ?? 'unknown',
            'vendor_id' => $vendorId,
            'has_customer_data' => isset($orderData['customer']),
            'has_billing_address' => isset($orderData['billing_address']),
            'has_shipping_address' => isset($orderData['shipping_address']),
            'customer_keys' => isset($orderData['customer']) ? array_keys($orderData['customer']) : 'no_customer',
            'billing_keys' => isset($orderData['billing_address']) ? array_keys($orderData['billing_address']) : 'no_billing'
        ]);

        // Extract phone from various possible locations in Shopify order data
        $phone = $orderData['phone'] ?? 
                 $orderData['customer']['phone'] ?? 
                 $orderData['billing_address']['phone'] ?? 
                 $orderData['shipping_address']['phone'] ?? '';
        
        // Extract email from various possible locations
        $email = $orderData['email'] ?? 
                 $orderData['customer']['email'] ?? 
                 $orderData['billing_address']['email'] ?? 
                 $orderData['shipping_address']['email'] ?? '';
        
        // Extract name from various possible locations
        $firstName = $orderData['customer']['first_name'] ?? 
                    $orderData['billing_address']['first_name'] ?? 
                    $orderData['shipping_address']['first_name'] ?? '';
        
        $lastName = $orderData['customer']['last_name'] ?? 
                   $orderData['billing_address']['last_name'] ?? 
                   $orderData['shipping_address']['last_name'] ?? '';
        
        $name = trim($firstName . ' ' . $lastName);

        // For COD orders, try to extract phone from billing address if not found
        if (!$phone && isset($orderData['billing_address'])) {
            $phone = $orderData['billing_address']['phone'] ?? '';
        }

        // For orders without customer data, try to use order name as fallback
        if (!$name && isset($orderData['name'])) {
            $name = $orderData['name'];
        }

        // Additional fallback for email from customer data
        if (!$email && isset($orderData['customer']['email'])) {
            $email = $orderData['customer']['email'];
        }

        // Additional fallback for phone from customer data
        if (!$phone && isset($orderData['customer']['phone'])) {
            $phone = $orderData['customer']['phone'];
        }

        // Try to get name from customer data if not found
        if (!$name && isset($orderData['customer']['first_name'])) {
            $firstName = $orderData['customer']['first_name'];
            $lastName = $orderData['customer']['last_name'] ?? '';
            $name = trim($firstName . ' ' . $lastName);
        }

        // If we don't have contact info but have a customer ID, try to fetch from Shopify API
        if ((!$phone || !$email) && isset($orderData['customer']['id'])) {
            $customerData = $this->fetchCustomerFromShopify($orderData['customer']['id'], $vendorId);
            if ($customerData && is_array($customerData)) {
                // Try to get phone from various locations
                if (!$phone) {
                    $phone = $customerData['phone'] ?? '';
                    if (empty($phone) && isset($customerData['default_address']['phone'])) {
                        $phone = $customerData['default_address']['phone'];
                    }
                    if (empty($phone) && isset($customerData['addresses']) && is_array($customerData['addresses'])) {
                        foreach ($customerData['addresses'] as $address) {
                            if (!empty($address['phone'])) {
                                $phone = $address['phone'];
                                break;
                            }
                        }
                    }
                }
                
                // Try to get email
                if (!$email && isset($customerData['email']) && !empty($customerData['email'])) {
                    $email = $customerData['email'];
                }
                
                // Try to get name from various locations
                if (!$name) {
                    if (isset($customerData['first_name']) && !empty($customerData['first_name'])) {
                        $firstName = $customerData['first_name'];
                        $lastName = isset($customerData['last_name']) ? $customerData['last_name'] : '';
                        $name = trim($firstName . ' ' . $lastName);
                    } elseif (isset($customerData['default_address']['first_name']) && !empty($customerData['default_address']['first_name'])) {
                        $firstName = $customerData['default_address']['first_name'];
                        $lastName = isset($customerData['default_address']['last_name']) ? $customerData['default_address']['last_name'] : '';
                        $name = trim($firstName . ' ' . $lastName);
                    }
                }
                
                \Log::info('Fetched customer data from Shopify API', [
                    'customer_id' => $orderData['customer']['id'],
                    'phone' => $phone,
                    'email' => $email,
                    'name' => $name,
                    'customer_data_keys' => array_keys($customerData),
                    'found_phone' => !empty($phone),
                    'found_email' => !empty($email),
                    'found_name' => !empty($name)
                ]);
            } else {
                \Log::warning('Failed to fetch customer data from Shopify API or data is invalid', [
                    'customer_id' => $orderData['customer']['id'],
                    'customer_data' => $customerData
                ]);
            }
        }

        // If we still don't have contact info, try to fetch from Shopify API using customer ID
        if ((!$phone || !$email) && isset($orderData['customer']['id'])) {
            $customerId = $orderData['customer']['id'];
            \Log::info('Attempting to fetch customer data from Shopify API', [
                'customer_id' => $customerId,
                'current_phone' => $phone,
                'current_email' => $email
            ]);
            
            $customerData = $this->fetchCustomerFromShopify($customerId, $vendorId);
            if ($customerData && is_array($customerData)) {
                // Update phone if not found
                if (!$phone) {
                    $phone = $customerData['phone'] ?? '';
                    if (empty($phone) && isset($customerData['default_address']['phone'])) {
                        $phone = $customerData['default_address']['phone'];
                    }
                    if (empty($phone) && isset($customerData['addresses']) && is_array($customerData['addresses'])) {
                        foreach ($customerData['addresses'] as $address) {
                            if (!empty($address['phone'])) {
                                $phone = $address['phone'];
                                break;
                            }
                        }
                    }
                }
                
                // Update email if not found
                if (!$email && isset($customerData['email']) && !empty($customerData['email'])) {
                    $email = $customerData['email'];
                }
                
                // Update name if not found
                if (!$name) {
                    if (isset($customerData['first_name']) && !empty($customerData['first_name'])) {
                        $firstName = $customerData['first_name'];
                        $lastName = isset($customerData['last_name']) ? $customerData['last_name'] : '';
                        $name = trim($firstName . ' ' . $lastName);
                    } elseif (isset($customerData['default_address']['first_name']) && !empty($customerData['default_address']['first_name'])) {
                        $firstName = $customerData['default_address']['first_name'];
                        $lastName = isset($customerData['default_address']['last_name']) ? $customerData['default_address']['last_name'] : '';
                        $name = trim($firstName . ' ' . $lastName);
                    }
                }
                
                \Log::info('Successfully updated contact info from Shopify API', [
                    'customer_id' => $customerId,
                    'phone' => $phone,
                    'email' => $email,
                    'name' => $name,
                    'found_phone' => !empty($phone),
                    'found_email' => !empty($email),
                    'found_name' => !empty($name)
                ]);
            } else {
                \Log::warning('Failed to fetch customer data from Shopify API', [
                    'customer_id' => $customerId
                ]);
            }
        }

        \Log::info('Extracted contact information', [
            'phone' => $phone,
            'email' => $email,
            'name' => $name,
            'has_phone' => !empty($phone),
            'has_email' => !empty($email),
            'has_name' => !empty($name),
            'order_id' => $orderData['id'] ?? 'unknown',
            'order_number' => $orderData['order_number'] ?? $orderData['name'] ?? 'unknown',
            'customer_id' => $orderData['customer']['id'] ?? 'no_customer_id'
        ]);

        // If we still don't have contact info after all attempts, log and create a placeholder contact
        if (!$phone && !$email) {
            \Log::warning('No contact information found after all attempts, creating placeholder contact', [
                'order_id' => $orderData['id'] ?? 'unknown',
                'order_number' => $orderData['order_number'] ?? $orderData['name'] ?? 'unknown',
                'customer_id' => $orderData['customer']['id'] ?? 'no_customer_id',
                'customer_data' => $orderData['customer'] ?? 'no_customer_data'
            ]);
            
            // Create a minimal contact for orders without customer data
            return $this->createMinimalContact($orderData, $vendorId);
        }

        // Try to find existing contact
        $contact = null;
        if ($phone) {
            // Convert phone to integer for getVendorContactByWaId method
            $phoneInt = (int) preg_replace('/[^0-9]/', '', $phone);
            $contact = $this->contactRepository->getVendorContactByWaId($phoneInt, $vendorId);
        }
        
        if (!$contact && $email) {
            $contact = $this->contactRepository->fetchIt([
                'vendors__id' => $vendorId,
                'email' => $email,
            ]);
        }

        // Create new contact if not found
        if (!$contact) {
            $contactData = [
                'vendors__id' => $vendorId,
                'first_name' => $name,
                'email' => $email,
                'phone_number' => $phone, // ContactRepository expects phone_number
                'wa_id' => $phone,
                'status' => 'active',
                '__data' => [
                    'shopify_customer_id' => $orderData['customer']['id'] ?? null,
                    'shopify_order_id' => $orderData['id'] ?? null,
                    'order_number' => $orderData['order_number'] ?? $orderData['name'] ?? null,
                ]
            ];

            \Log::info('Creating new contact', $contactData);
            try {
                $contact = $this->contactRepository->storeContact($contactData, $vendorId);
                
                if (!$contact) {
                    \Log::error('Failed to create contact', $contactData);
                    throw new Exception('Failed to create contact');
                }
            } catch (\Exception $e) {
                \Log::error('Exception while creating contact', [
                    'error' => $e->getMessage(),
                    'contact_data' => $contactData
                ]);
                throw $e;
            }
        }

        return $contact;
    }

    /**
     * Create a minimal contact record for orders without customer data
     */
    protected function createMinimalContact(array $orderData, int $vendorId)
    {
        $orderNumber = $orderData['order_number'] ?? $orderData['name'] ?? 'Unknown';
        $orderId = $orderData['id'] ?? 'unknown';
        
        $contactData = [
            'vendors__id' => $vendorId,
            'first_name' => "Order #{$orderNumber}",
            'email' => "order-{$orderId}@placeholder.com",
            'phone_number' => '0000000000',
            'wa_id' => '0000000000',
            'status' => 'active',
            '__data' => [
                'shopify_order_id' => $orderId,
                'order_number' => $orderNumber,
                'is_placeholder' => true,
                'missing_customer_data' => true
            ]
        ];

        \Log::info('Creating minimal contact for order without customer data', $contactData);
        
        try {
            $contact = $this->contactRepository->storeContact($contactData, $vendorId);
            
            if (!$contact) {
                \Log::error('Failed to create minimal contact', $contactData);
                throw new Exception('Failed to create minimal contact');
            }
            
            return $contact;
        } catch (\Exception $e) {
            \Log::error('Exception while creating minimal contact', [
                'error' => $e->getMessage(),
                'contact_data' => $contactData
            ]);
            throw $e;
        }
    }

    /**
     * Fetch customer data from Shopify API
     */
    public function fetchCustomerFromShopify(int $customerId, int $vendorId)
    {
        try {
            $integration = $this->shopifyIntegrationRepository->getActiveByVendorId($vendorId);
            if (!$integration) {
                \Log::error('No active Shopify integration found for vendor', ['vendor_id' => $vendorId]);
                return null;
            }

            $shopDomain = $integration->shop_domain;
            $accessToken = $integration->access_token;

            \Log::info('Fetching customer data from Shopify API', [
                'customer_id' => $customerId,
                'shop_domain' => $shopDomain
            ]);

            $response = Http::timeout(30)->withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("https://{$shopDomain}/admin/api/2023-10/customers/{$customerId}.json");

            if (!$response->successful()) {
                \Log::error('Failed to fetch customer from Shopify API', [
                    'customer_id' => $customerId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                // Don't return null here, continue with existing data
                return null;
            }

            $customerData = $response->json()['customer'] ?? null;
            
            if ($customerData && is_array($customerData)) {
                // Clean and validate the customer data
                $cleanedData = $this->cleanCustomerData($customerData);
                
                \Log::info('Successfully fetched and cleaned customer data from Shopify API', [
                    'customer_id' => $customerId,
                    'customer_state' => $customerData['state'] ?? 'unknown',
                    'has_phone' => !empty($cleanedData['phone']),
                    'has_email' => !empty($cleanedData['email']),
                    'has_name' => !empty($cleanedData['first_name']),
                    'phone' => $cleanedData['phone'] ?? 'NOT_FOUND',
                    'email' => $cleanedData['email'] ?? 'NOT_FOUND',
                    'first_name' => $cleanedData['first_name'] ?? 'NOT_FOUND',
                    'last_name' => $cleanedData['last_name'] ?? 'NOT_FOUND',
                    'default_address' => isset($cleanedData['default_address']) ? 'EXISTS' : 'NOT_FOUND',
                    'addresses_count' => isset($cleanedData['addresses']) ? count($cleanedData['addresses']) : 0
                ]);
                
                // If customer is disabled, they might not have email/phone
                if (($customerData['state'] ?? '') === 'disabled') {
                    \Log::info('Customer is disabled, email and phone may not be available', [
                        'customer_id' => $customerId,
                        'state' => $customerData['state']
                    ]);
                }
                
                return $cleanedData;
            } else {
                \Log::warning('Customer data from Shopify API is invalid or empty', [
                    'customer_id' => $customerId,
                    'customer_data' => $customerData
                ]);
                return null;
            }

        } catch (\Exception $e) {
            \Log::error('Exception while fetching customer from Shopify API', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Clean and validate customer data from Shopify API
     */
    protected function cleanCustomerData(array $customerData): array
    {
        $cleaned = [];
        
        // Basic customer information
        $cleaned['id'] = $customerData['id'] ?? null;
        $cleaned['email'] = trim($customerData['email'] ?? '');
        $cleaned['phone'] = trim($customerData['phone'] ?? '');
        $cleaned['first_name'] = trim($customerData['first_name'] ?? '');
        $cleaned['last_name'] = trim($customerData['last_name'] ?? '');
        $cleaned['note'] = trim($customerData['note'] ?? '');
        $cleaned['tags'] = trim($customerData['tags'] ?? '');
        
        // Clean phone number - remove any non-numeric characters except + and -
        if (!empty($cleaned['phone'])) {
            $cleaned['phone'] = preg_replace('/[^0-9+\-()\s]/', '', $cleaned['phone']);
            $cleaned['phone'] = trim($cleaned['phone']);
        }
        
        // Clean email - basic validation
        if (!empty($cleaned['email']) && !filter_var($cleaned['email'], FILTER_VALIDATE_EMAIL)) {
            \Log::warning('Invalid email format from Shopify customer data', [
                'email' => $cleaned['email'],
                'customer_id' => $cleaned['id']
            ]);
            $cleaned['email'] = ''; // Clear invalid email
        }
        
        // Default address
        if (isset($customerData['default_address']) && is_array($customerData['default_address'])) {
            $defaultAddress = $customerData['default_address'];
            $cleaned['default_address'] = [
                'first_name' => trim($defaultAddress['first_name'] ?? ''),
                'last_name' => trim($defaultAddress['last_name'] ?? ''),
                'company' => trim($defaultAddress['company'] ?? ''),
                'address1' => trim($defaultAddress['address1'] ?? ''),
                'address2' => trim($defaultAddress['address2'] ?? ''),
                'city' => trim($defaultAddress['city'] ?? ''),
                'province' => trim($defaultAddress['province'] ?? ''),
                'country' => trim($defaultAddress['country'] ?? ''),
                'zip' => trim($defaultAddress['zip'] ?? ''),
                'phone' => trim($defaultAddress['phone'] ?? ''),
            ];
            
            // Clean phone from default address
            if (!empty($cleaned['default_address']['phone'])) {
                $cleaned['default_address']['phone'] = preg_replace('/[^0-9+\-()\s]/', '', $cleaned['default_address']['phone']);
                $cleaned['default_address']['phone'] = trim($cleaned['default_address']['phone']);
            }
        }
        
        // All addresses
        if (isset($customerData['addresses']) && is_array($customerData['addresses'])) {
            $cleaned['addresses'] = [];
            foreach ($customerData['addresses'] as $address) {
                if (is_array($address)) {
                    $cleanedAddress = [
                        'first_name' => trim($address['first_name'] ?? ''),
                        'last_name' => trim($address['last_name'] ?? ''),
                        'company' => trim($address['company'] ?? ''),
                        'address1' => trim($address['address1'] ?? ''),
                        'address2' => trim($address['address2'] ?? ''),
                        'city' => trim($address['city'] ?? ''),
                        'province' => trim($address['province'] ?? ''),
                        'country' => trim($address['country'] ?? ''),
                        'zip' => trim($address['zip'] ?? ''),
                        'phone' => trim($address['phone'] ?? ''),
                    ];
                    
                    // Clean phone from address
                    if (!empty($cleanedAddress['phone'])) {
                        $cleanedAddress['phone'] = preg_replace('/[^0-9+\-()\s]/', '', $cleanedAddress['phone']);
                        $cleanedAddress['phone'] = trim($cleanedAddress['phone']);
                    }
                    
                    $cleaned['addresses'][] = $cleanedAddress;
                }
            }
        }
        
        return $cleaned;
    }

    /**
     * Validate Shopify credentials
     */
    protected function validateShopifyCredentials(string $shopDomain, string $accessToken): array
    {
        \Log::info('Making Shopify API request', [
            'shop_domain' => $shopDomain,
            'url' => "https://{$shopDomain}/admin/api/2023-10/shop.json"
        ]);

        $response = Http::timeout(30)->withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get("https://{$shopDomain}/admin/api/2023-10/shop.json");

        \Log::info('Shopify API response', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->body()
        ]);

        if (!$response->successful()) {
            throw new Exception('Invalid Shopify credentials: ' . $response->body());
        }

        return $response->json()['shop'] ?? [];
    }

    /**
     * Setup webhooks
     */
    protected function setupWebhooks(ShopifyIntegrationModel $integration)
    {
        \Log::info('Setting up Shopify webhooks', [
            'integration_id' => $integration->_id,
            'webhook_url' => $integration->webhook_url
        ]);

        $webhookTopics = [
            'orders/create',
            'orders/updated',
            'orders/paid',
            'orders/fulfilled',
            'orders/cancelled',
            'refunds/create',
        ];

        foreach ($webhookTopics as $topic) {
            try {
                $this->createWebhook($integration, $topic);
            } catch (\Exception $e) {
                \Log::error('Failed to create webhook for topic', [
                    'topic' => $topic,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the entire connection if webhook creation fails
            }
        }
    }

    /**
     * Create webhook
     */
    protected function createWebhook(ShopifyIntegrationModel $integration, string $topic)
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'X-Shopify-Access-Token' => $integration->access_token,
                'Content-Type' => 'application/json',
            ])->post("https://{$integration->shop_domain}/admin/api/2023-10/webhooks.json", [
                'webhook' => [
                    'topic' => $topic,
                    'address' => $integration->webhook_url,
                    'format' => 'json',
                ]
            ]);

            if ($response->successful()) {
                $webhookData = $response->json()['webhook'];
                Log::info('Shopify webhook created', [
                    'topic' => $topic,
                    'webhook_id' => $webhookData['id'],
                ]);
            } else {
                Log::error('Failed to create Shopify webhook', [
                    'topic' => $topic,
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                // Don't throw exception, just log the error
            }
        } catch (Exception $e) {
            Log::error('Exception creating Shopify webhook', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove webhooks
     */
    protected function removeWebhooks(ShopifyIntegrationModel $integration)
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'X-Shopify-Access-Token' => $integration->access_token,
            ])->get("https://{$integration->shop_domain}/admin/api/2023-10/webhooks.json");

            if ($response->successful()) {
                $webhooks = $response->json()['webhooks'] ?? [];
                
                foreach ($webhooks as $webhook) {
                    if (str_contains($webhook['address'], $integration->webhook_url)) {
                        $this->deleteWebhook($integration, $webhook['id']);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Exception removing Shopify webhooks', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete webhook
     */
    protected function deleteWebhook(ShopifyIntegrationModel $integration, int $webhookId)
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'X-Shopify-Access-Token' => $integration->access_token,
            ])->delete("https://{$integration->shop_domain}/admin/api/2023-10/webhooks/{$webhookId}.json");

            if ($response->successful()) {
                Log::info('Shopify webhook deleted', ['webhook_id' => $webhookId]);
            } else {
                Log::error('Failed to delete Shopify webhook', [
                    'webhook_id' => $webhookId,
                    'response' => $response->body(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Exception deleting Shopify webhook', [
                'webhook_id' => $webhookId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify webhook signature
     */
    protected function verifyWebhookSignature($request): bool
    {
        // For now, we'll skip signature verification
        // In production, you should implement proper signature verification
        return true;
    }
} 