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

                <!-- Modern Flex container for title and buttons -->
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 p-4 bg-white rounded-lg shadow-sm border" style="gap: 16px;">
                    <div>
                        <h2 class="font-weight-bold mb-1 text-dark d-flex align-items-center" style="gap: 10px;">
                            <i class="fas fa-paper-plane text-success"></i> {{ __tr('Launch WhatsApp Campaign') }}
                        </h2>
                        <p class="text-muted mb-0 small">{{ __tr('Select a verified Meta template, configure variable parameters, and dispatch to your audience.') }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <a class="btn btn-outline-secondary lw-ajax-link-action"
                            data-confirm="{{ __tr('On template sync, page will be refreshed') }}"
                            data-callback="__Utils.viewReload"
                            data-method="post"
                            href="{{ route('vendor.whatsapp_service.templates.write.sync') }}">
                                <i class="fas fa-sync-alt mr-1"></i> {{ __tr('Sync Meta Templates') }}
                        </a>

                        <a class="btn btn-secondary" href="{{ route('vendor.campaign.read.list_view') }}">
                            <i class="fas fa-list mr-1"></i> {{ __tr('All Campaigns') }}
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
                            <input type="hidden" id="lwBodyHasVariables" name="body_has_variables" value="0">
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-lw.input-field type="selectize" data-lw-plugin="lwSelectize" id="lwSelectGroupsField"
                                            :label="__tr('Groups/Contact')" name="contact_group" data-selectize-option-class="contact-group-option">
                                            <x-slot name="selectOptions">
                                                {{-- Debug: Log contact groups data --}}
                                                @php
                                                    $groupNames = [];
                                                    if (isset($vendorContactGroups) && is_array($vendorContactGroups)) {
                                                        foreach ($vendorContactGroups as $group) {
                                                            $groupNames[] = $group['title'] ?? 'No title';
                                                        }
                                                    }
                                                    
                                                    \Log::info('Frontend - Contact groups data:', [
                                                        'totalContactsCount' => $totalContactsCount ?? 'Not set',
                                                        'vendorContactGroups_count' => count($vendorContactGroups ?? []),
                                                        'group_names' => $groupNames,
                                                        'vendorContactGroups_data' => $vendorContactGroups ?? 'Not set'
                                                    ]);
                                                @endphp
                                                
                                                <option value="">{{ __tr('Select Contacts Group') }}</option>
                                                <option value="all_contacts">{{ __tr('All Contacts') }} ({{ $totalContactsCount ?? 0 }} {{ __tr('contacts') }})</option>
                                                @if(isset($vendorContactGroups) && is_array($vendorContactGroups))
                                                    @foreach($vendorContactGroups as $vendorContactGroup)
                                                        <option value="{{ $vendorContactGroup['_id'] }}" data-group-uid="{{ $vendorContactGroup['_uid'] }}">{{ $vendorContactGroup['title'] }} ({{ $vendorContactGroup['contacts_count'] ?? 0 }} {{ __tr('contacts') }})</option>
                                                    @endforeach
                                                @else
                                                    {{-- Debug: Show if no data --}}
                                                    <option value="" disabled>No contact groups found</option>
                                                @endif
                                            </x-slot>
                                        </x-lw.input-field>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Contacts Display Section --}}
                                        <div id="contactsDisplaySection" style="display: none;">
                                            <label class="form-label">{{ __tr('Group Contacts') }}</label>
                                            <div class="card" style="border: 1px solid #22A755; max-height: 300px;">
                                                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #f9fff9;">
                                                    <span class="badge badge-info" id="contactsCount">{{ __tr('0 contacts') }}</span>
                                                    <div class="input-group" style="max-width: 200px;">
                                                        <input type="text" id="contactsSearch" class="form-control form-control-sm" placeholder="{{ __tr('Search...') }}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body p-2" style="max-height: 250px; overflow-y: auto;">
                                                    <div id="contactsLoading" class="text-center py-3">
                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                            <span class="sr-only">{{ __tr('Loading...') }}</span>
                                                        </div>
                                                        <p class="mt-2 mb-0 small">{{ __tr('Loading contacts...') }}</p>
                                                    </div>
                                                    <div id="contactsList" style="display: none;">
                                                        <!-- Contacts will be loaded here -->
                                                    </div>
                                                    <div id="contactsError" class="alert alert-danger alert-sm" style="display: none;">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ __tr('Error loading contacts') }}
                                                    </div>
                                                    <div id="contactsEmpty" class="text-center text-muted py-3" style="display: none;">
                                                        <i class="fas fa-users fa-2x mb-2"></i>
                                                        <p class="mb-0 small">{{ __tr('No contacts found in this group') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Individual Phone Numbers --}}
                                <div class="form-group mt-3" id="lwIndividualNumbersBlock">
                                    <label for="lwPhoneNumbersField" class="form-label">
                                        {{ __tr('Individual Phone Numbers') }}
                                        <span class="badge badge-info ml-2">{{ __tr('Optional') }}</span>
                                    </label>
                                    <textarea 
                                        id="lwPhoneNumbersField" 
                                        name="phone_numbers" 
                                        class="form-control" 
                                        rows="3" 
                                        placeholder="e.g., 123456789012, 987654321098, 555123456789"
                                        style="border: 1px solid #22A755; border-radius: 8px; padding: 12px; font-family: monospace;"
                                        oninput="formatPhoneNumbers(this)"
                                        onkeypress="return isNumberKey(event)"
                                    ></textarea>
                                    <small class="form-text text-muted" id="lwIndividualNumbersHelp">
                                        {{ __tr('Enter phone numbers with country code (without + or 0 prefix). Only numeric input allowed. Commas will be automatically added after every 12 digits.') }}
                                        <br>
                                        <strong class="text-success">{{ __tr('💡 Tip: You can send to individual numbers without selecting a contact group!') }}</strong>
                                    </small>
                                </div>

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
                                    {{ __('Schedules Campaign ') }}<i class="fas fa-paper-plane me-2"></i>
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
@push('appStyles')
<style>
/* Contact group dropdown styling */
.contact-group-option {
    position: relative;
}

