<?php
/**
 * WhatsAppOrderService.php - Service file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Services;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppOrderRepository;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppUserStateRepository;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppApiService;
use App\Yantrana\Components\Contact\Repositories\ContactRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppOrderService extends BaseEngine
{
    /**
     * @var WhatsAppOrderRepository
     */
    protected $whatsAppOrderRepository;

    /**
     * @var WhatsAppUserStateRepository
     */
    protected $whatsAppUserStateRepository;

    /**
     * @var WhatsAppApiService
     */
    protected $whatsAppApiService;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * Constructor
     */
    public function __construct(
        WhatsAppOrderRepository $whatsAppOrderRepository,
        WhatsAppUserStateRepository $whatsAppUserStateRepository,
        WhatsAppApiService $whatsAppApiService,
        ContactRepository $contactRepository
    ) {
        $this->whatsAppOrderRepository = $whatsAppOrderRepository;
        $this->whatsAppUserStateRepository = $whatsAppUserStateRepository;
        $this->whatsAppApiService = $whatsAppApiService;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Process catalog order from WhatsApp
     *
     * @param array $orderData
     * @param string $customerPhone
     * @param int|null $vendorId
     * @return array
     */
    public function processCatalogOrder(array $orderData, string $customerPhone, ?int $vendorId = null): array
    {
        try {
            $vendorId = $vendorId ?: getVendorId();
            
            // Get or create contact
            $contact = $this->getOrCreateContact($customerPhone, $vendorId);
            
            // Generate order ID
            $orderId = $this->whatsAppOrderRepository->generateOrderId($vendorId);
            
            // Calculate totals
            $totals = $this->calculateOrderTotals($orderData['product_items']);
            
            // Create order
            $order = $this->whatsAppOrderRepository->createOrder([
                'order_id' => $orderId,
                'vendors__id' => $vendorId,
                'contacts__id' => $contact->_id,
                'customer_phone' => $customerPhone,
                'customer_name' => $contact->first_name . ' ' . $contact->last_name,
                'items' => $orderData['product_items'],
                'total_amount' => $totals['total'],
                'tax_amount' => $totals['tax'],
                'shipping_amount' => $totals['shipping'],
                'discount_amount' => $totals['discount'],
                'currency' => $totals['currency'],
                'status' => 'pending',
                '__data' => [
                    'catalog_data' => $orderData,
                    'customer_details' => [
                        'phone' => $customerPhone,
                        'name' => $contact->first_name . ' ' . $contact->last_name,
                    ],
                ],
            ]);

            if (!$order) {
                throw new \Exception('Failed to create order');
            }

            // Send order confirmation message
            $this->sendOrderConfirmationMessage($customerPhone, $order, $vendorId);

            Log::info('Catalog order processed successfully', [
                'order_id' => $orderId,
                'customer_phone' => $customerPhone,
                'vendor_id' => $vendorId,
                'total_amount' => $totals['total'],
            ]);

            return [
                'success' => true,
                'order_id' => $orderId,
                'order' => $order,
                'message' => 'Order processed successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to process catalog order', [
                'error' => $e->getMessage(),
                'customer_phone' => $customerPhone,
                'vendor_id' => $vendorId,
                'order_data' => $orderData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to process order',
            ];
        }
    }

    /**
     * Handle address input from customer
     *
     * @param string $customerPhone
     * @param string $address
     * @param int|null $vendorId
     * @return array
     */
    public function handleAddressInput(string $customerPhone, string $address, ?int $vendorId = null): array
    {
        try {
            $vendorId = $vendorId ?: getVendorId();
            
            // Get user state
            $userState = $this->whatsAppUserStateRepository->fetchByPhone($customerPhone, $vendorId);
            
            if (!$userState || !$userState->isState('awaiting_address')) {
                return [
                    'success' => false,
                    'message' => 'No pending address request found',
                ];
            }

            // Get order
            $order = $this->whatsAppOrderRepository->fetchByOrderId($userState->order_id, $vendorId);
            
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found',
                ];
            }

            // Update order with address
            $this->whatsAppOrderRepository->updateOrderStatus(
                $order->order_id,
                'awaiting_payment',
                ['delivery_address' => $address],
                $vendorId
            );

            // Clear user state
            $this->whatsAppUserStateRepository->clearState($customerPhone, $vendorId);

            // Create payment link and send payment message
            $paymentResult = app(WhatsAppPaymentService::class)->createPaymentLink($order->fresh(), $vendorId);

            if ($paymentResult['success']) {
                $this->sendPaymentMessage($customerPhone, $order->fresh(), $paymentResult['payment_link'], $vendorId);
            } else {
                // Send error message to customer about payment setup
                $errorMessage = "✅ *Address Confirmed!*\n\n" .
                              "📍 *Delivery Address:*\n{$address}\n\n" .
                              "⚠️ *Payment Setup Issue*\n" .
                              "We're experiencing technical difficulties with payment processing.\n\n" .
                              "📞 *Please contact us directly to complete your order:*\n" .
                              "• Reply to this chat\n" .
                              "• We'll arrange payment and delivery\n\n" .
                              "📝 *Order ID: #{$order->order_id}*\n" .
                              "💰 *Amount: {$order->fresh()->formatted_total_amount}*\n\n" .
                              "Thank you for your patience! 🙏";

                $this->whatsAppApiService->sendMessage($customerPhone, $errorMessage, $vendorId);

                // Update order status to manual_payment_required
                $this->whatsAppOrderRepository->updateOrderStatus(
                    $order->order_id,
                    'manual_payment_required',
                    ['payment_error' => $paymentResult['error'] ?? 'Payment gateway not configured'],
                    $vendorId
                );
            }

            Log::info('Address processed successfully', [
                'order_id' => $order->order_id,
                'customer_phone' => $customerPhone,
                'vendor_id' => $vendorId,
            ]);

            return [
                'success' => true,
                'order_id' => $order->order_id,
                'message' => 'Address processed successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to process address input', [
                'error' => $e->getMessage(),
                'customer_phone' => $customerPhone,
                'vendor_id' => $vendorId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to process address',
            ];
        }
    }

    /**
     * Send order confirmation message
     *
     * @param string $customerPhone
     * @param object $order
     * @param int $vendorId
     * @return void
     */
    protected function sendOrderConfirmationMessage(string $customerPhone, $order, int $vendorId): void
    {
        // Send order confirmation message
        $confirmationMessage = "🛍️ *Order Confirmed!*\n\n" .
                              "Thank you for your order!\n\n" .
                              "📝 Order ID: #{$order->order_id}\n" .
                              "💰 Total: {$order->formatted_total_amount}\n\n" .
                              "📦 Items:\n{$order->formatted_items}";

        $this->whatsAppApiService->sendMessage($customerPhone, $confirmationMessage, $vendorId);

        // Send address request message
        $addressMessage = "📍 *Please provide your delivery address:*\n\n" .
                         "Format:\n" .
                         "🏠 House/Flat No.\n" .
                         "🏢 Building/Society Name\n" .
                         "🛣️ Street/Area\n" .
                         "🏙️ City, State - Pincode\n" .
                         "📱 Contact Number\n\n";
                         

        $this->whatsAppApiService->sendMessage($customerPhone, $addressMessage, $vendorId);

        // Set user state to awaiting address
        $this->whatsAppUserStateRepository->createOrUpdateState(
            $customerPhone,
            'awaiting_address',
            [
                'order_id' => $order->order_id,
                'context' => 'order_address_collection',
                'expires_at' => now()->addHours(24),
            ],
            $vendorId
        );
    }

    /**
     * Send payment message
     *
     * @param string $customerPhone
     * @param object $order
     * @param array $paymentLink
     * @param int $vendorId
     * @return void
     */
    protected function sendPaymentMessage(string $customerPhone, $order, array $paymentLink, int $vendorId): void
    {
        $message = "✅ *Address Confirmed!*\n\n" .
                  "📍 *Delivery Address:*\n{$order->delivery_address}\n\n" .
                  "💳 *Complete Payment:*\n{$paymentLink['short_url']}\n\n" .
                  "💰 *Amount: {$order->formatted_total_amount}*\n\n" .
                  "🔒 *Secure Payment Options:*\n\n" .
                  "⏰ Payment link expires in 24 hours";

        $this->whatsAppApiService->sendMessage($customerPhone, $message, $vendorId);
    }

    /**
     * Calculate order totals
     *
     * @param array $items
     * @return array
     */
    protected function calculateOrderTotals(array $items): array
    {
        $subtotal = 0;
        
        foreach ($items as $item) {
            $price = floatval($item['item_price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            $subtotal += $price * $quantity;
        }

        // Get tax and shipping settings from vendor settings
        $taxRate = getVendorSettings('order_tax_rate', 0) / 100;
        $shippingAmount = getVendorSettings('order_shipping_amount', 0);
        
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal + $taxAmount + $shippingAmount;

        return [
            'subtotal' => $subtotal,
            'tax' => $taxAmount,
            'shipping' => $shippingAmount,
            'discount' => 0,
            'total' => $total,
            'currency' => getVendorSettings('order_currency', 'INR'),
        ];
    }

    /**
     * Get or create contact
     *
     * @param string $phone
     * @param int $vendorId
     * @return object
     */
    protected function getOrCreateContact(string $phone, int $vendorId)
    {
        $contact = $this->contactRepository->getVendorContactByWaId($phone, $vendorId);
        
        if (!$contact) {
            // Create new contact
            $contact = $this->contactRepository->storeIt([
                'wa_id' => $phone,
                'first_name' => 'Customer',
                'last_name' => '',
                'vendors__id' => $vendorId,
                'status' => 1,
            ]);
        }

        return $contact;
    }
}
