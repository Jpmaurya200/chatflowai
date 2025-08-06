<?php
/**
 * WooCommerceOrderNotificationModel.php - Model file
 *
 * This file is part of the WooCommerce Integration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Integration\WooCommerce\Models;

use App\Yantrana\Base\BaseModel;
use Carbon\Carbon;

class WooCommerceOrderNotificationModel extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'woocommerce_order_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendors__id',
        'woocommerce_orders__id',
        'notification_type',
        'contacts__id',
        'whatsapp_templates__id',
        'variables',
        'status',
        'response',
        'sent_at',
        'delivered_at',
        'read_at',
        'error_message',
        'retry_count',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'vendors__id' => 'integer',
        'variables' => 'array',
        'response' => 'array',
        'status' => 'string',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'retry_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'notification_type_label',
        'status_label',
        'time_since_sent',
        'formatted_sent_date',
        'error_message',
        'response_summary'
    ];

    /**
     * Get notification type label
     */
    public function getNotificationTypeLabelAttribute(): string
    {
        $typeMap = [
            'order_confirmation' => 'Order Confirmation',
            'payment_confirmation' => 'Payment Confirmation',
            'shipment_tracking' => 'Shipment Tracking',
            'delivery_confirmation' => 'Delivery Confirmation',
            'cod_verification' => 'COD Verification'
        ];

        return $typeMap[$this->notification_type] ?? ucfirst(str_replace('_', ' ', $this->notification_type));
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $statusMap = [
            'pending' => 'Pending',
            'sent' => 'Sent',
            'failed' => 'Failed',
            'delivered' => 'Delivered',
            'read' => 'Read'
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if notification is sent
     */
    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'read']);
    }

    /**
     * Check if notification is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if notification can be retried
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && $this->retry_count < 3;
    }

    /**
     * Get time since sent
     */
    public function getTimeSinceSentAttribute(): ?string
    {
        if (!$this->sent_at) {
            return null;
        }

        return $this->sent_at->diffForHumans();
    }

    /**
     * Get formatted sent date
     */
    public function getFormattedSentDateAttribute(): string
    {
        if (!$this->sent_at) {
            return 'N/A';
        }

        return $this->sent_at->format('M d, Y H:i');
    }

    /**
     * Get error message
     */
    public function getErrorMessageAttribute(): string
    {
        if ($this->response && isset($this->response['error'])) {
            return $this->response['error'];
        }

        return $this->attributes['error_message'] ?? 'Unknown error';
    }



    /**
     * Get response summary
     */
    public function getResponseSummaryAttribute(): string
    {
        if (!$this->response) {
            return 'No response data';
        }

        if (isset($this->response['error'])) {
            return 'Error: ' . ($this->response['error']['message'] ?? 'Unknown error');
        }

        if (isset($this->response['messages']) && !empty($this->response['messages'])) {
            return 'Success: Message ID ' . ($this->response['messages'][0]['id'] ?? 'N/A');
        }

        return 'No response data';
    }

    /**
     * Relationship with order
     */
    public function order()
    {
        return $this->belongsTo(WooCommerceOrderModel::class, 'woocommerce_orders__id');
    }

    /**
     * Relationship with contact
     */
    public function contact()
    {
        return $this->belongsTo(\App\Yantrana\Components\Contact\Models\ContactModel::class, 'contacts__id');
    }

    /**
     * Relationship with template
     */
    public function template()
    {
        return $this->belongsTo(\App\Yantrana\Components\WhatsAppService\Models\WhatsAppTemplateModel::class, 'whatsapp_templates__id');
    }
} 