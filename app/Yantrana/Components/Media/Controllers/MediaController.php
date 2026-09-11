<?php
/**
* MediaController.php - Controller file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Configuration\ConfigurationEngine;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\Vendor\VendorSettingsEngine;
use Illuminate\Http\Request;

class MediaController extends BaseController
{
    /**
     * @var MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * @var ConfigurationEngine - Configuration Engine
     */
    protected $configurationEngine;

    /**
     * @var VendorSettingsEngine - Vendor Engine
     */
    protected $vendorSettingsEngine;

    /**
     * Constructor
     *
     * @param  MediaEngine  $mediaEngine  - Media Engine
     * @return void
     *-----------------------------------------------------------------------*/
    public function __construct(
        MediaEngine $mediaEngine,
        ConfigurationEngine $configurationEngine,
        VendorSettingsEngine $vendorSettingsEngine
    ) {
        $this->mediaEngine = $mediaEngine;
        $this->configurationEngine = $configurationEngine;
        $this->vendorSettingsEngine = $vendorSettingsEngine;
    }

    /**
     * Upload Temp Media.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadTempMedia(Request $request, $uploadItem = 'all')
    {
        // Process the upload
        $processReaction = $this->mediaEngine
            ->processUploadTempMedia($request->all(), $uploadItem);
    
        // Return JSON response for both success and failure cases
         // See the raw EngineResponse before wrapping
    \Log::info('Engine response data:', $processReaction->data());
    // Or just dump it in browser for debugging
    // dd($processReaction->data());

    return $this->processResponse(
        $processReaction,
        $processReaction->data(),
        [],
        true,
        $processReaction->success() ? 200 : 406
    );  }
    

/**
 * Show the template preview with the uploaded file URL
 */
public function showTemplatePreview(Request $request)
{
    // Get the fileUrl from query parameter
    $fileUrl = $request->query('fileUrl');
    
    // Decode the file URL if it's URL encoded
    $fileUrl = urldecode($fileUrl);
    
    // Log the file URL for debugging
    \Log::info('showTemplatePreview called with file URL: ' . $fileUrl);

    // Return the Blade view with the fileUrl
    return view('whatsapp-service.templates.new-template', compact('fileUrl'));
}




    /**
     * Upload Logo.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadLogo(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadLogo($request->all(), 'logo');
       
        // Check if file uploaded successfully
        if ($processReaction->success()) {
            $this->configurationEngine->processConfigurationsStore('general', [
                'logo_name' => $processReaction['data']['fileName'],
            ], true);
        }

        return $this->processResponse($processReaction, [], [], true, $processReaction->success() ? 200 : 406);
    }

    /**
     * Upload Logo.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadFavicon(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadFavicon($request->all(), 'favicon');

        // Check if file uploaded successfully
        if ($processReaction->success()) {
            $this->configurationEngine->processConfigurationsStore('general', [
                'favicon_name' => $processReaction['data']['fileName'],
            ], true);
        }

        return $this->processResponse($processReaction, [], [], true, $processReaction->success() ? 200 : 406);
    }

    /**
     * Upload Logo.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function vendorUpload(Request $request, $uploadItem)
    {
        $allowedItems = [
            'vendor_logo' => 'logo_name',
            'vendor_small_logo' => 'small_logo_name',
            'vendor_favicon' => 'favicon_name',
        ];
        $processReaction = $this->mediaEngine
            ->processVendorUpload($request->all(), $uploadItem, $allowedItems);
        // Check if file uploaded successfully
        if ($processReaction->success()) {
            $this->vendorSettingsEngine->updateBasicSettingsProcess([
                $allowedItems[$uploadItem] => $processReaction['data']['fileName'],
            ]);

            return $this->processResponse($processReaction, [], [], true);
        }

        return $this->processResponse($processReaction, [], [], true, $processReaction->success() ? 200 : 406);
    }

    /**
     * Upload  Small Logo.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadSmallLogo(Request $request)
    {
        $processReaction = $this->mediaEngine->processUploadSmallLogo($request->all(), 'small_logo');
    // Check if file uploaded successfully
    if ($processReaction->success()) {
        $this->configurationEngine->processConfigurationsStore('general', [
            'small_logo_name' => $processReaction['data']['fileName'],
        ], true);
    }

    return $this->processResponse($processReaction, [], [], true, $processReaction->success() ? 200 : 406);
    }

    /**
     * Upload Hero Image.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadHeroImage(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'filepond' => 'required|image|mimes:jpg,jpeg,png|max:5120' // 5MB max
            ]);

            // Process the upload
            $processReaction = $this->mediaEngine->processUploadHeroImage($request->all());

            // Check if file uploaded successfully
            if ($processReaction->success()) {
                $this->configurationEngine->processConfigurationsStore('general', [
                    'hero_image' => $processReaction['data']['path'],
                ], true);
            }

            return $this->processResponse($processReaction, [], [], true, $processReaction->success() ? 200 : 406);
            
        } catch (\Exception $e) {
            return $this->processResponse([
                'reaction_code' => -1,
                'message' => $e->getMessage()
            ], [], [], true);
        }
    }

    /**
     * Upload WhatsApp QR Code
     *
     * @param object $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadWhatsAppQR(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'filepond' => 'required|image|mimes:jpg,jpeg,png|max:5120' // 5MB max
            ]);

            // Process the upload
            $processReaction = $this->mediaEngine->processUploadWhatsAppQR($request->all());

            // Check if file uploaded successfully
            if ($processReaction->success()) {
                // Assuming you want to store the path in configurations
                $this->configurationEngine->processConfigurationsStore('general', [
                    'whatsapp_qr_image' => $processReaction['data']['path'],
                ], true);
            }

            return $this->processResponse($processReaction, [], [], true, $processReaction->success() ? 200 : 406);
            
        } catch (\Exception $e) {
            return $this->processResponse([
                'reaction_code' => -1,
                'message' => $e->getMessage()
            ], [], [], true);
        }
    }
}
