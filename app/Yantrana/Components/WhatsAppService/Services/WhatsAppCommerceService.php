<?php
/**
 * WhatsAppCommerceService.php - Service for WhatsApp Commerce operations
 *
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Services;

use App\Yantrana\Base\BaseEngine;
use Exception;
use Illuminate\Support\Arr;

class WhatsAppCommerceService extends BaseEngine
{
    protected $whatsAppApiService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->whatsAppApiService = app()->make(WhatsAppApiService::class);
    }

    /**
     * Fetch and store commerce settings
     *
     * @param int|null $vendorId
     * @return array
     */
    public function fetchCommerceSettings($vendorId = null)
    {
        try {
            $vendorId = $vendorId ?: getVendorId();
            
            // Fetch commerce settings from WhatsApp API
            $commerceSettings = $this->whatsAppApiService->getCommerceSettings(null, $vendorId);
            
            if ($commerceSettings) {
                // Store the settings in vendor settings
                $this->storeCommerceSettings($commerceSettings, $vendorId);
                
                // If catalog is available, fetch catalog info
                if (!empty($commerceSettings['catalog_id'])) {
                    $this->fetchAndStoreCatalogInfo($commerceSettings['catalog_id'], $vendorId);
                }
            }
            
            return $this->engineSuccessResponse([
                'commerce_settings' => $commerceSettings
            ], __tr('Commerce settings fetched successfully'));
            
        } catch (Exception $e) {
            return $this->engineFailedResponse(['show_message' => true], $e->getMessage());
        }
    }

    /**
     * Store commerce settings in vendor settings
     *
     * @param array $commerceSettings
     * @param int $vendorId
     * @return void
     */
    protected function storeCommerceSettings($commerceSettings, $vendorId)
    {
        $settingsToStore = [
            'whatsapp_commerce_settings' => $commerceSettings,
            'catalog_visibility' => $commerceSettings['is_catalog_visible'] ?? false,
            'cart_enabled' => $commerceSettings['is_cart_enabled'] ?? false,
        ];

        // Store settings using the vendor settings engine
        app()->make(\App\Yantrana\Components\Vendor\VendorSettingsEngine::class)
            ->updateProcess('whatsapp_commerce_setup', $settingsToStore, $vendorId);
    }

    /**
     * Fetch and store catalog information
     *
     * @param string $catalogId
     * @param int $vendorId
     * @return void
     */
    protected function fetchAndStoreCatalogInfo($catalogId, $vendorId)
    {
        try {
            $catalogInfo = $this->whatsAppApiService->getCatalogInfo($catalogId, $vendorId);
            
            if ($catalogInfo) {
                // Store catalog info
                app()->make(\App\Yantrana\Components\Vendor\VendorSettingsEngine::class)
                    ->updateProcess('whatsapp_commerce_setup', [
                        'whatsapp_catalog_info' => $catalogInfo
                    ], $vendorId);
            }
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            __logDebug('Failed to fetch catalog info: ' . $e->getMessage());
        }
    }

    /**
     * Update commerce settings
     *
     * @param array $settings
     * @param int|null $vendorId
     * @return array
     */
    public function updateCommerceSettings($settings, $vendorId = null)
    {
        try {
            $vendorId = $vendorId ?: getVendorId();
            
            // Prepare settings for WhatsApp API
            $apiSettings = [];
            if (isset($settings['catalog_visibility'])) {
                $apiSettings['is_catalog_visible'] = (bool) $settings['catalog_visibility'];
            }
            if (isset($settings['cart_enabled'])) {
                $apiSettings['is_cart_enabled'] = (bool) $settings['cart_enabled'];
            }
            
            if (empty($apiSettings)) {
                return $this->engineFailedResponse(['show_message' => true], __tr('No valid settings to update'));
            }
            
            // Update settings via WhatsApp API
            $result = $this->whatsAppApiService->updateCommerceSettings($apiSettings, null, $vendorId);
            
            if ($result) {
                // Store updated settings locally
                $this->storeCommerceSettings(array_merge(
                    getVendorSettings('whatsapp_commerce_settings', null, null, $vendorId) ?: [],
                    $apiSettings
                ), $vendorId);
                
                return $this->engineSuccessResponse([
                    'updated_settings' => $result
                ], __tr('Commerce settings updated successfully'));
            }
            
            return $this->engineFailedResponse(['show_message' => true], __tr('Failed to update commerce settings'));
            
        } catch (Exception $e) {
            return $this->engineFailedResponse(['show_message' => true], $e->getMessage());
        }
    }

    /**
     * Get catalog products with pagination
     *
     * @param array $options
     * @param int|null $vendorId
     * @return array
     */
    public function getCatalogProducts($options = [], $vendorId = null)
    {
        try {
            $vendorId = $vendorId ?: getVendorId();
            
            $products = $this->whatsAppApiService->getCatalogProducts(null, $options, $vendorId);
            
            return $this->engineSuccessResponse([
                'products' => $products
            ], __tr('Catalog products fetched successfully'));
            
        } catch (Exception $e) {
            return $this->engineFailedResponse(['show_message' => true], $e->getMessage());
        }
    }

    /**
     * Get commerce settings summary for display
     *
     * @param int|null $vendorId
     * @return array
     */
    public function getCommerceSettingsSummary($vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $commerceSettings = getVendorSettings('whatsapp_commerce_settings', null, null, $vendorId) ?: [];
        $catalogInfo = getVendorSettings('whatsapp_catalog_info', null, null, $vendorId) ?: [];
        
        return [
            'is_commerce_enabled' => getVendorSettings('enable_whatsapp_commerce', null, null, $vendorId),
            'is_catalog_visible' => $commerceSettings['is_catalog_visible'] ?? false,
            'is_cart_enabled' => $commerceSettings['is_cart_enabled'] ?? false,
            'catalog_id' => $commerceSettings['catalog_id'] ?? null,
            'catalog_name' => $catalogInfo['name'] ?? null,
            'product_count' => $catalogInfo['product_count'] ?? 0,
            'catalog_vertical' => $catalogInfo['vertical'] ?? null,
            'auto_sync_enabled' => getVendorSettings('auto_sync_commerce_settings', null, null, $vendorId),
            'last_sync' => $commerceSettings['last_updated'] ?? null,
        ];
    }

    /**
     * Check if commerce is properly configured
     *
     * @param int|null $vendorId
     * @return bool
     */
    public function isCommerceConfigured($vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $commerceSettings = getVendorSettings('whatsapp_commerce_settings', null, null, $vendorId);
        
        return !empty($commerceSettings) && !empty($commerceSettings['catalog_id']);
    }

    /**
     * Auto-sync commerce settings if enabled
     *
     * @param int|null $vendorId
     * @return array|null
     */
    public function autoSyncCommerceSettings($vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $autoSync = getVendorSettings('auto_sync_commerce_settings', null, null, $vendorId);
        
        if ($autoSync) {
            return $this->fetchCommerceSettings($vendorId);
        }
        
        return null;
    }
}
