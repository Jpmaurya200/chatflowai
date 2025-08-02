@extends('layouts.app', ['title' => __tr('Bot Flow Builder')])
@section('content')
@include('users.partials.header', [
'title' => __tr('Bot Flow Builder'),
'description' => '',
'class' => 'col-lg-7'
])
{!! __yesset([
'static-assets/packages/jquery.flowchart/jquery.flowchart.min.css'
]) !!}
<!-- enhanced old code with new functionality -->
<div class="container-fluid mt-lg--6">
    <div class="row mt-3" x-data="{isAdvanceBot:'interactive',botFlowUid:'{{ $botFlowUid }}'}">
        <!-- button -->
        <div class="col-xl-12 mb-3">
            <div class="mt-5">
                <a class="lw-btn btn btn-secondary" href="{{ route('vendor.bot_reply.bot_flow.read.list_view') }}">{{
                        __tr('Back to Bot Flows') }}</a>
            </div>
        </div>
        <!--/ button -->
        <div class="col-xl-12" x-data="initialAlpineData">
           <div class="row">
            <div class="card col-12">
                <div class="card-header">
                    <span class="h2">{{ $botFlow->title }}</span>
                    <div class="float-right">
                        <span class="form-group m-0 mr-3">
                            <label for="lwUpdateStatusSwitch">
                                <input data-lw-plugin="lwSwitchery" @click="function() {
                                    __DataRequest.post('{{ route('vendor.bot_reply.bot_flow_data.write.update') }}', {
                                        'botFlowUid' : '{{ $botFlowUid }}',
                                        'bot_flow_status' : (!botFlowStatusValue ? 1 : 0)
                                        }, function() {});
                                }" {{ ($botFlow->status == 1) ? 'checked' : '' }} x-model="botFlowStatusValue" value="1" class="custom-checkbox" id="lwUpdateStatusSwitch" type="checkbox" name="bot_flow_status">
                                {{  __tr('Status') }}
                            </label>
                        </span>
                        <template x-if="isUnsavedContent">
                            <div class="btn-group">
                            <button @click="window.unsavedAlert()" type="button" class="btn btn-primary dropdown-toggle" aria-expanded="false">
                            {{ __tr('Add New Bot Reply') }}
                        </button>
                            </div>
                        </template>
                        <template x-if="!isUnsavedContent">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                    aria-expanded="false">
                                    {{ __tr('Add New Bot Reply') }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <button type="button" @click="isAdvanceBot = 'simple'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Simple Bot Reply')
                                        }}</button>
                                    <button type="button" @click="isAdvanceBot = 'media'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Media Bot Reply')
                                        }}</button>
                                    <button type="button" @click="isAdvanceBot = 'interactive'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Advance Interactive Bot Reply') }}</button>
                                    <button type="button" @click="isAdvanceBot = 'question'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Ask Question') }}</button>
                                    <button type="button" @click="isAdvanceBot = 'goto'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Goto Node') }}</button>
                                    <button type="button" @click="isAdvanceBot = 'team_assignment'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Team Assignment Node') }}</button>
                                    <button type="button" @click="isAdvanceBot = 'webhook'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Webhook Node') }}</button>
                                    <button type="button" @click="isAdvanceBot = 'stay_in_session'" class="dropdown-item btn"
                                        data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Stay in Session Node') }}</button>
                                </div>
                            </div>
                        </template>
                        <button class="btn btn-warning" @click="saveData"><i class="fa fa-save"></i> {{  __tr('Save') }}</button>
                    </div>
                </div>
                {{-- added just to initialize --}}
                <div class="pl-1 m-1 lw-flow-builder-container-holder" dir="ltr">
                    <template x-text="processedFlowBots"></template>
                    <div class="lw-flow-builder-container p-4 card-body" id="lwBotFlowBuilder"></div>
                </div>
            </div>
           </div>
        </div>
        @include('bot-reply.bot-forms-partial')
    </div>
</div>
  <!-- Bot Reply delete template -->
  <script type="text/template" id="lwDeleteBotReply-template">
    <h2>{{ __tr('Are You Sure!') }}</h2>
    <p>{{ __tr('You want to delete this Bot Reply?') }}</p>
