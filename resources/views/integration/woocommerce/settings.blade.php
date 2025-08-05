@extends('layouts.app')

@section('title', 'WooCommerce Integration Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i>
                        WooCommerce Integration Settings
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.woocommerce.dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($integration && $integration->isActive())
                        <!-- Connection Status -->
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Connected!</strong> Your WooCommerce store is connected and active.
                            <br>
                            <small>Site URL: {{ $integration->site_url }}</small>
                        </div>

                        <!-- Disconnect Section -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Disconnect Integration</h4>
                            </div>
                            <div class="card-body">
                                <p>Disconnecting will remove all webhooks and stop receiving order notifications.</p>
                                <button class="btn btn-danger" id="disconnect-btn">
                                    <i class="fas fa-unlink"></i> Disconnect WooCommerce
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
                                                    {{ in_array('order_confirmation', $integration->getNotificationTypes() ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="order_confirmation">
                                                    Order Confirmation
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[order_confirmation]" class="form-control form-control-sm template-select" data-notification-type="order_confirmation">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ ($integration->getTemplateUid('order_confirmation') ?? '') == $template->_uid ? 'selected' : '' }}
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
                                                    {{ in_array('payment_confirmation', $integration->getNotificationTypes() ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="payment_confirmation">
                                                    Payment Confirmation
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[payment_confirmation]" class="form-control form-control-sm template-select" data-notification-type="payment_confirmation">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ ($integration->getTemplateUid('payment_confirmation') ?? '') == $template->_uid ? 'selected' : '' }}
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
                                                    {{ in_array('shipment_tracking', $integration->getNotificationTypes() ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="shipment_tracking">
                                                    Shipment Tracking
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[shipment_tracking]" class="form-control form-control-sm template-select" data-notification-type="shipment_tracking">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ ($integration->getTemplateUid('shipment_tracking') ?? '') == $template->_uid ? 'selected' : '' }}
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
                                            <h5>Additional Notifications</h5>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="delivery_confirmation" name="notification_types[]" value="delivery_confirmation"
                                                    {{ in_array('delivery_confirmation', $integration->getNotificationTypes() ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="delivery_confirmation">
                                                    Delivery Confirmation
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[delivery_confirmation]" class="form-control form-control-sm template-select" data-notification-type="delivery_confirmation">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ ($integration->getTemplateUid('delivery_confirmation') ?? '') == $template->_uid ? 'selected' : '' }}
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
                                                <input class="form-check-input" type="checkbox" id="cod_verification" name="notification_types[]" value="cod_verification"
                                                    {{ in_array('cod_verification', $integration->getNotificationTypes() ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cod_verification">
                                                    COD Verification
                                                </label>
                                                <div class="mt-2">
                                                    <select name="template_uid[cod_verification]" class="form-control form-control-sm template-select" data-notification-type="cod_verification">
                                                        <option value="">Select Template</option>
                                                        @foreach($templates as $template)
                                                            <option value="{{ $template->_uid }}" 
                                                                {{ ($integration->getTemplateUid('cod_verification') ?? '') == $template->_uid ? 'selected' : '' }}
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
                                <h4 class="card-title">Connect WooCommerce Store</h4>
                            </div>
                            <div class="card-body">
                                <form id="connect-form">
                                    <div class="form-group">
                                        <label for="site_url">Site URL</label>
                                        <input type="url" class="form-control" id="site_url" name="site_url" 
                                            placeholder="https://your-store.com" required>
                                        <small class="form-text text-muted">Enter your WooCommerce store URL (e.g., https://your-store.com)</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="consumer_key">Consumer Key</label>
                                        <input type="text" class="form-control" id="consumer_key" name="consumer_key" 
                                            placeholder="Enter your WooCommerce consumer key" required>
                                        <small class="form-text text-muted">
                                            You can generate consumer keys from your WooCommerce admin panel under WooCommerce > Settings > Advanced > REST API
                                        </small>
                                    </div>
                                    <div class="form-group">
                                        <label for="consumer_secret">Consumer Secret</label>
                                        <input type="password" class="form-control" id="consumer_secret" name="consumer_secret" 
                                            placeholder="Enter your WooCommerce consumer secret" required>
                                        <small class="form-text text-muted">
                                            The consumer secret associated with your consumer key
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-link"></i> Connect WooCommerce
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
                                <h5>How to get your WooCommerce API credentials:</h5>
                                <ol>
                                    <li>Log in to your WordPress admin panel</li>
                                    <li>Go to WooCommerce > Settings > Advanced > REST API</li>
                                    <li>Click "Add key"</li>
                                    <li>Give your key a description (e.g., "WhatsApp Notifications")</li>
                                    <li>Set the following permissions:
                                        <ul>
                                            <li><strong>Read:</strong> Orders, Customers, Products</li>
                                            <li><strong>Write:</strong> Orders (if needed for status updates)</li>
                                        </ul>
                                    </li>
                                    <li>Click "Generate API key"</li>
                                    <li>Copy the "Consumer key" and "Consumer secret"</li>
                                    <li>Paste them in the form above</li>
                                </ol>
                                
                                <div class="alert alert-info mt-3">
                                    <strong>Note:</strong> Make sure your WooCommerce store has the REST API enabled and is accessible via HTTPS.
                                </div>
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
        
        console.log('Submitting WooCommerce connect form:', formData);
        console.log('Route URL:', '{{ route("vendor.integration.woocommerce.connect") }}');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Connecting...');
        
        $.ajax({
            url: '{{ route("vendor.integration.woocommerce.connect") }}',
            method: 'POST',
            data: formData,
            timeout: 30000, // 30 second timeout
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                console.log('Sending AJAX request to:', '{{ route("vendor.integration.woocommerce.connect") }}');
                console.log('Form data:', formData);
                console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function(response) {
                console.log('WooCommerce connect response:', response);
                if (response.success) {
                    toastr.success('WooCommerce connected successfully!');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Failed to connect WooCommerce');
                }
            },
            error: function(xhr, status, error) {
                console.error('WooCommerce connect error:', {xhr, status, error});
                let errorMessage = 'Failed to connect WooCommerce';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-link"></i> Connect WooCommerce');
            }
        });
    });

    // Disconnect button
    $('#disconnect-btn').click(function() {
        if (confirm('Are you sure you want to disconnect your WooCommerce integration? This will stop all order notifications.')) {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Disconnecting...');
            
            $.ajax({
                url: '{{ route("vendor.integration.woocommerce.disconnect") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('WooCommerce disconnected successfully!');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message || 'Failed to disconnect WooCommerce');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to disconnect WooCommerce');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-unlink"></i> Disconnect WooCommerce');
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
            url: '{{ route("vendor.integration.woocommerce.update_notification_settings") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Settings saved successfully!');
                } else {
                    toastr.error(response.message || 'Failed to save settings');
                }
            },
            error: function(xhr) {
                toastr.error('Failed to save settings');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Settings');
            }
        });
    });

    // Test webhook button
    $('#test-webhook-btn').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        
        $.ajax({
            url: '{{ route("vendor.integration.woocommerce.test_webhook") }}',
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

    // Template selection change handler
    $('.template-select').change(function() {
        const notificationType = $(this).data('notification-type');
        const selectedOption = $(this).find('option:selected');
        const templateComponents = selectedOption.data('template-components');
        
        if (templateComponents && templateComponents.length > 0) {
            showVariableMapping(notificationType, templateComponents);
        } else {
            hideVariableMapping(notificationType);
        }
    });

    // Show variable mapping section
    function showVariableMapping(notificationType, templateComponents) {
        const section = $(`#variable-mapping-${notificationType}`);
        const container = section.find('.template-variables-container');
        
        // Clear existing content
        container.empty();
        
        // Add variable mapping fields
        templateComponents.forEach(component => {
            if (component.type === 'body' && component.text) {
                const variables = extractVariables(component.text);
                variables.forEach(variable => {
                    const field = createVariableField(variable, notificationType);
                    container.append(field);
                });
            }
        });
        
        section.show();
    }

    // Hide variable mapping section
    function hideVariableMapping(notificationType) {
        $(`#variable-mapping-${notificationType}`).hide();
    }

    // Extract variables from template text
    function extractVariables(text) {
        const variables = [];
        const regex = /\{\{(\d+)\}\}/g;
        let match;
        
        while ((match = regex.exec(text)) !== null) {
            variables.push(match[1]);
        }
        
        return variables;
    }

    // Create variable field
    function createVariableField(variable, notificationType) {
        return '<div class="form-group">' +
            '<label for="variable_' + notificationType + '_' + variable + '">Variable {{' + variable + '}}</label>' +
            '<select name="variable_mapping[' + notificationType + '][' + variable + ']" class="form-control form-control-sm">' +
                '<option value="">Select WooCommerce field</option>' +
                '<option value="customer_name">Customer Name</option>' +
                '<option value="order_number">Order Number</option>' +
                '<option value="order_total">Order Total</option>' +
                '<option value="order_status">Order Status</option>' +
                '<option value="order_date">Order Date</option>' +
                '<option value="payment_method">Payment Method</option>' +
                '<option value="shipping_address">Shipping Address</option>' +
                '<option value="billing_address">Billing Address</option>' +
                '<option value="tracking_number">Tracking Number</option>' +
                '<option value="tracking_company">Tracking Company</option>' +
                '<option value="tracking_url">Tracking URL</option>' +
                '<option value="delivery_date">Delivery Date</option>' +
                '<option value="cod_amount">COD Amount</option>' +
            '</select>' +
        '</div>';
    }
});
</script>
@endpush
@endsection 