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
     * Skip JSON column protocol for debugging
     */
    protected $skipJsonColumnProtocol = false;

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
        'formatted_subtotal_price',
        'formatted_total_tax',
        'formatted_total_discounts',
        'formatted_total_shipping_price',
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
     * Get formatted subtotal price
     */
    protected function getFormattedSubtotalPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->subtotal_price, 2);
    }

    /**
     * Get formatted total tax
     */
    protected function getFormattedTotalTaxAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_tax, 2);
    }

    /**
     * Get formatted total discounts
     */
    protected function getFormattedTotalDiscountsAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_discounts, 2);
    }

    /**
     * Get formatted total shipping price
     */
    protected function getFormattedTotalShippingPriceAttribute(): string
    {
        // Try to get shipping price from __data
        $shippingPrice = 0;
        
        // Check if we have shipping lines in the order data
        $orderData = $this->__data['shopify_order_data'] ?? [];
        if (isset($orderData['shipping_lines']) && is_array($orderData['shipping_lines'])) {
            foreach ($orderData['shipping_lines'] as $shippingLine) {
                $shippingPrice += ($shippingLine['price'] ?? 0);
            }
        }
        
        // If no shipping lines found, try to calculate from total - subtotal
        if ($shippingPrice == 0 && $this->total_price > 0 && $this->subtotal_price > 0) {
            $shippingPrice = $this->total_price - $this->subtotal_price - $this->total_tax + $this->total_discounts;
        }
        
        return $this->currency . ' ' . number_format($shippingPrice, 2);
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
        $lineItems = $this->getLineItems();
        
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
     * Get line items array
     */
    public function getLineItems(): array
    {
        // Try to get from line_items first
        $lineItems = $this->__data['line_items'] ?? [];
        
        // If not found, try to get from shopify_order_data
        if (empty($lineItems)) {
            $lineItems = $this->__data['shopify_order_data']['line_items'] ?? [];
        }
        
        // If still not found, try to decode from raw JSON
        if (empty($lineItems)) {
            $rawJson = $this->getRawJsonData();
            $decodedData = json_decode($rawJson, true);
            if ($decodedData && is_array($decodedData)) {
                $lineItems = $decodedData['line_items'] ?? $decodedData['shopify_order_data']['line_items'] ?? [];
            }
        }
        
        // If still not found, try to decode from raw database value
        if (empty($lineItems)) {
            $rawDbValue = $this->getRawDatabaseData();
            $decodedData = json_decode($rawDbValue, true);
            if ($decodedData && is_array($decodedData)) {
                $lineItems = $decodedData['line_items'] ?? $decodedData['shopify_order_data']['line_items'] ?? [];
            }
        }
        
        // If still not found, try force decode
        if (empty($lineItems)) {
            $forceDecodedData = $this->forceDecodeData();
            $lineItems = $forceDecodedData['line_items'] ?? $forceDecodedData['shopify_order_data']['line_items'] ?? [];
        }
        
        // Debug logging
        \Log::info('Getting line items from model', [
            'order_id' => $this->shopify_order_id,
            'direct_line_items_count' => count($this->__data['line_items'] ?? []),
            'shopify_order_data_line_items_count' => count($this->__data['shopify_order_data']['line_items'] ?? []),
            'final_line_items_count' => count($lineItems),
            '__data_keys' => array_keys($this->__data ?? []),
            'raw_json_length' => strlen($this->getRawJsonData()),
            'raw_db_value_length' => strlen($this->getRawDatabaseData()),
            'force_decoded_keys' => array_keys($this->forceDecodeData()),
        ]);
        
        return $lineItems;
    }

    /**
     * Get raw __data for debugging
     */
    public function getRawData(): array
    {
        return $this->__data ?? [];
    }

    /**
     * Get raw JSON data from database
     */
    public function getRawJsonData(): string
    {
        return $this->getRawOriginal('__data') ?? '{}';
    }

    /**
     * Get raw database value for __data field
     */
    public function getRawDatabaseData(): string
    {
        $result = \DB::table('shopify_orders')
            ->where('_id', $this->_id)
            ->value('__data');
        
        return $result ?? '{}';
    }

    /**
     * Force decode JSON data from the raw database value for __data
     */
    public function forceDecodeData(): array
    {
        $rawValue = $this->getRawDatabaseData();
        $decoded = json_decode($rawValue, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Force refresh the model to get latest data
     */
    public function refreshData(): void
    {
        $this->refresh();
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
        
        // Try to get from customer data first
        if (!empty($customerData['first_name']) || !empty($customerData['last_name'])) {
            $firstName = $customerData['first_name'] ?? '';
            $lastName = $customerData['last_name'] ?? '';
            return trim($firstName . ' ' . $lastName);
        }
        
        // Fallback to order name
        if (!empty($this->name)) {
            return $this->name;
        }
        
        // Fallback to contact name if available
        if ($this->contact && !empty($this->contact->first_name)) {
            return $this->contact->first_name;
        }
        
        return 'Unknown Customer';
    }

    /**
     * Get customer first name
     */
    public function getCustomerFirstName(): string
    {
        $customerData = $this->__data['customer_data'] ?? [];
        
        // Try to get from customer data first
        if (!empty($customerData['first_name'])) {
            return $customerData['first_name'];
        }
        
        // If we have a full name in contact, try to extract first name
        if ($this->contact && !empty($this->contact->first_name)) {
            $fullName = $this->contact->first_name;
            $nameParts = explode(' ', $fullName, 2);
            return $nameParts[0] ?? $fullName;
        }
        
        // Fallback to order name
        if (!empty($this->name)) {
            $nameParts = explode(' ', $this->name, 2);
            return $nameParts[0] ?? $this->name;
        }
        
        return '';
    }

    /**
     * Get customer last name
     */
    public function getCustomerLastName(): string
    {
        $customerData = $this->__data['customer_data'] ?? [];
        
        // Try to get from customer data first
        if (!empty($customerData['last_name'])) {
            return $customerData['last_name'];
        }
        
        // If we have a full name in contact, try to extract last name
        if ($this->contact && !empty($this->contact->first_name)) {
            $fullName = $this->contact->first_name;
            $nameParts = explode(' ', $fullName, 2);
            return $nameParts[1] ?? '';
        }
        
        // Fallback to order name
        if (!empty($this->name)) {
            $nameParts = explode(' ', $this->name, 2);
            return $nameParts[1] ?? '';
        }
        
        return '';
    }
} 