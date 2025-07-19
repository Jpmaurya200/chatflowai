<?php
/**
 * WhatsAppOrderController.php - Controller file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppOrderRepository;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppPaymentRepository;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WhatsAppOrderController extends BaseController
{
    /**
     * @var WhatsAppOrderRepository
     */
    protected $whatsAppOrderRepository;

    /**
     * @var WhatsAppPaymentRepository
     */
    protected $whatsAppPaymentRepository;

    /**
     * @var WhatsAppPaymentService
     */
    protected $whatsAppPaymentService;

    /**
     * Constructor
     */
    public function __construct(
        WhatsAppOrderRepository $whatsAppOrderRepository,
        WhatsAppPaymentRepository $whatsAppPaymentRepository,
        WhatsAppPaymentService $whatsAppPaymentService
    ) {
        $this->whatsAppOrderRepository = $whatsAppOrderRepository;
        $this->whatsAppPaymentRepository = $whatsAppPaymentRepository;
        $this->whatsAppPaymentService = $whatsAppPaymentService;
    }

    /**
     * Show orders list page
     *
     * @return \Illuminate\View\View
     */
    public function showOrdersList()
    {
        validateVendorAccess('administrative');
        
        // Check if WhatsApp is configured
        if (!isWhatsAppBusinessAccountReady()) {
            return redirect()->route('vendor.settings.read', ['pageType' => 'whatsapp_cloud_api_setup'])
                ->with('error', __tr('Please complete your WhatsApp Cloud API Setup first'));
        }

        $orderStatistics = $this->whatsAppOrderRepository->getOrderStatistics();
        
        return $this->loadView('whatsapp-service.orders-list', [
            'orderStatistics' => $orderStatistics,
            'pageTitle' => __tr('WhatsApp Orders')
        ]);
    }

    /**
     * Fetch orders with pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchOrders(Request $request): JsonResponse
    {
        validateVendorAccess('administrative');
        
        $filters = [
            'status' => $request->get('status'),
            'customer_phone' => $request->get('customer_phone'),
            'order_id' => $request->get('order_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'per_page' => $request->get('per_page', 20),
        ];

        $orders = $this->whatsAppOrderRepository->fetchOrdersWithPagination($filters);

        return $this->processResponse(1, [], [
            'orders' => $orders,
            'filters' => $filters,
        ], true);
    }

    /**
     * Show order details
     *
     * @param string $orderUid
     * @return \Illuminate\View\View
     */
    public function showOrderDetails(string $orderUid)
    {
        validateVendorAccess('administrative');
        
        $order = $this->whatsAppOrderRepository->fetchIt($orderUid);
        
        if (!$order) {
            abort(404, __tr('Order not found'));
        }

        $payments = $this->whatsAppPaymentRepository->fetchByOrderId($order->order_id);
        
        return $this->loadView('whatsapp-service.order-details', [
            'order' => $order,
            'payments' => $payments,
            'pageTitle' => __tr('Order Details - :orderID', ['orderID' => $order->order_id])
        ]);
    }

    /**
     * Update order status
     *
     * @param BaseRequest $request
     * @param string $orderUid
     * @return JsonResponse
     */
    public function updateOrderStatus(BaseRequest $request, string $orderUid): JsonResponse
    {
        validateVendorAccess('administrative');
        
        $request->validate([
            'status' => 'required|in:pending,awaiting_address,awaiting_payment,paid,confirmed,shipped,delivered,cancelled,refunded'
        ]);

        $order = $this->whatsAppOrderRepository->fetchIt($orderUid);
        
        if (!$order) {
            return $this->processResponse(22, [
                22 => __tr('Order not found')
            ], [], true);
        }

        $updated = $this->whatsAppOrderRepository->updateOrderStatus(
            $order->order_id,
            $request->status,
            [],
            $order->vendors__id
        );

        if ($updated) {
            Log::info('Order status updated', [
                'order_id' => $order->order_id,
                'old_status' => $order->status,
                'new_status' => $request->status,
                'updated_by' => getUserID(),
            ]);

            return $this->processResponse(1, [
                1 => __tr('Order status updated successfully')
            ], [], true);
        }

        return $this->processResponse(22, [
            22 => __tr('Failed to update order status')
        ], [], true);
    }

    /**
     * Handle Razorpay webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleRazorpayWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('Razorpay webhook received', [
                'headers' => $request->headers->all(),
                'payload_keys' => array_keys($request->all()),
                'event' => $request->input('event'),
            ]);

            $payload = $request->all();
            
            // Extract vendor ID from the webhook payload if possible
            $vendorId = null;
            if (isset($payload['payload']['payment_link']['entity']['reference_id'])) {
                $orderId = $payload['payload']['payment_link']['entity']['reference_id'];
                $order = $this->whatsAppOrderRepository->fetchByOrderId($orderId);
                if ($order) {
                    $vendorId = $order->vendors__id;
                }
            }
            
            $result = $this->whatsAppPaymentService->processPaymentWebhook($payload, 'razorpay', $vendorId);
            
            if ($result['success']) {
                Log::info('Razorpay webhook processed successfully', [
                    'event' => $request->input('event'),
                    'vendor_id' => $vendorId,
                    'result' => $result,
                ]);
                return response()->json(['status' => 'success']);
            }
            
            Log::warning('Razorpay webhook processing failed', [
                'error' => $result['message'],
                'event' => $request->input('event'),
            ]);
            
            return response()->json(['error' => $result['message']], 400);
            
        } catch (\Exception $e) {
            Log::error('Razorpay webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Payment success page
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function paymentSuccess(Request $request)
    {
        try {
            $orderId = $request->get('razorpay_payment_link_reference_id');
            $paymentId = $request->get('razorpay_payment_id');
            $paymentLinkId = $request->get('razorpay_payment_link_id');
            $paymentStatus = $request->get('razorpay_payment_link_status');
            $signature = $request->get('razorpay_signature');
            
            $order = null;
            $payment = null;
            
            Log::info('Payment success page accessed', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'payment_link_id' => $paymentLinkId,
                'payment_status' => $paymentStatus,
                'has_signature' => !empty($signature),
                'all_params' => $request->all(),
            ]);
            
            if ($orderId) {
                // Try to find order across all vendors for this success page
                $order = $this->whatsAppOrderRepository->fetchByOrderId($orderId);
                
                if ($order) {
                    Log::info('Order found for payment success', [
                        'order_id' => $orderId,
                        'current_status' => $order->status,
                        'payment_status' => $order->payment_status,
                    ]);
                    
                    if ($paymentStatus === 'paid') {
                        // Update order status to paid
                        $updateData = [
                            'payment_id' => $paymentId,
                            'payment_status' => 'completed',
                            'payment_completed_at' => now(),
                        ];
                        
                        $updated = $this->whatsAppOrderRepository->updateOrderStatus(
                            $orderId,
                            'paid',
                            $updateData,
                            $order->vendors__id
                        );
                        
                        Log::info('Order status update attempt', [
                            'order_id' => $orderId,
                            'success' => $updated,
                            'update_data' => $updateData,
                        ]);
                        
                        // Refresh order data
                        $order = $this->whatsAppOrderRepository->fetchByOrderId($orderId);
                        
                        // Update payment record if exists
                        if ($paymentId) {
                            $payment = $this->whatsAppPaymentRepository->fetchByPaymentId($paymentId);
                            
                            if ($payment) {
                                $this->whatsAppPaymentRepository->updatePaymentStatus(
                                    $paymentId,
                                    'completed',
                                    [
                                        'payment_link_id' => $paymentLinkId,
                                        'transaction_id' => $paymentId,
                                        'payment_completed_at' => now(),
                                    ],
                                    $order->vendors__id
                                );
                            } else {
                                // Create payment record if it doesn't exist
                                $payment = $this->whatsAppPaymentRepository->createPayment([
                                    'payment_id' => $paymentId,
                                    'vendors__id' => $order->vendors__id,
                                    'order_id' => $orderId,
                                    'amount' => $order->total_amount,
                                    'currency' => $order->currency,
                                    'status' => 'completed',
                                    'payment_link_id' => $paymentLinkId,
                                    'transaction_id' => $paymentId,
                                    'payment_completed_at' => now(),
                                    'gateway' => 'razorpay',
                                ]);
                            }
                        }
                        
                        // Send confirmation message
                        try {
                            $this->whatsAppPaymentService->sendPaymentConfirmationMessage(
                                $order->customer_phone,
                                $order,
                                $order->vendors__id
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send payment confirmation message', [
                                'error' => $e->getMessage(),
                                'order_id' => $orderId,
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                } else {
                    Log::warning('Order not found for payment success', [
                        'order_id' => $orderId,
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing payment success', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $request->all(),
            ]);
        }
        
        return $this->loadView('whatsapp-service.payment-success', [
            'order' => $order,
            'payment' => $payment,
            'orderId' => $orderId,
            'paymentId' => $paymentId,
            'pageTitle' => __tr('Payment Successful')
        ]);
    }

    /**
     * Get order statistics
     *
     * @return JsonResponse
     */
    public function getOrderStatistics(): JsonResponse
    {
        validateVendorAccess('administrative');
        
        $statistics = $this->whatsAppOrderRepository->getOrderStatistics();
        $paymentStatistics = $this->whatsAppPaymentRepository->getPaymentStatistics();
        
        return $this->processResponse(1, [], [
            'order_statistics' => $statistics,
            'payment_statistics' => $paymentStatistics,
        ], true);
    }

    /**
     * Export orders
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportOrders(Request $request)
    {
        validateVendorAccess('administrative');
        
        $filters = [
            'status' => $request->get('status'),
            'customer_phone' => $request->get('customer_phone'),
            'order_id' => $request->get('order_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        // Get all orders matching filters (without pagination)
        $orders = $this->whatsAppOrderRepository->fetchOrdersWithPagination(
            array_merge($filters, ['per_page' => 10000])
        );

        $filename = 'whatsapp_orders_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'Order ID',
                'Customer Phone',
                'Customer Name',
                'Status',
                'Total Amount',
                'Currency',
                'Items Count',
                'Delivery Address',
                'Payment Status',
                'Ordered At',
                'Payment Completed At',
            ]);

            // CSV data
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_id,
                    $order->customer_phone,
                    $order->customer_name,
                    $order->status,
                    $order->total_amount,
                    $order->currency,
                    count($order->items),
                    $order->delivery_address,
                    $order->payment_status,
                    $order->ordered_at?->format('Y-m-d H:i:s'),
                    $order->payment_completed_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Test payment gateway configuration (for debugging)
     *
     * @return JsonResponse
     */
    public function testPaymentGateway(): JsonResponse
    {
        try {
            $vendorId = getVendorId();
            $paymentService = app(\App\Yantrana\Components\WhatsAppService\Services\WhatsAppPaymentService::class);

            // Use reflection to access the protected method
            $reflection = new \ReflectionClass($paymentService);
            $method = $reflection->getMethod('getPaymentGatewaySettings');
            $method->setAccessible(true);
            $settings = $method->invoke($paymentService, $vendorId);

            return response()->json([
                'success' => true,
                'vendor_id' => $vendorId,
                'settings' => [
                    'gateway' => $settings['gateway'],
                    'enabled' => $settings['enabled'],
                    'has_key_id' => !empty($settings['key_id']),
                    'has_key_secret' => !empty($settings['key_secret']),
                    'has_webhook_secret' => !empty($settings['webhook_secret']),
                ],
                'message' => 'Payment gateway configuration retrieved',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to test payment gateway',
            ], 500);
        }
    }
}
