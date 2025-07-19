<?php
/**
 * WhatsAppPaymentService.php - Service file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Services;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppPaymentRepository;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppOrderRepository;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppPaymentService extends BaseEngine
{
    /**
     * @var WhatsAppPaymentRepository
     */
    protected $whatsAppPaymentRepository;

    /**
     * @var WhatsAppOrderRepository
     */
    protected $whatsAppOrderRepository;

    /**
     * @var WhatsAppApiService
     */
    protected $whatsAppApiService;

    /**
     * Constructor
     */
    public function __construct(
        WhatsAppPaymentRepository $whatsAppPaymentRepository,
        WhatsAppOrderRepository $whatsAppOrderRepository,
        WhatsAppApiService $whatsAppApiService
    ) {
        $this->whatsAppPaymentRepository = $whatsAppPaymentRepository;
        $this->whatsAppOrderRepository = $whatsAppOrderRepository;
        $this->whatsAppApiService = $whatsAppApiService;
    }

    /**
     * Create payment link for order
     *
     * @param object $order
     * @param int|null $vendorId
     * @return array
     */
    public function createPaymentLink($order, ?int $vendorId = null): array
    {
        try {
            $vendorId = $vendorId ?: getVendorId();

            // Get payment gateway settings
            $gatewaySettings = $this->getPaymentGatewaySettings($vendorId);

            Log::debug('Creating payment link', [
                'order_id' => $order->order_id,
                'vendor_id' => $vendorId,
                'gateway' => $gatewaySettings['gateway'],
                'enabled' => $gatewaySettings['enabled'],
            ]);

            if (!$gatewaySettings['enabled']) {
                throw new \Exception('Payment gateway not enabled. Please configure payment settings in Orders & Payments section.');
            }

            // Create payment link based on gateway
            switch ($gatewaySettings['gateway']) {
                case 'razorpay':
                    return $this->createRazorpayPaymentLink($order, $gatewaySettings, $vendorId);

                default:
                    throw new \Exception('Unsupported payment gateway: ' . $gatewaySettings['gateway']);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create payment link', [
                'error' => $e->getMessage(),
                'order_id' => $order->order_id,
                'vendor_id' => $vendorId,
                'gateway_settings' => $gatewaySettings ?? null,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create payment link',
            ];
        }
    }

    /**
     * Create Razorpay payment link
     *
     * @param object $order
     * @param array $settings
     * @param int $vendorId
     * @return array
     */
    protected function createRazorpayPaymentLink($order, array $settings, int $vendorId): array
    {
        try {
            $amount = round($order->getFinalAmount() * 100); // Amount in paise
            
            $paymentData = [
                'amount' => $amount,
                'currency' => $order->currency,
                'accept_partial' => false,
                'expire_by' => now()->addDay()->timestamp,
                'reference_id' => $order->order_id,
                'description' => "Order #{$order->order_id}",
                'customer' => [
                    'name' => $order->customer_name ?: 'Customer',
                    'contact' => $order->customer_phone,
                    'email' => '',
                ],
                'notify' => [
                    'sms' => true,
                    'email' => false,
                    'whatsapp' => true,
                ],
                'reminder_enable' => true,
                'callback_url' => route('whatsapp.payment.success'),
                'callback_method' => 'get',
            ];

            // Make API request to Razorpay
            $response = Http::withBasicAuth($settings['key_id'], $settings['key_secret'])
                ->post('https://api.razorpay.com/v1/payment_links', $paymentData);

            if (!$response->successful()) {
                throw new \Exception('Razorpay API error: ' . $response->body());
            }

            $paymentLink = $response->json();

            Log::debug('Razorpay payment link response', [
                'order_id' => $order->order_id,
                'response_keys' => array_keys($paymentLink),
                'has_url' => isset($paymentLink['url']),
                'has_short_url' => isset($paymentLink['short_url']),
            ]);

            // Validate required fields in response
            if (!isset($paymentLink['id'])) {
                throw new \Exception('Payment link ID not found in Razorpay response');
            }

            if (!isset($paymentLink['short_url'])) {
                throw new \Exception('Payment link URL not found in Razorpay response');
            }

            // Store payment record
            $payment = $this->whatsAppPaymentRepository->createPayment([
                'payment_id' => $paymentLink['id'],
                'vendors__id' => $vendorId,
                'order_id' => $order->order_id,
                'amount' => $order->getFinalAmount(),
                'currency' => $order->currency,
                'status' => 'pending',
                'payment_link_id' => $paymentLink['id'],
                'payment_link_url' => $paymentLink['short_url'],
                'gateway' => 'razorpay',
                'gateway_response' => [
                    'request_data' => $paymentData,
                    'response_data' => $paymentLink,
                ],
            ]);

            Log::info('Razorpay payment link created', [
                'order_id' => $order->order_id,
                'payment_id' => $paymentLink['id'],
                'vendor_id' => $vendorId,
            ]);

            return [
                'success' => true,
                'payment_link' => [
                    'id' => $paymentLink['id'],
                    'url' => $paymentLink['url'] ?? $paymentLink['short_url'],
                    'short_url' => $paymentLink['short_url'],
                ],
                'payment' => $payment,
                'message' => 'Payment link created successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create Razorpay payment link', [
                'error' => $e->getMessage(),
                'order_id' => $order->order_id,
                'vendor_id' => $vendorId,
            ]);

            throw $e;
        }
    }

    /**
     * Process payment webhook
     *
     * @param array $webhookData
     * @param string $gateway
     * @param int|null $vendorId
     * @return array
     */
    public function processPaymentWebhook(array $webhookData, string $gateway = 'razorpay', ?int $vendorId = null): array
    {
        try {
            // Get payment gateway settings
            $gatewaySettings = $this->getPaymentGatewaySettings($vendorId);

            // Verify webhook signature for Razorpay
            if ($gateway === 'razorpay' && !empty($gatewaySettings['webhook_secret'])) {
                $signature = request()->header('X-Razorpay-Signature');
                if (!$this->verifyRazorpaySignature($webhookData, $signature, $gatewaySettings['webhook_secret'])) {
                    throw new \Exception('Invalid webhook signature');
                }
            }

            switch ($gateway) {
                case 'razorpay':
                    return $this->processRazorpayWebhook($webhookData, $vendorId);
                
                default:
                    throw new \Exception('Unsupported payment gateway');
            }

        } catch (\Exception $e) {
            Log::error('Failed to process payment webhook', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
                'webhook_data' => $webhookData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to process payment webhook',
            ];
        }
    }

    /**
     * Process Razorpay webhook
     *
     * @param array $webhookData
     * @param int|null $vendorId
     * @return array
     */
    /**
     * Verify Razorpay webhook signature
     *
     * @param array $webhookData
     * @param string|null $signature
     * @param string $webhookSecret
     * @return bool
     */
    protected function verifyRazorpaySignature($webhookData, $signature, $webhookSecret): bool
    {
        if (empty($signature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', json_encode($webhookData, JSON_UNESCAPED_SLASHES), $webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    protected function processRazorpayWebhook(array $webhookData, ?int $vendorId = null): array
    {
        $event = $webhookData['event'] ?? '';
        
        Log::info('Processing Razorpay webhook', [
            'event' => $event,
            'vendor_id' => $vendorId,
            'webhook_data' => array_keys($webhookData),
        ]);
        
        
        if ($event === 'payment_link.paid') {
            return $this->handlePaymentLinkPaid($webhookData, $vendorId);
        }

        if ($event === 'payment.failed') {
            return $this->handlePaymentFailed($webhookData, $vendorId);
        }

        return [
            'success' => true,
            'message' => 'Webhook event not handled: ' . $event,
        ];
    }

    /**
     * Handle payment link paid event
     *
     * @param array $webhookData
     * @param int|null $vendorId
     * @return array
     */
    protected function handlePaymentLinkPaid(array $webhookData, ?int $vendorId = null): array
    {
        $paymentLinkData = $webhookData['payload']['payment_link']['entity'] ?? [];
        $paymentData = $webhookData['payload']['payment']['entity'] ?? [];
        
        $orderId = $paymentLinkData['reference_id'] ?? '';
        $paymentId = $paymentData['id'] ?? '';
        $transactionId = $paymentData['id'] ?? '';

        if (!$orderId || !$paymentId) {
            throw new \Exception('Missing order ID or payment ID in webhook data');
        }

        // Find order
        $order = $this->whatsAppOrderRepository->fetchByOrderId($orderId, $vendorId);
        
        if (!$order) {
            throw new \Exception('Order not found: ' . $orderId);
        }

        // Update order status
        $this->whatsAppOrderRepository->updateOrderStatus(
            $orderId,
            'paid',
            [
                'payment_id' => $paymentId,
                'payment_status' => 'completed',
            ],
            $order->vendors__id
        );

        // Update payment record
        $this->whatsAppPaymentRepository->updatePaymentStatus(
            $paymentId,
            'completed',
            [
                'transaction_id' => $transactionId,
                'payment_method' => $paymentData['method'] ?? null,
                'gateway_response' => [
                    'webhook_data' => $webhookData,
                ],
            ],
            $order->vendors__id
        );

        try {
            // Send payment confirmation message
            $this->sendPaymentConfirmationMessage($order->customer_phone, $order->fresh(), $order->vendors__id);
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation message', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'customer_phone' => $order->customer_phone,
            ]);
            // Don't throw the exception as payment is already confirmed
        }

        Log::info('Payment confirmed via webhook', [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'vendor_id' => $order->vendors__id,
        ]);

        return [
            'success' => true,
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'message' => 'Payment confirmed successfully',
        ];
    }

    /**
     * Handle payment failed event
     *
     * @param array $webhookData
     * @param int|null $vendorId
     * @return array
     */
    protected function handlePaymentFailed(array $webhookData, ?int $vendorId = null): array
    {
        $paymentData = $webhookData['payload']['payment']['entity'] ?? [];
        $paymentId = $paymentData['id'] ?? '';

        if (!$paymentId) {
            throw new \Exception('Missing payment ID in webhook data');
        }

        // Update payment record
        $this->whatsAppPaymentRepository->updatePaymentStatus(
            $paymentId,
            'failed',
            [
                'gateway_response' => [
                    'webhook_data' => $webhookData,
                ],
            ],
            $vendorId
        );

        Log::info('Payment failed via webhook', [
            'payment_id' => $paymentId,
            'vendor_id' => $vendorId,
        ]);

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'message' => 'Payment failure processed',
        ];
    }

    /**
     * Send payment confirmation message
     *
     * @param string $customerPhone
     * @param object $order
     * @param int $vendorId
     * @return void
     */
    public function sendPaymentConfirmationMessage(string $customerPhone, $order, int $vendorId): void
    {
        try {
            Log::info('Attempting to send payment confirmation message', [
                'customer_phone' => $customerPhone,
                'order_id' => $order->order_id,
                'vendor_id' => $vendorId,
            ]);

            // Format items list
            $itemsList = "";
            if ($order->items && is_array($order->items)) {
                foreach ($order->items as $item) {
                    $itemName = $item['name'] ?? 'Product';
                    $quantity = $item['quantity'] ?? 1;
                    $price = ($item['item_price'] ?? 0) * $quantity;
                    
                    $itemsList .= sprintf(
                        "• %s x%d - %s %s\n",
                        $itemName,
                        $quantity,
                        $order->currency,
                        number_format($price, 2)
                    );
                }
            }

            // Format total amount
            $totalAmount = $order->formatted_total_amount ?? ($order->currency . ' ' . number_format($order->total_amount, 2));

            $message = "🎉 *Payment Successful!*\n\n" .
                      "✅ *Order Confirmed*\n" .
                      "Order ID: #{$order->order_id}\n" .
                      "Amount Paid: {$totalAmount}\n\n";

            if ($itemsList) {
                $message .= "*Order Details:*\n" . $itemsList . "\n";
            }

            if ($order->delivery_address) {
                $message .= "*Delivery Address:*\n{$order->delivery_address}\n\n";
            }

            $message .= "🚚 *Delivery Information:*\n" .
                       "• You'll receive tracking details soon\n\n" .
                       
                       "📞 *Support:* Reply to this chat for any queries\n\n" .
                       "Thank you for your order! 🙏";

            Log::info('Sending payment confirmation message', [
                'customer_phone' => $customerPhone,
                'order_id' => $order->order_id,
                'vendor_id' => $vendorId,
                'message_length' => strlen($message),
            ]);

            $result = $this->whatsAppApiService->sendMessage($customerPhone, $message, $vendorId);

            Log::info('Payment confirmation message sent successfully', [
                'customer_phone' => $customerPhone,
                'order_id' => $order->order_id,
                'vendor_id' => $vendorId,
                'whatsapp_response' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation message', [
                'error' => $e->getMessage(),
                'customer_phone' => $customerPhone,
                'order_id' => $order->order_id ?? 'unknown',
                'vendor_id' => $vendorId,
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw the exception so calling code can handle it
            throw $e;
        }
    }

    /**
     * Get payment gateway settings
     *
     * @param int $vendorId
     * @return array
     */
    protected function getPaymentGatewaySettings(int $vendorId): array
    {
        $gateway = getVendorSettings('payment_gateway', null, null, $vendorId) ?: 'razorpay';
        $enabled = getVendorSettings('payment_gateway_enabled', null, null, $vendorId) ?: false;

        $settings = [
            'gateway' => $gateway,
            'enabled' => $enabled,
        ];

        if ($gateway === 'razorpay') {
            $settings['key_id'] = getVendorSettings('razorpay_key_id', null, null, $vendorId) ?: '';
            $settings['key_secret'] = getVendorSettings('razorpay_key_secret', null, null, $vendorId) ?: '';
            $settings['webhook_secret'] = getVendorSettings('razorpay_webhook_secret', null, null, $vendorId) ?: '';

            // Check if required Razorpay settings are configured
            if (empty($settings['key_id']) || empty($settings['key_secret'])) {
                $settings['enabled'] = false;
                Log::warning('Razorpay credentials not configured', [
                    'vendor_id' => $vendorId,
                    'has_key_id' => !empty($settings['key_id']),
                    'has_key_secret' => !empty($settings['key_secret']),
                ]);
            }
        }

        Log::debug('Payment gateway settings retrieved', [
            'vendor_id' => $vendorId,
            'gateway' => $gateway,
            'enabled' => $enabled,
            'has_credentials' => !empty($settings['key_id']) && !empty($settings['key_secret']),
        ]);

        return $settings;
    }
}
