<?php
/**
 * WooCommerceOrderModel.php - Model file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceIntegrationModel;
use Carbon\Carbon;

class WooCommerceOrderModel extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'woocommerce_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendors__id',
        'woocommerce_integrations__id',
        'woocommerce_order_id',
        'order_number',
        'status',
        'total',
        'currency',
        'customer_data',
        'order_data',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'vendors__id' => 'integer',
        'woocommerce_integrations__id' => 'integer',
        'woocommerce_order_id' => 'integer',
        'total' => 'float',
        'customer_data' => 'array',
        'order_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_method',
        'formatted_total_price',
        'status_label',
        'payment_status',
        'fulfillment_status',
        'subtotal',
        'formatted_subtotal',
        'tax_total',
        'formatted_tax_total',
        'shipping_total',
        'formatted_shipping_total',
        'discount_total',
        'formatted_discount_total',
        'billing_address',
        'shipping_address',
        'items_count',
        'order_items',
        'total_items_quantity'
    ];

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        if ($this->total > 0) {
            return $this->currency . ' ' . number_format($this->total, 2);
        }
        return 'N/A';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $statusMap = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'on-hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'failed' => 'Failed'
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get customer name
     */
    public function getCustomerNameAttribute(): string
    {
        $customerData = $this->customer_data;
        
        if (isset($customerData['first_name']) && isset($customerData['last_name'])) {
            return trim($customerData['first_name'] . ' ' . $customerData['last_name']);
        }
        
        return $customerData['first_name'] ?? $customerData['last_name'] ?? 'N/A';
    }

    /**
     * Get customer email
     */
    public function getCustomerEmailAttribute(): string
    {
        return $this->customer_data['email'] ?? 'N/A';
    }

    /**
     * Get customer phone
     */
    public function getCustomerPhoneAttribute(): string
    {
        return $this->customer_data['phone'] ?? 'N/A';
    }

    /**
     * Get payment method
     */
    public function getPaymentMethodAttribute(): string
    {
        $orderData = $this->order_data;
        return $orderData['payment_method_title'] ?? 'N/A';
    }

    /**
     * Get payment status
     */
    public function getPaymentStatusAttribute(): string
    {
        if ($this->isPaid()) {
            return 'paid';
        }
        
        if ($this->isCancelled()) {
            return 'cancelled';
        }
        
        return 'pending';
    }

    /**
     * Get fulfillment status
     */
    public function getFulfillmentStatusAttribute(): string
    {
        if ($this->isFulfilled()) {
            return 'fulfilled';
        }
        
        if ($this->status === 'processing' || $this->status === 'on-hold') {
            return 'processing';
        }
        
        return 'unfulfilled';
    }

    /**
     * Get the integration that owns the order
     */
    public function integration()
    {
        return $this->belongsTo(WooCommerceIntegrationModel::class, 'woocommerce_integrations__id');
    }

    /**
     * Get order subtotal
     */
    public function getSubtotalAttribute(): float
    {
        $orderData = $this->order_data;
        // If subtotal is not available, calculate from line items
        if (!isset($orderData['subtotal'])) {
            $subtotal = 0;
            foreach ($orderData['line_items'] ?? [] as $item) {
                $subtotal += floatval($item['total'] ?? 0);
            }
            return $subtotal;
        }
        return floatval($orderData['subtotal'] ?? $this->total);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->subtotal, 2);
    }

    /**
     * Get tax total
     */
    public function getTaxTotalAttribute(): float
    {
        $orderData = $this->order_data;
        return floatval($orderData['total_tax'] ?? 0);
    }

    /**
     * Get formatted tax total
     */
    public function getFormattedTaxTotalAttribute(): string
    {
        $taxTotal = $this->tax_total;
        if ($taxTotal > 0) {
            return $this->currency . ' ' . number_format($taxTotal, 2);
        }
        return 'N/A';
    }

    /**
     * Get shipping total
     */
    public function getShippingTotalAttribute(): float
    {
        $orderData = $this->order_data;
        return floatval($orderData['shipping_total'] ?? 0);
    }

    /**
     * Get formatted shipping total
     */
    public function getFormattedShippingTotalAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->shipping_total, 2);
    }

    /**
     * Get discount total
     */
    public function getDiscountTotalAttribute(): float
    {
        $orderData = $this->order_data;
        return floatval($orderData['discount_total'] ?? 0);
    }

    /**
     * Get formatted discount total
     */
    public function getFormattedDiscountTotalAttribute(): string
    {
        $discountTotal = $this->discount_total;
        if ($discountTotal > 0) {
            return $this->currency . ' ' . number_format($discountTotal, 2);
        }
        return 'N/A';
    }

    /**
     * Get billing address
     */
    public function getBillingAddressAttribute(): string
    {
        $orderData = $this->order_data;
        $billing = $orderData['billing'] ?? [];
        
        $parts = [];
        if (!empty($billing['address_1'])) $parts[] = $billing['address_1'];
        if (!empty($billing['address_2'])) $parts[] = $billing['address_2'];
        if (!empty($billing['city'])) $parts[] = $billing['city'];
        if (!empty($billing['state'])) $parts[] = $billing['state'];
        if (!empty($billing['postcode'])) $parts[] = $billing['postcode'];
        if (!empty($billing['country'])) $parts[] = $billing['country'];
        
        return implode(', ', $parts) ?: 'N/A';
    }

    /**
     * Get shipping address
     */
    public function getShippingAddressAttribute(): string
    {
        $orderData = $this->order_data;
        $shipping = $orderData['shipping'] ?? [];
        
        $parts = [];
        if (!empty($shipping['address_1'])) $parts[] = $shipping['address_1'];
        if (!empty($shipping['address_2'])) $parts[] = $shipping['address_2'];
        if (!empty($shipping['city'])) $parts[] = $shipping['city'];
        if (!empty($shipping['state'])) $parts[] = $shipping['state'];
        if (!empty($shipping['postcode'])) $parts[] = $shipping['postcode'];
        if (!empty($shipping['country'])) $parts[] = $shipping['country'];
        
        return implode(', ', $parts) ?: 'N/A';
    }

    /**
     * Get notifications for this order
     */
    public function notifications()
    {
        return $this->hasMany(\App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceOrderNotificationModel::class, 'woocommerce_orders__id');
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['processing', 'completed']);
    }

    /**
     * Check if order is fulfilled
     */
    public function isFulfilled(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'refunded']);
    }

    /**
     * Get order items count
     */
    public function getItemsCountAttribute(): int
    {
        $orderData = $this->order_data;
        return count($orderData['line_items'] ?? []);
    }

    /**
     * Get order items
     */
    public function getOrderItemsAttribute(): array
    {
        $orderData = $this->order_data;
        return $orderData['line_items'] ?? [];
    }

    /**
     * Get total items quantity
     */
    public function getTotalItemsQuantityAttribute(): int
    {
        $items = $this->order_items;
        $totalQuantity = 0;
        
        foreach ($items as $item) {
            $totalQuantity += $item['quantity'] ?? 0;
        }
        
        return $totalQuantity;
    }
} 