<?php
/**
 * WhatsAppOrderRepository.php - Repository file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\WhatsAppService\Models\WhatsAppOrderModel;
use App\Yantrana\Components\WhatsAppService\Interfaces\WhatsAppOrderRepositoryInterface;
use Illuminate\Support\Str;

class WhatsAppOrderRepository extends BaseRepository implements WhatsAppOrderRepositoryInterface
{
    /**
     * Repository related to primary model
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $primaryModel = WhatsAppOrderModel::class;

    /**
     * Fetch order by order ID and vendor
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByOrderId(string $orderId, ?int $vendorId = null)
    {
        $query = $this->primaryModel::where('order_id', $orderId);
        
        // Only filter by vendor ID if provided
        if ($vendorId !== null) {
            $query->where('vendors__id', $vendorId);
        }
        
        return $query->first();
    }

    /**
     * Fetch orders by customer phone
     *
     * @param string $customerPhone
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByCustomerPhone(string $customerPhone, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('customer_phone', $customerPhone)
            ->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Fetch orders by status
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
     * Fetch pending orders for vendor
     *
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchPendingOrders(?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::whereIn('status', ['pending', 'awaiting_address', 'awaiting_payment'])
            ->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Fetch orders with pagination
     *
     * @param array $filters
     * @param int|null $vendorId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function fetchOrdersWithPagination(array $filters = [], ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $query = $this->primaryModel::where('vendors__id', $vendorId)
            ->with(['contact', 'payments']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_phone'])) {
            $query->where('customer_phone', 'like', '%' . $filters['customer_phone'] . '%');
        }

        if (!empty($filters['order_id'])) {
            $query->where('order_id', 'like', '%' . $filters['order_id'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);

        // Add items summary to each order and debug formatted_final_amount
        $orders->getCollection()->transform(function ($order) {
            $order->items_summary = $order->getItemsSummary();
            
            // Manually add formatted_final_amount to ensure it's included in JSON response
            $order->formatted_final_amount = $order->formatted_final_amount;
            
            return $order;
        });

        return $orders;
    }

    /**
     * Create new order
     *
     * @param array $orderData
     * @return object
     */
    public function createOrder(array $orderData)
    {
        $orderData['vendors__id'] = $orderData['vendors__id'] ?? getVendorId();
        $orderData['ordered_at'] = now();
        
        return $this->storeIt($orderData);
    }

    /**
     * Update order status
     *
     * @param string $orderId
     * @param string $status
     * @param array $additionalData
     * @param int|null $vendorId
     * @return bool
     */
    public function updateOrderStatus(string $orderId, string $status, array $additionalData = [], ?int $vendorId = null): bool
    {
        // First find the order to get its vendor ID if not provided
        if ($vendorId === null) {
            $order = $this->fetchByOrderId($orderId);
            if (!$order) {
                return false;
            }
            $vendorId = $order->vendors__id;
        }
        
        $updateData = array_merge(['status' => $status], $additionalData);
        
        // Set timestamp based on status
        switch ($status) {
            case 'paid':
                $updateData['payment_completed_at'] = now();
                break;
            case 'shipped':
                $updateData['shipped_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
        }

        return $this->updateIt([
            'order_id' => $orderId,
            'vendors__id' => $vendorId
        ], $updateData);
    }

    /**
     * Get order statistics for vendor
     *
     * @param int|null $vendorId
     * @return array
     */
    public function getOrderStatistics(?int $vendorId = null): array
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $baseQuery = $this->primaryModel::where('vendors__id', $vendorId);
        
        return [
            'total_orders' => (clone $baseQuery)->count(),
            'pending_orders' => (clone $baseQuery)->whereIn('status', ['pending', 'awaiting_address', 'awaiting_payment'])->count(),
            'completed_orders' => (clone $baseQuery)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'total_revenue' => (clone $baseQuery)->where('status', 'delivered')->sum('total_amount'),
            'pending_revenue' => (clone $baseQuery)->whereIn('status', ['paid', 'confirmed', 'shipped'])->sum('total_amount'),
        ];
    }

    /**
     * Generate unique order ID
     *
     * @param int|null $vendorId
     * @return string
     */
    public function generateOrderId(?int $vendorId = null): string
    {
        $vendorId = $vendorId ?: getVendorId();

        do {
            $orderId = 'ORD' . now()->format('YmdHis') . strtoupper(Str::random(5));
        } while ($this->primaryModel::where('order_id', $orderId)->where('vendors__id', $vendorId)->exists());

        return $orderId;
    }

    /**
     * Delete expired pending orders
     *
     * @param int $daysOld
     * @param int|null $vendorId
     * @return int
     */
    public function deleteExpiredPendingOrders(int $daysOld = 7, ?int $vendorId = null): int
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
