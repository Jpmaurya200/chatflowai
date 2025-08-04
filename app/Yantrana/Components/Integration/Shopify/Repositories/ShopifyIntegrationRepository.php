<?php
/**
 * ShopifyIntegrationRepository.php - Repository file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Integration\Shopify\Models\ShopifyIntegrationModel;

class ShopifyIntegrationRepository extends BaseRepository
{
    /**
     * @var ShopifyIntegrationModel - Shopify Integration Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(ShopifyIntegrationModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get integration by vendor ID
     */
    public function getByVendorId(int $vendorId): ?ShopifyIntegrationModel
    {
        return $this->model->where('vendors__id', $vendorId)->first();
    }

    /**
     * Get active integration by vendor ID
     */
    public function getActiveByVendorId(int $vendorId): ?ShopifyIntegrationModel
    {
        return $this->model->where('vendors__id', $vendorId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get integration by shop domain
     */
    public function getByShopDomain(string $shopDomain): ?ShopifyIntegrationModel
    {
        return $this->model->where('shop_domain', $shopDomain)->first();
    }

    /**
     * Create or update integration
     */
    public function createOrUpdate(array $data): ShopifyIntegrationModel
    {
        $integration = $this->getByVendorId($data['vendors__id']);

        if ($integration) {
            $integration->update($data);
            return $integration;
        }

        return $this->model->create($data);
    }

    /**
     * Update integration status
     */
    public function updateStatus(int $vendorId, bool $isActive, array $additionalData = []): bool
    {
        $data = array_merge([
            'is_active' => $isActive,
            'connected_at' => $isActive ? now() : null,
        ], $additionalData);

        return $this->model->where('vendors__id', $vendorId)->update($data);
    }

    /**
     * Update last sync time
     */
    public function updateLastSync(int $vendorId): bool
    {
        return $this->model->where('vendors__id', $vendorId)
            ->update(['last_sync_at' => now()]);
    }

    /**
     * Get all active integrations
     */
    public function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    /**
     * Get integrations with webhook configured
     */
    public function getWithWebhookConfigured(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)
            ->whereNotNull('webhook_id')
            ->get();
    }

    /**
     * Delete integration by vendor ID
     */
    public function deleteByVendorId(int $vendorId): bool
    {
        return $this->model->where('vendors__id', $vendorId)->delete();
    }

    /**
     * Get integration statistics
     */
    public function getStatistics(int $vendorId): array
    {
        $integration = $this->getByVendorId($vendorId);

        if (!$integration) {
            return [
                'connected' => false,
                'shop_domain' => null,
                'last_sync' => null,
                'webhook_configured' => false,
            ];
        }

        return [
            'connected' => $integration->isActive(),
            'shop_domain' => $integration->shop_domain,
            'last_sync' => $integration->last_sync_at,
            'webhook_configured' => !empty($integration->webhook_id),
            'notification_types' => $integration->getNotificationTypes(),
        ];
    }
} 