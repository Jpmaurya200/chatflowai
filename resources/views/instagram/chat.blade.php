@extends('layouts.app', ['title' => __tr('Instagram Messages')])

@section('content')
<div class="container-fluid ">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            <div class="card shadow">
                <div class="card-header bg-gradient-instagram text-white border-0">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">
                                <i class="fab fa-instagram"></i>
                                {{ __tr('Instagram Messages') }}
                            </h3>
                        </div>
                        <div class="col-4 text-right">
                            @if(!getVendorSettings('enable_instagram_messaging'))
                                <a href="{{ route('vendor.settings.read', ['pageType' => 'instagram-api-setup']) }}" 
                                   class="btn btn-sm btn-light">
                                    <i class="fas fa-cog"></i> {{ __tr('Setup Instagram') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(!getVendorSettings('enable_instagram_messaging'))
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="alert-heading">{{ __tr('Instagram Not Configured') }}</h4>
                                    <p class="mb-0">
                                        {{ __tr('Please configure your Instagram API settings to start using Instagram messaging.') }}
                                        <a href="{{ route('vendor.settings.read', ['pageType' => 'instagram-api-setup']) }}" class="alert-link">
                                            {{ __tr('Configure Now') }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Instagram Chat Interface -->
                    <div id="lwInstagramChatWindow" class=" lw-whatsapp-chat-window ">


                            <div class="row g-0">
                                <!-- Contact List -->
                                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 mb-4 lw-contact-list-block">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h1>{{ __tr('Instagram') }}</h1>
                                        <button class="btn btn-sm btn-outline-primary" id="refresh-button">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>

                                    <!-- Toggle between Chats and Comments -->
                                    <div class="btn-group  w-100 mb-3  " role="group">
                                        <input type="radio" class="btn-check mr-2" name="instagram-mode" id="chat-mode" autocomplete="off" checked style="appearance: none;">
                                        <label class="btn btn-outline-primary mr-2" for="chat-mode">
                                            <i class="fas fa-comments me-1"></i>{{ __tr('Chats') }}
                                        </label>

                                        <input type="radio" class="btn-check" name="instagram-mode" id="comments-mode" autocomplete="off" style="appearance: none;">
                                        <label class="btn btn-outline-primary" for="comments-mode">
                                            <i class="fas fa-comment-dots me-1"></i>{{ __tr('Comments') }}
                                        </label>
                                    </div>

                                    <!-- Search -->
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="searchInput" placeholder="{{ __tr('Search...') }}">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>

                                    <!-- Instagram Conversations (Chat Mode) -->
                                    <div id="conversations-section" class="list-group lw-contact-list" style="max-height: 500px; overflow-y: auto;">
                                        <!-- Loading State -->
                                        <div class="text-center p-4 loading-state" style="display: none;">
                                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('Loading conversations...') }}</p>
                                        </div>

                                        <!-- No Conversations -->
                                        <div class="text-center p-4 no-conversations" style="display: none;">
                                            <i class="fab fa-instagram fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('No conversations found') }}</p>
                                        </div>

                                        <!-- Conversations List Container -->
                                        <div id="conversationsList" class="conversations-container"></div>
                                    </div>
                                    <!-- Instagram Media Posts (Comments Mode) -->
                                    <div id="media-section" class="list-group lw-contact-list" style="max-height: 500px; overflow-y: auto; display: none;">
                                        <!-- Loading State -->
                                        <div class="text-center p-4 media-loading-state" style="display: none;">
                                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('Loading media posts...') }}</p>
                                        </div>

                                        <!-- No Media -->
                                        <div class="text-center p-4 no-media" style="display: none;">
                                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('No media posts found') }}</p>
                                        </div>

                                        <!-- Media Posts List -->
                                        <div id="mediaPostsList"></div>
                                    </div>
                                </div>

                                <!-- Chat Window -->
                                <div class="col-sm-12 col-md-9 col-lg-9 col-xl-9 mb-4">
                                    <div id="chat-window-container">
                                        <!-- Empty State for Chat Mode -->
                                        <div id="empty-state" class="text-center p-5">
                                            <i class="fab fa-instagram fa-3x text-danger mb-3"></i>
                                            <h5 class="text-muted">{{ __tr('Select a conversation') }}</h5>
                                            <p class="text-muted">{{ __tr('Choose a conversation from the list to start chatting') }}</p>
                                        </div>

                                        <!-- Empty State for Comments Mode -->
                                        <div id="comments-empty-state" class="text-center p-5" style="display: none;">
                                            <i class="fas fa-comment-dots fa-3x text-danger mb-3"></i>
                                            <h5 class="text-muted">{{ __tr('Select a media post') }}</h5>
                                            <p class="text-muted">{{ __tr('Choose a media post from the list to view and reply to comments') }}</p>
                                        </div>

                                        <div id="chat-container" class="chat-container" style="display: none;">
                                            <div class="chat-header bg-white p-3 border-bottom">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-danger text-white text-center me-3">
                                                        <span id="chat-header-initials">IG</span>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0" id="chat-header-username">Instagram User</h5>
                                                        <small class="text-muted">Instagram Contact</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="chat-messages" class="chat-messages p-3" style="height: 400px; overflow-y: auto;">
                                                <!-- Loading State -->
                                                <div id="messages-loading" class="text-center p-4" style="display: none;">
                                                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                                    <p class="text-muted">{{ __tr('Loading messages...') }}</p>
                                                </div>

                                                <!-- No Messages -->
                                                <div id="no-messages" class="text-center p-4" style="display: none;">
                                                    <p class="text-muted">{{ __tr('No messages yet') }}</p>
                                                </div>

                                                <!-- Messages List -->
                                                <div id="messages-list"></div>
                                            </div>

                                        <div class="ig-chat-input">
                                          <form id="message-form">
                                            @csrf
                                            <input
                                              type="text"
                                              id="message-input"
                                              placeholder="Message..."
                                              autocomplete="off"
                                            />
                                            <button type="submit" id="send-button" disabled>
                                              <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="#0095f6"
                                                width="20"
                                                height="20"
                                              >
                                                <path
                                                  d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"
                                                />
                                              </svg>
                                            </button>
                                          </form>
                                        </div>


                                        </div>

                                        <!-- Comments Container -->
                                        <div id="comments-container" class="comments-container" style="display: none;">
                                            <div class="comments-header">
                                              <div class="comments-header-content">
                                                <div class="avatar">
                                                  <span id="comments-header-icon">
                                                    <!-- Image icon SVG -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" width="18" height="18">
                                                      <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14l4-4h12a2 2 0 0 0 2-2zM5 13.17l2.59-2.58a1 1 0 0 1 1.41 0L12 13.17l4.59-4.58a1 1 0 0 1 1.41 0L21 11V5H5v8.17z"/>
                                                    </svg>
                                                  </span>
                                                </div>
                                                <div class="caption-block">
                                                  <h5 id="comments-header-caption">Media Post</h5>
                                                  <small class="source-label">Instagram Media</small>
                                                </div>
                                              </div>
                                            </div>

                                            <div id="comments-messages" class="comments-messages p-3" style="height: 400px; overflow-y: auto;">
                                                <!-- Loading State -->
                                                <div id="comments-loading" class="text-center p-4" style="display: none;">
                                                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                                    <p class="text-muted">{{ __tr('Loading comments...') }}</p>
                                                </div>

                                                <!-- No Comments -->
                                                <div id="no-comments" class="text-center p-4" style="display: none;">
                                                    <i class="fas fa-comment-slash fa-2x text-muted mb-3"></i>
                                                    <p class="text-muted">{{ __tr('No comments found') }}</p>
                                                </div>

                                                <!-- Comments List -->
                                                <div id="comments-list"></div>
                                            </div>

                                            <!-- Comment Reply Form -->
                                            <div class="ig-comments-input ">
                                                <form id="comment-reply-form" >
                                                   
                                                        <input type="text"
                                                               id="comment-reply-input"
                                                               
                                                               placeholder="{{ __tr('Send message to start chat') }}">
                                                        <button type="submit" id="comment-reply-button"  disabled>
                                                           <svg
                                                             xmlns="http://www.w3.org/2000/svg"
                                                             viewBox="0 0 24 24"
                                                             fill="#0095f6"
                                                             width="20"
                                                             height="20"
                                                                >
                                                             <path
                                                                d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"
                                                                 />
                                                            </svg>
                                                        </button>
                                                    
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    

   /* Instagram Chat Custom Styles */
