<?php
/**
 * IntegrationEngineInterface.php - Interface file
 *
 * This file is part of the Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Interfaces;

interface IntegrationEngineInterface
{
    /**
     * Get available integrations
     */
    public function getAvailableIntegrations(): array;

    /**
     * Process integration webhook
     */
    public function processIntegrationWebhook(string $integration, $request, $vendorId = null);

    /**
     * Send order notification
     */
    public function sendOrderNotification(string $integration, array $orderData, string $notificationType, $vendorId = null);

    /**
     * Get integration status
     */
    public function getIntegrationStatus(string $integration, $vendorId = null): array;

    /**
     * Connect integration
     */
    public function connectIntegration(string $integration, array $credentials, $vendorId = null): array;

    /**
     * Disconnect integration
     */
    public function disconnectIntegration(string $integration, $vendorId = null): array;
} 