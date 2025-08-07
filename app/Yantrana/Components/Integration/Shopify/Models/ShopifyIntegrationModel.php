<?php
/**
 * ShopifyIntegrationModel.php - Model file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Vendor\Models\VendorModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyIntegrationModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'shopify_integrations';

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'webhook_settings' => 'array',
            'notification_settings' => 'array',
            'shop_data' => 'array',
            'credentials' => 'array:encrypted',
            'metadata' => 'array:extend',
            'template_mappings' => 'array',
        ],
    ];

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
        '_uid',
        'vendors__id',
        'shop_domain',
        'access_token',
        'webhook_id',
        'is_active',
        'notification_types',
        'webhook_url',
        'connected_at',
        'last_sync_at',
        '__data',
    ];

    protected $appends = [
        'status_label',
        'connection_status',
    ];

    /**
     * Get the vendor that owns the integration
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendors__id', '_id');
    }

    /**
     * Get status label
     */
    protected function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Disconnected';
        }

        if (!$this->connected_at) {
            return 'Not Connected';
        }

        return 'Connected';
    }

    /**
     * Get connection status
     */
    protected function getConnectionStatusAttribute(): array
    {
        return [
            'connected' => $this->is_active && $this->connected_at,
            'shop_domain' => $this->shop_domain,
            'last_sync' => $this->last_sync_at,
            'webhook_configured' => !empty($this->webhook_id),
        ];
    }

    /**
     * Check if integration is active
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->connected_at;
    }

    /**
     * Get notification types as array
     */
    public function getNotificationTypes(): array
    {
        if (empty($this->notification_types)) {
            return [];
        }

        return is_array($this->notification_types) 
            ? $this->notification_types 
            : explode(',', $this->notification_types);
    }

    /**
     * Set notification types
     */
    public function setNotificationTypes(array $types): void
    {
        $this->notification_types = implode(',', $types);
    }

    /**
     * Get webhook settings
     */
    public function getWebhookSettings(): array
    {
        return $this->__data['webhook_settings'] ?? [];
    }

    /**
     * Set webhook settings
     */
    public function setWebhookSettings(array $settings): void
    {
        $data = $this->__data ?? [];
        $data['webhook_settings'] = $settings;
        $this->__data = $data;
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings(): array
    {
        return $this->__data['notification_settings'] ?? [];
    }

    /**
     * Set notification settings
     */
    public function setNotificationSettings(array $settings): void
    {
        $data = $this->__data ?? [];
        $data['notification_settings'] = $settings;
        $this->__data = $data;
    }

    /**
     * Get shop data
     */
    public function getShopData(): array
    {
        return $this->__data['shop_data'] ?? [];
    }

    /**
     * Set shop data
     */
    public function setShopData(array $data): void
    {
        $currentData = $this->__data ?? [];
        $currentData['shop_data'] = $data;
        $this->__data = $currentData;
    }

    /**
     * Get credentials
     */
    public function getCredentials(): array
    {
        return $this->__data['credentials'] ?? [];
    }

    /**
     * Set credentials
     */
    public function setCredentials(array $credentials): void
    {
        $data = $this->__data ?? [];
        $data['credentials'] = $credentials;
        $this->__data = $data;
    }

    /**
     * Get template mappings
     */
    public function getTemplateMappings(): array
    {
        return $this->__data['template_mappings'] ?? [];
    }

    /**
     * Set template mappings
     */
    public function setTemplateMappings(array $mappings): void
    {
        $data = $this->__data ?? [];
        $data['template_mappings'] = $mappings;
        $this->__data = $data;
    }

    /**
     * Get template UID for a specific notification type
     */
    public function getTemplateUid(string $notificationType): ?string
    {
        $mappings = $this->getTemplateMappings();
        return $mappings[$notificationType] ?? null;
    }

    /**
     * Get variable mappings for a specific notification type
     */
    public function getVariableMappings(string $notificationType): array
    {
        $mappings = $this->getTemplateMappings();
        return $mappings[$notificationType . '_variables'] ?? [];
    }

    /**
     * Set variable mappings for a specific notification type
     */
    public function setVariableMappings(string $notificationType, array $variables): void
    {
        $mappings = $this->getTemplateMappings();
        $mappings[$notificationType . '_variables'] = $variables;
        $this->setTemplateMappings($mappings);
    }

    /**
     * Get all available Shopify order variables
     */
    public static function getAvailableShopifyVariables(): array
    {
        return [
            // Order Information
            'order_number' => 'Order Number',
            'order_name' => 'Order Name',
            'order_id' => 'Order ID',
            'order_total' => 'Order Total',
            'order_status' => 'Order Status',
            'order_date' => 'Order Date',
            'total_price' => 'Total Price',
            'subtotal_price' => 'Subtotal Price',
            'total_tax' => 'Total Tax',
            'total_discounts' => 'Total Discounts',
            'currency' => 'Currency',
            'financial_status' => 'Payment Status',
            'fulfillment_status' => 'Fulfillment Status',
            'processed_at' => 'Processed Date',
            
            // Customer Information
            'customer_name' => 'Customer Name',
            'customer_email' => 'Customer Email',
            'customer_phone' => 'Customer Phone',
            'customer_first_name' => 'Customer First Name',
            'customer_last_name' => 'Customer Last Name',
            
            // Address Information
            'shipping_address' => 'Shipping Address',
            'shipping_address_name' => 'Shipping Address Name',
            'shipping_address_company' => 'Shipping Company',
            'shipping_address_address1' => 'Shipping Address Line 1',
            'shipping_address_address2' => 'Shipping Address Line 2',
            'shipping_address_city' => 'Shipping City',
            'shipping_address_province' => 'Shipping Province/State',
            'shipping_address_country' => 'Shipping Country',
            'shipping_address_zip' => 'Shipping ZIP/Postal Code',
            'shipping_address_phone' => 'Shipping Phone',
            
            'billing_address' => 'Billing Address',
            'billing_address_name' => 'Billing Address Name',
            'billing_address_company' => 'Billing Company',
            'billing_address_address1' => 'Billing Address Line 1',
            'billing_address_address2' => 'Billing Address Line 2',
            'billing_address_city' => 'Billing City',
            'billing_address_province' => 'Billing Province/State',
            'billing_address_country' => 'Billing Country',
            'billing_address_zip' => 'Billing ZIP/Postal Code',
            'billing_address_phone' => 'Billing Phone',
            
            // Payment Information
            'payment_method' => 'Payment Method',
            'payment_status' => 'Payment Status',
            'cod_amount' => 'COD Amount',
            
            // Delivery Information
            'delivery_date' => 'Delivery Date',
            'tracking_number' => 'Tracking Number',
            'tracking_company' => 'Shipping Company',
            'tracking_url' => 'Tracking URL',
            'fulfillment_date' => 'Fulfillment Date',
            
            // Line Items
            'line_items_summary' => 'Order Items Summary',
            'total_items' => 'Total Items Count',
            'total_weight' => 'Total Weight',
            
            // Additional
            'note' => 'Order Note',
            'tags' => 'Order Tags',
            'shop_domain' => 'Shop Domain',
        ];
    }
} 