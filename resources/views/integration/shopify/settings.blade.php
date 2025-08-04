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
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="order_confirmation" name="notification_types[]" value="order_confirmation" 
                                                    {{ in_array('order_confirmation', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="order_confirmation">
                                                    Order Confirmation
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="payment_confirmation" name="notification_types[]" value="payment_confirmation"
                                                    {{ in_array('payment_confirmation', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="payment_confirmation">
                                                    Payment Confirmation
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="shipment_tracking" name="notification_types[]" value="shipment_tracking"
                                                    {{ in_array('shipment_tracking', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="shipment_tracking">
                                                    Shipment Tracking
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="delivery_confirmation" name="notification_types[]" value="delivery_confirmation"
                                                    {{ in_array('delivery_confirmation', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="delivery_confirmation">
                                                    Delivery Confirmation
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Special Notifications</h5>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="cod_verification" name="notification_types[]" value="cod_verification"
                                                    {{ in_array('cod_verification', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cod_verification">
                                                    COD Verification (India)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="order_cancelled" name="notification_types[]" value="order_cancelled"
                                                    {{ in_array('order_cancelled', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="order_cancelled">
                                                    Order Cancelled
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="refund_processed" name="notification_types[]" value="refund_processed"
                                                    {{ in_array('refund_processed', $integration->getNotificationTypes()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="refund_processed">
                                                    Refund Processed
                                                </label>
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
});
</script>
@endpush
@endsection 