@extends('layouts.app', ['title' => __tr('Bot Flows')])
@section('content')
@include('users.partials.header', [
'title' => __tr(''),
'description' => '',
'class' => 'col-lg-7'
])
<div class="container-fluid mt-lg--6">
    <div class="row mt-3">
        <!-- Header Section -->
        <div class="col-xl-12 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-nowrap mt-5">
                <h1 class="mb-0"><i class="fas fa-robot me-2" style="color: #0B7753;"></i> {{ __tr('Bot Flows') }}</h1>
                <div class="d-flex align-items-center">
                    <button type="button"
                            class="lw-btn btn btn-neo btn-neo-gradient-green"
                            data-toggle="modal"
                            data-target="#lwAddNewBotFlow">
                        <i class="fas fa-plus"></i> {{ __tr('Add New Bot Flow') }}
                    </button>
                </div>
            </div>
        </div>
        <!-- Add New Bot Flow Modal -->
        <x-lw.modal id="lwAddNewBotFlow" :header="__tr('Add New Bot Flow')" :hasForm="true">
            <!--  Add New Bot Flow Form -->
            <x-lw.form id="lwAddNewBotFlowForm" :action="route('vendor.bot_reply.bot_flow.write.create')"
                :data-callback-params="['modalId' => '#lwAddNewBotFlow', 'datatableId' => '#lwBotFlowList']"
                data-callback="appFuncs.modelSuccessCallback" x-data="{triggerType:'is'}">
                <!-- form body -->
                <div class="lw-form-modal-body">
                    <!-- form fields form fields -->
                    <!-- Title -->
                    <x-lw.input-field type="text" id="lwTitleField" data-form-group-class="" :label="__tr('Title')"
                        name="title" required="true" minlength="1" maxlength="150" />
                    <!-- /Title -->

                    <!-- Trigger Type -->
                    <x-lw.input-field x-model="triggerType" type="selectize" id="lwTriggerTypeField"
                        data-form-group-class="" data-selected="is" :label="__tr('Trigger Type')" name="trigger_type"
                        required="true">
                        <x-slot name="selectOptions">
                            <option value="">{{ __tr('How do you want to trigger this flow?') }}</option>
                            @foreach (configItem('bot_reply_trigger_types') as $replyBotTypeKey => $replyBotType)
                            <option value="{{ $replyBotTypeKey }}">{{ $replyBotType['title'] }} </option>
                            @endforeach
                        </x-slot>
                    </x-lw.input-field>
                    <!-- /Trigger Type -->

                    @foreach (configItem('bot_reply_trigger_types') as $replyBotTypeKey => $replyBotType)
                    <div x-show="triggerType == '{{ $replyBotTypeKey }}'" class="alert alert-dark">{{
                        $replyBotType['description'] }}</div>
                    @endforeach

                    <!-- Start Trigger -->
                    <div x-show="triggerType != 'welcome' && triggerType != 'new_message'">
                        <x-lw.input-field type="text" id="lwStartTriggerField" data-form-group-class="" :label="__tr('Start Trigger Subject')" name="start_trigger" required="true" minlength="1" maxlength="255" />
                        <div><small class="text-muted">{{ __tr('You can have comma separated multiple triggers.') }}</small></div>
                    </div>
                    <!-- /Start Trigger -->
                </div>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
            <!--/  Add New Bot Flow Form -->
        </x-lw.modal>
        <!--/ Add New Bot Flow Modal -->
        <!-- Edit Bot Flow Modal -->
        <x-lw.modal id="lwEditBotFlow" :header="__tr('Edit Bot Flow')" :hasForm="true">
            <!--  Edit Bot Flow Form -->
            <x-lw.form id="lwEditBotFlowForm" :action="route('vendor.bot_reply.bot_flow.write.update')"
                :data-callback-params="['modalId' => '#lwEditBotFlow', 'datatableId' => '#lwBotFlowList']"
                data-callback="appFuncs.modelSuccessCallback">
                <!-- form body -->
                <div id="lwEditBotFlowBody" class="lw-form-modal-body"></div>
                <script type="text/template" id="lwEditBotFlowBody-template">
                    <div x-data="{triggerType:'<%- __tData.trigger_type || 'is' %>'}">
                    <input type="hidden" name="botFlowIdOrUid" value="<%- __tData._uid %>" />
                        <!-- form fields -->
                        <!-- Title -->
           <x-lw.input-field type="text" id="lwTitleEditField" data-form-group-class="" :label="__tr('Title')" value="<%- __tData.title %>" name="title"  required="true"      minlength="1"      maxlength="150"           />
                <!-- /Title -->

                        <!-- Trigger Type -->
                        <x-lw.input-field x-model="triggerType" type="selectize" id="lwTriggerTypeEditField"
                            data-form-group-class="" data-selected="<%- __tData.trigger_type || 'is' %>" :label="__tr('Trigger Type')" name="trigger_type"
                            required="true">
                            <x-slot name="selectOptions">
                                <option value="">{{ __tr('How do you want to trigger this flow?') }}</option>
                                @foreach (configItem('bot_reply_trigger_types') as $replyBotTypeKey => $replyBotType)
                                <option value="{{ $replyBotTypeKey }}">{{ $replyBotType['title'] }} </option>
                                @endforeach
                            </x-slot>
                        </x-lw.input-field>
                        <!-- /Trigger Type -->

                        @foreach (configItem('bot_reply_trigger_types') as $replyBotTypeKey => $replyBotType)
                        <div x-show="triggerType == '{{ $replyBotTypeKey }}'" class="alert alert-dark">{{
                            $replyBotType['description'] }}</div>
                        @endforeach

                        <!-- Start Trigger -->
                        <div x-show="triggerType != 'welcome' && triggerType != 'new_message'">
           <x-lw.input-field type="text" id="lwStartTriggerEditField" data-form-group-class="" :label="__tr('Start Trigger Subject')" value="<%- __tData.start_trigger %>" name="start_trigger"  required="true"    minlength="1"      maxlength="255"           />
                        <div><small class="text-muted">{{ __tr('You can have comma separated multiple triggers.') }}</small></div>
                        </div>
                <!-- /Start Trigger -->
                <div class="form-group pt-3">
                    <input type="checkbox" id="lwEditBotFlowStatus" <%- __tData.status == 1 ? 'checked' : '' %> data-lw-plugin="lwSwitchery" value="1" name="status">
                    <label for="lwEditBotFlowStatus">{{  __tr('Status') }}</label>
                </div>
                    </div>
                     </script>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
            <!--/  Edit Bot Flow Form -->
        </x-lw.modal>
        <!--/ Edit Bot Flow Modal -->
        <!-- DataTable Container -->
        <div class="col-xl-12">
            <div class="modern-table-container">
                <!-- Header Controls: Show entries (left) and Search (right) -->
                <div class="table-header-controls">
                    <div class="entries-control">
                        <label for="bf-entries-per-page" class="mb-0">{{ __tr('Show') }}</label>
                        <select id="bf-entries-per-page" class="entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="entries-text">{{ __tr('entries') }}</span>
                    </div>
                    <div class="search-control">
                        <input type="text" id="bf-table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <x-lw.datatable
                        id="lwBotFlowList"
                        class="modern-datatable table-hover align-middle"
                        lw-card-classes="border-0 rounded"
                        data-page-length="100"
                        :url="route('vendor.bot_reply.bot_flow.read.list')">
                        <th data-orderable="true" data-name="title">{{ __tr('Title') }}</th>
                        <th data-orderable="true" data-name="trigger_type" data-template="#triggerTypeColumnTemplate">{{ __tr('Trigger Type') }}</th>
                        <th data-orderable="true" data-name="start_trigger">{{ __tr('Start Trigger Subject') }}</th>
                        <th data-template="#botFlowStatusColumnTemplate" name="null" class="text-center">{{ __tr('Status') }}</th>
                        <th data-template="#botFlowActionColumnTemplate" name="null" class="text-right">{{ __tr('Action') }}</th>
                    </x-lw.datatable>
                </div>
            </div>
        </div>
        <!-- Trigger Type Column Template -->
        <script type="text/template" id="triggerTypeColumnTemplate">
            <%
                var triggerTypes = {
                    @foreach (configItem('bot_reply_trigger_types') as $key => $type)
                        '{{ $key }}': '{{ $type['title'] }}',
                    @endforeach
                };
                var triggerType = __tData.trigger_type || 'is';
                var displayName = triggerTypes[triggerType] || triggerType;
            %>
            <span class="badge badge-info"><%= displayName %></span>
        </script>

        <!-- Status Column Template -->
        <script type="text/template" id="botFlowStatusColumnTemplate">
            <% if(__tData.status == 'Active') { %>
                <span class="badge badge-success">
                    <i class="fa fa-check-circle"></i> {{ __tr('ACTIVE') }}
                </span>
            <% } else { %>
                <span class="badge badge-danger">
                    <i class="fa fa-times-circle"></i> {{ __tr('INACTIVE') }}
                </span>
            <% } %>
        </script>
        <!-- Action Column Template -->
        <script type="text/template" id="botFlowActionColumnTemplate">
            <div class="dropdown d-inline-block action-dropdown">
                <button class="btn btn-sm btn-light action-kebab-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __tr('Actions') }}">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <a class="dropdown-item lw-ajax-link-action"
                       data-pre-callback="appFuncs.clearContainer"
                       data-response-template="#lwEditBotFlowBody"
                       href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.bot_flow.read.update.data', ['botFlowIdOrUid']) }}', {'botFlowIdOrUid': __tData._uid}) %>"
                       data-toggle="modal"
                       data-target="#lwEditBotFlow">
                        <i class="fa fa-edit mr-2"></i>{{ __tr('Edit') }}
                    </a>
                    <a class="dropdown-item"
                       href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.bot_flow.builder.read.view', ['botFlowIdOrUid']) }}', {'botFlowIdOrUid': __tData._uid}) %>">
                        <i class="fas fa-project-diagram mr-2"></i>{{ __tr('Flow Builder') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger lw-ajax-link-action-via-confirm"
                       data-method="post"
                       data-confirm="#lwDeleteBotFlow-template"
                       data-callback-params="{{ json_encode(['datatableId' => '#lwBotFlowList']) }}"
                       data-callback="appFuncs.modelSuccessCallback"
                       href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.bot_flow.write.delete', ['botFlowIdOrUid']) }}', {'botFlowIdOrUid': __tData._uid}) %>">
                        <i class="fa fa-trash mr-2"></i>{{ __tr('Delete') }}
                    </a>
                </div>
            </div>
        </script>

        <!-- /action template -->

        <!-- Bot Flow delete template -->
        <script type="text/template" id="lwDeleteBotFlow-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this Bot Flow?') }}</p>
    </script>
        <!-- /Bot Flow delete template -->
    </div>
</div>

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
    .modern-datatable tbody td:nth-child(4) { text-align: center !important; }

    /* Badge polish */
    .badge { font-weight: 600; letter-spacing: 0.2px; }
    .badge-success { background-color: #22a06b; }
    .badge-danger { background-color: #e35d6a; }
    .badge-warning { background-color: #f5a524; color: #1f2937; }

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

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var $tableEl = $('#lwBotFlowList');

        function bindControls(table) {
            // Set default select to current page length
            $('#bf-entries-per-page').val(table.page.len());

            // Change page length
            $('#bf-entries-per-page').off('change.botflows').on('change.botflows', function() {
                var len = parseInt(this.value, 10) || 10;
                table.page.len(len).draw();
            });

            // Ensure server receives current custom search value on every request
            $tableEl.off('preXhr.botflows').on('preXhr.botflows', function (e, settings, data) {
                data.search = data.search || {};
                data.search.value = ($('#bf-table-search').val() || '').toString();
            });

            // Debounced search -> trigger ajax reload
            var debounceTimer = null;
            $('#bf-table-search').off('input.botflows keyup.botflows').on('input.botflows keyup.botflows', function() {
                var val = this.value;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function(){
                    // Explicitly reload so server gets latest search via preXhr hook
                    table.ajax.reload(null, true);
                }, 180);
            });

            // Keep header controls responsive to redraws
            $tableEl.off('draw.botflows').on('draw.botflows', function(){
                $('#bf-entries-per-page').val(table.page.len());
            });
        }

        function ensureInitializedThenBind() {
            if ($.fn.dataTable && $.fn.dataTable.isDataTable && $.fn.dataTable.isDataTable('#lwBotFlowList')) {
                return bindControls($tableEl.DataTable());
            }
            // Bind once when DataTable fires init
            $tableEl.one('init.dt', function(){
                bindControls($tableEl.DataTable());
            });
            // Trigger init if not already
            if (window.initializeDatatable) {
                window.initializeDatatable();
            }
            // Fallback: re-check soon in case init event already fired before binding
            setTimeout(function(){
                if ($.fn.dataTable && $.fn.dataTable.isDataTable && $.fn.dataTable.isDataTable('#lwBotFlowList')) {
                    bindControls($tableEl.DataTable());
                }
            }, 250);
        }

        ensureInitializedThenBind();
    });
</script>
@endsection