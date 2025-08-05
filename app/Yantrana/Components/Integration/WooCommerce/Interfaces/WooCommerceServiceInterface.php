<?php
/**
 * WooCommerceServiceInterface.php - Interface file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Interfaces;

interface WooCommerceServiceInterface
{
    /**
     * Connect WooCommerce integration
     */
    public function connect(array $credentials, $vendorId = null): array;

    /**
     * Disconnect WooCommerce integration
     */
    public function disconnect($vendorId = null): array;

    /**
     * Get integration status
     */
    public function getIntegrationStatus($vendorId = null): array;

    /**
     * Process webhook
     */
    public function processWebhook($request, $vendorId = null);

    /**
     * Send order notification
     */
    public function sendOrderNotification(array $orderData, string $notificationType, $vendorId = null);

    /**
     * Validate WooCommerce credentials
     */
    public function validateWooCommerceCredentials(string $siteUrl, string $consumerKey, string $consumerSecret): array;

    /**
     * Setup webhooks
     */
    public function setupWebhooks($integration): void;

    /**
     * Remove webhooks
     */
    public function removeWebhooks($integration): void;

    /**
     * Create webhook
     */
    public function createWebhook($integration, string $topic): void;

    /**
     * Delete webhook
     */
    public function deleteWebhook($integration, int $webhookId): void;

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($request, $integration): bool;
} 