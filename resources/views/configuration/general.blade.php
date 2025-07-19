<!-- Page Heading -->
<h1><?= __tr('General Settings') ?></h1>
<!-- Page Heading -->
<hr>
<!-- General setting form -->
<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
<div class="row">
</div>
    <div class="">
        <div class="col-12">
            <div class="alert alert-default">
                {{  __tr('Upload will be processed automatically on valid selection.') }}
            </div>
        </div>
        <div class=" row col-lg-12 ">
             <!-- upload logo -->
            <div class="form-group float-left mr-5">
                <label for="lwUploadLogo"><?= __tr('Logo') ?></label>
            <input type="file" data-lw-plugin="lwUploader" data-label-idle="{{ __tr('Select New Logo') }}" data-allow-revert="true" data-instant-upload="true" data-action="<?= route('media.upload_logo') ?>" id="lwUploadLogo" data-callback="afterUploadedFile" data-default-image-url="<?= getAppSettings('logo_image_url') ?>">
            <small class="form-text text-muted">{{ __tr('Recommended size: 930x240 pixels | Format:png') }}</small>    
        </div>
             <!-- /upload logo -->
              <!-- upload small logo -->
            <div class="form-group mr-5">
                <label for="lwUploadSmallLogo"><?= __tr('Small Logo') ?></label>
            <input type="file" data-lw-plugin="lwUploader" data-label-idle="{{ __tr('Select New Small Logo') }}" data-allow-revert="true" data-instant-upload="true" data-action="<?= route('media.upload_small_logo') ?>" id="lwUploadSmallLogo" data-callback="afterUploadedFile" data-default-image-url="<?= getAppSettings('small_logo_image_url') ?>">
            <small class="form-text text-muted">{{ __tr('Recommended size: 520x460 pixels | Format:png') }}</small>    
        </div>
             <!-- /upload small logo -->
              <!-- upload favicon -->
            <div class="form-group float-right">
                <label for="lwUploadFavicon"><?= __tr('Favicon') ?></label>
                <input type="file" data-lw-plugin="lwUploader" data-label-idle="{{ __tr('Select New Favicon') }}" data-instant-upload="true" data-action="<?= route('media.upload_favicon') ?>" data-callback="afterUploadedFile" id="lwUploadFavicon" data-default-image-url="<?= getAppSettings('favicon_image_url') ?>">
                <small class="form-text text-muted">{{ __tr('Recommended size: 520x460 pixels ') }}</small>
                <small class="form-text text-muted">{{ __tr(' Format:png') }}</small>
            </div>
             <!-- /upload favicon -->
        </div>
    </div>
    <hr>
    <!-- Website Name -->
    <div class="form-group">
        <label for="lwWebsiteName"><?= __tr('Your Website Name') ?></label>
        <input type="text" class="form-control form-control-user" name="name" id="lwWebsiteName" value="<?= $configurationData['name'] ?>" required>
    </div>
    <!-- /Website Name -->
    <!-- Website Description -->
    <div class="form-group">
        <label for="lwWebsiteDescription"><?= __tr('Your Website Description') ?></label>
        <textarea name="description" id="lwWebsiteDescription" class="form-control" rows="2"><?= $configurationData['description'] ?></textarea>
    </div>
    <!-- /Website Description -->

    <fieldset>
        <legend>{{  __tr('Contact Settings') }}</legend>
            <!-- Contact Email -->
        <div class="form-group">
            <label for="lwContactEmail"><?= __tr('Contact Email') ?></label>
            <input type="email" class="form-control form-control-user" name="contact_email" id="lwContactEmail" value="<?= $configurationData['contact_email'] ?>">
            <small class="help-text">{{  __tr('It will be used to receive contact form emails') }}</small>
        </div>
        <!-- /Contact Email -->
        <!-- Contact details -->
        <div class="form-group">
            <label for="lwContactDetails"><?= __tr('Contact Details') ?></label>
            <textarea class="form-control form-control-user" name="contact_details" rows="4" id="lwContactDetails">{!! $configurationData['contact_details'] !!}</textarea>
            <small class="help-text">{{  __tr('Details added here will be shown on contact page') }}</small>
        </div>
        <!-- /Contact details -->
    </fieldset>

    <fieldset>
        <legend>{{  __tr('Additional Settings') }}</legend>
            <!-- Contact Phone -->
        
        <!-- /Contact Phone -->
       <!-- Demo Video Link -->
       <div class="form-group">
            <label for="lwDemoVideoLink"><?= __tr('Demo Video Link') ?></label>
            <input type="text" class="form-control form-control-user" name="demo_video_link" id="lwDemoVideoLink" value="<?= $configurationData['demo_video_link'] ?>">
            <small class="help-text">{{  __tr('It will be used to display Demo Video button in Homepage ') }}</small>
        </div>
        <!-- /Demo Video Link -->
         <!-- WhatsApp Demo Link -->
       <div class="form-group">
            <label for="lwWhatsAppDemoLink"><?= __tr('WhatsApp Demo Link') ?></label>
            <input type="text" class="form-control form-control-user" name="whatsapp_demo_link" id="lwWhatsAppDemoLink" value="<?= $configurationData['whatsapp_demo_link'] ?>">
            <small class="help-text">{{  __tr('It will be used to add link for Book Demo Button in Homepage ') }}</small>
        </div>
        <!-- /WhatsApp Demo Link -->
          <!-- Hero Image Upload -->
          <div class="form-group col-lg-6 px-0">
                <label for="lwUploadHeroImage"><?= __tr('Hero Image') ?></label>
                <input type="file" 
                    data-lw-plugin="lwUploader" 
                    data-label-idle="{{ __tr('Select New Hero Image') }}" 
                    data-instant-upload="true" 
                    data-action="<?= route('media.upload_hero_image') ?>" 
                    data-callback="afterUploadedFile" 
                    id="lwUploadHeroImage" 
                    data-default-image-url="<?= getAppSettings('hero_image') ?>">
                <small class="form-text text-muted">{{ __tr('Recommended size: 1200x1200 pixels | Format: .png') }}</small>
            </div>
             <!-- /Hero Image Upload -->

             <!-- WhatsApp QR Code Upload -->
             <div class="form-group col-lg-6 px-0">
                 <label for="lwUploadWhatsAppQR"><?= __tr('WhatsApp QR Code') ?></label>
                 <input type="file" 
                     data-lw-plugin="lwUploader" 
                     data-label-idle="{{ __tr('Select WhatsApp QR Code') }}" 
                     data-instant-upload="true" 
                     data-action="<?= route('media.upload_whatsapp_qr') ?>" 
                     data-callback="afterUploadedFile" 
                     id="lwUploadWhatsAppQR" 
                     data-default-image-url="<?= getAppSettings('whatsapp_qr_image') ?>">
                 <small class="form-text text-muted">{{ __tr('Upload your WhatsApp QR code image for easy scanning') }}</small>
             </div>
             <!-- /WhatsApp QR Code Upload -->
    </fieldset>

    <fieldset>
        <legend>{{  __tr('Localization') }}</legend>
        <!-- Select Timezone -->
    <div class="form-group">
        <label for="lwSelectTimezone"><?= __tr('Select Timezone') ?></label>
        <select data-lw-plugin="lwSelectize" data-label-field="name" data-selected="{{ $configurationData['timezone'] }}" data-search-field="{{ json_encode(['id','name']) }}" data-value-field="id" id="lwSelectTimezone" class="form-control form-control-user" name="timezone" required>
            @foreach($configurationData['timezone_list'] as $timezone)
            <option value="<?= $timezone['value'] ?>"><?= $timezone['text'] ?></option>
            @endforeach
        </select>
    </div>
    <!-- /Select Timezone -->

    <!-- Select Default language -->
    <div class="form-group mt-2">
        <label for="lwSelectDefaultLanguage"><?= __tr('Default Language') ?></label>
        <select id="lwSelectDefaultLanguage" data-lw-plugin="lwSelectize" placeholder="Default Language..." name="default_language">
            @if(!__isEmpty($configurationData['languageList']))
            @foreach($configurationData['languageList'] as $key => $language)
            <option value="<?= $language['id'] ?>" <?= $configurationData['default_language'] == $language['id'] ? 'selected' : '' ?> required><?= $language['name'] ?></option>
            @endforeach
            @endif
        </select>
    </div>
    <!-- /Select Default language -->
    </fieldset>

    <!-- Update Button -->
   <div class="mt-4">
    <a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
        <?= __tr('Save') ?>
    </a>
   </div>
    <!-- /Update Button -->
</form>
<!-- /General setting form -->
@push('appScripts')
<script>
    (function($) {
        'use strict';
    // After file successfully uploaded then this function is called
    window.afterUploadedFile = function (responseData) {
        var requestData = responseData.data;
        $('#lwUploadedLogo').attr('src', requestData.path);
    }
    })(jQuery);
</script>
@endpush