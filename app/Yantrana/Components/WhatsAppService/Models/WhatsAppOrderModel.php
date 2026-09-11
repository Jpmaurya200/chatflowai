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
        'formatted_final_amount',
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
     * Get formatted final amount (after discount)
     */
    protected function getFormattedFinalAmountAttribute(): string
    {
        try {
            $finalAmount = $this->getFinalAmount();
            $currency = $this->currency ?: 'INR';
            return $currency . ' ' . number_format($finalAmount, 2);
        } catch (\Exception $e) {
            \Log::error('Error in getFormattedFinalAmountAttribute', [
                'order_id' => $this->order_id ?? 'unknown',
                'error' => $e->getMessage(),
                'total_amount' => $this->total_amount ?? 0,
                'discount_amount' => $this->discount_amount ?? 0,
                'currency' => $this->currency ?? 'INR',
            ]);
            
            // Fallback to basic formatting
            $finalAmount = ($this->total_amount ?? 0) - ($this->discount_amount ?? 0);
            $currency = $this->currency ?: 'INR';
            return $currency . ' ' . number_format($finalAmount, 2);
        }
    }

    /**
     * Get formatted items list
     */
    protected function getFormattedItemsAttribute(): string
    {
        if (empty($this->items)) {
            return '';
        }

        return collect($this->getItemsWithProductNames())->map(function ($item) {
            $price = isset($item['item_price']) ? number_format($item['item_price'], 2) : '0.00';
            $quantity = $item['quantity'] ?? 1;
            $name = $item['display_name'] ?? 'Product Item';
            
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
        $summary .= "Total: {$this->formatted_final_amount}\n";
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
        try {
            // total_amount already includes tax and shipping, so we just need to subtract discount
            $totalAmount = floatval($this->total_amount ?? 0);
            $discountAmount = floatval($this->discount_amount ?? 0);
            $finalAmount = $totalAmount - $discountAmount;
            
            \Log::debug('getFinalAmount calculation', [
                'order_id' => $this->order_id ?? 'unknown',
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
            ]);
            
            return $finalAmount;
        } catch (\Exception $e) {
            \Log::error('Error in getFinalAmount', [
                'order_id' => $this->order_id ?? 'unknown',
                'error' => $e->getMessage(),
                'total_amount' => $this->total_amount ?? 0,
                'discount_amount' => $this->discount_amount ?? 0,
            ]);
            
            // Fallback to basic calculation
            return floatval($this->total_amount ?? 0) - floatval($this->discount_amount ?? 0);
        }
    }

    /**
     * Get subtotal (amount before tax and shipping)
     */
    public function getSubtotal(): float
    {
        return $this->total_amount - $this->tax_amount - $this->shipping_amount;
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

    /**
     * Get product name from catalog
     *
     * @param string $productRetailerId
     * @param int|null $vendorId
     * @return string
     */
    public function getProductName(string $productRetailerId, ?int $vendorId = null): string
    {
        $vendorId = $vendorId ?: $this->vendors__id;
        
        try {
            // Try to get from cache first
            $cacheKey = "whatsapp_product_{$productRetailerId}_{$vendorId}";
            $cachedName = cache()->get($cacheKey);
            
            if ($cachedName) {
                return $cachedName;
            }
            
            // Check if WhatsApp commerce is properly configured
            $commerceEnabled = getVendorSettings('enable_whatsapp_commerce', false, null, $vendorId);
            if (!$commerceEnabled) {
                \Log::debug('WhatsApp commerce not enabled for vendor', [
                    'vendor_id' => $vendorId,
                    'product_retailer_id' => $productRetailerId,
                ]);
                return "Product #{$productRetailerId}";
            }
            
            // Fetch from WhatsApp API
            $whatsAppApiService = app(\App\Yantrana\Components\WhatsAppService\Services\WhatsAppApiService::class);
            $productInfo = $whatsAppApiService->getCatalogProduct($productRetailerId, $vendorId);
            
            $productName = $productInfo['name'] ?? "Product #{$productRetailerId}";
            
            // Cache for 1 hour
            cache()->put($cacheKey, $productName, now()->addHour());
            
            return $productName;
            
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch product name from catalog', [
                'product_retailer_id' => $productRetailerId,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage(),
            ]);
            
            // Return a meaningful fallback name
            return "Product #{$productRetailerId}";
        }
    }

    /**
     * Get formatted items with resolved product names
     *
     * @return array
     */
    public function getItemsWithProductNames(): array
    {
        if (empty($this->items)) {
            return [];
        }
        
        $itemsWithNames = [];
        
        foreach ($this->items as $item) {
            $productRetailerId = $item['product_retailer_id'] ?? null;
            
            // Try multiple possible field names for product name
            $productName = $item['name'] ?? 
                          $item['title'] ?? 
                          $item['product_name'] ?? 
                          $item['display_name'] ?? 
                          $item['description'] ?? 
                          null;
            
            // If we have a product_retailer_id but no name, try to fetch it from catalog
            if ($productRetailerId && !$productName) {
                // Only try to fetch from catalog if WhatsApp commerce is enabled
                $commerceEnabled = getVendorSettings('enable_whatsapp_commerce', false, null, $this->vendors__id);
                if ($commerceEnabled) {
                    $productName = $this->getProductName($productRetailerId);
                } else {
                    // If commerce is not enabled, use a simple fallback
                    $productName = "Product #{$productRetailerId}";
                }
            }
            
            // If still no name, try to get from catalog data in __data
            if (!$productName && !empty($this->__data['catalog_data']['product_items'])) {
                foreach ($this->__data['catalog_data']['product_items'] as $catalogItem) {
                    if (($catalogItem['product_retailer_id'] ?? null) === $productRetailerId) {
                        $productName = $catalogItem['name'] ?? 
                                      $catalogItem['title'] ?? 
                                      $catalogItem['product_name'] ?? 
                                      $catalogItem['display_name'] ?? 
                                      $catalogItem['description'] ?? 
                                      null;
                        break;
                    }
                }
            }
            
            // Final fallback - use product_retailer_id or a generic name
            $displayName = $productName ?: 
                          ($productRetailerId ? "Product #{$productRetailerId}" : "Product Item");
            
            $itemsWithNames[] = array_merge($item, [
                'display_name' => $displayName,
            ]);
        }
        
        return $itemsWithNames;
    }

    /**
     * Debug method to analyze order data structure
     *
     * @return array
     */
    public function debugOrderData(): array
    {
        $debug = [
            'order_id' => $this->order_id,
            'items_count' => count($this->items ?? []),
            'items_structure' => [],
            'catalog_data_available' => !empty($this->__data['catalog_data']),
        ];

        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {
                $debug['items_structure'][$index] = [
                    'available_fields' => array_keys($item),
                    'product_retailer_id' => $item['product_retailer_id'] ?? null,
                    'name' => $item['name'] ?? null,
                    'title' => $item['title'] ?? null,
                    'product_name' => $item['product_name'] ?? null,
                    'display_name' => $item['display_name'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'item_price' => $item['item_price'] ?? null,
                    'price' => $item['price'] ?? null,
                ];
            }
        }

        if (!empty($this->__data['catalog_data']['product_items'])) {
            $debug['catalog_items'] = [];
            foreach ($this->__data['catalog_data']['product_items'] as $index => $catalogItem) {
                $debug['catalog_items'][$index] = [
                    'available_fields' => array_keys($catalogItem),
                    'product_retailer_id' => $catalogItem['product_retailer_id'] ?? null,
                    'name' => $catalogItem['name'] ?? null,
                    'title' => $catalogItem['title'] ?? null,
                    'product_name' => $catalogItem['product_name'] ?? null,
                    'display_name' => $catalogItem['display_name'] ?? null,
                    'description' => $catalogItem['description'] ?? null,
                ];
            }
        }

        return $debug;
    }

    /**
     * Get items summary for display in orders list
     *
     * @return string
     */
    public function getItemsSummary(): string
    {
        if (empty($this->items)) {
            return 'No items';
        }
        
        $itemsWithNames = $this->getItemsWithProductNames();
        $totalItems = count($itemsWithNames);
        
        if ($totalItems === 1) {
            $item = $itemsWithNames[0];
            $quantity = $item['quantity'] ?? 1;
            $name = $item['display_name'];
            return "1 item: {$name}" . ($quantity > 1 ? " (Qty: {$quantity})" : "");
        }
        
        if ($totalItems <= 3) {
            $itemNames = [];
            foreach ($itemsWithNames as $item) {
                $quantity = $item['quantity'] ?? 1;
                $name = $item['display_name'];
                $itemNames[] = $name . ($quantity > 1 ? " (Qty: {$quantity})" : "");
            }
            return implode(', ', $itemNames);
        }
        
        // For more than 3 items, show first 2 items + count
        $itemNames = [];
        for ($i = 0; $i < 2; $i++) {
            $item = $itemsWithNames[$i];
            $quantity = $item['quantity'] ?? 1;
            $name = $item['display_name'];
            $itemNames[] = $name . ($quantity > 1 ? " (Qty: {$quantity})" : "");
        }
        
        $remaining = $totalItems - 2;
        return implode(', ', $itemNames) . " + {$remaining} more";
    }
}
