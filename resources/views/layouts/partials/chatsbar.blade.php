{{-- Chat Sidebar Navigation --}}
<div class="chat-sidebar" id="chatSidebar" style="display: none;">
    <div class="chat-sidebar-header">
        <h5 class="mb-0">
            <i class="fas fa-comments"></i>
            {{ __tr('Quick Chat') }}
        </h5>
        <button type="button" class="btn-close" id="closeChatSidebar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    
    <div class="chat-sidebar-content">
        <div class="chat-platforms">
            <div class="platform-item">
                <a href="{{ route('vendor.chat_message.contact.view') }}" class="platform-link">
                    <i class="fab fa-whatsapp text-success"></i>
                    <span>{{ __tr('WhatsApp') }}</span>
                </a>
            </div>
            
            <div class="platform-item">
                <a href="{{ route('vendor.facebook.contact.chat.view') }}" class="platform-link">
                    <i class="fab fa-facebook text-primary"></i>
                    <span>{{ __tr('Facebook') }}</span>
                </a>
            </div>
            
            <div class="platform-item">
                <a href="{{ route('vendor.instagram.contact.chat.view') }}" class="platform-link">
                    <i class="fab fa-instagram text-danger"></i>
                    <span>{{ __tr('Instagram') }}</span>
                </a>
            </div>
        </div>
        
        <div class="chat-stats">
            <div class="stat-item">
                <span class="stat-label">{{ __tr('Unread') }}</span>
                <span class="stat-value" id="unreadCount">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">{{ __tr('Active') }}</span>
                <span class="stat-value" id="activeCount">0</span>
            </div>
        </div>
    </div>
</div>

<style>
.chat-sidebar {
    position: fixed;
    top: 0;
    right: 0;
    width: 300px;
    height: 100vh;
    background: #fff;
    border-left: 1px solid #e9ecef;
    z-index: 1050;
    box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.chat-sidebar.show {
    transform: translateX(0);
}

.chat-sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.chat-sidebar-content {
    padding: 1rem;
}

.chat-platforms {
    margin-bottom: 2rem;
}

.platform-item {
    margin-bottom: 0.5rem;
}

.platform-link {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    text-decoration: none;
    color: #495057;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.platform-link:hover {
    background: #f8f9fa;
    color: #495057;
    text-decoration: none;
}

.platform-link i {
    margin-right: 0.75rem;
    font-size: 1.2rem;
}

.chat-stats {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.stat-value {
    font-weight: 600;
    color: #495057;
}

@media (max-width: 768px) {
    .chat-sidebar {
        width: 100%;
    }
}
</style>

<script>
// Chat sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const chatSidebar = document.getElementById('chatSidebar');
    const closeChatSidebar = document.getElementById('closeChatSidebar');
    
    if (closeChatSidebar) {
        closeChatSidebar.addEventListener('click', function() {
            chatSidebar.classList.remove('show');
        });
    }
    
    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        if (chatSidebar && !chatSidebar.contains(event.target)) {
            chatSidebar.classList.remove('show');
        }
    });
    
    // Function to show sidebar
    window.showChatSidebar = function() {
        if (chatSidebar) {
            chatSidebar.classList.add('show');
        }
    };
    
    // Function to hide sidebar
    window.hideChatSidebar = function() {
        if (chatSidebar) {
            chatSidebar.classList.remove('show');
        }
    };
});
</script> 