@extends('layouts.app', ['title' => __tr('Google Sheets App Script')])
@section('content')
<div class="lw-page-content py-4">
    <div class="container">
        <!-- Modern Header Banner -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-left: 5px solid #10b981 !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background-color: #ecfdf5; color: #059669; font-size: 11px;">
                                <i class="fas fa-file-excel me-1"></i> {{ __tr('1-Click Integration') }}
                            </span>
                            <span class="text-muted small">• {{ __tr('Google Apps Script') }}</span>
                        </div>
                        <h2 class="h3 fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.02em;">
                            {{ __tr('Google Sheets Automation Generator') }}
                        </h2>
                        <p class="text-muted mb-0 small">
                            {{ __tr('Generate instant Google Apps Script to auto-dispatch WhatsApp notifications whenever new rows are submitted via Google Forms or Sheets.') }}
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('vendor.settings.read', ['pageType' => 'api-access']) }}" class="btn btn-light border rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm">
                            <i class="fas fa-arrow-left me-1.5 text-muted"></i> {{ __tr('Developer Portal') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark"><i class="fas fa-cog text-primary me-2"></i>{{ __tr('Script Configuration') }}</span>
                    </div>
                    <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('google-sheet-script.generate') }}" x-data="{
                        customFields: {
                            totalAllowed: 5,
                            totalUsed: 0,
                            data: {}
                        },
                        addCustomField() {
                            if (this.customFields.totalUsed >= this.customFields.totalAllowed) {
                                alert('Maximum 5 custom fields allowed');
                                return;
                            }
                            let fieldId = Date.now();
                            this.customFields.data[fieldId] = {
                                index: fieldId,
                                value: ''
                            };
                            this.customFields.totalUsed++;
                        },
                        removeCustomField(fieldId) {
                            delete this.customFields.data[fieldId];
                            this.customFields.totalUsed--;
                        }
                    }">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="template_name">Template</label>
                            <select class="form-control" id="template_name" name="template_name" required>
                                <option value="">Select Template</option>
                                @foreach ($whatsAppTemplates as $template)
                                    <option value="{{ $template->template_name }}" 
                                            data-language="{{ $template->language }}"
                                            {{ old('template_name') == $template->template_name ? 'selected' : '' }}>
                                        {{ $template->template_name }} ({{ $template->language }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select a WhatsApp message template to use.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone_column_index">Phone Number Column Index</label>
                            <input type="number" class="form-control" id="phone_column_index" name="phone_column_index" 
                                value="{{ old('phone_column_index', '2') }}" min="0" required>
                            <small class="form-text text-muted">The column index (0-based) containing phone numbers in the Google Sheet.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label>Custom Fields (Maximum 5)</label>
                            <div id="custom-fields-container">
                                <template x-for="field in customFields.data" :key="field.index">
                                    <div class="input-group mb-2">
                                        <input type="number" 
                                            class="form-control" 
                                            x-model="field.value"
                                            :name="'custom_fields[]'" 
                                            min="0" 
                                            placeholder="Column Index"
                                            required>
                                        <div class="input-group-append">
                                            <button type="button" 
                                                class="btn btn-danger" 
                                                @click="removeCustomField(field.index)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" 
                                class="btn btn-secondary btn-sm mt-2" 
                                @click="addCustomField()"
                                :disabled="customFields.totalUsed >= customFields.totalAllowed">
                                <i class="fas fa-plus"></i> Add Custom Field
                            </button>
                            <small class="form-text text-muted">Add column indices for additional fields to include in the payload (field_1, field_2, etc).</small>
                            <template x-if="customFields.totalUsed >= customFields.totalAllowed">
                                <div class="alert alert-warning mt-2">
                                    Maximum number of custom fields (5) reached
                                </div>
                            </template>
                        </div>

                        <button type="submit" class="btn btn-primary">Generate Script</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Alpine.js if needed
    if (typeof Alpine === 'undefined') {
        console.error('Alpine.js is required for this page to work properly');
    }
});
</script>
@endpush
@endsection