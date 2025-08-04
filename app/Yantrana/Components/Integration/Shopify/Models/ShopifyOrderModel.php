<?php
/**
 * ShopifyOrderModel.php - Model file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Vendor\Models\VendorModel;
use App\Yantrana\Components\Contact\Models\ContactModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopifyOrderModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'shopify_orders';

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'shopify_order_data' => 'array',
            'customer_data' => 'array',
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'line_items' => 'array',
            'fulfillments' => 'array',
            'refunds' => 'array',
            'metadata' => 'array:extend',
        ],
    ];

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'total_price' => 'decimal:2',
        'subtotal_price' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'created_at_shopify' => 'datetime',
        'updated_at_shopify' => 'datetime',
        'processed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancelled_at_shopify' => 'datetime',
        'closed_at' => 'datetime',
        'closed_at_shopify' => 'datetime',
        'processed_at_shopify' => 'datetime',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
        '_uid',
        'shopify_integrations__id',
        'vendors__id',
        'contacts__id',
        'shopify_order_id',
        'order_number',
        'name',
        'email',
        'phone',
        'currency',
        'financial_status',
        'fulfillment_status',
        'total_price',
        'subtotal_price',
        'total_tax',
        'total_discounts',
        'total_weight',
        'total_items',
        'tags',
        'note',
        'processed_at',
        'cancelled_at',
        'closed_at',
        'created_at_shopify',
        'updated_at_shopify',
        'processed_at_shopify',
        'cancelled_at_shopify',
        'closed_at_shopify',
        '__data',
    ];

    protected $appends = [
        'status_label',
        'financial_status_label',
        'fulfillment_status_label',
        'formatted_total_price',
        'order_summary',
    ];

    /**
     * Get the integration that owns the order
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(ShopifyIntegrationModel::class, 'shopify_integrations__id', '_id');
    }

    /**
     * Get the vendor that owns the order
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendors__id', '_id');
    }

    /**
     * Get the contact that placed the order
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(ContactModel::class, 'contacts__id', '_id');
    }

    /**
     * Get the notifications for this order
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(ShopifyOrderNotificationModel::class, 'shopify_orders__id', '_id');
    }

    /**
     * Get status label
     */
    protected function getStatusLabelAttribute(): string
    {
        $statusLabels = [
            'open' => 'Open',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
        ];

        return $statusLabels[$this->attributes['status'] ?? 'open'] ?? 'Unknown';
    }

    /**
     * Get financial status label
     */
    protected function getFinancialStatusLabelAttribute(): string
    {
        $statusLabels = [
            'pending' => 'Pending',
            'authorized' => 'Authorized',
            'paid' => 'Paid',
            'partially_paid' => 'Partially Paid',
            'refunded' => 'Refunded',
            'voided' => 'Voided',
            'partially_refunded' => 'Partially Refunded',
            'unpaid' => 'Unpaid',
        ];

        return $statusLabels[$this->financial_status] ?? ucfirst($this->financial_status);
    }

    /**
     * Get fulfillment status label
     */
    protected function getFulfillmentStatusLabelAttribute(): string
    {
        $statusLabels = [
            'unfulfilled' => 'Unfulfilled',
            'partial' => 'Partially Fulfilled',
            'fulfilled' => 'Fulfilled',
            'restocked' => 'Restocked',
        ];

        return $statusLabels[$this->fulfillment_status] ?? ucfirst($this->fulfillment_status);
    }

    /**
     * Get formatted total price
     */
    protected function getFormattedTotalPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_price, 2);
    }

    /**
     * Get order summary
     */
    protected function getOrderSummaryAttribute(): string
    {
        $summary = "Order #{$this->order_number}\n";
        $summary .= "Status: {$this->status_label}\n";
        $summary .= "Financial: {$this->financial_status_label}\n";
        $summary .= "Fulfillment: {$this->fulfillment_status_label}\n";
        $summary .= "Total: {$this->formatted_total_price}\n";
        $summary .= "Items: {$this->total_items}";
        
        return $summary;
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return in_array($this->financial_status, ['paid', 'partially_paid']);
    }

    /**
     * Check if order is fulfilled
     */
    public function isFulfilled(): bool
    {
        return in_array($this->fulfillment_status, ['fulfilled', 'partial']);
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Get customer phone number
     */
    public function getCustomerPhone(): string
    {
        return $this->phone ?? '';
    }

    /**
     * Get customer email
     */
    public function getCustomerEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * Get line items as formatted string
     */
    public function getFormattedLineItems(): string
    {
        $lineItems = $this->__data['line_items'] ?? [];
        
        if (empty($lineItems)) {
            return 'No items';
        }

        return collect($lineItems)->map(function ($item) {
            $price = number_format($item['price'] ?? 0, 2);
            $quantity = $item['quantity'] ?? 1;
            $name = $item['name'] ?? 'Unknown Item';
            
            return "• {$name} (Qty: {$quantity}) - {$this->currency} {$price}";
        })->join("\n");
    }

    /**
     * Get shipping address
     */
    public function getShippingAddress(): array
    {
        return $this->__data['shipping_address'] ?? [];
    }

    /**
     * Get billing address
     */
    public function getBillingAddress(): array
    {
        return $this->__data['billing_address'] ?? [];
    }

    /**
     * Get fulfillments
     */
    public function getFulfillments(): array
    {
        return $this->__data['fulfillments'] ?? [];
    }

    /**
     * Get refunds
     */
    public function getRefunds(): array
    {
        return $this->__data['refunds'] ?? [];
    }

    /**
     * Get customer name
     */
    public function getCustomerName(): string
    {
        $customerData = $this->__data['customer_data'] ?? [];
        return $customerData['first_name'] . ' ' . $customerData['last_name'] ?? $this->name ?? '';
    }

    /**
     * Get customer first name
     */
    public function getCustomerFirstName(): string
    {
        $customerData = $this->__data['customer_data'] ?? [];
        return $customerData['first_name'] ?? '';
    }

    /**
     * Get customer last name
     */
    public function getCustomerLastName(): string
    {
        $customerData = $this->__data['customer_data'] ?? [];
        return $customerData['last_name'] ?? '';
    }
} 