<?php
/**
 * WhatsAppUserStateRepositoryInterface.php - Repository Interface file
 *
 * This file is part of the WhatsAppService component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Interfaces;

interface WhatsAppUserStateRepositoryInterface
{
    /**
     * Fetch user state by phone and vendor
     *
     * @param string $phone
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByPhone(string $phone, ?int $vendorId = null);

    /**
     * Fetch user state by order ID
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return object|null
     */
    public function fetchByOrderId(string $orderId, ?int $vendorId = null);

    /**
     * Fetch user states by state
     *
     * @param string $state
     * @param int|null $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByState(string $state, ?int $vendorId = null);

    /**
     * Create or update user state
     *
     * @param string $phone
     * @param string $state
     * @param array $data
     * @param int|null $vendorId
     * @return object
     */
    public function createOrUpdateState(string $phone, string $state, array $data = [], ?int $vendorId = null);

    /**
     * Update user state data
     *
     * @param string $phone
     * @param array $data
     * @param int|null $vendorId
     * @return bool
     */
    public function updateStateData(string $phone, array $data, ?int $vendorId = null): bool;

    /**
     * Clear user state
     *
     * @param string $phone
     * @param int|null $vendorId
     * @return bool
     */
    public function clearState(string $phone, ?int $vendorId = null): bool;

    /**
     * Clear state by order ID
     *
     * @param string $orderId
     * @param int|null $vendorId
     * @return bool
     */
    public function clearStateByOrderId(string $orderId, ?int $vendorId = null): bool;

    /**
     * Clear expired states
     *
     * @param int|null $vendorId
     * @return int
     */
    public function clearExpiredStates(?int $vendorId = null): int;

    /**
     * Extend state expiration
     *
     * @param string $phone
     * @param int $minutes
     * @param int|null $vendorId
     * @return bool
     */
    public function extendStateExpiration(string $phone, int $minutes = 30, ?int $vendorId = null): bool;

    /**
     * Check if user is in specific state
     *
     * @param string $phone
     * @param string $state
     * @param int|null $vendorId
     * @return bool
     */
    public function isUserInState(string $phone, string $state, ?int $vendorId = null): bool;

    /**
     * Get active states count by state
     *
     * @param int|null $vendorId
     * @return array
     */
    public function getActiveStatesCount(?int $vendorId = null): array;

    /**
     * Set state data for user
     *
     * @param string $phone
     * @param string $key
     * @param mixed $value
     * @param int|null $vendorId
     * @return bool
     */
    public function setStateData(string $phone, string $key, $value, ?int $vendorId = null): bool;

    /**
     * Get state data for user
     *
     * @param string $phone
     * @param string $key
     * @param mixed $default
     * @param int|null $vendorId
     * @return mixed
     */
    public function getStateData(string $phone, string $key, $default = null, ?int $vendorId = null);
}
