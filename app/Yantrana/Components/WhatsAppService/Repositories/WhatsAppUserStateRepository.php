<?php
/**
 * WhatsAppUserStateRepository.php - Repository file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\WhatsAppService\Models\WhatsAppUserStateModel;
use App\Yantrana\Components\WhatsAppService\Interfaces\WhatsAppUserStateRepositoryInterface;

class WhatsAppUserStateRepository extends BaseRepository implements WhatsAppUserStateRepositoryInterface
{
    /**
     * Repository related to primary model
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $primaryModel = WhatsAppUserStateModel::class;

    /**
     * Fetch user state by phone and vendor
     *
     * @param string $phone
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByPhone(string $phone, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('phone', $phone)
            ->where('vendors__id', $vendorId)
            ->first();
    }

    /**
     * Fetch user state by order ID
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByOrderId(string $orderId, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('order_id', $orderId)
            ->where('vendors__id', $vendorId)
            ->first();
    }

    /**
     * Fetch user states by state
     *
     * @param string $state
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByState(string $state, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('state', $state)
            ->where('vendors__id', $vendorId)
            ->get();
    }

    /**
     * Create or update user state
     *
     * @param string $phone
     * @param string $state
     * @param array $data
     * @param int|null $vendorId
     * @return object
     */
    public function createOrUpdateState(string $phone, string $state, array $data = [], ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $stateData = [
            'vendors__id' => $vendorId,
            'phone' => $phone,
            'state' => $state,
        ];

        // Merge additional data
        if (!empty($data['order_id'])) {
            $stateData['order_id'] = $data['order_id'];
        }

        if (!empty($data['context'])) {
            $stateData['context'] = $data['context'];
        }

        if (!empty($data['__data'])) {
            $stateData['__data'] = $data['__data'];
        }

        if (!empty($data['expires_at'])) {
            $stateData['expires_at'] = $data['expires_at'];
        } else {
            // Default expiration: 1 hour
            $stateData['expires_at'] = now()->addHour();
        }

        // Find existing state
        $existingState = $this->fetchByPhone($phone, $vendorId);

        if ($existingState) {
            $this->updateIt(['_id' => $existingState->_id], $stateData);
            return $existingState->fresh();
        }

        return $this->storeIt($stateData);
    }

    /**
     * Update user state data
     *
     * @param string $phone
     * @param array $data
     * @param int|null $vendorId
     * @return bool
     */
    public function updateStateData(string $phone, array $data, ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->updateIt([
            'phone' => $phone,
            'vendors__id' => $vendorId
        ], $data);
    }

    /**
     * Clear user state
     *
     * @param string $phone
     * @param int|null $vendorId
     * @return bool
     */
    public function clearState(string $phone, ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('phone', $phone)
            ->where('vendors__id', $vendorId)
            ->delete() > 0;
    }

    /**
     * Clear state by order ID
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return bool
     */
    public function clearStateByOrderId(string $orderId, ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('order_id', $orderId)
            ->where('vendors__id', $vendorId)
            ->delete() > 0;
    }

    /**
     * Clear expired states
     *
     * @param int|null $vendorId
     * @return int
     */
    public function clearExpiredStates(?int $vendorId = null): int
    {
        $query = $this->primaryModel::where('expires_at', '<', now());
        
        if ($vendorId) {
            $query->where('vendors__id', $vendorId);
        }
        
        return $query->delete();
    }

    /**
     * Extend state expiration
     *
     * @param string $phone
     * @param int $minutes
     * @param int|null $vendorId
     * @return bool
     */
    public function extendStateExpiration(string $phone, int $minutes = 30, ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $state = $this->fetchByPhone($phone, $vendorId);
        
        if (!$state) {
            return false;
        }

        $newExpiration = $state->expires_at ? 
            $state->expires_at->addMinutes($minutes) : 
            now()->addMinutes($minutes);

        return $this->updateIt(['_id' => $state->_id], [
            'expires_at' => $newExpiration
        ]);
    }

    /**
     * Check if user is in specific state
     *
     * @param string $phone
     * @param string $state
     * @param int|null $vendorId
     * @return bool
     */
    public function isUserInState(string $phone, string $state, ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $userState = $this->fetchByPhone($phone, $vendorId);
        
        return $userState && $userState->state === $state && !$userState->is_expired;
    }

    /**
     * Get active states count by state
     *
     * @param int|null $vendorId
     * @return array
     */
    public function getActiveStatesCount(?int $vendorId = null): array
    {
        $vendorId = $vendorId ?: getVendorId();
        
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->groupBy('state')
            ->selectRaw('state, count(*) as count')
            ->pluck('count', 'state')
            ->toArray();
    }

    /**
     * Set state data for user
     *
     * @param string $phone
     * @param string $key
     * @param mixed $value
     * @param int|null $vendorId
     * @return bool
     */
    public function setStateData(string $phone, string $key, $value, ?int $vendorId = null): bool
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $state = $this->fetchByPhone($phone, $vendorId);
        
        if (!$state) {
            return false;
        }

        $data = $state->__data ?? [];
        $data['step_data'][$key] = $value;

        return $this->updateIt(['_id' => $state->_id], [
            '__data' => $data
        ]);
    }

    /**
     * Get state data for user
     *
     * @param string $phone
     * @param string $key
     * @param mixed $default
     * @param int|null $vendorId
     * @return mixed
     */
    public function getStateData(string $phone, string $key, $default = null, ?int $vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        
        $state = $this->fetchByPhone($phone, $vendorId);
        
        if (!$state) {
            return $default;
        }

        return $state->__data['step_data'][$key] ?? $default;
    }
}
