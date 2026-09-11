@extends('layouts.app', ['title' => __tr('WhatsApp Chat')])
@section('content')
@include('users.partials.header', [
// 'title' => __tr('WhatsApp Chat'),
'description' => '',
// 'class' => 'col-lg-7'
])
@push('head')
{!! __yesset('dist/css/whatsapp-chat.css', true) !!}
<style>
    /* Full-width Shared Team Inbox modern layout */
    .card.lw-whatsapp-chat-block-container {
        border-radius: 16px !important;
        border: 1px solid #E2E8F0 !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
        background: #FFFFFF !important;
        overflow: hidden !important;
    }

    /* Kill 2015 phone mockup constraints so it renders as standard full desktop web app */
    .marvel-device.nexus5 {
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .marvel-device.nexus5:before, .marvel-device.nexus5:after {
        display: none !important;
    }
    .marvel-device .screen {
        width: 100% !important;
        height: 100% !important;
        border-radius: 0 !important;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .chat-container {
        height: 80vh !important;
        min-height: 600px !important;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .screen-container {
        height: 100% !important;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .chat {
        height: 100% !important;
        display: flex;
        flex-direction: column;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .user-bar {
        background: #FFFFFF !important;
        color: #0F172A !important;
        border-bottom: 1px solid #E2E8F0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        min-height: 68px !important;
        padding: 12px 20px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .user-bar .avatar {
        width: 42px !important;
        height: 42px !important;
        border-radius: 50% !important;
        font-weight: 700;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .user-bar .name {
        color: #0F172A !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        margin-left: 12px !important;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .user-bar .name a {
        color: #10B981 !important;
        font-weight: 500;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .conversation {
        height: calc(80vh - 145px) !important;
        border-radius: 0 !important;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .conversation .conversation-container {
        height: 100% !important;
        box-shadow: none !important;
    }
    .card.lw-whatsapp-chat-block-container .lw-whatsapp-chat-window .conversation-compose {
        background: #FFFFFF !important;
        border-top: 1px solid #E2E8F0 !important;
        padding: 8px 16px !important;
        height: auto !important;
        min-height: 64px !important;
        align-items: center;
    }

    /* 24-Hour Session Pill */
    .lw-session-status-badge {
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .lw-session-status-badge.active {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }
    .lw-session-status-badge.expired {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }

    /* Canned Replies UI */
    .lw-canned-dropdown {
        position: absolute;
        bottom: 70px;
        left: 20px;
        width: 360px;
        max-height: 250px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12);
        overflow-y: auto;
        z-index: 100;
    }
    .lw-canned-item-btn {
        display: block;
        width: 100%;
        text-align: left;
        padding: 9px 14px;
        border: none;
        background: transparent;
        border-bottom: 1px solid #F1F5F9;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .lw-canned-item-btn:hover {
        background: #ECFDF5;
    }
    .lw-canned-code {
        font-weight: 700;
        color: #059669;
        background: #D1FAE5;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11.5px;
        margin-right: 6px;
    }

    /* Composer Tabs */
    .lw-composer-tabs {
        display: flex;
        gap: 6px;
        padding: 6px 16px 0;
        background: #FFFFFF;
        border-top: 1px solid #E2E8F0;
    }
    .lw-composer-tab {
        padding: 4px 12px;
        border-radius: 6px 6px 0 0;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        background: #F1F5F9;
        color: #64748B;
        transition: all 0.15s ease;
    }
    .lw-composer-tab.active {
        background: #FFFFFF;
        color: #10B981;
        border-color: #E2E8F0;
        border-bottom-color: #FFFFFF;
    }
    .lw-composer-tab.note.active {
        background: #FEF9C3;
        color: #854D0E;
        border-color: #FDE047;
    }
    .lw-note-composer-box {
        background: #FFFBEB !important;
        border: 1px dashed #F59E0B !important;
        border-radius: 8px !important;
    }

    .lw-contact-list {
        max-height: 72vh;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        position: relative;
    }

    .lw-order-message {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        margin: 8px 0;
    }

    .lw-order-message .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .lw-order-message .order-details {
        font-size: 0.9em;
    }

    .lw-order-message .order-details ul {
        list-style: none;
        padding-left: 15px;
        margin: 5px 0;
    }

    .lw-order-message .order-actions {
        margin-top: 10px;
        text-align: right;
    }

    .badge-pending, .badge-awaiting_address, .badge-awaiting_payment { background: #ffc107; color: #000; }
    .badge-paid, .badge-confirmed, .badge-delivered { background: #28a745; color: #fff; }
    .badge-shipped { background: #007bff; color: #fff; }
    .badge-cancelled { background: #dc3545; color: #fff; }
    
    .lw-contact-list-loading {
        text-align: center;
        padding: 10px;
        font-size: 12px;
        color: #666;
    }
</style>
@endpush
<div x-data="initialMessageData">
{{-- @if ($contact) --}}
<div class="container-fluid" x-data="{myAssignedUnreadMessagesCount:null,myUnassignedUnreadMessagesCount:null,showUnreadContactsOnly:false}">
    <div class="">
        <div class="card lw-whatsapp-chat-block-container">
            @if (!getVendorSettings('current_phone_number_number'))
            <div class="card-header">
            <div class="text-danger">
                {{  __tr('Phone number does not configured yet.') }}
            </div>
            </div>
            @endif
            <div id="lwWhatsAppChatWindow"
                class="card-body lw-whatsapp-chat-window p-sm-4" x-init="$watch('messagePaginatePage', function(value) {window.messagePaginatePage = value;});$watch('contactsPaginatePage', function(value) {window.contactsPaginatePage = value; });" :data-paginate-page="messagePaginatePage" :data-unread-only="showUnreadContactsOnly" :data-search-value="search" :data-contact-uid="contact?._uid">
                <div class="row" x-cloak x-data="{isContactListOpened:false,isContactCrmBlockOpened:false}">
                    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 mb-4 lw-contact-list-block" x-show="isContactListOpened">
                        <h1>{{  __tr('WhatsApp Chat') }}</h1>
                        <hr class="my-2">
                        <h2 class="lw-contacts-header"> <span class="btn btn-light btn-sm float-right d-md-none" @click.prevent="isContactListOpened = false"><i class="fa fa-arrow-left"></i></span>  <abbr class="float-right" title="{{  __tr('Once you get the response by the contact, they will be come in the chat list of this chat window, alternatively you can click on chat button of the contact list to chat with the contact.') }}">?</abbr></h2>
                        <div class="form-group m-0"><label for="lwShowUnreadOnlyContacts"><input data-lw-plugin="lwSwitchery" data-color="orange" data-size="small" x-model="showUnreadContactsOnly" x-init="$watch('showUnreadContactsOnly', function(value) {
                            window.showUnreadContactsOnly = value;
                            _.defer(function() {
                                window.searchContacts();
                            });
                        })" class="custom-checkbox" id="lwShowUnreadOnlyContacts" type="checkbox" name="unread_only_contacts" id=""> <span x-show="!showUnreadContactsOnly">{{  __tr('Show all') }}</span><span x-show="showUnreadContactsOnly">{{  __tr('Show unread only') }}</span></label>
                        
                    </div>
                    <div>
                        <a href="{{ route('vendor.chat_message.export' 
             ) }}" data-method="post" class="btn btn-dark btn-sm mt-3"><i class="fa fa-download"></i> {{  __tr('Report') }}</a>
                    </div>
<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        @if (isVendorAdmin(getVendorId()) or !hasVendorAccess('assigned_chats_only'))
            <a class="nav-link {{ ($assigned ?? null) ? '' : 'active' }}" href="{{ route('vendor.chat_message.contact.view') }}" id="lw-all-contacts-tab" data-target="#lwAllContactsTab" type="button" role="tab" aria-controls="lwAllContactsTab" aria-selected="true">{{  __tr('All') }} <span x-cloak x-show="unreadContactsCount()" class="badge bg-yellow text-dark badge-white rounded-pill ml-2" x-text="unreadContactsCount()"></span></a>
        @endif
        <a href="{{ route('vendor.chat_message.contact.view', ['assigned' => 'to-me']) }}" class="nav-link {{ (($assigned ?? null) == 'to-me') ? 'active' : '' }}" id="lw-to-me-tab" data-target="#lwAssignedToMeTab" type="button" role="tab" aria-controls="lwAssignedToMeTab" aria-selected="false">{{  __tr('Mine') }} <span x-cloak x-show="myAssignedUnreadContactsCount()" class="badge bg-yellow text-dark badge-white rounded-pill ml-2" x-text="myAssignedUnreadContactsCount()"></span></a>
        
        @if (isVendorAdmin(getVendorId()) or !hasVendorAccess('assigned_chats_only'))
            <a href="{{ route('vendor.chat_message.contact.view', ['assigned' => 'unassigned']) }}" class="nav-link {{ ($assigned ?? null) == 'unassigned' ? 'active' : '' }}" id="lw-unassigned-tab" data-target="#lwUnassignedTab" type="button" role="tab" aria-controls="lwUnassignedTab" aria-selected="false">{{  __tr('Unassigned') }} <span x-cloak x-show="myUnassignedUnreadContactsCount()" class="badge bg-yellow text-dark badge-white rounded-pill ml-2" x-text="myUnassignedUnreadContactsCount()"></span></a>
        @endif
        
        <!-- Label Filter Dropdown -->
        <div class="nav-item dropdown ml-auto">
            <a class="nav-link dropdown-toggle" href="#" id="labelFilterDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-tags"></i> {{ __tr('Filter by Label') }}
                <span x-show="selectedLabelTitle" class="badge bg-info-subtle rounded-pill ml-1" x-text="selectedLabelTitle"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="labelFilterDropdown">
                <div class="px-3 py-2">
                    <input type="text" x-model="labelSearch" @input="filterLabels()" class="form-control form-control-sm mb-2" placeholder="{{ __tr('Search labels...') }}">
                </div>
                <div class="dropdown-divider"></div>
                <div style="max-height: 200px; overflow-y: auto;">
                    <a class="dropdown-item" href="#" @click.prevent="selectedLabel = ''; filterByLabel('');" :class="{'active': !selectedLabel}">
                        {{ __tr('All Labels') }}
                    </a>
                    <template x-for="label in filteredLabels" :key="label._id">
                        <a class="dropdown-item d-flex justify-content-between align-items-center" 
                           href="#" 
                           @click.prevent="selectedLabel = label._id; selectedLabelTitle = label.title; filterByLabel(label._id);"
                           :class="{'active': selectedLabel === label._id}">
                            <span x-text="label.title"></span>
                            <span class="badge badge-pill ml-2" :style="'background-color: ' + label.color + '; color: ' + getContrastColor(label.color)" x-text="label.contacts_count"></span>
                        </a>
                    </template>
                    <div x-show="filteredLabels.length === 0" class="px-3 py-2 text-muted small">
                        {{ __tr('No labels found') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
                          <div class="tab-content" id="nav-tabContent" x-cloak>
                            <div class="tab-pane fade show active" id="lwAllContactsTab" role="tabpanel" aria-labelledby="lw-all-contacts-tab">
                            <div class="form-group">
                                <input x-model="search" x-on:keyup.debounce.500ms="function(value) {
                                    window.searchValue = this.search;
                                    window.searchContacts();
                                }" x-ref="searchField" placeholder="{{ __tr('type to search') }}" type="search" class="form-control">
                            </div>
                            <div class="list-group lw-contact-list shadow-lg list-group-flush" >
                            <template x-for="contactItem in filteredContacts">
                                @if (($assigned ?? null))
                                {{-- <template x-if="contactItem.assigned_users__id == '{{ getUserId() }}'"> --}}
                                @endif
                                <a x-show="((contact && contact._uid == contactItem._uid) || (showUnreadContactsOnly && contactItem.unread_messages_count) || !showUnreadContactsOnly) && (!selectedLabel || (contactItem.labels && contactItem.labels.some(label => label._id === selectedLabel)))" 
                                   @click.prevent="isContactListOpened = false; contact = {}; whatsappMessageLogs = []; messagePaginatePage = 0; contact.__data = {}" 
                                   :data-messaged-at="contactItem.last_message?.messaged_at" 
                                   :data-has-labels="contactItem.labels && contactItem.labels.length > 0"
                                   :data-labels="JSON.stringify(contactItem.labels || [])"
                                   :class="[(contact && (contact._uid == contactItem._uid)) ? 'list-group-item-light' : '']"
                                   :href="__Utils.apiURL('{{ route('vendor.chat_message.contact.view', ['contactUid', 'assigned' => ($assigned ?? '')]) }}',{'contactUid': contactItem._uid})"
                                   class="list-group-item list-group-item-action lw-contact lw-ajax-link-action" 
                                   data-callback="updateContactInfo">
                                    {{-- d-flex align-items-start --}}
                                    <div class="ms-2 me-auto w-100 mt-1">
                                        <div class="float-left">
                                                <div class="lw-contact-avatar bg-success text-white text-center align-content-center">
                                                    <span x-text="contactItem.name_initials"></span>
                                                </div>
                                        </div>
                                        <div class="mt-2">
                                            <h3>
                                                <span x-show="contactItem.full_name" x-text="contactItem.full_name"></span>
                                                <span x-show="contactItem.full_name"> - </span>
                                                <span x-text="contactItem.wa_id"></span>
                                            </h3>
                                            <div class="mb--2 lw-contact-labels" x-init="contactItem.label_string = ''; contactLabel = {}">
                                                <template x-for="contactLabel in contactItem.labels">
                                                    <span x-init="contactItem.label_string = contactItem.label_string + ' ' + contactLabel.title" 
                                                          x-bind:style="'color:'+contactLabel.text_color+';background-color:'+contactLabel.bg_color+';'" 
                                                          class="badge mr-1 label-badge" 
                                                          x-text="contactLabel.title"
                                                          :data-label-id="contactLabel._id">
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-right w-100 mt-3">
                                        <small class="text-muted lw-last-message-at"
                                            x-text="contactItem.last_message?.formatted_message_ago_time"></small>
                                        <span x-show="contactItem.unread_messages_count"
                                            class="badge bg-success rounded-pill"
                                            x-text="contactItem.unread_messages_count"></span>
                                    </div>
                                </a>
                                @if (($assigned ?? null))
                                {{-- </template> --}}
                                @endif
                            </template>
                        </div>
                    </div>
                    </div>
                    </div>
                    <div class="page col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-4" :class="(!contact) ? 'lw-disabled-block-content' : ''" class="chat-container" x-cloak>
                        {{-- <h2>{{ __tr('Chat') }}</h2> --}}
                        <div class="marvel-device nexus5">
                            <div class="screen">
                                <div class="screen-container">
                                    <div class="chat" id="lwChatWindowBox">
                                        {{-- <template x-if="contact"> --}}
                                            <div>
                                                <template x-if="contact">
                                                <div class="user-bar d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center" style="gap: 12px; min-width: 0;">
                                                        <div class="back d-md-none" @click.prevent="isContactListOpened = true">
                                                            <i class="fa fa-users text-dark"></i>
                                                        </div>
                                                        <div class="avatar d-none d-md-inline text-white text-center align-content-center">
                                                            <span x-text="contact.name_initials"></span>
                                                        </div>
                                                        <div class="name" style="width: auto; overflow: visible;">
                                                            <div class="d-flex align-items-center" style="gap: 8px;">
                                                                <span class="font-weight-bold text-dark" x-text="contact.full_name"></span>
                                                                <small class="text-muted"><a target="_blank" class="text-success font-weight-600" x-bind:href="'https://api.whatsapp.com/send?phone=' + contact.wa_id"><i class="fab fa-whatsapp"></i> <span x-text="contact.wa_id"></span></a></small>
                                                            </div>
                                                            <div class="mt-1">
                                                                <template x-if="isDirectMessageDeliveryWindowOpened">
                                                                    <span class="lw-session-status-badge active" :title="'Direct session window open until: ' + directMessageDeliveryWindowOpenedTillMessage">
                                                                        <i class="fas fa-circle text-success" style="font-size: 8px;"></i>
                                                                        <span>24h Session Active</span>
                                                                        <small class="text-muted ml-1" x-text="'(' + directMessageDeliveryWindowOpenedTillMessage + ')'"></small>
                                                                    </span>
                                                                </template>
                                                                <template x-if="!isDirectMessageDeliveryWindowOpened">
                                                                    <span class="lw-session-status-badge expired" title="{{ __tr("As you may not received any response in last 24 hours, your direct message may not get delivered. However you can send template messages.") }}">
                                                                        <i class="fas fa-history text-warning"></i>
                                                                        <span>Session Closed &bull; Template Required</span>
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <template x-if="contact">
                                                    <div class="actions d-flex align-items-center" style="gap: 8px;" x-data="{isAiChatBotEnabled:!contact.disable_ai_bot}" x-cloak>
                                                        <template x-if="!isDirectMessageDeliveryWindowOpened">
                                                            <a x-bind:href="__Utils.apiURL('{{ route('vendor.template_message.contact.view', [ 'contactIdOrUid']) }}', {'contactIdOrUid': contact._uid})" class="btn btn-sm btn-primary py-1 px-2 d-none d-sm-inline-flex align-items-center" style="font-size: 12px; gap: 4px;">
                                                                <i class="fas fa-paper-plane"></i> {{ __tr('Send Template') }}
                                                            </a>
                                                        </template>
                                                        @if(isAiBotAvailable())
                                                        <a :title="isAiChatBotEnabled ? '{{ __tr('Disable AI Bot') }}' : '{{ __tr('Enable AI Bot') }}'" x-bind:href="__Utils.apiURL('{{ route('vendor.contact.write.toggle_ai_bot', [ 'contactIdOrUid']) }}', {'contactIdOrUid': contact._uid})" :class="isAiChatBotEnabled ? 'btn-success text-white' : 'btn-outline-secondary'" class="btn btn-sm py-1 px-2 lw-ajax-link-action" data-method="post">
                                                           <i class="fa fa-robot mr-1"></i> <span class="d-none d-lg-inline" x-text="isAiChatBotEnabled ? 'AI Active' : 'AI Paused'"></span>
                                                        </a>
                                                        @endif
                                                        <div class="dropdown">
                                                            <a href="#" class="btn btn-sm btn-light border py-1 px-2" data-toggle="dropdown" aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v text-muted"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                            <a x-bind:href="__Utils.apiURL('{{ route('vendor.template_message.contact.view', [ 'contactIdOrUid']) }}', {'contactIdOrUid': contact._uid})" class="dropdown-item"><i class="fas fa-paper-plane mr-2 text-primary"></i> {{ __tr('Send Template Message') }}</a>
                                                            <a x-cloak
                                                                :class="whatsappMessageLogs.length <= 0 ? 'disabled' : ''"
                                                                data-method="post" data-confirm="#lwClearChatHistoryWarning" x-bind:href="__Utils.apiURL('{{ route('vendor.chat_message.delete.process', [ 'contactIdOrUid']) }}', {'contactIdOrUid': contact._uid})"
                                                                class="dropdown-item text-danger lw-ajax-link-action"><i class="fas fa-eraser mr-2"></i> {{ __tr('Clear Chat History') }}</a>
                                                            <script type="text/template" id="lwClearChatHistoryWarning">
                                                                <h3>{{  __tr('Are you sure you want to clear chat history for this contact?') }}</h3>
                                                                    <p class="text-warning">{{  __tr('Only chat history will be deleted permanently, it won\'t delete campaign messages.') }}</p>
                                                            </script>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-light border d-md-none" @click.prevent="isContactCrmBlockOpened = true"><i class="fa fa-user-tie"></i></button>
                                                    </div>
                                                    </template>
                                                </div>
                                                </template>
                                                <div class="conversation">
                                                    <div class="conversation-container" id="lwConversionChatContainer">
                                                            <div class="w-100" id="lwEndOfChats">&shy;</div>
                                                            <template x-for="whatsappMessageLogItem in whatsappMessageLogs">
                                                                <div class="lw-chat-message-item"
                                                                    :id="whatsappMessageLogItem._uid">
                                                                    <template
                                                                        x-if="whatsappMessageLogItem.is_incoming_message">
                                                                        <div class="message received">
                                                                            <template
                                                                                x-if="whatsappMessageLogItem.replied_to_whatsapp_message_logs__uid">
                                                                                <a href="#"
                                                                                    @click.prevent="lwScrollTo('#'+whatsappMessageLogItem.replied_to_whatsapp_message_logs__uid)"
                                                                                    class="badge d-flex text-muted justify-content-end"><i
                                                                                        class="fa fa-link"></i> {{
                                                                                    __tr('Replied to') }}</a>
                                                                            </template>
                                                            <template
                                                                x-if="whatsappMessageLogItem.template_message">
                                                                <div class="lw-template-message"
                                                                    x-show="whatsappMessageLogItem.template_message"
                                                                    x-html="whatsappMessageLogItem.template_message">
                                                                </div>
                                                            </template>
                                                            <template x-if="whatsappMessageLogItem.__data?.order_data">
                                                                <div class="lw-order-message">
                                                                    <div class="order-header">
                                                                        <strong>🛍️ Order #<span x-text="whatsappMessageLogItem.__data.order_data.order_id"></span></strong>
                                                                        <span class="badge" x-bind:class="'badge-' + getStatusColor(whatsappMessageLogItem.__data.order_data.status)" x-text="whatsappMessageLogItem.__data.order_data.status_label"></span>
                                                                    </div>
                                                                    <div class="order-details">
                                                                        <p>💰 Total: <span x-text="whatsappMessageLogItem.__data.order_data.formatted_total_amount"></span></p>
                                                                        <p>📦 Items:</p>
                                                                        <ul>
                                                                            <template x-for="item in whatsappMessageLogItem.__data.order_data.items">
                                                                                <li>
                                                                                    <span x-text="item.name || item.product_retailer_id"></span>
                                                                                    <span>x</span>
                                                                                    <span x-text="item.quantity"></span>
                                                                                </li>
                                                                            </template>
                                                                        </ul>
                                                                        <template x-if="whatsappMessageLogItem.__data.order_data.delivery_address">
                                                                            <p>📍 Delivery Address:<br>
                                                                            <span x-text="whatsappMessageLogItem.__data.order_data.delivery_address"></span></p>
                                                                        </template>
                                                                    </div>
                                                                    <div class="order-actions">
                                                                        <a x-bind:href="'/vendor/whatsapp/orders/' + whatsappMessageLogItem.__data.order_data._uid" class="btn btn-sm btn-primary">
                                                                            View Details
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                            <div x-show="whatsappMessageLogItem.message && !whatsappMessageLogItem.__data?.interaction_message_data"><span class="lw-plain-message-text" x-html="whatsappMessageLogItem.message"></span></div>
                                                                            <template
                                                                                x-if="(whatsappMessageLogItem.whatsapp_message_error)">
                                                                                <div class="p-1 mt-2">
                                                                                    <small class="text-danger"> <i
                                                                                            class="fas fa-exclamation-circle text-danger text-shadow"></i>
                                                                                        <em
                                                                                            x-text="whatsappMessageLogItem.whatsapp_message_error"></em></small>
                                                                                </div>
                                                                            </template>
                                                                            <span class="metadata"><span class="time"
                                                                                    x-text="whatsappMessageLogItem.formatted_message_time"></span></span>
                                                                        </div>
                                                                    </template>
                                                                    <template
                                                                        x-if="!whatsappMessageLogItem.is_incoming_message">
                                                                        <div class="message sent">
                                                                            <template
                                                                                x-if="whatsappMessageLogItem.__data?.options?.bot_reply">
                                                                                <span class="badge d-flex text-muted justify-content-end"
                                                                                    :title="whatsappMessageLogItem.__data?.options?.ai_bot_reply ? '{{ __tr('AI Bot Reply') }}' : '{{ __tr('Bot Reply') }}'">
                                                                                    <template x-if="whatsappMessageLogItem.__data?.options?.ai_bot_reply">
                                                                                        <span class="mr-1 text-warning">AI</span>
                                                                                    </template>
                                                                                    <i class="fas fa-robot text-muted"></i>
                                                                                </span>
                                                                            </template>
                                                                            <template
                                                                                x-if="whatsappMessageLogItem.campaigns__id">
                                                                                <span class="badge d-flex justify-content-end" title="{{ __tr('Campaign Message') }}">
                                                                                    <i class="fas fa-bullhorn text-info"></i>
                                                                                </span>
                                                                            </template>
                                                                            <template
                                                                                x-if="whatsappMessageLogItem.template_message">
                                                                                <div class="lw-template-message"
                                                                                    x-show="whatsappMessageLogItem.template_message"
                                                                                    x-html="whatsappMessageLogItem.template_message">
                                                                                </div>
                                                                            </template>
                                                                            <template x-if="whatsappMessageLogItem.message && !whatsappMessageLogItem.__data?.interaction_message_data">
                                                                                <div class="lw-template-message" x-show="whatsappMessageLogItem.message"><span class="lw-plain-message-text" x-html="whatsappMessageLogItem.message"></span>
                                                                                </div>
                                                                            </template>
                                                                            <template
                                                                                x-if="(whatsappMessageLogItem.whatsapp_message_error)">
                                                                                <div class="p-1 mt-2">
                                                                                    <small class="text-danger"> <i
                                                                                            class="fas fa-exclamation-circle text-danger text-shadow"></i>
                                                                                        <em
                                                                                            x-text="whatsappMessageLogItem.whatsapp_message_error"></em></small>
                                                                                </div>
                                                                            </template>
                                                                            <span class="metadata">
                                                                                <span class="time"
                                                                                    x-text="whatsappMessageLogItem.formatted_message_time"></span>
                                                                                <span class="tick">
                                                                                    <template
                                                                                        x-if="whatsappMessageLogItem.status == 'read'">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                                            width="16" height="15"
                                                                                            id="msg-dblcheck-ack" x="2063"
                                                                                            y="2076">
                                                                                            <path
                                                                                                d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.88a.32.32 0 0 1-.484.032l-.358-.325a.32.32 0 0 0-.484.032l-.378.48a.418.418 0 0 0 .036.54l1.32 1.267a.32.32 0 0 0 .484-.034l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.88a.32.32 0 0 1-.484.032L1.892 7.77a.366.366 0 0 0-.516.005l-.423.433a.364.364 0 0 0 .006.514l3.255 3.185a.32.32 0 0 0 .484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"
                                                                                                fill="#4fc3f7" />
                                                                                        </svg>
                                                                                    </template>
                                                                                    <template
                                                                                        x-if="whatsappMessageLogItem.status == 'delivered'">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                                            width="16" height="15"
                                                                                            id="msg-dblcheck" x="2047"
                                                                                            y="2061">
                                                                                            <path
                                                                                                d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.88a.32.32 0 0 1-.484.032l-.358-.325a.32.32 0 0 0-.484.032l-.378.48a.418.418 0 0 0 .036.54l1.32 1.267a.32.32 0 0 0 .484-.034l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.88a.32.32 0 0 1-.484.032L1.892 7.77a.366.366 0 0 0-.516.005l-.423.433a.364.364 0 0 0 .006.514l3.255 3.185a.32.32 0 0 0 .484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"
                                                                                                fill="#92a58c" />
                                                                                        </svg>
                                                                                    </template>
                                                                                    <template
                                                                                        x-if="whatsappMessageLogItem.status == 'sent'">
                                                                                        <svg width="16" height="16"
                                                                                            viewBox="0 0 24 24" fill="none"
                                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                                            <path
                                                                                                d="M4 12.6111L8.92308 17.5L20 6.5"
                                                                                                stroke="#92a58c"
                                                                                                stroke-width="2"
                                                                                                stroke-linecap="round"
                                                                                                stroke-linejoin="round" />
                                                                                        </svg>
                                                                                    </template>
                                                                                    <template
                                                                                        x-if="whatsappMessageLogItem.status == 'failed'">
                                                                                        <i
                                                                                            class="fas fa-exclamation-circle text-danger"></i>
                                                                                    </template>
                                                                                    <template
                                                                                        x-if="(whatsappMessageLogItem.status == 'accepted')">
                                                                                        <i
                                                                                            class="far fa-clock text-muted"></i>
                                                                                    </template>
                                                                                </span>
                                                                            </span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <div class="w-100 px-4" id="lwEndOfChats">&shy; <button x-cloak x-show="messagePaginatePage" class="btn btn-sm btn-block btn-secondary" @click="loadEarlierMessages" ><i class="fa fa-download"></i> {{  __tr('Load earlier messages') }}</button></div>
                                                    </div>
                                                    <!-- Dual-Mode Composer Tabs (WhatsApp Reply vs Internal Note) -->
                                                    <div class="lw-composer-tabs d-flex align-items-center justify-content-between" x-data="{isInternalNoteMode: false}">
                                                        <div class="d-flex align-items-center" style="gap: 4px;">
                                                            <button type="button" class="lw-composer-tab" :class="{'active': !isInternalNoteMode}" @click.prevent="isInternalNoteMode = false">
                                                                <i class="fab fa-whatsapp mr-1 text-success"></i> {{ __tr('Customer Reply') }}
                                                            </button>
                                                            <button type="button" class="lw-composer-tab note" :class="{'active': isInternalNoteMode}" @click.prevent="isInternalNoteMode = true">
                                                                <i class="fas fa-lock mr-1 text-warning"></i> {{ __tr('Internal Note') }}
                                                            </button>
                                                        </div>
                                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                                            <!-- Canned quick replies picker toggle -->
                                                            <button type="button" @click="showCannedPicker = !showCannedPicker" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 11.5px; height: 26px;" title="{{ __tr('Quick Replies (Shortcut: /)') }}">
                                                                <i class="fas fa-bolt text-warning mr-1"></i> {{ __tr('Quick Reply (/ )') }}
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Canned Quick Reply Dropdown list -->
                                                    <div x-cloak x-show="showCannedPicker" @click.outside="showCannedPicker = false" class="lw-canned-dropdown shadow-lg">
                                                        <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                                                            <strong class="small text-muted text-uppercase"><i class="fas fa-bolt text-warning"></i> {{ __tr('Quick Replies') }}</strong>
                                                            <span class="badge badge-light text-muted">{{ __tr('Click to insert') }}</span>
                                                        </div>
                                                        <div style="max-height: 200px; overflow-y: auto;">
                                                            <template x-for="item in cannedReplies" :key="item.shortcut">
                                                                <button type="button" class="lw-canned-item-btn" @click="insertCannedReply(item.text)">
                                                                    <span class="lw-canned-code" x-text="'/' + item.shortcut"></span>
                                                                    <span class="font-weight-600 text-dark small" x-text="item.title"></span>
                                                                    <div class="text-muted text-truncate" style="font-size: 11.5px;" x-text="item.text"></div>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <!-- Customer WhatsApp Reply Form -->
                                                    <x-lw.form x-show="!isInternalNoteMode" data-event-stream-update="true" data-callback="appFuncs.resetForm" id="whatsAppMessengerForm"
                                                        class="conversation-compose" data-show-processing="false"
                                                        :action="route('vendor.chat_message.send.process')">
                                                        <input type="hidden" name="contact_uid" x-bind:value="contact?._uid">
                                                        <div class="emoji"></div>
                                                        <textarea name="message_body" required class="input-msg lw-input-emoji"
                                                            name="input" placeholder="{{ __tr('Type a message or press / for quick replies...') }}" autocomplete="off" autofocus></textarea>
                                                            <div class="photo dropup">
                                                                <!-- Default dropup button -->
                                                                <a href="#" class="lw-whatsapp-bar-icon-btn" data-toggle="dropdown" aria-expanded="false">
                                                                    <i class=" fa fa-paperclip text-muted"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <a title="{{ __tr('Send Document') }}"
                                                                class="lw-ajax-link-action dropdown-item" data-toggle="modal"
                                                                data-response-template="#lwWhatsappAttachment"
                                                                data-target="#lwMediaUploadAndSend"
                                                                data-callback="appFuncs.prepareUpload" href="{{ route('vendor.chat_message_media.upload.prepare', [
                                                                'mediaType' => 'document'
                                                            ]) }}"><i class="fa fa-file text-muted mr-2"></i> {{ __tr('Send Document') }}</a>
                                                            <a title="{{ __tr('Send Image') }}" class="lw-ajax-link-action dropdown-item"
                                                            data-toggle="modal"
                                                            data-response-template="#lwWhatsappAttachment"
                                                            data-target="#lwMediaUploadAndSend"
                                                            data-callback="appFuncs.prepareUpload" href="{{ route('vendor.chat_message_media.upload.prepare', [
                                                            'mediaType' => 'image'
                                                        ]) }}"><i class="fa fa-image text-muted mr-2"></i> {{ __tr('Send Image') }}</a>
                                                        <a title="{{ __tr('Send Video') }}" class="lw-ajax-link-action dropdown-item"
                                                        data-toggle="modal"
                                                        data-response-template="#lwWhatsappAttachment"
                                                        data-target="#lwMediaUploadAndSend"
                                                        data-callback="appFuncs.prepareUpload" href="{{ route('vendor.chat_message_media.upload.prepare', [
                                                        'mediaType' => 'video'
                                                    ]) }}"><i class="fa fa-video text-muted mr-2"></i> {{ __tr('Send Video') }}</a>
                                                    <a title="{{ __tr('Send Audio') }}" class="lw-ajax-link-action dropdown-item"
                                                    data-toggle="modal"
                                                    data-response-template="#lwWhatsappAttachment"
                                                    data-target="#lwMediaUploadAndSend"
                                                    data-callback="appFuncs.prepareUpload" href="{{ route('vendor.chat_message_media.upload.prepare', [
                                                    'mediaType' => 'audio'
                                                ]) }}"><i class="fa fa-headphones text-muted mr-2"></i> {{ __tr('Send Audio') }}</a>
                                                                </div>
                                                            </div>
                                                        <button class="send" type="submit" title="{{ __tr('Send Message') }}">
                                                            <div class="circle pl-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="1.5em"
                                                                    height="1.5em" viewBox="0 0 24 24">
                                                                    <path fill="currentColor"
                                                                        d="M2.01 21L23 12L2.01 3L2 10l15 2l-15 2z" />
                                                                </svg>
                                                            </div>
                                                        </button>
                                                    </x-lw.form>

                                                    <!-- Team Internal Note Form -->
                                                    <x-lw.form x-cloak x-show="isInternalNoteMode" id="lwChatInternalNotesComposerForm" :action="route('vendor.chat.update_notes.process')" class="p-2 lw-note-composer-box">
                                                        <input type="hidden" name="contactIdOrUid" :value="contact?._uid">
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <span class="small font-weight-bold text-warning"><i class="fas fa-eye-slash mr-1"></i> {{ __tr('Only visible to your team (Customer will not see this)') }}</span>
                                                        </div>
                                                        <div class="d-flex" style="gap: 8px;">
                                                            <textarea name="contact_notes" class="form-control form-control-sm border-0" rows="2" style="background: transparent; resize: none;" placeholder="{{ __tr('Add internal note about this customer or issue...') }}" x-model="tempInternalNote"></textarea>
                                                            <button type="submit" class="btn btn-warning btn-sm font-weight-bold px-3 align-self-end" @click="if(!contact['__data']) { contact['__data'] = {}} contact['__data']['contact_notes'] = (contact['__data']['contact_notes'] ? contact['__data']['contact_notes'] + '\n' : '') + tempInternalNote; isInternalNoteMode = false; tempInternalNote = '';">
                                                                {{ __tr('Save Note') }}
                                                            </button>
                                                        </div>
                                                    </x-lw.form>

                                                    {{-- error container --}}
                                                    <div data-form-id="#whatsAppMessengerForm"
                                                        class="lw-error-container-message_body p-2">
                                                    </div>
                                                </div>
                                            </div>
                                        {{-- </template> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 mb-4 lw-contact-crm-block" :class="(!contact) ? 'lw-disabled-block-content' : ''" x-show="isContactCrmBlockOpened">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <span class="btn btn-light btn-sm float-right d-md-none" @click.prevent="isContactCrmBlockOpened = false"><i class="fa fa-arrow-left"></i></span>
                                </div>
                                <template x-if="contact">
                                    <fieldset class="col-12 p-2 mt-0">
                                        <legend>{{  __tr('Contact Info') }}</legend>
                                        @if (hasVendorAccess('manage_contacts'))
                                        <div class="text-right mt--3">
                                            <a data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Edit') }}" class="lw-btn btn btn-sm btn-light lw-ajax-link-action" data-response-template="#lwEditContactBody" x-bind:href="__Utils.apiURL('{{ route('vendor.contact.read.update.data', [ 'contactIdOrUid']) }}', {'contactIdOrUid': contact._uid})"  data-toggle="modal" data-target="#lwEditContact"><i class="fa fa-user-edit"></i> {{  __tr('Edit Contact') }}</a>
                                        </div>
                                        @endif
                                        <dl class="px-2">
                                            <dt>{{  __tr('Name') }}</dt>
                                            <dd x-text="contact.full_name"></dd>
                                            <dt>{{  __tr('Phone') }}</dt>
                                            <dd x-text="contact.wa_id"></dd>
                                            <dt>{{  __tr('Email') }}</dt>
                                            <dd x-text="contact.email ? contact.email : '-'"></dd>
                                            <dt>{{  __tr('Language') }}</dt>
                                            <dd x-text="contact.language_code ? contact.language_code : '-'"></dd>
                                        </dl>
                                    </fieldset>
                                </template>
                                <div class="col-12 p-0">
                                    <x-lw.form id="lwAssignSystemUserForm" :action="route('vendor.chat.assign_user.process')" >
                                        <input type="hidden" name="contactIdOrUid" :value="contact?._uid">
                                        {{-- Select messaging permitted team member to assign this contact chat --}}
                                        <fieldset class="col-12 p-2">
                                            <legend>{{  __tr('Assign Team Member') }}</legend>
                                            <x-lw.input-field id="lwCurrentlyAssignedUserUid" type="selectize" data-form-group-class="mt--4" name="assigned_users_uid" class="custom-select"
                                    data-selected="{{ $currentlyAssignedUserUid }}" x-model="currentlyAssignedUserUid">
                                            <x-slot name="selectOptions">
                                                <option value="">{{  __tr('Not Assigned') }}</option>
                                                <option value="no_one">{{  __tr('Not Assigned') }}</option>
                                                @foreach ($vendorMessagingUsers as $vendorMessagingUser)
                                                <option value="{{ $vendorMessagingUser->_uid }}">{{ $vendorMessagingUser->first_name . ' ' . $vendorMessagingUser->last_name }} @if($vendorMessagingUser->_uid == getUserUID()) ({{  __tr('You') }}) @endif</option>
                                                @endforeach
                                            </x-slot>
                                            </x-lw.input-field>
                                            <div class="">
                                                <button type="submit" class="btn btn-dark btn-sm mt--1 float-right">{{  __tr('Save') }}</button>
                                            </div>
                                        </fieldset>
                                    </x-lw.form>
                                </div>
                                <template x-if="contact">
                                    {{-- tags and labels --}}
                                    <fieldset class="col-12 p-2">
                                        {{-- <hr class="my-4"> --}}
                                        <legend class="pb-0 pt-1">{{  __tr('Labels/Tags') }} <a data-pre-callback="appFuncs.clearContainer" title="{{  __tr('Manage Labels') }}" class="lw-btn btn btn-sm btn-link lw-ajax-link-action float-right pt-1" data-response-template="#lwManageContactLabelsBody" x-bind:href="__Utils.apiURL('{{ route('vendor.chat.contact_labels.read', [ 'contactUid']) }}', {'contactUid': contact._uid})"  data-toggle="modal" data-target="#lwManageContactLabels"><i class="fa fa-cog text-blue"></i></a></legend>
                                        <x-lw.form data-callback="onUpdateContactDetails" id="lwAssignContactLabelsForm" :action="route('vendor.chat.assign_labels.process')">
                                                <input type="hidden" name="contactUid" x-bind:value="contact._uid" />
                                                <div x-show="labelsElement"></div>
                                                <select class="border-0 lw-borderers-selectize" id="lwAssignLabelsField" data-form-group-class="" x-bind:data-selected="assignedLabelIds" name="contact_labels[]" multiple >
                                                    <option value="">{{ __tr('Select Labels') }}</option>
                                                        @foreach($allLabels as $label)
                                                            <option value="{{ $label['_id'] }}">{{ $label['title'] }}</option>
                                                        @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-dark btn-sm float-right">{{  __tr('Update') }}</button>
                                        </x-lw.form>
                                    </fieldset>
                                </template>
                                <template x-if="contact">
                                    {{-- notes --}}
                                    <fieldset class="col-12 p-2" x-data="{openNotesEdit:false,contactNotes:contact.__data?.contact_notes}">
                                        {{-- <hr class="my-4"> --}}
                                        <legend class="pb-0 pt-1" for="lwContactNotes">{{  __tr('Notes') }} <button class="btn btn-link btn-sm float-right pt-1" @click="openNotesEdit = true"><i class="fas fa-edit"></i></button></legend>
                                        <div x-show="!openNotesEdit" class="lw-ws-pre-line px-2 pb-4" x-text="contact.__data?.contact_notes"></div>
                                        <x-lw.form x-show="openNotesEdit" id="lwNotesForm" :action="route('vendor.chat.update_notes.process')" >
                                            <input type="hidden" name="contactIdOrUid" :value="contact?._uid">
                                            <div class="form-group">
                                                <textarea name="contact_notes" id="lwContactNotes" class="form-control" x-bind:value="contact.__data?.contact_notes" x-model="contactNotes" rows="5"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-dark btn-sm mt--3" @click="openNotesEdit = false; if(!contact['__data']) { contact['__data'] = {}} contact['__data']['contact_notes'] = contactNotes;">{!! __tr('Save & Close') !!}</button>
                                            </div>
                                        </x-lw.form>
                                    </fieldset>
                                </template>
                            </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
<x-lw.modal id="lwMediaUploadAndSend" :header="__tr('Send Media')" :hasForm="true"
    data-pre-callback="clearModelContainer">
    <!--  document form -->
    <x-lw.form id="lwMediaUploadAndSendForm" :action="route('vendor.chat_message_media.send.process')"
        data-callback="appFuncs.modelSuccessCallback" :data-callback-params="['modalId' => '#lwMediaUploadAndSend']">
        <!-- form body -->
        <input type="hidden" name="contact_uid" x-bind:value="contact?._uid">
        <div id="lwWhatsappAttachment" class="lw-form-modal-body"></div>
        <script type="text/template" id="lwWhatsappAttachment-template">
            <% if(__tData.mediaType == 'document') { %>
            <div class="form-group col-sm-12">
                <input id="lwDocumentMediaFilepond" type="file" data-allow-revert="true"
                    data-label-idle="{{ __tr('Select Document') }}" class="lw-file-uploader" data-instant-upload="true"
                    data-action="<?= route('media.upload_temp_media', 'whatsapp_document') ?>" id="lwDocumentField" data-file-input-element="#lwDocumentMedia" data-raw-upload-data-element="#lwRawDocumentMedia" data-allowed-media='<?= getMediaRestriction('whatsapp_document') ?>' />
                <input id="lwDocumentMedia" type="hidden" value="" name="uploaded_media_file_name" />
                <input type="hidden" value="document" name="media_type" />
            </div>
            <% } else if(__tData.mediaType == 'image') { %>
                <div class="form-group col-sm-12">
                    <input id="lwImageMediaFilepond" type="file" data-allow-revert="true"
                        data-label-idle="{{ __tr('Select Image') }}" class="lw-file-uploader" data-instant-upload="true"
                        data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>" id="lwImageField" data-file-input-element="#lwImageMedia" data-raw-upload-data-element="#lwRawDocumentMedia" data-allowed-media='<?= getMediaRestriction('whatsapp_image') ?>' />
                    <input id="lwImageMedia" type="hidden" value="" name="uploaded_media_file_name" />
                    <input type="hidden" value="image" name="media_type" />
                </div>
                <% } else if(__tData.mediaType == 'video') { %>
                    <div class="form-group col-sm-12">
                        <input id="lwVideoMediaFilepond" type="file" data-allow-revert="true"
                            data-label-idle="{{ __tr('Select Video') }}" class="lw-file-uploader" data-instant-upload="true"
                            data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>" id="lwVideoField" data-file-input-element="#lwVideoMedia" data-raw-upload-data-element="#lwRawDocumentMedia" data-allowed-media='<?= getMediaRestriction('whatsapp_video') ?>' />
                        <input id="lwVideoMedia" type="hidden" value="" name="uploaded_media_file_name" />
                        <input type="hidden" value="video" name="media_type" />
                    </div>
                <% } else if(__tData.mediaType == 'audio') { %>
                    <div class="form-group col-sm-12">
                        <input id="lwAudioMediaFilepond" type="file" data-allow-revert="true"
                            data-label-idle="{{ __tr('Select Audio') }}" class="lw-file-uploader" data-instant-upload="true"
                            data-action="<?= route('media.upload_temp_media', 'whatsapp_audio') ?>" id="lwAudioField" data-file-input-element="#lwAudioMedia" data-raw-upload-data-element="#lwRawDocumentMedia" data-allowed-media='<?= getMediaRestriction('whatsapp_audio') ?>' />
                        <input id="lwAudioMedia" type="hidden" value="" name="uploaded_media_file_name" />
                        <input type="hidden" value="audio" name="media_type" />
                    </div>
                <% } %>
                <input id="lwRawDocumentMedia" type="hidden" value="" name="raw_upload_data"/>
                <% if(__tData.mediaType != 'audio') { %>
                <div>
                    <label for="lwMediaCaptionText">{{  __tr('Caption/Text') }}</label>
                    <textarea name="caption" id="lwCaptionField" class="form-control" rows="2"></textarea>
                </div>
                <% } %>
        </script>
        <!-- form footer -->
        <div class="modal-footer">
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">{{ __('Send') }}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Cancel') }}</button>
        </div>
    </x-lw.form>
    <!--/  document form -->
</x-lw.modal>
 <!-- Edit Contact Modal -->
 @include('contact.contact-edit-modal-partial')
 <!--/ Edit Contact Modal -->
 {{-- Manage labels Modal --}}
 <x-lw.modal id="lwManageContactLabels" :header="__tr('Manage Labels')" :hasForm="true">
        <!-- form body -->
        <div id="lwManageContactLabelsBody" class="lw-form-modal-body"></div>
        <script type="text/template" id="lwManageContactLabelsBody-template">
            <fieldset class="pb-4 my-4">
                {{-- <legend>{{  __tr('New Label') }}</legend> --}}
                <x-lw.form data-callback="onNewLabelCreated" id="lwManageContactLabelsForm" :action="route('vendor.chat.label.create.write')">
                    <div class="row">
                        <x-lw.input-field type="text" id="lwLabelFieldTitle" data-form-group-class="col-12" :label="__tr('New Label')"  name="title"  required="true">
                            <x-slot name="append">
                            <input type="color" name="text_color" value="#ffffff" style="height: 50px;" title="{{ __tr('Label Text Color') }}" class="lw-color-field">
                            <input type="color" name="bg_color" value="#000000" style="height: 50px;" title="{{ __tr('Label BG Color') }}" class="lw-color-field">
                            <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                            </x-slot>
                        </x-lw.input-field>
                    </div>
                </x-lw.form>
            </fieldset>
            <fieldset>
                <legend>{{  __tr('Labels') }}</legend>
                    <ul class="list-group">
                        <template x-for="labelItem in allLabels">
                            <li x-bind:class="'lw-contact-label-'+labelItem._uid" class="list-group-item" >
                                <x-lw.form data-callback="onUpdateContactDetails" class="w-100" :action="route('vendor.chat.label.update.write')">
                                    <div class="row">
                                        <input type="hidden" name="labelUid" x-bind:value="labelItem._uid" />
                                        <x-lw.input-field type="text" data-form-group-class="col-12" :label="__tr('Edit Label')"  name="title" x-bind:value="labelItem.title" required="true">
                                            <x-slot name="append">
                                            <input type="color" name="text_color" x-bind:value="labelItem.text_color" style="height: 50px;" title="{{ __tr('Label Text Color') }}" class="lw-color-field">
                                            <input type="color" name="bg_color" x-bind:value="labelItem.bg_color" style="height: 50px;" title="{{ __tr('Label BG Color') }}" class="lw-color-field">
                                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                            <a class="btn btn-outline-danger lw-ajax-link-action" data-confirm="{{ __tr('Are you sure you want to delete this label?') }}"  data-callback="updateManageLabelsList" data-method="post" x-bind:href="__Utils.apiURL('{{ route('vendor.chat.label.delete.write', ['labelUid']) }}',{'labelUid': labelItem._uid})"><i class="fa fa-trash"></i></a>
                                            </x-slot>
                                        </x-lw.input-field>
                                    </div>
                                </x-lw.form>
                            </li>
                            </template>
                    </ul>
            </fieldset>
    </script>
        <!-- form footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
        </div>
    <!--/  Edit Contact Form -->
</x-lw.modal>
 {{-- /Manage labels Modal --}}
</div>
<script>
     (function() {
        'use strict';
     document.addEventListener('alpine:init', () => {
        Alpine.data('initialMessageData', () => ({
            // Label filtering
            selectedLabel: '',
            labelSearch: '',
            allLabels: @json($allLabels ?? []),
            filteredLabels: @json($allLabels ?? []),
            
            // Initialize filtered labels
            initLabels() {
                this.filteredLabels = [...this.allLabels];
            },
            
            // Filter labels based on search input
            filterLabels() {
                if (!this.labelSearch.trim()) {
                    this.filteredLabels = [...this.allLabels];
                    return;
                }
                const searchTerm = this.labelSearch.toLowerCase();
                this.filteredLabels = this.allLabels.filter(label => 
                    label.title.toLowerCase().includes(searchTerm)
                );
            },
            
            // Filter contacts by label
            filterByLabel(labelId = '') {
                this.selectedLabel = labelId;
                this.selectedLabelTitle = labelId ? this.allLabels.find(l => l._id === labelId)?.title || '' : '';
                
                // Update URL with the selected label
                const url = new URL(window.location.href);
                if (labelId) {
                    url.searchParams.set('label', labelId);
                } else {
                    url.searchParams.delete('label');
                }
                window.history.pushState({}, '', url);
                
                // Apply the filter
                window.searchContacts();
            },
            
            // Helper function to get contrast color for label text
            getContrastColor(hexColor) {
                // If the color is not valid, return black
                if (!hexColor) return '#000000';
                
                // Convert hex to RGB
                const r = parseInt(hexColor.substr(1, 2), 16);
                const g = parseInt(hexColor.substr(3, 2), 16);
                const b = parseInt(hexColor.substr(5, 2), 16);
                
                // Calculate luminance (perceived brightness)
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                
                // Return black for light colors, white for dark colors
                return luminance > 0.5 ? '#000000' : '#ffffff';
            },
            
            // Helper function to get status color for order badges
            getStatusColor(status) {
                const statusColors = {
                    'pending': 'pending',
                    'awaiting_address': 'awaiting_address',
                    'awaiting_payment': 'awaiting_payment',
                    'paid': 'paid',
                    'confirmed': 'confirmed',
                    'shipped': 'shipped',
                    'delivered': 'delivered',
                    'cancelled': 'cancelled'
                };
                return statusColors[status] || 'pending';
            },
            // whatsappMessageLogs: @json($whatsappMessageLogs),
            whatsappMessageLogs: [],
            messagePaginatePage: 0,
            contactsPaginatePage: 0,
            isDirectMessageDeliveryWindowOpened: {{ $isDirectMessageDeliveryWindowOpened ?: 0 }},
            directMessageDeliveryWindowOpenedTillMessage: '{{ $directMessageDeliveryWindowOpenedTillMessage }}',
            contact:@json($contact),
            isContactDetailsUpdated: false,
            currentlyAssignedUserUid:'{{ $currentlyAssignedUserUid }}',
            search: "",
            contacts: {},
            assignedLabelIds: [],
            // Phase 2: Canned Quick Replies & Internal Notes
            showCannedPicker: false,
            tempInternalNote: '',
            cannedReplies: [
                { shortcut: 'welcome', title: 'Welcome Greeting', text: 'Hello! Welcome to our store. How can we help you today?' },
                { shortcut: 'support', title: 'Support Assistance', text: 'Thank you for reaching out to our support team. We are checking this for you right now.' },
                { shortcut: 'order', title: 'Order Status Query', text: 'Could you please share your Order ID so that I can look up the tracking details for you?' },
                { shortcut: 'pricing', title: 'Pricing & Plans', text: 'You can check our full pricing and available catalogue anytime. Would you like a brochure?' },
                { shortcut: 'thankyou', title: 'Thank You Message', text: 'Thank you for chatting with us! Have a wonderful day ahead!' },
                { shortcut: 'bank', title: 'Payment Details', text: 'Please complete your payment using our secure UPI/payment link or reply with your payment screenshot.' }
            ],
            insertCannedReply(text) {
                this.showCannedPicker = false;
                const emojiEditor = $('.lw-input-emoji').data('emojioneArea');
                if (emojiEditor) {
                    emojiEditor.setText(text);
                    emojiEditor.setFocus();
                } else {
                    const inputEl = document.querySelector('.lw-input-emoji');
                    if (inputEl) {
                        inputEl.value = text;
                        inputEl.focus();
                    }
                }
            },
            unreadContactsCount() {
        return Object.values(this.contacts).filter(contact => contact.unread_messages_count > 0).length;
    },
    myAssignedUnreadContactsCount() {
        return Object.values(this.contacts).filter(contact => 
            contact.unread_messages_count > 0 && 
            contact.assigned_users__id == '{{ getUserId() }}'
        ).length;
    },
    myUnassignedUnreadContactsCount() {
        return Object.values(this.contacts).filter(contact => 
            contact.unread_messages_count > 0 && 
            !contact.assigned_users__id
        ).length;
    },
            filteredContacts: function () {
                return _.reverse(_.sortBy(this.contacts, [function(o) { return o.last_message?.messaged_at; }]));
            },
            labelsElement : function() {
                // reset the selectize
               var $labelsElement =  $('#lwAssignLabelsField').selectize({
                    maxItems: null,
                    items: _.values(this.assignedLabelIds),
                    valueField: '_id',
                    labelField: 'title',
                    searchField: 'title',
                    options: this.allLabels,
                    create: false,
                    closeAfterSelect: true,
                    render: {
                        item: function (item, escape) {
                            return (
                            '<div class="" style="color:'+item.text_color+';background-color:'+item.bg_color+';" >' +
                            (item.title
                                ? '<span>' + escape(item.title) + "</span>"
                                : "") +
                            "</div>"
                            );
                        },
                        option: function (item, escape) {
                            return (
                            '<div class="p-1 rounded m-2" style="color:'+item.text_color+';background-color:'+item.bg_color+';">' +
                            '<span>' +
                            escape(item.title) +
                            "</span>" +
                            "</div>"
                            );
                        },
                    }
                });
                $labelsElement[0].selectize.clear(true);
                $labelsElement[0].selectize.setValue(['']);
                $labelsElement[0].selectize.setValue(_.values(this.assignedLabelIds));
            }
        }));
    });
})();
</script>
@push('head')
    {!! __yesset('dist/emojionearea/emojionearea.min.css', true) !!}
@endpush
@push('appScripts')
{!! __yesset('dist/emojionearea/emojionearea.min.js', true) !!}
<script>
(function($) {
    'use strict';
    window.messagePaginatePage = 1;
    window.contactsPaginatePage = 1;
    window.searchValue = '';
        // Initialize from URL parameters
        document.addEventListener('alpine:initialized', () => {
            const component = document.querySelector('[x-data]').__x.$data;
            const url = new URL(window.location.href);
            const labelId = url.searchParams.get('label');
            const searchValue = url.searchParams.get('search') || '';
            
            // Set the search value if present
            if (searchValue) {
                window.searchValue = searchValue;
                const searchInput = document.querySelector('input[type="search"]');
                if (searchInput) {
                    searchInput.value = searchValue;
                }
            }
            
            // Set the label filter if present
            if (labelId) {
                const label = component.allLabels.find(l => l._id === labelId);
                if (label) {
                    component.selectedLabel = labelId;
                    component.selectedLabelTitle = label.title;
                }
            }
            
            // Apply filters after a short delay to ensure DOM is ready
            setTimeout(() => {
                window.searchContacts();
            }, 100);
        });
    window.showUnreadContactsOnly = 0;
    window.isLoadingMoreContacts = false;
    $(document).ready(function() {
    const contactListContainer = $('.lw-contact-list');
    
    contactListContainer.on('scroll', function() {
        // Check if we're near the bottom (within 200px of bottom)
        if (this.scrollHeight - this.scrollTop - this.clientHeight < 200) {
            // Only load more if there are more pages to load
            if (window.contactsPaginatePage > 0) {
                // Prevent multiple simultaneous requests
                if (!window.isLoadingMoreContacts) {
                    window.isLoadingMoreContacts = true;
                    window.loadMoreContacts();
                    
                    // Reset the loading flag after a short delay
                    setTimeout(function() {
                        window.isLoadingMoreContacts = false;
                    }, 1000);
                }
            }
        }
    });
});
    window.loadEarlierMessages = function(responseData, callbackParams) {
        __DataRequest.get(__Utils.apiURL('{!! route('vendor.chat_message.contact.view', ['contactUid', 'way' => 'prepend', 'page', 'assigned' => ($assigned ?? '')]) !!}',{'contactUid': $('#lwWhatsAppChatWindow').attr('data-contact-uid'),'page':'page='+ window.messagePaginatePage}),{}, function() {});
        if(callbackParams) {
            appFuncs.modelSuccessCallback(responseData, callbackParams);
        }
    };
    window.onUpdateContactDetails = function(responseData, callbackParams) {
        __DataRequest.get(__Utils.apiURL('{!! route('vendor.chat_message.contact.view', ['contactUid', 'current_page', 'assigned' => ($assigned ?? '')]) !!}',{'contactUid': $('#lwWhatsAppChatWindow').attr('data-contact-uid'),'current_page':'current_page='+ window.messagePaginatePage}),{}, function() {});
        if(callbackParams) {
            appFuncs.modelSuccessCallback(responseData, callbackParams);
        }
    };
    window.contactsPaginatePage = 1;
    window.loadMoreContacts = function(responseData, callbackParams) {
    // If responseData is provided and indicates no more pages, set contactsPaginatePage to 0
    if (responseData && responseData.data && responseData.data.hasMorePages === false) {
        window.contactsPaginatePage = 0;
        __DataRequest.updateModels({
            contactsPaginatePage: 0,
        });
        return;
    }
    
    __DataRequest.get(__Utils.apiURL("{!! route('vendor.contacts.data.read', ['contactUid', 'page' => '', 'way' => 'append', 'search' => '', 'unread_only' => '', 'assigned' => ($assigned ?? '')]) !!}", {'contactUid': $('#lwWhatsAppChatWindow').attr('data-contact-uid'),'page':'page='+ window.contactsPaginatePage + '&', 'search':'search='+ window.searchValue + '&', 'unread_only':'unread_only='+ window.showUnreadContactsOnly + '&'}),{}, function() {});
};
    window.searchContacts = function(responseData, callbackParams) {
        const url = new URL(window.location.href);
        const searchValue = window.searchValue || '';
        
        // Get the component instance
        const component = document.querySelector('[x-data]')?.__x?.$data;
        const labelId = component?.selectedLabel || '';
        
        // Update URL with search and label parameters
        if (searchValue) {
            url.searchParams.set('search', searchValue);
        } else {
            url.searchParams.delete('search');
        }
        
        if (labelId) {
            url.searchParams.set('label', labelId);
        } else {
            url.searchParams.delete('label');
        }
        
        window.history.pushState({}, '', url);
        
        // Get the contacts container and all contacts
        const contactsContainer = document.querySelector('.lw-contact-list');
        const contacts = contactsContainer?.querySelectorAll('.lw-contact') || [];
        
        // Apply both search and label filters
        contacts.forEach(contact => {
            const contactName = contact.textContent?.toLowerCase() || '';
            const labels = JSON.parse(contact.getAttribute('data-labels') || '[]');
            const hasLabel = !labelId || labels.some(label => label._id === labelId);
            const matchesSearch = !searchValue || contactName.includes(searchValue.toLowerCase());
            
            contact.style.display = (hasLabel && matchesSearch) ? '' : 'none';
        });
        
        // If this was called from a data request, update the contacts
        if (responseData) {
            updateContacts(responseData, callbackParams);
        }
        
        __DataRequest.get(__Utils.apiURL('{!! route('vendor.chat_message.contact.view', ['page' => 1, 'assigned' => ($assigned ?? '')]) !!}', {
            'search': window.searchValue || '',
            'unread_only': window.showUnreadContactsOnly || 0,
            'label_id': labelId,
            'page': 1
        }), {}, function(response) {});
    };
    window.updateContactList = function(respodata) {
        __DataRequest.get(__Utils.apiURL("{!! route('vendor.contacts.data.read', ['contactUid', 'page' => '', 'assigned' => ($assigned ?? '')]) !!}", {'contactUid': $('#lwWhatsAppChatWindow').attr('data-contact-uid'),'page':'page='+ window.contactsPaginatePage + '&'}),{}, function() {});
    };
    window.updateContactInfo = function(responseData) {
        $('#lwCurrentlyAssignedUserUid')[0].selectize.setValue(responseData.data.currentlyAssignedUserUid);
    };
    window.onNewLabelCreated = function(responseData) {
        $('#lwLabelFieldTitle').val('');
    };
    window.updateManageLabelsList = function(responseData) {
        if(responseData.reaction == 1) {
            window.onUpdateContactDetails();
        }
    };
    window.updateContactList();
    window.onUpdateContactDetails();
    window.lwMessengerEmojiArea = $(".lw-input-emoji").emojioneArea({
    useInternalCDN: true,
    pickerPosition: "top",
    searchPlaceholder: "{{ __tr('Search') }}",
    buttonTitle: "{{ __tr('Use the TAB key to insert emoji faster') }}",
    events: {
        'emojibtn.click': function (editor, event) {
            this.hidePicker();
        },
        keyUp: function (editor, event) {
            var currentText = $.trim(this.getText());
            // Show canned replies picker when text starts with /
            if (currentText.startsWith('/')) {
                var component = document.querySelector('[x-data]')?.__x?.$data;
                if (component) {
                    component.showCannedPicker = true;
                }
            }
            if (event && event.which == 13 && !event.shiftKey && currentText) { // On Enter
                $('.lw-input-emoji').val(currentText);
                $('#whatsAppMessengerForm').submit();
                this.hidePicker();
                appFuncs.resetForm();
                var comp = document.querySelector('[x-data]')?.__x?.$data;
                if (comp) {
                    comp.showCannedPicker = false;
                }
            }
        }
    }
});
})(jQuery);
</script>
@endpush
@endsection()