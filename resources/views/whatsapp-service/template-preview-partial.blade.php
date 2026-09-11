<style>
.lw-whatsapp-carousel-container {
    margin: 10px 0;
}

/* Variable placeholder styling */
.variable-placeholder {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
    border: 1px dashed #1976d2;
    transition: all 0.3s ease;
}

.variable-placeholder.updated {
    background-color: #c8e6c9;
    color: #2e7d32;
    border-color: #2e7d32;
    border-style: solid;
}

.variable-placeholder:hover {
    background-color: #bbdefb;
    transform: scale(1.05);
}

/* Image preview styles */
#uploadedHeaderImage {
    position: absolute;
    top: 0;
    left: 0; 
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
    z-index: 2;
}

#defaultHeaderIcon {
    z-index: 1;
    transition: opacity 0.3s ease;
}

.lw-whatsapp-header-placeholder {
    position: relative;
    overflow: hidden;
}

/* Make preview sticky while scrolling */
.lw-whatsapp-preview-container {
    position: sticky;
    top: 80px;
    z-index: 3;
}


.lw-whatsapp-carousel-container .carousel-card-preview {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.lw-whatsapp-carousel-container .carousel-card-preview .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 8px;
}

.lw-whatsapp-carousel-container .carousel-card-preview .card-body {
    padding: 8px;
}

.lw-whatsapp-carousel-container .carousel-card-preview .card-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 4px;
}

.lw-whatsapp-carousel-container .carousel-card-preview .list-group-item {
    background: transparent;
    border: none;
    padding: 4px 8px;
    font-size: 12px;
    color: #007bff;
    cursor: pointer;
}

.lw-whatsapp-carousel-container .carousel-card-preview .list-group-item:hover {
    background: #e9ecef;
}

.lw-whatsapp-carousel-container .d-flex {
    overflow-x: auto;
    padding-bottom: 10px;
}

.lw-whatsapp-carousel-container .d-flex::-webkit-scrollbar {
    height: 6px;
}

.lw-whatsapp-carousel-container .d-flex::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.lw-whatsapp-carousel-container .d-flex::-webkit-scrollbar-thumb {
    background: #269C4C;
    border-radius: 3px;
}
</style>

