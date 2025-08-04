@extends('layouts.app')

@section('title', 'Shopify Integration Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i>
                        Shopify Integration Settings
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.shopify.dashboard') }}" class="btn btn-secondary btn-sm">
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
                                <h4 class="card-title">Disconnect Integration</h4>
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
                                <h4 class="card-title">Notification Settings</h4>
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
                                                                data-template-components="{{ json_encode($template->__data['template']['components'] ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-order_confirmation" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template Variable Mapping</h6>
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
                                                                data-template-components="{{ json_encode($template->__data['template']['components'] ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-payment_confirmation" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template Variable Mapping</h6>
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
                                                                data-template-components="{{ json_encode($template->__data['template']['components'] ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-shipment_tracking" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template Variable Mapping</h6>
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
                                                                data-template-components="{{ json_encode($template->__data['template']['components'] ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-delivery_confirmation" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template Variable Mapping</h6>
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
                                                                data-template-components="{{ json_encode($template->__data['template']['components'] ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-cod_verification" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template Variable Mapping</h6>
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
                                                                data-template-components="{{ json_encode($template->__data['template']['components'] ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-order_cancelled" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template Variable Mapping</h6>
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
                                                                data-template-components="{{ json_encode($template->__data['template']['components'] ?? []) }}">
                                                                {{ $template->template_name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Variable Mapping Section -->
                                                <div class="variable-mapping-section mt-3" id="variable-mapping-refund_processed" style="display: none;">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="card-title mb-0">Template Variable Mapping</h6>
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
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '{{ route("vendor.integration.shopify.update_notification_settings") }}',
            method: 'POST',
            data: formData,
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
    
    // Handle template selection change
    $('.template-select').on('change', function() {
        const notificationType = $(this).data('notification-type');
        const selectedOption = $(this).find('option:selected');
        const templateComponents = selectedOption.data('template-components');
        const mappingSection = $(`#variable-mapping-${notificationType}`);
        
        if (selectedOption.val() && templateComponents) {
            // Show mapping section
            mappingSection.show();
            
            // Generate variable mapping form
            const variablesContainer = mappingSection.find('.template-variables-container');
            variablesContainer.empty();
            
            // Extract variables from template components
            const variables = extractTemplateVariables(templateComponents);
            
            if (variables.length > 0) {
                variables.forEach((variable, index) => {
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
                });
            } else {
                variablesContainer.html('<p class="text-muted">No variables found in this template.</p>');
            }
        } else {
            // Hide mapping section
            mappingSection.hide();
        }
    });
    
    // Function to extract template variables
    function extractTemplateVariables(components) {
        const variables = [];
        const pattern = /\{\{(\d+)\}\}/g;
        
        components.forEach(component => {
            if (component.type === 'HEADER' && component.format === 'TEXT') {
                const matches = component.text.match(pattern);
                if (matches) {
                    matches.forEach(match => {
                        const varNumber = match.replace(/\{\{(\d+)\}\}/, '$1');
                        variables.push({
                            name: `header_field_${varNumber}`,
                            label: `Header Variable ${varNumber}`
                        });
                    });
                }
            } else if (component.type === 'BODY') {
                const matches = component.text.match(pattern);
                if (matches) {
                    matches.forEach(match => {
                        const varNumber = match.replace(/\{\{(\d+)\}\}/, '$1');
                        variables.push({
                            name: `field_${varNumber}`,
                            label: `Body Variable ${varNumber}`
                        });
                    });
                }
            } else if (component.type === 'BUTTONS') {
                component.buttons.forEach(button => {
                    if (button.type === 'URL' && button.url.includes('{{1}}')) {
                        variables.push({
                            name: 'button_0',
                            label: 'Button URL Variable'
                        });
                    }
                });
            }
        });
        
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
    
    // Trigger change event for existing selections
    $('.template-select').each(function() {
        if ($(this).val()) {
            $(this).trigger('change');
        }
    });
});
</script>
@endpush
@endsection 