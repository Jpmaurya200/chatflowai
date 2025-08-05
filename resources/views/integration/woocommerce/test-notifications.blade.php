@extends('layouts.app')

@section('title', 'Test WooCommerce Notifications')

@push('head')
<style>
/* Modern WooCommerce Dashboard Styles - Overriding built-in styles */
.woocommerce-dashboard {
    background: #f8fafc;
    min-height: 100vh;
    padding: 2rem 0;
}

.woocommerce-dashboard .container-fluid {
    max-width: 1400px;
    margin: 0 auto;
}

/* Modern Card Styling */
.woocommerce-dashboard .card {
    background: #ffffff;
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    margin-bottom: 2rem;
    border: 1px solid #e5e7eb;
}

.woocommerce-dashboard .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.woocommerce-dashboard .card-header {
    background: #7c3aed;
    color: white;
    border-radius: 16px 16px 0 0 !important;
    padding: 1.5rem 2rem;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.woocommerce-dashboard .card-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.woocommerce-dashboard .card-title i {
    font-size: 1.25rem;
    opacity: 0.9;
}

.woocommerce-dashboard .card-body {
    padding: 2rem;
}

/* Modern Button Styling */
.woocommerce-dashboard .btn {
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

.woocommerce-dashboard .btn-primary {
    background: #7c3aed;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.2);
}

.woocommerce-dashboard .btn-primary:hover {
    background: #6d28d9;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(124, 58, 237, 0.3);
    color: white;
}

.woocommerce-dashboard .btn-secondary {
    background: #6b7280;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(107, 114, 128, 0.2);
}

.woocommerce-dashboard .btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(107, 114, 128, 0.3);
    color: white;
}

.woocommerce-dashboard .btn-success {
    background: #7c3aed;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.2);
}

.woocommerce-dashboard .btn-success:hover {
    background: #6d28d9;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(124, 58, 237, 0.3);
    color: white;
}

/* Modern Form Styling */
.woocommerce-dashboard .form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.woocommerce-dashboard .form-control:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.woocommerce-dashboard .form-group {
    margin-bottom: 1.5rem;
}

.woocommerce-dashboard .form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: block;
}