<div class="lw-whatsapp-preview-container">
    <img class="lw-whatsapp-preview-bg" src="{{ asset('imgs/wa-message-bg.png') }}" alt="">
    <div class="lw-whatsapp-preview">
        <div class="card ">
            @foreach ($templateComponents as $templateComponent)
            @if ($templateComponent['type'] == 'HEADER')
            @if ($templateComponent['format'] != 'TEXT')
            <div class="lw-whatsapp-header-placeholder" style="position: relative; overflow: hidden; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                @if ($templateComponent['format'] == 'LOCATION')
                <i class="fa fa-5x fa-map-marker-alt text-white"></i>
                @elseif ($templateComponent['format'] == 'VIDEO')
                <i class="fa fa-5x fa-play-circle text-white"></i>
                @elseif ($templateComponent['format'] == 'IMAGE')
                <!-- Default icon (shown when no image uploaded) -->
                <i id="defaultHeaderIcon" class="fa fa-5x fa-image text-white" style="opacity: 0.7;"></i>
                <!-- Uploaded image (shown when image is uploaded) -->
                <img id="uploadedHeaderImage" src="" class="img-fluid" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; display: none; border-radius: 0;">
                @elseif ($templateComponent['format'] == 'DOCUMENT')
                <i class="fa fa-5x fa-file-alt text-white"></i>
                @endif
            </div>
            @endif
            @if ($templateComponent['format'] == 'LOCATION')
            <div class="lw-whatsapp-location-meta bg-secondary p-2">
                <small>@{{location_name}}</small><br>
                <small>@{{address}}</small>
            </div>
            @elseif ($templateComponent['format'] == 'TEXT')
            <div class="lw-whatsapp-body mb--3">
                @php
                $exampleHeaderItems = [
                "\n" => '<br>',
                ];
                @endphp
                @isset($templateComponent['example'])
                @php
                $headerTextItems = $templateComponent['example']['header_text'];
                $exampleHeaderTextItemIndex = 1;
                foreach ($headerTextItems as $headerTextItem) {
                    $exampleHeaderItems["{{{$exampleHeaderTextItemIndex}}}"] = '<span class="variable-placeholder" data-variable-index="' . $exampleHeaderTextItemIndex . '">{{Header ' . $exampleHeaderTextItemIndex . '}}</span>';
                    $exampleHeaderTextItemIndex++;
                }
                @endphp
                @endisset
                <strong class="header-text-preview"><?= strtr($templateComponent['text'], $exampleHeaderItems) ?></strong>
            </div>
            @endif
            @endif
            @if ($templateComponent['type'] == 'BODY')
            <div class="lw-whatsapp-body body-text-preview" data-has-body-variables="0">
                @php
                $exampleBodyItems = [
                "\n" => '<br>',
                ];
                // Add dynamic variable placeholders
                $bodyText = $templateComponent['text'];
                $variablePattern = '/\{\{(\d+)\}\}/';
                $bodyText = preg_replace_callback($variablePattern, function($matches) {
                    $index = $matches[1];
                    return '<span class="variable-placeholder" data-variable-index="' . $index . '">{{' . $index . '}}</span>';
                }, $bodyText);
                @endphp
                <?= formatWhatsAppText(strtr($bodyText, $exampleBodyItems)) ?>
            </div>
            @endif
            @if ($templateComponent['type'] == 'FOOTER')
            <div class="lw-whatsapp-footer text-muted">
                {{ $templateComponent['text'] }}
            </div>
            @endif
            @if($templateComponent['type'] == 'BUTTONS')
            <div class="card-footer lw-whatsapp-buttons">
                <div class="list-group list-group-flush lw-whatsapp-buttons">
                    @foreach ($templateComponent['buttons'] as $templateComponentButton)
                    <div class="list-group-item">
                        @if ($templateComponentButton['type'] == 'URL')
                        <i class="fas fa-external-link-square-alt"></i>
                        @elseif ($templateComponentButton['type'] == 'QUICK_REPLY')
                        <i class="fa fa-reply"></i>
                        @elseif ($templateComponentButton['type'] == 'PHONE_NUMBER')
                        <i class="fa fa-phone-alt"></i>
                        @elseif ($templateComponentButton['type'] == 'VOICE_CALL')
                        <i class="fa fa-phone-alt"></i>
                        @elseif ($templateComponentButton['type'] == 'COPY_CODE')
                        <i class="fa fa-copy"></i>
                        @endif
                        {{ $templateComponentButton['text'] }}
                    </div>
                    @if(($loop->count > 2) and ($loop->index == 1))
                    <div class="list-group-item"><i class="fa fa-menu"></i> {{ __tr('See all options') }} <br><small class="text-orange">{{  __tr('More than 3 buttons will be shown in the list by clicking') }}</small></div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
            @if($templateComponent['type'] == 'CAROUSEL')
            <div class="lw-whatsapp-carousel-container">
                <div class="d-flex overflow-auto pb-2" style="gap: 10px;">
                    @foreach($templateComponent['cards'] as $cardIndex => $card)
                    <div class="card shadow-sm carousel-card-preview" style="min-width: 200px; max-width: 200px;">
                        @foreach($card['components'] as $cardComponent)
                            @if($cardComponent['type'] == 'HEADER')
                                @if($cardComponent['format'] == 'PRODUCT')
                                <div class="card-header bg-light text-center">
                                    <i class="fa fa-shopping-bag"></i>
                                    <small class="d-block">{{ __tr('Product from Catalog') }}</small>
                                </div>
                                @elseif($cardComponent['format'] == 'IMAGE')
                                <div class="card-header p-0">
                                    <div class="lw-whatsapp-header-placeholder" style="height: 120px;">
                                        <i class="fa fa-3x fa-image text-white"></i>
                                    </div>
                                </div>
                                @elseif($cardComponent['format'] == 'VIDEO')
                                <div class="card-header p-0">
                                    <div class="lw-whatsapp-header-placeholder" style="height: 120px;">
                                        <i class="fa fa-3x fa-play-circle text-white"></i>
                                    </div>
                                </div>
                                @endif
                            @endif
                            @if($cardComponent['type'] == 'BODY')
                            <div class="card-body p-2">
                                <div class="lw-whatsapp-body" style="font-size: 13px;">
                                    {{ $cardComponent['text'] }}
                                </div>
                            </div>
                            @endif
                        @endforeach

                        @foreach($card['components'] as $cardComponent)
                            @if($cardComponent['type'] == 'BUTTONS')
                            <div class="card-footer p-1">
                                <div class="list-group list-group-flush">
                                    @foreach($cardComponent['buttons'] as $button)
                                    <div class="list-group-item p-1 text-center" style="font-size: 12px;">
                                        @if($button['type'] == 'URL')
                                        <i class="fas fa-external-link-square-alt"></i>
                                        @elseif($button['type'] == 'QUICK_REPLY')
                                        <i class="fa fa-reply"></i>
                                        @elseif($button['type'] == 'SPM')
                                        <i class="fa fa-shopping-bag"></i>
                                        @endif
                                        {{ $button['text'] }}
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>
