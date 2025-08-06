<?php
/**
 * IntegrationEngine.php - Base integration engine
 *
 * This file is part of the Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\WhatsAppService\WhatsAppServiceEngine;
use App\Yantrana\Components\Integration\Interfaces\IntegrationEngineInterface;
use App\Yantrana\Components\Integration\Shopify\Services\ShopifyService;
use App\Yantrana\Components\Integration\WooCommerce\Services\WooCommerceService;
use App\Yantrana\Components\Vendor\VendorSettingsEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Components\Configuration\ConfigurationEngine;
use Illuminate\Support\Facades\Log;

class IntegrationEngine extends BaseEngine implements IntegrationEngineInterface
{
    /**
     * @var WhatsAppServiceEngine - WhatsApp Service Engine
     */
    protected $whatsAppServiceEngine;

    /**
     * @var VendorSettingsEngine - Vendor Settings Engine
     */
    protected $vendorSettingsEngine;

    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var ConfigurationEngine - Configuration Engine
     */
    protected $configurationEngine;

    /**
     * @var ShopifyService - Shopify Service
     */
    protected $shopifyService;

    /**
     * @var WooCommerceService - WooCommerce Service
     */
    protected $wooCommerceService;

    /**
     * Constructor
     */
    public function __construct(
        WhatsAppServiceEngine $whatsAppServiceEngine,
        VendorSettingsEngine $vendorSettingsEngine,
        UserRepository $userRepository,
        ConfigurationEngine $configurationEngine,
        ShopifyService $shopifyService,
        WooCommerceService $wooCommerceService
    ) {
        $this->whatsAppServiceEngine = $whatsAppServiceEngine;
        $this->vendorSettingsEngine = $vendorSettingsEngine;
        $this->userRepository = $userRepository;
        $this->configurationEngine = $configurationEngine;
        $this->shopifyService = $shopifyService;
        $this->wooCommerceService = $wooCommerceService;
    }

    /**
     * Get available integrations
     */
    public function getAvailableIntegrations(): array
    {
        return [
            'shopify' => [
                'name' => 'Shopify',
                'description' => 'E-commerce platform integration for order notifications',
                'features' => [
                    'order_confirmation',
                    'payment_confirmation',
                    'shipment_tracking',
                    'delivery_confirmation',
                    'cod_verification'
                ],
                'webhook_events' => [
                    'orders/create',
                    'orders/updated',
                    'orders/paid',
                    'orders/fulfilled',
                    'orders/cancelled',
                    'refunds/create'
                ]
            ],
            'woocommerce' => [
                'name' => 'WooCommerce',
                'description' => 'WordPress e-commerce platform integration for order notifications',
                'features' => [
                    'order_confirmation',
                    'payment_confirmation',
                    'shipment_tracking',
                    'delivery_confirmation',
                    'cod_verification'
                ],
                'webhook_events' => [
                    'order.created',
                    'order.updated',
                    'order.deleted',
                    'order.restored',
                    'order.trashed'
                ]
            ]
        ];
    }

    /**
     * Process integration webhook
     */
    public function processIntegrationWebhook(string $integration, $request, $vendorId = null)
    {
        try {
            switch ($integration) {
                case 'shopify':
                    return $this->shopifyService->processWebhook($request, $vendorId);
                case 'woocommerce':
                    return $this->wooCommerceService->processWebhook($request, $vendorId);
                default:
                    throw new \Exception("Unsupported integration: {$integration}");
            }
        } catch (\Exception $e) {
            Log::error("Integration webhook processing failed", [
                'integration' => $integration,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send order notification
     */
    public function sendOrderNotification(string $integration, array $orderData, string $notificationType, $vendorId = null)
    {
        try {
            switch ($integration) {
                case 'shopify':
                    return $this->shopifyService->sendOrderNotification($orderData, $notificationType, $vendorId);
                case 'woocommerce':
                    return $this->wooCommerceService->sendOrderNotification($orderData, $notificationType, $vendorId);
                default:
                    throw new \Exception("Unsupported integration: {$integration}");
            }
        } catch (\Exception $e) {
            Log::error("Order notification sending failed", [
                'integration' => $integration,
                'notification_type' => $notificationType,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get integration status
     */
    public function getIntegrationStatus(string $integration, $vendorId = null): array
    {
        try {
            switch ($integration) {
                case 'shopify':
                    return $this->shopifyService->getIntegrationStatus($vendorId);
                case 'woocommerce':
                    return $this->wooCommerceService->getIntegrationStatus($vendorId);
                default:
                    throw new \Exception("Unsupported integration: {$integration}");
            }
        } catch (\Exception $e) {
            Log::error("Integration status check failed", [
                'integration' => $integration,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Connect integration
     */
    public function connectIntegration(string $integration, array $credentials, $vendorId = null): array
    {
        try {
            switch ($integration) {
                case 'shopify':
                    return $this->shopifyService->connect($credentials, $vendorId);
                case 'woocommerce':
                    return $this->wooCommerceService->connect($credentials, $vendorId);
                default:
                    throw new \Exception("Unsupported integration: {$integration}");
            }
        } catch (\Exception $e) {
            Log::error("Integration connection failed", [
                'integration' => $integration,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Disconnect integration
     */
    public function disconnectIntegration(string $integration, $vendorId = null): array
    {
        try {
            switch ($integration) {
                case 'shopify':
                    return $this->shopifyService->disconnect($vendorId);
                case 'woocommerce':
                    return $this->wooCommerceService->disconnect($vendorId);
                default:
                    throw new \Exception("Unsupported integration: {$integration}");
            }
        } catch (\Exception $e) {
            Log::error("Integration disconnection failed", [
                'integration' => $integration,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get Shopify service
     */
    public function getShopifyService()
    {
        return $this->shopifyService;
    }

    /**
     * Get WooCommerce service
     */
    public function getWooCommerceService()
    {
        return $this->wooCommerceService;
    }
} 