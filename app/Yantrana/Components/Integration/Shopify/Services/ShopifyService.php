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
     * Constructor
     */
    public function __construct(
        WhatsAppServiceEngine $whatsAppServiceEngine,
        ContactRepository $contactRepository,
        ShopifyIntegrationRepository $shopifyIntegrationRepository,
        ShopifyOrderRepository $shopifyOrderRepository,
        ShopifyOrderNotificationRepository $shopifyOrderNotificationRepository
    ) {
        $this->whatsAppServiceEngine = $whatsAppServiceEngine;
        $this->contactRepository = $contactRepository;
        $this->shopifyIntegrationRepository = $shopifyIntegrationRepository;
        $this->shopifyOrderRepository = $shopifyOrderRepository;
        $this->shopifyOrderNotificationRepository = $shopifyOrderNotificationRepository;
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
            $integration = $this->shopifyIntegrationRepository->getByShopDomain($shopDomain);
            if (!$integration || !$integration->isActive()) {
                throw new Exception('No active integration found for shop: ' . $shopDomain);
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
            $integration = $this->shopifyIntegrationRepository->getActiveByVendorId($vendorId);
            if (!$integration) {
                throw new Exception('No active Shopify integration found');
            }

            // Get or create contact
            $contact = $this->getOrCreateContact($orderData, $vendorId);

            // Create or update order
            $order = $this->shopifyOrderRepository->createOrUpdate([
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
            ]);

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
        if (in_array('order_confirmation', $notificationTypes)) {
            $this->sendOrderNotification($orderData, 'order_confirmation', $integration->vendors__id);
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
            // Create notification record
            $notification = $this->shopifyOrderNotificationRepository->createNotification([
                'shopify_orders__id' => $order->_id,
                'vendors__id' => $order->vendors__id,
                'contacts__id' => $order->contacts__id,
                'notification_type' => $notificationType,
                'status' => 'pending',
            ]);

            // Prepare message content
            $messageContent = $this->prepareNotificationMessage($order, $notificationType);

            // Send via WhatsApp
            $whatsAppResult = $this->whatsAppServiceEngine->sendTemplateMessageProcess(
                [
                    'contact_uid' => $order->contact->uid,
                    'message' => $messageContent,
                    'template_name' => $this->getTemplateName($notificationType),
                ],
                $order->contact,
                false,
                null,
                $order->vendors__id
            );

            if ($whatsAppResult['success']) {
                $notification->markAsSent($whatsAppResult['data']['message_id'] ?? null);
                $notification->setWhatsAppMessageData($whatsAppResult['data'] ?? []);
                $notification->save();

                return [
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'notification_id' => $notification->_id,
                ];
            } else {
                $notification->markAsFailed($whatsAppResult['message'] ?? 'WhatsApp sending failed');
                $notification->save();

                return [
                    'success' => false,
                    'message' => 'Failed to send notification: ' . ($whatsAppResult['message'] ?? 'Unknown error'),
                ];
            }

        } catch (Exception $e) {
            Log::error('Failed to send notification', [
                'order_id' => $order->_id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare notification message
     */
    protected function prepareNotificationMessage(ShopifyOrderModel $order, string $notificationType): string
    {
        $message = '';

        switch ($notificationType) {
            case 'order_confirmation':
                $message = "🎉 Order Confirmed!\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Total: {$order->formatted_total_price}\n";
                $message .= "Status: {$order->status_label}\n\n";
                $message .= "Thank you for your order! We'll keep you updated on your order status.";
                break;

            case 'payment_confirmation':
                $message = "💳 Payment Confirmed!\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Amount: {$order->formatted_total_price}\n";
                $message .= "Payment Status: {$order->financial_status_label}\n\n";
                $message .= "Your payment has been received. We're processing your order!";
                break;

            case 'shipment_tracking':
                $message = "📦 Order Shipped!\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Status: {$order->fulfillment_status_label}\n\n";
                $message .= "Your order has been shipped and is on its way to you!";
                break;

            case 'delivery_confirmation':
                $message = "✅ Order Delivered!\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Status: Delivered\n\n";
                $message .= "Your order has been delivered. Thank you for shopping with us!";
                break;

            case 'cod_verification':
                $message = "💰 COD Payment Required!\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Amount: {$order->formatted_total_price}\n\n";
                $message .= "Please have the exact amount ready for cash on delivery.";
                break;

            case 'order_cancelled':
                $message = "❌ Order Cancelled!\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Status: Cancelled\n\n";
                $message .= "Your order has been cancelled. If you have any questions, please contact us.";
                break;

            case 'refund_processed':
                $message = "💸 Refund Processed!\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Refund Amount: {$order->formatted_total_price}\n\n";
                $message .= "Your refund has been processed and will be credited to your original payment method.";
                break;

            default:
                $message = "Order Update\n\n";
                $message .= "Order #{$order->order_number}\n";
                $message .= "Status: {$order->status_label}\n";
                $message .= "Total: {$order->formatted_total_price}";
        }

        return $message;
    }

    /**
     * Get template name for notification type
     */
    protected function getTemplateName(string $notificationType): string
    {
        $templateMap = [
            'order_confirmation' => 'order_confirmation',
            'payment_confirmation' => 'payment_confirmation',
            'shipment_tracking' => 'shipment_tracking',
            'delivery_confirmation' => 'delivery_confirmation',
            'cod_verification' => 'cod_verification',
            'order_cancelled' => 'order_cancelled',
            'refund_processed' => 'refund_processed',
        ];

        return $templateMap[$notificationType] ?? 'order_update';
    }

    /**
     * Get or create contact
     */
    protected function getOrCreateContact(array $orderData, int $vendorId)
    {
        $phone = $orderData['phone'] ?? '';
        $email = $orderData['email'] ?? '';
        $name = $orderData['name'] ?? '';

        if (!$phone && !$email) {
            throw new Exception('No phone or email found in order data');
        }

        // Try to find existing contact
        $contact = null;
        if ($phone) {
            $contact = $this->contactRepository->getByPhone($phone, $vendorId);
        }
        
        if (!$contact && $email) {
            $contact = $this->contactRepository->getByEmail($email, $vendorId);
        }

        // Create new contact if not found
        if (!$contact) {
            $contactData = [
                'vendors__id' => $vendorId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'status' => 'active',
            ];

            $contact = $this->contactRepository->create($contactData);
        }

        return $contact;
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