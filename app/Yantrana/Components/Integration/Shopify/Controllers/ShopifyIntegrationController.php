<?php
/**
 * ShopifyIntegrationController.php - Controller file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Controllers;

use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Integration\IntegrationEngine;
use App\Yantrana\Components\Integration\Shopify\Repositories\ShopifyIntegrationRepository;
use App\Yantrana\Components\Integration\Shopify\Repositories\ShopifyOrderRepository;
use App\Yantrana\Components\Integration\Shopify\Repositories\ShopifyOrderNotificationRepository;

class ShopifyIntegrationController extends BaseController
{
    /**
     * @var IntegrationEngine - Integration Engine
     */
    protected $integrationEngine;

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
        IntegrationEngine $integrationEngine,
        ShopifyIntegrationRepository $shopifyIntegrationRepository,
        ShopifyOrderRepository $shopifyOrderRepository,
        ShopifyOrderNotificationRepository $shopifyOrderNotificationRepository
    ) {
        $this->integrationEngine = $integrationEngine;
        $this->shopifyIntegrationRepository = $shopifyIntegrationRepository;
        $this->shopifyOrderRepository = $shopifyOrderRepository;
        $this->shopifyOrderNotificationRepository = $shopifyOrderNotificationRepository;
    }

    /**
     * Show integration dashboard
     */
    public function dashboard(Request $request)
    {
        $vendorId = getVendorId();
        
        $integration = $this->shopifyIntegrationRepository->getByVendorId($vendorId);
        $orderStatistics = $this->shopifyOrderRepository->getOrderStatistics($vendorId);
        $notificationStatistics = $this->shopifyOrderNotificationRepository->getNotificationStatistics($vendorId);
        $recentOrders = $this->shopifyOrderRepository->getByVendorId($vendorId, 10);
        $recentNotifications = $this->shopifyOrderNotificationRepository->getByVendorId($vendorId, 10);

        return view('integration.shopify.dashboard', compact(
            'integration',
            'orderStatistics',
            'notificationStatistics',
            'recentOrders',
            'recentNotifications'
        ));
    }

    /**
     * Show integration settings
     */
    public function settings(Request $request)
    {
        $vendorId = getVendorId();
        $integration = $this->shopifyIntegrationRepository->getByVendorId($vendorId);
        $availableIntegrations = $this->integrationEngine->getAvailableIntegrations();
        
        // Get available WhatsApp templates
        $templates = app(\App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository::class)
            ->fetchItAll([
                'vendors__id' => $vendorId,
                'status' => 'APPROVED'
            ]);

        // Process templates to include components data
        $templates->each(function($template) {
            $template->components_data = \Illuminate\Support\Arr::get($template->toArray(), '__data.template.components', []);
        });

        // Get available Shopify variables
        $shopifyVariables = \App\Yantrana\Components\Integration\Shopify\Models\ShopifyIntegrationModel::getAvailableShopifyVariables();

        // Get saved variable mappings for all notification types
        $savedVariableMappings = [];
        if ($integration) {
            $notificationTypes = ['order_confirmation', 'payment_confirmation', 'shipment_tracking', 'delivery_confirmation', 'cod_verification', 'order_cancelled', 'refund_processed'];
            foreach ($notificationTypes as $type) {
                $savedVariableMappings[$type] = $integration->getVariableMappings($type);
            }
        }

        return view('integration.shopify.settings', compact('integration', 'availableIntegrations', 'templates', 'shopifyVariables', 'savedVariableMappings'));
    }

    /**
     * Connect Shopify integration
     */
    public function connect(Request $request)
    {
        \Log::info('Shopify connect method called', [
            'request_data' => $request->all(),
            'vendor_id' => getVendorId()
        ]);

        try {
            $request->validate([
                'shop_domain' => 'required|string',
                'access_token' => 'required|string',
            ]);

            $vendorId = getVendorId();
            
            \Log::info('Shopify connect attempt', [
                'vendor_id' => $vendorId,
                'shop_domain' => $request->shop_domain,
                'has_token' => !empty($request->access_token)
            ]);
            
            $result = $this->integrationEngine->connectIntegration('shopify', [
                'shop_domain' => $request->shop_domain,
                'access_token' => $request->access_token,
            ], $vendorId);

            \Log::info('Shopify connect result', $result);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shopify integration connected successfully',
                    'data' => $result['data'] ?? []
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Shopify connect error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect Shopify: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect Shopify integration
     */
    public function disconnect(Request $request)
    {
        $vendorId = getVendorId();
        
        $result = $this->integrationEngine->disconnectIntegration('shopify', $vendorId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Shopify integration disconnected successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        $request->validate([
            'notification_types' => 'required|array',
            'notification_types.*' => 'string|in:order_confirmation,payment_confirmation,shipment_tracking,delivery_confirmation,cod_verification,order_cancelled,refund_processed',
            'template_uid' => 'array',
            'template_uid.*' => 'nullable|string',
            'variable_mappings' => 'nullable|string'
        ]);

        $vendorId = getVendorId();
        $integration = $this->shopifyIntegrationRepository->getByVendorId($vendorId);

        if (!$integration) {
            return response()->json([
                'success' => false,
                'message' => 'No Shopify integration found'
            ], 404);
        }

        // Set notification types
        $integration->setNotificationTypes($request->notification_types);
        
        // Save template mappings
        if ($request->has('template_uid')) {
            $templateMappings = [];
            foreach ($request->template_uid as $notificationType => $templateUid) {
                if (!empty($templateUid)) {
                    $templateMappings[$notificationType] = $templateUid;
                }
            }
            $integration->setTemplateMappings($templateMappings);
        }
        
        // Save variable mappings
        if ($request->has('variable_mappings') && !empty($request->variable_mappings)) {
            $variableMappings = json_decode($request->variable_mappings, true);
            
            if (is_array($variableMappings)) {
                foreach ($variableMappings as $notificationType => $variables) {
                    if (is_array($variables) && !empty($variables)) {
                        $integration->setVariableMappings($notificationType, $variables);
                    }
                }
            }
        }
        
        $integration->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully'
        ]);
    }

    /**
     * Get integration status
     */
    public function getStatus(Request $request)
    {
        $vendorId = getVendorId();
        $status = $this->integrationEngine->getIntegrationStatus('shopify', $vendorId);

        return response()->json($status);
    }

    /**
     * Show orders list
     */
    public function orders(Request $request)
    {
        $vendorId = getVendorId();
        
        $status = $request->get('status');
        $financialStatus = $request->get('financial_status');
        $fulfillmentStatus = $request->get('fulfillment_status');
        $search = $request->get('search');
        
        $orders = $this->shopifyOrderRepository->getByVendorId($vendorId, 50);

        // Apply filters
        if ($status) {
            $orders = $orders->where('status', $status);
        }
        if ($financialStatus) {
            $orders = $orders->where('financial_status', $financialStatus);
        }
        if ($fulfillmentStatus) {
            $orders = $orders->where('fulfillment_status', $fulfillmentStatus);
        }

        return view('integration.shopify.orders', compact('orders', 'status', 'financialStatus', 'fulfillmentStatus', 'search'));
    }

    /**
     * Show order details
     */
    public function orderDetails(Request $request, $orderId)
    {
        $vendorId = getVendorId();
        $order = $this->shopifyOrderRepository->getByShopifyOrderId($orderId, $vendorId);

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Force refresh the order to get latest data
        $order->refreshData();

        // Debug: Log the order data
        \Log::info('Order details view data', [
            'order_id' => $order->shopify_order_id,
            'line_items_count' => count($order->getLineItems()),
            'fallback_line_items_count' => count($order->forceDecodeData()['line_items'] ?? []),
            '__data_keys' => array_keys($order->__data ?? []),
            '__data_type' => gettype($order->__data),
            'raw_json' => $order->getRawJsonData(),
            'raw_db_value' => $order->getRawDatabaseData(),
            'raw_db_value_length' => strlen($order->getRawDatabaseData()),
            'force_decoded_keys' => array_keys($order->forceDecodeData()),
            'force_decoded_line_items' => $order->forceDecodeData()['line_items'] ?? [],
        ]);

        $notifications = $this->shopifyOrderNotificationRepository->getByOrderId($order->_id);

        return view('integration.shopify.order-details', compact('order', 'notifications'));
    }

    /**
     * Show notifications list
     */
    public function notifications(Request $request)
    {
        $vendorId = getVendorId();
        
        $status = $request->get('status');
        $notificationType = $request->get('notification_type');
        
        $notifications = $this->shopifyOrderNotificationRepository->getByVendorId($vendorId, 50);

        // Apply filters
        if ($status) {
            $notifications = $notifications->where('status', $status);
        }
        if ($notificationType) {
            $notifications = $notifications->where('notification_type', $notificationType);
        }

        return view('integration.shopify.notifications', compact('notifications', 'status', 'notificationType'));
    }

    /**
     * Resend notification
     */
    public function resendNotification(Request $request, $notificationId)
    {
        $vendorId = getVendorId();
        $notification = $this->shopifyOrderNotificationRepository->find($notificationId);

        if (!$notification || $notification->vendors__id !== $vendorId) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Reset notification status
        $notification->status = 'pending';
        $notification->sent_at = null;
        $notification->delivered_at = null;
        $notification->read_at = null;
        $notification->error_message = null;
        $notification->save();

        // Resend notification
        $result = $this->integrationEngine->sendOrderNotification(
            'shopify',
            $notification->order->__data['shopify_order_data'] ?? [],
            $notification->notification_type,
            $vendorId
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Notification resent successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(Request $request)
    {
        $vendorId = getVendorId();
        
        $orderStatistics = $this->shopifyOrderRepository->getOrderStatistics($vendorId);
        $notificationStatistics = $this->shopifyOrderNotificationRepository->getNotificationStatistics($vendorId);

        return response()->json([
            'orders' => $orderStatistics,
            'notifications' => $notificationStatistics
        ]);
    }

    /**
     * Test webhook
     */
    public function testWebhook(Request $request)
    {
        $vendorId = getVendorId();
        $integration = $this->shopifyIntegrationRepository->getByVendorId($vendorId);

        if (!$integration || !$integration->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'No active Shopify integration found'
            ], 404);
        }

        // Send test notification
        $testOrderData = [
            'id' => 'test_order_' . time(),
            'order_number' => 'TEST-' . time(),
            'name' => 'Test Order',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'currency' => 'USD',
            'financial_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'total_price' => '99.99',
            'status' => 'open',
        ];

        $result = $this->integrationEngine->sendOrderNotification(
            'shopify',
            $testOrderData,
            'order_confirmation',
            $vendorId
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $result['message']
            ], 400);
        }
    }

    /**
     * Test method for debugging
     */
    public function testMethod(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Shopify controller test successful',
            'vendor_id' => getVendorId(),
            'request_data' => $request->all()
        ]);
    }

    public function testNotification(Request $request)
    {
        try {
            $vendorId = getVendorId();
            
            // Validate request
            $request->validate([
                'notification_type' => 'required|string',
                'phone_number' => 'required|string',
                'customer_name' => 'required|string',
                'order_number' => 'required|string',
                'order_total' => 'required|numeric',
                'currency' => 'required|string|size:3',
            ]);

            // Create sample order data
            $orderData = [
                'id' => 'TEST-' . time(),
                'order_number' => $request->order_number,
                'name' => $request->customer_name,
                'email' => 'test@example.com',
                'phone' => $request->phone_number,
                'total_price' => $request->order_total,
                'currency' => $request->currency,
                'financial_status' => 'paid',
                'fulfillment_status' => 'unfulfilled',
                'status' => 'open',
                'tracking_number' => $request->tracking_number,
                'carrier' => $request->carrier,
            ];

            // Send test notification
            $result = $this->integrationEngine->sendOrderNotification(
                'shopify',
                $orderData,
                $request->notification_type,
                $vendorId
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test notification sent successfully',
                    'message_id' => $result['message_id'] ?? null,
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to send test notification'
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Test notification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testNotifications(Request $request)
    {
        return view('integration.shopify.test-notifications');
    }

    /**
     * Test order data
     */
    public function testOrderData(Request $request, $orderId)
    {
        $vendorId = getVendorId();
        $order = $this->shopifyOrderRepository->getByShopifyOrderId($orderId, $vendorId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->shopify_order_id,
            'line_items_count' => count($order->getLineItems()),
            'fallback_line_items_count' => count($order->forceDecodeData()['line_items'] ?? []),
            '__data_keys' => array_keys($order->__data ?? []),
            '__data_type' => gettype($order->__data),
            'raw_json' => $order->getRawJsonData(),
            'raw_db_value' => $order->getRawDatabaseData(),
            'raw_db_value_length' => strlen($order->getRawDatabaseData()),
            'force_decoded_keys' => array_keys($order->forceDecodeData()),
            'force_decoded_line_items' => $order->forceDecodeData()['line_items'] ?? [],
            'line_items' => $order->getLineItems(),
        ]);
    }
} 