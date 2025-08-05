<?php
/**
 * WooCommerceIntegrationRepository.php - Repository file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceIntegrationModel;

class WooCommerceIntegrationRepository extends BaseRepository
{
    /**
     * @var WooCommerceIntegrationModel - WooCommerce Integration Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(WooCommerceIntegrationModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get integration by vendor ID
     */
    public function getByVendorId($vendorId)
    {
        return $this->model->where('vendors__id', $vendorId)->first();
    }

    /**
     * Get active integrations
     */
    public function getActiveIntegrations()
    {
        return $this->model->where('is_active', true)->get();
    }

    /**
     * Get integration by site URL
     */
    public function getBySiteUrl($siteUrl)
    {
        return $this->model->where('site_url', $siteUrl)->first();
    }

    /**
     * Update integration settings
     */
    public function updateSettings($integrationId, array $settings)
    {
        return $this->update($integrationId, [
            'settings' => $settings,
            'updated_at' => now()
        ]);
    }

    /**
     * Update last sync time
     */
    public function updateLastSync($integrationId)
    {
        return $this->update($integrationId, [
            'last_sync_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get integrations with webhook IDs
     */
    public function getIntegrationsWithWebhooks()
    {
        return $this->model->whereNotNull('webhook_ids')->get();
    }

    /**
     * Add webhook ID to integration
     */
    public function addWebhookId($integrationId, $webhookId)
    {
        $integration = $this->find($integrationId);
        
        if ($integration) {
            $webhookIds = $integration->webhook_ids ?? [];
            $webhookIds[] = $webhookId;
            
            return $this->update($integrationId, [
                'webhook_ids' => array_unique($webhookIds),
                'updated_at' => now()
            ]);
        }
        
        return false;
    }

    /**
     * Remove webhook ID from integration
     */
    public function removeWebhookId($integrationId, $webhookId)
    {
        $integration = $this->find($integrationId);
        
        if ($integration) {
            $webhookIds = $integration->webhook_ids ?? [];
            $webhookIds = array_diff($webhookIds, [$webhookId]);
            
            return $this->update($integrationId, [
                'webhook_ids' => array_values($webhookIds),
                'updated_at' => now()
            ]);
        }
        
        return false;
    }

    /**
     * Create new integration
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update integration
     */
    public function update($id, array $data)
    {
        return $this->model->where('_id', $id)->update($data);
    }

    /**
     * Find integration by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get integration statistics
     */
    public function getIntegrationStatistics($vendorId = null)
    {
        $query = $this->model;
        
        if ($vendorId) {
            $query = $query->where('vendors__id', $vendorId);
        }
        
        return [
            'total_integrations' => $query->count(),
            'active_integrations' => $query->where('is_active', true)->count(),
            'inactive_integrations' => $query->where('is_active', false)->count(),
            'recent_connections' => $query->where('connected_at', '>=', now()->subDays(30))->count()
        ];
    }
} 