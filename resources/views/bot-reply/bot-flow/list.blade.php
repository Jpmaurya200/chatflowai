@extends('layouts.app', ['title' => __tr('Bot Flows')])
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endpush
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
            <div class="mt-5 d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0" style="color: #22A755;">
                    <i class="fas fa-robot me-2"></i>{{ __tr(' Bot Flows') }}
                </h1>
                <div class="d-flex">
                    <button type="button"
                        class="lw-btn btn btn-primary btn-rounded animate__animated animate__fadeIn"
                        data-toggle="modal"
                        data-target="#lwAddNewBotFlow">
                        <i class="fas fa-plus-circle me-2"></i>{{ __tr(' Add New Bot Flow') }}
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __tr('Bot Flows List') }}</h3>
                </div>
                <div class="card-body">
                    <x-lw.datatable id="lwBotFlowList" :url="route('vendor.bot_reply.bot_flow.read.list')">
                        <th data-orderable="true" data-name="title">{{ __tr('Title') }}</th>
                        <th data-orderable="true" data-name="trigger_type" data-template="#triggerTypeColumnTemplate">{{ __tr('Trigger Type') }}</th>
                        <th data-orderable="true" data-name="start_trigger">{{ __tr('Start Trigger Subject') }}</th>
                        <th data-template="#botFlowStatusColumnTemplate" name="null" class="status-column">{{ __tr('Status') }}</th>
                        <th data-template="#botFlowActionColumnTemplate" name="null">{{ __tr('Action') }}</th>
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
            <div class="btn-group" role="group">
                <!-- Edit Button -->
                <a data-pre-callback="appFuncs.clearContainer"
                   title="{{ __tr('Edit') }}"
                   class="btn btn-sm btn-primary lw-ajax-link-action"
                   data-response-template="#lwEditBotFlowBody"
                   href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.bot_flow.read.update.data', ['botFlowIdOrUid']) }}', {'botFlowIdOrUid': __tData._uid}) %>"
                   data-toggle="modal"
                   data-target="#lwEditBotFlow">
                    <i class="fa fa-edit"></i>
                </a>

                <!-- Delete Button -->
                <a data-method="post"
                   href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.bot_flow.write.delete', ['botFlowIdOrUid']) }}', {'botFlowIdOrUid': __tData._uid}) %>"
                   class="btn btn-sm btn-danger lw-ajax-link-action-via-confirm"
                   data-confirm="#lwDeleteBotFlow-template"
                   title="{{ __tr('Delete') }}"
                   data-callback-params="{{ json_encode(['datatableId' => '#lwBotFlowList']) }}"
                   data-callback="appFuncs.modelSuccessCallback">
                    <i class="fa fa-trash"></i>
                </a>

                <!-- Flow Builder Button -->
                <a title="{{ __tr('Flow Builder') }}"
                   class="btn btn-sm btn-success"
                   href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.bot_flow.builder.read.view', ['botFlowIdOrUid']) }}', {'botFlowIdOrUid': __tData._uid}) %>">
                    <i class="fas fa-project-diagram"></i>
                </a>
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

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#lwBotFlowList').DataTable();
    });
</script>
@endsection