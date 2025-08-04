<?php
/**
 * ShopifyOrderNotificationModel.php - Model file
 *
 * This file is part of the Shopify Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\Shopify\Models;

use App\Yantrana\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyOrderNotificationModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'shopify_order_notifications';

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'notification_data' => 'array',
            'whatsapp_message_data' => 'array',
            'metadata' => 'array:extend',
        ],
    ];

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
        '_uid',
        'shopify_orders__id',
        'vendors__id',
        'contacts__id',
        'notification_type',
        'status',
        'message_id',
        'whatsapp_message_id',
        'sent_at',
        'delivered_at',
        'read_at',
        'error_message',
        '__data',
    ];

    protected $appends = [
        'status_label',
        'notification_type_label',
    ];

    /**
     * Get the order that owns the notification
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopifyOrderModel::class, 'shopify_orders__id', '_id');
    }

    /**
     * Get the vendor that owns the notification
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Yantrana\Components\Vendor\Models\VendorModel::class, 'vendors__id', '_id');
    }

    /**
     * Get the contact that received the notification
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(\App\Yantrana\Components\Contact\Models\ContactModel::class, 'contacts__id', '_id');
    }

    /**
     * Get status label
     */
    protected function getStatusLabelAttribute(): string
    {
        $statusLabels = [
            'pending' => 'Pending',
            'sent' => 'Sent',
            'delivered' => 'Delivered',
            'read' => 'Read',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];

        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get notification type label
     */
    protected function getNotificationTypeLabelAttribute(): string
    {
        $typeLabels = [
            'order_confirmation' => 'Order Confirmation',
            'payment_confirmation' => 'Payment Confirmation',
            'shipment_tracking' => 'Shipment Tracking',
            'delivery_confirmation' => 'Delivery Confirmation',
            'cod_verification' => 'COD Verification',
            'order_cancelled' => 'Order Cancelled',
            'refund_processed' => 'Refund Processed',
        ];

        return $typeLabels[$this->notification_type] ?? ucfirst(str_replace('_', ' ', $this->notification_type));
    }

    /**
     * Check if notification is sent
     */
    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'read']);
    }

    /**
     * Check if notification is delivered
     */
    public function isDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'read']);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    /**
     * Check if notification failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get notification data
     */
    public function getNotificationData(): array
    {
        return $this->__data['notification_data'] ?? [];
    }

    /**
     * Set notification data
     */
    public function setNotificationData(array $data): void
    {
        $currentData = $this->__data ?? [];
        $currentData['notification_data'] = $data;
        $this->__data = $currentData;
    }

    /**
     * Get WhatsApp message data
     */
    public function getWhatsAppMessageData(): array
    {
        return $this->__data['whatsapp_message_data'] ?? [];
    }

    /**
     * Set WhatsApp message data
     */
    public function setWhatsAppMessageData(array $data): void
    {
        $currentData = $this->__data ?? [];
        $currentData['whatsapp_message_data'] = $data;
        $this->__data = $currentData;
    }

    /**
     * Mark as sent
     */
    public function markAsSent(?string $messageId = null): void
    {
        $this->status = 'sent';
        $this->message_id = $messageId;
        $this->sent_at = now();
        $this->save();
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(): void
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        $this->save();
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->status = 'read';
        $this->read_at = now();
        $this->save();
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(?string $errorMessage = null): void
    {
        $this->status = 'failed';
        $this->error_message = $errorMessage;
        $this->save();
    }
} 