:root {
    --ig-primary: #0095f6;
    --ig-secondary: #262626;
    --ig-border: #dbdbdb;
    --ig-background: #fafafa;
    --ig-text: #262626;
    --ig-text-secondary: #8e8e8e;
    --ig-white: #ffffff;
    --ig-danger: #ed4956;
    --ig-success: #78de45;
}
.bg-gradient-instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}
.btn-check {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.card {
    border: none;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1) !important;
}
.card-header {
    padding: 1.25rem;
}
.card-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
}
.card-body {
    padding: 0;
}
#lwInstagramChatWindow {
    padding: 1.5rem !important;
}
.lw-contact-list-block h1 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--ig-secondary);
    margin-bottom: 1rem;
}
.lw-contact-list {
    border: 1px solid var(--ig-border);
    border-radius: 8px;
    padding: 0.5rem;
    background: var(--ig-white);
}
.chat-container {
    border: 1px solid var(--ig-border);
    border-radius: 4px;
    background: var(--ig-white);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.message {
    max-width: 65%;
    margin-bottom: 0.75rem;
    font-size: 0.9375rem;
}
.message.sent {
    margin-left: auto;
}
.message.received {
    margin-right: auto;
}
.message-content {
    padding: 0.75rem 1rem;
    border-radius: 22px;
    background: var(--ig-background) !important;
    color: var(--ig-text);
    line-height: 1.4;
}
.message.sent .message-content {
    background: var(--ig-primary);
    color: var(--ig-white);
}
.message .username {
    font-size: 0.75rem;
    color: var(--ig-text-secondary);
    margin-top: 0.25rem;
    display: block;
}
.lw-contact-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    padding: 2px;
}
.lw-contact-avatar span {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--ig-white);
}
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--ig-background);
}
.conversation-item {
    cursor: pointer;
    transition: all 0.2s ease;
    border: none !important;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: 8px;
}
.conversation-item:hover {
    background-color: var(--ig-background);
}
.conversation-item.active {
    background-color: rgba(0, 149, 246, 0.1);
    color: var(--ig-primary);
}
.conversation-item.active .text-muted {
    color: var(--ig-primary) !important;
    opacity: 0.8;
}
.chat-header {
    border-bottom: 1px solid var(--ig-border);
    padding: 1rem;
}
.chat-messages {
    background: var(--ig-white);
    padding: 1.5rem;
}
.chat-input {
    border-top: 1px solid var(--ig-border);
    padding: 1rem;
}
.chat-input .form-control {
    border-radius: 22px;
    padding: 0.75rem 1.25rem;
    border: 1px solid var(--ig-border);
    font-size: 0.9375rem;
}
.chat-input .form-control:focus {
    border-color: var(--ig-primary);
    box-shadow: none;
}
.chat-input .btn-primary {
    border-radius: 50%;
    width: 42px;
    height: 42px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--ig-primary);
    border: none;
    margin-left: 0.5rem;
}
.chat-input .btn-primary:disabled {
    background: var(--ig-text-secondary);
    opacity: 0.5;
}
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: var(--ig-background);
}
::-webkit-scrollbar-thumb {
    background: var(--ig-border);
    border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
    background: var(--ig-text-secondary);
}
.loading-state .fa-spinner {
    color: var(--ig-primary) !important;
}
.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
.alert-danger {
    background: var(--ig-danger);
    color: var(--ig-white);
}
.alert-success {
    background: var(--ig-success);
    color: var(--ig-white);
}
.input-group {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--ig-border);
}
.input-group .form-control {
    border: none;
    padding: 0.75rem 1rem;
}
.input-group .input-group-text {
    background: var(--ig-white);
    border: none;
    color: var(--ig-text-secondary);
}
#empty-state {
    color: var(--ig-text-secondary);
}
#empty-state i {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.comments-header {
  background-color: #fff;
  padding: 12px 16px;
  border-bottom: 1px solid #dbdbdb;
}

