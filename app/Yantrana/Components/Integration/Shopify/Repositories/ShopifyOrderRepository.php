<?php
/**
 * ShopifyOrderRepository.php - Repository file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Integration\Shopify\Models\ShopifyOrderModel;

class ShopifyOrderRepository extends BaseRepository
{
    /**
     * @var ShopifyOrderModel - Shopify Order Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(ShopifyOrderModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get order by Shopify order ID
     */
    public function getByShopifyOrderId(string $shopifyOrderId, int $vendorId): ?ShopifyOrderModel
    {
        return $this->model->where('shopify_order_id', $shopifyOrderId)
            ->where('vendors__id', $vendorId)
            ->first();
    }

    /**
     * Get order by order number
     */
    public function getByOrderNumber(string $orderNumber, int $vendorId): ?ShopifyOrderModel
    {
        return $this->model->where('order_number', $orderNumber)
            ->where('vendors__id', $vendorId)
            ->first();
    }

    /**
     * Create or update order
     */
    public function createOrUpdate(array $data): ShopifyOrderModel
    {
        $order = $this->getByShopifyOrderId($data['shopify_order_id'], $data['vendors__id']);

        if ($order) {
            $order->update($data);
            return $order;
        }

        return $this->model->create($data);
    }

    /**
     * Get orders by vendor ID
     */
    public function getByVendorId(int $vendorId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders by status
     */
    public function getByStatus(int $vendorId, string $status, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders by financial status
     */
    public function getByFinancialStatus(int $vendorId, string $financialStatus, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->where('financial_status', $financialStatus)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders by fulfillment status
     */
    public function getByFulfillmentStatus(int $vendorId, string $fulfillmentStatus, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->where('fulfillment_status', $fulfillmentStatus)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders by customer phone
     */
    public function getByCustomerPhone(string $phone, int $vendorId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('phone', $phone)
            ->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get orders by customer email
     */
    public function getByCustomerEmail(string $email, int $vendorId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('email', $email)
            ->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $vendorId, int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        $date = now()->subDays($days);

        return $this->model->where('vendors__id', $vendorId)
            ->where('created_at', '>=', $date)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get order statistics
     */
    public function getOrderStatistics(int $vendorId): array
    {
        $totalOrders = $this->model->where('vendors__id', $vendorId)->count();
        $paidOrders = $this->model->where('vendors__id', $vendorId)
            ->whereIn('financial_status', ['paid', 'partially_paid'])
            ->count();
        $fulfilledOrders = $this->model->where('vendors__id', $vendorId)
            ->whereIn('fulfillment_status', ['fulfilled', 'partial'])
            ->count();
        $cancelledOrders = $this->model->where('vendors__id', $vendorId)
            ->where('status', 'cancelled')
            ->count();

        return [
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'fulfilled_orders' => $fulfilledOrders,
            'cancelled_orders' => $cancelledOrders,
            'payment_rate' => $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 2) : 0,
            'fulfillment_rate' => $totalOrders > 0 ? round(($fulfilledOrders / $totalOrders) * 100, 2) : 0,
        ];
    }

    /**
     * Delete order by Shopify order ID
     */
    public function deleteByShopifyOrderId(string $shopifyOrderId, int $vendorId): bool
    {
        return $this->model->where('shopify_order_id', $shopifyOrderId)
            ->where('vendors__id', $vendorId)
            ->delete();
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(string $shopifyOrderId, int $vendorId, string $status): bool
    {
        return $this->model->where('shopify_order_id', $shopifyOrderId)
            ->where('vendors__id', $vendorId)
            ->update(['status' => $status]);
    }

    /**
     * Update financial status
     */
    public function updateFinancialStatus(string $shopifyOrderId, int $vendorId, string $financialStatus): bool
    {
        return $this->model->where('shopify_order_id', $shopifyOrderId)
            ->where('vendors__id', $vendorId)
            ->update(['financial_status' => $financialStatus]);
    }

    /**
     * Update fulfillment status
     */
    public function updateFulfillmentStatus(string $shopifyOrderId, int $vendorId, string $fulfillmentStatus): bool
    {
        return $this->model->where('shopify_order_id', $shopifyOrderId)
            ->where('vendors__id', $vendorId)
            ->update(['fulfillment_status' => $fulfillmentStatus]);
    }
} 