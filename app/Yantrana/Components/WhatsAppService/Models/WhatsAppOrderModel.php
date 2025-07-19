<?php
/**
 * WhatsAppOrderModel.php - Model file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Models;

use App\Yantrana\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Yantrana\Components\Contact\Models\ContactModel;
use App\Yantrana\Components\Vendor\Models\VendorModel;

class WhatsAppOrderModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'whatsapp_orders';

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'catalog_data' => 'array',
            'shipping_details' => 'array',
            'customer_details' => 'array',
            'order_notes' => 'array',
            'tracking_info' => 'array',
            'metadata' => 'array:extend',
        ],
    ];

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
        'payment_completed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'vendors__id',
        'contacts__id',
        'customer_phone',
        'customer_name',
        'items',
        'total_amount',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'delivery_address',
        'status',
        'payment_id',
        'payment_status',
        'currency',
        '__data',
        'ordered_at',
        'payment_completed_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $appends = [
        'formatted_total_amount',
        'formatted_items',
        'order_summary',
        'status_label',
    ];

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
     * Get the payments for this order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(WhatsAppPaymentModel::class, 'order_id', 'order_id');
    }

    /**
     * Get the user state for this order
     */
    public function userState(): HasOne
    {
        return $this->hasOne(WhatsAppUserStateModel::class, 'order_id', 'order_id');
    }

    /**
     * Get formatted total amount
     */
    protected function getFormattedTotalAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted items list
     */
    protected function getFormattedItemsAttribute(): string
    {
        if (empty($this->items)) {
            return '';
        }

        return collect($this->items)->map(function ($item) {
            $price = isset($item['item_price']) ? number_format($item['item_price'], 2) : '0.00';
            $quantity = $item['quantity'] ?? 1;
            $name = $item['product_retailer_id'] ?? $item['name'] ?? 'Unknown Item';
            
            return "• {$name} (Qty: {$quantity}) - {$this->currency} {$price}";
        })->join("\n");
    }

    /**
     * Get order summary
     */
    protected function getOrderSummaryAttribute(): string
    {
        $summary = "Order #{$this->order_id}\n";
        $summary .= "Status: {$this->status_label}\n";
        $summary .= "Total: {$this->formatted_total_amount}\n";
        $summary .= "Items:\n{$this->formatted_items}";
        
        return $summary;
    }

    /**
     * Get status label
     */
    protected function getStatusLabelAttribute(): string
    {
        $statusLabels = [
            'pending' => 'Pending',
            'awaiting_address' => 'Awaiting Address',
            'awaiting_payment' => 'Awaiting Payment',
            'payment_processing' => 'Processing Payment',
            'paid' => 'Paid',
            'confirmed' => 'Confirmed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Calculate final amount including tax and shipping
     */
    public function getFinalAmount(): float
    {
        return $this->total_amount + $this->tax_amount + $this->shipping_amount - $this->discount_amount;
    }

    /**
     * Check if order is in a completed state
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled', 'refunded']);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'awaiting_address', 'awaiting_payment', 'paid', 'confirmed']);
    }

    /**
     * Check if order requires payment
     */
    public function requiresPayment(): bool
    {
        return in_array($this->status, ['awaiting_payment', 'payment_processing']);
    }
}
