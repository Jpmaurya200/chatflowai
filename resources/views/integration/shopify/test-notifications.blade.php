@extends('layouts.app')

@section('title', 'Test Shopify Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-vial"></i>
                        Test Shopify Notifications
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.shopify.dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Test Notification Form -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Send Test Notification</h5>
                                </div>
                                <div class="card-body">
                                    <form id="test-notification-form">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="notification_type">Notification Type</label>
                                                    <select class="form-control" id="notification_type" name="notification_type" required>
                                                        <option value="">Select Type</option>
                                                        <option value="order_confirmation">Order Confirmation</option>
                                                        <option value="payment_confirmation">Payment Confirmation</option>
                                                        <option value="shipment_tracking">Shipment Tracking</option>
                                                        <option value="delivery_confirmation">Delivery Confirmation</option>
                                                        <option value="cod_verification">COD Verification</option>
                                                        <option value="order_cancelled">Order Cancelled</option>
                                                        <option value="refund_processed">Refund Processed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone_number">Phone Number</label>
                                                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                                                           placeholder="+1234567890" value="+1234567890" required>
                                                    <small class="form-text text-muted">Include country code (e.g., +1 for US)</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="customer_name">Customer Name</label>
                                                    <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                                           value="John Doe" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="order_number">Order Number</label>
                                                    <input type="text" class="form-control" id="order_number" name="order_number" 
                                                           value="TEST-{{ time() }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="order_total">Order Total</label>
                                                    <input type="number" step="0.01" class="form-control" id="order_total" name="order_total" 
                                                           value="99.99" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="currency">Currency</label>
                                                    <select class="form-control" id="currency" name="currency">
                                                        <option value="USD">USD</option>
                                                        <option value="EUR">EUR</option>
                                                        <option value="GBP">GBP</option>
                                                        <option value="INR">INR</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tracking_number">Tracking Number (for shipment)</label>
                                                    <input type="text" class="form-control" id="tracking_number" name="tracking_number" 
                                                           value="TRK{{ time() }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="carrier">Carrier (for shipment)</label>
                                                    <input type="text" class="form-control" id="carrier" name="carrier" 
                                                           value="FedEx">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send Test Notification
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Test Results</h5>
                                </div>
                                <div class="card-body">
                                    <div id="test-results">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <p>Test results will appear here</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Webhook Data -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Sample Webhook Data</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Order Created Webhook</h6>
                                            <pre class="bg-light p-3 rounded"><code>{
  "id": 123456789,
  "order_number": "1001",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "total_price": "99.99",
  "currency": "USD",
  "financial_status": "pending",
  "fulfillment_status": "unfulfilled",
  "status": "open"
}</code></pre>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Order Paid Webhook</h6>
                                            <pre class="bg-light p-3 rounded"><code>{
  "id": 123456789,
  "order_number": "1001",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "total_price": "99.99",
  "currency": "USD",
  "financial_status": "paid",
  "fulfillment_status": "unfulfilled",
  "status": "open"
}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testing Instructions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Testing Instructions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Manual Testing</h6>
                                            <ol>
                                                <li>Fill in the test form above</li>
                                                <li>Select a notification type</li>
                                                <li>Enter a valid phone number</li>
                                                <li>Click "Send Test Notification"</li>
                                                <li>Check the results panel</li>
                                            </ol>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Webhook Testing</h6>
                                            <ol>
                                                <li>Use a tool like Postman or curl</li>
                                                <li>Send POST to: <code>{{ url('shopify-webhook/' . getVendorId()) }}</code></li>
                                                <li>Use the sample webhook data above</li>
                                                <li>Check the logs for processing</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#test-notification-form').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const resultsDiv = $('#test-results');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: '{{ route("vendor.integration.shopify.test_notification") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    resultsDiv.html(`
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Success!</strong><br>
                            Notification sent successfully.<br>
                            <small>Message ID: ${response.message_id || 'N/A'}</small>
                        </div>
                    `);
                } else {
                    resultsDiv.html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error!</strong><br>
                            ${response.message || 'Failed to send notification'}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to send notification';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                resultsDiv.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error!</strong><br>
                        ${errorMessage}
                    </div>
                `);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Test Notification');
            }
        });
    });
});
</script>
@endpush
@endsection 