@extends('layouts.app')

@section('title', 'Shopify Integration Settings')

@push('head')
<style>
/* Modern Shopify Dashboard Styles - Overriding built-in styles */
.shopify-dashboard {
    background: #f8fafc;
    min-height: 100vh;
    padding: 2rem 0;
}

.shopify-dashboard .container-fluid {
    max-width: 1400px;
    margin: 0 auto;
}

/* Modern Card Styling */
.shopify-dashboard .card {
    background: #ffffff;
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    margin-bottom: 2rem;
    border: 1px solid #e5e7eb;
}

.shopify-dashboard .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shopify-dashboard .card-header {
    background: #10b981;
    color: white;
    border-radius: 16px 16px 0 0 !important;
    padding: 1.5rem 2rem;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.shopify-dashboard .card-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.shopify-dashboard .card-title i {
    font-size: 1.25rem;
    opacity: 0.9;
}

.shopify-dashboard .card-body {
    padding: 2rem;
}

/* Modern Alert Styling */
.shopify-dashboard .alert {
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    font-weight: 500;
}

.shopify-dashboard .alert-success {
    background: #10b981;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
}

.shopify-dashboard .alert-warning {
    background: #f59e0b;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);
}

.shopify-dashboard .alert-danger {
    background: #ef4444;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
}

/* Modern Button Styling */
.shopify-dashboard .btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.shopify-dashboard .btn-primary {
    background: #10b981;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
}

.shopify-dashboard .btn-primary:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(16, 185, 129, 0.3);
    color: white;
}

.shopify-dashboard .btn-secondary {
    background: #6b7280;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(107, 114, 128, 0.2);
}

.shopify-dashboard .btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(107, 114, 128, 0.3);
    color: white;
}

.shopify-dashboard .btn-danger {
    background: #ef4444;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
}

.shopify-dashboard .btn-danger:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(239, 68, 68, 0.3);
    color: white;
}

.shopify-dashboard .btn-warning {
    background: #f59e0b;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);
}

.shopify-dashboard .btn-warning:hover {
    background: #d97706;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(245, 158, 11, 0.3);
    color: white;
}

/* Modern Form Styling */
.shopify-dashboard .form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.shopify-dashboard .form-control:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.shopify-dashboard .form-check-input:checked {
    background-color: #10b981;
    border-color: #10b981;
}

.shopify-dashboard .form-check-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Modern Badge Styling */
.shopify-dashboard .badge {
    border-radius: 12px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.shopify-dashboard .badge-success {
    background: #10b981;
    color: white;
}

.shopify-dashboard .badge-danger {
    background: #ef4444;
    color: white;
}

.shopify-dashboard .badge-warning {
    background: #f59e0b;
    color: white;
}

.shopify-dashboard .badge-info {
    background: #06b6d4;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .shopify-dashboard {
        padding: 1rem 0;
    }
    
    .shopify-dashboard .card-body {
        padding: 1.5rem;
    }
    
    .shopify-dashboard .card-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}

/* Loading Animation */
.shopify-dashboard .loading {
    opacity: 0.7;
    pointer-events: none;
}

.shopify-dashboard .spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Custom Scrollbar */
.shopify-dashboard ::-webkit-scrollbar {
    width: 8px;
}

.shopify-dashboard ::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.shopify-dashboard ::-webkit-scrollbar-thumb {
    background: #10b981;
    border-radius: 10px;
}

.shopify-dashboard ::-webkit-scrollbar-thumb:hover {
    background: #059669;
}
</style>
@endpush

