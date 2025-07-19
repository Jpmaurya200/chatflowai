<?php
/**
 * WhatsAppOrderRepositoryInterface.php - Repository Interface file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Interfaces;

interface WhatsAppOrderRepositoryInterface
{
    /**
     * Fetch order by order ID and vendor
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByOrderId(string $orderId, ?int $vendorId = null);

    /**
     * Fetch orders by customer phone
     *
     * @param string $customerPhone
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByCustomerPhone(string $customerPhone, ?int $vendorId = null);

    /**
     * Fetch orders by status
     *
     * @param string $status
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByStatus(string $status, ?int $vendorId = null);

    /**
     * Fetch pending orders for vendor
     *
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchPendingOrders(?int $vendorId = null);

    /**
     * Fetch orders with pagination
     *
     * @param array $filters
     * @param int|null $vendorId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function fetchOrdersWithPagination(array $filters = [], ?int $vendorId = null);

    /**
     * Create new order
     *
     * @param array $orderData
     * @return object
     */
    public function createOrder(array $orderData);

    /**
     * Update order status
     *
     * @param string $orderId
     * @param string $status
     * @param array $additionalData
     * @param int|null $vendorId
     * @return bool
     */
    public function updateOrderStatus(string $orderId, string $status, array $additionalData = [], ?int $vendorId = null): bool;

    /**
     * Get order statistics for vendor
     *
     * @param int|null $vendorId
     * @return array
     */
    public function getOrderStatistics(?int $vendorId = null): array;

    /**
     * Generate unique order ID
     *
     * @param int|null $vendorId
     * @return string
     */
    public function generateOrderId(?int $vendorId = null): string;

    /**
     * Delete expired pending orders
     *
     * @param int $daysOld
     * @param int|null $vendorId
     * @return int
     */
    public function deleteExpiredPendingOrders(int $daysOld = 7, ?int $vendorId = null): int;
}
