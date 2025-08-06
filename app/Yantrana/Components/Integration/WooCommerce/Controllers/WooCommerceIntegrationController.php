<?php
/**
 * WooCommerceIntegrationController.php - Controller file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Controllers;

use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Integration\IntegrationEngine;
use App\Yantrana\Components\Integration\WooCommerce\Repositories\WooCommerceIntegrationRepository;
use App\Yantrana\Components\Integration\WooCommerce\Repositories\WooCommerceOrderRepository;
use App\Yantrana\Components\Integration\WooCommerce\Repositories\WooCommerceOrderNotificationRepository;

class WooCommerceIntegrationController extends BaseController
{
    /**
     * @var IntegrationEngine - Integration Engine
     */
    protected $integrationEngine;

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
     * Constructor
     */
    public function __construct(
        IntegrationEngine $integrationEngine,
        WooCommerceIntegrationRepository $wooCommerceIntegrationRepository,
        WooCommerceOrderRepository $wooCommerceOrderRepository,
        WooCommerceOrderNotificationRepository $wooCommerceOrderNotificationRepository
    ) {
        $this->integrationEngine = $integrationEngine;
        $this->wooCommerceIntegrationRepository = $wooCommerceIntegrationRepository;
        $this->wooCommerceOrderRepository = $wooCommerceOrderRepository;
        $this->wooCommerceOrderNotificationRepository = $wooCommerceOrderNotificationRepository;
    }

    /**
     * Show integration dashboard
     */
    public function dashboard(Request $request)
    {
        $vendorId = getVendorId();
        
        $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);
        $orderStatistics = $this->wooCommerceOrderRepository->getOrderStatistics($vendorId);
        $notificationStatistics = $this->wooCommerceOrderNotificationRepository->getNotificationStatistics($vendorId);
        $recentOrders = $this->wooCommerceOrderRepository->getByVendorId($vendorId, 10);
        $recentNotifications = $this->wooCommerceOrderNotificationRepository->getByVendorId($vendorId, 10);

        return view('integration.woocommerce.dashboard', compact(
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
        $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);
        $availableIntegrations = $this->integrationEngine->getAvailableIntegrations();
        
        // Get available WhatsApp templates
        $templates = app(\App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository::class)
            ->fetchItAll([
                'vendors__id' => $vendorId,
                'status' => 'APPROVED'
            ]);

        // Process templates to include components data
        $templates->each(function($template) {
            $templateData = $template->toArray();
            $components = \Illuminate\Support\Arr::get($templateData, '__data.template.components', []);
            
            // If no components found, try to extract from template text
            if (empty($components)) {
                $templateText = \Illuminate\Support\Arr::get($templateData, '__data.template.text', '');
                if ($templateText) {
                    $components = [
                        [
                            'type' => 'body',
                            'text' => $templateText
                        ]
                    ];
                }
            }
            
            $template->components_data = $components;
        });

        // Get available WooCommerce variables
        $wooCommerceVariables = [
            'customer_name' => 'Customer Name',
            'order_number' => 'Order Number',
            'order_total' => 'Order Total',
            'order_status' => 'Order Status',
            'order_date' => 'Order Date',
            'payment_method' => 'Payment Method',
            'shipping_address' => 'Shipping Address',
            'billing_address' => 'Billing Address',
            'tracking_number' => 'Tracking Number',
            'tracking_company' => 'Tracking Company',
            'tracking_url' => 'Tracking URL',
            'delivery_date' => 'Delivery Date',
            'cod_amount' => 'COD Amount'
        ];

        // Get saved variable mappings for all notification types
        $notificationTypes = [
            'order_confirmation' => 'Order Confirmation',
            'payment_confirmation' => 'Payment Confirmation',
            'shipment_tracking' => 'Shipment Tracking',
            'delivery_confirmation' => 'Delivery Confirmation',
            'cod_verification' => 'COD Verification'
        ];

        // Get existing variable mappings from integration
        $existingVariableMappings = [];
        if ($integration) {
            foreach ($notificationTypes as $type => $label) {
                $existingVariableMappings[$type] = $integration->getVariableMappings($type);
            }
        }

        // Debug: Log template data for troubleshooting
        \Log::info('Template data for WooCommerce settings', [
            'templates_count' => $templates->count(),
            'sample_template' => $templates->first() ? [
                'template_name' => $templates->first()->template_name,
                'components_data' => $templates->first()->components_data,
                'has_components' => !empty($templates->first()->components_data),
                'template_data_keys' => $templates->first()->__data ? array_keys($templates->first()->__data) : []
            ] : null
        ]);

        return view('integration.woocommerce.settings', compact(
            'integration',
            'availableIntegrations',
            'templates',
            'wooCommerceVariables',
            'notificationTypes',
            'existingVariableMappings'
        ));
    }

    /**
     * Connect WooCommerce integration
     */
    public function connect(Request $request)
    {
        try {
            $request->validate([
                'site_url' => 'required|url',
                'consumer_key' => 'required|string',
                'consumer_secret' => 'required|string'
            ]);

            $vendorId = getVendorId();
            
            $result = $this->integrationEngine->connectIntegration('woocommerce', [
                'site_url' => $request->site_url,
                'consumer_key' => $request->consumer_key,
                'consumer_secret' => $request->consumer_secret
            ], $vendorId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'WooCommerce integration connected successfully',
                    'data' => $result['data'] ?? []
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to connect WooCommerce integration'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error connecting WooCommerce integration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect WooCommerce integration
     */
    public function disconnect(Request $request)
    {
        try {
            $vendorId = getVendorId();
            
            $result = $this->integrationEngine->disconnectIntegration('woocommerce', $vendorId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'WooCommerce integration disconnected successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to disconnect WooCommerce integration'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error disconnecting WooCommerce integration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        try {
            $vendorId = getVendorId();
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'WooCommerce integration not found'
                ], 404);
            }

            // Get notification types from request
            $notificationTypes = $request->get('notification_types', []);
            
            // Get template mappings from request
            $templateMappings = [];
            $variableMappings = [];
            
            \Log::info('Updating WooCommerce notification settings', [
                'vendor_id' => $vendorId,
                'notification_types' => $notificationTypes,
                'all_request_data' => $request->all()
            ]);
            
            foreach ($notificationTypes as $notificationType) {
                // Get template UID from nested array structure
                $templateUid = $request->input("template_uid.{$notificationType}");
                
                \Log::info('Processing notification type', [
                    'notification_type' => $notificationType,
                    'template_uid' => $templateUid,
                    'template_uid_empty' => empty($templateUid),
                    'template_uid_null' => is_null($templateUid),
                    'all_template_uids' => $request->input('template_uid')
                ]);
                
                if (!empty($templateUid)) {
                    $templateMappings[$notificationType] = $templateUid;
                    
                    // Get variable mappings for this notification type
                    $variableMappingData = $request->input("variable_mapping.{$notificationType}", []);
                    
                    \Log::info('Processing variable mappings', [
                        'notification_type' => $notificationType,
                        'template_uid' => $templateUid,
                        'variable_mapping_data' => $variableMappingData,
                        'variable_mapping_empty' => empty($variableMappingData),
                        'all_variable_mappings' => $request->input('variable_mapping')
                    ]);
                    
                    if (!empty($variableMappingData)) {
                        $variableMappings[$notificationType] = $variableMappingData;
                    }
                }
            }

            \Log::info('Final mappings to save', [
                'template_mappings' => $templateMappings,
                'variable_mappings' => $variableMappings
            ]);

            // Update integration with new settings
            $integration->setNotificationTypes($notificationTypes);
            $integration->setTemplateMappings($templateMappings);
            
            // Update variable mappings for each notification type
            foreach ($variableMappings as $notificationType => $mappings) {
                $integration->setVariableMappings($notificationType, $mappings);
            }
            
            $integration->save();

            \Log::info('Settings saved successfully', [
                'integration_id' => $integration->_id,
                'settings' => $integration->settings
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating WooCommerce notification settings', [
                'vendor_id' => getVendorId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating notification settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get integration status
     */
    public function getStatus(Request $request)
    {
        try {
            $vendorId = getVendorId();
            $status = $this->integrationEngine->getIntegrationStatus('woocommerce', $vendorId);

            return response()->json($status);

        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show orders list
     */
    public function orders(Request $request)
    {
        $vendorId = getVendorId();
        
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        
        $orders = $this->wooCommerceOrderRepository->getByVendorId($vendorId, 50);
        
        if ($search) {
            $orders = $this->wooCommerceOrderRepository->searchOrders($vendorId, $search, 50);
        }
        
        if ($status) {
            $orders = $this->wooCommerceOrderRepository->getByStatus($vendorId, $status, 50);
        }
        
        if ($dateFrom && $dateTo) {
            $orders = $this->wooCommerceOrderRepository->getByDateRange($vendorId, $dateFrom, $dateTo, 50);
        }

        return view('integration.woocommerce.orders', compact('orders', 'search', 'status', 'dateFrom', 'dateTo'));
    }

    /**
     * Show order details
     */
    public function orderDetails(Request $request, $orderId)
    {
        $vendorId = getVendorId();
        $order = $this->wooCommerceOrderRepository->getByWooCommerceId($orderId, $vendorId);
        
        if (!$order) {
            abort(404, 'Order not found');
        }

        $notifications = $this->wooCommerceOrderNotificationRepository->getByOrderId($order->_id, 20);

        return view('integration.woocommerce.order-details', compact('order', 'notifications'));
    }

    /**
     * Show notifications list
     */
    public function notifications(Request $request)
    {
        $vendorId = getVendorId();
        
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $type = $request->get('type', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        
        $notifications = $this->wooCommerceOrderNotificationRepository->getByVendorId($vendorId, 50);
        
        if ($status) {
            $notifications = $this->wooCommerceOrderNotificationRepository->getByStatus($vendorId, $status, 50);
        }
        
        if ($type) {
            $notifications = $this->wooCommerceOrderNotificationRepository->getByType($vendorId, $type, 50);
        }
        
        if ($dateFrom && $dateTo) {
            $notifications = $this->wooCommerceOrderNotificationRepository->getByDateRange($vendorId, $dateFrom, $dateTo, 50);
        }

        return view('integration.woocommerce.notifications', compact('notifications', 'search', 'status', 'type', 'dateFrom', 'dateTo'));
    }

    /**
     * Resend notification
     */
    public function resendNotification(Request $request, $notificationId)
    {
        try {
            $vendorId = getVendorId();
            $notification = $this->wooCommerceOrderNotificationRepository->find($notificationId);
            
            if (!$notification || $notification->vendor_id != $vendorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            if (!$notification->canRetry()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification cannot be retried'
                ], 400);
            }

            // Increment retry count
            $this->wooCommerceOrderNotificationRepository->incrementRetryCount($notificationId);

            // Resend notification using the integration engine
            $result = $this->integrationEngine->sendOrderNotification(
                'woocommerce',
                $notification->order->order_data,
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
                    'message' => $result['message'] ?? 'Failed to resend notification'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $vendorId = getVendorId();
            
            $orderStatistics = $this->wooCommerceOrderRepository->getOrderStatistics($vendorId);
            $notificationStatistics = $this->wooCommerceOrderNotificationRepository->getNotificationStatistics($vendorId);
            $successRate = $this->wooCommerceOrderNotificationRepository->getSuccessRate($vendorId);

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orderStatistics,
                    'notifications' => $notificationStatistics,
                    'success_rate' => $successRate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting statistics: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Test method for debugging
     */
    public function testMethod(Request $request)
    {
        try {
            $vendorId = getVendorId();
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            return response()->json([
                'success' => true,
                'data' => [
                    'vendor_id' => $vendorId,
                    'integration' => $integration ? $integration->toArray() : null,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in test method: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test notification sending
     */
    public function testNotification(Request $request)
    {
        try {
            $request->validate([
                'notification_type' => 'required|string',
                'template_id' => 'required|string',
                'phone' => 'required|string'
            ]);

            $vendorId = getVendorId();
            $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);

            if (!$integration || !$integration->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'WooCommerce integration not found or inactive'
                ], 400);
            }

            // Create test order data
            $testOrderData = [
                'id' => 999999,
                'number' => 'TEST-001',
                'status' => 'processing',
                'total' => '99.99',
                'currency' => 'USD',
                'date_created' => now()->toISOString(),
                'date_modified' => now()->toISOString(),
                'billing' => [
                    'first_name' => 'Test',
                    'last_name' => 'Customer',
                    'email' => 'test@example.com',
                    'phone' => $request->phone
                ],
                'shipping' => [
                    'first_name' => 'Test',
                    'last_name' => 'Customer',
                    'address_1' => '123 Test St',
                    'city' => 'Test City',
                    'state' => 'TS',
                    'postcode' => '12345',
                    'country' => 'US'
                ],
                'payment_method_title' => 'Credit Card',
                'line_items' => [
                    [
                        'name' => 'Test Product',
                        'quantity' => 1,
                        'total' => '99.99'
                    ]
                ]
            ];

            // Send test notification
            $result = $this->integrationEngine->sendOrderNotification(
                'woocommerce',
                $testOrderData,
                $request->notification_type,
                $vendorId
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show test notifications page
     */
    public function testNotifications(Request $request)
    {
        $vendorId = getVendorId();
        $integration = $this->wooCommerceIntegrationRepository->getByVendorId($vendorId);
        
        // Get available WhatsApp templates
        $templates = app(\App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository::class)
            ->fetchItAll([
                'vendors__id' => $vendorId,
                'status' => 'APPROVED'
            ]);

        // Get available notification types
        $notificationTypes = [
            'order_confirmation' => 'Order Confirmation',
            'payment_confirmation' => 'Payment Confirmation',
            'shipment_tracking' => 'Shipment Tracking',
            'delivery_confirmation' => 'Delivery Confirmation',
            'cod_verification' => 'COD Verification'
        ];

        return view('integration.woocommerce.test-notifications', compact(
            'integration',
            'templates',
            'notificationTypes'
        ));
    }
} 