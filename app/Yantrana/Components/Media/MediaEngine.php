<?php

/**
 * MediaEngine.php - Main component file
 *
 * This file is part of the Media component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media;

use App\Yantrana\Base\BaseMediaEngine;
use App\Yantrana\Components\Media\Interfaces\MediaEngineInterface;
use Exception;
use File;
use Illuminate\Filesystem\Filesystem;
use YesFileStorage;

class MediaEngine extends BaseMediaEngine implements MediaEngineInterface
{
    protected $elements;

    protected $currentDisk;

    protected $disk;

    /**
     * Constructor.
     *
     * @param  MediaRepository  $mediaRepository  - Media Repository
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        $this->currentDisk = config('filesystems.default', 'public-media-storage'); //configItem('current_filesystem_driver');
        $this->disk = YesFileStorage::on($this->currentDisk); // do_s3_space, local
        $this->elements = config('yes-file-storage.element_config');
    }

    /**
     * Process Upload Logo
     *
     * @return array|object
     *---------------------------------------------------------------- */
    public function processUploadLogo($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('logo');

        return $this->processUpload($inputFile, $logoFolderPath, $requestFor);
    }

    /**
     * Process Upload Logo
     *
     * @return array|object
     *---------------------------------------------------------------- */
    public function processVendorUpload($inputFile, $requestFor, $allowedItems = [])
    {
        if (! array_key_exists($requestFor, $allowedItems)) {
            return $this->engineFailedResponse([], __tr('Invalid Request'));
        }

        $logoFolderPath = getPathByKey($requestFor, ['{_uid}' => getVendorUid()]);

        return $this->processUpload($inputFile, $logoFolderPath, $requestFor, true, getVendorSettings('logo_name'));
    }

    /**
     * Process Upload Logo
     *
     * @return EngineResponse
     *---------------------------------------------------------------- */
    public function processUploadSmallLogo($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('small_logo');

        return $this->processUpload($inputFile, $logoFolderPath, $requestFor);
    }

    /**
     * Process Upload Logo
     *
     * @return array|object
     *---------------------------------------------------------------- */
    public function processUploadFavicon($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('favicon');

        return $this->processUpload($inputFile, $logoFolderPath, $requestFor);
    }

    /**
     * Process Upload Profile Image
     *
     * @return array|object
     *---------------------------------------------------------------- */
    public function processUploadProfile($inputFile, $requestFor)
    {
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);

        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $profileImageFolderPath = getPathByKey('profile_photo', ['{_uid}' => authUID()]);

            return $this->resizeImageAndUpload($profileImageFolderPath, $fileName, [
                'height' => 360,
                'width' => 360,
            ]);

            return $this->engineFailedResponse([], __tr('Something went wrong while file moving.'));
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Process Upload Profile Image
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadCoverPhoto($inputFile, $requestFor)
    {
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);

        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $coverPhotoFolderPath = getPathByKey('cover_photo', ['{_uid}' => authUID()]);

            return $this->resizeImageAndUpload($coverPhotoFolderPath, $fileName, [
                'height' => 312,
                'width' => 820,
            ]);

            return $this->engineFailedResponse([], __tr('Something went wrong while file moving.'));
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Process Upload Profile Image
     *
     * @return EngineResponse
     *---------------------------------------------------------------- */
    public function whatsappMediaUploadProcess($inputFile, $requestFor)
    {
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);

        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $itemImageFolderPath = getPathByKey($requestFor, ['{_uid}' => getVendorUid()]);

            return $this->resizeImageAndUpload($itemImageFolderPath, $fileName);
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Download file and store
     *
     * @return array
     *---------------------------------------------------------------- */
    public function downloadAndStoreMediaFile($fileValue, $vendorUid, $mediaType = 'image')
    {
        $mimeTypesToExtension = [
            // audio
            'audio/aac' => 'aac',
            'audio/mp4' => 'm4a', // or 'mp4' if you are not distinguishing between audio-only and video
            'audio/mpeg' => 'mp3',
            'audio/amr' => 'amr',
            'audio/ogg' => 'ogg',
            // videos
            'video/mp4' => 'mp4',
            'video/3gp' => '3gp',
            'video/mpeg'      => 'mpeg',
            // images
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            // documents
            'text/plain' => 'txt',
            'application/pdf' => 'pdf',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            // Add more MIME types and their corresponding extensions as needed.
            'application/zip' => 'zip',
        ];
        $filesStored = [];
        try {
            $fileData = $fileValue['body'];
            if ($fileData) {
                $permanentFolderPath = getPathByKey("whatsapp_$mediaType", ['{_uid}' => $vendorUid]);
                $tempUploadFolderPath = getPathByKey('user_temp_uploads', ['{_uid}' => $vendorUid]);
                $filename = uniqid().'.'.$mimeTypesToExtension[$fileValue['mime_type']];
                // temp file storage
                $writtenFile = $this->disk->writeFile($tempUploadFolderPath.'/'.$filename, $fileData);
                // move to permanent storage
                $storedInfo = $this->processMoveFile($permanentFolderPath, $filename, [], [
                    'setVisibility' => 'public',
                    'publicMediaStorage' => false,
                    'pathParameters' => [
                        '{_uid}' => $vendorUid,
                    ],
                ]);
                $filesStored = $storedInfo->data();
            }
        } catch (Exception $e) {
            __logDebug($e->getMessage());
        }

        return $filesStored;
    }

    /**
     * Delete temp file
     *
     * @param  string  $filename
     * @return bool
     *---------------------------------------------------------------- */
    public function deleteLocalTempFile($filename)
    {
        $path = getPathByKey('user_temp_uploads', ['{_uid}' => authUID()]);

        return $this->processDeleteFile($path, $filename);
    }

    /**
     * Delete media image
     *
     * @param  number  $productID
     * @return bool
     *---------------------------------------------------------------- */
    public function processDeleteFile($destinationPath, $filename = null)
    {
        $imageMediaPath = $destinationPath.'/'.$filename;
        // Check if image media exist & is deleted successfully
        if (File::exists($imageMediaPath) and File::delete($imageMediaPath)) {
            return true;
        }

        return false;
    }

    /**
     * Delete user all account data
     *
     * @return array
     *---------------------------------------------------------------- */
    public function deleteUserVendor()
    {
        $userVendorFolderPath = getPathByKey('user', ['{_uid}' => getUserUID()]);

        return $this->disk->deleteFolder($userVendorFolderPath);
    }

    /**
     * Process Upload Logo
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadTranslationFile($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('language_file');
        $this->disk = YesFileStorage::on('local');
        $uploadResult = $this->processUpload($inputFile, $logoFolderPath, $requestFor);
        $this->disk = YesFileStorage::on($this->currentDisk);

        return $uploadResult;
    }
    /**
     * Process Import Contacts
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadImportContactFile($inputFile)
    {
        $logoFolderPath = getPathByKey('vendor_contact_import');
        $this->disk = YesFileStorage::on('local');
        $uploadResult = $this->processUpload($inputFile, $logoFolderPath, 'vendor_contact_import');
        $this->disk = YesFileStorage::on($this->currentDisk);
        return $uploadResult;
    }

    /**
     * Delete older files
     *
     * @param  string  $dir
     * @param  int  $max_age  - default is 24 hours
     * @return void
     */
    public function deleteOldFiles($dir, $max_age = 3600) // 1 hours
    {
        $list = [];

        $limit = time() - $max_age;

        $dir = realpath($dir);

        if (! is_dir($dir)) {
            return;
        }

        $dh = opendir($dir);
        if ($dh === false) {
            return;
        }

        while (($file = readdir($dh)) !== false) {
            $file = $dir.'/'.$file;
            if (! is_file($file)) {
                continue;
            }

            if (filemtime($file) < $limit) {
                $list[] = $file;
                unlink($file);
            }
        }
        closedir($dh);

        return $list;
    }

    /**
     * Process Upload Profile Image
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadProfilePicture($inputFile, $requestFor, $pathValues = [])
    {
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);
        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $profileImageFolderPath = getPathByKey('profile_picture', $pathValues);

            return $this->resizeImageAndUpload($profileImageFolderPath, $fileName, [
                'height' => 360,
                'width' => 360,
            ]);

            return $this->engineFailedResponse([], __tr('Something went wrong while file moving.'));
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Common Process Upload Image
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadedFile($inputFile, $requestFor, $pathValues = [], $options = [])
    {
        $options = array_merge([
            'resize' => null,
        ], $options);
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);
        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $uploadedItemFolderPath = getPathByKey($requestFor, $pathValues);

            $processReaction = $this->resizeImageAndUpload($uploadedItemFolderPath, $fileName);
            if ($processReaction['reaction_code'] == 1) {
                return $this->engineSuccessResponse([
                    'folder_path' => $uploadedItemFolderPath,
                    'file_name' => $fileName,
                    'file_url' => getMediaUrl($uploadedItemFolderPath, $fileName),
                    'file_path' => $uploadedItemFolderPath.DIRECTORY_SEPARATOR.$fileName,
                ], __tr('File Uploaded Successfully'));
            }

            return $this->engineFailedResponse([], __tr('Something went wrong while file moving.'));
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Process Upload Hero Image
     *
     * @param array $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadHeroImage($inputData)
    {
        try {
            // Check if file is valid
            if (!isset($inputData['filepond']) || empty($inputData['filepond'])) {
                return $this->engineErrorResponse([
                    'message' => __tr('Invalid file uploaded.')
                ]);
            }

            $file = $inputData['filepond'];
            
            // Validate file size before processing
            if ($file->getSize() > 5242880) { // 5MB limit
                return $this->engineErrorResponse([
                    'message' => __tr('File size should not exceed 5MB.')
                ]);
            }

            // Get the current hero image path from the configuration
            $currentHeroImagePath = getAppSettings('hero_image');

            // Delete the previous hero image if it exists
            if ($currentHeroImagePath && file_exists(public_path($currentHeroImagePath))) {
                unlink(public_path($currentHeroImagePath));
            }

            $fileName = 'hero-image-' . time() . '.' . $file->getClientOriginalExtension();
            $path = 'media/hero/';
            $fullPath = public_path($path);
            
            // Ensure directory exists
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            // Store file
            if ($file->move($fullPath, $fileName)) {
                return $this->engineSuccessResponse([
                    'path' => getMediaUrl($path . $fileName),
                    'message' => __tr('Hero Image uploaded successfully.')
                ]);
            }

            return $this->engineErrorResponse([
                'message' => __tr('Something went wrong while uploading hero image.')
            ]);

        } catch (\Exception $e) {
            return $this->engineErrorResponse([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process Upload WhatsApp QR Code
     *
     * @param array $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadWhatsAppQR($inputData)
    {
        try {
            // Check if file is valid
            if (!isset($inputData['filepond']) || empty($inputData['filepond'])) {
                return $this->engineErrorResponse([
                    'message' => __tr('Invalid file uploaded.')
                ]);
            }

            $file = $inputData['filepond'];
            
            // Validate file size (1MB limit for QR codes)
            if ($file->getSize() > 1048576) { // 1MB limit
                return $this->engineErrorResponse([
                    'message' => __tr('QR code image size should not exceed 1MB.')
                ]);
            }

            // Validate file type
            $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return $this->engineErrorResponse([
                    'message' => __tr('Only PNG and JPEG images are allowed for QR code.')
                ]);
            }

            // Get the current QR code path from the configuration
            $currentQRPath = getAppSettings('whatsapp_qr_image');

            // Delete the previous QR code if it exists
            if ($currentQRPath && file_exists(public_path($currentQRPath))) {
                unlink(public_path($currentQRPath));
            }

            $fileName = 'whatsapp-qr-' . getVendorUid() . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = 'media/whatsapp-qr/';
            $fullPath = public_path($path);
            
            // Ensure directory exists
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            // Store file
            if ($file->move($fullPath, $fileName)) {
                return $this->engineSuccessResponse([
                    'path' => getMediaUrl($path . $fileName),
                    'message' => __tr('QR Image uploaded successfully.')
                ]);
            }
            

            return $this->engineErrorResponse([
                'message' => __tr('Something went wrong while uploading WhatsApp QR code.')
            ]);

        } catch (\Exception $e) {
            return $this->engineErrorResponse([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process Upload Media
     *
     * @param array $inputData
     * @param string $requestFor
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadMedia($inputData, $requestFor)
    {
        // Validate the input data
        if (!isset($inputData['filepond']) || empty($inputData['filepond'])) {
            return $this->engineErrorResponse([
                'message' => __tr('Invalid file uploaded.')
            ]);
        }

        $file = $inputData['filepond'];

        // Validate file size (5MB limit for general media)
        if ($file->getSize() > 5242880) { // 5MB limit
            return $this->engineErrorResponse([
                'message' => __tr('File size should not exceed 5MB.')
            ]);
        }

        // Validate file type (you can customize this based on your requirements)
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'video/mp4', 'audio/mpeg'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->engineErrorResponse([
                'message' => __tr('Invalid file type. Only images and videos are allowed.')
            ]);
        }

        // Define the upload path based on the requestFor parameter
        $uploadPath = getPathByKey($requestFor, ['{_uid}' => getVendorUid()]);

        // Ensure the directory exists
        if (!file_exists(public_path($uploadPath))) {
            mkdir(public_path($uploadPath), 0777, true);
        }

        // Generate a unique filename
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        // Move the file to the specified path
        if ($file->move(public_path($uploadPath), $fileName)) {
            return $this->engineSuccessResponse([
                'path' => getMediaUrl($uploadPath, $fileName),
                'fileName' => $fileName,
                'message' => __tr('File uploaded successfully.')
            ]);
        }

        return $this->engineErrorResponse([
            'message' => __tr('Something went wrong while uploading the file.')
        ]);
    }
}
