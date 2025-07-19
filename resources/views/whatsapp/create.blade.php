@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">{{ __tr('Create New Flow') }}</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('vendor.flow.read.list_view') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __tr('Back to Flows') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.flow.write.store.process') }}" method="POST" id="createFlowForm" class="lw-ajax-form lw-form" data-show-processing="true" data-callback="afterFlowCreated">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">{{ __tr('Flow Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="category">{{ __tr('Category') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">{{ __tr('Select Category') }}</option>
                                <option value="CUSTOMER_SUPPORT" {{ old('category') == 'CUSTOMER_SUPPORT' ? 'selected' : '' }}>{{ __tr('Customer Support') }}</option>
                                <option value="MARKETING" {{ old('category') == 'MARKETING' ? 'selected' : '' }}>{{ __tr('Marketing') }}</option>
                                <option value="UTILITY" {{ old('category') == 'UTILITY' ? 'selected' : '' }}>{{ __tr('Utility') }}</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">{{ __tr('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="whatsapp_flow_type">{{ __tr('WhatsApp Flow Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('whatsapp_flow_type') is-invalid @enderror" id="whatsapp_flow_type" name="whatsapp_flow_type" required>
                                <option value="">{{ __tr('Select Flow Type') }}</option>
                                <option value="LEAD_GENERATION">{{ __tr('Lead Generation') }}</option>
                                <option value="CUSTOMER_SUPPORT">{{ __tr('Customer Support') }}</option>
                                <option value="PROMOTIONAL">{{ __tr('Promotional') }}</option>
                            </select>
                            @error('whatsapp_flow_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="whatsapp_language">{{ __tr('Primary Language') }}</label>
                            <select class="form-control @error('whatsapp_language') is-invalid @enderror" id="whatsapp_language" name="whatsapp_language">
                                <option value="en">English</option>
                                <option value="es">Spanish</option>
                                <option value="pt">Portuguese</option>
                                <!-- Add more languages as needed -->
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            {{ __tr('Create Flow') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function afterFlowCreated(response) {
    if (response.reaction == 1) {
        // Show success notification with WhatsApp sync status
        const syncStatus = response.data.flow.whatsapp_sync_status;
        const message = syncStatus === 'synced' 
            ? 'Flow created and synced with WhatsApp'
            : 'Flow created successfully, WhatsApp sync pending';
            
        window.__Utils.notification(message, 'success');
        
        setTimeout(function() {
            window.location.href = '{{ url("/vendor-console/whatsapp/flows") }}/' + response.data.flow._id + '/builder';
        }, 1500);
    } else {
        window.__Utils.notification(response.data.message || '{{ __tr("Something went wrong") }}', 'error');
        $('#submitBtn').prop('disabled', false);
    }
}

$(document).ready(function() {
    // Handle form submission errors
    $(document).on('formValidationErrors', function(e, errors) {
        Object.keys(errors).forEach(function(key) {
            const field = $(`#${key}`);
            field.addClass('is-invalid');
            field.siblings('.invalid-feedback').text(errors[key][0]);
        });
        $('#submitBtn').prop('disabled', false);
    });
});
</script>
@endpush 