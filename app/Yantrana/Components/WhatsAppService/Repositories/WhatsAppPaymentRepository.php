<?php
/**
 * WhatsAppPaymentRepository.php - Repository file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\WhatsAppService\Models\WhatsAppPaymentModel;
use App\Yantrana\Components\WhatsAppService\Interfaces\WhatsAppPaymentRepositoryInterface;

class WhatsAppPaymentRepository extends BaseRepository implements WhatsAppPaymentRepositoryInterface
{
    /**
     * Repository related to primary model
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $primaryModel = WhatsAppPaymentModel::class;

    /**
     * Fetch payment by payment ID
     *
     * @param string $paymentId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByPaymentId(string $paymentId, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('payment_id', $paymentId)
            ->where('vendors__id', $vendorId)
            ->first();
    }

    /**
     * Fetch payments by order ID
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByOrderId(string $orderId, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('order_id', $orderId)
            ->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Fetch payment by transaction ID
     *
     * @param string $transactionId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByTransactionId(string $transactionId, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('transaction_id', $transactionId)
            ->where('vendors__id', $vendorId)
            ->first();
    }

    /**
     * Fetch payment by payment link ID
     *
     * @param string $paymentLinkId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByPaymentLinkId(string $paymentLinkId, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('payment_link_id', $paymentLinkId)
            ->where('vendors__id', $vendorId)
            ->first();
    }

    /**
     * Fetch payments by status
     *
     * @param string $status
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByStatus(string $status, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('status', $status)
            ->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create new payment record
     *
     * @param array $paymentData
     * @return object
     */
    public function createPayment(array $paymentData)
    {
        $paymentData['vendors__id'] = $paymentData['vendors__id'] ?? getVendorId();
        $paymentData['payment_initiated_at'] = now();
        
        return $this->storeIt($paymentData);
    }

    /**
     * Update payment status
     *
     * @param string $paymentId
     * @param string $status
     * @param array $additionalData
     * @param int|null $vendorId
     * @return bool
     */
    public function updatePaymentStatus(string $paymentId, string $status, array $additionalData = [], ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $updateData = array_merge(['status' => $status], $additionalData);
        
        // Set timestamp based on status
        switch ($status) {
            case 'completed':
                $updateData['payment_completed_at'] = now();
                break;
            case 'failed':
            case 'cancelled':
                $updateData['payment_failed_at'] = now();
                break;
        }

        return $this->updateIt([
            'payment_id' => $paymentId,
            'vendors__id' => $vendorId
        ], $updateData);
    }

    /**
     * Update payment by transaction ID
     *
     * @param string $transactionId
     * @param array $updateData
     * @param int|null $vendorId
     * @return bool
     */
    public function updateByTransactionId(string $transactionId, array $updateData, ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->updateIt([
            'transaction_id' => $transactionId,
            'vendors__id' => $vendorId
        ], $updateData);
    }

    /**
     * Get payment statistics for vendor
     *
     * @param int|null $vendorId
     * @return array
     */
    public function getPaymentStatistics(?int $vendorId = null): array
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $baseQuery = $this->primaryModel::where('vendors__id', $vendorId);
        
        return [
            'total_payments' => (clone $baseQuery)->count(),
            'successful_payments' => (clone $baseQuery)->where('status', 'completed')->count(),
            'failed_payments' => (clone $baseQuery)->whereIn('status', ['failed', 'cancelled'])->count(),
            'pending_payments' => (clone $baseQuery)->whereIn('status', ['pending', 'processing'])->count(),
            'total_amount' => (clone $baseQuery)->where('status', 'completed')->sum('amount'),
            'pending_amount' => (clone $baseQuery)->whereIn('status', ['pending', 'processing'])->sum('amount'),
            'refunded_amount' => (clone $baseQuery)->whereIn('status', ['refunded', 'partially_refunded'])->sum('amount'),
        ];
    }

    /**
     * Get payments with pagination
     *
     * @param array $filters
     * @param int|null $vendorId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function fetchPaymentsWithPagination(array $filters = [], ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $query = $this->primaryModel::where('vendors__id', $vendorId)
            ->with(['order']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['order_id'])) {
            $query->where('order_id', 'like', '%' . $filters['order_id'] . '%');
        }

        if (!empty($filters['payment_id'])) {
            $query->where('payment_id', 'like', '%' . $filters['payment_id'] . '%');
        }

        if (!empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get pending payments older than specified minutes
     *
     * @param int $minutes
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchExpiredPendingPayments(int $minutes = 30, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->whereIn('status', ['pending', 'processing'])
            ->where('payment_initiated_at', '<', now()->subMinutes($minutes))
            ->get();
    }

    /**
     * Mark expired payments as failed
     *
     * @param int $minutes
     * @param int|null $vendorId
     * @return int
     */
    public function markExpiredPaymentsAsFailed(int $minutes = 30, ?int $vendorId = null): int
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->whereIn('status', ['pending', 'processing'])
            ->where('payment_initiated_at', '<', now()->subMinutes($minutes))
            ->update([
                'status' => 'failed',
                'payment_failed_at' => now(),
            ]);
    }

    /**
     * Get successful payments for date range
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchSuccessfulPaymentsByDateRange(string $dateFrom, string $dateTo, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->where('status', 'completed')
            ->whereDate('payment_completed_at', '>=', $dateFrom)
            ->whereDate('payment_completed_at', '<=', $dateTo)
            ->orderBy('payment_completed_at', 'desc')
            ->get();
    }
}
