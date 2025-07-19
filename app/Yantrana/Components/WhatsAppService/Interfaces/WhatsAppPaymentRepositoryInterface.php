<?php
/**
 * WhatsAppPaymentRepositoryInterface.php - Repository Interface file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Interfaces;

interface WhatsAppPaymentRepositoryInterface
{
    /**
     * Fetch payment by payment ID
     *
     * @param string $paymentId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByPaymentId(string $paymentId, ?int $vendorId = null);

    /**
     * Fetch payments by order ID
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByOrderId(string $orderId, ?int $vendorId = null);

    /**
     * Fetch payment by transaction ID
     *
     * @param string $transactionId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByTransactionId(string $transactionId, ?int $vendorId = null);

    /**
     * Fetch payment by payment link ID
     *
     * @param string $paymentLinkId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByPaymentLinkId(string $paymentLinkId, ?int $vendorId = null);

    /**
     * Fetch payments by status
     *
     * @param string $status
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByStatus(string $status, ?int $vendorId = null);

    /**
     * Create new payment record
     *
     * @param array $paymentData
     * @return object
     */
    public function createPayment(array $paymentData);

    /**
     * Update payment status
     *
     * @param string $paymentId
     * @param string $status
     * @param array $additionalData
     * @param int|null $vendorId
     * @return bool
     */
    public function updatePaymentStatus(string $paymentId, string $status, array $additionalData = [], ?int $vendorId = null): bool;

    /**
     * Update payment by transaction ID
     *
     * @param string $transactionId
     * @param array $updateData
     * @param int|null $vendorId
     * @return bool
     */
    public function updateByTransactionId(string $transactionId, array $updateData, ?int $vendorId = null): bool;

    /**
     * Get payment statistics for vendor
     *
     * @param int|null $vendorId
     * @return array
     */
    public function getPaymentStatistics(?int $vendorId = null): array;

    /**
     * Get payments with pagination
     *
     * @param array $filters
     * @param int|null $vendorId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function fetchPaymentsWithPagination(array $filters = [], ?int $vendorId = null);

    /**
     * Get pending payments older than specified minutes
     *
     * @param int $minutes
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchExpiredPendingPayments(int $minutes = 30, ?int $vendorId = null);

    /**
     * Mark expired payments as failed
     *
     * @param int $minutes
     * @param int|null $vendorId
     * @return int
     */
    public function markExpiredPaymentsAsFailed(int $minutes = 30, ?int $vendorId = null): int;

    /**
     * Get successful payments for date range
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchSuccessfulPaymentsByDateRange(string $dateFrom, string $dateTo, ?int $vendorId = null);
}
