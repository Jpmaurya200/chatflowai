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
     * Payment Gateway Constants
     */
    const GATEWAY_RAZORPAY = 'razorpay';
    const GATEWAY_PHONEPE = 'phonepe';
    
    /**
     * Payment Status Constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

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
        'gateway_label',
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
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_PARTIALLY_REFUNDED => 'Partially Refunded',
        ];

        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get gateway label
     */
    protected function getGatewayLabelAttribute(): string
    {
        $gatewayLabels = [
            self::GATEWAY_RAZORPAY => 'Razorpay',
            self::GATEWAY_PHONEPE => 'PhonePe',
        ];

        return $gatewayLabels[$this->gateway] ?? ucfirst($this->gateway);
    }

    /**
     * Check if payment is successful
     */
    protected function getIsSuccessfulAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment failed
     */
    protected function getIsFailedAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED]);
    }

    /**
     * Check if payment is pending
     */
    protected function getIsPendingAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Get available payment gateways
     */
    public static function getAvailableGateways(): array
    {
        return [
            self::GATEWAY_RAZORPAY => [
                'name' => 'Razorpay',
                'description' => 'Popular payment gateway for Indian businesses',
                'supported_currencies' => ['INR', 'USD'],
                'webhook_url' => 'whatsapp.payment.webhook.razorpay',
            ],
            self::GATEWAY_PHONEPE => [
                'name' => 'PhonePe',
                'description' => 'UPI-based payment solution',
                'supported_currencies' => ['INR'],
                'webhook_url' => 'whatsapp.payment.webhook.phonepe',
            ],
        ];
    }

    /**
     * Check if gateway supports currency
     */
    public function isCurrencySupported(string $currency): bool
    {
        $gateways = self::getAvailableGateways();
        $gateway = $gateways[$this->gateway] ?? null;
        
        return $gateway && in_array($currency, $gateway['supported_currencies']);
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
     * Set gateway-specific metadata
     */
    public function setGatewayMetadata(string $key, $value): void
    {
        $data = $this->__data ?? [];
        $data['gateway_metadata'][$key] = $value;
        $this->__data = $data;
    }

    /**
     * Get gateway-specific metadata
     */
    public function getGatewayMetadata(string $key, $default = null)
    {
        return $this->__data['gateway_metadata'][$key] ?? $default;
    }

    /**
     * Set customer details
     */
    public function setCustomerDetails(array $details): void
    {
        $data = $this->__data ?? [];
        $data['customer_details'] = $details;
        $this->__data = $data;
    }

    /**
     * Get customer details
     */
    public function getCustomerDetails(): array
    {
        return $this->__data['customer_details'] ?? [];
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(string $transactionId = null): void
    {
        $this->status = self::STATUS_COMPLETED;
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
        $this->status = self::STATUS_FAILED;
        $this->payment_failed_at = now();
        
        if ($reason) {
            $this->setPaymentMetadata('failure_reason', $reason);
        }
    }

    /**
     * Mark payment as processing
     */
    public function markAsProcessing(): void
    {
        $this->status = self::STATUS_PROCESSING;
    }

    /**
     * Mark payment as cancelled
     */
    public function markAsCancelled(string $reason = null): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->payment_failed_at = now();
        
        if ($reason) {
            $this->setPaymentMetadata('cancellation_reason', $reason);
        }
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
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

    /**
     * Get gateway webhook URL
     */
    public function getWebhookUrl(): string
    {
        $gateways = self::getAvailableGateways();
        $gateway = $gateways[$this->gateway] ?? null;
        
        return $gateway ? route($gateway['webhook_url']) : '';
    }

    /**
     * Scope for filtering by gateway
     */
    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }
}