@section('content')
<div class="shopify-dashboard">
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-white">
                        <i class="fas fa-cog"></i>
                        Shopify Integration Settings
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.shopify.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($integration && $integration->isActive())
                        <!-- Connection Status -->
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Connected!</strong> Your Shopify store is connected and active.
                            <br>
                            <small>Shop Domain: {{ $integration->shop_domain }}</small>
                        </div>

                        <!-- Disconnect Section -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title text-white">Disconnect Integration</h4>
                            </div>
                            <div class="card-body">
                                <p>Disconnecting will remove all webhooks and stop receiving order notifications.</p>
                                <button class="btn btn-danger" id="disconnect-btn">
                                    <i class="fas fa-unlink"></i> Disconnect Shopify
                                </button>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title text-white">Notification Settings</h4>
                            </div>
                            <div class="card-body">
                                <form id="notification-settings-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Order Notifications</h5>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="order_confirmation" name="notification_types[]" value="order_confirmation" 
                                                    {{ in_array('order_confirmation', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="order_confirmation">
                                                    Order Confirmation
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[order_confirmation]" class="form-control form-control-sm template-select" data-notification-type="order_confirmation">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ $integration->getTemplateUid('order_confirmation') == $template->_uid ? 'selected' : '' }}
                                                                data-template-components="{{ json_encode($template->components_data ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-order_confirmation" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0 text-white">Template Variable Mapping</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="template-variables-container">
                                                                <!-- Template variables will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="payment_confirmation" name="notification_types[]" value="payment_confirmation"
                                                    {{ in_array('payment_confirmation', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="payment_confirmation">
                                                    Payment Confirmation
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[payment_confirmation]" class="form-control form-control-sm template-select" data-notification-type="payment_confirmation">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ $integration->getTemplateUid('payment_confirmation') == $template->_uid ? 'selected' : '' }}
                                                                data-template-components="{{ json_encode($template->components_data ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-payment_confirmation" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0 text-white">Template Variable Mapping</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="template-variables-container">
                                                                <!-- Template variables will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="shipment_tracking" name="notification_types[]" value="shipment_tracking"
                                                    {{ in_array('shipment_tracking', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="shipment_tracking">
                                                    Shipment Tracking
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[shipment_tracking]" class="form-control form-control-sm template-select" data-notification-type="shipment_tracking">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ $integration->getTemplateUid('shipment_tracking') == $template->_uid ? 'selected' : '' }}
                                                                data-template-components="{{ json_encode($template->components_data ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-shipment_tracking" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0 text-white">Template Variable Mapping</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="template-variables-container">
                                                                <!-- Template variables will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="delivery_confirmation" name="notification_types[]" value="delivery_confirmation"
                                                    {{ in_array('delivery_confirmation', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="delivery_confirmation">
                                                    Delivery Confirmation
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[delivery_confirmation]" class="form-control form-control-sm template-select" data-notification-type="delivery_confirmation">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ $integration->getTemplateUid('delivery_confirmation') == $template->_uid ? 'selected' : '' }}
                                                                data-template-components="{{ json_encode($template->components_data ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-delivery_confirmation" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0 text-white">Template Variable Mapping</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="template-variables-container">
                                                                <!-- Template variables will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Special Notifications</h5>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="cod_verification" name="notification_types[]" value="cod_verification"
                                                    {{ in_array('cod_verification', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cod_verification">
                                                    COD Verification (India)
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[cod_verification]" class="form-control form-control-sm template-select" data-notification-type="cod_verification">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ $integration->getTemplateUid('cod_verification') == $template->_uid ? 'selected' : '' }}
                                                                data-template-components="{{ json_encode($template->components_data ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-cod_verification" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0 text-white">Template Variable Mapping</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="template-variables-container">
                                                                <!-- Template variables will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="order_cancelled" name="notification_types[]" value="order_cancelled"
                                                    {{ in_array('order_cancelled', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="order_cancelled">
                                                    Order Cancelled
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[order_cancelled]" class="form-control form-control-sm template-select" data-notification-type="order_cancelled">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ $integration->getTemplateUid('order_cancelled') == $template->_uid ? 'selected' : '' }}
                                                                data-template-components="{{ json_encode($template->components_data ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-order_cancelled" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0 text-white">Template Variable Mapping</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="template-variables-container">
                                                                <!-- Template variables will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="refund_processed" name="notification_types[]" value="refund_processed"
                                                    {{ in_array('refund_processed', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="refund_processed">
                                                    Refund Processed
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[refund_processed]" class="form-control form-control-sm template-select" data-notification-type="refund_processed">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ $integration->getTemplateUid('refund_processed') == $template->_uid ? 'selected' : '' }}
                                                                data-template-components="{{ json_encode($template->components_data ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-refund_processed" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0 text-white">Template Variable Mapping</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="template-variables-container">
                                                                <!-- Template variables will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Test Webhook -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Test Integration</h4>
                            </div>
                            <div class="card-body">
                                <p>Send a test notification to verify your integration is working correctly.</p>
                                <button class="btn btn-info" id="test-webhook-btn">
                                    <i class="fas fa-paper-plane"></i> Send Test Notification
                                </button>
                            </div>
                        </div>

                        <!-- Debug Panel -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Debug Information</h4>
                                <button class="btn btn-sm btn-secondary" id="toggle-debug">
                                    <i class="fas fa-bug"></i> Toggle Debug
                                </button>
                            </div>
                            <div class="card-body" id="debug-panel" style="display: none;">
                                <h6>Available Templates:</h6>
                                <div id="debug-templates"></div>
                                <hr>
                                <h6>Template Components:</h6>
                                <div id="debug-components"></div>
                            </div>
                        </div>

                    @else
                        <!-- Connection Form -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Connect Shopify Store</h4>
                            </div>
                            <div class="card-body">
                                <form id="connect-form">
                                    <div class="form-group">
                                        <label for="shop_domain">Shop Domain</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="shop_domain" name="shop_domain" 
                                                placeholder="your-store.myshopify.com" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">.myshopify.com</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Enter your Shopify store domain (without .myshopify.com)</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="access_token">Access Token</label>
                                        <input type="password" class="form-control" id="access_token" name="access_token" 
                                            placeholder="Enter your Shopify access token" required>
                                        <small class="form-text text-muted">
                                            You can generate an access token from your Shopify admin panel under Apps > Private apps
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-link"></i> Connect Shopify
                                    </button>
                                    <button type="button" class="btn btn-secondary ml-2" id="test-btn">
                                        Test Button
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Setup Instructions -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Setup Instructions</h4>
                            </div>
                            <div class="card-body">
                                <h5>How to get your Shopify Access Token:</h5>
                                <ol>
                                    <li>Log in to your Shopify admin panel</li>
                                    <li>Go to Apps > Manage private apps</li>
                                    <li>Click "Create new private app"</li>
                                    <li>Give your app a name (e.g., "WhatsApp Notifications")</li>
                                    <li>Set the following permissions:
                                        <ul>
                                            <li><strong>Orders:</strong> Read and write</li>
                                            <li><strong>Customers:</strong> Read</li>
                                            <li><strong>Products:</strong> Read</li>
                                        </ul>
                                    </li>
                                    <li>Save the app</li>
                                    <li>Copy the "Admin API access token"</li>
                                    <li>Paste it in the form above</li>
                                </ol>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Document ready, jQuery version:', $.fn.jquery);
    console.log('Connect form exists:', $('#connect-form').length > 0);
    
    // Connect form submission
    $('#connect-form').submit(function(e) {
        console.log('Form submission triggered');
        console.log('Form data:', $(this).serialize());
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        
        console.log('Submitting Shopify connect form:', formData);
        console.log('Route URL:', '{{ route("vendor.integration.shopify.connect") }}');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Connecting...');
        
        $.ajax({
            url: '{{ route("vendor.integration.shopify.connect") }}',
            method: 'POST',
            data: formData,
            timeout: 30000, // 30 second timeout
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                console.log('Sending AJAX request to:', '{{ route("vendor.integration.shopify.connect") }}');
                console.log('Form data:', formData);
                console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function(response) {
                console.log('Shopify connect response:', response);
                if (response.success) {
                    toastr.success('Shopify connected successfully!');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Failed to connect Shopify');
                }
            },
            error: function(xhr, status, error) {
                console.error('Shopify connect error:', {xhr, status, error});
                let errorMessage = 'Failed to connect Shopify';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-link"></i> Connect Shopify');
            }
        });
    });

    // Disconnect button
    $('#disconnect-btn').click(function() {
        if (confirm('Are you sure you want to disconnect your Shopify integration? This will stop all order notifications.')) {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Disconnecting...');
            
            $.ajax({
                url: '{{ route("vendor.integration.shopify.disconnect") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Shopify disconnected successfully!');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message || 'Failed to disconnect Shopify');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to disconnect Shopify');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-unlink"></i> Disconnect Shopify');
                }
            });
        }
    });

    // Notification settings form
    $('#notification-settings-form').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        
        // Collect form data manually to include variable mappings
        const formData = new FormData(this);
        
        // Collect variable mappings
        const variableMappings = {};
        $('.shopify-variable-select').each(function() {
            const select = $(this);
            const name = select.attr('name');
            const value = select.val();
            
            if (name && value) {
                // Extract notification type and variable name from the select name
                // name format: variable_mappings[notification_type][variable_name]
                const match = name.match(/variable_mappings\[([^\]]+)\]\[([^\]]+)\]/);
                if (match) {
                    const notificationType = match[1];
                    const variableName = match[2];
                    
                    if (!variableMappings[notificationType]) {
                        variableMappings[notificationType] = {};
                    }
                    variableMappings[notificationType][variableName] = value;
                }
            }
        });
        
        // Add variable mappings to form data
        if (Object.keys(variableMappings).length > 0) {
            formData.append('variable_mappings', JSON.stringify(variableMappings));
        }
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '{{ route("vendor.integration.shopify.update_notification_settings") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Notification settings updated successfully!');
                } else {
                    toastr.error(response.message || 'Failed to update settings');
                }
            },
            error: function(xhr) {
                toastr.error('Failed to update notification settings');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Settings');
            }
        });
    });

    // Test button
    $('#test-btn').click(function() {
        console.log('Test button clicked');
        alert('Test button works!');
    });

    // Test webhook button
    $('#test-webhook-btn').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: '{{ route("vendor.integration.shopify.test_webhook") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Test notification sent successfully!');
                } else {
                    toastr.error(response.message || 'Failed to send test notification');
                }
            },
            error: function(xhr) {
                toastr.error('Failed to send test notification');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Test Notification');
            }
        });
    });

    // Template variable mapping functionality
    const shopifyVariables = @json($shopifyVariables);
    const savedVariableMappings = @json($savedVariableMappings ?? []);
    
    console.log('Loaded saved variable mappings:', savedVariableMappings);
    
    // Handle template selection change
    $('.template-select').on('change', function() {
        const notificationType = $(this).data('notification-type');
        const selectedOption = $(this).find('option:selected');
        const templateComponents = selectedOption.data('template-components');
        const mappingSection = $(`#variable-mapping-${notificationType}`);
        
        console.log('Template selection changed:', {
            notificationType: notificationType,
            templateComponents: templateComponents
        });
        
        if (selectedOption.val() && templateComponents) {
            // Show mapping section
            mappingSection.show();
            
            // Generate variable mapping form
            const variablesContainer = mappingSection.find('.template-variables-container');
            variablesContainer.empty();
            
            // Extract variables from template components
            const variables = extractTemplateVariables(templateComponents);
            
            console.log('Extracted variables:', variables);
            
            if (variables.length > 0) {
                variables.forEach((variable, index) => {
                    // Get saved mapping for this variable
                    const savedMapping = savedVariableMappings[notificationType]?.[variable.name] || '';
                    
                    const variableHtml = `
                        <div class="form-group mb-3">
                            <label class="form-label">${variable.label}</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm" 
                                           value="${variable.name}" readonly>
                                    <small class="form-text text-muted">Template Variable</small>
                                </div>
                                <div class="col-md-6">
                                    <select name="variable_mappings[${notificationType}][${variable.name}]" 
                                            class="form-control form-control-sm shopify-variable-select">
                                        <option value="">Select Shopify Variable</option>
                                        ${generateShopifyVariableOptions(shopifyVariables)}
                                    </select>
                                    <small class="form-text text-muted">Map to Shopify Data</small>
                                </div>
                            </div>
                        </div>
                    `;
                    variablesContainer.append(variableHtml);
                    
                    // Set the saved value
                    const selectElement = variablesContainer.find(`select[name="variable_mappings[${notificationType}][${variable.name}]"]`);
                    selectElement.val(savedMapping);
                });
            } else {
                variablesContainer.html('<p class="text-muted">No variables found in this template. This template may not contain any dynamic variables ({{1}}, {{2}}, etc.).</p>');
            }
        } else if (selectedOption.val()) {
            // Template selected but no components data
            mappingSection.show();
            const variablesContainer = mappingSection.find('.template-variables-container');
            variablesContainer.html('<p class="text-warning">Template selected but component data not available. Please refresh the page and try again.</p>');
        } else {
            // Hide mapping section
            mappingSection.hide();
        }
    });
    
    // Function to extract template variables
    function extractTemplateVariables(components) {
        const variables = [];
        const pattern = /\{\{(\d+)\}\}/g;
        
        console.log('Processing components:', components);
        
        if (!Array.isArray(components)) {
            console.error('Components is not an array:', components);
            return variables;
        }
        
        components.forEach((component, index) => {
            console.log(`Processing component ${index}:`, component);
            
            if (component.type === 'HEADER' && component.format === 'TEXT') {
                console.log('Processing HEADER component:', component.text);
                const matches = component.text.match(pattern);
                if (matches) {
                    matches.forEach(match => {
                        const varNumber = match.replace(/\{\{(\d+)\}\}/, '$1');
                        variables.push({
                            name: varNumber,
                            label: `Variable ${varNumber}`
                        });
                    });
                }
            } else if (component.type === 'BODY') {
                console.log('Processing BODY component:', component.text);
                const matches = component.text.match(pattern);
                if (matches) {
                    matches.forEach(match => {
                        const varNumber = match.replace(/\{\{(\d+)\}\}/, '$1');
                        variables.push({
                            name: varNumber,
                            label: `Variable ${varNumber}`
                        });
                    });
                }
            } else if (component.type === 'BUTTONS') {
                console.log('Processing BUTTONS component:', component.buttons);
                if (component.buttons && Array.isArray(component.buttons)) {
                    component.buttons.forEach(button => {
                        if (button.type === 'URL' && button.url && button.url.includes('{{1}}')) {
                            variables.push({
                                name: '1',
                                label: 'Variable 1'
                            });
                        }
                    });
                }
            }
        });
        
        console.log('Extracted variables:', variables);
        return variables;
    }
    
    // Function to generate Shopify variable options
    function generateShopifyVariableOptions(variables) {
        let options = '';
        Object.entries(variables).forEach(([key, label]) => {
            options += `<option value="${key}">${label}</option>`;
        });
        return options;
    }
    
    // Trigger change event for existing selections and restore saved mappings
    $('.template-select').each(function() {
        if ($(this).val()) {
            $(this).trigger('change');
        }
    });

    // Debug panel functionality
    $('#toggle-debug').click(function() {
        $('#debug-panel').toggle();
        
        if ($('#debug-panel').is(':visible')) {
            // Populate debug information
            const templates = @json($templates);
            let debugHtml = '<ul>';
            templates.forEach(template => {
                debugHtml += `<li><strong>${template.template_name}</strong> (${template.language})`;
                debugHtml += `<br><small>Components: ${JSON.stringify(template.components_data || [], null, 2)}</small></li>`;
            });
            debugHtml += '</ul>';
            $('#debug-templates').html(debugHtml);
            
            // Show saved variable mappings
            $('#debug-components').html(`<h6>Saved Variable Mappings:</h6><pre>${JSON.stringify(savedVariableMappings, null, 2)}</pre>`);
        }
    });

    // Update debug components when template is selected
    $('.template-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const templateComponents = selectedOption.data('template-components');
        
        if ($('#debug-panel').is(':visible')) {
            $('#debug-components').html(`<pre>${JSON.stringify(templateComponents || [], null, 2)}</pre>`);
        }
    });
});
</script>
@endpush
@endsection 