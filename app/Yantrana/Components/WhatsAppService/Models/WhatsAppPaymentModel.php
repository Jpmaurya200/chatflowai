<?php
/**
 * WhatsAppPaymentModel.php - Model file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Models;

use App\Yantrana\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Yantrana\Components\Vendor\Models\VendorModel;

class WhatsAppPaymentModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'whatsapp_payments';

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'payment_details' => 'array',
            'customer_details' => 'array',
            'gateway_metadata' => 'array',
            'refund_details' => 'array',
            'metadata' => 'array:extend',
        ],
        'gateway_response' => [
            'request_data' => 'array',
            'response_data' => 'array',
            'webhook_data' => 'array:extend',
        ],
    ];

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'gateway_response' => 'array',
        'amount' => 'decimal:2',
        'payment_initiated_at' => 'datetime',
        'payment_completed_at' => 'datetime',
        'payment_failed_at' => 'datetime',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
        'payment_id',
        'vendors__id',
        'order_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'payment_link_id',
        'payment_link_url',
        'gateway',
        'gateway_response',
        '__data',
        'payment_initiated_at',
        'payment_completed_at',
        'payment_failed_at',
    ];

    protected $appends = [
        'formatted_amount',
        'status_label',
        'is_successful',
        'is_failed',
        'is_pending',
    ];

    /**
     * Get the vendor that owns the payment
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendors__id', '_id');
    }

    /**
     * Get the order associated with this payment
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(WhatsAppOrderModel::class, 'order_id', 'order_id');
    }

    /**
     * Get formatted amount
     */
    protected function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get status label
     */
    protected function getStatusLabelAttribute(): string
    {
        $statusLabels = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially Refunded',
        ];

        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if payment is successful
     */
    protected function getIsSuccessfulAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment failed
     */
    protected function getIsFailedAttribute(): bool
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    /**
     * Check if payment is pending
     */
    protected function getIsPendingAttribute(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Set gateway response data
     */
    public function setGatewayResponse(string $type, array $data): void
    {
        $response = $this->gateway_response ?? [];
        $response[$type] = $data;
        $this->gateway_response = $response;
    }

    /**
     * Get gateway response data
     */
    public function getGatewayResponse(string $type, $default = null)
    {
        return $this->gateway_response[$type] ?? $default;
    }

    /**
     * Set payment metadata
     */
    public function setPaymentMetadata(string $key, $value): void
    {
        $data = $this->__data ?? [];
        $data['payment_details'][$key] = $value;
        $this->__data = $data;
    }

    /**
     * Get payment metadata
     */
    public function getPaymentMetadata(string $key, $default = null)
    {
        return $this->__data['payment_details'][$key] ?? $default;
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(string $transactionId = null): void
    {
        $this->status = 'completed';
        $this->payment_completed_at = now();
        
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->status = 'failed';
        $this->payment_failed_at = now();
        
        if ($reason) {
            $this->setPaymentMetadata('failure_reason', $reason);
        }
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get payment duration in minutes
     */
    public function getPaymentDuration(): ?int
    {
        if (!$this->payment_initiated_at || !$this->payment_completed_at) {
            return null;
        }

        return $this->payment_initiated_at->diffInMinutes($this->payment_completed_at);
    }
}
