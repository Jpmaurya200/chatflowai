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
} 