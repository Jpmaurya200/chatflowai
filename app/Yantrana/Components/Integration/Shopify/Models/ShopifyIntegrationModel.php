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
} 