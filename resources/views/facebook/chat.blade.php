@extends('layouts.app', ['title' => __tr('Facebook Messages')])

@section('content')
<div class="container-fluid ">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            <div class="card shadow">
                <div class="card-header bg-gradient-facebook text-white border-0">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">
                                <i class="fab fa-facebook"></i>
                                {{ __tr('Facebook Messages') }}
                            </h3>
                        </div>
                        <div class="col-4 text-right">
                            @if(!getVendorSettings('enable_facebook_messaging'))
                                <a href="{{ route('vendor.settings.read', ['pageType' => 'facebook-api-setup']) }}"
                                   class="btn btn-sm btn-light">
                                    <i class="fas fa-cog"></i> {{ __tr('Setup Facebook') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(!getVendorSettings('enable_facebook_messaging'))
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="alert-heading">{{ __tr('Facebook Not Configured') }}</h4>
                                    <p class="mb-0">
                                        {{ __tr('Please configure your Facebook API settings to start using Facebook messaging.') }}
                                        <a href="{{ route('vendor.settings.read', ['pageType' => 'facebook-api-setup']) }}" class="alert-link">
                                            {{ __tr('Configure Now') }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Facebook Chat Interface -->
<div id="lwFacebookChatWindow" class=" lw-whatsapp-chat-window ">


                            <div class="row g-0">
                                <!-- Contact List -->
                                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 mb-4 lw-contact-list-block">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h1>{{ __tr('Facebook') }}</h1>
                                        <button class="btn btn-sm btn-outline-primary" id="refresh-button">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>

                                    <!-- Toggle between Chats and Comments -->
                                    <div class="btn-group w-100 mb-3" role="group">
                                        <input type="radio" class="btn-check mr-2" name="facebook-mode" id="chat-mode" autocomplete="off" checked style="appearance: none;">
                                        <label class="btn btn-outline-primary mr-2" for="chat-mode">
                                            <i class="fas fa-comments me-1"></i>{{ __tr('Chats') }}
                                        </label>

                                        <input type="radio" class="btn-check" name="facebook-mode" id="comments-mode" autocomplete="off" style="appearance: none;">
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

                                    <!-- Facebook Conversations (Chat Mode) -->
                                    <div id="conversations-section" class="list-group lw-contact-list" style="max-height: 500px; overflow-y: auto;">
                                        <!-- Loading State -->
                                        <div class="text-center p-4 loading-state" style="display: none;">
                                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('Loading conversations...') }}</p>
                                        </div>

                                        <!-- No Conversations -->
                                        <div class="text-center p-4 no-conversations" style="display: none;">
                                            <i class="fab fa-facebook fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('No conversations found') }}</p>
                                        </div>

                                        <!-- Conversations List Container -->
                                        <div id="conversationsList" class="conversations-container"></div>
                                    </div>

                                    <!-- Facebook Posts (Comments Mode) -->
                                    <div id="posts-section" class="list-group lw-contact-list" style="max-height: 500px; overflow-y: auto; display: none;">
                                        <!-- Loading State -->
                                        <div class="text-center p-4 posts-loading-state" style="display: none;">
                                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('Loading posts...') }}</p>
                                        </div>

                                        <!-- No Posts -->
                                        <div class="text-center p-4 no-posts" style="display: none;">
                                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">{{ __tr('No posts found') }}</p>
                                        </div>

                                        <!-- Posts List -->
                                        <div id="postsList"></div>
                                    </div>
                                </div>

                                <!-- Chat Window -->
                                <div class="col-sm-12 col-md-9 col-lg-9 col-xl-9 mb-4">
                                    <div id="chat-window-container">
                                        <!-- Empty State for Chat Mode -->
                                        <div id="empty-state" class="text-center p-5">
                                            <i class="fab fa-facebook fa-3x text-primary mb-3"></i>
                                            <h5 class="text-muted">{{ __tr('Select a conversation') }}</h5>
                                            <p class="text-muted">{{ __tr('Choose a conversation from the list to start chatting') }}</p>
                                        </div>

                                        <!-- Empty State for Comments Mode -->
                                        <div id="comments-empty-state" class="text-center p-5" style="display: none;">
                                            <i class="fas fa-comment-dots fa-3x text-primary mb-3"></i>
                                            <h5 class="text-muted">{{ __tr('Select a post') }}</h5>
                                            <p class="text-muted">{{ __tr('Choose a post from the list to view and reply to comments') }}</p>
                                        </div>

                                        <div id="chat-container" class="chat-container" style="display: none;">
                                            <div class="chat-header bg-white p-3 border-bottom">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-primary text-white text-center me-3">
                                                        <span id="chat-header-initials">FB</span>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0" id="chat-header-username">Facebook User</h5>
                                                        <small class="text-muted">Facebook Contact</small>
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

                                            <div class="fb-chat-input bg-white p-3 border-top">
                                                <form id="message-form">
                                                    @csrf
                                                    
                                                        <input type="text" 
                                                               id="message-input"
                                                               class="form-control" 
                                                               placeholder="{{ __tr('Type a message') }}">
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
                                                  <small class="source-label">Facebook Media</small>
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
                                            <div class="fb-comments-input">
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
/* Facebook-like Theme */
:root {
    --fb-primary: #1877f2;
    --fb-secondary: #262626;
    --fb-border: #dbdbdb;
    --fb-background: #fafafa;
    --fb-text: #262626;
    --fb-text-secondary: #8e8e8e;
    --fb-white: #ffffff;
    --fb-danger: #ed4956;
    --fb-success: #42b883;
}

