@php
/**
* Component : Campaign
* Controller : CampaignController
* File : campaign.list.blade.php
* -----------------------------------------------------------------------------
*/
@endphp
@extends('layouts.app', ['title' => __tr('Campaigns')])
@section('content')
@include('users.partials.header', [
'title' => __tr(''),
'description' => '',
'class' => 'col-lg-7'
])

<?php $status = request()->status ?? 'active'; ?>
<div class="container-fluid mt-lg--6">
    <div class="row mt-5">
        <!-- modern header actions (AiSensy / WATI standard) -->
        <div class="col-xl-12 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap p-4 bg-white rounded-lg shadow-sm border" style="gap: 16px;">
                <div>
                    <h2 class="font-weight-bold mb-1 text-dark d-flex align-items-center" style="gap: 10px;">
                        <i class="fas fa-bullhorn text-success"></i> {{ __tr('WhatsApp Broadcast & Campaigns') }}
                    </h2>
                    <p class="text-muted mb-0 small">{{ __tr('Launch targeted template broadcasts, analyze delivery rates, and scale customer engagement.') }}</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a class="btn btn-outline-secondary" href="{{ route('vendor.whatsapp_service.templates.read.list_view') }}">
                        <i class="fas fa-layer-group mr-1"></i> {{ __tr('Templates') }}
                    </a>
                    <a class="btn btn-primary shadow-sm" href="{{ route('vendor.campaign.new.view') }}">
                        <i class="fas fa-plus mr-1"></i> {{ __tr('Create Campaign') }}
                    </a>
                </div>
            </div>
        </div>
        <!--/ modern header actions -->

        <div class="col-12 mb-3">
            <ul class="nav nav-pills" style="gap: 8px;">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 font-weight-bold <?= $status == 'active' ? 'active bg-success text-white' : 'bg-white text-dark border' ?>" href="<?= route('vendor.campaign.read.list_view', ['status' => 'active']) ?>">
                        <i class="fas fa-chart-line mr-1"></i> <?= __tr('Active Campaigns') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 font-weight-bold <?= $status == 'archived' ? 'active bg-success text-white' : 'bg-white text-dark border' ?>" href="<?= route('vendor.campaign.read.list_view', ['status' => 'archived']) ?>">
                        <i class="fas fa-archive mr-1"></i> <?= __tr('Archived') ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-xl-12">
            <div class="modern-table-container">
                <!-- Header Controls: Show entries & Search -->
                <div class="table-header-controls">
                    <div class="entries-control">
                        <label for="cl-entries-per-page" class="mb-0">{{ __tr('Show') }}</label>
                        <select id="cl-entries-per-page" class="entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="entries-text">{{ __tr('entries') }}</span>
                    </div>
                    <div class="search-control">
                        <input type="text" id="cl-table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <x-lw.datatable data-page-length="100" id="lwCampaignList" class="modern-datatable table-hover align-middle" lw-card-classes="border-0 rounded" :url="route('vendor.campaign.read.list', ['status' => $status])">
                        <th data-orderable="true" data-name="title">{{ __tr('Title') }}</th>
                        <th data-orderable="true" data-name="template_name">{{ __tr('Template') }}</th>
                        <th data-name="contacts_count">{{ __tr('No. of Contacts') }}</th>
                        <th data-orderable="true" data-name="created_at">{{ __tr('Created At') }}</th>
                        <th data-orderable="true" data-order-type="desc" data-order-by="true" data-name="scheduled_at">{{ __tr('Schedule At') }}</th>
                        <th data-template="#campaignStatusColumnTemplate" name="null" class="text-center">{!! __tr('Status') !!}</th>
                        <th data-template="#campaignActionColumnTemplate" name="null" class="text-right">{!! __tr('Action') !!}</th>
                    </x-lw.datatable>
                </div>
            </div>
        </div>
        <!-- action template -->
        <script type="text/template" id="campaignActionColumnTemplate">
            <div class="dropdown d-inline-block action-dropdown">
                <button class="btn btn-sm btn-light action-kebab-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __tr('Actions') }}">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <a class="dropdown-item" href="<%= __Utils.apiURL("{{ route('vendor.campaign.status.view', ['campaignUid' => 'campaignUid',]) }}", {'campaignUid': __tData._uid}) %>">
                        <i class="fa fa-tachometer-alt mr-2 "></i>{{  __tr('Campaign Dashboard') }}
                    </a>
                    <a class="dropdown-item lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.campaign.write.duplicate', [ 'campaignIdOrUid']) }}", {'campaignIdOrUid': __tData._uid}) %>" data-confirm="#lwDuplicateCampaign-template" title="{{ __tr('Duplicate Campaign') }}" data-callback="appFuncs.modelSuccessCallback">
                        <i class="fa fa-clone mr-2 "></i>{{ __tr('Duplicate Campaign') }}
                    </a>
                    <% if(__tData.delete_allowed) { %>
                        <a class="dropdown-item text-danger lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.campaign.write.delete', [ 'campaignIdOrUid']) }}", {'campaignIdOrUid': __tData._uid}) %>" data-confirm="#lwDeleteCampaign-template" title="{{ __tr('Delete') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwCampaignList']) }}" data-callback="appFuncs.modelSuccessCallback">
                            <i class="fa fa-trash mr-2"></i>{{  __tr('Delete') }}
                        </a>
                    <% } else { %>
                        <% if(__tData.status != 5) { %>
                            <a class="dropdown-item lw-ajax-link-action" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.campaign.write.archive', [ 'campaignIdOrUid']) }}", {'campaignIdOrUid': __tData._uid}) %>" title="{{ __tr('Archive') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwCampaignList']) }}" data-callback="appFuncs.modelSuccessCallback">
                                <i class="fa fa-archive mr-2 "></i>{{  __tr('Archive') }}
                            </a>
                        <% } else { %>
                            <a class="dropdown-item text-warning lw-ajax-link-action" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.campaign.write.unarchive', [ 'campaignIdOrUid']) }}", {'campaignIdOrUid': __tData._uid}) %>" title="{{ __tr('Unarchive') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwCampaignList']) }}" data-callback="appFuncs.modelSuccessCallback">
                                <i class="fa fa-undo mr-2"></i>{{  __tr('Unarchive') }}
                            </a>
                        <% } %>
                    <% } %>
                </div>
            </div>
        </script>
        <!-- /action template -->
        <!-- action template -->
        <script type="text/template" id="campaignStatusColumnTemplate">
            <% if(__tData.delete_allowed) { %>
                <span class="badge badge-pill badge-success"><i class="fa fa-check-circle mr-1"></i><%- __tData.scheduled_status %></span>
            <% } else { %>
                <span class="badge badge-pill badge-warning"><i class="fa fa-clock mr-1"></i><%- __tData.scheduled_status %></span>
            <% } %>
        </script>
        <!-- /status template -->

        <!-- Campaign delete template -->
        <script type="text/template" id="lwDeleteCampaign-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this Campaign?') }}</p>
    </script>
        <!-- /Campaign delete template -->
        <!-- Campaign duplicate template -->
        <script type="text/template" id="lwDuplicateCampaign-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to duplicate this Campaign?') }}</p>
    </script>
        <!-- /Campaign duplicate template -->
    </div>