.contact-group-option .contact-count {
    color: #6c757d;
    font-size: 0.9em;
    font-weight: normal;
}

.selectize-dropdown .option[data-value="all_contacts"] {
    font-weight: bold;
    background-color: #f8f9fa;
}

.selectize-dropdown .option[data-value="all_contacts"]:hover {
    background-color: #e9ecef;
}

/* Enhanced dropdown option styling */
.selectize-dropdown .option .group-name {
    font-weight: 500;
    color: #333;
}

.selectize-dropdown .option .contact-count {
    color: #6c757d;
    font-size: 0.85em;
    font-weight: normal;
    margin-left: 5px;
}

.selectize-dropdown .option[data-value="all_contacts"] .group-name {
    font-weight: bold;
    color: #22A755;
}

.selectize-dropdown .option[data-value="all_contacts"] .contact-count {
    color: #22A755;
    font-weight: 600;
}
</style>
@endpush
@push('appScripts')
<script>
    (function($){
            'use strict';
             
            window.clearTemplateContainer = function(inputData) {
                $('#lwTemplateStructureContainer').text('');
                // reset body variable flag until new template renders
                $('#lwBodyHasVariables').val('0');
                return inputData;
            };

  function validatePhoneNumbers() {
      const phoneNumbersField = document.getElementById  ('lwPhoneNumbersField');
            if (!phoneNumbersField) return;

         phoneNumbersField.addEventListener('blur', function() {
        let value = this.value;
        // Remove any non-numeric characters except commas
        value = value.replace(/[^\d,]/g, '');
        // Remove multiple consecutive commas
        value = value.replace(/,+/g, ',');
        // Remove leading/trailing commas
        value = value.replace(/^,|,$/g, '');
        
        this.value = value;

        // Validate individual numbers
        const numbers = value.split(',').filter(num => num.trim() !== '');
        const invalidNumbers = numbers.filter(num => {
            const cleanNum = num.trim();
            return cleanNum.length < 10 || cleanNum.length > 15;
        });

        let feedback = this.parentNode.querySelector('.phone-validation-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'phone-validation-feedback mt-1';
            this.parentNode.appendChild(feedback);
        }

        if (invalidNumbers.length > 0) {
            feedback.innerHTML = `<small class="text-danger">Invalid numbers: ${invalidNumbers.join(', ')} (must be 10-15 digits)</small>`;
            feedback.style.display = 'block';
        } else if (numbers.length > 0) {
            feedback.innerHTML = `<small class="text-success">✓ ${numbers.length} valid phone number(s)</small>`;
            feedback.style.display = 'block';
        } else {
            feedback.style.display = 'none';
        }
    });
    }


            // Form validation for contact group and phone numbers
            function validateFormSubmission() {
                const contactGroupField = document.getElementById('lwSelectGroupsField');
                const phoneNumbersField = document.getElementById('lwPhoneNumbersField');
                const form = document.querySelector('form[action*="campaign.schedule.process"]');
                
                if (!form) return;
                
                form.addEventListener('submit', function(e) {
                    const contactGroupValue = contactGroupField ? contactGroupField.value : '';
                    const phoneNumbersValue = phoneNumbersField ? phoneNumbersField.value.trim() : '';
                    
                    // Debug: Log form data including media fields
                    console.log('📋 Form submission data:');
                    const formData = new FormData(form);
                    for (let [key, value] of formData.entries()) {
                        console.log(`  ${key}: ${value}`);
                    }
                    
                    // Check if media fields are present
                    const mediaImageField = form.querySelector('input[name="media[image]"]');
                    const mediaVideoField = form.querySelector('input[name="media[video]"]');
                    const mediaDocumentField = form.querySelector('input[name="media[document]"]');
                    
                    console.log('📎 Media fields status:');
                    console.log(`  media[image]: ${mediaImageField ? mediaImageField.value : 'not found'}`);
                    console.log(`  media[video]: ${mediaVideoField ? mediaVideoField.value : 'not found'}`);
                    console.log(`  media[document]: ${mediaDocumentField ? mediaDocumentField.value : 'not found'}`);
                    
                    // Check if both contact group and phone numbers are empty
                    if ((!contactGroupValue || contactGroupValue === '') && !phoneNumbersValue) {
                        e.preventDefault();
                        alert('{{ __tr("Please select a contact group or enter individual phone numbers.") }}');
                        return false;
                    }
                    
                    // If phone numbers are provided, validate them
                    if (phoneNumbersValue) {
                        const numbers = phoneNumbersValue.split(',').filter(num => num.trim() !== '');
                        const invalidNumbers = numbers.filter(num => {
                            const cleanNum = num.trim();
                            return cleanNum.length < 10 || cleanNum.length > 15;
                        });
                        
                        if (invalidNumbers.length > 0) {
                            e.preventDefault();
                            alert('{{ __tr("Please fix invalid phone numbers:") }} ' + invalidNumbers.join(', '));
                            return false;
                        }
                    }
                });
            }

            // Initialize Contact Group Dropdown with enhanced formatting
            function initializeContactGroupDropdown() {
                const contactGroupField = document.getElementById('lwSelectGroupsField');
                if (!contactGroupField) {
                    console.log('❌ Contact group field not found');
                    return;
                }
                
                console.log('✅ Contact group field found:', contactGroupField);
                console.log('📋 Contact group options:', contactGroupField.querySelectorAll('option'));
                
                // Wait for selectize to be initialized
                setTimeout(function() {
                    const selectizeInstance = contactGroupField.selectize;
                    if (selectizeInstance) {
                        // Enhance the dropdown options display
                        selectizeInstance.on('dropdown_open', function() {
                            // Add custom styling to options
                            const dropdown = document.querySelector('.selectize-dropdown');
                            if (dropdown) {
                                const options = dropdown.querySelectorAll('.option');
                                options.forEach(function(option) {
                                    const text = option.textContent;
                                    if (text.includes('(') && text.includes('contacts)')) {
                                        // Split the text to separate group name and count
                                        const parts = text.split(' (');
                                        if (parts.length === 2) {
                                            const groupName = parts[0];
                                            const countPart = parts[1].replace(')', '');
                                            
                                            // Create enhanced HTML structure
                                            option.innerHTML = `
                                                <span class="group-name">${groupName}</span>
                                                <span class="contact-count">(${countPart})</span>
                                            `;
                                        }
                                    }
                                });
                            }
                        });
                    }
                }, 500);
            }

            // Contact Group Selection and Display
            function initializeContactGroupDisplay() {
                const contactGroupField = document.getElementById('lwSelectGroupsField');
                const contactsDisplaySection = document.getElementById('contactsDisplaySection');
                
                if (!contactGroupField || !contactsDisplaySection) return;
                
                // Handle contact group selection change
                contactGroupField.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const selectedOption = this.options[this.selectedIndex];
                    const groupUid = selectedOption.getAttribute('data-group-uid');
                    
                    if (selectedValue && selectedValue !== 'all_contacts' && groupUid) {
                        // Show the contacts display section
                        contactsDisplaySection.style.display = 'block';
                        // Load contacts for the selected group using UID
                        loadGroupContacts(groupUid);
                    } else {
                        // Hide the contacts display section
                        contactsDisplaySection.style.display = 'none';
                    }
                });
                
                // Handle search functionality
                const contactsSearch = document.getElementById('contactsSearch');
                if (contactsSearch) {
                    contactsSearch.addEventListener('input', function() {
                        filterContacts(this.value);
                    });
                }
            }
            
            // Load group contacts via AJAX
            function loadGroupContacts(groupUid) {
                const loading = document.getElementById('contactsLoading');
                const content = document.getElementById('contactsList');
                const error = document.getElementById('contactsError');
                const empty = document.getElementById('contactsEmpty');
                const contactsCount = document.getElementById('contactsCount');
                
                // Show loading state
                loading.style.display = 'block';
                content.style.display = 'none';
                error.style.display = 'none';
                empty.style.display = 'none';
                
                // Make AJAX request to get contacts
                const baseUrl = '{{ route("vendor.contact.read.list", ["groupUid" => "GROUP_UID_PLACEHOLDER"]) }}';
                const contactUrl = baseUrl.replace('GROUP_UID_PLACEHOLDER', groupUid);
                
                console.log('Loading contacts for group UID:', groupUid);
                console.log('Contact URL:', contactUrl);
                
                $.ajax({
                    url: contactUrl,
                    method: 'GET',
                    data: {
                        per_page: 1000 // Get all contacts for the group
                    },
                    success: function(response) {
                        console.log('Contact API Response:', response);
                        loading.style.display = 'none';
                        
                        if (response.data && response.data.length > 0) {
                            displayContacts(response.data);
                            content.style.display = 'block';
                        } else {
                            empty.style.display = 'block';
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Contact API Error:', xhr, status, error);
                        loading.style.display = 'none';
                        error.style.display = 'block';
                    }
                });
            }
            
            // Display contacts in the section
            function displayContacts(contacts) {
                const contactsList = document.getElementById('contactsList');
                const contactsCount = document.getElementById('contactsCount');
                
                console.log('Displaying contacts:', contacts);
                
                let html = '';
                contacts.forEach(function(contact) {
                    const fullName = (contact.first_name || '') + ' ' + (contact.last_name || '');
                    const phone = contact.phone_number || contact.wa_id || 'N/A';
                    const country = contact.country_name || 'N/A';
                    
                    html += `
                        <div class="contact-item mb-2 p-2 border rounded" data-name="${fullName.toLowerCase()}" data-phone="${phone}" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-dark" style="font-size: 0.9rem;">${fullName.trim() || '{{ __tr("No Name") }}'}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-phone me-1"></i>${phone}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-globe me-1"></i>${country}
                                    </small>
                                </div>
                                <span class="badge badge-light badge-sm">${phone}</span>
                            </div>
                        </div>
                    `;
                });
                
                contactsList.innerHTML = html;
                contactsCount.textContent = `${contacts.length} {{ __tr("contacts") }}`;
            }
            
            // Filter contacts based on search term
            function filterContacts(searchTerm) {
                const contactItems = document.querySelectorAll('.contact-item');
                const term = searchTerm.toLowerCase();
                
                contactItems.forEach(function(item) {
                    const name = item.getAttribute('data-name');
                    const phone = item.getAttribute('data-phone');
                    
                    if (name.includes(term) || phone.includes(term)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }



            // Function to show immediate preview when file is selected while creating a campaign
            function showImmediatePreview(file) {
                
                if (file.getFileEncodeDataURL) {
                    file.getFileEncodeDataURL().then(dataURL => {
                        console.log('✅ Got data URL from FilePond');
                        updateWhatsAppPreviewImage(dataURL);
                    }).catch(err => {
                        console.error('Error getting data URL:', err);
                        tryFileReaderFallback(file);
                    });
                } else {
                    tryFileReaderFallback(file);
                }
            }
            
        
            // Fallback method using FileReader
            function tryFileReaderFallback(file) {
                console.log('🔄 Trying FileReader fallback');
                const fileObject = file.file || file;
                if (fileObject && fileObject instanceof File) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        console.log('✅ Got data URL from FileReader');
                        updateWhatsAppPreviewImage(e.target.result);
                    };
                    reader.onerror = function(e) {
                        console.error('FileReader error:', e);
                    };
                    reader.readAsDataURL(fileObject);
                } else {
                    console.warn('⚠️ Could not get file object for preview');
                }
            }

            // Function to update WhatsApp preview image immediately after upload
            function updateWhatsAppPreviewImage(imagePath) {
                // Only proceed if we have a valid image path
                if (!imagePath || imagePath === '') {
                    console.log('⚠️ No image path provided, skipping preview update');
                    return;
                }
                
                console.log('🔄 Updating WhatsApp preview image with:', imagePath);
                
                // Try multiple selectors to find the elements
                const defaultIcon = document.querySelector('#defaultHeaderIcon') || 
                                  document.querySelector('.lw-whatsapp-preview #defaultHeaderIcon') ||
                                  document.querySelector('.lw-whatsapp-preview .fa-image');
                const uploadedImage = document.querySelector('#uploadedHeaderImage') || 
                                    document.querySelector('.lw-whatsapp-preview #uploadedHeaderImage') ||
                                    document.querySelector('.lw-whatsapp-preview img[src*="' + imagePath + '"]');

                console.log('Default icon found:', defaultIcon);
                console.log('Uploaded image element found:', uploadedImage);

                if (defaultIcon) {
                    defaultIcon.style.display = 'none';
                    console.log('✅ Default header icon hidden');
                } else {
                    console.warn('⚠️ Default header icon not found in WhatsApp preview');
                }
                
                if (uploadedImage) {
                    uploadedImage.src = imagePath;
                    uploadedImage.style.display = 'block';
                    uploadedImage.style.position = 'absolute';
                    uploadedImage.style.top = '0';
                    uploadedImage.style.left = '0';
                    uploadedImage.style.width = '100%';
                    uploadedImage.style.height = '100%';
                    uploadedImage.style.objectFit = 'cover';
                    uploadedImage.style.zIndex = '2';
                    uploadedImage.style.borderRadius = '0';
                    console.log('✅ Header image updated in WhatsApp preview:', imagePath);
                    
                    // Also update the hidden input field (with error handling)
                    try {
                        updateHeaderMediaHiddenField(imagePath, 'image');
                    } catch (error) {
                        console.error('Error updating hidden field:', error);
                    }
                } else {
                    console.warn('⚠️ Uploaded image element not found in WhatsApp preview');
                    
                    // Try to create the image element if it doesn't exist
                    const headerPlaceholder = document.querySelector('.lw-whatsapp-header-placeholder');
                    if (headerPlaceholder) {
                        const newImage = document.createElement('img');
                        newImage.id = 'uploadedHeaderImage';
                        newImage.src = imagePath;
                        newImage.style.position = 'absolute';
                        newImage.style.top = '0';
                        newImage.style.left = '0';
                        newImage.style.width = '100%';
                        newImage.style.height = '100%';
                        newImage.style.objectFit = 'cover';
                        newImage.style.zIndex = '2';
                        newImage.style.borderRadius = '0';
                        newImage.style.display = 'block';
                        
                        headerPlaceholder.appendChild(newImage);
                        console.log('✅ Created new uploaded image element');
                        
                        // Hide default icon
                        if (defaultIcon) {
                            defaultIcon.style.display = 'none';
                        }
                    }
                }
            }

           
            
          

          

            // Function to update hidden input field with media path
            function updateHeaderMediaHiddenField(mediaPath, mediaType = 'image') {
                // Look for the correct hidden input field based on the template structure
                const hiddenInput = document.querySelector(`#lwHeader${mediaType.charAt(0).toUpperCase() + mediaType.slice(1)}`) || 
                                   document.querySelector(`input[name="header_${mediaType}"]`) ||
                                   document.querySelector(`input[name="media[${mediaType}]"]`);
                if (hiddenInput) {
                    hiddenInput.value = mediaPath;
                    console.log(`✅ Header ${mediaType} hidden field updated:`, mediaPath);
                    
                    // Also ensure we have the media[type] field for the controller
                    const mediaField = document.querySelector(`input[name="media[${mediaType}]"]`);
                    if (!mediaField && hiddenInput.name !== `media[${mediaType}]`) {
                        // Create or update the media[type] field
                        let mediaInput = document.querySelector(`input[name="media[${mediaType}]"]`);
                        if (!mediaInput) {
                            mediaInput = document.createElement('input');
                            mediaInput.type = 'hidden';
                            mediaInput.name = `media[${mediaType}]`;
                            hiddenInput.parentNode.appendChild(mediaInput);
                        }
                        mediaInput.value = mediaPath;
                        console.log(`✅ Media[${mediaType}] field created/updated:`, mediaPath);
                    }
                    
                    return true;
                } else {
                    console.warn(`⚠️ Header ${mediaType} hidden field not found`);
                    return false;
                }
            }

            // Manual function to test image preview (for debugging)
            window.testImagePreview = function(imagePath) {
                console.log('🧪 Testing image preview with path:', imagePath);
                updateWhatsAppPreviewImage(imagePath);
                updateHeaderMediaHiddenField(imagePath, 'image');
            };

            // Manual function to test preview with a sample image
            window.testPreviewWithSample = function() {
                const sampleImagePath = 'https://via.placeholder.com/400x200/22A755/ffffff?text=Test+Image';
                console.log('🧪 Testing preview with sample image:', sampleImagePath);
                updateWhatsAppPreviewImage(sampleImagePath);
            };

            // Debug function to check preview elements
            window.debugPreviewElements = function() {
                console.log('🔍 Debugging preview elements:');
                
                const elements = [
                    { selector: '#defaultHeaderIcon', name: 'Default Icon' },
                    { selector: '#uploadedHeaderImage', name: 'Uploaded Image' },
                    { selector: '.lw-whatsapp-header-placeholder', name: 'Header Placeholder' },
                    { selector: '.lw-whatsapp-preview', name: 'WhatsApp Preview' }
                ];
                
                elements.forEach(el => {
                    const element = document.querySelector(el.selector);
                    if (element) {
                        console.log(`✅ ${el.name}: Found`, element);
                        if (el.name === 'Default Icon') {
                            console.log(`   Display: ${element.style.display}, Opacity: ${element.style.opacity}`);
                        }
                        if (el.name === 'Uploaded Image') {
                            console.log(`   Display: ${element.style.display}, Src: ${element.src}`);
                        }
                    } else {
                        console.log(`❌ ${el.name}: Not found`);
                    }
                });
                
                // Check all image elements in preview
                const allImages = document.querySelectorAll('.lw-whatsapp-preview img');
                console.log(`📷 Total images in preview: ${allImages.length}`);
                allImages.forEach((img, index) => {
                    console.log(`   Image ${index + 1}:`, img.id, img.src, img.style.display);
                });
            };

          

            // Debug function to check hidden field values
            window.debugHiddenFields = function() {
                console.log('🔍 Checking hidden field values:');
                const fields = [
                    { selector: '#lwHeaderImage', name: 'header_image' },
                    { selector: 'input[name="media[image]"]', name: 'media[image]' },
                    { selector: '#lwHeaderVideo', name: 'header_video' },
                    { selector: 'input[name="media[video]"]', name: 'media[video]' },
                    { selector: '#lwHeaderDocument', name: 'header_document' },
                    { selector: 'input[name="media[document]"]', name: 'media[document]' }
                ];
                
                fields.forEach(field => {
                    const element = document.querySelector(field.selector);
                    if (element) {
                        console.log(`  ${field.name}: "${element.value}"`);
                    } else {
                        console.log(`  ${field.name}: not found`);
                    }
                });
                
              
            };

           

           





         

            // Callback function for FilePond upload completion
            window.updateMediaFields = function(requestData, element) {
                const mediaPath = requestData.data.path;
                const fileName = requestData.data.fileName;
                
                // Determine media type based on the element ID (with null check)
                let mediaType = 'image';
                
                // Handle both DOM elements and jQuery objects
                const actualElement = element && element.length ? element[0] : element;
                
                if (actualElement && actualElement.id) {
                    if (actualElement.id.includes('Video')) {
                        mediaType = 'video';
                    } else if (actualElement.id.includes('Document')) {
                        mediaType = 'document';
                    }
                } else {
                    // Fallback: try to determine from the current FilePond element
                    const currentFilePond = document.querySelector('.filepond--root[data-pond-initialized="true"]');
                    if (currentFilePond && currentFilePond.id) {
                        if (currentFilePond.id.includes('Video')) {
                            mediaType = 'video';
                        } else if (currentFilePond.id.includes('Document')) {
                            mediaType = 'document';
                        }
                    }
                }
                
                // Update the media[type] field with the full path for preview
                const mediaField = document.querySelector(`input[name="media[${mediaType}]"]`);
                if (mediaField) {
                    mediaField.value = mediaPath;
                }
                
                // Update preview for images
                if (mediaType === 'image') {
                    updateWhatsAppPreviewImage(mediaPath);
                }
            };

            // Initialize phone number validation when DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                validatePhoneNumbers();
                validateFormSubmission();
                detectAndToggleIndividualNumbers();
                
                initializeContactGroupDisplay();
                initializeContactGroupDropdown();
                
                // Initialize image preview event listeners only (no automatic preview)
                setTimeout(function() {
                    initializeImagePreview();
                }, 1000);
                
                // Override the existing FilePond initialization to ensure our callbacks work
                const originalInitUploader = window.initUploader;
                window.initUploader = function() {
                    // Call the original function
                    originalInitUploader();
                    
                    // Add our custom handlers after FilePond is initialized
                    setTimeout(function() {
                        document.querySelectorAll('.lw-file-uploader').forEach(function(element) {
                            if (element.id && element.id.includes('Image')) {
                                // Try to get the FilePond instance
                                const pond = element._pond || element.__pond;
                                if (pond) {
                                    // Add our custom event listeners
                                    pond.on('processfile', function(error, file) {
                        if (error) {
                            return;
                        }

                                        try {
                        const response = JSON.parse(file.serverId);
                                            const mediaPath = response.data.path;
                                            
                                            // Update preview
                                            updateWhatsAppPreviewImage(mediaPath);
                                            
                                            // Update media field
                                            const mediaField = document.querySelector('input[name="media[image]"]');
                                            if (mediaField) {
                                                mediaField.value = mediaPath;
                                            }
                                        } catch (error) {
                                            // Silent error handling
                                        }
                                    });
                                }
                            }
                        });
                    }, 500);
                };
                
                // Add a mutation observer to watch for FilePond changes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        // Detect when template preview BODY is rendered and set flag
                        if (mutation.type === 'childList') {
                            const bodyPreview = document.querySelector('.body-text-preview');
                            if (bodyPreview) {
                                const hasVars = bodyPreview.querySelector('.variable-placeholder') !== null;
                                const hidden = document.getElementById('lwBodyHasVariables');
                                if (hidden) {
                                    hidden.value = hasVars ? '1' : '0';
                                }
                                toggleIndividualNumbersUI(hasVars);
                            }
                        }
                        if (mutation.type === 'attributes' && mutation.attributeName === 'data-file-id') {
                            const element = mutation.target;
                            if (element.classList.contains('filepond--file')) {
                                // Check if this is an image file
                                const fileName = element.querySelector('.filepond--file-info-main')?.textContent;
                                if (fileName && /\.(jpg|jpeg|png|gif|webp)$/i.test(fileName)) {
                                    // Try to get the file data and update preview
                                    setTimeout(function() {
                                        const filepondRoot = element.closest('.filepond--root');
                                        if (filepondRoot) {
                                            const pond = filepondRoot._pond;
                                            if (pond && pond.getFiles().length > 0) {
                                                const file = pond.getFiles()[0];
                                                if (file && file.serverId) {
                                                    try {
                                                        const response = JSON.parse(file.serverId);
                                                        const mediaPath = response.data.path;
                                                        updateWhatsAppPreviewImage(mediaPath);
                                                    } catch (error) {
                                                        // Silent error handling
                                                    }
                                                }
                                            }
                                        }
                                    }, 1000);
                                }
                            }
                        }
                    });
                });
                
                // Start observing
                observer.observe(document.body, {
                    attributes: true,
                    subtree: true,
                    attributeFilter: ['data-file-id'],
                    childList: true
                });
                
                // Also add a global FilePond event listener as a fallback
                document.addEventListener('FilePond:processfile', function(e) {
                    const pond = e.detail.pond;
                    const file = e.detail.file;
                    
                    // Check if this is an image upload
                    if (pond.element && pond.element.id && pond.element.id.includes('Image')) {
                        try {
                            const response = JSON.parse(file.serverId);
                            const mediaPath = response.data.path;
                            
                            // Update preview
                            updateWhatsAppPreviewImage(mediaPath);
                            
                            // Update media field
                            const mediaField = document.querySelector('input[name="media[image]"]');
                            if (mediaField) {
                                mediaField.value = mediaPath;
                            }
                        } catch (error) {
                            // Silent error handling
                        }
                    }
                });
            });
            function detectAndToggleIndividualNumbers() {
                const bodyPreview = document.querySelector('.body-text-preview');
                const hidden = document.getElementById('lwBodyHasVariables');
                const hasVars = bodyPreview && bodyPreview.querySelector('.variable-placeholder');
                if (hidden) hidden.value = hasVars ? '1' : '0';
                toggleIndividualNumbersUI(!!hasVars);
            }

            function toggleIndividualNumbersUI(disable) {
                const block = document.getElementById('lwIndividualNumbersBlock');
                const field = document.getElementById('lwPhoneNumbersField');
                const help = document.getElementById('lwIndividualNumbersHelp');
                if (!block || !field) return;
                if (disable) {
                    block.style.display = 'none';
                    field.value = '';
                } else {
                    block.style.display = '';
                }
            }


            

            // Function to initialize header media upload
            function initializeHeaderMediaUpload(mediaElement, mediaType) {
                // Don't create FilePond here - let the existing system handle it
                // Just add our custom event listeners
                mediaElement.setAttribute('data-pond-initialized', 'true');
                
                // Add click event listener to the upload area
                mediaElement.addEventListener('click', function() {
                    // Silent click handling
                });
                
                // Listen for FilePond events using document-level event delegation
                document.addEventListener('FilePond:addfile', function(e) {
                    const pond = e.detail.pond;
                    const file = e.detail.file;
                    
                    // Check if this is our media element
                    if (pond.element === mediaElement) {
                        // Show immediate preview for images
                        if (mediaType === 'image') {
                            showImmediatePreview(file);
                        }
                    }
                });
                
                document.addEventListener('FilePond:processfile', function(e) {
                    const pond = e.detail.pond;
                    const file = e.detail.file;
                    
                    // Check if this is our media element
                    if (pond.element === mediaElement) {
                        try {
                        // Parse backend JSON response
                        const response = JSON.parse(file.serverId);
                            const mediaPath = response.data.path;

                            // Update the hidden input field with the media path
                            updateHeaderMediaHiddenField(mediaPath, mediaType);

                            // Update the preview if it's an image
                            if (mediaType === 'image') {
                                updateWhatsAppPreviewImage(mediaPath);
                            }
                        } catch (error) {
                            // Silent error handling
                        }
                    }
                });
                
                // Listen for file removal
                document.addEventListener('FilePond:removefile', function(e) {
                    const pond = e.detail.pond;
                    
                    // Check if this is our media element
                    if (pond.element === mediaElement) {
                        // Clear all related hidden fields
                        const hiddenFields = [
                            document.querySelector(`#lwHeader${mediaType.charAt(0).toUpperCase() + mediaType.slice(1)}`),
                            document.querySelector(`input[name="header_${mediaType}"]`),
                            document.querySelector(`input[name="media[${mediaType}]"]`)
                        ];
                        
                        hiddenFields.forEach(field => {
                            if (field) {
                                field.value = '';
                            }
                        });
                        
                        // Reset preview to show default icon for images
                        if (mediaType === 'image') {
                            const defaultIcon = document.querySelector('#defaultHeaderIcon');
                            const uploadedImage = document.querySelector('#uploadedHeaderImage');
                            
                            if (defaultIcon) {
                                defaultIcon.style.display = 'block';
                            }
                            if (uploadedImage) {
                                uploadedImage.style.display = 'none';
                                uploadedImage.src = '';
                            }
                        }
                    }
                });
            }

        

            // Enhanced template change handler to reinitialize image preview
            const originalClearTemplateContainer = window.clearTemplateContainer;
            window.clearTemplateContainer = function(inputData) {
                // Call original function
                const result = originalClearTemplateContainer(inputData);
                
                // Reinitialize image preview after template container is updated
                setTimeout(function() {
                    // Wait for the new template content to be loaded
                    const checkForTemplateContent = setInterval(function() {
                        if ($('#lwTemplateStructureContainer').find('.lw-file-uploader').length > 0) {
                            clearInterval(checkForTemplateContent);
                            initializeImagePreview();
                        }
                    }, 100);
                    
                    // Stop checking after 5 seconds
                    setTimeout(function() {
                        clearInterval(checkForTemplateContent);
                    }, 5000);
                }, 100);
                
                return result;
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
                        // Initialize image preview after template loads
                        setTimeout(initializeImagePreview, 500);
                    }
                });
            }, {
                eventStreamUpdate: true
            });
            @endif
        })(jQuery);

        // Function to allow only numeric input and comma
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            // Allow backspace, delete, tab, escape, enter
            if (charCode == 8 || charCode == 9 || charCode == 27 || charCode == 13 || 
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (charCode == 65 && evt.ctrlKey === true) || 
                (charCode == 67 && evt.ctrlKey === true) || 
                (charCode == 86 && evt.ctrlKey === true) || 
                (charCode == 88 && evt.ctrlKey === true)) {
                return true;
            }
            // Allow comma
            if (charCode == 44) {
                return true;
            }
            // Ensure that it is a number and stop the keypress
            if ((charCode < 48 || charCode > 57)) {
                evt.preventDefault();
                return false;
            }
            return true;
        }

        // Sanitize input to keep only digits and commas (handles paste and edits)
        function formatPhoneNumbers(textarea) {
            var value = textarea.value;
            var cleaned = value.replace(/[^0-9,]/g, '');
            if (cleaned !== value) {
                var start = textarea.selectionStart;
                var end = textarea.selectionEnd;
                textarea.value = cleaned;
                // Restore cursor position best-effort
                textarea.setSelectionRange(start, end);
            }
        }

        // Function to format phone numbers with commas after every 12 digits
        // function formatPhoneNumbers(textarea) {
        //     let value = textarea.value;
        //     let cursorPosition = textarea.selectionStart;
            
        //     // Store the original cursor position relative to digits only
        //     let digitsBeforeCursor = 0;
        //     for (let i = 0; i < cursorPosition && i < value.length; i++) {
        //         if (value[i].match(/[0-9]/)) {
        //             digitsBeforeCursor++;
        //         }
        //     }
            
        //     // Remove all non-numeric characters except commas
        //     let numericValue = value.replace(/[^0-9,]/g, '');
            
        //     // Split by commas and process each number
        //     let numbers = numericValue.split(',');
        //     let formattedNumbers = [];
        //     let newCursorPosition = 0;
        //     let digitCount = 0;
            
        //     for (let i = 0; i < numbers.length; i++) {
        //         let number = numbers[i];
        //         // Remove any existing commas within the number
        //         number = number.replace(/,/g, '');
                
        //         // Add commas after every 12 digits
        //         let formattedNumber = '';
        //         for (let j = 0; j < number.length; j++) {
        //             if (j > 0 && j % 12 === 0) {
        //                 formattedNumber += ',';
        //             }
        //             formattedNumber += number[j];
        //             digitCount++;
                    
        //             // Set cursor position after the digit that was at the original cursor position
        //             if (digitCount === digitsBeforeCursor) {
        //                 newCursorPosition = formattedNumbers.join('').length + formattedNumber.length;
        //             }
        //         }
                
        //         formattedNumbers.push(formattedNumber);
                
        //         // Add comma between different phone numbers (except for the last one)
        //         if (i < numbers.length - 1) {
        //             formattedNumbers[formattedNumbers.length - 1] += ',';
        //         }
        //     }
            
        //     // Join with commas between different phone numbers
        //     let result = formattedNumbers.join('');
            
        //     // If cursor position wasn't set (end of input), put it at the end
        //     if (newCursorPosition === 0) {
        //         newCursorPosition = result.length;
        //     }
            
        //     // Update the textarea value
        //     textarea.value = result;
            
        //     // Set cursor position
        //     setTimeout(() => {
        //         textarea.setSelectionRange(newCursorPosition, newCursorPosition);
        //     }, 0);
        // }
        
        // Debug function to inspect contact group data
        window.debugContactGroups = function() {
            console.log('🔍 Debugging Contact Groups Data:');
            
            const contactGroupField = document.getElementById('lwSelectGroupsField');
            if (!contactGroupField) {
                console.log('❌ Contact group field not found');
                return;
            }
            
            const options = contactGroupField.querySelectorAll('option');
            console.log('📋 Total options found:', options.length);
            
            // Extract group names for easy viewing
            const groupNames = [];
            options.forEach((option, index) => {
                const optionData = {
                    index: index,
                    value: option.value,
                    text: option.textContent,
                    hasDataGroupUid: option.hasAttribute('data-group-uid'),
                    dataGroupUid: option.getAttribute('data-group-uid')
                };
                
                console.log(`Option ${index}:`, optionData);
                
                // Extract group name from text (remove contact count)
                if (option.value && option.value !== '') {
                    const groupName = option.textContent.split(' (')[0];
                    groupNames.push(groupName);
                }
            });
            
            console.log('📝 Group names found:', groupNames);
            console.log('📊 Total groups:', groupNames.length);
            
            // Check if selectize is initialized
            if (contactGroupField.selectize) {
                console.log('✅ Selectize is initialized');
                console.log('📊 Selectize options:', contactGroupField.selectize.options);
            } else {
                console.log('❌ Selectize not initialized yet');
            }
        };
    </script>

@endpush