/* Facebook Gradient Header */
.bg-gradient-facebook {
    background: linear-gradient(45deg, #1877f2 0%, #42a5f5 50%, #1565c0 100%);
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
    background: var(--ig-background);
    color: var(--fb-text);
    line-height: 1.4;
}

.message.sent .message-content {
    background: var(--fb-primary);
    color: var(--fb-white);
}

.message .username {
    font-size: 0.75rem;
    color: var(--fb-text-secondary);
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
    background: linear-gradient(45deg, #1877f2 0%, #42a5f5 50%, #1565c0 100%);
    padding: 2px;
}

.lw-contact-avatar span {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(45deg, #1877f2 0%, #42a5f5 50%, #1565c0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--fb-white);
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--fb-background);
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

/* Custom Scrollbar */
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

/* Loading Animation */
.loading-state .fa-spinner {
    color: var(--ig-primary) !important;
}

/* Alert Styling */
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

/* Search Input */
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

/* Empty State */
#empty-state {
    color: var(--ig-text-secondary);
}

#empty-state i {
    background: linear-gradient(45deg, #1877f2 0%, #42a5f5 50%, #1565c0 100%);
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
    background-color: #bebec2ff !important;
}

.fb-chat-input {
  padding: 12px 16px;
  background-color: #fff;
  border-top: 1px solid #dbdbdb;
}

.fb-chat-input form {
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
.fb-comments-input {
  padding: 12px 16px;
  background-color: #fff;
  border-top: 1px solid #dbdbdb;
}

.fb-comments-input form {
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

/* Comments and Replies Styling */
.comment-thread {
    margin-bottom: 1.5rem;
}

.main-comment {
    background: var(--fb-white);
    border: 1px solid var(--fb-border);
}

.reply-item {
    background: rgba(24, 119, 242, 0.05) !important;
    border-left: 3px solid var(--fb-primary) !important;
    margin-left: 3rem;
    margin-top: 0.5rem;
    padding: 0.75rem;
    border-radius: 0.375rem;
}

.reply-item .avatar {
    width: 30px;
    height: 30px;
    line-height: 30px;
    font-size: 0.75rem;
}

.replies-container {
    margin-top: 0.5rem;
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
    let posts = [];
    let selectedPost = null;
    let selectedComment = null;
    let selectedReplyType = 'private'; // 'private' or 'public'
    let commentsRefreshInterval = null;

    // Initialize
    loadConversations();

    // Handle mode toggle
    $('input[name="facebook-mode"]').on('change', function() {
        const mode = $(this).attr('id').replace('-mode', '');
        switchMode(mode);
    });

    // Handle refresh button
    $('#refresh-button').on('click', function() {
        if (currentMode === 'chat') {
            loadConversations();
        } else {
            loadPosts();
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

        return fetch('{{ route("vendor.facebook_chat.conversations") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Facebook Conversations API response:', data);
                
                if (!data.success) {
                    console.error('API returned error:', data.message);
                    showAlert('Failed to load conversations: ' + (data.message || 'Unknown error'), 'danger');
                    $('.no-conversations').show();
                    return [];
                }

                contacts = data.data || [];
                console.log('Loaded conversations:', contacts.length, contacts);
                
                if (contacts.length === 0) {
                    console.warn('No conversations found');
                    $('.no-conversations').show();
                }
                
                updateConversationsList();
                return contacts; // Return the contacts for chaining
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
                showAlert('Failed to load conversations: ' + error.message, 'danger');
                $('.no-conversations').show();
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

        contacts.forEach(conversation => {
            try {
                // Handle both data structures (direct and nested)
                const participants = conversation.participants?.data || conversation.participants || [];
                const pageId = '{{ getVendorSettings('facebook_page_id') }}';
                
                // Find the other participant (not the page)
                let otherParticipant = {};
                if (Array.isArray(participants) && participants.length > 0) {
                    otherParticipant = participants.find(p => p.id !== pageId) || participants[0] || {};
                }
                
                // Extract username from conversation data - similar to Instagram
                // Prioritize full_name, then sender_name, then facebook_username, then participant name
                let username = 'Facebook User';
                if (conversation.full_name) {
                    username = conversation.full_name;
                } else if (conversation.sender_name) {
                    username = conversation.sender_name;
                } else if (conversation.facebook_username) {
                    username = conversation.facebook_username;
                } else if (otherParticipant.name) {
                    username = otherParticipant.name;
                } else if (otherParticipant.username) {
                    username = otherParticipant.username;
                } else if (conversation.name) {
                    username = conversation.name;
                } else if (conversation.last_message_preview) {
                    // Try to extract name from snippet if available
                    const snippet = conversation.last_message_preview;
                    if (snippet && typeof snippet === 'string' && snippet.includes(':')) {
                        username = snippet.split(':')[0].trim();
                    }
                }
                
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
            const pageId = '{{ getVendorSettings('facebook_page_id') }}';
            const otherParticipant = participants.find(p => p.id !== pageId) || {};
            
            // Extract username - same logic as in updateConversationsList
            let username = 'Facebook User';
            if (conversation.full_name) {
                username = conversation.full_name;
            } else if (conversation.sender_name) {
                username = conversation.sender_name;
            } else if (conversation.facebook_username) {
                username = conversation.facebook_username;
            } else if (otherParticipant.name) {
                username = otherParticipant.name;
            } else if (otherParticipant.username) {
                username = otherParticipant.username;
            } else if (conversation.name) {
                username = conversation.name;
            }
            
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

        fetch(`/vendor-console/facebook-chat/conversation/${conversationId}/messages`)
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
        fetch(`/vendor-console/facebook-chat/conversation/${conversationId}/send`, {
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
        }
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
            $('#posts-section').hide();
            $('#empty-state').show();
            $('#comments-empty-state').hide();
            $('#chat-container').hide();
            $('#comments-container').hide();

            // Update button states
            $('#chat-mode').prop('checked', true);
            $('#comments-mode').prop('checked', false);

            // Load conversations if not already loaded
            if (contacts.length === 0) {
                loadConversations();
            }
        } else if (mode === 'comments') {
            // Show comments sections, hide chat sections
            $('#conversations-section').hide();
            $('#posts-section').show();
            $('#empty-state').hide();
            $('#comments-empty-state').show();
            $('#chat-container').hide();
            $('#comments-container').hide();

            // Update button states
            $('#chat-mode').prop('checked', false);
            $('#comments-mode').prop('checked', true);

            // Load posts if not already loaded
            if (posts.length === 0) {
                loadPosts();
            }

            // If a post is already selected, restart the refresh interval
            if (selectedPost) {
                if (commentsRefreshInterval) {
                    clearInterval(commentsRefreshInterval);
                }
                commentsRefreshInterval = setInterval(() => {
                    if (selectedPost && currentMode === 'comments') {
                        loadPostComments(selectedPost.id);
                    }
                }, 30000);
            }
        }
    }

    // Load Facebook posts
    function loadPosts() {
        $('.posts-loading-state').show();
        $('.no-posts').hide();

        fetch('{{ route("vendor.facebook_chat.posts") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Facebook Posts API response:', data);
                
                if (!data.success) {
                    console.error('API returned error:', data.message);
                    showAlert('Failed to load posts: ' + (data.message || 'Unknown error'), 'danger');
                    $('.no-posts').show();
                    return;
                }

                posts = data.data?.posts || data.data || [];
                console.log('Loaded posts:', posts.length, posts);
                
                if (posts.length === 0) {
                    console.warn('No posts found');
                    $('.no-posts').show();
                }
                
                updatePostsList();
            })
            .catch(error => {
                console.error('Error loading posts:', error);
                showAlert('Failed to load posts: ' + error.message, 'danger');
                $('.no-posts').show();
            })
            .finally(() => {
                $('.posts-loading-state').hide();
            });
    }

    // Update posts list
    function updatePostsList() {
        const $list = $('#postsList');
        $list.empty();

        if (!posts || posts.length === 0) {
            console.warn('No posts to display');
            $('.no-posts').show();
            return;
        }

        console.log('Displaying posts:', posts.length);
        
        // Sort posts by created_time (newest first) if not already sorted
        const sortedPosts = posts.slice().sort((a, b) => {
            const timeA = new Date(a.created_time || 0).getTime();
            const timeB = new Date(b.created_time || 0).getTime();
            return timeB - timeA;
        });

        sortedPosts.forEach((post, index) => {
            const message = post.message || post.story || 'No content';
            const shortMessage = message.length > 50 ? message.substring(0, 50) + '...' : message;
            const timeAgo = post.formatted_time || post.created_time || 'Unknown time';
            
            // Ensure post has an ID
            if (!post.id) {
                console.warn('Post missing ID:', post);
                return;
            }

            const $item = $(`
                <a href="#" class="list-group-item list-group-item-action post-item" data-id="${post.id}">
                    <div class="d-flex w-100 align-items-center">
                        <div class="lw-contact-avatar bg-primary text-white text-center me-3">
                            <i class="fas fa-file-text"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1 ml-2">${shortMessage}</h4>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                    </div>
                </a>
            `);

            $list.append($item);
        });
        
        console.log('Posts displayed:', $list.children().length);

        // Handle post selection
        $('.post-item').on('click', function(e) {
            e.preventDefault();
            const postId = $(this).data('id');
            const post = posts.find(p => p.id === postId);

            if (post) {
                selectPost(post);
            }
        });
    }

    // Select post and load comments
    function selectPost(post) {
        selectedPost = post;

        // Clear any existing refresh interval
        if (commentsRefreshInterval) {
            clearInterval(commentsRefreshInterval);
            commentsRefreshInterval = null;
        }

        // Update header
        const message = post.message || post.story || 'No content';
        const shortMessage = message.length > 30 ? message.substring(0, 30) + '...' : message;
        $('#comments-header-caption').text(shortMessage);

        // Show comments container
        $('#comments-empty-state').hide();
        $('#comments-container').show();

        // Load comments for this post
        loadPostComments(post.id);

        // Set up auto-refresh every 30 seconds
        commentsRefreshInterval = setInterval(() => {
            if (selectedPost && currentMode === 'comments') {
                loadPostComments(selectedPost.id);
            }
        }, 30000); // Refresh every 30 seconds
    }

    // Load comments for a post
    function loadPostComments(postId) {
        $('#comments-loading').show();
        $('#no-comments').hide();
        
        // Don't clear the list on refresh - let updateCommentsList handle it
        if (!$('#comments-list').children().length) {
            $('#comments-list').empty();
        }

        fetch(`/vendor-console/facebook-chat/post/${postId}/comments`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Facebook Comments API response:', data);
                
                if (!data.success) {
                    console.error('API returned error:', data.message);
                    showAlert('Failed to load comments: ' + (data.message || 'Unknown error'), 'danger');
                    if ($('#comments-list').children().length === 0) {
                        $('#no-comments').show();
                    }
                    return;
                }

                // The controller returns data directly, not nested in data.data
                const comments = data.data || [];
                console.log('Loaded comments:', comments.length, comments);
                
                if (comments.length === 0 && $('#comments-list').children().length === 0) {
                    console.warn('No comments found for post:', postId);
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

    // Update comments list with nested replies
    function updateCommentsList(comments) {
        const $list = $('#comments-list');
        
        // Store existing comment IDs to detect new comments
        const existingCommentIds = new Set();
        $list.find('.comment-thread').each(function() {
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

        const commentIdsInResponse = new Set();

        comments.forEach(comment => {
            // Ensure comment ID is always a string
            const commentId = String(comment.id || '');
            
            if (!commentId) {
                console.error('Comment missing ID:', comment);
                return; // Skip comments without IDs
            }

            commentIdsInResponse.add(commentId);
            
            // Check if this comment already exists
            if (!isFirstLoad && existingCommentIds.has(commentId)) {
                return; // Skip existing comments on refresh
            }

            const username = comment.commenter_name || 'Facebook User';
            const text = comment.message || '';
            const timeAgo = comment.formatted_time || 'Unknown time';

            // Create main comment
            const $comment = $(`
                <div class="comment-thread mb-4" data-comment-id="${commentId}">
                    <div class="main-comment p-3 border rounded">
                        <div class="d-flex align-items-start">
                            <div class="avatar bg-secondary text-white text-center me-3" style="width: 40px; height: 40px; line-height: 40px; border-radius: 50%;">
                                <span>${getInitials(username)}</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1">${username}</h6>
                                    <small class="text-muted">${timeAgo}</small>
                                </div>
                                <p class="mb-2">${text}</p>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary reply-comment-btn" data-comment-id="${commentId}" data-username="${username}" data-reply-type="private">
                                        <i class="fas fa-envelope me-1"></i>Private Reply
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary reply-comment-btn" data-comment-id="${commentId}" data-username="${username}" data-reply-type="public">
                                        <i class="fas fa-reply me-1"></i>Public Reply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="replies-container"></div>
                </div>
            `);

            // Add replies if they exist
            if (comment.replies && comment.replies.length > 0) {
                const $repliesContainer = $comment.find('.replies-container');

                comment.replies.forEach(reply => {
                    const replyUsername = reply.commenter_name || 'Facebook User';
                    const replyText = reply.message || '';
                    const replyTimeAgo = reply.formatted_time || 'Unknown time';
                    const isPageReply = reply.commenter_id === '{{ getVendorSettings('facebook_page_id') }}';

                    const $reply = $(`
                        <div class="reply-item ms-5 mt-2 p-2 border-start border-3 border-primary bg-light rounded">
                            <div class="d-flex align-items-start">
                                <div class="avatar ${isPageReply ? 'bg-primary' : 'bg-info'} text-white text-center me-2" style="width: 30px; height: 30px; line-height: 30px; border-radius: 50%; font-size: 0.75rem;">
                                    <span>${getInitials(replyUsername)}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1 small">${replyUsername} ${isPageReply ? '(Page)' : ''}</h6>
                                        <small class="text-muted">${replyTimeAgo}</small>
                                    </div>
                                    <p class="mb-0 small">${replyText}</p>
                                </div>
                            </div>
                        </div>
                    `);

                    $repliesContainer.append($reply);
                });
            }

            $list.append($comment);
        });

        // Remove comments that are no longer in the response (if any)
        if (!isFirstLoad) {
            $list.find('.comment-thread').each(function() {
                const commentId = String($(this).data('comment-id') || '');
                if (!commentIdsInResponse.has(commentId)) {
                    $(this).remove();
                }
            });
        }

        // Reattach click handlers for all reply buttons (including new ones)
        $('.reply-comment-btn').off('click').on('click', function() {
            const commentId = String($(this).data('comment-id') || $(this).attr('data-comment-id') || '');
            const username = $(this).data('username') || $(this).attr('data-username') || 'Facebook User';
            const replyType = $(this).data('reply-type') || $(this).attr('data-reply-type') || 'private';
            if (commentId) {
                selectCommentForReply(commentId, username, replyType);
            } else {
                console.error('Comment ID not found');
                showAlert('Error: Comment ID not found', 'danger');
            }
        });

        // Scroll to bottom
        const container = document.getElementById('comments-messages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Select comment for reply
    function selectCommentForReply(commentId, username, replyType = 'private') {
        // Ensure commentId is a string
        commentId = String(commentId || '');
        
        if (!commentId) {
            console.error('Invalid comment ID provided');
            showAlert('Error: Invalid comment ID', 'danger');
            return;
        }
        
        // Remove previous selection highlight
        $('.comment-thread').removeClass('border-primary');
        
        selectedComment = commentId;
        selectedReplyType = replyType;
        
        // Highlight selected comment
        $(`.comment-thread[data-comment-id="${commentId}"]`).addClass('border-primary');

        if (replyType === 'private') {
            $('#comment-reply-input').attr('placeholder', `Send private message to ${username}...`);
            $('#comment-reply-button').html('<i class="fas fa-envelope"></i> Send Private Message');
        } else {
            $('#comment-reply-input').attr('placeholder', `Reply publicly to ${username}...`);
            $('#comment-reply-button').html('<i class="fas fa-reply"></i> Post Public Reply');
        }

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

    // Send comment reply
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

        const commentElement = $(`.comment-thread[data-comment-id="${commentId}"]`);
        const username = commentElement.find('.reply-comment-btn').data('username') || 
                         commentElement.find('.reply-comment-btn').attr('data-username') || 
                         'Facebook User';

        console.log('Sending Facebook reply:', { comment_id: commentId, message: message, username: username, reply_type: selectedReplyType });

        // Disable input and button while sending
        $input.prop('disabled', true);
        $('#comment-reply-button').prop('disabled', true);

        // Determine which API endpoint to use based on reply type
        const apiUrl = selectedReplyType === 'private'
            ? '{{ route("vendor.facebook_chat.comment.reply") }}'
            : '{{ route("vendor.facebook_chat.comment.reply_public") }}';

        fetch(apiUrl, {
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
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (selectedReplyType === 'private') {
                    showAlert(`Private message sent to ${username}`, 'success');
                    // Clear input and reset
                    $input.val('');
                    $input.prop('disabled', false);
                    selectedComment = null;
                    selectedReplyType = 'private';
                    $('.comment-thread').removeClass('border-primary');
                    $('#comment-reply-input').attr('placeholder', 'Send message to start chat...');
                    $('#comment-reply-button').html('<i class="fas fa-paper-plane"></i>');
                    redirectToChatMode(username);
                } else {
                    showAlert(`Public reply posted to ${username}'s comment`, 'success');
                    // Clear input and reset
                    $input.val('');
                    $input.prop('disabled', false);
                    selectedComment = null;
                    selectedReplyType = 'private';
                    $('.comment-thread').removeClass('border-primary');
                    $('#comment-reply-input').attr('placeholder', 'Send message to start chat...');
                    $('#comment-reply-button').html('<i class="fas fa-paper-plane"></i>');
                    // Reload comments to show the new public reply
                    if (selectedPost) {
                        loadPostComments(selectedPost.id);
                    }
                }
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

    // Redirect to chat mode
    function redirectToChatMode(username) {
        $('#chat-mode').prop('checked', true);
        switchMode('chat');
        loadConversations();
        selectedComment = null;
        selectedReplyType = 'private';
        $('#comment-reply-input').attr('placeholder', 'Send message to start chat...');
        $('#comment-reply-button').html('<i class="fas fa-paper-plane"></i>');
    }





    // Filter contacts
    function filterContacts(searchTerm) {
        try {
            const filtered = contacts.filter(conversation => {
                try {
                    // Handle Facebook conversation structure
                    const participants = conversation.participants?.data || conversation.participants || [];
                    const facebookPageName = '{{ getVendorSettings('facebook_page_name') }}';
                    const otherParticipant = participants.find(p => p.name !== facebookPageName) || {};

                    // Use fallback values if data is missing
                    const username = otherParticipant.name || conversation.facebook_username || '';
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

            filtered.forEach(conversation => {
                try {
                    // Handle Facebook conversation structure
                    const participants = conversation.participants?.data || conversation.participants || [];
                    const facebookPageName = '{{ getVendorSettings('facebook_page_name') }}';
                    const otherParticipant = participants.find(p => p.name !== facebookPageName) || {};

                    // Use fallback values if data is missing
                    const username = otherParticipant.name || conversation.facebook_username || 'Facebook User';
                    const conversationId = conversation.id || conversation.conversation_id;

                    if (!conversationId) {
                        console.warn('Missing conversation ID:', conversation);
                        return;
                    }

                    const $item = $(`
                        <a href="#" class="list-group-item list-group-item-action conversation-item" data-id="${conversationId}">
                            <div class="d-flex w-100 align-items-center">
                                <div class="lw-contact-avatar bg-primary text-white text-center me-3">
                                    <span>${getInitials(username)}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${username}</h6>
                                    <small class="text-muted">Facebook conversation</small>
                                </div>
                            </div>
                        </a>
                    `);

                    $list.append($item);
                } catch (error) {
                    console.error('Error processing filtered conversation:', error, conversation);
                }
            });

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
        if (!name) return 'FB';
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