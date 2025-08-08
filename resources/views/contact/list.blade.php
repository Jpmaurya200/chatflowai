@php
/**
* Component : Contact
* Controller : ContactController
* File : contact.list.blade.php
* ----------------------------------------------------------------------------- */
$currentGroup = $groupUid ? $vendorContactGroups->where('_uid', $groupUid)->first() : null;
@endphp
@extends('layouts.app', ['title' => __tr('Contacts')])
@section('content')
@include('users.partials.header', [
'title' => $groupUid ? __tr('__groupName__ group contacts', [
'__groupName__' => $currentGroup->title
]) : __tr(''),
// 'description' => $groupUid ? $currentGroup->description : '',
'class' => 'col-lg-7'
])
@php
$groupDescription = $groupUid ? $currentGroup->description : '';
@endphp
<div class="container-fluid mt-lg--6">
    <div class="row">
        <!-- button -->
        <div class="col-xl-12 mt-3">
            <div class="d-flex align-items-center justify-content-between flex-nowrap mt-5">
                <h1 class="mb-0"><i class="fas fa-layer-group me-2" style="color: #0B7753;"></i> Contacts</h1>
                <div class="d-flex align-items-center">
                    @if ($groupUid)
                    <a class="lw-btn btn btn-neo btn-neo-gradient-green" href="{{ route('vendor.contact.group.read.list_view') }}">
                        <i class="fas fa-arrow-left"></i> {{ __tr('Back to Contact Groups') }}
                    </a>
                    @endif
                    <button type="button" class="lw-btn btn btn-neo btn-neo-gradient-green ml-2" data-toggle="modal" data-target="#lwAddNewContact">
                        <i class="fas fa-plus"></i> {{ __tr('Create New Contact') }}
                    </button>
                    <button type="button" class="lw-btn btn btn-neo btn-neo-gradient-green ml-2" data-toggle="modal" data-target="#lwExportDialog">
                        <i class="fas fa-file-export"></i> {{ __tr('Export Contacts') }}
                    </button>
                    <button type="button" class="lw-btn btn btn-neo btn-neo-gradient-green ml-2" data-toggle="modal" data-target="#lwImportContactDialog">
                        <i class="fas fa-file-import"></i> {{ __tr('Import Contacts') }}
                    </button>
                </div>
            </div>
        </div>
        <!--/ button -->
        {{-- import contacts --}}
        <x-lw.modal id="lwImportContactDialog" :header="__tr('Import Contacts')" :hasForm="true"
            data-pre-callback="appFuncs.clearContainer">
            <x-lw.form id="lwImportContactDialogForm" :action="route('vendor.contact.write.import')"
                :data-callback-params="['modalId' => '#lwImportContactDialog', 'datatableId' => '#lwContactList']"
                data-callback="appFuncs.modelSuccessCallback">
                <div class="lw-form-modal-body">
                    <div class="alert alert-danger">
                        {{ __tr('Please use Template from Export contacts') }}
                    </div>
                    <p>{{ __tr('You can import excel file with new contacts or existing updated.') }}</p>
                    <div class="alert alert-light">
                        <h3>{{ __tr('Conventions') }}</h3>
                        <h4>{{ __tr('Mobile Number') }}</h4>
                        {{ __tr('Mobile number treated as unique entity, it should be with country code without prefixing
                        0 or +, if the Mobile number is found in the records other information for the same will get
                        updated with data from the excel.') }}
                        <div class="mt-3">
                            <h4>{{ __tr('Group') }}</h4>
                            {{ __tr('Use comma separated group title, make sure groups are already exists into the
                            system. Groups won\'t be deleted, only new groups will be assigned.') }}
                        </div>
                    </div>
                    <div class="form-group ">
                        <input id="lwImportDocumentFilepond" type="file" data-allow-revert="true"
                            data-label-idle="{{ __tr('Select XLSX File') }}" class="lw-file-uploader"
                            data-instant-upload="true"
                            data-action="<?= route('media.upload_temp_media', 'vendor_contact_import') ?>"
                            data-file-input-element="#lwImportDocument" data-allowed-media='{{ getMediaRestriction('
                            vendor_contact_import') }}'>
                        <input id="lwImportDocument" type="hidden" value="" name="document_name" />
                    </div>
                </div>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Process Import') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
        </x-lw.modal>
        {{-- /import contacts --}}
        {{-- export contacts --}}
        <x-lw.modal id="lwExportDialog" :header="__tr('Export Contacts')" :hasForm="true"
            data-pre-callback="appFuncs.clearContainer">
            <div class="lw-form-modal-body p-3">
                <h5>{{ __tr('Export with Data') }}</h5>
                <p>{{ __tr('You can export all contacts excel file and import it back with updated data.') }}</p>
                <a href="{{ route('vendor.contact.write.export', [
                    'exportType' => 'data'
                ]) }}" data-method="post" class="btn btn-primary">{{ __('Export Excel File with Data') }}</a>
                <hr>
                <h5>{{ __tr('Blank Excel Template') }}</h5>
                <p>{{ __tr('You can export blank excel file and fill with data according to column header and import it
                    for updates.') }}</p>
                <a href="{{ route('vendor.contact.write.export') }}" data-method="post" class="btn btn-primary">{{
                    __('Export Blank Template') }}</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
            </div>
        </x-lw.modal>
        {{-- /export contacts --}}
        <!-- Add New Contact Modal -->
        <x-lw.modal id="lwAddNewContact" :header="__tr('Add New Contact')" :hasForm="true"
            data-pre-callback="appFuncs.clearContainer">
            <!--  Add New Contact Form -->
            <x-lw.form id="lwAddNewContactForm" :action="route('vendor.contact.write.create')"
                :data-callback-params="['modalId' => '#lwAddNewContact', 'datatableId' => '#lwContactList']"
                data-callback="appFuncs.modelSuccessCallback">
                <!-- form body -->
                <div class="lw-form-modal-body">
                    <!-- form fields form fields -->
                    <!-- First_Name -->
                    <x-lw.input-field type="text" id="lwFirstNameField" data-form-group-class=""
                        :label="__tr('First Name')" name="first_name"  />
                    <!-- /First_Name -->
                    <!-- Last_Name -->
                    <x-lw.input-field type="text" id="lwLastNameField" data-form-group-class=""
                        :label="__tr('Last Name')" name="last_name"  />
                    <!-- /Last_Name -->
                    <!-- Country -->
                    <x-lw.input-field type="selectize" data-lw-plugin="lwSelectize" id="lwCountryField"
                        data-form-group-class="" data-selected=" " :label="__tr('Country')" name="country"
                        >
                        <x-slot name="selectOptions">
                            <option value="">{{ __tr('Country') }}</option>
                            @foreach(getCountryPhoneCodes() as $getCountryCode)
                            <option value="{{ $getCountryCode['_id'] }}">{{ $getCountryCode['name'] }}</option>
                            @endforeach
                        </x-slot>
                    </x-lw.input-field>
                    <!-- /Country -->
                    <!-- Phone_Number -->
                    <x-lw.input-field type="number" id="lwPhoneNumberField" data-form-group-class=""
                        :label="__tr('Mobile Number')" name="phone_number" minlength="9"
                        :helpText="__tr('Number should be with country code without 0 or +')" required="true" />
                    <!-- /Phone_Number -->
                    <!-- Language Code -->
                    <x-lw.input-field type="text" id="lwLanguageCodeField" data-form-group-class=""
                        :label="__tr('Language Code')" name="language_code" />
                    <!-- /Language Code -->
                    <!-- Email -->
                    <x-lw.input-field type="email" id="lwEmailField" data-form-group-class="" :label="__tr('Email')"
                        name="email" />
                    <!-- /Email -->
                    <x-lw.input-field type="selectize" data-lw-plugin="lwSelectize" id="lwSelectGroupsField"
                        data-form-group-class="" data-selected=" " :label="__tr('Groups')" name="contact_groups[]"
                        multiple>
                        <x-slot name="selectOptions">
                            <option value="">{{ __tr('Select Groups') }}</option>
                            @foreach($vendorContactGroups as $vendorContactGroup)
                            <option value="{{ $vendorContactGroup['_id'] }}">{{ $vendorContactGroup['title'] }} {{ $vendorContactGroup['status'] == 5  ? __tr('(Archived)') : '' }}</option>
                            @endforeach
                        </x-slot>
                    </x-lw.input-field>
                    <div class="my-3">
                        <x-lw.checkbox id="lwPromotionalOpt" name="whatsapp_opt_out" data-color="#ff0000" data-size="small" value="1" data-lw-plugin="lwSwitchery" :label="__tr('Opt out Marketing Messages')" />
                    </div>
                    <div class="my-3">
                        @if(isAiBotAvailable())
                        <x-lw.checkbox id="lwAiBotEnable" :checked="getVendorSettings('default_enable_flowise_ai_bot_for_users')" name="enable_ai_bot" value="1" data-size="small" 
                            data-lw-plugin="lwSwitchery" :label="__tr('Enable AI Bot')" />
                        @endif
                    </div>
                    <fieldset>
                        <legend>{{ __tr('Other Information') }}</legend>
                        @foreach ($vendorContactCustomFields as $vendorContactCustomField)
                        <x-lw.input-field type="{{ $vendorContactCustomField->input_type }}"
                            id="lwCustomField{{ $vendorContactCustomField->_id }}" data-form-group-class=""
                            :label="$vendorContactCustomField->input_name"
                            name="custom_input_fields[{{ $vendorContactCustomField->_uid }}]" />
                        @endforeach
                    </fieldset>
                </div>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
            <!--/  Add New Contact Form -->
        </x-lw.modal>
        <!--/ Add New Contact Modal -->

        <!-- Details Contact Modal -->
        <x-lw.modal id="lwDetailsContact" :header="__tr('Contact Details')">
            <!--  Details Contact Form -->
            <!-- Details body -->
            <div id="lwDetailsContactBody" class="lw-form-modal-body"></div>
            <script type="text/template" id="lwDetailsContactBody-template">
                <!-- form fields -->
                <div>
                    <label class="small">{{ __tr('First Name') }}:</label>
                    <div class="lw-details-item">
                        <%- __tData.first_name %>
                    </div>
                </div>

                <div>
                    <label class="small">{{ __tr('Last Name') }}:</label>
                    <div class="lw-details-item">
                        <%- __tData.last_name %>
                    </div>
                </div>

                <div>
                    <label class="small">{{ __tr('Country') }}:</label>
                    <div class="lw-details-item">
                        <%- __tData.country?.name %>
                    </div>
                </div>

                <div>
                    <label class="small">{{ __tr('Mobile Number') }}:</label>
                    <div class="lw-details-item">
                        <%- __tData.wa_id %>
                    </div>
                </div>
                <div>
                    <label class="small">{{ __tr('Language Code') }}:</label>
                    <div class="lw-details-item">
                        <%- __tData.language_code %>
                    </div>
                </div>

                <div>
                    <label class="small">{{ __tr('Email') }}:</label>
                    <div class="lw-details-item">
                        <%- __tData.email %>
                    </div>
                </div>

                <fieldset>
                    <legend>{{ __tr('Groups') }}</legend>
                    <% _.forEach(__tData.groups, function(value, key) { %>
                        <span class="badge badge-light">
                            <%- value.title %>
                        </span>
                        <% } ); %>
                </fieldset>
                <fieldset>
                    <legend>{{ __tr('Other Information') }}</legend>
                    @foreach ($vendorContactCustomFields as $vendorContactCustomField)
                    <div class="mb-2">
                        <label class="small">{{ $vendorContactCustomField->input_name }}:</label>
                        <div class="lw-details-item">
                            <%- _.get(_.find(__tData.custom_field_values, {'contact_custom_fields__id' : {{
                                $vendorContactCustomField->_id }} }), 'field_value') %>
                        </div>
                    </div>
                    @endforeach
                </fieldset>
            </script>
            <!--/  Details Contact Form -->
        </x-lw.modal>
        <!--/ Edit Contact Modal -->
         <!--Group description -->
        <div class="ml-3">
            <p class="card-text">{{$groupDescription
            }}</p>
        </div>
         <!--/ Group description -->
        <!-- Edit Contact Modal -->
        @include('contact.contact-edit-modal-partial')
        <!--/ Edit Contact Modal -->
        <div class="col-xl-12" x-cloak x-data="{isSelectedAll:false,selectedContacts: [],selectedGroupsForSelectedContacts:[],
            toggle(id) {
                if (this.selectedContacts.includes(id)) {
                    const index = this.selectedContacts.indexOf(id);
                    this.selectedContacts.splice(index, 1);
                    this.isSelectedAll = false;
                } else {
                    this.selectedContacts.push(id);
                    if($('.dataTables_wrapper table>tbody input[type=checkbox].lw-checkboxes').length == this.selectedContacts.length) {
                        this.isSelectedAll = true;
                    }
                };
            },toggleAll() {
                if(!this.isSelectedAll) {
                    $('.dataTables_wrapper table>tbody input[type=checkbox].lw-checkboxes').not(':checked').trigger('click');
                    this.isSelectedAll = true;
                } else {
                    $('.dataTables_wrapper table>tbody input[type=checkbox].lw-checkboxes:checked').trigger('click');
                    this.isSelectedAll = false;
                }
            },deleteSelectedContacts() {
                var that = this;
                showConfirmation('{{ __tr('Are you sure you want to delete all selected contacts?') }}', function() {
                    __DataRequest.post('{{ route('vendor.contacts.selected.write.delete') }}', {
                        'selected_contacts' : that.selectedContacts
                    });
                }, {
                    confirmButtonText: '{{ __tr('Yes') }}',
                    cancelButtonText: '{{ __tr('No') }}',
                    type: 'error'
                });
            }, assignGroupsToSelectedContacts(){
                var that = this;
                __DataRequest.post('{{ route('vendor.contacts.selected.write.assign_groups') }}', {
                    'selected_contacts' : that.selectedContacts,
                    'selected_groups' : that.selectedGroupsForSelectedContacts
                });
                $('#lwAssignGroups').modal('hide');
                $('.dataTables_wrapper table>tbody input[type=checkbox].lw-checkboxes:checked').trigger('click');
                this.isSelectedAll = false;
            }}" x-init="$('#lwContactList').on( 'draw.dt', function () {
                $('.dataTables_wrapper table>tbody input[type=checkbox].lw-checkboxes:checked').trigger('click');
                isSelectedAll = false;
            } );">
            <button x-show="!isSelectedAll" class="btn btn-dark btn-sm my-2" @click="toggleAll">{{ __tr('Select All')
                }}</button>
            <button x-show="isSelectedAll" class="btn btn-dark btn-sm my-2" @click="toggleAll">{{ __tr('Unselect All')
                }}</button>
            <div class="btn-group">
                <button :class="!selectedContacts.length ? 'disabled' : ''"
                    class="btn btn-danger mt-1 btn-sm dropdown-toggle" type="button" data-toggle="dropdown"
                    aria-expanded="false">
                    {{ __tr('Bulk Actions') }}
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" @click.prevent="deleteSelectedContacts" href="#">{{ __tr('Delete Selected
                        Contacts') }}</a>
                    <a class="dropdown-item" data-toggle="modal" data-target="#lwAssignGroups" href="#">{{ __tr('Assign
                        Group to Selected Contacts') }}</a>
                </div>
            </div>
            <!-- Assign Groups to the selected contacts -->
            <x-lw.modal id="lwAssignGroups" :header="__tr('Assign Groups to Selected Contacts')" :hasForm="true"
                data-pre-callback="appFuncs.clearContainer">
                <!-- form body -->
                <div class="lw-form-modal-body p-4">
                    <!-- form fields form fields -->
                    <x-lw.input-field x-model="selectedGroupsForSelectedContacts" type="selectize"
                        data-lw-plugin="lwSelectize" id="lwSelectGroupsField" data-form-group-class="" data-selected=" "
                        :label="__tr('Groups')" name="contact_groups[]" multiple>
                        <x-slot name="selectOptions">
                            <option value="">{{ __tr('Select Groups') }}</option>
                            @foreach($vendorContactGroups as $vendorContactGroup)
                            <option value="{{ $vendorContactGroup['_id'] }}">{{ $vendorContactGroup['title'] }} {{ $vendorContactGroup['status'] == 5  ? __tr('(Archived)') : '' }}</option>
                            @endforeach
                        </x-slot>
                    </x-lw.input-field>
                </div>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="button" @click="assignGroupsToSelectedContacts" class="btn btn-primary">{{
                        __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
                <!--/  Add New Contact Form -->
            </x-lw.modal>
            <!--/ Assign Groups to the selected contacts -->
            <div class="modern-table-container">
                <!-- Header Controls: Show entries (left) and Search (right) -->
                <div class="table-header-controls">
                    <div class="entries-control">
                        <label for="tl-entries-per-page" class="mb-0">{{ __tr('Show') }}</label>
                        <select id="tl-entries-per-page" class="entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="entries-text">{{ __tr('entries') }}</span>
                    </div>
                    <div class="search-control">
                        <input type="text" id="tl-table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <x-lw.datatable
                        data-page-length="100"
                        id="lwContactList"
                        class="modern-datatable table-hover align-middle"
                        lw-card-classes="border-0 rounded"
                        :url="route('vendor.contact.read.list', [
                            'groupUid' => $groupUid
                        ])">
                        <th style="width: 1px;padding:0;" data-name="none"></th>
                        <th data-name="none" data-template="#lwSelectMultipleContactsCheckbox">{{ __tr('ID') }}</th>
                        <th data-orderable="true" data-name="first_name">{{ __tr('First Name') }}</th>
                        <th data-orderable="true" data-name="last_name">{{ __tr('Last Name') }}</th>
                        <th data-name="phone_number">{{ __tr('Mobile Number') }}</th>
                        <th data-name="language_code">{{ __tr('Language Code') }}</th>
                        <th data-orderable="true" data-name="created_at">{{ __tr('Created on') }}</th>
                        <th data-name="country_name">{{ __tr('Country') }}</th>
                        <th data-orderable="true" data-name="email">{{ __tr('Email') }}</th>
                        <th data-orderable="true" data-name="whatsapp_opt_out">{{ __tr('Marketing') }}</th>
                        @if (isAiBotAvailable())
                        <th data-orderable="true" data-name="disable_ai_bot">{{ __tr('AI Bot') }}</th>
                        @endif
                        <th data-template="#contactActionColumnTemplate" name="null" class="text-right">{{ __tr('Action') }}</th>
                    </x-lw.datatable>
                </div>
            </div>
        </div>
        <!-- action template -->
        <script type="text/template" id="lwSelectMultipleContactsCheckbox">
            <input @click="toggle('<%- __tData._uid %>')" type="checkbox" name="selected_contacts[]" class="lw-checkboxes custom-checkbox" value="<%- __tData._uid %>">
        </script>
        <script type="text/template" id="contactActionColumnTemplate">
            <div class="dropdown d-inline-block action-dropdown" data-uid="<%- __tData._uid %>">
                <button class="btn btn-sm btn-light action-kebab-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __tr('Actions') }}">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <a class="dropdown-item row-toggle-select" href="#" data-uid="<%- __tData._uid %>">
                        <i class="far fa-check-square mr-2"></i><span class="row-toggle-select-label">{{ __tr('Select') }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item lw-ajax-link-action" data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Details') }}" data-response-template="#lwDetailsContactBody" href="<%= __Utils.apiURL("{{ route('vendor.contact.read.update.data', [ 'contactIdOrUid']) }}", {'contactIdOrUid': __tData._uid}) %>"  data-toggle="modal" data-target="#lwDetailsContact">
                        <i class="fa fa-info-circle mr-2"></i>{{  __tr('Details') }}
                    </a>
                    <a class="dropdown-item lw-ajax-link-action" data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Edit') }}" data-response-template="#lwEditContactBody" href="<%= __Utils.apiURL("{{ route('vendor.contact.read.update.data', [ 'contactIdOrUid']) }}", {'contactIdOrUid': __tData._uid}) %>"  data-toggle="modal" data-target="#lwEditContact">
                        <i class="fa fa-edit mr-2"></i>{{  __tr('Edit') }}
                    </a>
                    @if(hasVendorAccess('messaging'))
                    <a class="dropdown-item" data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Send Template Message') }}" href="<%= __Utils.apiURL("{{ route('vendor.template_message.contact.view', ['contactUid']) }}",{'contactUid': __tData._uid}) %>">
                        <i class="fab fa-whatsapp mr-2 text-success"></i>{{  __tr('Send Template Message') }}
                    </a>
                    <a class="dropdown-item" data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Chat') }}" href="<%= __Utils.apiURL("{{ route('vendor.chat_message.contact.view', ['contactUid']) }}",{'contactUid': __tData._uid}) %>">
                        <i class="fab fa-whatsapp mr-2 text-success"></i>{{  __tr('Chat') }}
                    </a>
                    @endif
                    @if($currentGroup!=null)
                    <a class="dropdown-item lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.contact.write.remove',['contactIdOrUid', 'groupUid' => $groupUid]) }}",{ 'contactIdOrUid': __tData._uid }) %>" data-confirm="#lwRemoveContact-template" title="{{ __tr('Remove contact from group') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwContactList']) }}" data-callback="appFuncs.modelSuccessCallback">
                        <i class="fa fa-user-times mr-2 text-warning"></i>{{  __tr('Remove from Group') }}
                    </a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.contact.write.delete', [ 'contactIdOrUid']) }}", {'contactIdOrUid': __tData._uid}) %>" data-confirm="#lwDeleteContact-template" title="{{ __tr('Delete') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwContactList']) }}" data-callback="appFuncs.modelSuccessCallback">
                        <i class="fa fa-trash mr-2"></i>{{  __tr('Delete Contact') }}
                    </a>
                </div>
            </div>
        </script>
        <!-- /action template -->
        <!-- Contact delete template -->
        <script type="text/template" id="lwDeleteContact-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this Contact permanently?') }}</p>
    </script>
        <!-- /Contact delete template -->
         <!-- Contact remove template -->
         <script type="text/template" id="lwRemoveContact-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to remove this Contact from this group?') }}</p>
    </script>
        <!-- /Contact remove template -->
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function initContactListControls() {
            var table = $('#lwContactList').DataTable();
            if (!table) { setTimeout(initContactListControls, 150); return; }

            // Set default select to current page length
            $('#tl-entries-per-page').val(table.page.len());

            // Change page length
            $('#tl-entries-per-page').off('change.contacts').on('change.contacts', function() {
                var len = parseInt(this.value, 10) || 10;
                table.page.len(len).draw();
            });

            // Search
            $('#tl-table-search').off('keyup.contacts').on('keyup.contacts', function() {
                table.search(this.value).draw();
            });

            // Keep header controls responsive to redraws
            $('#lwContactList').on('draw.dt', function(){
                $('#tl-entries-per-page').val(table.page.len());
            });

            // Wire per-row "Select" in the actions dropdown to toggle the row checkbox
            $('#lwContactList tbody').off('click.rowToggleSelect').on('click.rowToggleSelect', 'a.row-toggle-select', function(e){
                e.preventDefault();
                var uid = $(this).data('uid');
                // find matching checkbox by value
                var $checkbox = $(this).closest('tr').find('input.lw-checkboxes[value="'+uid+'"]');
                if ($checkbox.length) { $checkbox.trigger('click'); }
                // update label text based on selection state after toggle
                var isChecked = $checkbox.is(':checked');
                var $label = $(this).find('.row-toggle-select-label');
                if ($label.length) { $label.text(isChecked ? '{{ __tr('Unselect') }}' : '{{ __tr('Select') }}'); }
            });

            // On table draw, refresh the Select/Unselect label according to checkbox state
            $('#lwContactList').on('draw.dt', function(){
                $('#lwContactList tbody tr').each(function(){
                    var $tr = $(this);
                    var $cb = $tr.find('input.lw-checkboxes');
                    var uid = $cb.val();
                    var isChecked = $cb.is(':checked');
                    $tr.find('a.row-toggle-select .row-toggle-select-label').text(isChecked ? '{{ __tr('Unselect') }}' : '{{ __tr('Select') }}');
                    $tr.find('a.row-toggle-select').attr('data-uid', uid);
                });
            });
        }

        // Initialize after a small delay to allow DataTable setup
        setTimeout(initContactListControls, 500);
    });
