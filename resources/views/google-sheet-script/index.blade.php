@extends('layouts.app', ['title' => __tr('Google Sheets App Script')])
@section('content')
<div class="container mt-8">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
 <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Generate Google Sheets Integration Script</span>
                    <a href="https://drive.google.com/file/d/15ZqjRlzqpFoGrD0DOSDd4zZMRDwunRAD/view?usp=sharing" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-video"></i> {{ __tr('Watch Instructions') }}
                    </a>
                </div>
                <div class="card-body">
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