@extends('layouts.app', ['title' => $contact ? __tr('Send WhatsApp Template Message') : __tr('Create New Campaign')])
@section('content')
@include('users.partials.header', [
'title' => $contact ? __tr('Send WhatsApp Template Message') : __tr(''),
'description' => '',
// 'class' => 'col-lg-7'
])

<div class="container-fluid mt-lg--6">
          <div class="row mt-3">
              <!-- button -->
            <div class="col-xl-12 mb-3 mt-5">
                @if ($contact)
                    <a class="lw-btn btn btn-secondary mb-2" href="{{ route('vendor.contact.read.list_view') }}">
                        {{ __tr('Back to Contacts') }}
                    </a>
                @endif

                <!-- Flex container for title and buttons -->
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <!-- Left: Title -->
                    <h1 class="page-title mb-0" style="color: #22A755;">
                        <i class="fas fa-file-alt me-2"></i>{{ __tr(' Create New Campaign') }}
                    </h1>

                    <!-- Right: Buttons -->
                    <div class="d-flex gap-2 mt-2 mt-sm-0">
                        <a class="lw-btn btn btn-success btn-md text-white lw-ajax-link-action"
                            data-confirm="{{ __tr('On template sync page will be refreshed') }}"
                            data-callback="__Utils.viewReload"
                            data-method="post"
                            style="transition: background-color 0.3s ease; margin-right: 8px;" 
                            onmouseover="this.style.backgroundColor='#21B55F'"
                            onmouseout="this.style.backgroundColor='#22A755'"
                            href="{{ route('vendor.whatsapp_service.templates.write.sync') }}">
                                <i class="fas fa-sync-alt me-2"></i>{{ __tr(' Sync WhatsApp Templates') }}
                        </a>

                        <a class="lw-btn btn btn-seconday btn-md text-white"
                            style="background-color: #003366; transition: background-color 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#002855'" 
                            onmouseout="this.style.backgroundColor='#003366'" 
                            href="{{ route('vendor.campaign.read.list_view') }}">
                            {{ __tr('Manage Campaigns') }}
                        </a>
                    </div>
                </div>
            </div>

    <!--/ button -->
    <div class="col-12">
        <div class="card">
            @if ($contact)
            <div class="card-header">
                <div>{{  __tr('Name') }} : {{ $contact->full_name }}</div>
                <div>{{  __tr('Phone') }} : {{ $contact->wa_id }}</div>
                <div>{{  __tr('Country') }} : {{ $contact->country?->name }}</div>
            </div>
            @else
                @if(!getVendorSettings('test_recipient_contact'))
                <div class="card-body">
                    <div class="alert alert-danger">
                        {{  __tr('Test Contact missing, You need to set the Test Contact first, do it under the WhatsApp Settings') }}
                    </div>
                </div>
                @endif
            @endif
            <div class="card-body" x-data="{selectedTemplate:'' }">
                <div class="col-sm-12 col-md-8 col-lg-6">
                    @if (!$contact)
                    <h2 class="text-warning">{{  __tr('Step 1') }}</h2>
                    @endif
                    <x-lw.form lwSubmitOnChange data-event-callback="lwPrepareUploadPlugIn"
                        :action="route('vendor.request.template.view')" data-pre-callback="clearTemplateContainer">
                        <div x-cloak x-show="!selectedTemplate">
                            <x-lw.input-field x-model="selectedTemplate"
                                placeholder="{!! __tr('Select & Configure Template') !!}" type="selectize"
                                data-lw-plugin="lwSelectize" data-selected=" " type="select"
                                id="lwField_templateSelection" name="template_selection" data-form-group-class=""
                                class="custom-select" data-selected=" " :label="__tr('Select Template')">
                                <x-slot name="selectOptions">
                                    <option value="">{{ __tr('Select & Configure Template') }}</option>
                                    @foreach ($whatsAppTemplates as $whatsAppTemplate)
                                    <option value="{{ $whatsAppTemplate->_uid }}">{{ $whatsAppTemplate->template_name }}
                                        ({{ $whatsAppTemplate->language }})</option>
                                    @endforeach
                                </x-slot>
                            </x-lw.input-field>
                        </div>
                    </x-lw.form>
                </div>
                <div x-cloak class="col-12">
                        @if ($contact)
                        <x-lw.form x-show="selectedTemplate" :action="route('vendor.template_message.contact.process', [
                            'contactUid' => $contact->_uid
                        ])">
                            <input type="hidden" name="contact_uid" value="{{ $contact->_uid }}">
                            <div id="lwTemplateStructureContainer">
                                {!! $template !!}
                            </div>
                             @include('whatsapp.from-phone-number')
                            <button type="submit" class="btn btn-primary mt-4">{{ __('Send') }}</button>
                        </x-lw.form>
                        @else
                        {{-- Campaign Creation --}}
                        <x-lw.form x-show="selectedTemplate" 
                            :action="route('vendor.campaign.schedule.process')" 
                            data-confirm="#lwScheduleMessageConfirmation"
                            class="p-4 rounded shadow-sm animate__animated animate__fadeIn"
                            style="border: 1px solid #22A755; background-color: #ffffff;">
                            
                            <div id="lwTemplateStructureContainer">
                                {!! $template !!}
                            </div>

                            <h2 class="mt-5 text-warning">{{ __tr('Step 2') }}</h2>

                            <fieldset 
                                class="col-sm-12 col-md-8 col-lg-6 p-4 mb-4 rounded"
                                style="border: 1px solid #22A755; background-color: #f9fff9; transition: border-color 0.3s ease; margin-right: 10px;">
                                
                                <legend style="color: #22A755; font-weight: 600; font-size: 1.3rem;">
                                    {{ __tr('Contacts and Schedule') }}
                                </legend>

                                {{-- Campaign Title --}}
                                <x-lw.input-field type="text" id="lwCampaignTitle" :label="__tr('Campaign Title')" name="title" required />

                                {{-- Contact Group Select --}}
                                <x-lw.input-field type="selectize" data-lw-plugin="lwSelectize" id="lwSelectGroupsField"
                                    :label="__tr('Groups/Contact')" name="contact_group">
                                    <x-slot name="selectOptions">
                                        <option value="">{{ __tr('Select Contacts Group') }}</option>
                                        <option value="all_contacts">{{ __tr('All Contacts') }}</option>
                                        @foreach($vendorContactGroups as $vendorContactGroup)
                                            <option value="{{ $vendorContactGroup['_id'] }}">{{ $vendorContactGroup['title'] }}</option>
                                        @endforeach
                                    </x-slot>
                                </x-lw.input-field>

                                {{-- Restrict by Template Language --}}
                                <div class="form-group pt-3">
                                    <label for="lwOnlyForTemplateLanguageMatchingContact" class="text-muted">
                                        <input type="checkbox" id="lwOnlyForTemplateLanguageMatchingContact" 
                                            data-lw-plugin="lwSwitchery" data-color="#22A755" 
                                            name="restrict_by_templated_contact_language">
                                        {!! __tr('Restrict by Language Code - Send only to the contacts whose language code matches with template language code.') !!}
                                    </label>
                                </div>

                                {{-- Schedule Field --}}
                                <fieldset x-data="{scheduleNow:true}">
                                    <legend class="mt-4 text-success fw-bold">{{ __tr('Schedule') }}</legend>
                                    <div class="form-group pt-2">
                                        <label for="lwNowCampaign">
                                            <input x-model="scheduleNow" type="checkbox" id="lwNowCampaign" 
                                                data-lw-plugin="lwSwitchery" checked 
                                                data-color="#22A755" name="schedule_now">
                                            {{ __tr('Now') }}
                                        </label>
                                    </div>

                                    {{-- Timezone + Schedule At --}}
                                    <div x-show="!scheduleNow" class="animate__animated animate__fadeIn">
                                        <x-lw.input-field type="selectize" name="timezone" :label="__tr('Select your Timezone')" 
                                            data-selected="{{ getVendorSettings('timezone') }}">
                                            <x-slot name="selectOptions">
                                                @foreach (getTimezonesArray() as $timezone)
                                                    <option value="{{ $timezone['value'] }}">{{ $timezone['text'] }}</option>
                                                @endforeach
                                            </x-slot>
                                        </x-lw.input-field>

                                        <x-lw.input-field type="datetime-local" id="lwScheduleAt" 
                                            min="{{ formatDateTime(now(), 'Y-m-d\TH:i:s') }}"
                                            :label="__tr('Schedule At')" name="schedule_at" required />
                                    </div>
                                </fieldset>
                            </fieldset>

                            {{-- Phone Number Include --}}
                            @include('whatsapp.from-phone-number')

                            {{-- Submit Button --}}
                            <div class="my-4 text-center">
                                <button type="submit" 
                                    class="btn btn-success btn-md px-5 animate__animated animate__pulse animate__delay-1s"
                                    style="transition: background-color 0.3s ease;">
                                    {{ __('Schedule Campaign ') }}<i class="fas fa-paper-plane me-2"></i>
                                </button>
                            </div>
                        </x-lw.form>

                        <template type="text/template" id="lwScheduleMessageConfirmation">
                            <h3>{{  __tr('Are you sure?') }}</h3>
                            <p>{{  __tr('You want to schedule a WhatsApp Template Message. Test message will be sent to your selected test contact immediately and on success it will get scheduled for the selected group contacts ') }}</p>
                        </template>
                        @endif
                </div>
            </div>
        </div>
    </div>
          </div>
</div>
@endsection()
@push('appScripts')
<script>
    (function($){
            'use strict';
            window.clearTemplateContainer = function(inputData) {
                $('#lwTemplateStructureContainer').text('');
                return inputData;
            };
            @if(request()->use_template)
            // Initial Change if required
            __DataRequest.post('{{ route('vendor.request.template.view') }}', {
                'template_selection' : '{{ request()->use_template }}',
            }, function() {
                __DataRequest.updateModels({selectedTemplate:'{{ request()->use_template }}'});
                    _.defer(function(){
                        if ($('#lwTemplateStructureContainer').find('.lw-file-uploader').length) {
                        window.initUploader();
                    }
                });
            }, {
                eventStreamUpdate: true
            });
            @endif
        })(jQuery);
</script>
@endpush