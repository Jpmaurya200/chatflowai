@php
/**
* Component : Contact
* Controller : ContactCustomFieldController
* File : Custom Field.list.blade.php
* ----------------------------------------------------------------------------- */
@endphp
@extends('layouts.app', ['title' => __tr('Contact Custom Fields')])
@section('content')
@include('users.partials.header', [
'title' => __tr(''),
'description' => '',
'class' => 'col-lg-7'
])
<div class="container-fluid mt-lg--6">
    <div class="row mt-3">
        <!-- header -->
        <div class="col-xl-12 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-nowrap mt-5">
                <h1 class="mb-0"><i class="fas fa-sliders-h me-2" style="color: #0B7753;"></i> {{ __tr('Input Fields') }}</h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="lw-btn btn btn-neo btn-neo-gradient-green" data-toggle="modal" data-target="#lwAddNewCustomField">
                        <i class="fas fa-plus"></i> {{ __tr('Add New Input Field') }}
                    </button>
                </div>
            </div>
        </div>
        <!--/ header -->
        <!-- Add New Custom Field Modal -->
        <x-lw.modal id="lwAddNewCustomField" :header="__tr('Add New Input Field')" :hasForm="true">
            <!--  Add New Custom Field Form -->
            <x-lw.form id="lwAddNewCustomFieldForm"
                :action="route('vendor.contact.custom_field.write.create')"
                :data-callback-params="['modalId' => '#lwAddNewCustomField', 'datatableId' => '#lwCustomFieldList']"
                data-callback="appFuncs.modelSuccessCallback">
                <!-- form body -->
                <div class="lw-form-modal-body">
                    <!-- form fields form fields -->
                    <!-- Input_Name -->
                    <x-lw.input-field type="text" id="lwInputNameField" data-form-group-class=""
                        :label="__tr('Input Name')" name="input_name" required="true" />
                    <!-- /Input_Name -->
                    <!-- Input_Type -->
                    <x-lw.input-field type="selectize" id="lwInputTypeField" data-form-group-class="" data-selected=" "
                        :label="__tr('Input Type')" name="input_type" required="true">
                        <x-slot name="selectOptions">
                            @foreach (configItem('contact_custom_input_types') as $inputTypeKey => $inputTypeValue)
                            <option value="{{ $inputTypeKey }}">{{ $inputTypeValue }}</option>
                            @endforeach
                        </x-slot>
                    </x-lw.input-field>
                    <!-- /Input_Type -->
                </div>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
            <!--/  Add New Custom Field Form -->
        </x-lw.modal>
        <!--/ Add New Custom Field Modal -->

        <!-- Edit Custom Field Modal -->
        <x-lw.modal id="lwEditCustomField" :header="__tr('Edit Input Field')" :hasForm="true">
            <!--  Edit Custom Field Form -->
            <x-lw.form id="lwEditCustomFieldForm"
                :action="route('vendor.contact.custom_field.write.update')"
                :data-callback-params="['modalId' => '#lwEditCustomField', 'datatableId' => '#lwCustomFieldList']"
                data-callback="appFuncs.modelSuccessCallback">
                <!-- form body -->
                <div id="lwEditCustomFieldBody" class="lw-form-modal-body"></div>
                <script type="text/template" id="lwEditCustomFieldBody-template">

                    <input type="hidden" name="contactCustomFieldIdOrUid" value="<%- __tData._uid %>" />
                        <!-- form fields -->
                        <!-- Input_Name -->
           <x-lw.input-field type="text" id="lwInputNameEditField" data-form-group-class="" :label="__tr('Input Name')" value="<%- __tData.input_name %>" name="input_name"  required="true"                 />
                <!-- /Input_Name -->
                <!-- Input_Type -->
                 <x-lw.input-field type="selectize" id="lwInputTypeEditField" data-form-group-class="" data-selected="<%- __tData.input_type %>" :label="__tr('Input Type')" name="input_type"  required="true"                >
                    <x-slot name="selectOptions">
                    <option value="">{{ __tr('Input Type') }}</option>
                    @foreach (configItem('contact_custom_input_types') as $inputTypeKey => $inputTypeValue)
                        <option value="{{ $inputTypeKey }}">{{ $inputTypeValue }}</option>
                    @endforeach
                </x-slot>
            </x-lw.input-field>
                <!-- /Input_Type -->
                     </script>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
            <!--/  Edit Custom Field Form -->
        </x-lw.modal>
        <!--/ Edit Custom Field Modal -->
        <div class="col-xl-12">
            <div class="modern-table-container">
                <!-- Header Controls: Show entries (left) and Search (right) -->
                <div class="table-header-controls">
                    <div class="entries-control">
                        <label for="cfl-entries-per-page" class="mb-0">{{ __tr('Show') }}</label>
                        <select id="cfl-entries-per-page" class="entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="entries-text">{{ __tr('entries') }}</span>
                    </div>
                    <div class="search-control">
                        <input type="text" id="cfl-table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <x-lw.datatable
                        id="lwCustomFieldList"
                        class="modern-datatable table-hover align-middle"
                        lw-card-classes="border-0 rounded"
                        data-page-length="100"
                        :url="route('vendor.contact.custom_field.read.list')">
                        <th data-orderable="true" data-name="input_name">{{ __tr('Input Name') }}</th>
                        <th data-orderable="true" data-name="input_type">{{ __tr('Input Type') }}</th>
                        <th data-template="#customFieldActionColumnTemplate" name="null" class="text-right">{{ __tr('Action') }}</th>
                    </x-lw.datatable>
                </div>
            </div>
        </div>

        <!-- action template -->
        <script type="text/template" id="customFieldActionColumnTemplate">
            <div class="dropdown d-inline-block action-dropdown">
                <button class="btn btn-sm btn-light action-kebab-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __tr('Actions') }}">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <a class="dropdown-item lw-ajax-link-action" data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Edit') }}" data-response-template="#lwEditCustomFieldBody" href="<%= __Utils.apiURL("{{ route('vendor.contact.custom_field.read.update.data', [ 'contactCustomFieldIdOrUid']) }}", {'contactCustomFieldIdOrUid': __tData._uid}) %>" data-toggle="modal" data-target="#lwEditCustomField">
                        <i class="fa fa-edit mr-2"></i>{{  __tr('Edit') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.contact.custom_field.write.delete', [ 'contactCustomFieldIdOrUid']) }}", {'contactCustomFieldIdOrUid': __tData._uid}) %>" data-confirm="#lwDeleteCustomField-template" title="{{ __tr('Delete') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwCustomFieldList']) }}" data-callback="appFuncs.modelSuccessCallback">
                        <i class="fa fa-trash mr-2"></i>{{  __tr('Delete') }}
                    </a>
                </div>
            </div>
        </script>
        <!-- /action template -->

        <!-- Custom Field delete template -->
        <script type="text/template" id="lwDeleteCustomField-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this Custom Field?') }}</p>
    </script>
        <!-- /CustomField delete template -->
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function initCustomFieldListControls() {
            var table = $('#lwCustomFieldList').DataTable();
            if (!table) { setTimeout(initCustomFieldListControls, 150); return; }

            // Set default select to current page length
            $('#cfl-entries-per-page').val(table.page.len());

            // Change page length
            $('#cfl-entries-per-page').off('change.cfields').on('change.cfields', function() {
                var len = parseInt(this.value, 10) || 10;
                table.page.len(len).draw();
            });

            // Search
            $('#cfl-table-search').off('keyup.cfields').on('keyup.cfields', function() {
                table.search(this.value).draw();
            });

            // Keep header controls responsive to redraws
            $('#lwCustomFieldList').on('draw.dt', function(){
                $('#cfl-entries-per-page').val(table.page.len());
            });
        }

        // Initialize after a small delay to allow DataTable setup
        setTimeout(initCustomFieldListControls, 500);
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
    .modern-datatable { width: 100% !important; border-collapse: collapse !important; }
    .modern-datatable thead th { background: #f8f9fa !important; border: none !important; padding: 1rem 1.25rem !important; font-weight: 700 !important; font-size: 0.85rem !important; color: #495057 !important; text-transform: uppercase !important; letter-spacing: 0.4px !important; }
    .modern-datatable tbody td { padding: 0.9rem 1.25rem !important; vertical-align: middle !important; font-size: 0.92rem !important; color: #334155 !important; border-top: 1px solid #eef2f7 !important; }
    .modern-datatable tbody tr:hover { background: #fbfbfd !important; }
    .modern-datatable thead th.text-right,
    .modern-datatable tbody td:last-child { text-align: right !important; white-space: nowrap; }
    .modern-datatable thead th.text-center { text-align: center !important; }

    /* Modern gradient buttons */
    .btn-neo { border-radius: 12px; font-weight: 600; padding: 0.65rem 1.1rem; transition: transform 200ms ease, box-shadow 200ms ease, background-position 350ms ease, color 180ms ease; line-height: 1.25rem; }
    .btn-neo i { margin-right: .4rem; }
    .btn-neo-gradient-green { color: #ffffff !important; border: 0; background-image: linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%); background-size: 200% 200%; box-shadow: 0 2px 8px rgba(11, 119, 83, 0.18); }
    .btn-neo-gradient-green:hover, .btn-neo-gradient-green:focus { background-position: right center; transform: translateY(-1px); box-shadow: 0 10px 24px rgba(11, 119, 83, 0.28); }
    .btn-neo-gradient-green:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(11, 119, 83, 0.22); }

    /* Actions dropdown (three-dot) */
    .action-kebab-btn { border: 1px solid #e5e7eb; }
    .action-kebab-btn:hover { background: #f3f4f6; }
    .action-dropdown .dropdown-menu { min-width: 220px; border-radius: 10px; }
</style>
@endsection()