</script>
<!-- /Bot Reply delete template -->
  <!-- Bot Reply duplicate template -->
  <script type="text/template" id="lwDuplicateBotReply-template">
    <h2>{{ __tr('Are You Sure!') }}</h2>
    <p>{{ __tr('Are you sure you want to duplicate this Bot Reply?') }}</p>
</script>
<!-- /Bot Reply duplicate template -->
@push('js')
{!! __yesset([
    'static-assets/packages/jqueryui-1.13.3/jquery-ui.min.js',
    'static-assets/packages/others/jquery.mousewheel.min.js',
    'static-assets/packages/others/jquery.panzoom.min.js',
    'static-assets/packages/jquery.flowchart/jquery.flowchart.js'
]) !!}
@endpush
<script>
    var data = {
        links : {}
    };
        window.flowchartData = {};
        window.$flowBuilderInstance = null;
        window.__isUnsavedContent = false;
        window.isFlowChatInitialized = false;
</script>
@push('appScripts')
<script>
    $(document).ready(function() {
       'use strict';
        window.$flowBuilderInstance = $('#lwBotFlowBuilder').flowchart({
            data: {},
            canUserEditLinks: true,
            canUserMoveOperators: true,
            defaultSelectedLinkColor: '#16a34a',
            grid: 10,
            multipleLinksOnInput: true,
            multipleLinksOnOutput: true,
            linkWidth: 3,
            defaultLinkColor: '#50c878',
            defaultSelectedLinkColor: '#16a34a',
            onOperatorSelect : function(elementUid) {
                return true;
            },
            onLinkCreate : function(linkId, linkData) {
                data.links[linkId] = linkData;
                if(window.isFlowChatInitialized) {
                    window.updateDraft();
                };
                return true;
            },
            onLinkSelect : function(linkId, linkData) {
                $('.lw-operator-link-'+data.links[linkId]['toOperator']).show();
                return true;
            },
            onLinkUnselect : function(linkId) {
                $('.lw-delete-link-btn').hide();
                return true;
            },
            onLinkDelete : function(linkId) {
                delete data.links[linkId];
                window.updateDraft();
                return true;
            },
            onOperatorMoved : function(operatorId, position) {
                window.updateDraft();
            },
        });
         // Enhanced Panzoom initialization with zoom controls
        /*
        @link https://github.com/timmywil/panzoom/tree/v3.2.2
        */
        window.$panzoomInstance = window.$flowBuilderInstance.panzoom({
            contain: 'automatic',
            cursor: "grab",
            increment: 0.1,
            minScale: 0.1,
            maxScale: 3,
            startTransform: 'scale(1)'
        });

        // Initialize zoom level tracking
        window.currentZoomLevel = 1;
        window.updateZoomDisplay = function(scale) {
            window.currentZoomLevel = scale || 1;
            console.log('Zoom level: ' + Math.round(window.currentZoomLevel * 100) + '%');
        };

        // Mouse wheel zoom support
        window.$flowBuilderInstance.on('mousewheel.focal', function(e) {
            e.preventDefault();
            const delta = e.delta || e.originalEvent.wheelDelta;
            const zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;

            window.$flowBuilderInstance.panzoom('zoom', zoomOut, {
                increment: 0.1,
                animate: false,
                focal: e
            });

            const matrix = window.$flowBuilderInstance.panzoom('getMatrix');
            window.updateZoomDisplay(matrix[0]);
        });

        // Update zoom display on pan/zoom events
        window.$flowBuilderInstance.on('panzoomchange', function(e, panzoom, transform) {
            const matrix = panzoom.getMatrix();
            window.updateZoomDisplay(matrix[0]);
        });

    // required to trigger default flow
    __DataRequest.updateModels({
        tempClick : '{{ uniqid() }}'
    });
    window.onBotReplyDeleted = function(response) {
        window.$flowBuilderInstance.flowchart('deleteOperator', response.data.botReplyUid);
        _.defer(function() {
            window.saveFlowChartData();
        });
        appFuncs.modelSuccessCallback(response);
    };
    window.unsavedAlert = function() {
        showConfirmation('{{ __tr('You have unsaved changes. You need to save it first, Do you want to save it now?') }}', function() {
            window.saveFlowChartData();
        });
    };
    window.updateDraft = function(response) {
        window.__isUnsavedContent = true;
        __DataRequest.updateModels({
            isUnsavedContent : true
        });
        return true;
    };
    window.saveFlowChartData = function() {
        window.isFlowChatInitialized = false;
        __DataRequest.post("{{ route('vendor.bot_reply.bot_flow_data.write.update') }}", {
            'botFlowUid' : '{{ $botFlowUid }}',
            'flow_chart_data' : window.$flowBuilderInstance.flowchart('getData')
            }, function() {
                window.__isUnsavedContent = false;
                // __Utils.viewReload();
        });
    };
    window.onbeforeunload = function (e) {
        if(window.__isUnsavedContent) {
            var message = "{{ __tr('Changes that you made may not be saved.') }}",
            e = e || window.event;
            // For IE and Firefox
            if (e) {
                e.returnValue = message;
            }
            // For Safari
            return message;
        };
    };
    _.defer(function() {
        window.isFlowChatInitialized = true;
    });
});
</script>
@endpush
<script>
(function() {
    'use strict';
    document.addEventListener('alpine:init', () => {
        Alpine.data('initialAlpineData', () => ({
            tempClick:false,
            isUnsavedContent:false,
            botFlowStatusValue:{{ $botFlow->status == 1 ?: 0 }},
            saveData:function() {
                if(window.$flowBuilderInstance) {
                    window.saveFlowChartData();
                };
                return {};
            },
            flowBots: @json($flowBots),
            botFlowData: @json($botFlow->__data['flow_builder_data'] ?? []),
            processedFlowBots: function () {
                var xyz = this.tempClick;
                _.merge(data, this.botFlowData, {
                    operators : {
                        start : {
                            top: 10,
                            left: 10,
                            properties: {
                                title: "{{ __tr('Start') }} ->",
                                type: 'start',
                                outputs: {
                                    start_output : {
                                        label : '{{ $botFlow->trigger_type === "welcome" ? "Welcome Message" : $botFlow->start_trigger }}'
                                    }
                                }
                            }
                        }
                    }
                });
                var nodeCounter = 1;
                for (const flowBotIndex in this.flowBots) {
                    if (Object.hasOwnProperty.call(this.flowBots, flowBotIndex)) {
                        const element = this.flowBots[flowBotIndex];
                        let nodeType = 'message'; // Default to message

                        // Enhanced node type detection
                        if(_.get(element.__data, 'question_message')) {
                            nodeType = 'condition';
                        } else if(_.get(element.__data, 'goto_message')) {
                            nodeType = 'goto';
                        } else if(_.get(element.__data, 'wait_message')) {
                            nodeType = 'wait';
                        } else if(_.get(element.__data, 'team_assignment_message')) {
                            nodeType = 'team_assignment';
                        } else if(_.get(element.__data, 'webhook_message')) {
                            nodeType = 'webhook';
                        } else if(_.get(element.__data, 'custom_field_message')) {
                            nodeType = 'custom_field';
                        } else if(_.get(element.__data, 'stay_in_session_message')) {
                            nodeType = 'stay_in_session';
                        } else if(_.get(element.__data, 'whatsapp_template_message')) {
                            nodeType = 'whatsapp_template';
                        } else if(element.name && (element.name.toLowerCase().includes('wait') || element.name.toLowerCase().includes('delay'))) {
                            // Fallback detection for wait nodes
                            nodeType = 'wait';
                        }

                        // Enhanced node body generation based on type
                        let enhancedBody = '';
                        
                        if (nodeType === 'wait') {
                            // Wait node
                            const waitData = element.__data.wait_message || {};
                            const waitTime = waitData.wait_delay_seconds || waitData.delay_seconds || 5;
                            const waitText = waitData.wait_message || element.reply || 'Waiting...';

                            enhancedBody = `<div style="padding: 8px; background: #fff3cd; border-left: 3px solid #FFA500; margin-bottom: 8px;">
                                <strong style="color: #FFA500; font-size: 12px;">WAIT TIME:</strong><br>
                                <span style="color: #333; font-size: 13px;">${waitTime} seconds</span>
                            </div>
                            <div style="padding: 8px;">
                                <strong style="color: #666; font-size: 12px;">MESSAGE:</strong><br>
                                <span style="color: #333; font-size: 13px; line-height: 1.4;">${waitText}</span>
                            </div>`;
                        } else if (nodeType === 'condition' && element.__data?.question_message) {
                            // Question/Condition node
                            const questionData = element.__data.question_message;
                            const questionText = element.reply || 'Question message';
                            const conditionalFlows = questionData.conditional_flows || [];
                            const storeInField = questionData.store_in_field || '';
                            
                            enhancedBody = `<div style="padding: 8px;">
                                <strong style="color: #666; font-size: 12px;">QUESTION:</strong><br>
                                <span style="color: #333; font-size: 13px; line-height: 1.4;">${questionText}</span>
                            </div>`;
                            
                            if (storeInField) {
                                enhancedBody += `<div style="padding: 8px; background: #e7f3ff; border-left: 3px solid #17a2b8; margin-bottom: 8px;">
                                    <strong style="color: #17a2b8; font-size: 12px;">MAPPED FIELD:</strong><br>
                                    <span style="color: #333; font-size: 13px;">${storeInField}</span>
                                </div>`;
                            }
                            
                            if (conditionalFlows.length > 0) {
                                enhancedBody += `<div style="padding: 8px;">
                                    <strong style="color: #666; font-size: 12px;">OPTIONS:</strong><br>`;
                                conditionalFlows.forEach(flow => {
                                    enhancedBody += `<div style="margin: 4px 0; padding: 4px 8px; background: #dbeafe; border: 1px solid #3b82f6; border-radius: 4px;">
                                        <span style="color: #1d4ed8; font-size: 12px; font-weight: 500;">${flow.label || 'Option'}</span>
                                    </div>`;
                                });
                                enhancedBody += `</div>`;
                            }
                        } else if (nodeType === 'team_assignment') {
                            // Team Assignment node
                            const teamAssignmentData = element.__data.team_assignment_message || {};
                            const assignmentMessage = teamAssignmentData.assignment_message || 'Assigning to team member...';
                            const assignedTeamMemberName = teamAssignmentData.assigned_team_member_name || 'Team Member';

                            enhancedBody = `<div style="padding: 8px; background: #f3e8ff; border-left: 3px solid #8B5CF6; margin-bottom: 8px;">
                                <strong style="color: #8B5CF6; font-size: 12px;">ASSIGNED TO:</strong><br>
                                <span style="color: #333; font-size: 13px;"><i class="fas fa-user"></i> ${assignedTeamMemberName}</span>
                            </div>`;
                            
                            if (assignmentMessage) {
                                enhancedBody += `<div style="padding: 8px;">
                                    <strong style="color: #666; font-size: 12px;">MESSAGE:</strong><br>
                                    <span style="color: #333; font-size: 13px; line-height: 1.4;">${assignmentMessage}</span>
                                </div>`;
                            }
                            
                            enhancedBody += `<div style="padding: 8px; font-style: italic; color: #666; font-size: 12px;">
                                <i class="fas fa-hand-point-right"></i> Terminal Node - Ends Flow
                            </div>`;
                        } else if (nodeType === 'webhook') {
                            // Webhook node
                            const webhookData = element.__data.webhook_message || {};
                            const webhookUrl = webhookData.webhook_url || 'Not configured';
                            const httpMethod = webhookData.http_method || 'POST';
                            const successMessage = webhookData.success_message || 'Webhook executed successfully';

                            enhancedBody = `<div style="padding: 8px; background: #e8f5e8; border-left: 3px solid #28a745; margin-bottom: 8px;">
                                <strong style="color: #28a745; font-size: 12px;">WEBHOOK URL:</strong><br>
                                <span style="color: #333; font-size: 13px;"><i class="fas fa-globe"></i> ${httpMethod} ${webhookUrl}</span>
                            </div>`;
                            
                            if (successMessage) {
                                enhancedBody += `<div style="padding: 8px;">
                                    <strong style="color: #666; font-size: 12px;">SUCCESS MESSAGE:</strong><br>
                                    <span style="color: #333; font-size: 13px; line-height: 1.4;">${successMessage}</span>
                                </div>`;
                            }
                            
                            enhancedBody += `<div style="padding: 8px; font-style: italic; color: #666; font-size: 12px;">
                                <i class="fas fa-external-link-alt"></i> Calls External API
                            </div>`;
                        } else if (nodeType === 'custom_field') {
                            // Custom Field node
                            const customFieldData = element.__data.custom_field_message || {};
                            const customFieldName = customFieldData.custom_field_name || 'Custom Field';
                            const questionText = customFieldData.question_text || 'Please provide your information:';

                            enhancedBody = `<div style="padding: 8px; background: #e7f3ff; border-left: 3px solid #17a2b8; margin-bottom: 8px;">
                                <strong style="color: #17a2b8; font-size: 12px;">CUSTOM FIELD:</strong><br>
                                <span style="color: #333; font-size: 13px;"><i class="fas fa-form"></i> ${customFieldName}</span>
                            </div>
                            <div style="padding: 8px;">
                                <strong style="color: #666; font-size: 12px;">QUESTION:</strong><br>
                                <span style="color: #333; font-size: 13px; line-height: 1.4;">${questionText}</span>
                            </div>
                            <div style="padding: 8px; font-style: italic; color: #666; font-size: 12px;">
                                <i class="fas fa-keyboard"></i> Awaits User Input
                            </div>`;
                        } else if (nodeType === 'stay_in_session') {
                            // Stay in Session node
                            const stayInSessionData = element.__data.stay_in_session_message || {};
                            const sessionMessage = stayInSessionData.session_message || element.reply || 'Session will remain active...';

                            enhancedBody = `<div style="padding: 8px;">
                                <strong style="color: #666; font-size: 12px;">SESSION MESSAGE:</strong><br>
                                <span style="color: #333; font-size: 13px; line-height: 1.4;">${sessionMessage}</span>
                            </div>
                            <div style="padding: 8px; background: #f0f9ff; border-left: 3px solid #5a67d8; margin-bottom: 8px;">
                                <strong style="color: #5a67d8; font-size: 12px;">SESSION STATUS:</strong><br>
                                <span style="color: #333; font-size: 13px;"><i class="fas fa-infinity"></i> Active - Prevents Flow Termination</span>
                            </div>
                            <div style="padding: 8px; font-style: italic; color: #666; font-size: 12px;">
                                <i class="fas fa-hand-stop"></i> Keeps Session Active
                            </div>`;
                        } else if (nodeType === 'goto') {
                            // Goto node
                            const messageText = element.reply || 'Redirect message';

                            enhancedBody = `<div style="padding: 8px;">
                                <strong style="color: #666; font-size: 12px;">MESSAGE:</strong><br>
                                <span style="color: #333; font-size: 13px; line-height: 1.4;">${messageText}</span>
                            </div>
                            <div style="padding: 8px; font-style: italic; color: #666; font-size: 12px;">
                                <i class="fas fa-arrow-right"></i> Redirects to another flow
                            </div>`;
                        } else if (element.__data?.interaction_message) {
                            // Interactive message node
                            const interactionData = element.__data.interaction_message;
                            const messageText = element.reply || 'Interactive message';
                            const buttons = interactionData.buttons || {};
                            const buttonList = Object.values(buttons).filter(btn => btn && btn.trim());

                            enhancedBody = `<div style="padding: 8px;">
                                <strong style="color: #666; font-size: 12px;">MESSAGE:</strong><br>
                                <span style="color: #333; font-size: 13px; line-height: 1.4;">${messageText}</span>
                            </div>`;

                            if (buttonList.length > 0) {
                                enhancedBody += `<div style="padding: 8px;">
                                    <strong style="color: #666; font-size: 12px;">BUTTONS:</strong><br>`;
                                buttonList.forEach(button => {
                                    enhancedBody += `<div style="margin: 4px 0; padding: 4px 8px; background: #dbeafe; border: 1px solid #3b82f6; border-radius: 4px;">
                                        <span style="color: #1d4ed8; font-size: 12px; font-weight: 500;">${button}</span>
                                    </div>`;
                                });
                                enhancedBody += `</div>`;
                            }
                        } else {
                            // Simple message node
                            const messageText = element.reply || 'Simple message';

                            enhancedBody = `<div style="padding: 8px;">
                                <strong style="color: #666; font-size: 12px;">MESSAGE:</strong><br>
                                <span style="color: #333; font-size: 13px; line-height: 1.4;">${messageText}</span>
                            </div>`;
                        }

                        // Original button structure with enhanced body
                        const originalButtons = `<div class="btn-group btn-group-sm">
                            <a class="btn btn-default" style="padding: 1px;"></a> 
                            <a style="display:none;" x-show="isUnsavedContent" @click.prevent="window.unsavedAlert()" title="{{  __tr('Edit') }}" class="btn btn-default btn-sm" href="#"><i class="fa fa-edit"></i> {{  __tr('Edit') }}</a> 
                            <a x-show="!isUnsavedContent" data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Edit') }}" class="btn btn-default lw-ajax-link-action" data-response-template="#lwEditBotReplyBody" href="`+__Utils.apiURL("{{ route('vendor.bot_reply.read.update.data', [ 'botReplyIdOrUid']) }}", {'botReplyIdOrUid': element._uid})+`" data-toggle="modal" data-target="#lwEditBotReply"><i class="fa fa-edit"></i> {{  __tr('Edit') }}</a> 
                            <a style="display:none;" x-show="isUnsavedContent" @click.prevent="window.unsavedAlert()" title="{{  __tr('Delete') }}" class="btn btn-danger btn-sm" href="#"><i class="fa fa-trash"></i> {{  __tr('Delete') }} </a>
                            <a x-show="!isUnsavedContent" data-method="post" href="`+ __Utils.apiURL("{{ route('vendor.bot_reply.write.delete', [ 'botReplyIdOrUid']) }}", {'botReplyIdOrUid': element._uid}) +`" class="btn btn-danger lw-ajax-link-action" data-confirm="#lwDeleteBotReply-template" title="{{ __tr('Delete') }}" data-callback="onBotReplyDeleted"><i class="fa fa-trash"></i> {{  __tr('Delete') }}</a> 
                            <a style="display:none;" x-show="isUnsavedContent" @click.prevent="window.unsavedAlert()" title="{{  __tr('Duplicate') }}" class="btn btn-light btn-sm" href="#"><i class="fa fa-copy"></i></a> 
                            <a x-show="!isUnsavedContent" data-method="post" href="`+ __Utils.apiURL("{{ route('vendor.bot_reply.write.duplicate', [ 'botReplyIdOrUid']) }}", {'botReplyIdOrUid': element._uid}) +`" class="btn btn-light lw-ajax-link-action" data-confirm="#lwDuplicateBotReply-template" title="{{ __tr('Duplicate') }}"><i class="fa fa-copy"></i></a>
                            <a class="btn btn-light" style="padding: 1px;"></a>
                        </div>
                        <button style="display:none;" class="lw-delete-link-btn lw-operator-link-`+element._uid+` btn btn-warning btn-block btn-sm" @click="window.$flowBuilderInstance.flowchart('deleteSelected');"><i class="fas fa-unlink"></i> {{  __tr('Delete Link') }}</button>`;

                        data.operators[element._uid] = {
                            top: _.get(data.operators[element._uid],'top', _.random(150, 200)),
                            left: _.get(data.operators[element._uid],'left', _.random(20, 100)),
                            properties: {
                                title: element.name,
                                type: nodeType,
                                body: enhancedBody + originalButtons,
                                inputs: {
                                    input: {
                                        label: "-->"
                                    },
                                },
                                outputs: {}
                            }
                        };
                        
                        // Enhanced output handling based on node type
                        if(_.get(element.__data, 'interaction_message')) {
                            if(_.get(element.__data, 'interaction_message.buttons')) {
                                for (const interactiveButtonIndex in element.__data.interaction_message.buttons) {
                                    if (Object.hasOwnProperty.call(element.__data.interaction_message.buttons, interactiveButtonIndex)) {
                                        const buttonElement = element.__data.interaction_message.buttons[interactiveButtonIndex];
                                        data.operators[element._uid]['properties']['outputs'][interactiveButtonIndex] = {
                                            label: buttonElement
                                        };
                                    };
                                };
                            };
                            if(_.get(element.__data, 'interaction_message.list_data.sections')) {
                                for (const interactiveListSectionIndex in element.__data.interaction_message.list_data.sections) {
                                    if (Object.hasOwnProperty.call(element.__data.interaction_message.list_data.sections, interactiveListSectionIndex)) {
                                        const sectionElement = element.__data.interaction_message.list_data.sections[interactiveListSectionIndex];
                                        if(_.get(sectionElement, 'rows')) {
                                            for (const rowIndex in sectionElement.rows) {
                                                if (Object.hasOwnProperty.call(sectionElement.rows, rowIndex)) {
                                                    const rowElement = sectionElement.rows[rowIndex];
                                                        data.operators[element._uid]['properties']['outputs']['sections___' + interactiveListSectionIndex + '___rows___' + rowIndex + '___title'] = {
                                                        label: rowElement['title']
                                                    };
                                                };
                                            };
                                        };
                                    };
                                };
                            };
                            // Add standard WhatsApp bot flow outputs for interactive messages
                            data.operators[element._uid]['properties']['outputs']['no_input'] = {
                                label: '{{ __tr("No Input") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['no_match'] = {
                                label: '{{ __tr("No Match") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['delivery_failed'] = {
                                label: '{{ __tr("Delivery Failed") }}'
                            };
                        } else if(_.get(element.__data, 'goto_message')) {
                            // For goto nodes, no output connector needed - they auto-redirect
                            data.operators[element._uid]['properties']['outputs'] = {};
                        } else if(_.get(element.__data, 'question_message')) {
                            // For question nodes, create output connectors for conditional flows and default flow
                            const questionData = element.__data.question_message;
                            const conditionalFlows = questionData.conditional_flows || [];

                            // Add output connectors for each conditional flow
                            for (let i = 0; i < conditionalFlows.length; i++) {
                                const flow = conditionalFlows[i];
                                data.operators[element._uid]['properties']['outputs']['condition_' + i] = {
                                    label: flow.label || ('Condition ' + (i + 1))
                                };
                            }

                            // Add default output connector if default next node is specified
                            if (questionData.default_next_node) {
                                data.operators[element._uid]['properties']['outputs']['default_flow'] = {
                                    label: '{{ __tr("Default") }}'
                                };
                            }

                            // If no conditional flows or default node, add a simple output
                            if (conditionalFlows.length === 0 && !questionData.default_next_node) {
                                data.operators[element._uid]['properties']['outputs']['simple_output'] = {
                                    label: '{{ __tr("Continue") }}'
                                };
                            }

                            // Add standard WhatsApp bot flow outputs for question nodes
                            data.operators[element._uid]['properties']['outputs']['no_input'] = {
                                label: '{{ __tr("No Input") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['no_match'] = {
                                label: '{{ __tr("No Match") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['delivery_failed'] = {
                                label: '{{ __tr("Delivery Failed") }}'
                            };
                        } else if(_.get(element.__data, 'team_assignment_message')) {
                            // Team assignment nodes have NO outputs - they are terminal nodes
                            data.operators[element._uid]['properties']['outputs'] = {};
                        } else if(_.get(element.__data, 'stay_in_session_message')) {
                            // Stay in session nodes have NO outputs - they prevent flow termination
                            data.operators[element._uid]['properties']['outputs'] = {};
                        } else if(_.get(element.__data, 'webhook_message')) {
                            // Webhook nodes have specific outputs for success and failure
                            data.operators[element._uid]['properties']['outputs'] = {
                                'success': {
                                    label: '{{ __tr("Success") }}'
                                },
                                'delivery_failed': {
                                    label: '{{ __tr("Failed") }}'
                                }
                            };
                        } else if(_.get(element.__data, 'custom_field_message')) {
                            // Custom field nodes have standard WhatsApp bot flow outputs
                            data.operators[element._uid]['properties']['outputs'] = {
                                'success': {
                                    label: '{{ __tr("Continue") }}'
                                },
                                'no_input': {
                                    label: '{{ __tr("No Input") }}'
                                },
                                'no_match': {
                                    label: '{{ __tr("No Match") }}'
                                },
                                'delivery_failed': {
                                    label: '{{ __tr("Delivery Failed") }}'
                                }
                            };
                        } else if(_.get(element.__data, 'whatsapp_template_message')) {
                            // WhatsApp template nodes have standard WhatsApp bot flow outputs
                            data.operators[element._uid]['properties']['outputs'] = {
                                'template_selected': {
                                    label: '{{ __tr("Template Selected") }}'
                                },
                                'no_input': {
                                    label: '{{ __tr("No Input") }}'
                                },
                                'no_match': {
                                    label: '{{ __tr("No Match") }}'
                                },
                                'delivery_failed': {
                                    label: '{{ __tr("Delivery Failed") }}'
                                }
                            };
                        } else {
                            // For simple bot replies, add a default output to allow connections
                            data.operators[element._uid]['properties']['outputs']['simple_output'] = {
                                label: '{{ __tr("Continue") }}'
                            };
                        };

                        nodeCounter++;
                    };
                };
                if(window.$flowBuilderInstance) {
                    window.$flowBuilderInstance.flowchart('setData', data);
                };
            }
        }));
    });

    // Auto-connection functions for goto nodes
    window.onGotoTargetNodeSelected = function(response, element) {
        if (response && response.success && element && element.val()) {
            const targetNodeId = element.val();

            // Auto-save the form to create the goto node first
            setTimeout(function() {
                const form = element.closest('form');
                if (form && form.length) {
                    // Trigger form submission to create the goto node
                    $(form).trigger('submit');
                }
            }, 100);
        }
    };

    window.onEditGotoTargetNodeSelected = function(response, element) {
        if (response && response.success && element && element.val()) {
            const targetNodeId = element.val();

            // Auto-save the form to update the goto node
            setTimeout(function() {
                const form = element.closest('form');
                if (form && form.length) {
                    // Trigger form submission to update the goto node
                    $(form).trigger('submit');
                }
            }, 100);
        }
    };

    // Function to auto-connect goto nodes after creation/update
    window.autoConnectGotoNode = function(gotoNodeId, targetNodeId) {
        if (gotoNodeId && targetNodeId && window.$flowBuilderInstance) {
            setTimeout(function() {
                const flowData = window.$flowBuilderInstance.flowchart('getData');

                // Remove any existing links from this goto node
                const existingLinks = flowData.links || [];
                const filteredLinks = existingLinks.filter(link => link.fromOperator !== gotoNodeId);

                // Add new link to target node (internal connection, not visible)
                // This is for data consistency, but won't show visually since goto nodes have no outputs
                filteredLinks.push({
                    fromOperator: gotoNodeId,
                    fromConnector: 'goto_internal',
                    toOperator: targetNodeId,
                    toConnector: 'input'
                });

                flowData.links = filteredLinks;
                window.$flowBuilderInstance.flowchart('setData', flowData);
                window.__isUnsavedContent = true;

                // Auto-save the flow data
                if (window.saveFlowChartData) {
                    window.saveFlowChartData();
                }
            }, 500);
        }
    };

    // Handle bot reply creation success with auto-connection for goto nodes
    window.handleBotReplyCreateSuccess = function(response, requestData, $form) {
        // Call the default success callback first
        if (window.appFuncs && window.appFuncs.modelSuccessCallback) {
            window.appFuncs.modelSuccessCallback(response, requestData, $form);
        }

        // Handle auto-connection for goto nodes
        if (response && response.data && response.data.autoConnectGoto) {
            const autoConnect = response.data.autoConnectGoto;
            if (autoConnect.gotoNodeId && autoConnect.targetNodeId) {
                window.autoConnectGotoNode(autoConnect.gotoNodeId, autoConnect.targetNodeId);
            }
        }
    };

    // Handle bot reply edit success with auto-connection for goto nodes
    window.handleBotReplyEditSuccess = function(response, requestData, $form) {
        // Handle auto-connection for goto nodes first
        if (response && response.data && response.data.autoConnectGoto) {
            const autoConnect = response.data.autoConnectGoto;
            if (autoConnect.gotoNodeId && autoConnect.targetNodeId) {
                window.autoConnectGotoNode(autoConnect.gotoNodeId, autoConnect.targetNodeId);
            }
        }

        // Call the default success callback (which may reload the page)
        if (window.appFuncs && window.appFuncs.modelSuccessCallback) {
            window.appFuncs.modelSuccessCallback(response, requestData, $form);
        }
    };
})();
</script>
@endsection