</div>

<script>
// Bind header controls to DataTable (similar to templates page)
document.addEventListener('DOMContentLoaded', function(){
    function initCampaignListControls(){
        var table = $('#lwCampaignList').DataTable();
        if(!table){ setTimeout(initCampaignListControls, 150); return; }

        // sync default value
        $('#cl-entries-per-page').val(table.page.len());

        // Page length
        $('#cl-entries-per-page').off('change.campaign').on('change.campaign', function(){
            var len = parseInt(this.value, 10) || 10;
            table.page.len(len).draw();
        });

        // Search
        $('#cl-table-search').off('keyup.campaign').on('keyup.campaign', function(){
            table.search(this.value).draw();
        });

        // Keep select in sync on redraw
        $('#lwCampaignList').on('draw.dt', function(){
            $('#cl-entries-per-page').val(table.page.len());
        });
    }
    setTimeout(initCampaignListControls, 500);
});
</script>

<style>
/* Match templates-list modern UI */
.modern-table-container { background:#ffffff; border-radius:12px; border:1px solid #e9ecef; box-shadow:0 4px 18px rgba(2,6,23,0.06); overflow:hidden; }
.table-header-controls { display:flex; justify-content:space-between; align-items:center; padding:1rem 1.25rem; background:linear-gradient(135deg,#f9fafb,#f3f4f6); border-bottom:1px solid #e9ecef; }
.entries-control { display:flex; align-items:center; gap:.5rem; color:#6b7280; }
.entries-select { padding:.35rem .6rem; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:#111827; min-width:64px; }
.entries-text { font-size:.9rem; color:#6b7280; }
.search-control { position:relative; }
.search-input { padding:.5rem .9rem .5rem 2.2rem; border:1px solid #cbd5e1; border-radius:10px; min-width:220px; }
.search-input:focus { outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.12); border-color:#93c5fd; }
.search-icon { position:absolute; left:.65rem; top:50%; transform:translateY(-50%); color:#9ca3af; }

.modern-datatable thead th { background:#f8f9fa !important; border:none !important; padding:1rem 1.25rem !important; font-weight:700 !important; font-size:.85rem !important; color:#495057 !important; text-transform:uppercase !important; letter-spacing:.4px !important; }
.modern-datatable tbody td { padding:.9rem 1.25rem !important; vertical-align:middle !important; font-size:.92rem !important; color:#334155 !important; border-top:1px solid #eef2f7 !important; }
.modern-datatable tbody tr:hover { background:#fbfbfd !important; }

/* Hide default DataTables native controls */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter { display:none !important; }

/* Actions dropdown */
.action-kebab-btn { border:1px solid #e5e7eb; }
.action-kebab-btn:hover { background:#f3f4f6; }
.action-dropdown .dropdown-menu { min-width:240px; border-radius:10px; }

@media (max-width: 768px){
  .search-input { min-width:140px; }
}

/* Modern gradient button (matches templates-list) */
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
.btn-neo-gradient-green:focus {
    background-position: right center;
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(11, 119, 83, 0.28);
}
.btn-neo-gradient-green:active {
    transform: translateY(0);
    box-shadow: 0 6px 14px rgba(11, 119, 83, 0.22);
}
</style>

@endsection()