.comments-header-content {
  display: flex;
  align-items: center;
}

.avatar {
  width: 40px;
  height: 40px;
  background-color: #ed4956; /* IG red */
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
}

#comments-header-icon svg {
  width: 18px;
  height: 18px;
  fill: white;
}

.caption-block h5 {
  font-size: 14px;
  margin: 0;
  color: #262626;
  font-weight: 600;
}

.source-label {
  font-size: 12px;
  color: #8e8e8e;
}

.list-group-item.active
{
    z-index: 2;

    color: #fff;
    border:none;
    background-color: #bebec2ff;
}

.ig-chat-input {
  padding: 12px 16px;
  background-color: #fff;
  border-top: 1px solid #dbdbdb;
}

.ig-chat-input form {
  display: flex;
  align-items: center;
  background: #f0f0f0;
  padding: 8px 12px;
  border-radius: 22px;
  transition: background 0.2s ease;
}

#message-input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 14px;
  padding: 4px 8px;
  color: #262626;
}

#send-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
}

#send-button:disabled svg {
  opacity: 0.4;
  cursor: default;
}
.ig-comments-input {
  padding: 12px 16px;
  background-color: #fff;
  border-top: 1px solid #dbdbdb;
}

.ig-comments-input form {
  display: flex;
  align-items: center;
  background: #f0f0f0;
  padding: 8px 12px;
  border-radius: 22px;
  transition: background 0.2s ease;
}

#comment-reply-input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 14px;
  padding: 4px 8px;
  color: #262626;
}

#comment-reply-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
}
#comment-reply-button:disabled svg {
  opacity: 0.4;
  cursor: default;
}

