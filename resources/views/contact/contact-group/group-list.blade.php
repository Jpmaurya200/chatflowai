@php
/**
* Component : Contact
* Controller : ContactGroupController
* File : group.list.blade.php
----------------------------------------------------------------------------- */
@endphp
@extends('layouts.app', ['title' => __tr('Contact Groups')])
@section('content')
@include('users.partials.header', [
'title' => __tr(''),
'description' => '',
'class' => 'col-lg-7'
])
<?php $status = request()->status ?? 'active'; ?>

<div class="container-fluid mt-lg--6">
    <div class="row mt-3">
        <!-- header -->
        <div class="col-xl-12 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-nowrap mt-5">
                <h1 class="mb-0"><i class="fas fa-layer-group me-2" style="color: #0B7753;"></i> {{ __tr('Contact Groups') }}</h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="lw-btn btn btn-neo btn-neo-gradient-green" data-toggle="modal" data-target="#lwAddNewGroup">
                        <i class="fas fa-plus"></i> {{ __tr('Add New Group') }}
                    </button>
                </div>
            </div>
        </div>
        <!--/ header -->
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
        },deleteSelectedContactGroups() {
            var that = this;
            showConfirmation('{{ __tr('Are you sure you want to delete all selected groups?') }}', function() {
                __DataRequest.post('{{ route('vendor.contact.group.selected.write.delete') }}', {
                    'selected_groups' : that.selectedContacts
                });
            }, {
                confirmButtonText: '{{ __tr('Yes') }}',
                cancelButtonText: '{{ __tr('No') }}',
                type: 'error'
            });
        },
        archiveSelectedContactGroups() {
            var that = this;
            showConfirmation('{{ __tr('Are you sure you want to archive all selected groups?') }}', function() {
                __DataRequest.post('{{ route('vendor.contact.group.selected.write.archive') }}', {
                    'selected_groups' : that.selectedContacts
                });
            }, {
                confirmButtonText: '{{ __tr('Yes') }}',
                cancelButtonText: '{{ __tr('No') }}',
                type: 'warning'
            });
        },
        unarchiveSelectedContactGroups() {
            var that = this;
            showConfirmation('{{ __tr('Are you sure you want to unarchive all selected groups?') }}', function() {
                __DataRequest.post('{{ route('vendor.contact.group.selected.write.unarchive') }}', {
                    'selected_groups' : that.selectedContacts
                });
            }, {
                confirmButtonText: '{{ __tr('Yes') }}',
                cancelButtonText: '{{ __tr('No') }}',
                type: 'warning'
            });
        },
            }
        " x-init="$('#lwGroupList').on( 'draw.dt', function () {
            $('.dataTables_wrapper table>tbody input[type=checkbox].lw-checkboxes:checked').trigger('click');
            isSelectedAll = false;
        } );">
            <ul class="nav nav-tabs mt-1 ml-1">
                <!-- Active tab -->
                <li class="nav-item">
                    <a class="nav-link <?= $status == 'active' ? 'active' : '' ?>" data-title="{{ __tr('Active ') }}"
                        href="<?= route('vendor.contact.group.read.list_view', ['status' => 'active']) ?>">
                        <?= __tr('Active') ?>
                    </a>
                </li>
                <!-- /Active tab -->

                <!-- Archive tab -->
                <li class="nav-item">
                    <a class="nav-link  <?= $status == 'archived' ? 'active' : '' ?>  "
                        data-title="{{ __tr('Archive') }}"
                        href="<?= route('vendor.contact.group.read.list_view', ['status' => 'archived']) ?>">
                        <?= __tr('Archive') ?>
                    </a>
                </li>
                <!-- /Archive tab -->
            </ul>
            <!-- Add New Group Modal -->
            <x-lw.modal id="lwAddNewGroup" :header="__tr('Add New Group')" :hasForm="true">
                <!--  Add New Group Form -->
                <x-lw.form id="lwAddNewGroupForm" :action="route('vendor.contact.group.write.create')"
                    :data-callback-params="['modalId' => '#lwAddNewGroup', 'datatableId' => '#lwGroupList']"
                    data-callback="appFuncs.modelSuccessCallback">
                    <!-- form body -->
                    <div class="lw-form-modal-body">
                        <!-- form fields form fields -->
                        <!-- Title -->
                        <x-lw.input-field type="text" id="lwTitleField" data-form-group-class="" :label="__tr('Title')"
                            name="title" required="true" />
                        <!-- /Title -->
                        <!-- Description -->
                        <div class="form-group">
                            <label for="lwDescriptionField">{{ __tr('Description') }}</label>
                            <textarea cols="10" rows="3" id="lwDescriptionField" class="lw-form-field form-control"
                                placeholder="{{ __tr('Description') }}" name="description"></textarea>
                        </div>
                        <!-- /Description -->
                    </div>
                    <!-- form footer -->
                    <div class="modal-footer">
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close')
                            }}</button>
                    </div>
                </x-lw.form>
                <!--/  Add New Group Form -->
            </x-lw.modal>
            <!--/ Add New Group Modal -->

            <!-- Edit Group Modal -->
            <x-lw.modal id="lwEditGroup" :header="__tr('Edit Group')" :hasForm="true">
                <!--  Edit Group Form -->
                <x-lw.form id="lwEditGroupForm" :action="route('vendor.contact.group.write.update')"
                    :data-callback-params="['modalId' => '#lwEditGroup', 'datatableId' => '#lwGroupList']"
                    data-callback="appFuncs.modelSuccessCallback">
                    <!-- form body -->
                    <div id="lwEditGroupBody" class="lw-form-modal-body"></div>
                    <script type="text/template" id="lwEditGroupBody-template">

                        <input type="hidden" name="contactGroupIdOrUid" value="<%- __tData._uid %>" />
                        <!-- form fields -->
                        <!-- Title -->
           <x-lw.input-field type="text" id="lwTitleEditField" data-form-group-class="" :label="__tr('Title')" value="<%- __tData.title %>" name="title"  required="true"                 />
                <!-- /Title -->
                <!-- Description -->
                <div class="form-group">
                <label for="lwDescriptionEditField">{{ __tr('Description') }}</label>
                <textarea cols="10" rows="3" id="lwDescriptionEditField" value="<%- __tData.description %>" class="lw-form-field form-control" placeholder="{{ __tr('Description') }}" name="description"          ><%- __tData.description %></textarea>
            </div>
                <!-- /Description -->
                     </script>
                    <!-- form footer -->
                    <div class="modal-footer">
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close')
                            }}</button>
                    </div>
                </x-lw.form>
                <!--/  Edit Group Form -->
            </x-lw.modal>
            <!--/ Edit Group Modal -->
            <div class="modern-table-container">
                <!-- Header Controls: Show entries (left) and Search (right) -->
                <div class="table-header-controls">
                    <div class="entries-control">
                        <label for="gl-entries-per-page" class="mb-0">{{ __tr('Show') }}</label>
                        <select id="gl-entries-per-page" class="entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="entries-text">{{ __tr('entries') }}</span>
                    </div>
                    <div class="search-control">
                        <input type="text" id="gl-table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>

                <!-- Toolbar: Select All / Bulk Actions -->
                <div class="px-3 pt-3">
                    <button x-show="!isSelectedAll" class="btn btn-dark btn-sm mb-2" @click="toggleAll">{{ __tr('Select All') }}</button>
                    <button x-show="isSelectedAll" class="btn btn-dark btn-sm mb-2" @click="toggleAll">{{ __tr('Unselect All') }}</button>
                    <div class="btn-group mb-2">
                        <button :class="!selectedContacts.length ? 'disabled' : ''" class="btn btn-danger mt-1 btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                            {{ __tr('Bulk Actions') }}
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" @click.prevent="deleteSelectedContactGroups" href="#">{{ __tr('Delete Selected Groups') }}</a>
                            @if($status == 'active')
                                <a class="dropdown-item" @click.prevent="archiveSelectedContactGroups" href="#">{{ __tr('Archive Selected Groups') }}</a>
                            @else
                                <a class="dropdown-item" @click.prevent="unarchiveSelectedContactGroups" href="#">{{ __tr('Unarchive Selected Groups') }}</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <x-lw.datatable
                        id="lwGroupList"
                        class="modern-datatable table-hover align-middle"
                        lw-card-classes="border-0 rounded"
                        data-page-length="50"
                        :url="route('vendor.contact.group.read.list', ['status' => $status])">
                        <th style="width: 1px;padding:0;" data-name="none"></th>
                        <th data-name="none" data-template="#lwSelectMultipleContactGroupsCheckbox">{{ __tr('Select') }}</th>
                        <th data-orderable="true" data-name="title">{{ __tr('Title') }}</th>
                        <th data-name="description">{{ __tr('Description') }}</th>
                        <th data-template="#groupActionColumnTemplate" name="null" class="text-right">{{ __tr('Action') }}</th>
                    </x-lw.datatable>
                </div>
            </div>
        </div>

        <!-- action template -->
        <script type="text/template" id="groupActionColumnTemplate">
            <div class="dropdown d-inline-block action-dropdown">
                <button class="btn btn-sm btn-light action-kebab-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __tr('Actions') }}">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <a class="dropdown-item" href="<%= __Utils.apiURL("{{ route('vendor.contact.read.list_view', [ 'groupUid']) }}", {'groupUid': __tData._uid}) %>">
                        <i class="fa fa-users mr-2 text-info"></i>{{  __tr('Group Contacts') }}
                    </a>
                    <a class="dropdown-item lw-ajax-link-action" data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Edit') }}" data-response-template="#lwEditGroupBody" href="<%= __Utils.apiURL("{{ route('vendor.contact.group.read.update.data', [ 'contactGroupIdOrUid']) }}", {'contactGroupIdOrUid': __tData._uid}) %>" data-toggle="modal" data-target="#lwEditGroup">
                        <i class="fa fa-edit mr-2"></i>{{  __tr('Edit') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <% if(__tData.status != 5) { %>
                        <a class="dropdown-item lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.contact.group.write.archive', [ 'contactGroupIdOrUid']) }}", {'contactGroupIdOrUid': __tData._uid}) %>" data-confirm="#lwArchiveGroup-template" title="{{ __tr('Archive') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwGroupList']) }}" data-callback="appFuncs.modelSuccessCallback">
                            <i class="fa fa-box mr-2 text-secondary"></i>{{  __tr('Archive') }}
                        </a>
                    <% } else { %>
                        <a class="dropdown-item lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.contact.group.write.unarchive', [ 'contactGroupIdOrUid']) }}", {'contactGroupIdOrUid': __tData._uid}) %>" data-confirm="#lwUnarchiveGroup-template" title="{{ __tr('Unarchive') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwGroupList']) }}" data-callback="appFuncs.modelSuccessCallback">
                            <i class="fa fa-box-open mr-2 text-secondary"></i>{{  __tr('Unarchive') }}
                        </a>
                    <% } %>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger lw-ajax-link-action-via-confirm" data-method="post" href="<%= __Utils.apiURL("{{ route('vendor.contact.group.write.delete', [ 'contactGroupIdOrUid']) }}", {'contactGroupIdOrUid': __tData._uid}) %>" data-confirm="#lwDeleteGroup-template" title="{{ __tr('Delete') }}" data-callback-params="{{ json_encode(['datatableId' => '#lwGroupList']) }}" data-callback="appFuncs.modelSuccessCallback">
                        <i class="fa fa-trash mr-2"></i>{{  __tr('Delete') }}
                    </a>
                </div>
            </div>
        </script>
        <!-- /action template -->
        <!-- select multiple -->
        <script type="text/template" id="lwSelectMultipleContactGroupsCheckbox">
            <input @click="toggle('<%- __tData._uid %>')" type="checkbox" name="selected_groups[]" class="lw-checkboxes custom-checkbox" value="<%- __tData._uid %>">
        </script>
        <!-- /select multiple -->

        <!-- Group delete template -->
        <script type="text/template" id="lwDeleteGroup-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this Group?') }}</p>
    </script>
        <!-- /Group delete template -->
        <!-- Group archive template -->
        <script type="text/template" id="lwArchiveGroup-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to archive this Group?') }}</p>
    </script>
        <!-- /Group archive template -->
        <!-- Group unarchive template -->
        <script type="text/template" id="lwUnarchiveGroup-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to unarchive this Group?') }}</p>
    </script>
        <!-- /Group archive template -->
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function initGroupListControls() {
            var table = $('#lwGroupList').DataTable();
            if (!table) { setTimeout(initGroupListControls, 150); return; }

            // Set default select to current page length
            $('#gl-entries-per-page').val(table.page.len());

            // Change page length
            $('#gl-entries-per-page').off('change.groups').on('change.groups', function() {
                var len = parseInt(this.value, 10) || 10;
                table.page.len(len).draw();
            });

            // Search
            $('#gl-table-search').off('keyup.groups').on('keyup.groups', function() {
                table.search(this.value).draw();
            });

            // Keep header controls responsive to redraws
            $('#lwGroupList').on('draw.dt', function(){
                $('#gl-entries-per-page').val(table.page.len());
            });
        }

        // Initialize after a small delay to allow DataTable setup
        setTimeout(initGroupListControls, 500);
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

    /* Badge polish (kept for consistency if needed) */
    .badge { font-weight: 600; letter-spacing: 0.2px; }

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