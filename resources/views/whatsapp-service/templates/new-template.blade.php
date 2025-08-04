@extends('layouts.app', ['title' => __tr('Create New Template')])

@section('page-style')
<style>
/* Carousel Template Styles */
.carousel-card {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.carousel-card:hover {
    border-color: #269C4C;
    box-shadow: 0 4px 12px rgba(38, 156, 76, 0.15);
}

.carousel-cards-container {
    max-height: 600px;
    overflow-y: auto;
}

.carousel-card-preview {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.carousel-cards-preview {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.carousel-cards-preview::-webkit-scrollbar {
    height: 6px;
}

.carousel-cards-preview::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.carousel-cards-preview::-webkit-scrollbar-thumb {
    background: #269C4C;
    border-radius: 3px;
}

.carousel-cards-preview::-webkit-scrollbar-thumb:hover {
    background: #1e7a3e;
}

.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    line-height: 1.2;
    border-radius: 0.2rem;
}

.modern-format-btn:hover {
    background-color: #269C4C;
    color: white;
    border-color: #269C4C;
}

.variables-container {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-top: 15px;
}

.variables-title {
    color: #269C4C;
    font-size: 1.1rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.modern-input-group .input-group-text {
    background-color: #269C4C;
    color: white;
    border-color: #269C4C;
}

.carousel-preview {
    max-width: 100%;
}

@media (max-width: 768px) {
    .carousel-card-preview {
        min-width: 160px;
        max-width: 160px;
    }
}
</style>
@endsection

@section('content')
@include('users.partials.header', [
'description' => '',
'class' => 'col-lg-7'
])
<div class="container-fluid mt-lg--6">
    <div class="row mt-3">
        <div class="col-12 mb-3">
            <div class="mt-5 d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0" style="color: #22A755;">
                    <i class="fas fa-clipboard-list me-2"></i>{{ __tr(' Create New Template') }}
                </h1>
                <div class="d-flex gap-2">
                    <a class="lw-btn btn btn-primary me-2" href="{{ route('vendor.whatsapp_service.templates.read.list_view') }}"><i class="fas fa-arrow-left me-2"></i>{{
                    __tr(' Back to Templates') }}</a>
                    <a href="https://business.facebook.com/business/help/2055875911147364" target="_blank" class="btn btn-modern-blue animate__animated animate__fadeIn">{{ __tr('Help') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12" x-data="{
    headerType:'',
    header_text_body:'',
    footer_text_body:'',
    text_body:'',
    enableHeaderVariableExample:false,
    newBodyTextInputFields:[],
    buttonModels:{},
    carouselCards:[],
    customButtons:{
        totalAllowedButtons:10,
        totalButtonsUsed:0,
        buttonUsesByTypes:{
            URL_BUTTON:0,
            URL_BUTTON_LIMIT:2,
            COPY_CODE:0,
            COPY_CODE_LIMIT:1,
            VOICE_CALL:0,
            VOICE_CALL_LIMIT:1,
            PHONE_NUMBER:0,
            PHONE_NUMBER_LIMIT:1
        },
        totalUrlButtonUsed:0,
        data:{},
    },
    addCarouselCard: function() {
        if(this.carouselCards.length < 10) {
            this.carouselCards.push({
                id: _.uniqueId('card_'),
                header_type: 'image', // Default to image
                body_text: 'Card ' + (this.carouselCards.length + 1) + ' description', // Default body text for each card
                buttons: [{
                    id: _.uniqueId('btn_'),
                    type: 'URL_BUTTON',
                    text: 'Visit',
                    url: 'https://example.com'
                }], // Default URL button for carousel
                uploaded_media_file_name: ''
            });

            // Set default body text for carousel if empty
            if(!this.text_body && this.headerType === 'carousel') {
                this.text_body = 'Check out our amazing products!';
            }

            // Initialize FilePond for new card after DOM update
            this.$nextTick(() => {
                this.initializeCarouselFilePonds();
            });
        }
    },
    initializeCarouselFilePonds: function() {
        // Re-initialize FilePond for all carousel file uploaders
        setTimeout(() => {
            if (window.initUploader) {
                window.initUploader();
            }
        }, 100);
    },
    removeCarouselCard: function(cardId) {
        this.carouselCards = this.carouselCards.filter(card => card.id !== cardId);
    },
    addCarouselCardButton: function(cardId, buttonType) {
        let card = this.carouselCards.find(c => c.id === cardId);
        if(card && card.buttons.length < 1) { // Limit to 1 button per card
            // Only URL_BUTTON type is supported now
            if(buttonType === 'URL_BUTTON') {
                let buttonData = {
                    id: _.uniqueId('btn_'),
                    type: 'URL_BUTTON',
                    text: 'Visit',
                    url: 'https://example.com'
                };
                card.buttons.push(buttonData);
            }
        }
    },
    removeCarouselCardButton: function(cardId, buttonId) {
        let card = this.carouselCards.find(c => c.id === cardId);
        if(card) {
            card.buttons = card.buttons.filter(btn => btn.id !== buttonId);
        }
    },
    addWhatsAppButtonOption : function(buttonType) {
        let uniqueBtnId = _.uniqueId();
        this.customButtons.data[uniqueBtnId] = {
            buttonType : buttonType,
            buttonIndex : uniqueBtnId
        };
        this.customButtons.totalButtonsUsed++;
        if((buttonType == 'URL_BUTTON') || (buttonType == 'DYNAMIC_URL_BUTTON')) {
            this.customButtons.buttonUsesByTypes['URL_BUTTON']++;
        } else if((buttonType == 'COPY_CODE')) {
            this.customButtons.buttonUsesByTypes['COPY_CODE']++;
        } else if((buttonType == 'VOICE_CALL')) {
            this.customButtons.buttonUsesByTypes['VOICE_CALL']++;
        } else if((buttonType == 'PHONE_NUMBER')) {
            this.customButtons.buttonUsesByTypes['PHONE_NUMBER']++;
        }
    }, deleteWhatsAppButtonOption : function(buttonIndex) {
        let buttonType = this.customButtons.data[buttonIndex]['buttonType'];
        if((buttonType == 'URL_BUTTON') || (buttonType == 'DYNAMIC_URL_BUTTON')) {
            this.customButtons.buttonUsesByTypes['URL_BUTTON']--;
        } else if((buttonType == 'COPY_CODE')) {
            this.customButtons.buttonUsesByTypes['COPY_CODE']-- ;
        } else if((buttonType == 'VOICE_CALL')) {
            this.customButtons.buttonUsesByTypes['VOICE_CALL']-- ;
        } else if((buttonType == 'PHONE_NUMBER')) {
            this.customButtons.buttonUsesByTypes['PHONE_NUMBER']-- ;
        }
        delete this.customButtons.data[buttonIndex];
        delete this.buttonModels[buttonIndex];
        this.customButtons.totalButtonsUsed--;
    }}">
        <div class="card shadow-lg border-0 rounded-lg animate__animated animate__fadeIn">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>{{ __tr(' Template Configuration') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <x-lw.form id="lwNewTemplateCreationForm"
                            :action="route('vendor.whatsapp_service.templates.write.create')">
                            <!-- Template Name -->
                            <x-lw.input-field type="text" id="lwTemplateNameField"
                                data-form-group-class="animate__animated animate__fadeInUp"
                                class="green-shadow-input"
                                :label="__tr('Template Name') . ' <i class=\'fas fa-question-circle text-info\' data-toggle=\'tooltip\' title=\'Template names must be lowercase with underscores instead of spaces\'></i>'"
                                name="template_name"
                                required="true"
                                oninput="this.value = this.value.replace(/\s+/g, '_').toLowerCase();" />
                            <span class="form-text text-muted mt-3 text-sm animate__animated animate__fadeIn animate__delay-1s">
                                <a class="alert alert-blue" target="_blank" href="https://developers.facebook.com/docs/whatsapp/message-templates/guidelines/">
                                    <i class="fas fa-lightbulb me-2 text-warning"></i>{{ __tr(' Template Formatting Help') }}
                                </a>
                            </span>
                            <!-- /Template Name -->
                            <!-- Template Language -->
                            <x-lw.input-field type="selectize" data-lw-plugin="lwSelectize" id="lwTemplateLanguageField"
                                data-form-group-class="animate__animated animate__fadeInUp animate__delay-1s" :label="__tr('Template Language Code')" name="language_code" required="true">
                                <x-slot name="selectOptions">
                                    <option value="af">Afrikaans</option>
                                    <option value="sq">Albanian</option>
                                    <option value="ar">Arabic</option>
                                    <option value="ar_EG">Arabic (EGY)</option>
                                    <option value="ar_AE">Arabic (UAE)</option>
                                    <option value="ar_LB">Arabic (LBN)</option>
                                    <option value="ar_MA">Arabic (MAR)</option>
                                    <option value="ar_QA">Arabic (QAT)</option>
                                    <option value="az">Azerbaijani</option>
                                    <option value="be_BY">Belarusian</option>
                                    <option value="bn">Bengali</option>
                                    <option value="bn_IN">Bengali (IND)</option>
                                    <option value="bg">Bulgarian</option>
                                    <option value="ca">Catalan</option>
                                    <option value="zh_CN">Chinese (CHN)</option>
                                    <option value="zh_HK">Chinese (HKG)</option>
                                    <option value="zh_TW">Chinese (TAI)</option>
                                    <option value="hr">Croatian</option>
                                    <option value="cs">Czech</option>
                                    <option value="da">Danish</option>
                                    <option value="prs_AF">Dari</option>
                                    <option value="nl">Dutch</option>
                                    <option value="nl_BE">Dutch (BEL)</option>
                                    <option value="en" selected>English</option>
                                    <option value="en_GB">English (UK)</option>
                                    <option value="en_US">English (US)</option>
                                    <option value="en_AE">English (UAE)</option>
                                    <option value="en_AU">English (AUS)</option>
                                    <option value="en_CA">English (CAN)</option>
                                    <option value="en_GHA">English (GHA)</option>
                                    <option value="en_IE">English (IRL)</option>
                                    <option value="en_IN">English (IND)</option>
                                    <option value="en_JM">English (JAM)</option>
                                    <option value="en_MY">English (MYS)</option>
                                    <option value="en_NZ">English (NZL)</option>
                                    <option value="en_QA">English (QAT)</option>
                                    <option value="en_SGP">English (SGP)</option>
                                    <option value="en_UG">English (UGA)</option>
                                    <option value="en_ZA">English (ZAF)</option>
                                    <option value="et">Estonian</option>
                                    <option value="fil">Filipino</option>
                                    <option value="fi">Finnish</option>
                                    <option value="fr">French</option>
                                    <option value="fr_BE">French (BEL)</option>
                                    <option value="fr_CA">French (CAN)</option>
                                    <option value="fr_CH">French (CHE)</option>
                                    <option value="fr_CI">French (CIV)</option>
                                    <option value="fr_MA">French (MAR)</option>
                                    <option value="ka">Georgian</option>
                                    <option value="de">German</option>
                                    <option value="de_AT">German (AUT)</option>
                                    <option value="de_CH">German (CHE)</option>
                                    <option value="el">Greek</option>
                                    <option value="gu">Gujarati</option>
                                    <option value="ha">Hausa</option>
                                    <option value="he">Hebrew</option>
                                    <option value="hi">Hindi</option>
                                    <option value="hu">Hungarian</option>
                                    <option value="id">Indonesian</option>
                                    <option value="ga">Irish</option>
                                    <option value="it">Italian</option>
                                    <option value="ja">Japanese</option>
                                    <option value="kn">Kannada</option>
                                    <option value="kk">Kazakh</option>
                                    <option value="rw_RW">Kinyarwanda</option>
                                    <option value="ko">Korean</option>
                                    <option value="ky_KG">Kyrgyz (Kyrgyzstan)</option>
                                    <option value="lo">Lao</option>
                                    <option value="lv">Latvian</option>
                                    <option value="lt">Lithuanian</option>
                                    <option value="mk">Macedonian</option>
                                    <option value="ms">Malay</option>
                                    <option value="ml">Malayalam</option>
                                    <option value="mr">Marathi</option>
                                    <option value="nb">Norwegian</option>
                                    <option value="ps_AF">Pashto</option>
                                    <option value="fa">Persian</option>
                                    <option value="pl">Polish</option>
                                    <option value="pt_BR">Portuguese (BR)</option>
                                    <option value="pt_PT">Portuguese (POR)</option>
                                    <option value="pa">Punjabi</option>
                                    <option value="ro">Romanian</option>
                                    <option value="ru">Russian</option>
                                    <option value="sr">Serbian</option>
                                    <option value="si_LK">Sinhala</option>
                                    <option value="sk">Slovak</option>
                                    <option value="sl">Slovenian</option>
                                    <option value="es">Spanish</option>
                                    <option value="es_AR">Spanish (ARG)</option>
                                    <option value="es_CL">Spanish (CHL)</option>
                                    <option value="es_CO">Spanish (COL)</option>
                                    <option value="es_CR">Spanish (CRI)</option>
                                    <option value="es_DO">Spanish (DOM)</option>
                                    <option value="es_EC">Spanish (ECU)</option>
                                    <option value="es_HN">Spanish (HND)</option>
                                    <option value="es_MX">Spanish (MEX)</option>
                                    <option value="es_PA">Spanish (PAN)</option>
                                    <option value="es_PE">Spanish (PER)</option>
                                    <option value="es_ES">Spanish (SPA)</option>
                                    <option value="es_UY">Spanish (URY)</option>
                                    <option value="sw">Swahili</option>
                                    <option value="sv">Swedish</option>
                                    <option value="ta">Tamil</option>
                                    <option value="te">Telugu</option>
                                    <option value="th">Thai</option>
                                    <option value="tr">Turkish</option>
                                    <option value="uk">Ukrainian</option>
                                    <option value="ur">Urdu</option>
                                    <option value="uz">Uzbek</option>
                                    <option value="vi">Vietnamese</option>
                                    <option value="zu">Zulu</option>
                                </x-slot>
                            </x-lw.input-field>
                            <div class="alert alert-blue my-3 animate__animated animate__fadeIn animate__delay-2s"
                                style="background-color: #269C4C; color: white;">
                                {{ __tr('While Authentication and Flow templates are supported for sending however you need to create/edit those templates on Meta.') }}
                                <a class="btn btn-sm float-right animate__animated animate__pulse animate__infinite animate__slower hover-effect"
                                    style="background-color: #103529; color: white; transition: all 0.3s ease;"
                                    target="_blank"
                                    href="https://business.facebook.com/wa/manage/message-templates/?waba_id={{ getVendorSettings('whatsapp_business_account_id') }}">
                                    <i class="fas fa-external-link-alt me-1"></i>{{ __tr(' Manage on Meta') }}
                                </a>
                            </div>
                            <!-- /Template Language -->
                            <x-lw.input-field type="selectize" data-lw-plugin="lwSelectize" id="lwSelectCategoryField"
                                data-form-group-class="animate__animated animate__fadeInUp animate__delay-2s" data-selected=" " :label="__tr('Category')" name="category">
                                <x-slot name="selectOptions">
                                    <option value="MARKETING">{{ __tr('MARKETING') }}</option>
                                    <option value="UTILITY">{{ __tr('UTILITY') }}</option>
                                    {{-- <option value="AUTHENTICATION">{{ __tr('AUTHENTICATION') }}</option> --}}
                                </x-slot>
                            </x-lw.input-field>
                            <fieldset class="modern-fieldset animate__animated animate__fadeIn animate__delay-3s"
                                style="background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #ddd;">

                                <input id="lwMediaFileName" type="hidden" value="" name="uploaded_media_file_name" />

                                <legend class="modern-legend" style="color: #269C4C;">
                                    {{ __tr('Header') }} <small>{{ __tr('(Optional)') }}</small>
                                </legend>

                                <x-lw.input-field x-model="headerType" type="selectize" id="lwMediaHeaderType"
                                    data-form-group-class="animate__animated animate__fadeInUp"
                                    data-selected=" " :label="__tr('Header Type')" name="media_header_type">
                                    <x-slot name="selectOptions">
                                        <option value="0">{{ __tr('None') }}</option>
                                        <optgroup label="{{ __tr('Text') }}">
                                            <option value="text">{{ __tr('Text') }}</option>
                                        </optgroup>
                                        <optgroup label="{{ __tr('Media') }}">
                                            <option value="image">{{ __tr('Image') }}</option>
                                            <option value="video">{{ __tr('Video') }}</option>
                                            <option value="document">{{ __tr('Document') }}</option>
                                            <option value="location">{{ __tr('Location') }}</option>
                                        </optgroup>
                                        <optgroup label="{{ __tr('Interactive') }}">
                                            <option value="carousel">{{ __tr('Media Card Carousel') }}</option>
                                        </optgroup>
                                    </x-slot>
                                </x-lw.input-field>

                                <div class="my-3">
                                    {{-- text --}}
                                    <div x-show="headerType == 'text'" class="form-group col-sm-12 animate__animated"
                                        :class="headerType == 'text' ? 'animate__fadeIn' : ''">
                                        <x-lw.input-field type="text" id="lwHeaderTextBody" data-form-group-class=""
                                            :label="__tr('Header Text')" x-model="header_text_body" name="header_text_body"
                                            style="box-shadow: 0 0 6px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'" />
                                        <div class="form-group text-right">
                                            <button :disabled="enableHeaderVariableExample" id="lwAddSinglePlaceHolder"
                                                class="btn btn-primary btn-sm modern-btn" type="button">
                                                <i class="fa fa-plus"></i> {{ __tr('Add Variable') }}
                                            </button>
                                        </div>
                                        <template x-if="enableHeaderVariableExample">
                                            <x-lw.input-field type="text" id="lwHeaderTextBodyExample"
                                                data-form-group-class="animate__animated animate__fadeIn"
                                                :label="__tr('Header Text Variable Example')" name="example_header_fields"
                                                style="box-shadow: 0 0 6px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                                onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'" />
                                        </template>
                                    </div>

                                    {{-- document --}}
                                    <div x-show="headerType == 'document'" class="form-group col-sm-12 animate__animated"
                                        :class="headerType == 'document' ? 'animate__fadeIn' : ''">
                                        <input id="lwDocumentMediaFilepond" type="file" data-allow-revert="true"
                                            data-label-idle="{{ __tr('Select Document') }}"
                                            class="lw-file-uploader modern-file-uploader"
                                            data-instant-upload="true"
                                            data-action="<?= route('media.upload_temp_media', 'whatsapp_document') ?>"
                                            data-file-input-element="#lwMediaFileName"
                                            data-allowed-media='<?= getMediaRestriction('whatsapp_document') ?>' />
                                    </div>

                                    {{-- image --}}
                                    <div x-show="headerType == 'image'" class="form-group col-sm-12 animate__animated"
                                        :class="headerType == 'image' ? 'animate__fadeIn' : ''">
                                        <input id="lwImageMediaFilepond" type="file" data-allow-revert="true"
                                            data-label-idle="{{ __tr('Select Image') }}"
                                            class="lw-file-uploader modern-file-uploader"
                                            data-instant-upload="true"
                                            data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>"
                                            data-file-input-element="#lwMediaFileName"
                                            data-allowed-media='<?= getMediaRestriction('whatsapp_image') ?>' />
                                    </div>

                                    {{-- video --}}
                                    <div x-show="headerType == 'video'" class="form-group col-sm-12 animate__animated"
                                        :class="headerType == 'video' ? 'animate__fadeIn' : ''">
                                        <input id="lwVideoMediaFilepond" type="file" data-allow-revert="true"
                                            data-label-idle="{{ __tr('Select Video') }}"
                                            class="lw-file-uploader modern-file-uploader"
                                            data-instant-upload="true"
                                            data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>"
                                            data-file-input-element="#lwMediaFileName"
                                            data-allowed-media='<?= getMediaRestriction('whatsapp_video') ?>' />
                                    </div>

                                    {{-- carousel --}}
                                    <div x-show="headerType == 'carousel'" class="form-group col-sm-12 animate__animated"
                                        :class="headerType == 'carousel' ? 'animate__fadeIn' : ''">
                                        <div class="carousel-cards-container">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0">{{ __tr('Carousel Cards') }}</h5>
                                                <div>
                                                    <!-- Debug and Reinit buttons hidden for cleaner UI -->
                                                    <button type="button" @click="addCarouselCard()"
                                                        :disabled="carouselCards.length >= 10"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fa fa-plus"></i> {{ __tr('Add Card') }}
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="alert alert-info mb-3">
                                                <i class="fa fa-info-circle"></i>
                                                {{ __tr('Carousel templates support 2-10 cards. Each card MUST have a header (image/video) and 1 URL button. All cards will use URL buttons for consistency.') }}
                                                <br><strong>{{ __tr('Tip:') }}</strong> {{ __tr('Image and video headers with URL buttons work best for engaging carousel templates!') }}
                                            </div>

                                            <template x-for="(card, cardIndex) in carouselCards" :key="card.id">
                                                <div class="card mb-3 carousel-card">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">
                                                            <i class="fa fa-layer-group"></i>
                                                            {{ __tr('Card') }} <span x-text="cardIndex + 1"></span>
                                                        </h6>
                                                        <button type="button" @click="removeCarouselCard(card.id)"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <!-- Card Header Type -->
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __tr('Header Type') }}</label>
                                                            <select x-model="card.header_type"
                                                                :name="'carousel_cards[' + cardIndex + '][header_type]'"
                                                                class="form-control">
                                                                <option value="image">{{ __tr('Image') }}</option>
                                                                <option value="video">{{ __tr('Video') }}</option>
                                                            </select>
                                                        </div>

                                                        <!-- Card Media Upload -->
                                                        <div x-show="card.header_type === 'image'" class="form-group">
                                                            <label class="form-label">
                                                                {{ __tr('Upload Image (Optional)') }}
                                                                <small class="text-muted">{{ __tr('- For preview only') }}</small>
                                                            </label>
                                                            <input :id="'lwCarouselImageFilepond_' + cardIndex"
                                                                type="file"
                                                                data-allow-revert="true"
                                                                data-label-idle="{{ __tr('Select Image for Card') }}"
                                                                class="lw-file-uploader modern-file-uploader"
                                                                data-instant-upload="true"
                                                                data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>"
                                                                :data-file-input-element="'#lwCarouselImageMediaFileName_' + cardIndex"
                                                                data-allowed-media='<?= getMediaRestriction('whatsapp_image') ?>' />
                                                            <input :id="'lwCarouselImageMediaFileName_' + cardIndex"
                                                                type="hidden"
                                                                :name="'carousel_cards[' + cardIndex + '][uploaded_media_file_name]'"
                                                                x-model="card.uploaded_media_file_name" />
                                                            <div x-show="card.uploaded_media_file_name" class="alert alert-success mt-2">
                                                                <i class="fa fa-check"></i> {{ __tr('Image uploaded successfully') }}
                                                                <small class="d-block">File: <span x-text="card.uploaded_media_file_name"></span></small>
                                                            </div>
                                                            <div x-show="!card.uploaded_media_file_name" class="alert alert-info mt-2">
                                                                <i class="fa fa-info-circle"></i> {{ __tr('No preview image uploaded. Media will be added when sending the template.') }}
                                                            </div>

                                                            <!-- Fallback manual input for debugging -->
                                                            <div class="mt-2">
                                                                <label class="form-label text-muted small">{{ __tr('Manual filename (if upload fails)') }}</label>
                                                                <input type="text" x-model="card.uploaded_media_file_name"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="{{ __tr('Enter uploaded filename manually') }}">
                                                                <small class="text-muted">{{ __tr('Use this only if automatic upload fails') }}</small>
                                                            </div>

                                                            <small class="text-muted">{{ __tr('Optional for template creation. Supported formats: JPG, PNG, GIF. Media will be uploaded when sending to users.') }}</small>
                                                        </div>

                                                        <div x-show="card.header_type === 'video'" class="form-group">
                                                            <label class="form-label">
                                                                {{ __tr('Upload Video (Optional)') }}
                                                                <small class="text-muted">{{ __tr('- For preview only') }}</small>
                                                            </label>
                                                            <input :id="'lwCarouselVideoFilepond_' + cardIndex"
                                                                type="file"
                                                                data-allow-revert="true"
                                                                data-label-idle="{{ __tr('Select Video for Card') }}"
                                                                class="lw-file-uploader modern-file-uploader"
                                                                data-instant-upload="true"
                                                                data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>"
                                                                :data-file-input-element="'#lwCarouselVideoMediaFileName_' + cardIndex"
                                                                data-allowed-media='<?= getMediaRestriction('whatsapp_video') ?>' />
                                                            <input :id="'lwCarouselVideoMediaFileName_' + cardIndex"
                                                                type="hidden"
                                                                :name="'carousel_cards[' + cardIndex + '][uploaded_media_file_name]'"
                                                                x-model="card.uploaded_media_file_name" />
                                                            <div x-show="card.uploaded_media_file_name" class="alert alert-success mt-2">
                                                                <i class="fa fa-check"></i> {{ __tr('Video uploaded successfully') }}
                                                                <small class="d-block">File: <span x-text="card.uploaded_media_file_name"></span></small>
                                                            </div>
                                                            <div x-show="!card.uploaded_media_file_name" class="alert alert-info mt-2">
                                                                <i class="fa fa-info-circle"></i> {{ __tr('No preview video uploaded. Media will be added when sending the template.') }}
                                                            </div>

                                                            <!-- Fallback manual input for debugging -->
                                                            <div class="mt-2">
                                                                <label class="form-label text-muted small">{{ __tr('Manual filename (if upload fails)') }}</label>
                                                                <input type="text" x-model="card.uploaded_media_file_name"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="{{ __tr('Enter uploaded filename manually') }}">
                                                                <small class="text-muted">{{ __tr('Use this only if automatic upload fails') }}</small>
                                                            </div>

                                                            <small class="text-muted">{{ __tr('Optional for template creation. Supported formats: MP4, AVI, MOV. Media will be uploaded when sending to users.') }}</small>
                                                        </div>



                                                        <!-- Card Body Text -->
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __tr('Card Body Text') }}</label>
                                                            <textarea x-model="card.body_text"
                                                                :name="'carousel_cards[' + cardIndex + '][body_text]'"
                                                                class="form-control"
                                                                rows="2"
                                                                maxlength="160"
                                                                placeholder="{{ __tr('Enter text for this card (max 160 characters)') }}"></textarea>
                                                            <small class="text-muted">{{ __tr('Each carousel card should have its own descriptive text') }}</small>
                                                        </div>

                                                        <!-- Card Buttons -->
                                                        <div class="form-group">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <label class="form-label mb-0">{{ __tr('Button (Required - 1 per card)') }} <span class="text-danger">*</span></label>
                                                                <div class="btn-group">
                                                                    <button type="button" @click="addCarouselCardButton(card.id, 'URL_BUTTON')"
                                                                        :disabled="card.buttons.length >= 1"
                                                                        class="btn btn-outline-primary btn-sm">
                                                                        {{ __tr('Add URL Button') }}
                                                                    </button>
                                                                    <!-- SPM and QUICK_REPLY buttons removed - only URL buttons supported -->
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ __tr('Carousel cards support only 1 URL button each. All cards should use URL buttons for consistency.') }}</small>

                                                            <template x-for="(button, buttonIndex) in card.buttons" :key="button.id">
                                                                <div class="card mb-2">
                                                                    <div class="card-body p-3">
                                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                                            <small class="text-muted" x-text="button.type"></small>
                                                                            <button type="button" @click="removeCarouselCardButton(card.id, button.id)"
                                                                                class="btn btn-danger btn-xs">
                                                                                <i class="fa fa-times"></i>
                                                                            </button>
                                                                        </div>

                                                                        <input type="hidden"
                                                                            :name="'carousel_cards[' + cardIndex + '][buttons][' + buttonIndex + '][type]'"
                                                                            :value="button.type">

                                                                        <div class="form-group mb-2">
                                                                            <input type="text" x-model="button.text"
                                                                                :name="'carousel_cards[' + cardIndex + '][buttons][' + buttonIndex + '][text]'"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="{{ __tr('Button text') }}"
                                                                                maxlength="25">
                                                                        </div>

                                                                        <div x-show="button.type === 'URL_BUTTON'" class="form-group mb-0">
                                                                            <input type="url" x-model="button.url"
                                                                                :name="button.type === 'URL_BUTTON' ? 'carousel_cards[' + cardIndex + '][buttons][' + buttonIndex + '][url]' : ''"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="{{ __tr('Button URL') }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <div x-show="carouselCards.length === 0" class="text-center py-4">
                                                <i class="fa fa-layer-group fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">{{ __tr('No carousel cards added yet. Click "Add Card" to get started.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="modern-fieldset animate__animated animate__fadeIn animate__delay-3s"
                                x-show="headerType !== 'carousel'"
                                style="background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #ddd;">
                                <legend class="modern-legend" style="color: #269C4C;">{{ __tr('Body') }}</legend>

                                <small class="text-muted mb-3 d-block fw-bold" style="font-weight: 600;">
                                    {{ __tr('Enter the text for your message in the language you\'ve selected.') }}
                                </small>


                                <div class="form-group">
                                    <label for="lwTemplateBody" class="form-label">{{ __tr('Body Text') }}</label>
                                    <textarea :name="headerType === 'carousel' ? '' : 'template_body'" id="lwTemplateBody" class="form-control modern-textarea"
                                        x-model="text_body" rows="3" required
                                        style="box-shadow: 0 0 8px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                        onfocus="this.style.boxShadow='0 0 12px 3px rgba(38, 156, 76, 0.6)'"
                                        onblur="this.style.boxShadow='0 0 8px rgba(38, 156, 76, 0.3)'">
                                </textarea>
                                </div>

                                <div class="form-group text-right mb-4">
                                    <div class="btn-group formatting-buttons">
                                        <button id="lwBoldBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-bold"></i>
                                        </button>
                                        <button id="lwItalicBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-italic"></i>
                                        </button>
                                        <button id="lwStrikeThroughBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-strikethrough"></i>
                                        </button>
                                        <button id="lwCodeBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-code"></i>
                                        </button>
                                    </div>
                                    <button id="lwAddPlaceHolder" class="btn btn-primary btn-sm modern-btn ms-2" type="button">
                                        <i class="fa fa-plus"></i> {{ __tr('Add Variables') }}
                                    </button>
                                </div>

                                <div>
                                    <template x-if="_.size(newBodyTextInputFields)">
                                        <div class="variables-container animate__animated animate__fadeIn">
                                            <h4 class="variables-title">{{ __tr('Samples Text') }}</h4>
                                            <template x-for="(item, index) in newBodyTextInputFields" :key="index">
                                                <div class="form-group animate__animated animate__fadeInUp"
                                                    :style="'animation-delay: ' + (index * 0.1) + 's'">
                                                    <div class="input-group modern-input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <span x-text="item.text_variable"></span>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control"
                                                            x-bind:name="'example_body_fields[' + index + ']'"
                                                            required="required"
                                                            style="box-shadow: 0 0 6px rgba(38,156,76,0.3); transition: box-shadow 0.4s ease;"
                                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'" />
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </fieldset>

                            <!-- Carousel Body Section -->
                            <fieldset class="modern-fieldset animate__animated animate__fadeIn animate__delay-3s"
                                x-show="headerType === 'carousel'"
                                style="background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #ddd;">
                                <legend class="modern-legend" style="color: #269C4C;">{{ __tr('Carousel Message Body') }} <span class="text-danger">*</span></legend>

                                <small class="text-muted mb-3 d-block fw-bold" style="font-weight: 600;">
                                    {{ __tr('Enter the main message text that will appear above the carousel cards. This is required for carousel templates.') }}
                                </small>

                                <div class="form-group">
                                    <label for="lwCarouselTemplateBody" class="form-label">{{ __tr('Message Text') }} <span class="text-danger">*</span></label>
                                    <textarea :name="headerType === 'carousel' ? 'template_body' : ''" id="lwCarouselTemplateBody" class="form-control modern-textarea"
                                        x-model="text_body" rows="3" required
                                        style="box-shadow: 0 0 8px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                        onfocus="this.style.boxShadow='0 0 12px 3px rgba(38, 156, 76, 0.6)'"
                                        onblur="this.style.boxShadow='0 0 8px rgba(38, 156, 76, 0.3)'"
                                        placeholder="{{ __tr('e.g., Check out our amazing products!') }}">
                                    </textarea>
                                </div>

                                <div class="form-group text-right mb-4">
                                    <div class="btn-group formatting-buttons">
                                        <button id="lwCarouselBoldBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-bold"></i>
                                        </button>
                                        <button id="lwCarouselItalicBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-italic"></i>
                                        </button>
                                        <button id="lwCarouselStrikeThroughBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-strikethrough"></i>
                                        </button>
                                        <button id="lwCarouselCodeBtn" class="btn btn-light btn-sm modern-format-btn" type="button">
                                            <i class="fa fa-code"></i>
                                        </button>
                                    </div>
                                    <button id="lwCarouselAddPlaceHolder" class="btn btn-primary btn-sm modern-btn ms-2" type="button">
                                        <i class="fa fa-plus"></i> {{ __tr('Add Variables') }}
                                    </button>
                                </div>

                                <div>
                                    <template x-if="_.size(newBodyTextInputFields)">
                                        <div class="variables-container animate__animated animate__fadeIn">
                                            <h4 class="variables-title">{{ __tr('Samples Text') }}</h4>
                                            <template x-for="(item, index) in newBodyTextInputFields" :key="index">
                                                <div class="form-group animate__animated animate__fadeInUp"
                                                    :style="'animation-delay: ' + (index * 0.1) + 's'">
                                                    <div class="input-group modern-input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <span x-text="item.text_variable"></span>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control"
                                                            x-bind:name="'example_body_fields[' + index + ']'"
                                                            required="required"
                                                            style="box-shadow: 0 0 6px rgba(38,156,76,0.3); transition: box-shadow 0.4s ease;"
                                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'" />
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </fieldset>

                            <!-- Footer hidden for carousel templates -->
                            <div x-show="headerType !== 'carousel'">
                                <x-lw.input-field
                                    type="text"
                                    id="lwTemplateFooter"
                                    x-model="footer_text_body"
                                    name="template_footer"
                                    :label="__tr('Footer (Optional)')"
                                    :helpText="__tr('Add a short line of text to the bottom of your message template.')"
                                    data-form-group-class="animate__animated animate__fadeInUp animate__delay-4s"
                                    data-label-class="text-success fw-bold"
                                    data-help-class="text-dark fw-semibold"
                                    style="background:#fff; padding:10px; border-radius:8px; box-shadow:0 0 6px rgba(38,156,76,0.2); transition:box-shadow 0.3s ease;"
                                    onfocusin="this.style.boxShadow='0 0 12px 2px rgba(38,156,76,0.4)'"
                                    onfocusout="this.style.boxShadow='0 0 6px rgba(38,156,76,0.2)'"
                                />
                            </div>

                            <!-- Buttons section hidden for carousel templates -->
                            <fieldset x-show="headerType !== 'carousel'" class="modern-fieldset animate__animated animate__fadeIn animate__delay-4s"
                                style="background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #ddd;">

                                <legend class="modern-legend" style="color: #269C4C;">
                                    {{ __tr('Buttons') }} <small>{{ __tr('(Optional)') }}</small>
                                </legend>

                                <div class="mb-4 d-block">
                                    <p class="text-dark fw-bolder" style="font-size: 1.05rem;">
                                        {{ __tr('Create buttons that let customers respond to your message or take action.') }}
                                    </p>
                                </div>

                                <div class="lw-buttons-container">
                                    <div class="buttons-list">
                                        <template x-for="customButtonData in customButtons.data">
                                            <div class="card shadow-sm border-0 rounded mb-3 animate__animated animate__fadeInUp">
                                                <h3 class="card-header d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <template x-if="customButtonData.buttonType == 'QUICK_REPLY'">
                                                            <span><i class="fa fa-reply me-2"></i>{{ __tr('Quick Reply Button') }}</span>
                                                        </template>
                                                        <template x-if="customButtonData.buttonType == 'PHONE_NUMBER'">
                                                            <span><i class="fa fa-phone-alt me-2"></i>{{ __tr('Phone Number Button') }}</span>
                                                        </template>
                                                        <template x-if="customButtonData.buttonType == 'URL_BUTTON'">
                                                            <span><i class="fa fa-link me-2"></i>{{ __tr('URL Button') }}</span>
                                                        </template>
                                                        <template x-if="customButtonData.buttonType == 'DYNAMIC_URL_BUTTON'">
                                                            <span><i class="fa fa-link me-2"></i>{{ __tr('Dynamic URL Button') }}</span>
                                                        </template>
                                                        <template x-if="customButtonData.buttonType == 'VOICE_CALL'">
                                                            <span><i class="fab fa-whatsapp me-2"></i>{{ __tr('WhatsApp Call Button') }}</span>
                                                        </template>
                                                        <template x-if="customButtonData.buttonType == 'COPY_CODE'">
                                                            <span><i class="fa fa-copy me-2"></i>{{ __tr('Coupon Code Copy Button') }}</span>
                                                        </template>
                                                    </span>
                                                    <button @click.prevent="deleteWhatsAppButtonOption(customButtonData.buttonIndex)"
                                                        class="btn btn-sm btn-danger" type="button">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </h3>

                                                <div class="card-body">
                                                    <input type="hidden"
                                                        x-bind:name="'message_buttons['+customButtonData.buttonIndex+'][type]'"
                                                        x-bind:value="customButtonData.buttonType">

                                                    <template x-if="_.includes(['QUICK_REPLY','PHONE_NUMBER', 'URL_BUTTON', 'VOICE_CALL','DYNAMIC_URL_BUTTON'], customButtonData.buttonType)">
                                                        <x-lw.input-field x-bind:id="customButtonData.buttonIndex"
                                                            type="text" data-form-group-class="mt-4"
                                                            :label="__tr('Button Text')"
                                                            x-model="buttonModels[customButtonData.buttonIndex]"
                                                            x-bind:name="'message_buttons['+customButtonData.buttonIndex+'][text]'"
                                                            style="box-shadow: 0 0 6px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'">
                                                            <x-slot name="prepend">
                                                                <span class="input-group-text"><i class="fa fa-font"></i></span>
                                                            </x-slot>
                                                        </x-lw.input-field>
                                                    </template>

                                                    <template x-if="customButtonData.buttonType == 'PHONE_NUMBER'">
                                                        <x-lw.input-field x-bind:id="customButtonData.buttonIndex"
                                                            type="number"
                                                            :label="__tr('Phone Number')"
                                                            x-bind:name="'message_buttons['+customButtonData.buttonIndex+'][phone_number]'"
                                                            style="box-shadow: 0 0 6px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'">
                                                            <x-slot name="prepend">
                                                                <span class="input-group-text"><i class="fa fa-phone-alt"></i></span>
                                                            </x-slot>
                                                        </x-lw.input-field>
                                                    </template>

                                                    <template x-if="customButtonData.buttonType == 'URL_BUTTON'">
                                                        <x-lw.input-field x-bind:id="customButtonData.buttonIndex"
                                                            type="url"
                                                            :label="__tr('Website URL')"
                                                            x-bind:name="'message_buttons['+customButtonData.buttonIndex+'][url]'"
                                                            style="box-shadow: 0 0 6px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'">
                                                            <x-slot name="prepend">
                                                                <span class="input-group-text"><i class="fa fa-link"></i></span>
                                                            </x-slot>
                                                        </x-lw.input-field>
                                                    </template>

                                                    <template x-if="customButtonData.buttonType == 'DYNAMIC_URL_BUTTON'">
                                                        <x-lw.input-field x-bind:id="customButtonData.buttonIndex"
                                                            type="url"
                                                            :label="__tr('Website URL')"
                                                            x-bind:name="'message_buttons['+customButtonData.buttonIndex+'][url]'"
                                                            style="box-shadow: 0 0 6px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'">
                                                            <x-slot name="prepend">
                                                                <span class="input-group-text"><i class="fa fa-link"></i></span>
                                                            </x-slot>
                                                            <x-slot name="append">
                                                                <span class="input-group-text">@{{1}}</span>
                                                            </x-slot>
                                                        </x-lw.input-field>
                                                    </template>

                                                    <template x-if="_.includes(['COPY_CODE', 'DYNAMIC_URL_BUTTON'], customButtonData.buttonType)">
                                                        <x-lw.input-field x-bind:id="customButtonData.buttonIndex"
                                                            type="text"
                                                            :label="__tr('Example')"
                                                            x-bind:name="'message_buttons['+customButtonData.buttonIndex+'][example]'"
                                                            style="box-shadow: 0 0 6px rgba(38, 156, 76, 0.3); transition: box-shadow 0.4s ease;"
                                                            onfocus="this.style.boxShadow='0 0 10px 2px rgba(38,156,76,0.6)'"
                                                            onblur="this.style.boxShadow='0 0 6px rgba(38,156,76,0.3)'">
                                                        </x-lw.input-field>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Action Buttons with gap -->
                                    <div class="mt-4 button-actions">
                                        <div class="d-flex flex-wrap gap-3">
                                            <button :disabled="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons"
                                                class="btn btn-success btn-sm text-white modern-btn"
                                                style="background-color: #0B5D1E; border-color: #0B5D1E; margin-right: 6px;"
                                                @click.prevent="addWhatsAppButtonOption('QUICK_REPLY')">
                                                <i class="fa fa-reply me-1"></i> {{ __tr('Quick Reply') }}
                                            </button>

                                            <button :disabled="(customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons) || (customButtons.buttonUsesByTypes.PHONE_NUMBER >= customButtons.buttonUsesByTypes.PHONE_NUMBER_LIMIT)"
                                                class="btn btn-success btn-sm text-white modern-btn"
                                                style="background-color: #0B5D1E; border-color: #0B5D1E;margin-right: 6px;"
                                                @click.prevent="addWhatsAppButtonOption('PHONE_NUMBER')">
                                                <i class="fa fa-phone-alt me-1"></i> {{ __tr('Phone Number') }}
                                            </button>

                                            <button :disabled="(customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons) || (customButtons.buttonUsesByTypes.COPY_CODE >= customButtons.buttonUsesByTypes.COPY_CODE_LIMIT)"
                                                class="btn btn-success btn-sm text-white modern-btn"
                                                style="background-color: #0B5D1E; border-color: #0B5D1E;margin-right: 6px;"
                                                @click.prevent="addWhatsAppButtonOption('COPY_CODE')">
                                                <i class="fa fa-clipboard me-1"></i> {{ __tr('Copy Code') }}
                                            </button>

                                            <button :disabled="(customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons) || (customButtons.buttonUsesByTypes.URL_BUTTON >= customButtons.buttonUsesByTypes.URL_BUTTON_LIMIT)"
                                                class="btn btn-success btn-sm text-white modern-btn"
                                                style="background-color: #0B5D1E; border-color: #0B5D1E;margin-right: 6px;"
                                                @click.prevent="addWhatsAppButtonOption('URL_BUTTON')">
                                                <i class="fa fa-link me-1"></i> {{ __tr('URL Button') }}
                                            </button>

                                            <button :disabled="(customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons) || (customButtons.buttonUsesByTypes.URL_BUTTON >= customButtons.buttonUsesByTypes.URL_BUTTON_LIMIT)"
                                                class="btn btn-success btn-sm text-white modern-btn"
                                                style="background-color: #0B5D1E; border-color: #0B5D1E;"
                                                @click.prevent="addWhatsAppButtonOption('DYNAMIC_URL_BUTTON')">
                                                <i class="fa fa-link me-1"></i> {{ __tr('Dynamic URL') }}
                                            </button>
                                        </div>


                                        <!-- Limit reached alert -->
                                        <template x-if="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons">
                                            <div class="alert alert-danger mt-4 animate__animated animate__fadeIn">
                                                {{ __tr('You have reached maximum buttons allowed by Meta for template') }}
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="form-group mt-4 animate__animated animate__fadeInUp animate__delay-5s">
                                <button type="submit"
                                    class="btn btn-lg w-100 text-white submit-btn"
                                    style="background-color: #0B5D1E; border-color: #0B5D1E; transition: all 0.3s ease;">
                                    {{ __('Submit Template') }} <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </div>

                        </x-lw.form>
                    </div>
                    <div class="col-md-1 d-none d-md-block"></div>
                    <div class="col-md-4">
                        <div class="lw-whatsapp-template-create-preview animate__animated animate__fadeIn animate__delay-3s">
                            <h3 class="preview-title">{{ __tr('Template Preview') }}</h3>
                            <div class="lw-whatsapp-preview-container">
                                <img class="lw-whatsapp-preview-bg" src="{{ asset('imgs/wa-message-bg.png') }}" alt="">
                                <div class="lw-whatsapp-preview">
                                    <!-- Regular Template Preview -->
                                    <div x-show="headerType !== 'carousel'" class="card shadow-lg">
                                        <div x-show="headerType && (headerType != 'text')" class="lw-whatsapp-header-placeholder">
                                            <i x-show="headerType == 'video'" class="fa fa-5x fa-play-circle text-white"></i>
                                            <i x-show="headerType == 'image'" class="fa fa-5x fa-image text-white"></i>
                                            <i x-show="headerType == 'location'" class="fa fa-5x fa-map-marker-alt text-white"></i>
                                            <i x-show="headerType == 'document'" class="fa fa-5x fa-file-alt text-white"></i>
                                        </div>
                                        <div x-show="headerType == 'location'" class="lw-whatsapp-location-meta bg-secondary p-2">
                                            <small>@{{location_name}}</small><br>
                                            <small>@{{address}}</small>
                                        </div>
                                        <div x-show="headerType == 'text'" class="lw-whatsapp-body mb--3">
                                            <strong x-text="header_text_body"></strong>
                                        </div>
                                        <div class="lw-whatsapp-body lw-ws-pre-line" x-html="appFuncs.formatWhatsAppText(text_body)"></div>
                                        <div class="lw-whatsapp-footer text-muted" x-text="footer_text_body"></div>
                                        <div class="card-footer lw-whatsapp-buttons">
                                            <div class="list-group list-group-flush lw-whatsapp-buttons">
                                                <template x-for="(customButtonData, index) in customButtons.data" :key="index">
                                                    <div>
                                                        <div class="list-group-item modern-button-preview">
                                                            <template x-if="customButtonData.buttonType == 'QUICK_REPLY'">
                                                                <i class="fa fa-reply me-2"></i>
                                                            </template>
                                                            <template x-if="customButtonData.buttonType == 'PHONE_NUMBER'">
                                                                <i class="fa fa-phone-alt me-2"></i>
                                                            </template>
                                                            <template x-if="customButtonData.buttonType == 'URL_BUTTON'">
                                                                <i class="fas fa-external-link-square-alt me-2"></i>
                                                            </template>
                                                            <template x-if="customButtonData.buttonType == 'DYNAMIC_URL_BUTTON'">
                                                                <i class="fas fa-external-link-square-alt me-2"></i>
                                                            </template>
                                                            <template x-if="customButtonData.buttonType == 'VOICE_CALL'">
                                                                <i class="fab fa-whatsapp me-1"></i><i class="fa fa-phone-alt me-2"></i>
                                                            </template>
                                                            <template x-if="customButtonData.buttonType == 'COPY_CODE'">
                                                                <span><i class="fa fa-copy me-2"></i> {{ __tr('Copy Code') }}</span>
                                                            </template>
                                                            <span x-text="buttonModels[customButtonData.buttonIndex]"></span>
                                                        </div>
                                                        <template x-if="index == 3">
                                                            <div class="list-group-item"><i class="fa fa-menu me-2"></i> {{ __tr('See all options') }} <br><small class="text-orange">{{ __tr('More than 3 buttons will be shown in the list by clicking') }}</small></div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Carousel Template Preview -->
                                    <div x-show="headerType === 'carousel'" class="carousel-preview">
                                        <!-- Main message body -->
                                        <div x-show="text_body" class="card shadow-lg mb-3">
                                            <div class="lw-whatsapp-body lw-ws-pre-line p-3" x-html="appFuncs.formatWhatsAppText(text_body)"></div>
                                            <div x-show="footer_text_body" class="lw-whatsapp-footer text-muted px-3 pb-2" x-text="footer_text_body"></div>
                                        </div>

                                        <!-- Carousel Cards -->
                                        <div x-show="carouselCards.length > 0" class="carousel-cards-preview">
                                            <div class="d-flex overflow-auto pb-2" style="gap: 10px;">
                                                <template x-for="(card, index) in carouselCards" :key="card.id">
                                                    <div class="card shadow-sm carousel-card-preview" style="min-width: 200px; max-width: 200px;">
                                                        <!-- Card Header -->
                                                        <div class="lw-whatsapp-header-placeholder" style="height: 120px;">
                                                            <div x-show="card.header_type === 'image'">
                                                                <i x-show="card.uploaded_media_file_name" class="fa fa-3x fa-image text-white"></i>
                                                                <div x-show="!card.uploaded_media_file_name" class="text-center p-3">
                                                                    <i class="fa fa-2x fa-image text-white"></i>
                                                                    <small class="d-block text-white mt-1">{{ __tr('Image Placeholder') }}</small>
                                                                </div>
                                                            </div>
                                                            <div x-show="card.header_type === 'video'">
                                                                <i x-show="card.uploaded_media_file_name" class="fa fa-3x fa-play-circle text-white"></i>
                                                                <div x-show="!card.uploaded_media_file_name" class="text-center p-3">
                                                                    <i class="fa fa-2x fa-play-circle text-white"></i>
                                                                    <small class="d-block text-white mt-1">{{ __tr('Video Placeholder') }}</small>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <!-- Card Body Text -->
                                                        <div class="card-body p-2">
                                                            <div x-show="card.body_text" class="lw-whatsapp-body" style="font-size: 12px;" x-text="card.body_text"></div>
                                                            <div x-show="!card.body_text" class="text-muted" style="font-size: 11px;">{{ __tr('No card text') }}</div>
                                                        </div>

                                                        <!-- Card Buttons -->
                                                        <div x-show="card.buttons.length > 0" class="card-footer p-1">
                                                            <template x-for="button in card.buttons" :key="button.id">
                                                                <div class="btn btn-outline-primary btn-sm btn-block mb-1" style="font-size: 11px;">
                                                                    <i x-show="button.type === 'QUICK_REPLY'" class="fa fa-reply me-1"></i>
                                                                    <i x-show="button.type === 'URL_BUTTON'" class="fa fa-external-link-alt me-1"></i>
                                                                    <i x-show="button.type === 'SPM'" class="fa fa-eye me-1"></i>
                                                                    <span x-text="button.text || button.type"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fa fa-info-circle"></i>
                                                {{ __tr('Swipe horizontally to view all cards') }}
                                            </small>
                                        </div>

                                        <!-- Empty state -->
                                        <div x-show="carouselCards.length === 0" class="text-center py-4">
                                            <i class="fa fa-layer-group fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">{{ __tr('Add carousel cards to see preview') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection()
@push('appScripts')
<?= __yesset(
    [
        'dist/js/whatsapp-template.js',
    ],
    true,
) ?>
@endpush

@push('styles')
<style>
    /* Blue gradient button */
    .btn-modern-blue {
        background: linear-gradient(135deg, #0061ff, #60efff) !important;
        border: 1px solid !important;
        color: white !important;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 97, 255, 0.2);
    }

    .btn-modern-blue:hover {
        background: linear-gradient(135deg, #0052d9, #45c7ff) !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 97, 255, 0.3);
    }

    /* Add space between buttons with gap utility */
    .gap-2 {
        gap: 0.5rem;
    }

    /* Modern card styling */
    .card.shadow-lg {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.3s ease;
    }

    .card.shadow-lg:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }

    .card-header.bg-gradient-primary {
        background: linear-gradient(135deg, #22A755, #1a8040) !important;
        border-bottom: none;
    }

    /* Modern fieldset styling */
    .modern-fieldset {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
    }

    .modern-fieldset:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border-color: #22A755;
    }

    .modern-legend {
        font-weight: 600;
        font-size: 1.1rem;
        color: #22A755;
        padding: 0 10px;
        width: auto;
    }

    /* Modern form controls */
    .form-control,
    .selectize-input {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .selectize-input.focus {
        border-color: #22A755;
        box-shadow: 0 0 0 0.2rem rgba(34, 167, 85, 0.25);
    }

    .modern-textarea {
        min-height: 120px;
        line-height: 1.5;
    }

    /* Add animate.css for the fadeIn effect */
    @import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

    /* New Meta button style */
    .btn-meta-green {
        background-color: #103529;
        color: white;
        border: none;
    }

    .btn-meta-green:hover {
        background-color: #0d2b22;
        color: white;
    }

    /* Hover effect for Meta button */
    .hover-effect:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        background-color: #21C063 !important;
    }

    /* Green shadow for input field */
    .green-shadow-input {
        box-shadow: 0 0 5px rgba(34, 167, 85, 0.3) !important;
        border-color: #22A755 !important;
    }

    .green-shadow-input:focus {
        box-shadow: 0 0 10px rgba(34, 167, 85, 0.5) !important;
        border-color: #22A755 !important;
    }

    /* Apply green shadow to the form-group that contains the input */
    #lwTemplateNameField-form-group {
        background-color: rgba(34, 167, 85, 0.05);
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 0 15px rgba(34, 167, 85, 0.1);
    }
</style>
@endpush

<style>
    @media (max-width: 992px) {
        .col-xl-12 .d-flex.justify-content-between {
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 1.5rem;
            width: 100%;
        }

        .col-xl-12 .d-flex.justify-content-between>div {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carousel template formatting buttons
    const carouselBoldBtn = document.getElementById('lwCarouselBoldBtn');
    const carouselItalicBtn = document.getElementById('lwCarouselItalicBtn');
    const carouselStrikeThroughBtn = document.getElementById('lwCarouselStrikeThroughBtn');
    const carouselCodeBtn = document.getElementById('lwCarouselCodeBtn');
    const carouselAddPlaceHolder = document.getElementById('lwCarouselAddPlaceHolder');
    const carouselTemplateBody = document.getElementById('lwCarouselTemplateBody');

    if (carouselBoldBtn && carouselTemplateBody) {
        carouselBoldBtn.addEventListener('click', function() {
            insertFormatting(carouselTemplateBody, '*', '*');
        });
    }

    if (carouselItalicBtn && carouselTemplateBody) {
        carouselItalicBtn.addEventListener('click', function() {
            insertFormatting(carouselTemplateBody, '_', '_');
        });
    }

    if (carouselStrikeThroughBtn && carouselTemplateBody) {
        carouselStrikeThroughBtn.addEventListener('click', function() {
            insertFormatting(carouselTemplateBody, '~', '~');
        });
    }

    if (carouselCodeBtn && carouselTemplateBody) {
        carouselCodeBtn.addEventListener('click', function() {
            insertFormatting(carouselTemplateBody, '```', '```');
        });
    }

    if (carouselAddPlaceHolder && carouselTemplateBody) {
        carouselAddPlaceHolder.addEventListener('click', function() {
            insertPlaceholder(carouselTemplateBody);
        });
    }

    function insertFormatting(textarea, startTag, endTag) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        const replacement = startTag + selectedText + endTag;

        textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + startTag.length, start + startTag.length + selectedText.length);

        // Trigger Alpine.js update
        textarea.dispatchEvent(new Event('input'));
    }

    function insertPlaceholder(textarea) {
        const start = textarea.selectionStart;
        const placeholderCount = (textarea.value.match(/\{\{\d+\}\}/g) || []).length + 1;
        const placeholder = '{{' + placeholderCount + '}}';

        textarea.value = textarea.value.substring(0, start) + placeholder + textarea.value.substring(start);
        textarea.focus();
        textarea.setSelectionRange(start + placeholder.length, start + placeholder.length);

        // Trigger Alpine.js update
        textarea.dispatchEvent(new Event('input'));
    }

    // Function to check FilePond status and sync data
    function checkFilePondStatus() {
        console.log('=== FilePond Status Check ===');

        // Check all FilePond instances
        const filePondElements = document.querySelectorAll('.lw-file-uploader');
        filePondElements.forEach((element, index) => {
            console.log(`FilePond Element ${index}:`, {
                id: element.id,
                hasFilePond: !!element.filepond,
                files: element.filepond ? element.filepond.getFiles() : 'No FilePond instance'
            });
        });

        // Check hidden inputs
        const hiddenInputs = document.querySelectorAll('input[name*="uploaded_media_file_name"]');
        hiddenInputs.forEach((input, index) => {
            console.log(`Hidden Input ${index}:`, {
                name: input.name,
                value: input.value,
                id: input.id
            });
        });

        // Check Alpine.js data
        const alpineComponent = document.querySelector('[x-data]');
        if (alpineComponent && alpineComponent._x_dataStack) {
            const data = alpineComponent._x_dataStack[0];
            console.log('Alpine.js carousel cards:', data.carouselCards);
        }
    }

    // Handle file upload completion for carousel cards
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize FilePond for existing elements
        if (window.initUploader) {
            window.initUploader();
        }

        // Listen for FilePond file upload events
        document.addEventListener('FilePond:addfile', function(e) {
            console.log('File added:', e.detail);
        });

        document.addEventListener('FilePond:processfile', function(e) {
            console.log('File processed:', e.detail);
            const pond = e.detail.pond;
            const file = e.detail.file;

            // Check if this is a carousel file upload
            if (pond.element && pond.element.id && pond.element.id.includes('lwCarousel')) {
                const fileInputId = pond.element.getAttribute('data-file-input-element');
                console.log('Processing carousel file upload:', {
                    pondId: pond.element.id,
                    fileInputId: fileInputId,
                    serverId: file.serverId
                });

                if (fileInputId) {
                    const hiddenInput = document.querySelector(fileInputId);
                    if (hiddenInput && file.serverId) {
                        // Set the hidden input value
                        hiddenInput.value = file.serverId;
                        hiddenInput.dispatchEvent(new Event('input'));
                        hiddenInput.dispatchEvent(new Event('change'));

                        // Extract card index from the input ID
                        let cardIndex = null;
                        if (fileInputId.includes('lwCarouselImageMediaFileName_')) {
                            cardIndex = fileInputId.replace('#lwCarouselImageMediaFileName_', '');
                        } else if (fileInputId.includes('lwCarouselVideoMediaFileName_')) {
                            cardIndex = fileInputId.replace('#lwCarouselVideoMediaFileName_', '');
                        }

                        console.log('Extracted card index:', cardIndex);

                        // Update Alpine.js data
                        if (cardIndex !== null) {
                            const alpineComponent = document.querySelector('[x-data]');
                            if (alpineComponent && alpineComponent._x_dataStack) {
                                const data = alpineComponent._x_dataStack[0];
                                if (data.carouselCards && data.carouselCards[cardIndex]) {
                                    data.carouselCards[cardIndex].uploaded_media_file_name = file.serverId;
                                    console.log('Successfully updated carousel card', cardIndex, 'with file:', file.serverId);

                                    // Force Alpine to update the UI
                                    alpineComponent.dispatchEvent(new CustomEvent('alpine:update'));
                                } else {
                                    console.error('Could not find carousel card at index:', cardIndex);
                                }
                            } else {
                                console.error('Could not find Alpine component or data stack');
                            }
                        }
                    } else {
                        console.error('Hidden input not found or no serverId:', {
                            hiddenInput: !!hiddenInput,
                            serverId: file.serverId
                        });
                    }
                } else {
                    console.error('No file input element ID found');
                }
            }
        });

        // Also listen for file removal
        document.addEventListener('FilePond:removefile', function(e) {
            const pond = e.detail.pond;

            if (pond.element && pond.element.id && pond.element.id.includes('lwCarousel')) {
                const fileInputId = pond.element.getAttribute('data-file-input-element');
                if (fileInputId) {
                    const hiddenInput = document.querySelector(fileInputId);
                    if (hiddenInput) {
                        hiddenInput.value = '';
                        hiddenInput.dispatchEvent(new Event('input'));

                        // Update Alpine.js data
                        let cardIndex = fileInputId.replace('#lwCarouselImageMediaFileName_', '').replace('#lwCarouselVideoMediaFileName_', '');
                        const alpineComponent = document.querySelector('[x-data]');
                        if (alpineComponent && alpineComponent._x_dataStack) {
                            const data = alpineComponent._x_dataStack[0];
                            if (data.carouselCards && data.carouselCards[cardIndex]) {
                                data.carouselCards[cardIndex].uploaded_media_file_name = '';
                                console.log('Removed file from carousel card', cardIndex);
                            }
                        }
                    }
                }
            }
        });

        // Handle file removal
        document.addEventListener('FilePond:removefile', function(e) {
            const pond = e.detail.pond;

            if (pond.element && pond.element.id && pond.element.id.includes('lwCarousel')) {
                const fileInputId = pond.element.getAttribute('data-file-input-element');
                if (fileInputId) {
                    const hiddenInput = document.querySelector(fileInputId);
                    if (hiddenInput) {
                        hiddenInput.value = '';
                        hiddenInput.dispatchEvent(new Event('input'));
                        hiddenInput.dispatchEvent(new Event('change'));

                        // Extract card index and update Alpine.js data
                        let cardIndex = null;
                        if (fileInputId.includes('lwCarouselImageMediaFileName_')) {
                            cardIndex = fileInputId.replace('#lwCarouselImageMediaFileName_', '');
                        } else if (fileInputId.includes('lwCarouselVideoMediaFileName_')) {
                            cardIndex = fileInputId.replace('#lwCarouselVideoMediaFileName_', '');
                        }

                        if (cardIndex !== null) {
                            const alpineComponent = document.querySelector('[x-data]');
                            if (alpineComponent && alpineComponent._x_dataStack) {
                                const data = alpineComponent._x_dataStack[0];
                                if (data.carouselCards && data.carouselCards[cardIndex]) {
                                    data.carouselCards[cardIndex].uploaded_media_file_name = '';
                                    console.log('Removed file from carousel card', cardIndex);
                                }
                            }
                        }
                    }
                }
            }
        });

        // Add form validation for carousel templates
        const form = document.getElementById('lwNewTemplateCreationForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submission started');
                const alpineComponent = document.querySelector('[x-data]');
                if (alpineComponent && alpineComponent._x_dataStack) {
                    const data = alpineComponent._x_dataStack[0];

                    console.log('Form data:', {
                        headerType: data.headerType,
                        carouselCards: data.carouselCards
                    });

                    // Check if this is a carousel template
                    if (data.headerType === 'carousel' && data.carouselCards) {
                        console.log('Carousel template submission - media files are optional during template creation');
                        // Media files are not required during template creation
                        // They will be uploaded when sending the template to users
                    }
                }
            });
        }
    });
});
</script>