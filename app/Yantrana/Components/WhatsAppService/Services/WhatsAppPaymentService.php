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
use App\Yantrana\Components\WhatsAppService\Models\WhatsAppPaymentModel;
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

            // Validate currency support
            if (!$this->isCurrencySupported($gatewaySettings['gateway'], $order->currency)) {
                throw new \Exception("Currency {$order->currency} is not supported by {$gatewaySettings['gateway']} gateway.");
            }

            // Create payment link based on gateway
            switch ($gatewaySettings['gateway']) {
                case WhatsAppPaymentModel::GATEWAY_RAZORPAY:
                    return $this->createRazorpayPaymentLink($order, $gatewaySettings, $vendorId);

                case WhatsAppPaymentModel::GATEWAY_PHONEPE:
                    return $this->createPhonePePaymentLink($order, $gatewaySettings, $vendorId);

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

            // Create payment record
            $paymentData = [
                'payment_id' => $paymentLink['id'],
                'vendors__id' => $vendorId,
                'order_id' => $order->order_id,
                'amount' => $order->getFinalAmount(),
                'currency' => $order->currency,
                'status' => WhatsAppPaymentModel::STATUS_PENDING,
                'payment_link_id' => $paymentLink['id'],
                'payment_link_url' => $paymentLink['short_url'] ?? $paymentLink['url'],
                'gateway' => WhatsAppPaymentModel::GATEWAY_RAZORPAY,
                'gateway_response' => [
                    'request_data' => $paymentData,
                    'response_data' => $paymentLink,
                ],
                'payment_initiated_at' => now(),
            ];

            $payment = $this->whatsAppPaymentRepository->createPayment($paymentData);

            return [
                'success' => true,
                'payment_link' => $paymentLink['short_url'] ?? $paymentLink['url'],
                'payment_id' => $paymentLink['id'],
                'payment_record' => $payment,
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
     * Create PhonePe payment link
     *
     * @param object $order
     * @param array $settings
     * @param int $vendorId
     * @return array
     */
    protected function createPhonePePaymentLink($order, array $settings, int $vendorId): array
    {
        try {
            $amount = round($order->getFinalAmount() * 100); // Amount in paise
            $merchantTransactionId = 'TXN_' . $order->order_id . '_' . time();
            
            $payload = [
                'merchantId' => $settings['merchant_id'],
                'merchantTransactionId' => $merchantTransactionId,
                'merchantUserId' => 'MUID_' . $vendorId,
                'amount' => $amount,
                'redirectUrl' => route('whatsapp.payment.success'),
                'redirectMode' => 'POST',
                'callbackUrl' => route('whatsapp.payment.webhook.phonepe'),
                'mobileNumber' => $order->customer_phone,
                'paymentInstrument' => [
                    'type' => 'PAY_PAGE'
                ]
            ];

            $jsonPayload = json_encode($payload);
            $base64Payload = base64_encode($jsonPayload);
            
            // Generate checksum
            $checksum = hash('sha256', $base64Payload . '/pg/v1/pay' . $settings['salt_key']) . '###' . $settings['salt_index'];

            // Determine API URL based on environment
            $baseUrl = $settings['environment'] === 'PROD' 
                ? 'https://api.phonepe.com/apis/hermes'
                : 'https://api-preprod.phonepe.com/apis/hermes';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
            ])->post($baseUrl . '/pg/v1/pay', [
                'request' => $base64Payload
            ]);

            if (!$response->successful()) {
                throw new \Exception('PhonePe API error: ' . $response->body());
            }

            $paymentResponse = $response->json();

            Log::debug('PhonePe payment link response', [
                'order_id' => $order->order_id,
                'response_keys' => array_keys($paymentResponse),
                'has_url' => isset($paymentResponse['data']['instrumentResponse']['redirectInfo']['url']),
            ]);

            // Validate required fields in response
            if (!isset($paymentResponse['data']['instrumentResponse']['redirectInfo']['url'])) {
                throw new \Exception('Payment URL not found in PhonePe response');
            }

            $paymentUrl = $paymentResponse['data']['instrumentResponse']['redirectInfo']['url'];

            // Create payment record
            $paymentData = [
                'payment_id' => $merchantTransactionId,
                'vendors__id' => $vendorId,
                'order_id' => $order->order_id,
                'amount' => $order->getFinalAmount(),
                'currency' => $order->currency,
                'status' => WhatsAppPaymentModel::STATUS_PENDING,
                'payment_link_id' => $merchantTransactionId,
                'payment_link_url' => $paymentUrl,
                'gateway' => WhatsAppPaymentModel::GATEWAY_PHONEPE,
                'gateway_response' => [
                    'request_data' => $payload,
                    'response_data' => $paymentResponse,
                ],
                'payment_initiated_at' => now(),
            ];

            $payment = $this->whatsAppPaymentRepository->createPayment($paymentData);

            return [
                'success' => true,
                'payment_link' => $paymentUrl,
                'payment_id' => $merchantTransactionId,
                'payment_record' => $payment,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create PhonePe payment link', [
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

            // Verify webhook signature
            if (!$this->verifyWebhookSignature($webhookData, $gateway, $gatewaySettings)) {
                throw new \Exception('Invalid webhook signature');
            }

            switch ($gateway) {
                case WhatsAppPaymentModel::GATEWAY_RAZORPAY:
                    return $this->processRazorpayWebhook($webhookData, $vendorId);
                
                case WhatsAppPaymentModel::GATEWAY_PHONEPE:
                    return $this->processPhonePeWebhook($webhookData, $vendorId);
                
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
     * Verify webhook signature based on gateway
     *
     * @param array $webhookData
     * @param string $gateway
     * @param array $settings
     * @return bool
     */
    protected function verifyWebhookSignature(array $webhookData, string $gateway, array $settings): bool
    {
        switch ($gateway) {
            case WhatsAppPaymentModel::GATEWAY_RAZORPAY:
                if (empty($settings['webhook_secret'])) {
                    return true; // Skip verification if no secret configured
                }
                $signature = request()->header('X-Razorpay-Signature');
                return $this->verifyRazorpaySignature($webhookData, $signature, $settings['webhook_secret']);

            case WhatsAppPaymentModel::GATEWAY_PHONEPE:
                // PhonePe doesn't use signature verification in the same way
                // We'll rely on the callback URL and transaction ID validation
                return true;

            default:
                return false;
        }
    }

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

    /**
     * Process Razorpay webhook
     *
     * @param array $webhookData
     * @param int|null $vendorId
     * @return array
     */
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
     * Process PhonePe webhook
     *
     * @param array $webhookData
     * @param int|null $vendorId
     * @return array
     */
    protected function processPhonePeWebhook(array $webhookData, ?int $vendorId = null): array
    {
        Log::info('Processing PhonePe webhook', [
            'vendor_id' => $vendorId,
            'webhook_data' => array_keys($webhookData),
        ]);

        $merchantTransactionId = $webhookData['merchantTransactionId'] ?? '';
        $transactionId = $webhookData['transactionId'] ?? '';
        $amount = $webhookData['amount'] ?? 0;
        $code = $webhookData['code'] ?? '';
        $message = $webhookData['message'] ?? '';

        if (empty($merchantTransactionId)) {
            throw new \Exception('Missing merchant transaction ID in PhonePe webhook');
        }

        // Find payment record
        $payment = $this->whatsAppPaymentRepository->fetchByPaymentId($merchantTransactionId, $vendorId);
        
        if (!$payment) {
            throw new \Exception('Payment record not found: ' . $merchantTransactionId);
        }

        // Check payment status
        if ($code === 'PAYMENT_SUCCESS') {
            return $this->handlePhonePePaymentSuccess($webhookData, $payment, $vendorId);
        } else {
            return $this->handlePhonePePaymentFailure($webhookData, $payment, $vendorId);
        }
    }

    /**
     * Handle PhonePe payment success
     *
     * @param array $webhookData
     * @param object $payment
     * @param int $vendorId
     * @return array
     */
    protected function handlePhonePePaymentSuccess(array $webhookData, $payment, int $vendorId): array
    {
        $merchantTransactionId = $webhookData['merchantTransactionId'];
        $transactionId = $webhookData['transactionId'];
        $amount = $webhookData['amount'] / 100; // Convert from paise to rupees

        // Update payment record
        $this->whatsAppPaymentRepository->updatePaymentStatus(
            $merchantTransactionId,
            WhatsAppPaymentModel::STATUS_COMPLETED,
            [
                'transaction_id' => $transactionId,
                'payment_method' => 'UPI',
                'gateway_response' => [
                    'webhook_data' => $webhookData,
                ],
                'payment_completed_at' => now(),
            ],
            $vendorId
        );

        // Update order status
        $this->whatsAppOrderRepository->updateOrderStatus(
            $payment->order_id,
            'paid',
            [
                'payment_id' => $merchantTransactionId,
                'payment_status' => 'completed',
            ],
            $vendorId
        );

        // Send confirmation message
        $order = $this->whatsAppOrderRepository->fetchByOrderId($payment->order_id, $vendorId);
        if ($order) {
            try {
                $this->sendPaymentConfirmationMessage($order->customer_phone, $order, $vendorId);
            } catch (\Exception $e) {
                Log::error('Failed to send PhonePe payment confirmation message', [
                    'error' => $e->getMessage(),
                    'order_id' => $payment->order_id,
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'PhonePe payment processed successfully',
        ];
    }

    /**
     * Handle PhonePe payment failure
     *
     * @param array $webhookData
     * @param object $payment
     * @param int $vendorId
     * @return array
     */
    protected function handlePhonePePaymentFailure(array $webhookData, $payment, int $vendorId): array
    {
        $merchantTransactionId = $webhookData['merchantTransactionId'];
        $message = $webhookData['message'] ?? 'Payment failed';

        // Update payment record
        $this->whatsAppPaymentRepository->updatePaymentStatus(
            $merchantTransactionId,
            WhatsAppPaymentModel::STATUS_FAILED,
            [
                'gateway_response' => [
                    'webhook_data' => $webhookData,
                ],
                'payment_failed_at' => now(),
            ],
            $vendorId
        );

        // Update order status
        $this->whatsAppOrderRepository->updateOrderStatus(
            $payment->order_id,
            'payment_failed',
            [
                'payment_id' => $merchantTransactionId,
                'payment_status' => 'failed',
            ],
            $vendorId
        );

        return [
            'success' => true,
            'message' => 'PhonePe payment failure processed',
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

        return [
            'success' => true,
            'message' => 'Payment processed successfully',
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
        $errorCode = $paymentData['error_code'] ?? '';
        $errorDescription = $paymentData['error_description'] ?? '';

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
                    'error_code' => $errorCode,
                    'error_description' => $errorDescription,
                ],
                'payment_failed_at' => now(),
            ],
            $vendorId
        );

        return [
            'success' => true,
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
    protected function sendPaymentConfirmationMessage(string $customerPhone, $order, int $vendorId): void
    {
        try {
            // Build items list
            $itemsList = '';
            if ($order->items && is_array($order->items)) {
                foreach ($order->items as $item) {
                    $price = $item['price'] ?? 0;
                    $quantity = $item['quantity'] ?? 1;
                    $itemsList .= "• {$item['name']} x{$quantity} - " . 
                        $order->currency . ' ' . 
                        number_format($price, 2) . "\n";
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
     * Check if currency is supported by gateway
     *
     * @param string $gateway
     * @param string $currency
     * @return bool
     */
    protected function isCurrencySupported(string $gateway, string $currency): bool
    {
        $supportedCurrencies = WhatsAppPaymentModel::getAvailableGateways()[$gateway]['supported_currencies'] ?? [];
        return in_array($currency, $supportedCurrencies);
    }

    /**
     * Get payment gateway settings
     *
     * @param int $vendorId
     * @return array
     */
    protected function getPaymentGatewaySettings(int $vendorId): array
    {
        $gateway = getVendorSettings('payment_gateway', null, null, $vendorId) ?: WhatsAppPaymentModel::GATEWAY_RAZORPAY;
        $enabled = getVendorSettings('payment_gateway_enabled', null, null, $vendorId) ?: false;

        $settings = [
            'gateway' => $gateway,
            'enabled' => $enabled,
        ];

        if ($gateway === WhatsAppPaymentModel::GATEWAY_RAZORPAY) {
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
        } elseif ($gateway === WhatsAppPaymentModel::GATEWAY_PHONEPE) {
            $settings['merchant_id'] = getVendorSettings('phonepe_merchant_id', null, null, $vendorId) ?: '';
            $settings['salt_key'] = getVendorSettings('phonepe_salt_key', null, null, $vendorId) ?: '';
            $settings['salt_index'] = getVendorSettings('phonepe_salt_index', null, null, $vendorId) ?: '1';
            $settings['environment'] = getVendorSettings('phonepe_environment', null, null, $vendorId) ?: 'UAT';

            // Check if required PhonePe settings are configured
            if (empty($settings['merchant_id']) || empty($settings['salt_key'])) {
                $settings['enabled'] = false;
                Log::warning('PhonePe credentials not configured', [
                    'vendor_id' => $vendorId,
                    'has_merchant_id' => !empty($settings['merchant_id']),
                    'has_salt_key' => !empty($settings['salt_key']),
                ]);
            }
        }

        Log::debug('Payment gateway settings retrieved', [
            'vendor_id' => $vendorId,
            'gateway' => $gateway,
            'enabled' => $enabled,
            'has_credentials' => $this->hasValidCredentials($settings),
        ]);

        return $settings;
    }

    /**
     * Check if gateway has valid credentials
     *
     * @param array $settings
     * @return bool
     */
    protected function hasValidCredentials(array $settings): bool
    {
        switch ($settings['gateway']) {
            case WhatsAppPaymentModel::GATEWAY_RAZORPAY:
                return !empty($settings['key_id']) && !empty($settings['key_secret']);
            
            case WhatsAppPaymentModel::GATEWAY_PHONEPE:
                return !empty($settings['merchant_id']) && !empty($settings['salt_key']);
            
            default:
                return false;
        }
    }
}
