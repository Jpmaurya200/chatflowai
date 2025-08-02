@extends('layouts.app', ['title' => __tr('Edit Template')])
@section('content')
@include('users.partials.header', [
'description' => '',
'class' => 'col-lg-7'
])
<div class="container-fluid mt-lg--6">
    <div class="row mt-3">
        <div class="col-12 mb-3">
            <div class="mt-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Left Side: Title -->
                <h1 class="page-title mb-0" style="color: #22A755;">
                    <i class="fas fa-clipboard-list me-2"></i>{{ __tr('  Edit Template') }}
                </h1>

                <!-- Right Side: Buttons -->
                <div class="d-flex flex-wrap gap-4">
                    <!-- Back Button -->
                    <a class="btn btn-success text-white"
                    style="background-color: #22A755; border: none; transition: transform 0.2s ease, box-shadow 0.2s ease; margin-right: 8px;"
                    onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(34,167,85,0.3)'"
                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                    href="{{ route('vendor.whatsapp_service.templates.read.list_view') }}">
                        <i class="fas fa-arrow-left me-2"></i>{{ __tr(' Back to Templates') }}
                    </a>

                    <!-- Meta Edit Button -->
                    <a target="_blank"
                        title="{{ __tr('Edit this Template on Meta') }}"
                        class="btn text-white"
                        style="
                            background-color: #0861F2;   /* Dark blue */
                            border: none;
                            margin-right: 8px;
                            transition: transform 0.2s ease, box-shadow 0.2s ease;
                        "
                        onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(6, 44, 100, 0.4)'"
                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                        href="https://business.facebook.com/wa/manage/message-templates/?&waba_id={{ getVendorSettings('whatsapp_business_account_id') }}&id={{ $whatsAppTemplateData['id'] }}">
                            {{ __tr('Edit this Template on Meta') }} <i class="fas fa-external-link-alt"></i>
                        </a>

                    <!-- Help Button -->
                    <a href="https://business.facebook.com/business/help/2055875911147364"
                    target="_blank"
                    class="btn btn-outline-success"
                    style="transition: transform 0.2s ease, box-shadow 0.2s ease;"
                    onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(34,167,85,0.3)'"
                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                        {{ __tr('Help') }}
                    </a>
                </div>
            </div>
        </div>
    </div>


    @php
        $templateComponents = $whatsAppTemplateData['components'];
        $templateButtons = Arr::first($templateComponents, function ($value, $key) {
        return $value['type'] == 'BUTTONS';
    });
    @endphp
    <script>
         window.editTemplateButtonModelValues = {
            URL_BUTTON:0,
            URL_BUTTON_LIMIT:2,
            COPY_CODE:0,
            COPY_CODE_LIMIT:1,
            VOICE_CALL:0,
            VOICE_CALL_LIMIT:1,
            PHONE_NUMBER:0,
            PHONE_NUMBER_LIMIT:1,
         };
        @if(isset($templateButtons['buttons']) and !empty($templateButtons['buttons']))
        @foreach ($templateButtons['buttons'] as $templateComponentButton)
            @php
                $templateComponentButton['type'] = ($templateComponentButton['type'] == 'URL') ? 'URL_BUTTON' : $templateComponentButton['type'];
            @endphp
             if(editTemplateButtonModelValues['{{ $templateComponentButton['type'] }}']) {
                editTemplateButtonModelValues['{{ $templateComponentButton['type'] }}']++;
            } else {
                editTemplateButtonModelValues['{{ $templateComponentButton['type'] }}'] = 1;
            }
        @endforeach
        @endif
    </script>
    <div class="col-12" x-data="{
    headerType:'',
    header_text_body:'',
    footer_text_body:'',
    text_body:'',
    example_body_fields:[],
    enableHeaderVariableExample:true,
    newBodyTextInputFields:[],
    buttonModels:{
        @if(isset($templateButtons['buttons']) and !empty($templateButtons['buttons']))
        @foreach ($templateButtons['buttons'] as $templateComponentButton)
        @php
        if(isset($templateComponentButton['url'])) {
            $templateComponentButton['url'] = str_replace('{{1}}', '', $templateComponentButton['url']);
        }
        @endphp
        '{{ $loop->index + 1 }}' : {
            'text_value' : '{{ $templateComponentButton['text'] }}',
            'example_value' : {!! str_replace('"', "'", json_encode($templateComponentButton['url'] ?? cleanDisplayPhoneNumber($templateComponentButton['phone_number'] ?? null) ?? $templateComponentButton['example'] ?? [])) !!},
            'examples' : {!! str_replace('"', "'", json_encode($templateComponentButton['example'] ?? [])) !!},
        },
       @endforeach
       @endif
       },
    customButtons:{
        totalAllowedButtons:10,
        totalButtonsUsed:0,
        buttonUsesByTypes:window.editTemplateButtonModelValues,
        totalUrlButtonUsed:0,
        data: {
            @if(isset($templateButtons['buttons']) and !empty($templateButtons['buttons']))
            @foreach ($templateButtons['buttons'] as $templateComponentButton)
            @php
                $templateComponentButton['type'] = ($templateComponentButton['type'] == 'URL') ? 'URL_BUTTON' : $templateComponentButton['type'];
                if($templateComponentButton['type'] == 'URL_BUTTON') {
                    if(Str::contains($templateComponentButton['url'], '{{1}}')) {
                        $templateComponentButton['type'] = 'DYNAMIC_URL_BUTTON';
                        $templateComponentButton['url'] = str_replace('{{1}}', '', $templateComponentButton['url']);
                    }
                }
            @endphp
            '{{ $loop->index + 1 }}' : {
                   buttonType : '{{ $templateComponentButton['type'] }}',
                   buttonIndex : {{ $loop->index + 1 }}
               },
           @endforeach
           @endif
           },
    }, addWhatsAppButtonOption : function(buttonType) {
        {{-- let uniqueBtnId = _.uniqueId(); --}}
        let uniqueBtnId = _.size(this.customButtons.data) + 1;
        this.customButtons.data[uniqueBtnId] = {
            buttonType : buttonType,
            buttonIndex : uniqueBtnId
        };
        this.buttonModels[uniqueBtnId] = {
            'text_value': '',
            'example_value': '',
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
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <fieldset style="border: 1px solid #dee2e6; padding: 1rem; border-radius: 6px;">
                            <legend style="color: #22A755; font-weight: 600;">{{ __tr('Template Info') }}</legend>

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%;">{{ __tr('Name') }}</th>
                                            <td>{{ $whatsAppTemplateData['name'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __tr('Language') }}</th>
                                            <td>{{ $whatsAppTemplateData['language'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __tr('Category') }}</th>
                                            <td>{{ $whatsAppTemplateData['category'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __tr('Status') }}</th>
                                            <td>
                                                @if ($whatsAppTemplateData['status'] == 'APPROVED')
                                                    <i class="fa fa-check-circle text-success me-1"></i>
                                                @elseif ($whatsAppTemplateData['status'] == 'REJECTED')
                                                    <i class="fa fa-times-circle text-danger me-1"></i>
                                                @elseif ($whatsAppTemplateData['status'] == 'PENDING')
                                                    <i class="fa fa-clock text-warning me-1"></i>
                                                @endif
                                                {{ $whatsAppTemplateData['status'] }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    </div>
                </div>

                {{-- OTP and flow templates --}}
                @if ((($whatsAppTemplateData['sub_category'] ?? null) == 'FORM') or (($whatsAppTemplateData['category'] ?? null) == 'AUTHENTICATION'))
                    <div class="mt-3">
                        <div class="alert alert-warning">
                            {{  __tr('You need to edit this template on Meta') }}
                        </div>
                        <a target="_blank" title="{{  __tr('Edit this Template on Meta') }}" class="lw-btn btn btn-dark" href="https://business.facebook.com/wa/manage/message-templates/?&waba_id={{ getVendorSettings('whatsapp_business_account_id') }}&id={{ $whatsAppTemplateData['id'] }}">{{  __tr('Edit this Template on Meta') }} <i class="fas fa-external-link-alt"></i></a>
                    </div>
                @else
                <div class="row">
                    <div class="col-md-7">
                        <x-lw.form id="lwNewTemplateCreationForm"
                            :action="route('vendor.whatsapp_service.templates.write.update')">
                            <input type="hidden" name="template_uid" value="{{ $whatsAppTemplateUid }}">
                            <fieldset style="border: 2px solid #22A755; padding: 20px; border-radius: 10px; background: #f9fdfb;">
                                @php
                                    $headerSelected = '0'; // fallback if HEADER not found
                                    foreach ($templateComponents as $component) {
                                        if ($component['type'] === 'HEADER') {
                                            $headerSelected = strtolower($component['format']);
                                            break;
                                        }
                                    }
                                @endphp

                                <legend style="color: #22A755; font-weight: 600;">{{ __tr('Header') }} <small>({{ __tr('Optional') }})</small></legend>

                                <input id="lwMediaFileName" type="hidden" value="" name="uploaded_media_file_name" />

                                <x-lw.input-field
                                    x-model="headerType"
                                    type="selectize"
                                    id="lwMediaHeaderType"
                                    data-form-group-class="mb-3"
                                    data-selected="{{ $headerSelected }}"
                                    :label="__tr('Header Type')"
                                    name="media_header_type"
                                >
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
                                    {{-- TEXT TYPE --}}
                                    <div x-show="headerType == 'text'" class="form-group col-sm-12">
                                        <x-lw.input-field type="text" id="lwHeaderTextBody"
                                            :label="__tr('Header Text')" x-model="header_text_body"
                                            name="header_text_body" data-form-group-class="mb-2" />

                                        <div class="form-group text-right">
                                            <button :disabled="enableHeaderVariableExample"
                                                    id="lwAddSinglePlaceHolder"
                                                    class="btn btn-success btn-sm"
                                                    type="button">
                                                <i class="fa fa-plus"></i> {{ __tr('Add Variable') }}
                                            </button>
                                        </div>

                                        <template x-if="enableHeaderVariableExample">
                                            <x-lw.input-field type="text" id="lwHeaderTextBodyExample"
                                                :label="__tr('Header Text Variable Example')"
                                                name="example_header_fields"
                                                data-form-group-class="mt-2" />
                                        </template>
                                    </div>

                                    {{-- DOCUMENT TYPE --}}
                                    <div x-show="headerType == 'document'" class="form-group col-sm-12 mt-3">
                                        <h5 class="text-success">{{ __tr('Sample Document') }}</h5>
                                        <input id="lwDocumentMediaFilepond" type="file" data-allow-revert="true"
                                            data-label-idle="{{ __tr('Select Document') }}" class="lw-file-uploader"
                                            data-instant-upload="true"
                                            data-action="<?= route('media.upload_temp_media', 'whatsapp_document') ?>"
                                            data-file-input-element="#lwMediaFileName"
                                            data-allowed-media='<?= getMediaRestriction('whatsapp_document') ?>' />
                                    </div>

                                    {{-- IMAGE TYPE --}}
                                    <div x-show="headerType == 'image'" class="form-group col-sm-12 mt-3">
                                        <h5 class="text-success">{{ __tr('Sample Image') }}</h5>
                                        <input id="lwImageMediaFilepond" type="file" data-allow-revert="true"
                                            data-label-idle="{{ __tr('Select Image') }}" class="lw-file-uploader"
                                            data-instant-upload="true"
                                            data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>"
                                            data-file-input-element="#lwMediaFileName"
                                            data-allowed-media='<?= getMediaRestriction('whatsapp_image') ?>' />
                                    </div>

                                    {{-- VIDEO TYPE --}}
                                    <div x-show="headerType == 'video'" class="form-group col-sm-12 mt-3">
                                        <h5 class="text-success">{{ __tr('Sample Video') }}</h5>
                                        <input id="lwVideoMediaFilepond" type="file" data-allow-revert="true"
                                            data-label-idle="{{ __tr('Select Video') }}" class="lw-file-uploader"
                                            data-instant-upload="true"
                                            data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>"
                                            data-file-input-element="#lwMediaFileName"
                                            data-allowed-media='<?= getMediaRestriction('whatsapp_video') ?>' />
                                    </div>

                                    {{-- LOCATION TYPE --}}
                                    <div x-show="headerType == 'location'" class="form-group col-sm-12 mt-3">
                                        <h5 class="text-success">{{ __tr('Sample Location') }}</h5>
                                        <p class="text-muted">{{ __tr('The location will be automatically fetched and previewed.') }}</p>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset style="border: 2px solid #22A755; padding: 20px; border-radius: 10px; background: #f9fdfb;">
                                <legend style="color: #22A755; font-weight: 600;">{{ __tr('Body') }}</legend>

                                <small class="text-muted d-block mb-3 fw-semibold">
                                    {{ __tr('Enter the text for your message in the language you\'ve selected.') }}
                                </small>

                                <div class="form-group mb-4">
                                    <label for="lwTemplateBody" class="fw-bold">{{ __tr('Body Text') }}</label>
                                    <textarea name="template_body" id="lwTemplateBody"
                                            class="form-control"
                                            style="min-height: 80px; border: 1px solid #22A755;"
                                            x-model="text_body" rows="3"></textarea>
                                </div>

                                {{-- Text Formatting Buttons --}}
                                <div class="form-group d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button id="lwBoldBtn" class="btn btn-outline-success" type="button" title="Bold"
                                            style="transition: all 0.2s ease;">
                                            <i class="fa fa-bold"></i>
                                        </button>
                                        <button id="lwItalicBtn" class="btn btn-outline-success" type="button" title="Italic"
                                            style="transition: all 0.2s ease;">
                                            <i class="fa fa-italic"></i>
                                        </button>
                                        <button id="lwStrikeThroughBtn" class="btn btn-outline-success" type="button" title="Strikethrough"
                                            style="transition: all 0.2s ease;">
                                            <i class="fa fa-strikethrough"></i>
                                        </button>
                                        <button id="lwCodeBtn" class="btn btn-outline-success" type="button" title="Monospace"
                                            style="transition: all 0.2s ease;">
                                            <i class="fa fa-code"></i>
                                        </button>
                                    </div>

                                    <button id="lwAddPlaceHolder" class="btn btn-success btn-sm" type="button">
                                        <i class="fa fa-plus"></i> {{ __tr('Add Variables') }}
                                    </button>
                                </div>

                                {{-- Sample Variables Section --}}
                                <div>
                                    <template x-if="_.size(newBodyTextInputFields)">
                                        <div>
                                            <h5 class="text-success">{{ __tr('Samples Text') }}</h5>
                                            <template x-for="(item, index) in newBodyTextInputFields" :key="index">
                                                <div class="form-group mb-2">
                                                    <div class="input-group">
                                                        <span class="input-group-text" x-text="item.text_variable"></span>
                                                        <input type="text"
                                                            class="form-control"
                                                            required="required"
                                                            x-bind:value="example_body_fields[index - 1]"
                                                            x-bind:name="'example_body_fields[' + index + ']'"
                                                        />
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </fieldset>

                            <x-lw.input-field
                                type="text"
                                id="lwTemplateFooter"
                                data-form-group-class="border border-success rounded p-3 shadow-sm"
                                :label="__tr('Footer (Optional)')"
                                name="template_footer"
                                x-model="footer_text_body"
                                :helpText="__tr('Add a short line of text to the bottom of your message template.')"
                            />

                            <fieldset class="border p-4 rounded" style="border-color: #22A755;">
                                <legend style="color: #22A755; font-weight: 600;">
                                    {{ __tr('Buttons') }}
                                    <small class="text-muted" style="font-weight: 400;">{{ __tr('(Optional)') }}</small>
                                </legend>

                                <div class="mb-4">
                                    <h3 class="text-muted" style="font-size: 1rem; font-weight: 500;">
                                        {{ __tr('Create buttons that let customers respond to your message or take action.') }}
                                    </h3>
                                </div>

                                <div class="lw-buttons-container">
                                    <template x-for="customButtonData in customButtons.data">
                                        <div class="card border-success mb-3 shadow-sm">
                                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                                <template x-if="customButtonData.buttonType == 'QUICK_REPLY'">
                                                    <span class="text-success fw-bold">{{ __tr('Quick Reply Button') }}</span>
                                                </template>
                                                <template x-if="customButtonData.buttonType == 'PHONE_NUMBER'">
                                                    <span class="text-success fw-bold">{{ __tr('Phone Number Button') }}</span>
                                                </template>
                                                <template x-if="customButtonData.buttonType == 'URL_BUTTON'">
                                                    <span class="text-success fw-bold">{{ __tr('URL Button') }}</span>
                                                </template>
                                                <template x-if="customButtonData.buttonType == 'DYNAMIC_URL_BUTTON'">
                                                    <span class="text-success fw-bold">{{ __tr('Dynamic URL Button') }}</span>
                                                </template>
                                                <template x-if="customButtonData.buttonType == 'VOICE_CALL'">
                                                    <span class="text-success fw-bold">{{ __tr('WhatsApp Call Button') }}</span>
                                                </template>
                                                <template x-if="customButtonData.buttonType == 'COPY_CODE'">
                                                    <span class="text-success fw-bold">{{ __tr('Coupon Code Copy Button') }}</span>
                                                </template>
                                                <button @click.prevent="deleteWhatsAppButtonOption(customButtonData.buttonIndex)" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <!-- Button Input Fields Based on Type (unchanged) -->
                                                <!-- Keep your existing Alpine.js-based field rendering here -->
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Add Button Options -->
                                    <div class="mt-4 d-flex flex-wrap gap-2">
                                        <button
                                            :disabled="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons"
                                            class="btn btn-outline-success btn-sm" style="margin-right: 6px;"
                                            @click.prevent="addWhatsAppButtonOption('QUICK_REPLY')">
                                            <i class="fa fa-reply me-1"></i> {{ __tr('Quick Reply') }}
                                        </button>
                                        <button
                                            :disabled="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons"
                                            class="btn btn-outline-success btn-sm" style="margin-right: 6px;"
                                            @click.prevent="addWhatsAppButtonOption('PHONE_NUMBER')">
                                            <i class="fa fa-phone-alt me-1"></i> {{ __tr('Phone Number') }}
                                        </button>
                                        <button
                                            :disabled="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons"
                                            class="btn btn-outline-success btn-sm" style="margin-right: 6px;"
                                            @click.prevent="addWhatsAppButtonOption('COPY_CODE')">
                                            <i class="fa fa-clipboard me-1"></i> {{ __tr('Copy Code') }}
                                        </button>
                                        <button
                                            :disabled="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons"
                                            class="btn btn-outline-success btn-sm" style="margin-right: 6px;"
                                            @click.prevent="addWhatsAppButtonOption('URL_BUTTON')">
                                            <i class="fa fa-link me-1"></i> {{ __tr('URL Button') }}
                                        </button>
                                        <button
                                            :disabled="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons"
                                            class="btn btn-outline-success btn-sm"
                                            @click.prevent="addWhatsAppButtonOption('DYNAMIC_URL_BUTTON')">
                                            <i class="fa fa-link me-1"></i> {{ __tr('Dynamic URL') }}
                                        </button>
                                    </div>

                                    <template x-if="customButtons.totalButtonsUsed >= customButtons.totalAllowedButtons">
                                        <div class="alert alert-danger mt-4">
                                            {{ __tr('You have reached the maximum number of buttons allowed by Meta.') }}
                                        </div>
                                    </template>
                                </div>
                            </fieldset>

                            <div class="form-group text-center">
                                @if($whatsAppTemplateData['status'] == 'PENDING')
                                    <div class="alert alert-warning">
                                        {{ __tr('As template is in pending status it can not be edited.') }}
                                    </div>
                                @else
                                    <button type="submit"
                                            class="btn text-white"
                                            style="
                                                background-color: #1c6b3d;
                                                border: none;
                                                transition: all 0.3s ease;
                                            "
                                            onmouseover="this.style.backgroundColor='#14532d'; this.style.transform='scale(1.03)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.2)'"
                                            onmouseout="this.style.backgroundColor='#1c6b3d'; this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                        {{ __('Submit') }} <i class="fas fa-paper-plane"></i>
                                    </button>
                                @endif
                            </div>

                        </x-lw.form>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="col-md-4">
                        <div class="lw-whatsapp-template-create-preview">
                            <h3>{{  __tr('Template Preview') }}</h3>
                            <div class="lw-whatsapp-preview-container">
                                <img class="lw-whatsapp-preview-bg" src="{{ asset('imgs/wa-message-bg.png') }}" alt="">
                                <div class="lw-whatsapp-preview">
                                    <div class="card ">
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
                                                                <div class="list-group-item">
                                                                    <template x-if="customButtonData.buttonType == 'QUICK_REPLY'">
                                                                        <i class="fa fa-reply"></i>
                                                                    </template>
                                                                    <template x-if="customButtonData.buttonType == 'PHONE_NUMBER'">
                                                                        <i class="fa fa-phone-alt"></i>
                                                                    </template>
                                                                    <template x-if="customButtonData.buttonType == 'URL_BUTTON'">
                                                                        <i class="fas fa-external-link-square-alt"></i>
                                                                    </template>
                                                                    <template x-if="customButtonData.buttonType == 'DYNAMIC_URL_BUTTON'">
                                                                        <i class="fas fa-external-link-square-alt"></i>
                                                                    </template>
                                                                    <template x-if="customButtonData.buttonType == 'VOICE_CALL'">
                                                                        <i class="fab fa-whatsapp"></i><i class="fa fa-phone-alt"></i>
                                                                    </template>
                                                                    <template x-if="customButtonData.buttonType == 'COPY_CODE'">
                                                                        <span><i class="fa fa-copy"></i> {{  __tr('Copy Code') }}</span>
                                                                    </template>
                                                                    <span x-text="!_.isUndefined(buttonModels[customButtonData.buttonIndex]) ? buttonModels[customButtonData.buttonIndex]['text_value'] : ''"></span>
                                                                </div>
                                                            <template x-if="index == 3">
                                                                <div class="list-group-item"><i class="fa fa-menu"></i> {{ __tr('See all options') }} <br><small class="text-orange">{{  __tr('More than 3 buttons will be shown in the list by clicking') }}</small></div>
                                                            </template>
                                                        </div>
                                                    </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection()
@push('appScripts')
<?= __yesset([
            'dist/js/whatsapp-template.js',
        ],true,
) ?>
<script>
     (function(){
        'use strict';
    @foreach ($templateComponents as $templateComponent)
 @if($templateComponent['type'] == 'HEADER')
    __DataRequest.updateModels({headerType:`{{ strtolower($templateComponent['format']) }}`});
    @if($templateComponent['format'] == 'TEXT')
    __DataRequest.updateModels({header_text_body:`{!! $templateComponent['text'] !!}`});
    _.defer(function(){
        $('#lwHeaderTextBody').trigger('input');
     });
    @endif
     @endif
     @if($templateComponent['type'] == 'BODY')
     __DataRequest.updateModels({text_body:`{!! $templateComponent['text'] !!}`,example_body_fields:@json($templateComponent['example']['body_text'][0] ?? [])});
     _.defer(function(){
        $('#lwTemplateBody').trigger('input');
     });
     @endif
     @if($templateComponent['type'] == 'FOOTER')
     __DataRequest.updateModels({footer_text_body:`{!! $templateComponent['text'] !!}`});
     @endif
     @endforeach
     })();
</script>
@endpush