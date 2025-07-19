@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Sidebar - Screens List -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Flow Screens</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="addScreenBtn">
                        <i class="fas fa-plus-circle"></i> Add
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="screensList">
                        @forelse($flow->screens->sortBy('position') as $screen)
                            <a href="#" class="list-group-item list-group-item-action screen-item" 
                               data-screen-id="{{ $screen->_id }}">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $screen->name }}</strong>
                                        <small class="d-block text-muted">{{ $screen->screen_type }}</small>
                                    </div>
                                    <span class="badge bg-secondary">{{ $screen->position }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-center p-3">
                                <p class="text-muted">No screens yet</p>
                                <p>Start by adding your first screen</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Editor Area -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4>{{ $flow->name }} 
                                <small class="text-muted">({{ ucfirst($flow->status) }})</small>
                            </h4>
                            <span class="badge bg-{{ $flow->whatsapp_sync_status === 'synced' ? 'success' : 'warning' }}">
                                WhatsApp: {{ ucfirst($flow->whatsapp_sync_status) }}
                            </span>
                        </div>
                        <div>
                            @if($flow->whatsapp_sync_status !== 'synced')
                                <button class="btn btn-sm btn-outline-primary sync-with-whatsapp" 
                                        data-flow-id="{{ $flow->_id }}">
                                    <i class="fas fa-sync"></i> Sync with WhatsApp
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="screenEditor">
                        @if($flow->screens->isEmpty())
                            <div class="text-center p-5">
                                <h5>Welcome to the Flow Editor</h5>
                                <p>Create your first screen to start building your WhatsApp flow.</p>
                                <button class="btn btn-primary" id="createFirstScreenBtn">Create First Screen</button>
                            </div>
                        @else
                            <h5>Select a screen from the sidebar to edit</h5>
                        @endif
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('vendor.flow.read.list_view') }}" class="btn btn-secondary">Back to Dashboard</a>
                    <div>
                        <a href="{{ route('vendor.flow.read.preview', ['id' => $flow->_id]) }}" class="btn btn-info">Preview</a>
                        @if($flow->status !== 'published')
                            <form action="{{ route('vendor.flow.write.publish', ['id' => $flow->_id]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">Publish Flow</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Preview Panel -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Phone Preview</h5>
                </div>
                <div class="card-body p-0">
                    <div class="phone-preview">
                        <div class="phone-header">
                            <div class="phone-status-bar"></div>
                            <div class="phone-contact">
                                <i class="fab fa-whatsapp text-success"></i>
                                <span class="ms-2">WhatsApp Business</span>
                            </div>
                        </div>
                        <div class="phone-content" id="previewContent">
                            <div class="text-center p-4">
                                <p class="text-muted">Select a screen to preview</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Screen Modal -->
<div class="modal fade" id="screenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Screen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="screenForm">
                    @csrf
                    <input type="hidden" name="flow_id" value="{{ $flow->_id }}">
                    <input type="hidden" id="screenPosition" name="position" value="1">
                    
                    <div class="mb-3">
                        <label for="screenName" class="form-label">Screen Name</label>
                        <input type="text" class="form-control" id="screenName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="screenType" class="form-label">Screen Type</label>
                        <select class="form-select" id="screenType" name="screen_type" required>
                            <option value="">Select Type</option>
                            <option value="TEXT">Text</option>
                            <option value="LIST">List</option>
                            <option value="FORM">Form</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="screenContent" class="form-label">Content</label>
                        <textarea class="form-control" id="screenContent" name="content" rows="4" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="screenFooter" class="form-label">Footer (Optional)</label>
                        <input type="text" class="form-control" id="screenFooter" name="footer" maxlength="60">
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveScreenBtn">Save Screen</button>
            </div>
        </div>
    </div>
</div>

