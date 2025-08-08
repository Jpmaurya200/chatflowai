@php
/**
* Component : WhatsAppService
* Controller : WhatsAppServiceController
* File : templates.list.blade.php
----------------------------------------------------------------------------- */
@endphp
@extends('layouts.app', ['title' => __tr('Templates List')])
@section('content')
@include('users.partials.header', [
'title' => __tr(''),
'description' => '',
'class' => 'col-lg-7'
])
<div class="container-fluid mt-lg--6">
    <div class="row mt-3">
        <!-- button -->
        <div class="col-xl-12 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-nowrap mt-5">
                <h1 class="mb-0"><i class="fas fa-layer-group me-2" style="color: #0B7753;"></i> WhatsApp Templates</h1>
                <div class="d-flex align-items-center">
                    <a class="lw-btn btn btn-neo btn-neo-gradient-green" href="{{ route('vendor.whatsapp_service.templates.read.new_view') }}"><i class="fas fa-plus"></i> {{ __tr('Create New Template') }}
                    </a>
                    <a class="lw-btn btn btn-neo btn-neo-gradient-green ml-2" target="_blank" href="https://business.facebook.com/wa/manage/message-templates/?waba_id={{ getVendorSettings('whatsapp_business_account_id') }}"><i class="fas fa-external-link-alt"></i> {{ __tr('Manage Templates on Meta') }}</a>
                </div>
            </div>
        </div>
        <!--/ button -->
        <x-lw.modal id="lwTemplatePreview" :header="__tr('Template Preview')" :modalSize="'modal-sm'" :hasForm="true" x-data="{selectedTemplate:''}">
            <!--  Edit Contact Form -->
            <div id="lwTemplatePreviewForm">
                <!-- form body -->
                <div id="lwTemplateStructureContainer" class="lw-form-modal-body"></div>
                <!-- form footer -->
                <div class="modal-footer" id="lwTemplateStructureContainerActions"></div>
                <script type="text/template" id="lwTemplateStructureContainerActions-template">
                    <% if(__tData.template_status == 'APPROVED') { %>
                    <a title="{{  __tr('Create Campaign using this template') }}" class="lw-btn btn btn-primary"  href="{{ route('vendor.campaign.new.view') }}?use_template=<%- __tData._uid %>"><i class="fa fa-bullhorn"></i> {{  __tr('Create Campaign') }}</a>
                    <% } %>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </script>
            </div>
            <!--/  Edit Contact Form -->
        </x-lw.modal>
        <div class="col-xl-12">
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
                        id="lwTemplatesList"
                        class="modern-datatable table-hover align-middle"
                        lw-card-classes="border-0 rounded"
                        data-page-length="100"
                        :url="route('vendor.whatsapp_service.templates.read.list')">
                        <th data-orderable="true" data-name="template_name">{{ __tr('Name') }}</th>
                        <th data-orderable="true" data-name="language">{{ __tr('Language') }}</th>
                        <th data-orderable="true" data-name="category">{{ __tr('Category') }}</th>
                        <th data-template="#templatesStatusColumnTemplate" name="null" class="text-center">{{ __tr('Status') }}</th>
                        <th data-orderable="true" data-order-by="true" data-order-type="desc" data-name="updated_at">{{ __tr('Updated On') }}</th>
                        <th data-template="#templatesActionColumnTemplate" name="null" class="text-right">{{ __tr('Action') }}</th>
                    </x-lw.datatable>
                </div>
            </div>
        </div>
        <script type="text/template" id="templatesStatusColumnTemplate">
            <% if(__tData.status == 'APPROVED') { %>
                <span class="badge badge-pill badge-success"><i class="fa fa-check-circle mr-1"></i> {{  __tr('Approved') }}</span>
            <% } else if(__tData.status == 'REJECTED') { %>
                <span class="badge badge-pill badge-danger"><i class="fa fa-times-circle mr-1"></i> {{  __tr('Rejected') }}</span>
            <% } else if(__tData.status == 'PENDING') { %>
                <span class="badge badge-pill badge-warning"><i class="fa fa-clock mr-1"></i> {{  __tr('Pending') }}</span>
            <% } else { %>
                <span class="badge badge-pill badge-secondary"><%- __tData.status  %></span>
            <% } %>
        </script>
        <!-- action template -->
        <script type="text/template" id="templatesActionColumnTemplate">
            <div class="dropdown d-inline-block action-dropdown">
                <button class="btn btn-sm btn-light action-kebab-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __tr('Actions') }}">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <% if(__tData.status == 'APPROVED') { %>
                        <a class="dropdown-item" href="{{ route('vendor.campaign.new.view') }}?use_template=<%- __tData._uid %>">
                            <i class="fa fa-bullhorn mr-2 text-primary"></i>{{  __tr('Create Campaign') }}
                        </a>
                    <% } %>
                    <a class="dropdown-item lw-ajax-link-action" data-pre-callback="appFuncs.clearContainer" data-method="post" data-post-data="<%- toJsonString({'template_selection': __tData._uid,'template_status': __tData.status}) %>" data-response-template="#lwTemplateStructureContainerActions" href="{{ route('vendor.request.template.view') }}?only-preview=1" data-toggle="modal" data-target="#lwTemplatePreview">
                        <i class="fa fa-eye mr-2"></i>{{  __tr('Preview') }}
                    </a>
                    <a class="dropdown-item" href="<%= __Utils.apiURL("{{ route('vendor.whatsapp_service.templates.read.update_view',['whatsappTemplateUid']) }}", {'whatsappTemplateUid': __tData._uid}) %>">
                        <i class="fa fa-edit mr-2"></i>{{  __tr('Edit Template') }}
                    </a>
                    <a class="dropdown-item" target="_blank" href="https://business.facebook.com/wa/manage/message-templates/?&waba_id={{ getVendorSettings('whatsapp_business_account_id') }}&id=<%- __tData.template_id %>">
                        <i class="fas fa-external-link-alt mr-2"></i>{{  __tr('Edit on Meta') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger lw-ajax-link-action" data-callback="reloadDtOnSuccess" data-method="post" data-confirm="#lwConfirmTemplateDelete" data-confirm-params="<%- toJsonString({'templateName': __tData.template_name}) %>" href="<%= __Utils.apiURL(" {{ route('vendor.whatsapp_service.templates.write.delete',['whatsappTemplateUid']) }}", {'whatsappTemplateUid': __tData._uid}) %>">
                        <i class="fa fa-trash mr-2"></i>{{  __tr('Delete Template') }}
                    </a>
                </div>
            </div>
        </script>
        <!-- /action template -->
        <script type="text/template" id="lwConfirmTemplateDelete">
            <h3>{!! __tr('Are you sure you want to delete __templateName__ template', [
                '__templateName__' => '<strong><%- __tData.templateName %></strong>'
                ]) !!}</h3>
        </script>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to sync WhatsApp templates automatically
        function syncWhatsAppTemplates() {
            fetch("{{ route('vendor.whatsapp_service.templates.write.sync') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Handle success or update UI accordingly
                console.log('Templates synced successfully:', data);
            })
            .catch(error => {
                console.error('Error syncing templates:', error);
            });
        }

        // Call the sync function on page load
        syncWhatsAppTemplates();

        // Wire up custom Show entries & Search controls to DataTable
        function initTemplateListControls() {
            var table = $('#lwTemplatesList').DataTable();
            if (!table) { setTimeout(initTemplateListControls, 150); return; }

            // Set default select to current page length
            $('#tl-entries-per-page').val(table.page.len());

            // Change page length
            $('#tl-entries-per-page').off('change.templates').on('change.templates', function() {
                var len = parseInt(this.value, 10) || 10;
                table.page.len(len).draw();
            });

            // Search
            $('#tl-table-search').off('keyup.templates').on('keyup.templates', function() {
                table.search(this.value).draw();
            });

            // Keep header controls responsive to redraws
            $('#lwTemplatesList').on('draw.dt', function(){
                $('#tl-entries-per-page').val(table.page.len());
            });
        }

        // Initialize after a small delay to allow DataTable setup
        setTimeout(initTemplateListControls, 500);
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

    /* Align actions and status nicely */
    .modern-datatable thead th.text-right,
    .modern-datatable tbody td:last-child {
        text-align: right !important;
        white-space: nowrap;
    }

    .modern-datatable thead th.text-center {
        text-align: center !important;
    }
    .modern-datatable tbody td:nth-child(4) { /* status col */
        text-align: center !important;
    }

    /* Badge polish */
    .badge { font-weight: 600; letter-spacing: 0.2px; }
    .badge-success { background-color: #22a06b; }
    .badge-danger { background-color: #e35d6a; }
    .badge-warning { background-color: #f5a524; color: #1f2937; }

    /* Card polish for container */
    .card.shadow { box-shadow: 0 8px 24px rgba(2,6,23,0.06) !important; border-radius: 12px; }
    .card.shadow .card-body { padding: 0; }
    .card.shadow table { margin-bottom: 0; }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .modern-datatable thead th,
        .modern-datatable tbody td { padding: 0.7rem 0.9rem !important; }
    }

    /* Modern gradient buttons */
    .btn-neo {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.65rem 1.1rem;
        transition: transform 200ms ease, box-shadow 200ms ease, background-position 350ms ease, color 180ms ease;
        line-height: 1.25rem;
    }

    .btn-neo i { margin-right: .4rem; }

    /* Solid green gradient */
    .btn-neo-gradient-green {
        color: #ffffff !important;
        border: 0;
        background-image: linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%);
        background-size: 200% 200%;
        box-shadow: 0 2px 8px rgba(11, 119, 83, 0.18);
    }

    .btn-neo-gradient-green:hover,
    .btn-neo-gradient-green:focus {
        background-position: right center;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(11, 119, 83, 0.28);
    }

    .btn-neo-gradient-green:active {
        transform: translateY(0);
        box-shadow: 0 6px 14px rgba(11, 119, 83, 0.22);
    }

    /* Gradient border that fills on hover */
    .btn-neo-gradient-ghost {
        color: #0B7753 !important;
        border: 2px solid transparent;
        background: linear-gradient(#ffffff, #ffffff) padding-box,
                    linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%) border-box;
        box-shadow: 0 2px 8px rgba(11, 119, 83, 0.10);
    }

    .btn-neo-gradient-ghost:hover,
    .btn-neo-gradient-ghost:focus {
        color: #ffffff !important;
        background: linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%) padding-box,
                    linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%) border-box;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(11, 119, 83, 0.22);
    }

    .btn-neo-gradient-ghost:active {
        transform: translateY(0);
        box-shadow: 0 6px 14px rgba(11, 119, 83, 0.18);
    }
    /* Actions dropdown (three-dot) */
    .action-kebab-btn { border: 1px solid #e5e7eb; }
    .action-kebab-btn:hover { background: #f3f4f6; }
    .action-dropdown .dropdown-menu { min-width: 220px; border-radius: 10px; }
</style>
@endsection()