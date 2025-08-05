<?php
/**
 * WooCommerceOrderRepository.php - Repository file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceOrderModel;

class WooCommerceOrderRepository extends BaseRepository
{
    /**
     * @var WooCommerceOrderModel - WooCommerce Order Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(WooCommerceOrderModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get order by WooCommerce ID
     */
    public function getByWooCommerceId($wooCommerceOrderId, $vendorId)
    {
        return $this->model->where('woocommerce_order_id', $wooCommerceOrderId)
                          ->where('vendors__id', $vendorId)
                          ->first();
    }

    /**
     * Get orders by vendor ID
     */
    public function getByVendorId($vendorId, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get orders by status
     */
    public function getByStatus($vendorId, $status, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('status', $status)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders($vendorId, $days = 7, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('created_at', '>=', now()->subDays($days))
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get orders by date range
     */
    public function getByDateRange($vendorId, $startDate, $endDate, $limit = 50)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get order statistics
     */
    public function getOrderStatistics($vendorId)
    {
        $totalOrders = $this->model->where('vendors__id', $vendorId)->count();
        $paidOrders = $this->model->where('vendors__id', $vendorId)
                                 ->whereIn('status', ['processing', 'completed'])
                                 ->count();
        $fulfilledOrders = $this->model->where('vendors__id', $vendorId)
                                      ->where('status', 'completed')
                                      ->count();
        $pendingOrders = $this->model->where('vendors__id', $vendorId)
                                    ->where('status', 'pending')
                                    ->count();

        return [
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'fulfilled_orders' => $fulfilledOrders,
            'pending_orders' => $pendingOrders
        ];
    }

    /**
     * Get orders with notifications
     */
    public function getOrdersWithNotifications($vendorId, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->whereHas('notifications')
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Search orders
     */
    public function searchOrders($vendorId, $searchTerm, $limit = 20)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where(function($query) use ($searchTerm) {
                              $query->where('order_number', 'like', "%{$searchTerm}%")
                                    ->orWhere('customer_data.first_name', 'like', "%{$searchTerm}%")
                                    ->orWhere('customer_data.last_name', 'like', "%{$searchTerm}%")
                                    ->orWhere('customer_data.email', 'like', "%{$searchTerm}%");
                          })
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get orders by customer
     */
    public function getOrdersByCustomer($vendorId, $customerEmail, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('customer_data.email', $customerEmail)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get orders with specific statuses
     */
    public function getOrdersByStatuses($vendorId, array $statuses, $limit = 20)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->whereIn('status', $statuses)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue($vendorId, $startDate = null, $endDate = null)
    {
        $query = $this->model->where('vendors__id', $vendorId)
                            ->whereIn('status', ['processing', 'completed']);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->sum('total');
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue($vendorId)
    {
        $orders = $this->model->where('vendors__id', $vendorId)->get();
        
        if ($orders->count() === 0) {
            return 0;
        }

        return $orders->sum('total') / $orders->count();
    }

    /**
     * Get orders count by status
     */
    public function getOrdersCountByStatus($vendorId)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->selectRaw('status, count(*) as count')
                          ->groupBy('status')
                          ->get()
                          ->pluck('count', 'status')
                          ->toArray();
    }

    /**
     * Create new order
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update order
     */
    public function update($id, array $data)
    {
        return $this->model->where('_id', $id)->update($data);
    }

    /**
     * Find order by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get orders by month
     */
    public function getOrdersByMonth($vendorId, $year = null)
    {
        $year = $year ?? date('Y');
        
        return $this->model->where('vendors__id', $vendorId)
                          ->whereYear('created_at', $year)
                          ->selectRaw('MONTH(created_at) as month, count(*) as count, sum(total) as revenue')
                          ->groupBy('month')
                          ->orderBy('month')
                          ->get();
    }
} 