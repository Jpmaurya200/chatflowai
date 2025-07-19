<!-- resources/views/whatsapp/flow.blade.php -->
@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                    @endif
                    
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                    @endif
                    
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">WhatsApp Flows</h4>
                    <div>
                        <button type="button" class="btn btn-info me-2" id="syncManagerFlows">
                            <i class="fas fa-sync"></i> Sync from WhatsApp Manager
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFlowModal">
                            <i class="fas fa-plus-circle"></i> Create New Flow
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>WhatsApp Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                                @forelse($flows as $flow)
                                <tr>
                                    <td>{{ $flow->name }}</td>
                                    <td>{{ $flow->category }}</td>
                                    <td>
                                            <span class="badge bg-{{ $flow->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($flow->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $flow->whatsapp_sync_status === 'synced' ? 'success' : 'warning' }}">
                                            {{ ucfirst($flow->whatsapp_sync_status) }}
                                        </span>
                                        @if($flow->whatsapp_flow_id)
                                        <small class="d-block text-muted">ID: {{ $flow->whatsapp_flow_id }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $flow->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('vendor.flow.builder.read.view', ['id' => $flow->_id]) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Edit Flow">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('vendor.flow.read.preview', ['id' => $flow->_id]) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Preview Flow">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @if($flow->status !== 'published')
                                                <form action="{{ route('vendor.flow.write.publish', ['id' => $flow->_id]) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-success" 
                                                            title="Publish Flow">
                                                        <i class="fas fa-cloud-upload-alt"></i>
                                                    </button>
                                            </form>
                                            @endif
                                            @if($flow->status === 'published')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary start-flow" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#startFlowModal" 
                                                        data-flow-id="{{ $flow->_id }}"
                                                        title="Start Flow">
                                                    <i class="fas fa-play-circle"></i>
                                            </button>
                                            @endif
                                            @if($flow->status === 'published' && $flow->whatsapp_sync_status !== 'synced')
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info sync-flow" 
                                                    data-flow-id="{{ $flow->_id }}"
                                                    title="Sync with WhatsApp">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            @endif
                                            <a href="javascript:void(0)" 
                                               class="btn btn-sm btn-outline-danger lw-ajax-link-action"
                                               data-method="post"
                                               data-action="{{ route('vendor.flow.write.delete', ['id' => $flow->_id]) }}"
                                               data-callback="onFlowDeleted"
                                               data-flow-name="{{ $flow->name }}"
                                               data-confirm="#deleteFlowConfirm-template"
                                               title="Delete Flow">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                        <td colspan="5" class="text-center">
                                            <p class="text-muted my-3">No flows available</p>
                                            <button type="button" 
                                                    class="btn btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#createFlowModal">
                                                <i class="fas fa-plus-circle"></i> Create Your First Flow
                                            </button>
                                        </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Flow Modal -->
<div class="modal fade" id="createFlowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vendor.flow.write.store.process') }}" method="POST" id="createFlowForm" class="lw-ajax-form lw-form">
                @csrf
            <div class="modal-header">
                    <h5 class="modal-title">Create New Flow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Flow Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="CUSTOMER_SERVICE">Customer Service</option>
                            <option value="SALES">Sales</option>
                            <option value="SUPPORT">Support</option>
                            <option value="MARKETING">Marketing</option>
                            <option value="OTHER">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="whatsapp_flow_type" class="form-label">WhatsApp Flow Type</label>
                        <select class="form-select" id="whatsapp_flow_type" name="whatsapp_flow_type" required>
                            <option value="LEAD_GENERATION">Lead Generation</option>
                            <option value="PROMOTIONAL">Promotional</option>
                            <option value="UTILITY">Utility</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createFlowBtn">Create Flow</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Start Flow Modal -->
<div class="modal fade" id="startFlowModal" tabindex="-1" aria-labelledby="startFlowModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="startFlowForm" method="POST">
                @csrf
                <input type="hidden" name="flow_id" value="">
            <div class="modal-header">
                    <h5 class="modal-title" id="startFlowModalLabel">Start Flow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                        <div class="form-text">Enter the phone number with country code (e.g., +1234567890)</div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Flow</button>
            </div>
                </form>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/template" id="deleteFlowConfirm-template">
    <h3>{{ __tr('Delete Flow') }}</h3>
    <p>{{ __tr('Are you sure you want to delete flow') }} "<strong>__flowName__</strong>"? {{ __tr('This action cannot be undone.') }}</p>
</script>

<script>
    function onFlowDeleted(response, $this) {
        if (response.reaction === 1) {
            const flowName = $this.attr('data-flow-name');
            window.__Utils.notification(`Flow "${flowName}" deleted successfully`, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            window.__Utils.notification(response.data.message || 'Failed to delete flow', 'error');
        }
    }

    $(document).ready(function() {
        // Add global AJAX CSRF setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Handle dynamic confirmation message
        $(document).on('click', '.lw-ajax-link-action', function() {
            const flowName = $(this).attr('data-flow-name');
            const template = $('#deleteFlowConfirm-template').html();
            const message = template.replace('__flowName__', flowName);
            $(this).attr('data-confirm-message', message);
        });

        // Store all route URLs in JavaScript variables
        const routes = {
            builderUrl: "{{ route('vendor.flow.builder.read.view', ['id' => ':id']) }}",
            startUrl: "{{ route('vendor.flow.write.start') }}",
            storeUrl: "{{ route('vendor.flow.write.store.process') }}"
        };

        $('.start-flow').click(function() {
            const flowId = $(this).data('flow-id');
            $('#startFlowForm').attr('action', routes.startUrl);
            $('#startFlowForm input[name="flow_id"]').val(flowId);
        });

        // Create Flow Form Handler
        $('#createFlowForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const $submitBtn = $('#createFlowBtn');
            
            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').empty();
            
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.reaction == 1) {
                        showNotification('Flow created successfully', 'success');
                        window.location.href = response.data.redirectUrl || window.location.href;
                    } else {
                        showNotification(response.data.message || 'Error creating flow', 'error');
                        $submitBtn.prop('disabled', false).html('Create Flow');
                    }
                },
                error: function(xhr) {
                    showNotification('Failed to create flow', 'error');
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(field => {
                            $(`#${field}`).addClass('is-invalid')
                                .siblings('.invalid-feedback')
                                .text(xhr.responseJSON.errors[field][0]);
                        });
                    }
                    $submitBtn.prop('disabled', false).html('Create Flow');
                }
            });
        });
        
        // Add WhatsApp sync handler
        $('.sync-flow').on('click', function() {
            const flowId = $(this).data('flow-id');
            const $btn = $(this);
            
            $btn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: '{{ route("vendor.flow.write.sync_whatsapp", ["id" => "__ID__"]) }}'.replace('__ID__', flowId),
                type: 'POST',
                success: function(response) {
                    if (response.reaction === 1) {
                        window.__Utils.notification('Flow synced with WhatsApp successfully', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        window.__Utils.notification(response.data.message || 'Sync failed', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-sync"></i>');
                    }
                },
                error: function() {
                    window.__Utils.notification('Failed to sync with WhatsApp', 'error');
                    $btn.prop('disabled', false).html('<i class="fas fa-sync"></i>');
                }
            });
        });

        // Update create flow form to include WhatsApp fields
        $('#createFlowForm').append(`
            <input type="hidden" name="whatsapp_flow_type" value="LEAD_GENERATION">
            <input type="hidden" name="whatsapp_language" value="en">
        `);

        // Sync from WhatsApp Manager Handler
        $('#syncManagerFlows').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            if ($btn.prop('disabled')) {
                return;
            }
            
            $btn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin"></i> Syncing...');
            
            $.ajax({
                url: '{{ route("vendor.flow.write.sync_from_manager") }}',
                type: 'POST',
                success: function(response) {
                    if (response.reaction == 1) {
                        showNotification('Successfully synced flows from WhatsApp Manager', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification(response.data.message || 'Sync failed', 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to sync from WhatsApp Manager';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification(errorMessage, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false)
                        .html('<i class="fas fa-sync"></i> Sync from WhatsApp Manager');
                }
            });
        });

        // Delete Flow Confirmation Template
        const deleteFlowTemplate = $('#deleteFlowConfirm-template').html();
        
        // Handle Flow Deletion
        $('.delete-flow').on('click', function() {
            const flowId = $(this).data('flow-id');
            const flowName = $(this).data('flow-name');
            
            if (confirm(deleteFlowTemplate.replace('__flowName__', flowName))) {
                deleteFlow(flowId);
            }
        });

        // Helper function to show notifications
        function showNotification(message, type = 'success') {
            if (window.__Utils && window.__Utils.notification) {
                window.__Utils.notification(message, type);
            } else {
                alert(message);
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
.modal-backdrop {
    z-index: 1040;
}
.modal {
    z-index: 1050;
}
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}
.fa-spinner {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@endsection