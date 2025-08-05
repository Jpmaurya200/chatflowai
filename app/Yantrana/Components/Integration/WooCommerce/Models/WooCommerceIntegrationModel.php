<?php
/**
 * WooCommerceIntegrationModel.php - Model file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Models;

use App\Yantrana\Base\BaseModel;
use Carbon\Carbon;

class WooCommerceIntegrationModel extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'woocommerce_integrations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendors__id',
        'site_url',
        'consumer_key',
        'consumer_secret',
        'is_active',
        'connected_at',
        'disconnected_at',
        'last_sync_at',
        'webhook_ids',
        'settings',
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
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'webhook_ids' => 'array',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Check if integration is active
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->connected_at;
    }

    /**
     * Get formatted site URL
     */
    public function getFormattedSiteUrlAttribute(): string
    {
        return rtrim($this->site_url, '/');
    }

    /**
     * Get connection status
     */
    public function getConnectionStatusAttribute(): string
    {
        if ($this->isActive()) {
            return 'connected';
        }
        
        if ($this->disconnected_at) {
            return 'disconnected';
        }
        
        return 'never_connected';
    }

    /**
     * Get days since connection
     */
    public function getDaysSinceConnectionAttribute(): ?int
    {
        if (!$this->connected_at) {
            return null;
        }
        
        return $this->connected_at->diffInDays(Carbon::now());
    }

    /**
     * Get notification types
     */
    public function getNotificationTypes(): array
    {
        $settings = $this->settings ?? [];
        return $settings['notification_types'] ?? [];
    }

    /**
     * Set notification types
     */
    public function setNotificationTypes(array $types): void
    {
        $settings = $this->settings ?? [];
        $settings['notification_types'] = $types;
        $this->settings = $settings;
    }

    /**
     * Get webhook settings
     */
    public function getWebhookSettings(): array
    {
        $settings = $this->settings ?? [];
        return $settings['webhook_settings'] ?? [];
    }

    /**
     * Set webhook settings
     */
    public function setWebhookSettings(array $settings): void
    {
        $currentSettings = $this->settings ?? [];
        $currentSettings['webhook_settings'] = $settings;
        $this->settings = $currentSettings;
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings(): array
    {
        $settings = $this->settings ?? [];
        return $settings['notification_settings'] ?? [];
    }

    /**
     * Set notification settings
     */
    public function setNotificationSettings(array $settings): void
    {
        $currentSettings = $this->settings ?? [];
        $currentSettings['notification_settings'] = $settings;
        $this->settings = $currentSettings;
    }

    /**
     * Get site data
     */
    public function getSiteData(): array
    {
        $settings = $this->settings ?? [];
        return $settings['site_data'] ?? [];
    }

    /**
     * Set site data
     */
    public function setSiteData(array $data): void
    {
        $currentSettings = $this->settings ?? [];
        $currentSettings['site_data'] = $data;
        $this->settings = $currentSettings;
    }

    /**
     * Get credentials
     */
    public function getCredentials(): array
    {
        return [
            'consumer_key' => $this->consumer_key,
            'consumer_secret' => $this->consumer_secret,
        ];
    }

    /**
     * Set credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->consumer_key = $credentials['consumer_key'] ?? null;
        $this->consumer_secret = $credentials['consumer_secret'] ?? null;
    }

    /**
     * Get template mappings
     */
    public function getTemplateMappings(): array
    {
        $settings = $this->settings ?? [];
        return $settings['template_mappings'] ?? [];
    }

    /**
     * Set template mappings
     */
    public function setTemplateMappings(array $mappings): void
    {
        $currentSettings = $this->settings ?? [];
        $currentSettings['template_mappings'] = $mappings;
        $this->settings = $currentSettings;
    }

    /**
     * Get template UID for notification type
     */
    public function getTemplateUid(string $notificationType): ?string
    {
        $mappings = $this->getTemplateMappings();
        return $mappings[$notificationType] ?? null;
    }

    /**
     * Get variable mappings for notification type
     */
    public function getVariableMappings(string $notificationType): array
    {
        $settings = $this->settings ?? [];
        $variableMappings = $settings['variable_mappings'] ?? [];
        return $variableMappings[$notificationType] ?? [];
    }

    /**
     * Set variable mappings for notification type
     */
    public function setVariableMappings(string $notificationType, array $variables): void
    {
        $currentSettings = $this->settings ?? [];
        $variableMappings = $currentSettings['variable_mappings'] ?? [];
        $variableMappings[$notificationType] = $variables;
        $currentSettings['variable_mappings'] = $variableMappings;
        $this->settings = $currentSettings;
    }

    /**
     * Get available WooCommerce variables
     */
    public static function getAvailableWooCommerceVariables(): array
    {
        return [
            'order_id' => 'Order ID',
            'order_number' => 'Order Number',
            'order_status' => 'Order Status',
            'order_total' => 'Order Total',
            'order_currency' => 'Order Currency',
            'customer_name' => 'Customer Name',
            'customer_email' => 'Customer Email',
            'customer_phone' => 'Customer Phone',
            'billing_address' => 'Billing Address',
            'shipping_address' => 'Shipping Address',
            'payment_method' => 'Payment Method',
            'shipping_method' => 'Shipping Method',
            'order_date' => 'Order Date',
            'order_items' => 'Order Items',
            'site_name' => 'Site Name',
            'site_url' => 'Site URL',
        ];
    }
} 