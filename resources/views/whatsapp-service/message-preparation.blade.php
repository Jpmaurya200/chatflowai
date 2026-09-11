@php
$onlyTemplatePreview = request()->has('only-preview');
@endphp
<div class="row" x-cloak>
    @if(!$onlyTemplatePreview)
    <div class="col-sm-12 col-md-8 col-lg-6 lw-template-structure-form">
        <input type="hidden" name="template_uid" value="{{ $template->_uid }}">
          <fieldset 
            class="p-4 rounded mb-4"
            style="border: 1px solid #22A755; background-color: #f3fff5; max-width: 600px; margin: auto;">

            <legend class="text-success fw-bold d-flex justify-content-between align-items-center" style="font-size: 1.25rem;">
                {{ __tr('Template') }}
                <template x-if="selectedTemplate">
                    <button class="btn btn-outline-success btn-sm ms-auto" @click.prevent="selectedTemplate = ''">
                        {{ __tr('Change') }}
                    </button>
                </template>
            </legend>

            <table class="table table-bordered table-sm mt-3" style="background-color: #ffffff;">
                <tbody>
                    <tr>
                        <th style="width: 40%;">{{ __tr('Template Name') }}</th>
                        <td><strong>{{ $template->template_name }}</strong></td>
                    </tr>
                    <tr>
                        <th>{{ __tr('Language Code') }}</th>
                        <td><strong>{{ $template->language }}</strong></td>
                    </tr>
                    <tr>
                        <th>{{ __tr('Category') }}</th>
                        <td><strong>{{ $template->category }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </fieldset>

        {{-- Header --}}
        @if($headerFormat)
        <fieldset class="lw-template-header-variables-container">
            <legend>{{ __tr('Header') }}</legend>
            @if($headerFormat == 'LOCATION')
            <h3>{{ __tr('Location Details') }}</h3>
            @include('whatsapp-service.template-partial', [
            'parameters' => [
            'location_latitude',
            'location_longitude',
            'location_name',
            'location_address',
            ],
            'subjectType' => 'header',
            ])
            @elseif($headerFormat == 'TEXT' and !__isEmpty($headerParameters))
            @include('whatsapp-service.template-partial', [
            'parameters' => $headerParameters,
            'subjectType' => 'header',
            ])
            @elseif($headerFormat == 'TEXT' and __isEmpty($headerParameters))
            <div class="alert alert-info">{{  __tr('No variables available for header text.') }}</div>
            <style>
                .lw-template-header-variables-container{display:none;}
            </style>
            @elseif($headerFormat == 'IMAGE')
            <div class="form-group col-md-4 col-sm-12">
                <label for="lwHeaderImageFilepond">{{ __tr('Select Image') }}</label>
                <input id="lwHeaderImageFilepond" type="file" data-allow-revert="true"
                    data-label-idle="{{ __tr('Select Image') }}" class="lw-file-uploader" data-instant-upload="true"
                    data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>" data-allowed-media='{{ getMediaRestriction('whatsapp_image') }}'
                    data-file-input-element="#lwHeaderImage">
                <input id="lwHeaderImage" type="hidden" value="" name="header_image" />
            </div>
            @elseif($headerFormat == 'VIDEO')
            <div class="form-group col-md-4 col-sm-12">
                <label for="lwHeaderVideoFilepond">{{ __tr('Select Video') }}</label>
                <input id="lwHeaderVideoFilepond" type="file" data-allow-revert="true"
                    data-label-idle="{{ __tr('Select Video') }}" class="lw-file-uploader" data-instant-upload="true"
                    data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>" data-allowed-media='{{ getMediaRestriction('whatsapp_video') }}'
                    data-file-input-element="#lwHeaderVideo">
                <input id="lwHeaderVideo" type="hidden" value="" name="header_video" />
            </div>
            @elseif($headerFormat == 'DOCUMENT')
            <div class="form-group col-md-4 col-sm-12">
                <label for="lwHeaderDocumentFilepond">{{ __tr('Select Document') }}</label>
                <input id="lwHeaderDocumentFilepond" type="file" data-allow-revert="true"
                    data-label-idle="{{ __tr('Select Document') }}" class="lw-file-uploader" data-instant-upload="true"
                    data-action="<?= route('media.upload_temp_media', 'whatsapp_document') ?>" data-allowed-media='{{ getMediaRestriction('whatsapp_document') }}'
                    data-file-input-element="#lwHeaderDocument">
                <input id="lwHeaderDocument" type="hidden" value="" name="header_document" />
            </div>
            @include('whatsapp-service.template-partial', [
            'parameters' => [
            'header_document_name'
            ],
            'subjectType' => 'header',
            ])
            @endif
        </fieldset>
        @endif
        {{-- /Header --}}
        {{-- Body Variables --}}
        @if(!__isEmpty($bodyParameters))
        <fieldset>
            <legend>{{ __tr('Body') }}</legend>
            @include('whatsapp-service.template-partial', [
            'parameters' => $bodyParameters,
            'subjectType' => 'body',
            ])
        </fieldset>
        @endif
        {{-- /Body Variables --}}
        {{-- Button Variables --}}
        @if(!__isEmpty($buttonParameters) or !__isEmpty($buttonItems))
        <fieldset>
            <legend>{{ __tr('Buttons') }}</legend>
            @include('whatsapp-service.template-partial', [
            'parameters' => $buttonParameters,
            'buttonItems' => $buttonItems,
            'subjectType' => 'button',
            ])
            @if(array_key_exists('COPY_CODE', $buttonItems))
            <label for="">{{ __tr('Code for Copy') }}</label>
            @include('whatsapp-service.template-partial', [
            'parameters' => [
            'copy_code'
            ],
            'buttonItems' => [],
            'subjectType' => 'button',
            ])
            @endif
        </fieldset>
        @endif
        {{-- /Button Variables --}}

        {{-- Carousel Cards --}}
        @if(!empty($isCarouselTemplate) && !empty($carouselCards))
        <fieldset>
            <legend>{{ __tr('Carousel Cards Media') }}</legend>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                {{ __tr('Upload media files for each carousel card that requires images or videos. Product cards will use items from your catalog.') }}
            </div>

            @foreach($carouselCards as $cardIndex => $card)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fa fa-layer-group"></i>
                        {{ __tr('Card') }} {{ $cardIndex + 1 }}
                        @foreach($card['components'] as $component)
                            @if($component['type'] == 'HEADER')
                                - {{ ucfirst(strtolower($component['format'])) }} {{ __tr('Header') }}
                            @endif
                        @endforeach
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($card['components'] as $component)
                        @if($component['type'] == 'HEADER')
                            @if($component['format'] == 'IMAGE')
                            <div class="form-group">
                                <label for="lwCarouselCard{{ $cardIndex }}ImageFilepond">{{ __tr('Select Image for Card') }} {{ $cardIndex + 1 }}</label>
                                <input id="lwCarouselCard{{ $cardIndex }}ImageFilepond" type="file" data-allow-revert="true"
                                    data-label-idle="{{ __tr('Select Image') }}" class="lw-file-uploader" data-instant-upload="true"
                                    data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>" data-allowed-media='{{ getMediaRestriction('whatsapp_image') }}'
                                    data-file-input-element="#lwCarouselCard{{ $cardIndex }}Image">
                                <input id="lwCarouselCard{{ $cardIndex }}Image" type="hidden" value="" name="carousel_card_{{ $cardIndex }}_image" />
                            </div>
                            @elseif($component['format'] == 'VIDEO')
                            <div class="form-group">
                                <label for="lwCarouselCard{{ $cardIndex }}VideoFilepond">{{ __tr('Select Video for Card') }} {{ $cardIndex + 1 }}</label>
                                <input id="lwCarouselCard{{ $cardIndex }}VideoFilepond" type="file" data-allow-revert="true"
                                    data-label-idle="{{ __tr('Select Video') }}" class="lw-file-uploader" data-instant-upload="true"
                                    data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>" data-allowed-media='{{ getMediaRestriction('whatsapp_video') }}'
                                    data-file-input-element="#lwCarouselCard{{ $cardIndex }}Video">
                                <input id="lwCarouselCard{{ $cardIndex }}Video" type="hidden" value="" name="carousel_card_{{ $cardIndex }}_video" />
                            </div>
                            @elseif($component['format'] == 'PRODUCT')
                            <div class="alert alert-warning">
                                <i class="fa fa-shopping-bag"></i>
                                {{ __tr('This card will display a product from your catalog. No media upload required.') }}
                            </div>
                            @endif
                        @endif
                    @endforeach

                    {{-- Show card body text for reference --}}
                    @foreach($card['components'] as $component)
                        @if($component['type'] == 'BODY')
                        <div class="alert alert-light">
                            <strong>{{ __tr('Card Text:') }}</strong> {{ $component['text'] }}
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </fieldset>
        @endif
        {{-- /Carousel Cards --}}
    </div>
    {{-- Message Preview --}}
    <div class="col-sm-12 col-md-8 col-lg-6">
        <fieldset class="position-absolute w-100">
            <legend>{{ __tr('Message Preview') }}</legend>
            <div class="card">
                <div class="card-body">
                    @else
                    <div class="col-12">
                    @endif
                    @include('whatsapp-service.template-preview-partial', [
            'bodyComponentText' => $bodyComponentText,
            'parameters' => $bodyParameters,
            'subjectType' => 'body',
            'templateComponents' => $templateComponents
            ])
 </div>
@if(!$onlyTemplatePreview)
            </div>
            <div class="alert alert-light mt-5">
                <strong>{{  __tr('Please note:') }}</strong>
               {!! __tr('Words like @{{1}}, @{{abc}} etc are dynamic variables and will be replaced based on your selections.') !!}
            </div>
            @endif
        </fieldset>
    </div>
</div>