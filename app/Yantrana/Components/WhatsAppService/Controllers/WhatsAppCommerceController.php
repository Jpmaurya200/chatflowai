<?php
/**
 * WhatsAppCommerceController.php - Controller for WhatsApp Commerce operations
 *
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppCommerceService;

class WhatsAppCommerceController extends BaseController
{
    protected $whatsAppCommerceService;

    /**
     * Constructor
     */
    public function __construct(WhatsAppCommerceService $whatsAppCommerceService)
    {
        $this->whatsAppCommerceService = $whatsAppCommerceService;
    }

    /**
     * Show commerce settings page
     *
     * @return \Illuminate\View\View
     */
    public function showCommerceSettings()
    {
        validateVendorAccess('administrative');
        
        // Check if WhatsApp is configured
        if (!isWhatsAppBusinessAccountReady()) {
            return redirect()->route('vendor.settings.read', ['pageType' => 'whatsapp_cloud_api_setup'])
                ->with('error', __tr('Please complete your WhatsApp Cloud API Setup first'));
        }

        $commerceSummary = $this->whatsAppCommerceService->getCommerceSettingsSummary();
        
        return $this->loadView('whatsapp-service.commerce-settings', [
            'commerceSummary' => $commerceSummary,
            'pageTitle' => __tr('WhatsApp Commerce Settings')
        ]);
    }

    /**
     * Fetch commerce settings from WhatsApp API
     *
     * @param BaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCommerceSettings(BaseRequest $request)
    {
        validateVendorAccess('administrative');
        
        if (!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        $processReaction = $this->whatsAppCommerceService->fetchCommerceSettings();

        return $this->responseAction($this->processResponse($processReaction, [], [], true));
    }

    /**
     * Update commerce settings
     *
     * @param BaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCommerceSettings(BaseRequest $request)
    {
        validateVendorAccess('administrative');
        
        if (!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        $request->validate([
            'catalog_visibility' => 'nullable|in:0,1,true,false,on,off',
            'cart_enabled' => 'nullable|in:0,1,true,false,on,off',
            'enable_whatsapp_commerce' => 'nullable|in:0,1,true,false,on,off',
            'auto_sync_commerce_settings' => 'nullable|in:0,1,true,false,on,off',
        ]);

        // Update local settings first
        $localSettings = [];
        if ($request->has('enable_whatsapp_commerce')) {
            $localSettings['enable_whatsapp_commerce'] = $this->convertToBoolean($request->input('enable_whatsapp_commerce'));
        }
        if ($request->has('auto_sync_commerce_settings')) {
            $localSettings['auto_sync_commerce_settings'] = $this->convertToBoolean($request->input('auto_sync_commerce_settings'));
        }

        if (!empty($localSettings)) {
            app()->make(\App\Yantrana\Components\Vendor\VendorSettingsEngine::class)
                ->updateProcess('whatsapp_commerce_setup', $localSettings);
        }

        // Update WhatsApp API settings if provided
        $apiSettings = [];
        if ($request->has('catalog_visibility')) {
            $apiSettings['catalog_visibility'] = $this->convertToBoolean($request->input('catalog_visibility'));
        }
        if ($request->has('cart_enabled')) {
            $apiSettings['cart_enabled'] = $this->convertToBoolean($request->input('cart_enabled'));
        }

        if (!empty($apiSettings)) {
            $processReaction = $this->whatsAppCommerceService->updateCommerceSettings($apiSettings);
            return $this->responseAction($this->processResponse($processReaction, [], [], true));
        }

        return $this->processResponse(1, [
            1 => __tr('Settings updated successfully')
        ], [], true);
    }

    /**
     * Get catalog products
     *
     * @param BaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCatalogProducts(BaseRequest $request)
    {
        validateVendorAccess('messaging');
        
        if (!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
            'after' => 'nullable|string',
            'before' => 'nullable|string',
        ]);

        $options = [];
        if ($request->has('limit')) {
            $options['limit'] = $request->integer('limit');
        }
        if ($request->has('after')) {
            $options['after'] = $request->string('after');
        }
        if ($request->has('before')) {
            $options['before'] = $request->string('before');
        }

        $processReaction = $this->whatsAppCommerceService->getCatalogProducts($options);

        return $this->responseAction($this->processResponse($processReaction, [], [], true));
    }

    /**
     * Get commerce settings summary (AJAX)
     *
     * @param BaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommerceSettingsSummary(BaseRequest $request)
    {
        validateVendorAccess('administrative');

        $commerceSummary = $this->whatsAppCommerceService->getCommerceSettingsSummary();

        return $this->processResponse(1, [
            1 => __tr('Commerce settings retrieved successfully')
        ], [
            'commerceSummary' => $commerceSummary
        ], true);
    }

    /**
     * Auto-sync commerce settings
     *
     * @param BaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoSyncCommerceSettings(BaseRequest $request)
    {
        validateVendorAccess('administrative');
        
        if (!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        $processReaction = $this->whatsAppCommerceService->autoSyncCommerceSettings();

        if ($processReaction) {
            return $this->responseAction($this->processResponse($processReaction, [], [], true));
        }

        return $this->processResponse(22, [
            22 => __tr('Auto-sync is disabled')
        ], [], true);
    }

    /**
     * Test commerce API connection
     *
     * @param BaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testCommerceConnection(BaseRequest $request)
    {
        validateVendorAccess('administrative');
        
        if (!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        try {
            // Try to fetch commerce settings to test connection
            $processReaction = $this->whatsAppCommerceService->fetchCommerceSettings();
            
            if ($processReaction->success()) {
                return $this->processResponse(1, [
                    1 => __tr('Commerce API connection successful')
                ], [], true);
            } else {
                return $this->responseAction($this->processResponse($processReaction, [], [], true));
            }
        } catch (\Exception $e) {
            return $this->processResponse(22, [
                22 => __tr('Commerce API connection failed: ') . $e->getMessage()
            ], [], true);
        }
    }

    /**
     * Convert various boolean representations to actual boolean
     *
     * @param mixed $value
     * @return bool
     */
    private function convertToBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'on', 'yes']);
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return false;
    }
}
