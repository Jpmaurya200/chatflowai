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

<style>
    /* Floating Action Bar Styles - Hidden since buttons are now in title */
    .lw-node-action-bar {
        position: absolute;
        z-index: 1000;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        border: 1px solid #e5e7eb;
        padding: 8px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        display: none !important; /* Force hide since buttons are now in title */
    }

    .lw-node-action-bar.show {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        display: block !important;
    }

    .lw-action-bar-content {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .lw-action-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        color: #374151;
        background: #f8fafc;
        min-width: auto;
    }

    .lw-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: #374151;
    }

    .lw-action-btn i {
        font-size: 12px;
    }

    .lw-action-btn-edit:hover {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .lw-action-btn-delete:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .lw-action-btn-copy:hover {
        background: #f0f9ff;
        color: #0369a1;
    }

    /* Arrow pointing to selected node */
    .lw-node-action-bar::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 8px solid #ffffff;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .lw-action-btn span {
            display: none;
        }

        .lw-action-btn {
            padding: 8px;
            min-width: 36px;
            justify-content: center;
        }
    }

    /* Enhanced Node Styling to Match New Design */
    .flowchart-operator {
        border-radius: 16px !important;
        border: 2px solid #50c878 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        overflow: visible !important; /* Changed from hidden to visible to show full content */
        background: #ffffff !important;
        min-width: 280px !important; /* Ensure minimum width for content */
        max-width: 400px !important; /* Set reasonable maximum width */
    }

    .flowchart-operator-title {
        background: linear-gradient(135deg, #50c878 0%, #25a14e 100%) !important;
        color: #ffffff !important;
        padding: 12px 16px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        border-radius: 14px 14px 0 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 8px !important;
        position: relative !important;
        padding-right: 100px !important; /* Make space for action buttons */
    }

    /* Node title content wrapper */
    .flowchart-operator-title-content {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex: 1 !important;
    }

    /* Node action buttons in title */
    .flowchart-operator-title-actions {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        opacity: 1 !important;
        transition: opacity 0.2s ease !important;
        position: absolute !important;
        top: 6px !important;
        right: 6px !important;
    }

    .lw-title-action-btn {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 30px !important;
        height: 30px !important;
        border: none !important;
        border-radius: 5px !important;
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        font-size: 15px !important;
    }

    .lw-title-action-btn:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        transform: scale(1.1) !important;
        color: #ffffff !important;
    }

    .lw-title-action-btn-edit:hover {
        background: rgba(59, 130, 246, 0.8) !important;
    }

    .lw-title-action-btn-delete:hover {
        background: rgba(220, 38, 38, 0.8) !important;
    }

    .lw-title-action-btn-copy:hover {
        background: rgba(3, 105, 161, 0.8) !important;
    }

    .flowchart-operator-title::before {
        /* content: '💬'; */
        font-size: 16px;
    }

    .flowchart-operator.condition .flowchart-operator-title::before {
        content: '❓';
    }

    .flowchart-operator.goto .flowchart-operator-title::before {
        content: '➡️';
    }

    .flowchart-operator.stay_in_session .flowchart-operator-title::before {
        content: '♾️';
    }

    .flowchart-operator.start .flowchart-operator-title::before {
        content: '🚀';
    }

    .flowchart-operator.start .flowchart-operator-title {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }

    .flowchart-operator-body {
        background: #ffffff !important;
        padding: 0 !important;
        border-radius: 0 0 14px 14px !important;
        width: 100% !important; /* Ensure full width usage */
        box-sizing: border-box !important; /* Include padding in width calculation */
        overflow: visible !important; /* Allow content to be fully visible */
        min-height: auto !important; /* Allow height to adjust to content */
    }

    /* Node Content Sections */
    .lw-node-content {
        padding: 0 !important;
        width: 100% !important; /* Ensure full width usage */
        box-sizing: border-box !important; /* Include padding in width calculation */
    }

    .lw-node-section {
        margin: 0 !important;
        padding: 16px !important;
        width: 100% !important; /* Ensure full width usage */
        box-sizing: border-box !important; /* Include padding in width calculation */
        overflow: visible !important; /* Allow content to be fully visible */
    }

    .lw-node-section:first-child {
        background: #f8fafc !important;
        border-bottom: 1px solid #e5e7eb;
    }

    .lw-node-section-label {
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #6b7280 !important;
        margin-bottom: 8px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }

    .lw-node-message {
        font-size: 14px !important;
        color: #374151 !important;
        line-height: 1.5 !important;
        margin: 0 !important;
        word-wrap: break-word !important; /* Ensure long words wrap */
        white-space: normal !important; /* Allow text to wrap to multiple lines */
        overflow-wrap: break-word !important; /* Modern property for word wrapping */
        max-width: 100% !important; /* Ensure text doesn't overflow container */
        display: block !important; /* Ensure proper block display */
    }

    /* Button/Option Styling */
    .lw-node-options {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        margin: 0 !important;
    }

    .lw-node-option {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 12px 16px !important;
        background: #dbeafe !important;
        border: 1px solid #3b82f6 !important;
        border-radius: 8px !important;
        transition: all 0.2s ease !important;
    }

    .lw-node-option:hover {
        background: #bfdbfe !important;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2) !important;
    }

    .lw-node-option-text {
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #1d4ed8 !important;
        margin: 0 !important;
    }

    .lw-node-option-indicator {
        width: 8px !important;
        height: 8px !important;
        background: #3b82f6 !important;
        border-radius: 50% !important;
        flex-shrink: 0 !important;
    }

    /* Special styling for goto nodes */
    .lw-node-redirect-info {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        color: #6b7280 !important;
        font-size: 13px !important;
        font-style: italic !important;
    }

    .lw-node-redirect-info i {
        color: #50c878 !important;
    }

    /* Enhanced hover and selection effects */
    .flowchart-operator:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .flowchart-operator.selected {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2), 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    }

    /* Improved connector styling */
    .flowchart-operator-connector-arrow {
        border-left-color: #d1d5db !important;
    }

    .flowchart-operator-connector:hover .flowchart-operator-connector-arrow {
        border-left-color: #3b82f6 !important;
    }

    /* Override library rotation for output connectors - force 180 degree rotation */
    .flowchart-container .flowchart-operator .flowchart-operator-outputs .flowchart-operator-connector-arrow {
        /* transform: rotate(180deg) !important; Force rotate output arrows 180 degrees */
        transform-origin: center !important; /* Ensure rotation happens from center */
    }

    .flowchart-container .flowchart-operator .flowchart-operator-outputs .flowchart-operator-connector:hover .flowchart-operator-connector-arrow {
        /* transform: rotate(180deg) !important; Maintain rotation on hover for outputs */
        transform-origin: center !important; /* Ensure rotation happens from center */
    }

    /* Ensure input connectors are NOT rotated */
    .flowchart-container .flowchart-operator .flowchart-operator-inputs .flowchart-operator-connector-arrow {
        transform: none !important; /* Remove any rotation from input arrows */
    }

    .flowchart-container .flowchart-operator .flowchart-operator-inputs .flowchart-operator-connector:hover .flowchart-operator-connector-arrow {
        transform: none !important; /* Remove any rotation from input arrows on hover */
    }

    /* Better spacing for node sections */
    .lw-node-section + .lw-node-section {
        border-top: 1px solid #e5e7eb !important;
    }

    /* Modern Flowchart Link Styling - Professional Dotted Lines */
    .flowchart-link {
        stroke: #50c878 !important;
        stroke-width: 3px !important;
        stroke-dasharray: 8,4 !important;
        stroke-linecap: round !important;
        fill: none !important;
        /* transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important; */
        filter: drop-shadow(0 1px 3px rgba(80, 200, 120, 0.15)) !important;
    }

    .flowchart-link:hover {
        stroke: #22c55e !important;
        stroke-width: 3.5px !important;
        stroke-dasharray: 9,4 !important;
        filter: drop-shadow(0 2px 6px rgba(34, 197, 94, 0.25)) !important;
    }

    /* Active/Selected link styling */
    .flowchart-link.selected,
    .flowchart-link.active {
        stroke: #16a34a !important;
        stroke-width: 4px !important;
        stroke-dasharray: 10,4 !important;
        filter: drop-shadow(0 3px 8px rgba(22, 163, 74, 0.35)) !important;
    }

    /* Link arrow markers */
    .flowchart-link-marker {
        fill: #50c878 !important;
        transition: fill 0.3s ease !important;
    }

    .flowchart-link:hover .flowchart-link-marker {
        fill: #22c55e !important;
    }

    /* Animated flow effect for active links */
    @keyframes flowPulse {
        0%, 100% {
            stroke-opacity: 1;
            stroke-dasharray: 10,4;
        }
        50% {
            stroke-opacity: 0.7;
            stroke-dasharray: 12,4;
        }
    }

    .flowchart-link.flow-active {
        /* animation: flowPulse 2s ease-in-out infinite !important; */
        stroke: #50c878 !important;
        stroke-width: 4px !important;
        stroke-linecap: round !important;
    }

    /* Ensure SVG paths use dotted lines */
    .flowchart-links-layer path,
    .flowchart-links-layer line {
        stroke: #50c878 !important;
        stroke-width: 3px !important;
        stroke-dasharray: 8,4 !important;
        stroke-linecap: round !important;
        fill: none !important;
        /* transition: all 0.3s ease !important; */
    }

    .flowchart-links-layer path:hover,
    .flowchart-links-layer line:hover {
        stroke: #22c55e !important;
        stroke-width: 3.5px !important;
        stroke-dasharray: 9,4 !important;
    }

    /* Override any solid line styles from the flowchart library */
    .flowchart-container svg path,
    .flowchart-container svg line {
        stroke-dasharray: 8,4 !important;
        stroke-linecap: round !important;
    }

    /* Specific targeting for flowchart link elements */
    svg .flowchart-link,
    svg path.flowchart-link,
    svg line.flowchart-link {
        stroke: #50c878 !important;
        stroke-width: 3px !important;
        stroke-dasharray: 8,4 !important;
        stroke-linecap: round !important;
        fill: none !important;
    }

    /* Delete button styling for flowchart links */
    .flowchart-link-delete-btn {
        opacity: 1 !important;
        transition: opacity 0.2s ease !important;
        cursor: pointer !important;
        z-index: 1000 !important;
    }

    /* .flowchart-link:hover .flowchart-link-delete-btn {
        opacity: 1 !important;
    } */

    .flowchart-link-delete-btn circle {
        fill: #ef4444 !important;
        stroke: #ffffff !important;
        stroke-width: 2 !important;
        transition: all 0.2s ease !important;
    }

    .flowchart-link-delete-btn:hover circle {
        fill: #dc2626 !important;
        transform: scale(1.1) !important;
    }

    .flowchart-link-delete-btn line {
        stroke: #ffffff !important;
        stroke-width: 2 !important;
        stroke-linecap: round !important;
    }

    /* Zoom Controls Styling */
    .lw-zoom-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1001;
        display: flex;
        flex-direction: column;
        gap: 8px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        border: 1px solid #e5e7eb;
        padding: 8px;
    }

    .lw-zoom-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 8px;
        background: #f8fafc;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 16px;
        font-weight: 600;
    }

    .lw-zoom-btn:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .lw-zoom-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .lw-zoom-btn i {
        font-size: 14px;
    }

    .lw-zoom-level {
        font-size: 11px;
        color: #6b7280;
        text-align: center;
        padding: 4px 8px;
        background: #f3f4f6;
        border-radius: 6px;
        font-weight: 500;
        min-width: 50px;
    }

    /* Dotted Grid Background for Flow Builder */
    .lw-flow-builder-container {
        background-color: #ffffff !important;
        background-image:
            radial-gradient(circle, #d1d5db 1px, transparent 1px) !important;
        background-size: 20px 20px !important;
        background-position: 0 0, 10px 10px !important;
        min-height: 600px !important;
        position: relative !important;
    }

    /* Ensure the flowchart container has the dotted background */
    #lwBotFlowBuilder {
        background-color: #ffffff !important;
        background-image:
            radial-gradient(circle, #d1d5db 1px, transparent 1px) !important;
        background-size: 20px 20px !important;
        background-position: 0 0 !important;
        min-height: 600px !important;
    }

    /* Alternative dotted pattern using CSS dots */
    .flowchart-container {
        background-color: #ffffff !important;
        background-image:
            radial-gradient(circle, #d1d5db 1.5px, transparent 1.5px) !important;
        background-size: 20px 20px !important;
        background-position: 0 0 !important;
    }

    /* Ensure the SVG container also has the grid background */
    .flowchart-container svg {
        background-color: transparent !important;
    }

    /* Additional styles to ensure full question text display */
    .flowchart-operator .lw-node-message {
        text-overflow: unset !important; /* Remove any text truncation */
        overflow: visible !important; /* Ensure text is fully visible */
        white-space: normal !important; /* Allow text wrapping */
        height: auto !important; /* Allow height to adjust to content */
        max-height: none !important; /* Remove any height restrictions */
    }

    /* Ensure flowchart library doesn't impose size restrictions */
    .flowchart-operator-body .lw-node-content {
        min-height: auto !important;
        height: auto !important;
        max-height: none !important;
    }

    /* Override any flowchart library text truncation */
    .flowchart-operator * {
        text-overflow: unset !important;
        overflow: visible !important;
    }

    /* Wait node styling */
    .wait-node {
        border-color: #FFA500 !important; /* Orange border for wait nodes */
    }
    .wait-node .flowchart-operator-title {
        background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%) !important;
    }
    .wait-node .lw-node-section {
        border-left: 3px solid #FFA500;
        margin-bottom: 8px;
        padding-left: 8px;
    }
    .wait-node .lw-node-section-label {
        color: #FFA500;
        font-weight: 500;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .wait-node .lw-node-message {
        color: #333;
        font-size: 13px;
        line-height: 1.4;
    }

    /* Team assignment node styling */
    .team-assignment-node {
        border-color: #8B5CF6 !important; /* Purple border for team assignment nodes */
    }
    .team-assignment-node .flowchart-operator-title {
        background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%) !important;
    }
    .team-assignment-node .lw-node-section {
        border-left: 3px solid #8B5CF6;
        margin-bottom: 8px;
        padding-left: 8px;
    }
    .team-assignment-node .lw-node-section-label {
        color: #8B5CF6;
        font-weight: 500;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .team-assignment-node .lw-node-message {
        color: #333;
        font-size: 13px;
        line-height: 1.4;
    }

    /* Custom field node styling */
    .custom-field-node {
        border-color: #17a2b8 !important; /* Teal border for custom field nodes */
    }
    .custom-field-node .flowchart-operator-title {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    }
    .custom-field-node .lw-node-section {
        border-left: 3px solid #17a2b8;
        margin-bottom: 8px;
        padding-left: 8px;
    }
    .custom-field-node .lw-node-section-label {
        color: #17a2b8;
        font-weight: 500;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .custom-field-node .lw-node-message {
        color: #333;
        font-size: 13px;
        line-height: 1.4;
    }

    /* Stay in Session Node Styling */
    .stay-in-session-node {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: 2px solid #5a67d8 !important;
        color: white !important;
    }
    .stay-in-session-node .lw-node-section-label {
        color: #e2e8f0 !important;
        font-weight: 500;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .stay-in-session-node .lw-node-message {
        color: white !important;
        font-size: 13px;
        line-height: 1.4;
    }
    .stay-in-session-node .lw-node-redirect-info {
        color: #cbd5e0 !important;
    }
    .stay-in-session-node .lw-node-redirect-info i {
        color: #ffd700 !important;
    }
</style>
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
                                <!-- <button type="button" @click="isAdvanceBot = 'wait'" class="dropdown-item btn"
                                    data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Wait Node') }}</button> -->
                                <button type="button" @click="isAdvanceBot = 'team_assignment'" class="dropdown-item btn"
                                    data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Team Assignment Node') }}</button>
                                <button type="button" @click="isAdvanceBot = 'webhook'" class="dropdown-item btn"
                                    data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Webhook Node') }}</button>
                                <!-- <button type="button" @click="isAdvanceBot = 'custom_field'" class="dropdown-item btn"
                                    data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Custom Field Node') }}</button> -->
                                <button type="button" @click="isAdvanceBot = 'stay_in_session'" class="dropdown-item btn"
                                    data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('Stay in Session Node') }}</button>
                                <!-- <button type="button" @click="isAdvanceBot = 'whatsapp_template'" class="dropdown-item btn"
                                    data-toggle="modal" data-target="#lwAddNewAdvanceBotReply"> {{ __tr('WhatsApp Template Node') }}</button> -->
                                </div>
                            </div>
                        </template>
                        <button class="btn btn-warning" @click="saveData"><i class="fa fa-save"></i> {{  __tr('Save') }}</button>
                    </div>
                </div>
                {{-- added just to initialize --}}
                <div class="pl-1 m-1 lw-flow-builder-container-holder" dir="ltr" style="position: relative;">
                    <template x-text="processedFlowBots"></template>
                    <div class="lw-flow-builder-container p-4 card-body" id="lwBotFlowBuilder"></div>

                    {{-- Zoom Controls --}}
                    <div id="lwZoomControls" class="lw-zoom-controls">
                        <button id="lwZoomInBtn" class="lw-zoom-btn" title="{{ __tr('Zoom In') }}">
                            <i class="fa fa-plus"></i>
                        </button>
                        <div id="lwZoomLevel" class="lw-zoom-level">100%</div>
                        <button id="lwZoomOutBtn" class="lw-zoom-btn" title="{{ __tr('Zoom Out') }}">
                            <i class="fa fa-minus"></i>
                        </button>
                        <button id="lwZoomResetBtn" class="lw-zoom-btn" title="{{ __tr('Reset Zoom') }}">
                            <i class="fa fa-expand-arrows-alt"></i>
                        </button>
                        <button id="lwZoomFitBtn" class="lw-zoom-btn" title="{{ __tr('Fit to Screen') }}">
                            <i class="fa fa-compress-arrows-alt"></i>
                        </button>
                    </div>

                    {{-- Floating Action Bar for Node Actions - Hidden since buttons are now in title --}}
                    <!-- <div id="lwNodeActionBar" class="lw-node-action-bar" style="display: none !important;">
                        <div class="lw-action-bar-content">
                            <button id="lwEditNodeBtn" class="lw-action-btn lw-action-btn-edit" title="{{ __tr('Edit') }}">
                                <i class="fa fa-edit"></i>
                                <span>{{ __tr('Edit') }}</span>
                            </button>
                            <button id="lwDeleteNodeBtn" class="lw-action-btn lw-action-btn-delete" title="{{ __tr('Delete') }}">
                                <i class="fa fa-trash"></i>
                                <span>{{ __tr('Delete') }}</span>
                            </button>
                            <button id="lwCopyNodeBtn" class="lw-action-btn lw-action-btn-copy" title="{{ __tr('Duplicate') }}">
                                <i class="fa fa-copy"></i>
                                <span>{{ __tr('Duplicate') }}</span>
                            </button>
                        </div>
                    </div> -->
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
                console.log('Node selected:', elementUid);
                window.showNodeActionBar(elementUid);
                return true;
            },
            onOperatorUnselect : function() {
                console.log('Node unselected');
                window.hideNodeActionBar();
                return true;
            },
            onLinkCreate : function(linkId, linkData) {
                data.links[linkId] = linkData;
                if(window.isFlowChatInitialized) {
                    window.updateDraft();
                };
                // Apply dotted line styles to newly created links
                setTimeout(function() {
                    window.applyDottedLinesToFlowchart();
                }, 50);
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
         // Panzoom initialization...
        /*
        @link https://github.com/timmywil/panzoom/tree/v3.2.2
        */
        window.$panzoomInstance = window.$flowBuilderInstance.panzoom({
            contain: 'automatic',
            cursor: "grab",
            increment: 0.1,
            minScale: 0.1,
            maxScale: 3,
            startTransform: 'scale(1)',
            $zoomIn: $('#lwZoomInBtn'),
            $zoomOut: $('#lwZoomOutBtn'),
            $reset: $('#lwZoomResetBtn')
        });

        // Initialize zoom level display
        window.currentZoomLevel = 1;
        window.updateZoomDisplay = function(scale) {
            window.currentZoomLevel = scale || 1;
            $('#lwZoomLevel').text(Math.round(window.currentZoomLevel * 100) + '%');
        };

        // Zoom control event handlers
        $('#lwZoomInBtn').on('click', function() {
            window.$flowBuilderInstance.panzoom('zoom');
            const matrix = window.$flowBuilderInstance.panzoom('getMatrix');
            window.updateZoomDisplay(matrix[0]);
        });

        $('#lwZoomOutBtn').on('click', function() {
            window.$flowBuilderInstance.panzoom('zoom', true);
            const matrix = window.$flowBuilderInstance.panzoom('getMatrix');
            window.updateZoomDisplay(matrix[0]);
        });

        $('#lwZoomResetBtn').on('click', function() {
            window.$flowBuilderInstance.panzoom('reset');
            window.updateZoomDisplay(1);
        });

        $('#lwZoomFitBtn').on('click', function() {
            // Calculate fit-to-screen zoom
            const container = $('#lwBotFlowBuilder');
            const containerWidth = container.width();
            const containerHeight = container.height();

            // Get flowchart content bounds
            const operators = $('.flowchart-operator');
            if (operators.length > 0) {
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

                operators.each(function() {
                    const $op = $(this);
                    const pos = $op.position();
                    const width = $op.outerWidth();
                    const height = $op.outerHeight();

                    minX = Math.min(minX, pos.left);
                    minY = Math.min(minY, pos.top);
                    maxX = Math.max(maxX, pos.left + width);
                    maxY = Math.max(maxY, pos.top + height);
                });

                const contentWidth = maxX - minX + 100; // Add padding
                const contentHeight = maxY - minY + 100; // Add padding

                const scaleX = containerWidth / contentWidth;
                const scaleY = containerHeight / contentHeight;
                const scale = Math.min(scaleX, scaleY, 1); // Don't zoom in beyond 100%

                window.$flowBuilderInstance.panzoom('zoom', scale, {
                    animate: true,
                    focal: {
                        clientX: containerWidth / 2,
                        clientY: containerHeight / 2
                    }
                });

                window.updateZoomDisplay(scale);
            }
        });

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

    // Node Action Bar Functions
    window.showNodeActionBar = function(nodeId) {
        console.log('showNodeActionBar called with nodeId:', nodeId);
        const $actionBar = $('#lwNodeActionBar');

        // Try to find the node
        let $node = null;
        $('.flowchart-operator').each(function() {
            const $this = $(this);
            if ($this.data('operator_id') === nodeId) {
                $node = $this;
                console.log('Found matching node:', $this);
                return false;
            }
        });

        console.log('Node found:', $node);

        if ($node && $node.length > 0) {
            // Position the action bar above the selected node
            try {
                const nodeOffset = $node.offset();
                const nodeWidth = $node.outerWidth();
                const containerOffset = $('#lwBotFlowBuilder').offset();

                if (nodeOffset && containerOffset) {
                    const actionBarLeft = (nodeOffset.left - containerOffset.left) + (nodeWidth / 2);
                    const actionBarTop = (nodeOffset.top - containerOffset.top) - 50;

                    console.log('Positioning action bar at:', actionBarLeft, actionBarTop);

                    $actionBar.css({
                        left: actionBarLeft + 'px',
                        top: actionBarTop + 'px',
                        position: 'absolute',
                        display: 'block'
                    }).addClass('show');
                }
            } catch (e) {
                console.log('Error positioning action bar:', e);
                // Fallback positioning
                $actionBar.css({
                    left: '200px',
                    top: '100px',
                    position: 'absolute',
                    display: 'block'
                }).addClass('show');
            }

            // Extract action URLs from node content (for both old and new formats)
            let editUrl = null, deleteUrl = null, duplicateUrl = null;

            // Try to find hidden action data first (new format)
            const nodeActions = $node.find('.lw-node-actions');
            if (nodeActions.length) {
                editUrl = nodeActions.data('edit-url');
                deleteUrl = nodeActions.data('delete-url');
                duplicateUrl = nodeActions.data('duplicate-url');
                console.log('Found new format action data');
            } else {
                // Fallback: extract from old button format
                const nodeBody = $node.find('.flowchart-operator-body');
                if (nodeBody.length) {
                    const bodyHtml = nodeBody.html();

                    // Extract edit URL
                    const editMatch = bodyHtml.match(/href="([^"]*get-update-data[^"]*)"/);
                    if (editMatch) editUrl = editMatch[1];

                    // Extract delete URL
                    const deleteMatch = bodyHtml.match(/href="([^"]*delete-process[^"]*)"/);
                    if (deleteMatch) deleteUrl = deleteMatch[1];

                    // Extract duplicate URL
                    const duplicateMatch = bodyHtml.match(/href="([^"]*duplicate-process[^"]*)"/);
                    if (duplicateMatch) duplicateUrl = duplicateMatch[1];

                    console.log('Extracted URLs from old format:', {editUrl, deleteUrl, duplicateUrl});
                }
            }

            window.currentNodeData = {
                nodeId: nodeId,
                editUrl: editUrl,
                deleteUrl: deleteUrl,
                duplicateUrl: duplicateUrl
            };
            console.log('Current node data:', window.currentNodeData);
        } else {
            console.log('No node found with ID:', nodeId);
            // Still show the action bar for testing
            $actionBar.css({
                left: '200px',
                top: '200px',
                display: 'block',
                position: 'absolute',
                'z-index': '9999'
            }).addClass('show');
            window.currentNodeData = {
                nodeId: nodeId,
                editUrl: null,
                deleteUrl: null,
                duplicateUrl: null
            };
        }
    };

    window.hideNodeActionBar = function() {
        console.log('Hiding node action bar');
        $('#lwNodeActionBar').removeClass('show').hide();
        window.currentNodeData = null;
    };

    // Hide action bar when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.flowchart-operator, .lw-node-action-bar').length) {
            window.hideNodeActionBar();
        }
    });
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
        // Ensure dotted lines are applied to all flowchart links
        window.applyDottedLinesToFlowchart();
    });

    // Function to apply dotted line styles to flowchart connections
    window.applyDottedLinesToFlowchart = function() {
        setTimeout(function() {
            // Apply styles to all SVG paths and lines in the flowchart
            $('#lwBotFlowBuilder svg path, #lwBotFlowBuilder svg line').each(function() {
                $(this).css({
                    'stroke': '#50c878',
                    'stroke-width': '3px',
                    'stroke-dasharray': '8,4',
                    'stroke-linecap': 'round',
                    'fill': 'none'
                });
            });

            // Also apply to elements with flowchart-link class
            $('#lwBotFlowBuilder .flowchart-link').each(function() {
                $(this).css({
                    'stroke': '#50c878',
                    'stroke-width': '3px',
                    'stroke-dasharray': '8,4',
                    'stroke-linecap': 'round',
                    'fill': 'none'
                });
            });
        }, 100);
    };

    // Function to apply node-specific CSS classes
    window.applyNodeClasses = function() {
        setTimeout(function() {
            $('.flowchart-operator').each(function() {
                const $node = $(this);
                const nodeType = $node.find('.flowchart-operator-title').text();

                // Remove existing node type classes
                $node.removeClass('wait-node team-assignment-node custom-field-node stay-in-session-node');

                // Apply classes based on node content or data attributes
                if ($node.find('.lw-node-message:contains("seconds")').length > 0 ||
                    $node.find('.lw-node-section-label:contains("Wait Time")').length > 0) {
                    $node.addClass('wait-node');
                } else if ($node.find('.lw-node-section-label:contains("Assignment Message")').length > 0 ||
                          $node.find('.lw-node-redirect-info:contains("team member")').length > 0) {
                    $node.addClass('team-assignment-node');
                } else if ($node.find('.lw-node-section-label:contains("Custom Field")').length > 0 ||
                          $node.find('.lw-node-redirect-info:contains("Awaits User Input")').length > 0) {
                    $node.addClass('custom-field-node');
                } else if ($node.find('.lw-node-section-label:contains("Session Status")').length > 0 ||
                          $node.find('.lw-node-redirect-info:contains("Keeps Session Active")').length > 0) {
                    $node.addClass('stay-in-session-node');
                }
            });
        }, 150);
    };

    // Test function to show action bar manually
    window.testActionBar = function() {
        console.log('Testing action bar...');
        const $actionBar = $('#lwNodeActionBar');
        console.log('Action bar element:', $actionBar);
        console.log('Action bar length:', $actionBar.length);

        if ($actionBar.length > 0) {
            $actionBar.css({
                left: '200px',
                top: '200px',
                display: 'block',
                position: 'absolute',
                'z-index': '9999'
            }).addClass('show');
            console.log('Action bar should be visible now');

            // Set test data
            window.currentNodeData = {
                nodeId: 'test',
                editUrl: 'test-edit-url',
                deleteUrl: 'test-delete-url',
                duplicateUrl: 'test-duplicate-url'
            };
        } else {
            console.log('Action bar element not found!');
        }
    };

    // Title Action Button Event Handlers (using event delegation)
    $(document).on('click', '.lw-title-action-btn-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const editUrl = $(this).data('edit-url');
        console.log('Title Edit button clicked, URL:', editUrl);

        if (editUrl) {
            if (window.__isUnsavedContent) {
                window.unsavedAlert();
            } else {
                // Trigger edit action
                const editLink = $('<a></a>')
                    .attr('href', editUrl)
                    .attr('data-toggle', 'modal')
                    .attr('data-target', '#lwEditBotReply')
                    .attr('data-response-template', '#lwEditBotReplyBody')
                    .attr('data-pre-callback', 'appFuncs.clearContainer')
                    .addClass('lw-ajax-link-action');

                $('body').append(editLink);
                editLink.trigger('click');
                editLink.remove();
            }
        }
    });

    $(document).on('click', '.lw-title-action-btn-delete', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const deleteUrl = $(this).data('delete-url');
        console.log('Title Delete button clicked, URL:', deleteUrl);

        if (deleteUrl) {
            if (window.__isUnsavedContent) {
                window.unsavedAlert();
            } else {
                // Trigger delete action
                const deleteLink = $('<a></a>')
                    .attr('href', deleteUrl)
                    .attr('data-method', 'post')
                    .attr('data-confirm', '#lwDeleteBotReply-template')
                    .attr('data-callback', 'onBotReplyDeleted')
                    .addClass('lw-ajax-link-action');

                $('body').append(deleteLink);
                deleteLink.trigger('click');
                deleteLink.remove();
            }
        }
    });

    $(document).on('click', '.lw-title-action-btn-copy', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const duplicateUrl = $(this).data('duplicate-url');
        console.log('Title Duplicate button clicked, URL:', duplicateUrl);

        if (duplicateUrl) {
            if (window.__isUnsavedContent) {
                window.unsavedAlert();
            } else {
                // Trigger duplicate action
                const duplicateLink = $('<a></a>')
                    .attr('href', duplicateUrl)
                    .attr('data-method', 'post')
                    .attr('data-confirm', '#lwDuplicateBotReply-template')
                    .addClass('lw-ajax-link-action');

                $('body').append(duplicateLink);
                duplicateLink.trigger('click');
                duplicateLink.remove();
            }
        }
    });

    // Legacy Action Bar Button Event Handlers (keeping for backward compatibility)
    $('#lwEditNodeBtn').on('click', function() {
        console.log('Edit button clicked');
        console.log('Current node data:', window.currentNodeData);

        if (window.currentNodeData && window.currentNodeData.editUrl) {
            if (window.__isUnsavedContent) {
                window.unsavedAlert();
            } else {
                // Trigger edit action
                const editLink = $('<a></a>')
                    .attr('href', window.currentNodeData.editUrl)
                    .attr('data-toggle', 'modal')
                    .attr('data-target', '#lwEditBotReply')
                    .attr('data-response-template', '#lwEditBotReplyBody')
                    .attr('data-pre-callback', 'appFuncs.clearContainer')
                    .addClass('lw-ajax-link-action');

                $('body').append(editLink);
                editLink.trigger('click');
                editLink.remove();
            }
        } else {
            console.log('Edit not available - no node selected or no edit URL');
        }
    });

    $('#lwDeleteNodeBtn').on('click', function() {
        if (window.currentNodeData && window.currentNodeData.deleteUrl) {
            if (window.__isUnsavedContent) {
                window.unsavedAlert();
            } else {
                // Trigger delete action
                const deleteLink = $('<a></a>')
                    .attr('href', window.currentNodeData.deleteUrl)
                    .attr('data-method', 'post')
                    .attr('data-confirm', '#lwDeleteBotReply-template')
                    .attr('data-callback', 'onBotReplyDeleted')
                    .addClass('lw-ajax-link-action');

                $('body').append(deleteLink);
                deleteLink.trigger('click');
                deleteLink.remove();
            }
        }
    });

    $('#lwCopyNodeBtn').on('click', function() {
        if (window.currentNodeData && window.currentNodeData.duplicateUrl) {
            if (window.__isUnsavedContent) {
                window.unsavedAlert();
            } else {
                // Trigger duplicate action
                const duplicateLink = $('<a></a>')
                    .attr('href', window.currentNodeData.duplicateUrl)
                    .attr('data-method', 'post')
                    .attr('data-confirm', '#lwDuplicateBotReply-template')
                    .addClass('lw-ajax-link-action');

                $('body').append(duplicateLink);
                duplicateLink.trigger('click');
                duplicateLink.remove();
            }
        }
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
                                body: '<div class="lw-node-content">' +
                                    '<div class="lw-node-section">' +
                                        '<div class="lw-node-section-label">Entry Point</div>' +
                                        '<div class="lw-node-message">Entry point for the chatbot conversation.</div>' +
                                    '</div>' +
                                    '<div class="lw-node-section">' +
                                        '<div class="lw-node-section-label">Trigger Type</div>' +
                                        '<div class="lw-node-message">{{ ucfirst(str_replace('_', ' ', $botFlow->trigger_type ?? 'is')) }}</div>' +
                                    '</div>' +
                                    @if($botFlow->trigger_type !== 'welcome' && $botFlow->start_trigger)
                                    '<div class="lw-node-section">' +
                                        '<div class="lw-node-section-label">Trigger Text</div>' +
                                        '<div class="lw-node-message">{{ $botFlow->start_trigger }}</div>' +
                                    '</div>' +
                                    @endif
                                '</div>',
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

                        // Enhanced wait node detection
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
                            // Fallback detection for wait nodes that might not have the data structure yet
                            nodeType = 'wait';
                            console.log('Wait node detected by name fallback:', element.name);
                        }

                        // Debug logging for all nodes to understand the data structure
                        console.log('Processing node:', {
                            name: element.name,
                            nodeType: nodeType,
                            hasWaitMessage: !!_.get(element.__data, 'wait_message'),
                            hasQuestionMessage: !!_.get(element.__data, 'question_message'),
                            hasGotoMessage: !!_.get(element.__data, 'goto_message'),
                            elementData: element.__data,
                            reply: element.reply
                        });

                        // Generate structured node content based on type and data
                        let nodeContent = '';

                        if (nodeType === 'wait') {
                            // Wait node
                            const waitData = element.__data.wait_message || {};
                            const waitTime = waitData.wait_delay_seconds || waitData.delay_seconds || 5;
                            const waitText = waitData.wait_message || element.reply || 'Waiting...';

                            console.log('Rendering wait node:', {
                                elementName: element.name,
                                waitTime: waitTime,
                                waitText: waitText,
                                waitData: waitData
                            });

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Wait Time</div>' +
                                    '<div class="lw-node-message">' + waitTime + ' seconds</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Message</div>' +
                                    '<div class="lw-node-message">' + waitText + '</div>' +
                                '</div>' +
                            '</div>';
                        } else if (nodeType === 'condition' && element.__data?.question_message) {
                            // Question/Condition node
                            const questionData = element.__data.question_message;
                            const questionText = element.reply || 'Question message';
                            const conditionalFlows = questionData.conditional_flows || [];
                            const storeInField = questionData.store_in_field || '';
                            let fieldType = '';
                            if (window.contactCustomFields && storeInField) {
                                const found = window.contactCustomFields.find(f => f.input_name === storeInField);
                                if (found) {
                                    fieldType = found.input_type ? found.input_type.charAt(0).toUpperCase() + found.input_type.slice(1) : '';
                                }
                            }
                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Message</div>' +
                                    '<div class="lw-node-message">' + questionText + '</div>' +
                                '</div>';
                            if (storeInField) {
                                nodeContent += '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Mapped Field</div>' +
                                    '<div class="lw-node-message">' + storeInField + (fieldType ? ' (' + fieldType + ')' : '') + '</div>' +
                                '</div>';
                            }
                            if (conditionalFlows.length > 0) {
                                nodeContent += '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Buttons</div>' +
                                    '<div class="lw-node-options">';
                                conditionalFlows.forEach(flow => {
                                    nodeContent += '<div class="lw-node-option">' +
                                        '<span class="lw-node-option-text">' + (flow.label || 'Option') + '</span>' +
                                        '<span class="lw-node-option-indicator"></span>' +
                                    '</div>';
                                });
                                nodeContent += '</div></div>';
                            }
                            nodeContent += '</div>';
                        } else if (element.__data?.interaction_message) {
                            // Interactive message node
                            const interactionData = element.__data.interaction_message;
                            const messageText = element.reply || 'Interactive message';
                            const buttons = interactionData.buttons || {};
                            const buttonList = Object.values(buttons).filter(btn => btn && btn.trim());

                            console.log('Creating interactive node:', {
                                messageText,
                                buttonList,
                                element
                            });

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Message</div>' +
                                    '<div class="lw-node-message">' + messageText + '</div>' +
                                '</div>';

                            if (buttonList.length > 0) {
                                nodeContent += '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Buttons</div>' +
                                    '<div class="lw-node-options">';

                                buttonList.forEach(button => {
                                    nodeContent += '<div class="lw-node-option">' +
                                        '<span class="lw-node-option-text">' + button + '</span>' +
                                        '<span class="lw-node-option-indicator"></span>' +
                                    '</div>';
                                });

                                nodeContent += '</div></div>';
                            }

                            nodeContent += '</div>';
                        } else if (nodeType === 'team_assignment') {
                            // Team Assignment node
                            const teamAssignmentData = element.__data.team_assignment_message || {};
                            const assignmentMessage = teamAssignmentData.assignment_message || 'Assigning to team member...';
                            const assignedTeamMemberName = teamAssignmentData.assigned_team_member_name || 'Team Member';

                            console.log('Rendering team assignment node:', {
                                elementName: element.name,
                                assignmentMessage: assignmentMessage,
                                assignedTeamMemberName: assignedTeamMemberName,
                                teamAssignmentData: teamAssignmentData
                            });

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Assigned To</div>' +
                                    '<div class="lw-node-message">' +
                                        '<i class="fas fa-user"></i> ' + assignedTeamMemberName +
                                    '</div>' +
                                '</div>' +
                                (assignmentMessage ?
                                    '<div class="lw-node-section">' +
                                        '<div class="lw-node-section-label">Assignment Message</div>' +
                                        '<div class="lw-node-message">' + assignmentMessage + '</div>' +
                                    '</div>' : '') +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-redirect-info">' +
                                        '<i class="fas fa-hand-point-right"></i>' +
                                        '<span>Terminal Node - Ends Flow</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                        } else if (nodeType === 'webhook') {
                            // Webhook node
                            const webhookData = element.__data.webhook_message || {};
                            const webhookUrl = webhookData.webhook_url || 'Not configured';
                            const httpMethod = webhookData.http_method || 'POST';
                            const successMessage = webhookData.success_message || 'Webhook executed successfully';

                            console.log('Rendering webhook node:', {
                                elementName: element.name,
                                webhookUrl: webhookUrl,
                                httpMethod: httpMethod,
                                webhookData: webhookData
                            });

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Webhook URL</div>' +
                                    '<div class="lw-node-message">' +
                                        '<i class="fas fa-globe"></i> ' + httpMethod + ' ' + webhookUrl +
                                    '</div>' +
                                '</div>' +
                                (successMessage ?
                                    '<div class="lw-node-section">' +
                                        '<div class="lw-node-section-label">Success Message</div>' +
                                        '<div class="lw-node-message">' + successMessage + '</div>' +
                                    '</div>' : '') +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-redirect-info">' +
                                        '<i class="fas fa-external-link-alt"></i>' +
                                        '<span>Calls External API</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                        } else if (nodeType === 'custom_field') {
                            // Custom Field node
                            const customFieldData = element.__data.custom_field_message || {};
                            const customFieldName = customFieldData.custom_field_name || 'Custom Field';
                            const questionText = customFieldData.question_text || 'Please provide your information:';

                            console.log('Rendering custom field node:', {
                                elementName: element.name,
                                customFieldName: customFieldName,
                                questionText: questionText,
                                customFieldData: customFieldData
                            });

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Custom Field</div>' +
                                    '<div class="lw-node-message">' +
                                        '<i class="fas fa-form"></i> ' + customFieldName +
                                    '</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Question</div>' +
                                    '<div class="lw-node-message">' + questionText + '</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-redirect-info">' +
                                        '<i class="fas fa-keyboard"></i>' +
                                        '<span>Awaits User Input</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                        } else if (nodeType === 'whatsapp_template') {
                            // WhatsApp Template node
                            const whatsappTemplateData = element.__data.whatsapp_template_message || {};
                            const templateName = whatsappTemplateData.template_name || 'WhatsApp Template';
                            const templateLanguage = whatsappTemplateData.template_language || 'en';
                            const messageText = element.reply || 'Available WhatsApp Templates:';

                            console.log('Rendering WhatsApp template node:', {
                                elementName: element.name,
                                templateName: templateName,
                                templateLanguage: templateLanguage,
                                messageText: messageText,
                                whatsappTemplateData: whatsappTemplateData
                            });

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Message</div>' +
                                    '<div class="lw-node-message">' + messageText + '</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Selected Template</div>' +
                                    '<div class="lw-node-message">' +
                                        '<i class="fas fa-envelope"></i> ' + templateName + ' (' + templateLanguage + ')' +
                                    '</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-redirect-info">' +
                                        '<i class="fas fa-list"></i>' +
                                        '<span>Shows WhatsApp Templates</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                        } else if (nodeType === 'stay_in_session') {
                            // Stay in Session node
                            const stayInSessionData = element.__data.stay_in_session_message || {};
                            const sessionMessage = stayInSessionData.session_message || element.reply || 'Session will remain active...';

                            console.log('Rendering stay in session node:', {
                                elementName: element.name,
                                sessionMessage: sessionMessage,
                                stayInSessionData: stayInSessionData
                            });

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Session Message</div>' +
                                    '<div class="lw-node-message">' + sessionMessage + '</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Session Status</div>' +
                                    '<div class="lw-node-message">' +
                                        '<i class="fas fa-infinity"></i> Active - Prevents Flow Termination' +
                                    '</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-redirect-info">' +
                                        '<i class="fas fa-hand-stop"></i>' +
                                        '<span>Keeps Session Active</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                        } else if (nodeType === 'goto') {
                            // Goto node
                            const messageText = element.reply || 'Redirect message';

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Message</div>' +
                                    '<div class="lw-node-message">' + messageText + '</div>' +
                                '</div>' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-redirect-info">' +
                                        '<i class="fas fa-arrow-right"></i>' +
                                        '<span>Redirects to another flow</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                        } else {
                            // Simple message node
                            const messageText = element.reply || 'Simple message';

                            nodeContent = '<div class="lw-node-content">' +
                                '<div class="lw-node-section">' +
                                    '<div class="lw-node-section-label">Message</div>' +
                                    '<div class="lw-node-message">' + messageText + '</div>' +
                                '</div>' +
                            '</div>';
                        }

                        // Store action data for floating action bar (hidden from view)
                        const actionData = {
                            editUrl: __Utils.apiURL("{{ route('vendor.bot_reply.read.update.data', [ 'botReplyIdOrUid']) }}", {'botReplyIdOrUid': element._uid}),
                            deleteUrl: __Utils.apiURL("{{ route('vendor.bot_reply.write.delete', [ 'botReplyIdOrUid']) }}", {'botReplyIdOrUid': element._uid}),
                            duplicateUrl: __Utils.apiURL("{{ route('vendor.bot_reply.write.duplicate', [ 'botReplyIdOrUid']) }}", {'botReplyIdOrUid': element._uid}),
                            nodeId: element._uid
                        };

                        // Hidden elements to preserve functionality (not visible in UI)
                        const hiddenButtons = `<div style="display:none;" class="lw-node-actions"
                            data-edit-url="`+actionData.editUrl+`"
                            data-delete-url="`+actionData.deleteUrl+`"
                            data-duplicate-url="`+actionData.duplicateUrl+`"
                            data-node-id="`+element._uid+`">
                            <button style="display:none;" class="lw-delete-link-btn lw-operator-link-`+element._uid+` btn btn-warning btn-block btn-sm" @click="window.$flowBuilderInstance.flowchart('deleteSelected');"><i class="fas fa-unlink"></i> {{  __tr('Delete Link') }}</button>
                        </div>`;

                        // Create title with action buttons
                        const nodeNumber = nodeCounter++;
                        const titleWithActions = `<div class="flowchart-operator-title-content">
                            <span class="node-number">${nodeNumber}</span>
                            <span>${element.name}</span>
                        </div>
                        <div class="flowchart-operator-title-actions">
                            <button class="lw-title-action-btn lw-title-action-btn-edit"
                                    data-edit-url="${actionData.editUrl}"
                                    data-node-id="${element._uid}"
                                    title="{{ __tr('Edit') }}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="lw-title-action-btn lw-title-action-btn-delete"
                                    data-delete-url="${actionData.deleteUrl}"
                                    data-node-id="${element._uid}"
                                    title="{{ __tr('Delete') }}">
                                <i class="fa fa-trash"></i>
                            </button>
                            <button class="lw-title-action-btn lw-title-action-btn-copy"
                                    data-duplicate-url="${actionData.duplicateUrl}"
                                    data-node-id="${element._uid}"
                                    title="{{ __tr('Duplicate') }}">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>`;

                        data.operators[element._uid] = {
                            top: _.get(data.operators[element._uid],'top', _.random(150, 200)),
                            left: _.get(data.operators[element._uid],'left', _.random(20, 100)),
                            properties: {
                                title: titleWithActions,
                                type: nodeType,
                                body: nodeContent + hiddenButtons,
                                inputs: {
                                    input: {
                                        label: "-->"
                                    },
                                },
                                outputs: {}
                            }
                        };
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
                                // Build a valid sections array for list_data
                                let sectionsArr = [];
                                for (const interactiveListSectionIndex in element.__data.interaction_message.list_data.sections) {
                                    if (Object.hasOwnProperty.call(element.__data.interaction_message.list_data.sections, interactiveListSectionIndex)) {
                                        const sectionElement = element.__data.interaction_message.list_data.sections[interactiveListSectionIndex];
                                        // Save section title in properties
                                        data.operators[element._uid]['properties']['section_title_' + interactiveListSectionIndex] = sectionElement.title || '';
                                        let rowsArr = [];
                                        if(_.get(sectionElement, 'rows')) {
                                            for (const rowIndex in sectionElement.rows) {
                                                if (Object.hasOwnProperty.call(sectionElement.rows, rowIndex)) {
                                                    const rowElement = sectionElement.rows[rowIndex];
                                                    data.operators[element._uid]['properties']['outputs']['sections___' + interactiveListSectionIndex + '___rows___' + rowIndex + '___title'] = {
                                                        label: rowElement['title']
                                                    };
                                                    // Save row description and title in properties
                                                    data.operators[element._uid]['properties']['row_description_' + interactiveListSectionIndex + '_' + rowIndex] = rowElement.description || '';
                                                    data.operators[element._uid]['properties']['row_title_' + interactiveListSectionIndex + '_' + rowIndex] = rowElement.title || '';
                                                    rowsArr.push({
                                                        row_id: rowIndex,
                                                        title: rowElement.title || '',
                                                        description: rowElement.description || ''
                                                    });
                                                };
                                            };
                                        };
                                        sectionsArr.push({
                                            title: sectionElement.title || '',
                                            rows: rowsArr
                                        });
                                    };
                                };
                                // Save the sections array in properties for backend compatibility
                                data.operators[element._uid]['properties']['list_data.sections'] = sectionsArr;
                            };
                        } else if(_.get(element.__data, 'goto_message')) {
                            // For goto nodes, no output connector needed - they auto-redirect
                            // Remove any existing outputs to prevent manual connections
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
                        } else {
                            // For simple bot replies, add a default output to allow connections
                            data.operators[element._uid]['properties']['outputs']['simple_output'] = {
                                label: '{{ __tr("Continue") }}'
                            };
                        };

                        // Team assignment nodes have NO outputs - they are terminal nodes
                        if (_.get(element.__data, 'team_assignment_message')) {
                            // Clear any existing outputs for team assignment nodes
                            data.operators[element._uid]['properties']['outputs'] = {};
                        }

                        // Stay in session nodes have NO outputs - they prevent flow termination
                        if (_.get(element.__data, 'stay_in_session_message')) {
                            // Clear any existing outputs for stay in session nodes
                            data.operators[element._uid]['properties']['outputs'] = {};
                        }

                        // Webhook nodes have specific outputs for success and failure
                        if (_.get(element.__data, 'webhook_message')) {
                            data.operators[element._uid]['properties']['outputs'] = {
                                'success': {
                                    label: '{{ __tr("Success") }}'
                                },
                                'delivery_failed': {
                                    label: '{{ __tr("Failed") }}'
                                }
                            };
                        }

                        // Custom field nodes have standard WhatsApp bot flow outputs
                        if (_.get(element.__data, 'custom_field_message')) {
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
                        }

                        // WhatsApp template nodes have standard WhatsApp bot flow outputs
                        if (_.get(element.__data, 'whatsapp_template_message')) {
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
                        }

                        // Add standard WhatsApp bot flow outputs for all nodes (except goto nodes, wait nodes, team assignment nodes, webhook nodes, custom field nodes, and whatsapp template nodes)
                        if (_.get(element.__data, 'interaction_message')) {
                            data.operators[element._uid]['properties']['outputs']['no_input'] = {
                                label: '{{ __tr("No Input") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['no_match'] = {
                                label: '{{ __tr("No Match") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['delivery_failed'] = {
                                label: '{{ __tr("Delivery Failed") }}'
                            };
                        } else if (_.get(element.__data, 'question_message')) {
                            data.operators[element._uid]['properties']['outputs']['no_input'] = {
                                label: '{{ __tr("No Input") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['no_match'] = {
                                label: '{{ __tr("No Match") }}'
                            };
                            data.operators[element._uid]['properties']['outputs']['delivery_failed'] = {
                                label: '{{ __tr("Delivery Failed") }}'
                            };
                        }
                    };
                };
                if(window.$flowBuilderInstance) {
                    window.$flowBuilderInstance.flowchart('setData', data);
                    // Apply dotted lines and node classes after setting data
                    setTimeout(function() {
                        window.applyDottedLinesToFlowchart();
                        window.applyNodeClasses();
                    }, 100);
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