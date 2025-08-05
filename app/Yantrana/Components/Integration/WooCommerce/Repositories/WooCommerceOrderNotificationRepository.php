<?php
/**
 * WooCommerceOrderNotificationRepository.php - Repository file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Integration\WooCommerce\Models\WooCommerceOrderNotificationModel;

class WooCommerceOrderNotificationRepository extends BaseRepository
{
    /**
     * @var WooCommerceOrderNotificationModel - WooCommerce Order Notification Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(WooCommerceOrderNotificationModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get notifications by vendor ID
     */
    public function getByVendorId($vendorId, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notifications by order ID
     */
    public function getByOrderId($orderId, $limit = 10)
    {
        return $this->model->where('woocommerce_orders__id', $orderId)
                          ->with(['contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notifications by type
     */
    public function getByType($vendorId, $notificationType, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('notification_type', $notificationType)
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notifications by status
     */
    public function getByStatus($vendorId, $status, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('status', $status)
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get failed notifications
     */
    public function getFailedNotifications($vendorId, $limit = 20)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('status', 'failed')
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get recent notifications
     */
    public function getRecentNotifications($vendorId, $days = 7, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('created_at', '>=', now()->subDays($days))
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStatistics($vendorId)
    {
        $totalNotifications = $this->model->where('vendors__id', $vendorId)->count();
        $sentNotifications = $this->model->where('vendors__id', $vendorId)
                                       ->whereIn('status', ['sent', 'delivered', 'read'])
                                       ->count();
        $failedNotifications = $this->model->where('vendors__id', $vendorId)
                                         ->where('status', 'failed')
                                         ->count();
        $pendingNotifications = $this->model->where('vendors__id', $vendorId)
                                          ->where('status', 'pending')
                                          ->count();

        return [
            'total_notifications' => $totalNotifications,
            'sent_notifications' => $sentNotifications,
            'failed_notifications' => $failedNotifications,
            'pending_notifications' => $pendingNotifications
        ];
    }

    /**
     * Get notifications by date range
     */
    public function getByDateRange($vendorId, $startDate, $endDate, $limit = 50)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notifications by contact
     */
    public function getByContact($vendorId, $contactId, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('contacts__id', $contactId)
                          ->with(['order', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notifications by template
     */
    public function getByTemplate($vendorId, $templateId, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('whatsapp_templates__id', $templateId)
                          ->with(['order', 'contact'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Update notification status
     */
    public function updateStatus($notificationId, $status, $response = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => now()
        ];

        if ($status === 'sent') {
            $data['sent_at'] = now();
        }

        if ($response) {
            $data['response'] = $response;
        }

        return $this->update($notificationId, $data);
    }

    /**
     * Increment retry count
     */
    public function incrementRetryCount($notificationId)
    {
        $notification = $this->find($notificationId);
        
        if ($notification) {
            return $this->update($notificationId, [
                'retry_count' => ($notification->retry_count ?? 0) + 1,
                'updated_at' => now()
            ]);
        }
        
        return false;
    }

    /**
     * Get notifications that can be retried
     */
    public function getRetryableNotifications($vendorId, $limit = 20)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('status', 'failed')
                          ->where('retry_count', '<', 3)
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notification success rate
     */
    public function getSuccessRate($vendorId, $days = 30)
    {
        $total = $this->model->where('vendors__id', $vendorId)
                            ->where('created_at', '>=', now()->subDays($days))
                            ->count();

        $successful = $this->model->where('vendors__id', $vendorId)
                                ->where('created_at', '>=', now()->subDays($days))
                                ->whereIn('status', ['sent', 'delivered', 'read'])
                                ->count();

        if ($total === 0) {
            return 0;
        }

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get notifications by type and status
     */
    public function getByTypeAndStatus($vendorId, $notificationType, $status, $limit = 10)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->where('notification_type', $notificationType)
                          ->where('status', $status)
                          ->with(['order', 'contact', 'template'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get notification count by type
     */
    public function getCountByType($vendorId)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->selectRaw('notification_type, count(*) as count')
                          ->groupBy('notification_type')
                          ->get()
                          ->pluck('count', 'notification_type')
                          ->toArray();
    }

    /**
     * Create new notification
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update notification
     */
    public function update($id, array $data)
    {
        return $this->model->where('_id', $id)->update($data);
    }

    /**
     * Find notification by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get notification count by status
     */
    public function getCountByStatus($vendorId)
    {
        return $this->model->where('vendors__id', $vendorId)
                          ->selectRaw('status, count(*) as count')
                          ->groupBy('status')
                          ->get()
                          ->pluck('count', 'status')
                          ->toArray();
    }
} 