.woocommerce-dashboard .form-text {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Modern Alert Styling */
.woocommerce-dashboard .alert {
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    font-weight: 500;
}

.woocommerce-dashboard .alert-success {
    background: #7c3aed;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.2);
}

.woocommerce-dashboard .alert-danger {
    background: #ef4444;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
}

.woocommerce-dashboard .alert-warning {
    background: #f59e0b;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);
}

/* Modern Badge Styling */
.woocommerce-dashboard .badge {
    border-radius: 12px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.woocommerce-dashboard .badge-success {
    background: #7c3aed;
    color: white;
}

.woocommerce-dashboard .badge-danger {
    background: #ef4444;
    color: white;
}

.woocommerce-dashboard .badge-warning {
    background: #f59e0b;
    color: white;
}

.woocommerce-dashboard .badge-info {
    background: #06b6d4;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .woocommerce-dashboard {
        padding: 1rem 0;
    }
    
    .woocommerce-dashboard .card-body {
        padding: 1.5rem;
    }
    
    .woocommerce-dashboard .card-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}

/* Loading Animation */
.woocommerce-dashboard .loading {
    opacity: 0.7;
    pointer-events: none;
}

.woocommerce-dashboard .spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Custom Scrollbar */
.woocommerce-dashboard ::-webkit-scrollbar {
    width: 8px;
}

.woocommerce-dashboard ::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.woocommerce-dashboard ::-webkit-scrollbar-thumb {
    background: #7c3aed;
    border-radius: 10px;
}

.woocommerce-dashboard ::-webkit-scrollbar-thumb:hover {
    background: #6d28d9;
}
</style>
@endpush

@section('content')
<div class="woocommerce-dashboard">
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-white">
                        <i class="fas fa-vial"></i>
                        Test WooCommerce Notifications
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.woocommerce.dashboard') }}" class="btn btn-secondary">
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
                                    <h5 class="card-title text-white">Send Test Notification</h5>
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
                                                    <select class="form-control" id="carrier" name="carrier">
                                                        <option value="FedEx">FedEx</option>
                                                        <option value="UPS">UPS</option>
                                                        <option value="DHL">DHL</option>
                                                        <option value="USPS">USPS</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="refund_amount">Refund Amount (for refund)</label>
                                                    <input type="number" step="0.01" class="form-control" id="refund_amount" name="refund_amount" 
                                                           value="25.00">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="refund_reason">Refund Reason (for refund)</label>
                                                    <input type="text" class="form-control" id="refund_reason" name="refund_reason" 
                                                           value="Customer request">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-paper-plane"></i> Send Test Notification
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title text-white">Test Results</h5>
                                </div>
                                <div class="card-body">
                                    <div id="test-results">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-vial fa-2x mb-2"></i>
                                            <p>Send a test notification to see results here</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Test Results -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title text-white">Recent Test Results</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Phone</th>
                                                    <th>Status</th>
                                                    <th>Sent At</th>
                                                    <th>Response</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentTests as $test)
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            {{ ucfirst(str_replace('_', ' ', $test->notification_type)) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $test->phone_number }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $test->status === 'success' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($test->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $test->created_at->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        @if($test->response)
                                                            <button class="btn btn-info btn-sm" onclick="viewResponse('{{ $test->_id }}')">
                                                                <i class="fas fa-eye"></i> View
                                                            </button>
                                                        @else
                                                            <span class="text-muted">No response</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">
                                                        <i class="fas fa-vial fa-2x text-muted mb-2"></i>
                                                        <div class="text-muted">No test results found</div>
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
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle form submission
    $('#test-notification-form').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.addClass('loading').prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: '{{ route("vendor.integration.woocommerce.test_notification") }}',
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success result
                    $('#test-results').html(`
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Success!</strong> Test notification sent successfully.
                            <br>
                            <small>Message ID: ${response.message_id || 'N/A'}</small>
                        </div>
                    `);
                    
                    // Reload page after 2 seconds to show updated results
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    $('#test-results').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error!</strong> ${response.message || 'Failed to send test notification'}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                $('#test-results').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error!</strong> Failed to send test notification
                    </div>
                `);
            },
            complete: function() {
                submitBtn.removeClass('loading').prop('disabled', false);
                submitBtn.html('<i class="fas fa-paper-plane"></i> Send Test Notification');
            }
        });
    });

    // Add smooth animations to cards
    $('.woocommerce-dashboard .card').each(function(index) {
        $(this).css({
            'animation-delay': (index * 0.1) + 's',
            'animation': 'fadeInUp 0.6s ease forwards'
        });
    });

    // Add hover effects to table rows
    $('.woocommerce-dashboard .table tbody tr').hover(
        function() {
            $(this).addClass('table-hover');
        },
        function() {
            $(this).removeClass('table-hover');
        }
    );
});

// Function to view response details
function viewResponse(testId) {
    $.ajax({
        url: `/vendor-console/integration/woocommerce/test-notifications/${testId}/response`,
        method: 'GET',
        success: function(response) {
            Swal.fire({
                title: 'Test Response Details',
                html: `<pre class="text-left">${JSON.stringify(response, null, 2)}</pre>`,
                width: '600px',
                confirmButtonColor: '#7c3aed',
                background: '#f8fafc',
                customClass: {
                    popup: 'rounded-lg shadow-lg',
                    title: 'text-lg font-semibold'
                }
            });
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load response details',
                confirmButtonColor: '#ef4444',
                background: '#f8fafc',
                customClass: {
                    popup: 'rounded-lg shadow-lg',
                    title: 'text-lg font-semibold'
                }
            });
        }
    });
}

// Add CSS animation for fadeInUp
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>
@endpush
@endsection 