<!-- Button Modal -->
<div class="modal fade" id="buttonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Button</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="buttonForm">
                    @csrf
                    <input type="hidden" id="currentScreenId" name="screen_id">
                    
                    <div class="mb-3">
                        <label for="buttonText" class="form-label">Button Text (max 20 chars)</label>
                        <input type="text" class="form-control" id="buttonText" name="text" maxlength="20" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="buttonType" class="form-label">Button Type</label>
                        <select class="form-select" id="buttonType" name="button_type" required>
                            <option value="">Select Type</option>
                            <option value="NEXT">Navigate to Next Screen</option>
                            <option value="URL">Open URL</option>
                            <option value="PHONE">Call Phone Number</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3 button-next-options">
                        <label for="nextScreenId" class="form-label">Next Screen</label>
                        <select class="form-select" id="nextScreenId" name="next_screen_id">
                            <option value="">Select a screen</option>
                            @foreach($flow->screens as $screen)
                                <option value="{{ $screen->_id }}">{{ $screen->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3 button-url-options" style="display: none;">
                        <label for="buttonUrl" class="form-label">URL</label>
                        <input type="url" class="form-control" id="buttonUrl" name="url" placeholder="https://example.com">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3 button-phone-options" style="display: none;">
                        <label for="buttonPhone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="buttonPhone" name="phone_number" placeholder="+1234567890">
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveButtonBtn">Save Button</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .phone-preview {
        border: 10px solid #222;
        border-radius: 25px;
        overflow: hidden;
        height: 600px;
        background: #f5f5f5;
    }
    
    .phone-header {
        background: #075e54;
        color: white;
        padding: 10px;
    }
    
    .phone-status-bar {
        height: 20px;
        background: #054c44;
    }
    
    .phone-contact {
        padding: 10px 0;
        font-size: 16px;
    }
    
    .phone-content {
        height: calc(100% - 74px);
        overflow-y: auto;
        background: #e4ddd6;
    }
    
    .message-bubble {
        max-width: 80%;
        padding: 8px 12px;
        border-radius: 7.5px;
        margin: 8px;
        position: relative;
    }
    
    .message-incoming {
        background: white;
        border-top-left-radius: 0;
        float: left;
    }
    
    .message-buttons {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 5px;
    }
    
    .message-button {
        background: #f7f7f7;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        padding: 8px;
        text-align: center;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Debug log to confirm script is running
    console.log('Editor script loaded');

    // Initialize Bootstrap modals
    const screenModal = new bootstrap.Modal(document.getElementById('screenModal'));
    
    // Add Screen button click handler with improved validation and feedback
    $('#addScreenBtn, #createFirstScreenBtn').on('click', function(e) {
        e.preventDefault();
        console.log('Add Screen button clicked');
        showScreenModal();
    });

    function showScreenModal() {
        resetScreenForm();
        screenModal.show();
    }

    function resetScreenForm() {
        const form = document.getElementById('screenForm');
        form.reset();
        $(form).find('.is-invalid').removeClass('is-invalid');
        $(form).find('.invalid-feedback').empty();
        
        // Set default values with validation
        $('#screenType').val('TEXT');
        $('#screenName').val('Welcome Screen');
        $('#screenContent').val('Welcome to your new screen!');
        $('#screenPosition').val($('#screensList .screen-item').length + 1);
    }

    // Save Screen button click handler with improved validation
    $('#saveScreenBtn').on('click', function(e) {
        e.preventDefault();
        console.log('Save Screen button clicked');

        const $saveBtn = $(this);
        const $form = $('#screenForm');
        
        // Validate required fields
        const name = $('#screenName').val();
        const type = $('#screenType').val();
        const content = $('#screenContent').val();
        
        let isValid = true;
        
        // Clear previous validation states
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').empty();
        
        // Validate name
        if (!name) {
            $('#screenName').addClass('is-invalid')
                .siblings('.invalid-feedback')
                .text('Screen name is required');
            isValid = false;
        }
        
        // Validate type
        if (!type) {
            $('#screenType').addClass('is-invalid')
                .siblings('.invalid-feedback')
                .text('Screen type is required');
            isValid = false;
        }
        
        // Validate content
        if (!content) {
            $('#screenContent').addClass('is-invalid')
                .siblings('.invalid-feedback')
                .text('Content is required');
            isValid = false;
        }
        
        if (!isValid) {
            return;
        }

        // Disable button to prevent double submission
        $saveBtn.prop('disabled', true);
        
        const formData = new FormData($form[0]);
        formData.append('flow_id', '{{ $flow->_id }}');
        formData.append('whatsapp_meta_data', JSON.stringify({
            screen_type: $('#screenType').val(),
            language: '{{ $flow->whatsapp_language ?? "en" }}',
            buttons_count: $('.button-item').length
        }));

        // Log form data for debugging
        const formDataObj = {};
        formData.forEach((value, key) => formDataObj[key] = value);
        console.log('Form data:', formDataObj);

        $.ajax({
            url: '{{ route("vendor.flow.screen.write.create", ["flowIdOrUid" => $flow->_id]) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log('Server response:', response);

                if (response.reaction === 1 && response.data.screen) {
                    const newScreen = response.data.screen;
                    
                    // Remove empty state if exists
                    $('#screensList .text-center.p-3').remove();
                    
                    // Add new screen to list with animation
                    const screenItem = `
                        <a href="#" class="list-group-item list-group-item-action screen-item" 
                           data-screen-id="${newScreen._id}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>${newScreen.name}</strong>
                                    <small class="d-block text-muted">${newScreen.screen_type}</small>
                                </div>
                                <span class="badge bg-secondary">${newScreen.position}</span>
                            </div>
                        </a>
                    `;
                    
                    const $newItem = $(screenItem).hide();
                    $('#screensList').append($newItem);
                    $newItem.fadeIn();
                    
                    screenModal.hide();
                    
                    // Update editor
                    loadScreenEditor(newScreen._id);
                    
                    // Show success message
                    if (window.__Utils && window.__Utils.notification) {
                        window.__Utils.notification('Screen created successfully', 'success');
                    }
                } else {
                    console.error('Invalid response:', response);
                    if (window.__Utils && window.__Utils.notification) {
                        window.__Utils.notification(response.data?.message || 'Error creating screen', 'error');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', {xhr, status, error});
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        $(`#${field}`).addClass('is-invalid')
                            .siblings('.invalid-feedback')
                            .text(errors[field][0]);
                    });
                }
                
                if (window.__Utils && window.__Utils.notification) {
                    window.__Utils.notification('Failed to create screen', 'error');
                }
            },
            complete: function() {
                $saveBtn.prop('disabled', false);
            }
        });
    });

    // Load screen editor
    function loadScreenEditor(screenId) {
        $.ajax({
            url: '{{ route("vendor.flow.screen.read.data", ["screenIdOrUid" => "__ID__"]) }}'.replace('__ID__', screenId),
            type: 'GET',
            success: function(response) {
                if (response.reaction === 1) {
                    renderScreenEditor(response.data.screen);
                    renderScreenPreview(response.data.screen);
                } else {
                    console.error('Failed to load screen:', response);
                    if (window.__Utils && window.__Utils.notification) {
                        window.__Utils.notification('Failed to load screen', 'error');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading screen:', error);
                if (window.__Utils && window.__Utils.notification) {
                    window.__Utils.notification('Failed to load screen', 'error');
                }
            }
        });
    }

    // Screen item click handler
    $(document).on('click', '.screen-item', function(e) {
        e.preventDefault();
        const screenId = $(this).data('screen-id');
        loadScreenEditor(screenId);
    });

    function renderScreenEditor(screen) {
        const buttons = screen.buttons.map(button => {
            let buttonDetails = '';
            
            if (button.button_type === 'NEXT') {
                buttonDetails = `Navigate to ${button.next_screen ? button.next_screen.name : 'Unknown'}`;
            } else if (button.button_type === 'URL') {
                buttonDetails = `Open ${button.url}`;
            } else if (button.button_type === 'PHONE') {
                buttonDetails = `Call ${button.phone_number}`;
            }
            
            return `
                <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${button.text}</strong>
                        <small class="d-block text-muted">${buttonDetails}</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-primary edit-button" data-button-id="${button._id}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-button" data-button-id="${button._id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        
        const editorHtml = `
            <div class="d-flex justify-content-between mb-4">
                <h5>${screen.name}</h5>
                <div>
                    <button class="btn btn-sm btn-outline-secondary edit-screen" data-screen-id="${screen._id}">
                        <i class="fas fa-pencil-alt"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-screen" data-screen-id="${screen._id}">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Type</label>
                <div>${screen.screen_type}</div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Content</label>
                <div class="p-2 border rounded bg-light">${screen.content}</div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Footer</label>
                <div>${screen.footer || '<em>No footer</em>'}</div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Buttons</label>
                    <button class="btn btn-sm btn-primary add-button" data-screen-id="${screen._id}">
                        <i class="fas fa-plus-circle"></i> Add Button
                    </button>
                </div>
                <div class="buttons-container">
                    ${buttons.length ? buttons : '<div class="text-muted">No buttons added yet</div>'}
                </div>
            </div>
        `;
        
        $('#screenEditor').html(editorHtml);
        $('#currentScreenId').val(screen._id);
    }
    
    function renderScreenPreview(screen) {
        let buttonsHtml = '';
        
        if (screen.buttons.length) {
            buttonsHtml = '<div class="message-buttons">';
            screen.buttons.forEach(button => {
                buttonsHtml += `<div class="message-button">${button.text}</div>`;
            });
            buttonsHtml += '</div>';
        }
        
        let footerHtml = '';
        if (screen.footer) {
            footerHtml = `<div class="text-muted small">${screen.footer}</div>`;
        }
        
        const previewHtml = `
            <div class="message-bubble message-incoming">
                <div>${screen.content}</div>
                ${footerHtml}
                ${buttonsHtml}
            </div>
        `;
        
        $('#previewContent').html(previewHtml);
    }

    // Add WhatsApp sync functionality
    $('.sync-with-whatsapp').on('click', function() {
        const flowId = $(this).data('flow-id');
        const $btn = $(this);
        
        $btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i> Syncing...');

        $.ajax({
            url: '{{ route("vendor.flow.write.sync_whatsapp", ["id" => "__ID__"]) }}'.replace('__ID__', flowId),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.reaction === 1) {
                    window.__Utils.notification('Flow synced with WhatsApp successfully', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.__Utils.notification(response.data.message || 'Sync failed', 'error');
                    $btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Retry Sync');
                }
            },
            error: function() {
                window.__Utils.notification('Failed to sync with WhatsApp', 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Retry Sync');
            }
        });
    });
});
</script>
@endpush

@endsection 