</script>
<style>
    /* Header Controls */
    .modern-table-container { background: #ffffff; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 4px 18px rgba(2,6,23,0.06); overflow: hidden; }
    .table-header-controls { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; background: linear-gradient(135deg,#f9fafb,#f3f4f6); border-bottom: 1px solid #e9ecef; }
    .entries-control { display: flex; align-items: center; gap: .5rem; color: #6b7280; }
    .entries-select { padding: .35rem .6rem; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #111827; min-width: 64px; }
    .entries-text { font-size: .9rem; color: #6b7280; }
    .search-control { position: relative; }
    .search-input { padding: .5rem .9rem .5rem 2.2rem; border: 1px solid #cbd5e1; border-radius: 10px; min-width: 220px; }
    .search-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,.12); border-color: #93c5fd; }
    .search-icon { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }

    /* Hide default DataTables length & search since we use custom controls */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { display: none !important; }

    /* Modern DataTable Styles (scoped to this page) */
    .modern-datatable {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .modern-datatable thead th {
        background: #f8f9fa !important;
        border: none !important;
        padding: 1rem 1.25rem !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        color: #495057 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
    }

    .modern-datatable tbody td {
        padding: 0.9rem 1.25rem !important;
        vertical-align: middle !important;
        font-size: 0.92rem !important;
        color: #334155 !important;
        border-top: 1px solid #eef2f7 !important;
    }

    .modern-datatable tbody tr:hover {
        background: #fbfbfd !important;
    }

    /* Align actions nicely */
    .modern-datatable thead th.text-right,
    .modern-datatable tbody td:last-child {
        text-align: right !important;
        white-space: nowrap;
    }

    /* Card polish for container */
    .card.shadow { box-shadow: 0 8px 24px rgba(2,6,23,0.06) !important; border-radius: 12px; }
    .card.shadow .card-body { padding: 0; }
    .card.shadow table { margin-bottom: 0; }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .modern-datatable thead th,
        .modern-datatable tbody td { padding: 0.7rem 0.9rem !important; }
    }

    /* Actions dropdown (three-dot) */
    .action-kebab-btn { border: 1px solid #e5e7eb; }
    .action-kebab-btn:hover { background: #f3f4f6; }
    .action-dropdown .dropdown-menu { min-width: 220px; border-radius: 10px; }

    /* Gradient buttons (reuse from templates list) */
    .btn-neo {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.65rem 1.1rem;
        transition: transform 200ms ease, box-shadow 200ms ease, background-position 350ms ease, color 180ms ease;
        line-height: 1.25rem;
    }
    .btn-neo i { margin-right: .4rem; }
    .btn-neo-gradient-green {
        color: #ffffff !important;
        border: 0;
        background-image: linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%);
        background-size: 200% 200%;
        box-shadow: 0 2px 8px rgba(11, 119, 83, 0.18);
    }
    .btn-neo-gradient-green:hover,
    .btn-neo-gradient-green:focus { background-position: right center; transform: translateY(-1px); box-shadow: 0 10px 24px rgba(11, 119, 83, 0.28); }
    .btn-neo-gradient-green:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(11, 119, 83, 0.22); }
</style>
@push('appScripts')
<script>
(function($) {
    'use strict';
    window.onUpdateContactDetails = function(responseData, callbackParams) {
        appFuncs.modelSuccessCallback(responseData, callbackParams);
    }
})(jQuery);
</script>
@endpush
@endsection()