</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let selectedConversation = null;
    let contacts = [];
    let currentMessages = []; // Store current messages
    let isSendingMessage = false; // Prevent multiple sends
    let currentMode = 'chat'; // 'chat' or 'comments'
    let mediaPosts = [];
    let selectedMediaPost = null;
    let selectedComment = null;
    let commentsRefreshInterval = null;

    // Initialize
    loadConversations();

    // Handle mode toggle
    $('input[name="instagram-mode"]').on('change', function() {
        const mode = $(this).attr('id').replace('-mode', '');
        switchMode(mode);
    });

    // Handle refresh button
    $('#refresh-button').on('click', function() {
        if (currentMode === 'chat') {
            loadConversations();
        } else {
            loadMediaPosts();
        }
    });

    // Handle message input
    $('#message-input').on('input', function() {
        $('#send-button').prop('disabled', !$(this).val().trim());
    });

    // Handle message form submission
    $('#message-form').on('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });

    // Handle search input
    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterContacts(searchTerm);
    });

    // Load conversations
    function loadConversations() {
        $('.loading-state').show();
        $('.no-conversations').hide();

        return fetch('{{ route("vendor.instagram.conversations") }}')
            .then(response => response.json())
            .then(data => {
                contacts = data.data || [];
                updateConversationsList();
                return contacts; // Return the contacts for chaining
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
                showAlert('Failed to load conversations', 'danger');
                return []; // Return empty array on error
            })
            .finally(() => {
                $('.loading-state').hide();
            });
    }

    // Update conversations list
    function updateConversationsList() {
        const $list = $('#conversationsList');
        $list.empty();

        if (!contacts || contacts.length === 0) {
            $('.no-conversations').show();
            return;
        }

        // Sort contacts by last_message_time (or created_at) descending to get the latest conversation first
        const sortedContacts = contacts.slice().sort((a, b) => {
            const aTime = new Date(a.last_message_time || a.created_at || 0).getTime();
            const bTime = new Date(b.last_message_time || b.created_at || 0).getTime();
            return bTime - aTime;
        });

        // Store the last conversation for later use
        let lastConversation = sortedContacts.length > 0 ? sortedContacts[0] : null;
        let autoSelected = false;

        sortedContacts.forEach(conversation => {
            try {
                // Handle both data structures (direct and nested)
                const participants = conversation.participants?.data || conversation.participants || [];
                const instagramUsername = '{{ getVendorSettings('instagram_account_name') }}';
                const otherParticipant = participants.find(p => p.username !== instagramUsername) || {};
                
                // Use fallback values if data is missing
                const username = otherParticipant.username || conversation.instagram_username || 'Unknown User';
                const conversationId = conversation.id || conversation.conversation_id;
                
                if (!conversationId) {
                    console.warn('Missing conversation ID:', conversation);
                    return;
                }

                const $item = $(`
                    <a href="#" class="list-group-item list-group-item-action conversation-item" data-id="${conversationId}">
                        <div class="d-flex w-100 align-items-center">
                            <div class="lw-contact-avatar bg-danger text-white text-center me-3">
                                <span>${getInitials(username)}</span>
                            </div>
                            <div class="">
                                <h4 class="mb-1 ml-2">${username}</h4>
                               
                            </div>
                        </div>
                    </a>
                `);

                $list.append($item);
            } catch (error) {
                console.error('Error processing conversation:', error, conversation);
            }
        });

        // Handle conversation selection
        $('.conversation-item').on('click', function(e) {
            e.preventDefault();
            const conversationId = $(this).data('id');
            // Prevent double selection if already selected
            if (selectedConversation && ((selectedConversation.id || selectedConversation.conversation_id) === conversationId)) {
                return;
            }
            const conversation = contacts.find(c => 
                (c.id === conversationId) || (c.conversation_id === conversationId)
            );
            if (!conversation) {
                console.error('Conversation not found:', conversationId);
                showAlert('Failed to load conversation', 'danger');
                return;
            }
            selectConversation(conversation);
        });

        // Auto-select the last conversation if available and none is selected
        if (lastConversation && !selectedConversation && !autoSelected) {
            selectedConversation = lastConversation;
            selectConversation(lastConversation);
            autoSelected = true;
        }
    }

    // Select conversation
    function selectConversation(conversation) {
        try {
            if (!conversation) {
                console.error('Invalid conversation object');
                return;
            }

            selectedConversation = conversation;
            
            // Handle both data structures (direct and nested)
            const participants = conversation.participants?.data || conversation.participants || [];
            const instagramUsername = '{{ getVendorSettings('instagram_account_name') }}';
            const otherParticipant = participants.find(p => p.username !== instagramUsername) || {};
            
            // Use fallback values if data is missing
            const username = otherParticipant.username || conversation.instagram_username || 'Unknown User';
            const conversationId = conversation.id || conversation.conversation_id;
            
            if (!conversationId) {
                console.error('Missing conversation ID');
                return;
            }

            // Update UI
            $('.conversation-item').removeClass('active');
            $(`.conversation-item[data-id="${conversationId}"]`).addClass('active');
            
            // Update chat header
            $('#chat-header-initials').text(getInitials(username));
            $('#chat-header-username').text(username);
            
            // Show chat container
            $('#empty-state').hide();
            $('#chat-container').show();
            
            // Load messages
            loadMessages(conversationId);
        } catch (error) {
            console.error('Error selecting conversation:', error, conversation);
            showAlert('Failed to load conversation', 'danger');
        }
    }

    // Load messages
    function loadMessages(conversationId) {
        $('#messages-loading').show();
        $('#no-messages').hide();
        $('#messages-list').empty();

        fetch(`/vendor-console/instagram/conversation/${conversationId}/messages`)
            .then(response => response.json())
            .then(data => {
                const messages = data.data || [];
                updateMessagesList(messages);
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                showAlert('Failed to load messages', 'danger');
            })
            .finally(() => {
                $('#messages-loading').hide();
            });
    }

    // Update messages list
    function updateMessagesList(messages) {
        // Store current messages
        currentMessages = messages;

        const $list = $('#messages-list');
        $list.empty();

        if (messages.length === 0) {
            $('#no-messages').show();
            return;
        }

        $('#no-messages').hide();

        messages.forEach(message => {
            // Ensure message has required properties
            const messageText = message.message || message.formatted_message || '';
            const isIncoming = message.is_incoming_message === true; // Explicitly check for true
            const username = message.from?.username || (isIncoming ? 'User' : 'You');

            const $message = $(`
                <div class="message ${isIncoming ? 'received' : 'sent'}" style="display: flex !important; flex-direction: column !important; max-width: 65% !important; margin-bottom: 0.75rem !important; ${isIncoming ? 'align-items: flex-start !important; margin-right: auto !important; margin-left: 0 !important;' : 'align-items: flex-end !important; margin-left: auto !important; margin-right: 0 !important;'}">
                    <div class="message-content" style="padding: 0.75rem 1rem !important; border-radius: 22px !important; line-height: 1.4 !important; ${isIncoming ? 'background: #f1f3f4 !important; color: #000 !important;' : 'background: #007bff !important; color: #fff !important;'}">${messageText}</div>
                    <small class="text-muted" style="font-size: 0.75rem !important; margin-top: 0.25rem !important; ${isIncoming ? 'text-align: left !important;' : 'text-align: right !important;'}">
                        <span class="username">${username}</span>
                        
                    </small>
                </div>
            `);

            $list.append($message);
        });

        // Scroll to bottom
        const container = document.getElementById('chat-messages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Send message
    function sendMessage() {
        const $input = $('#message-input');
        const message = $input.val().trim();

        if (!message || !selectedConversation || isSendingMessage) return;

        isSendingMessage = true;

        // Get conversation ID from the selected conversation
        const conversationId = selectedConversation.id || selectedConversation.conversation_id;

        if (!conversationId) {
            console.error('No conversation ID found:', selectedConversation);
            showAlert('Failed to send message: No conversation ID', 'danger');
            return;
        }

        // Add temporary message to UI
        const tempMessage = {
            _uid: 'temp_' + Date.now(),
            message: message,
            is_incoming_message: false,
            from: { username: 'You' }
        };

        // Add temp message to current messages and update UI
        const messagesWithTemp = [...currentMessages, tempMessage];
        updateMessagesList(messagesWithTemp);

        // Clear input
        $input.val('');
        $('#send-button').prop('disabled', true);

        // Send to backend
        fetch(`/vendor-console/instagram/conversation/${conversationId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload messages to show the sent message
                loadMessages(conversationId);
            } else {
                console.error('Send message failed:', data);
                showAlert('Failed to send message: ' + (data.message || 'Unknown error'), 'danger');
                // Reload messages to remove the temporary message
                loadMessages(conversationId);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            showAlert('Failed to send message', 'danger');
            // Reload messages to remove the temporary message
            loadMessages(conversationId);
        })
        .finally(() => {
            isSendingMessage = false;
        });
    }

    // Switch between chat and comments mode
    function switchMode(mode) {
        currentMode = mode;

        if (mode === 'chat') {
            // Clear comments refresh interval when switching to chat mode
            if (commentsRefreshInterval) {
                clearInterval(commentsRefreshInterval);
                commentsRefreshInterval = null;
            }

            // Show chat sections, hide comments sections
            $('#conversations-section').show();
            $('#media-section').hide();
            $('#chat-container').show();
            $('#comments-container').hide();
            $('#empty-state').show();
            $('#comments-empty-state').hide();

            // Update search placeholder
            $('#searchInput').attr('placeholder', '{{ __tr("Search conversations...") }}');

            // Load conversations if not already loaded
            if (contacts.length === 0) {
                loadConversations();
            }
        } else {
            // Show comments sections, hide chat sections
            $('#conversations-section').hide();
            $('#media-section').show();
            $('#chat-container').hide();
            $('#comments-container').hide();
            $('#empty-state').hide();
            $('#comments-empty-state').show();

            // Update search placeholder
            $('#searchInput').attr('placeholder', '{{ __tr("Search media posts...") }}');

            // Load media posts if not already loaded
            if (mediaPosts.length === 0) {
                loadMediaPosts();
            }

            // If a media post is already selected, restart the refresh interval
            if (selectedMediaPost) {
                if (commentsRefreshInterval) {
                    clearInterval(commentsRefreshInterval);
                }
                commentsRefreshInterval = setInterval(() => {
                    if (selectedMediaPost && currentMode === 'comments') {
                        loadMediaComments(selectedMediaPost.id);
                    }
                }, 30000);
            }
        }
    }

    // Load Instagram media posts
    function loadMediaPosts() {
        $('.media-loading-state').show();
        $('.no-media').hide();

        fetch('{{ route("vendor.instagram.media") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Media posts API response:', data);
                
                if (!data.success) {
                    console.error('API returned error:', data.message);
                    showAlert('Failed to load media posts: ' + (data.message || 'Unknown error'), 'danger');
                    $('.no-media').show();
                    return;
                }

                mediaPosts = data.data?.media || [];
                console.log('Loaded media posts:', mediaPosts.length, mediaPosts);
                
                if (mediaPosts.length === 0) {
                    console.warn('No media posts found');
                    $('.no-media').show();
                }
                
                updateMediaPostsList();
            })
            .catch(error => {
                console.error('Error loading media posts:', error);
                showAlert('Failed to load media posts: ' + error.message, 'danger');
                $('.no-media').show();
            })
            .finally(() => {
                $('.media-loading-state').hide();
            });
    }

    // Update media posts list
    function updateMediaPostsList() {
        const $list = $('#mediaPostsList');
        $list.empty();

        if (!mediaPosts || mediaPosts.length === 0) {
            $('.no-media').show();
            return;
        }

        mediaPosts.forEach(post => {
            const caption = post.caption || 'No caption';
            const shortCaption = caption.length > 50 ? caption.substring(0, 50) + '...' : caption;

            const $item = $(`
                <a href="#" class="list-group-item list-group-item-action media-item" data-id="${post.id}">
                    <div class="d-flex w-100 align-items-center">
                        <div class="lw-contact-avatar bg-primary text-white text-center me-3">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="flex-grow-1  ">
                            <h4 class="mb-1 ml-2">${shortCaption}</h4>
                            
                        </div>
                    </div>
                </a>
            `);

            $list.append($item);
        });

        // Handle media post selection
        $('.media-item').on('click', function(e) {
            e.preventDefault();
            const mediaId = $(this).data('id');
            const mediaPost = mediaPosts.find(p => p.id === mediaId);

            if (mediaPost) {
                selectMediaPost(mediaPost);
            }
        });
    }

    // Select media post and load comments
    function selectMediaPost(mediaPost) {
        selectedMediaPost = mediaPost;

        // Clear any existing refresh interval
        if (commentsRefreshInterval) {
            clearInterval(commentsRefreshInterval);
            commentsRefreshInterval = null;
        }

        // Update header
        const caption = mediaPost.caption || 'No caption';
        const shortCaption = caption.length > 30 ? caption.substring(0, 30) + '...' : caption;
        $('#comments-header-caption').text(shortCaption);

        // Show comments container
        $('#comments-empty-state').hide();
        $('#comments-container').show();

        // Load comments for this media post
        loadMediaComments(mediaPost.id);

        // Set up auto-refresh every 30 seconds
        commentsRefreshInterval = setInterval(() => {
            if (selectedMediaPost && currentMode === 'comments') {
                loadMediaComments(selectedMediaPost.id);
            }
        }, 30000); // Refresh every 30 seconds
    }

    // Load comments for a media post
    function loadMediaComments(mediaId) {
        $('#comments-loading').show();
        $('#no-comments').hide();
        
        // Don't clear the list on refresh - let updateCommentsList handle it
        if (!$('#comments-list').children().length) {
            $('#comments-list').empty();
        }

        fetch(`/vendor-console/instagram/media/${mediaId}/comments`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Comments API response:', data);
                
                if (!data.success) {
                    console.error('API returned error:', data.message);
                    showAlert('Failed to load comments: ' + (data.message || 'Unknown error'), 'danger');
                    if ($('#comments-list').children().length === 0) {
                        $('#no-comments').show();
                    }
                    return;
                }

                const comments = data.data?.comments || [];
                console.log('Loaded comments:', comments.length, comments);
                
                if (comments.length === 0 && $('#comments-list').children().length === 0) {
                    console.warn('No comments found for media:', mediaId);
                    $('#no-comments').show();
                }
                
                updateCommentsList(comments);
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                showAlert('Failed to load comments: ' + error.message, 'danger');
                if ($('#comments-list').children().length === 0) {
                    $('#no-comments').show();
                }
            })
            .finally(() => {
                $('#comments-loading').hide();
            });
    }

    // Update comments list
    function updateCommentsList(comments) {
        const $list = $('#comments-list');
        
        // Store existing comment IDs to detect new comments
        const existingCommentIds = new Set();
        $list.find('.comment-item').each(function() {
            existingCommentIds.add($(this).data('comment-id'));
        });

        if (comments.length === 0) {
            if ($list.children().length === 0) {
                $('#no-comments').show();
            }
            return;
        }

        $('#no-comments').hide();

        // If this is the first load (no existing comments), clear and show all
        const isFirstLoad = existingCommentIds.size === 0;
        
        if (isFirstLoad) {
            $list.empty();
        }

        let hasNewComments = false;
        const commentIdsInResponse = new Set();
        
        // Sort comments by timestamp (newest first) for display
        const sortedComments = comments.slice().sort((a, b) => {
            const timeA = new Date(a.timestamp || 0).getTime();
            const timeB = new Date(b.timestamp || 0).getTime();
            return timeB - timeA; // Newest first
        });

        sortedComments.forEach(comment => {
            commentIdsInResponse.add(comment.id);
            
            // Check if this comment already exists
            if (!isFirstLoad && existingCommentIds.has(comment.id)) {
                return; // Skip existing comments on refresh
            }

            hasNewComments = true;
            const username = comment.username || 'Unknown User';
            const text = comment.text || '';
            const timeAgo = comment.time_ago || comment.formatted_date || 'Unknown time';
            // Ensure comment ID is always a string
            const commentId = String(comment.id || '');

            if (!commentId) {
                console.error('Comment missing ID:', comment);
                return; // Skip comments without IDs
            }

            const $comment = $(`
                <div class="comment-item mb-3 p-3 border rounded" data-comment-id="${commentId}">
                    <div class="d-flex align-items-start">
                        <div class="avatar bg-secondary text-white text-center me-3" style="width: 40px; height: 40px; line-height: 40px; border-radius: 50%;">
                            <span>${getInitials(username)}</span>
                        </div>
                        <div class="flex-grow-1 ml-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 class="mb-1 ">${username}</h4>
                                <small class="text-muted">${timeAgo}</small>
                            </div>
                            <p class="mb-2">${text}</p>
                            <button class="btn btn-sm btn-outline-primary reply-comment-btn" data-comment-id="${commentId}" data-username="${username}">
                                <i class="fas fa-comment me-1"></i>Start Chat
                            </button>
                        </div>
                    </div>
                </div>
            `);

            // Append comments (newest will be at top due to sorting)
            $list.append($comment);
        });

        // Remove comments that are no longer in the response (if any)
        if (!isFirstLoad) {
            $list.find('.comment-item').each(function() {
                const commentId = $(this).data('comment-id');
                if (!commentIdsInResponse.has(commentId)) {
                    $(this).remove();
                }
            });
        }

        // Reattach click handlers for all reply buttons (including new ones)
        $('.reply-comment-btn').off('click').on('click', function() {
            const commentId = String($(this).data('comment-id') || $(this).attr('data-comment-id') || '');
            const username = $(this).data('username') || $(this).attr('data-username') || 'Unknown User';
            if (commentId) {
                selectCommentForReply(commentId, username);
            } else {
                console.error('Comment ID not found');
                showAlert('Error: Comment ID not found', 'danger');
            }
        });

        // Highlight the selected comment if one is selected
        if (selectedComment) {
            $list.find(`.comment-item[data-comment-id="${selectedComment}"]`).addClass('border-primary');
        }

        // Scroll to bottom
        const container = document.getElementById('comments-messages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Select comment for reply
    function selectCommentForReply(commentId, username) {
        // Ensure commentId is a string
        commentId = String(commentId || '');
        
        if (!commentId) {
            console.error('Invalid comment ID provided');
            showAlert('Error: Invalid comment ID', 'danger');
            return;
        }
        
        // Remove previous selection highlight
        $('.comment-item').removeClass('border-primary');
        
        selectedComment = commentId;
        
        // Highlight selected comment
        $(`.comment-item[data-comment-id="${commentId}"]`).addClass('border-primary');
        
        $('#comment-reply-input').attr('placeholder', `Send message to ${username}...`);
        $('#comment-reply-input').focus();
        $('#comment-reply-button').prop('disabled', false);
    }

    // Handle comment reply form
    $('#comment-reply-form').on('submit', function(e) {
        e.preventDefault();
        sendCommentReply();
    });

    $('#comment-reply-input').on('input', function() {
        $('#comment-reply-button').prop('disabled', !$(this).val().trim());
    });

    // Send comment reply - redirect to chat mode and start conversation
    function sendCommentReply() {
        const $input = $('#comment-reply-input');
        const message = $input.val().trim();

        if (!message) {
            showAlert('Please enter a message', 'warning');
            return;
        }

        // Ensure selectedComment is a string and exists
        const commentId = String(selectedComment || '');
        if (!commentId || commentId === 'null' || commentId === 'undefined') {
            showAlert('Please select a comment to reply to', 'warning');
            return;
        }

        // Find the comment details
        const commentElement = $(`.comment-item[data-comment-id="${commentId}"]`);
        const username = commentElement.find('.reply-comment-btn').data('username') || 
                         commentElement.find('.reply-comment-btn').attr('data-username') || 
                         'user';

        console.log('Sending reply:', { comment_id: commentId, message: message, username: username });

        // Disable input and button while sending
        $input.prop('disabled', true);
        $('#comment-reply-button').prop('disabled', true);

        // Send the initial message via comment reply API
        fetch('{{ route("vendor.instagram.comment.reply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                comment_id: commentId, // Ensure it's always a string
                message: message
            })
        })
        .then(response => {
            console.log('Comment reply HTTP response:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Comment reply response data:', data); // Debug log

            if (data.success) {
                showAlert(`Message sent to ${username}`, 'success');
                // Clear input and reset
                $input.val('');
                $input.prop('disabled', false);
                selectedComment = null;
                $('.comment-item').removeClass('border-primary');
                $('#comment-reply-input').attr('placeholder', 'Send message to start chat...');
                redirectToChatMode(username);
            } else {
                console.error('Send comment reply failed:', data);
                showAlert('Failed to send reply: ' + (data.message || 'Unknown error'), 'danger');
                // Re-enable input on error
                $input.prop('disabled', false);
                $('#comment-reply-button').prop('disabled', false);
            }
        })
        .catch(error => {
            console.error('Error sending comment reply:', error);
            showAlert('Failed to send reply: Network error', 'danger');
            // Re-enable input on error
            $input.prop('disabled', false);
            $('#comment-reply-button').prop('disabled', false);
        });
    }

    // Redirect to chat mode and find conversation
    function redirectToChatMode(username) {
        // Switch to chat mode
        $('#chat-mode').prop('checked', true);
        switchMode('chat');

        // Reload conversations to find the new conversation
        loadConversations().then(() => {
            // Try to find and select the conversation with this user
            setTimeout(() => {
                findAndSelectConversationByUsername(username);
            }, 1000); // Give some time for the conversation to appear
        });

        // Reset reply state
        selectedComment = null;
        $('#comment-reply-input').attr('placeholder', 'Send message to start chat...');
    }

    // Find and select conversation by username
    function findAndSelectConversationByUsername(username) {
        // Look for a conversation with this username
        const conversation = contacts.find(conv => {
            const participants = conv.participants?.data || conv.participants || [];
            return participants.some(p => p.username === username);
        });

        if (conversation) {
            // Select this conversation
            const conversationId = conversation.id || conversation.conversation_id;
            if (conversationId) {
                // Trigger click on the conversation item
                $(`.conversation-item[data-id="${conversationId}"]`).click();
                showAlert(`Switched to conversation with ${username}`, 'info');
            }
        } else {
            showAlert(`Message sent to ${username}. The conversation will appear when they reply.`, 'info');
        }
    }

    // Filter contacts
    function filterContacts(searchTerm) {
        try {
            const filtered = contacts.filter(conversation => {
                try {
                    // Handle both data structures (direct and nested)
                    const participants = conversation.participants?.data || conversation.participants || [];
                    const instagramUsername = '{{ getVendorSettings('instagram_account_name') }}';
                    const otherParticipant = participants.find(p => p.username !== instagramUsername) || {};
                    
                    // Use fallback values if data is missing
                    const username = otherParticipant.username || conversation.instagram_username || '';
                    return username.toLowerCase().includes(searchTerm);
                } catch (error) {
                    console.error('Error filtering conversation:', error, conversation);
                    return false;
                }
            });
            
            // Clear and rebuild the conversations list with filtered results
            const $list = $('#conversationsList');
            $list.empty();

            if (filtered.length === 0) {
                $('.no-conversations').show();
                return;
            }

            // Sort filtered conversations by last_message_time or created_at
            const sortedFiltered = filtered.slice().sort((a, b) => {
                const aTime = new Date(a.last_message_time || a.created_at || 0).getTime();
                const bTime = new Date(b.last_message_time || b.created_at || 0).getTime();
                return bTime - aTime;
            });

            let lastConversation = sortedFiltered.length > 0 ? sortedFiltered[0] : null;
            let autoSelected = false;

            sortedFiltered.forEach(conversation => {
                try {
                    // Handle both data structures (direct and nested)
                    const participants = conversation.participants?.data || conversation.participants || [];
                    const instagramUsername = '{{ getVendorSettings('instagram_account_name') }}';
                    const otherParticipant = participants.find(p => p.username !== instagramUsername) || {};
                    // Use fallback values if data is missing
                    const username = otherParticipant.username || conversation.instagram_username || 'Unknown User';
                    const conversationId = conversation.id || conversation.conversation_id;
                    if (!conversationId) {
                        console.warn('Missing conversation ID:', conversation);
                        return;
                    }
                    const $item = $(
                        `<a href="#" class="list-group-item list-group-item-action conversation-item" data-id="${conversationId}">
                            <div class="d-flex w-100 align-items-center">
                                <div class="lw-contact-avatar  text-white text-center me-3">
                                    <span>${getInitials(username)}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${username}</h6>
                                    <small class="text-muted">Instagram conversation</small>
                                </div>
                            </div>
                        </a>`
                    );
                    $list.append($item);
                } catch (error) {
                    console.error('Error processing filtered conversation:', error, conversation);
                }
            });

            // Auto-select the last filtered conversation if available
            if (lastConversation && !selectedConversation && !autoSelected) {
                selectedConversation = lastConversation;
                selectConversation(lastConversation);
                autoSelected = true;
            }

            // Reattach click handlers
            $('.conversation-item').on('click', function(e) {
                e.preventDefault();
                const conversationId = $(this).data('id');
                const conversation = contacts.find(c => 
                    (c.id === conversationId) || (c.conversation_id === conversationId)
                );
                
                if (!conversation) {
                    console.error('Conversation not found:', conversationId);
                    showAlert('Failed to load conversation', 'danger');
                    return;
                }
                
                selectConversation(conversation);
            });
        } catch (error) {
            console.error('Error filtering contacts:', error);
            showAlert('Failed to filter contacts', 'danger');
        }
    }

    // Helper function to get initials
    function getInitials(name) {
        if (!name) return 'IG';
        return name.split(' ')
            .map(word => word.charAt(0).toUpperCase())
            .join('')
            .substring(0, 2);
    }

    // Show alert message
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('.card-body').prepend(alertHtml);

        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
});
</script>
@endpush
@endsection