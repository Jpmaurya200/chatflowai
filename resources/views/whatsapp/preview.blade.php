@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Flow Details</h5>
                    <div>
                        <a href="{{ route('vendor.flow.builder.read.view', ['id' => $flow->_id]) }}" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Flow
                        </a>
                        <form method="POST" style="display: inline;">
                            @csrf
                            <a href="javascript:void(0)" 
                               class="btn btn-sm btn-danger lw-ajax-link-action"
                               data-method="post"
                               data-action="{{ route('vendor.flow.write.delete', ['id' => $flow->_id]) }}"
                               data-callback="onFlowDeleted"
                               data-flow-name="{{ $flow->name }}"
                               data-confirm="#deleteFlowConfirm-template"
                               title="Delete Flow">
                                <i class="fas fa-trash"></i> Delete Flow
                            </a>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8">{{ $flow->name }}</dd>
                        
                        <dt class="col-sm-4">Category</dt>
                        <dd class="col-sm-8">{{ $flow->category }}</dd>
                        
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $flow->status === 'published' ? 'success' : 'warning' }}">
                                {{ ucfirst($flow->status) }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $flow->created_at->format('M d, Y H:i') }}</dd>
                        
                        @if($flow->description)
                            <dt class="col-sm-4">Description</dt>
                            <dd class="col-sm-8">{{ $flow->description }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header">
                    <h5 class="mb-0">Flow Screens</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($flow->screens->sortBy('position') as $screen)
                            <a href="#screen-{{ $screen->_id }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $screen->name }}</strong>
                                        <small class="d-block text-muted">{{ $screen->screen_type }}</small>
                                    </div>
                                    <span class="badge bg-secondary">{{ $screen->position }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-center p-3">
                                <p class="text-muted">No screens available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Flow Preview</h5>
                    <a href="{{ route('vendor.flow.read.list_view') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Flows
                    </a>
                </div>
                <div class="card-body">
                    <div class="phone-preview mx-auto">
                        <div class="phone-header">
                            <div class="phone-status-bar"></div>
                            <div class="phone-contact">
                                <i class="fab fa-whatsapp text-success"></i>
                                <span class="ms-2">WhatsApp Business</span>
                            </div>
                        </div>
                        <div class="phone-content">
                            @forelse($flow->screens->sortBy('position') as $screen)
                                <div id="screen-{{ $screen->_id }}" class="message-container">
                                    <div class="message-bubble message-incoming">
                                        <div class="message-content">{{ $screen->content }}</div>
                                        @if($screen->footer)
                                            <div class="message-footer text-muted small">{{ $screen->footer }}</div>
                                        @endif
                                        @if($screen->buttons->count() > 0)
                                            <div class="message-buttons">
                                                @foreach($screen->buttons as $button)
                                                    <button class="message-button" type="button">
                                                        {{ $button->text }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center p-4">
                                    <p class="text-muted">This flow has no screens yet.</p>
                                    <a href="{{ route('vendor.flow.builder.read.view', ['id' => $flow->_id]) }}" class="btn btn-primary">
                                        Add Screens
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
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
            window.location.replace('{{ route("vendor.flow.read.list_view") }}');
        }, 1500);
    } else {
        window.__Utils.notification(response.data.message || 'Failed to delete flow', 'error');
    }
}

$(document).ready(function() {
    // Handle dynamic confirmation message
    $(document).on('click', '.lw-ajax-link-action', function() {
        const flowName = $(this).attr('data-flow-name');
        const template = $('#deleteFlowConfirm-template').html();
        const message = template.replace('__flowName__', flowName);
        $(this).attr('data-confirm-message', message);
    });
});
</script>
@endpush

@push('styles')
<style>
.phone-preview {
    border: 10px solid #222;
    border-radius: 25px;
    overflow: hidden;
    height: 700px;
    background: #f5f5f5;
    margin: 20px auto;
    max-width: 400px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.phone-header {
    background: #075e54;
    color: white;
    padding: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.phone-status-bar {
    height: 24px;
    background: #054c44;
}

.phone-contact {
    padding: 12px 0;
    font-size: 18px;
    display: flex;
    align-items: center;
}

.phone-content {
    height: calc(100% - 82px);
    overflow-y: auto;
    background: #e4ddd6;
    padding: 16px;
}

.message-container {
    margin-bottom: 20px;
    animation: fadeIn 0.3s ease-in-out;
}

.message-bubble {
    max-width: 85%;
    padding: 12px 16px;
    border-radius: 10px;
    margin: 8px;
    position: relative;
    background: white;
    border-top-left-radius: 0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message-content {
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 15px;
    line-height: 1.4;
}

.message-footer {
    margin-top: 6px;
    font-size: 0.85em;
    color: #667781;
}

.message-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 12px;
}

.message-button {
    background: #f7f7f7;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    padding: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
}

.message-button:hover {
    background: #ebebeb;
    transform: translateY(-1px);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scrollbar Styling */
.phone-content::-webkit-scrollbar {
    width: 6px;
}

.phone-content::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.05);
}

.phone-content::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

.sticky-top {
    z-index: 1020;
}
</style>
@endpush

@endsection 