<?php
/**
 * ShopifyOrderNotificationRepository.php - Repository file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Integration\Shopify\Models\ShopifyOrderNotificationModel;

class ShopifyOrderNotificationRepository extends BaseRepository
{
    /**
     * @var ShopifyOrderNotificationModel - Shopify Order Notification Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(ShopifyOrderNotificationModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get notification by order ID and type
     */
    public function getByOrderAndType(int $orderId, string $notificationType): ?ShopifyOrderNotificationModel
    {
        return $this->model->where('shopify_orders__id', $orderId)
            ->where('notification_type', $notificationType)
            ->first();
    }

    /**
     * Get notifications by order ID
     */
    public function getByOrderId(int $orderId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('shopify_orders__id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get notifications by vendor ID
     */
    public function getByVendorId(int $vendorId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notifications by status
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
     * Get notifications by type
     */
    public function getByType(int $vendorId, string $notificationType, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->where('notification_type', $notificationType)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create notification
     */
    public function createNotification(array $data): ShopifyOrderNotificationModel
    {
        return $this->model->create($data);
    }

    /**
     * Update notification status
     */
    public function updateStatus(int $notificationId, string $status, array $additionalData = []): bool
    {
        $data = array_merge(['status' => $status], $additionalData);

        if ($status === 'sent') {
            $data['sent_at'] = now();
        } elseif ($status === 'delivered') {
            $data['delivered_at'] = now();
        } elseif ($status === 'read') {
            $data['read_at'] = now();
        }

        return $this->model->where('_id', $notificationId)->update($data);
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(int $notificationId, ?string $messageId = null): bool
    {
        return $this->model->where('_id', $notificationId)->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as delivered
     */
    public function markAsDelivered(int $notificationId): bool
    {
        return $this->model->where('_id', $notificationId)->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        return $this->model->where('_id', $notificationId)->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(int $notificationId, ?string $errorMessage = null): bool
    {
        return $this->model->where('_id', $notificationId)->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStatistics(int $vendorId): array
    {
        $totalNotifications = $this->model->where('vendors__id', $vendorId)->count();
        $sentNotifications = $this->model->where('vendors__id', $vendorId)
            ->whereIn('status', ['sent', 'delivered', 'read'])
            ->count();
        $deliveredNotifications = $this->model->where('vendors__id', $vendorId)
            ->whereIn('status', ['delivered', 'read'])
            ->count();
        $readNotifications = $this->model->where('vendors__id', $vendorId)
            ->where('status', 'read')
            ->count();
        $failedNotifications = $this->model->where('vendors__id', $vendorId)
            ->where('status', 'failed')
            ->count();

        return [
            'total_notifications' => $totalNotifications,
            'sent_notifications' => $sentNotifications,
            'delivered_notifications' => $deliveredNotifications,
            'read_notifications' => $readNotifications,
            'failed_notifications' => $failedNotifications,
            'delivery_rate' => $totalNotifications > 0 ? round(($deliveredNotifications / $totalNotifications) * 100, 2) : 0,
            'read_rate' => $totalNotifications > 0 ? round(($readNotifications / $totalNotifications) * 100, 2) : 0,
        ];
    }

    /**
     * Get notifications by date range
     */
    public function getByDateRange(int $vendorId, string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get failed notifications
     */
    public function getFailedNotifications(int $vendorId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('vendors__id', $vendorId)
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete notifications by order ID
     */
    public function deleteByOrderId(int $orderId): bool
    {
        return $this->model->where('shopify_orders__id', $orderId)->delete();
    }

    /**
     * Delete notifications by vendor ID
     */
    public function deleteByVendorId(int $vendorId): bool
    {
        return $this->model->where('vendors__id', $vendorId)->delete();
    }
} 