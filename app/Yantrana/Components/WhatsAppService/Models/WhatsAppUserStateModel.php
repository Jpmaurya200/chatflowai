<?php
/**
 * WhatsAppUserStateModel.php - Model file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Models;

use App\Yantrana\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Yantrana\Components\Contact\Models\ContactModel;
use App\Yantrana\Components\Vendor\Models\VendorModel;

class WhatsAppUserStateModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'whatsapp_user_states';

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'step_data' => 'array',
            'form_data' => 'array',
            'context_data' => 'array',
            'metadata' => 'array:extend',
        ],
    ];

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendors__id',
        'contacts__id',
        'phone',
        'state',
        'order_id',
        'context',
        '__data',
        'expires_at',
    ];

    protected $appends = [
        'is_expired',
        'state_label',
    ];

    /**
     * Get the vendor that owns the user state
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendors__id', '_id');
    }

    /**
     * Get the contact for this user state
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(ContactModel::class, 'contacts__id', '_id');
    }

    /**
     * Get the order associated with this state
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(WhatsAppOrderModel::class, 'order_id', 'order_id');
    }

    /**
     * Check if the state is expired
     */
    protected function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Get human-readable state label
     */
    protected function getStateLabelAttribute(): string
    {
        $stateLabels = [
            'awaiting_address' => 'Waiting for Delivery Address',
            'awaiting_payment' => 'Waiting for Payment',
            'awaiting_confirmation' => 'Waiting for Confirmation',
            'processing_order' => 'Processing Order',
            'order_completed' => 'Order Completed',
        ];

        return $stateLabels[$this->state] ?? ucfirst(str_replace('_', ' ', $this->state));
    }

    /**
     * Set state data
     */
    public function setStateData(string $key, $value): void
    {
        $data = $this->__data ?? [];
        $data['step_data'][$key] = $value;
        $this->__data = $data;
    }

    /**
     * Get state data
     */
    public function getStateData(string $key, $default = null)
    {
        return $this->__data['step_data'][$key] ?? $default;
    }

    /**
     * Set context data
     */
    public function setContextData(string $key, $value): void
    {
        $data = $this->__data ?? [];
        $data['context_data'][$key] = $value;
        $this->__data = $data;
    }

    /**
     * Get context data
     */
    public function getContextData(string $key, $default = null)
    {
        return $this->__data['context_data'][$key] ?? $default;
    }

    /**
     * Clear expired states
     */
    public static function clearExpiredStates(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }

    /**
     * Set expiration time
     */
    public function setExpiration(int $minutes = 60): void
    {
        $this->expires_at = now()->addMinutes($minutes);
    }

    /**
     * Extend expiration time
     */
    public function extendExpiration(int $minutes = 30): void
    {
        if ($this->expires_at) {
            $this->expires_at = $this->expires_at->addMinutes($minutes);
        } else {
            $this->setExpiration($minutes);
        }
    }

    /**
     * Check if state matches
     */
    public function isState(string $state): bool
    {
        return $this->state === $state;
    }

    /**
     * Check if state is in list
     */
    public function isStateIn(array $states): bool
    {
        return in_array($this->state, $states);
    }
}
