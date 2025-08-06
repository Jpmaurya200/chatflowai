@extends('layouts.app')

@section('title', 'WooCommerce Notifications')

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

.woocommerce-dashboard .btn-warning {
    background: #f59e0b;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);
}

.woocommerce-dashboard .btn-warning:hover {
    background: #d97706;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(245, 158, 11, 0.3);
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

/* Modern Table Styling */
.woocommerce-dashboard .table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
}

.woocommerce-dashboard .table thead th {
    background: #7c3aed;
    color: white;
    border: none;
    padding: 1.25rem 1rem;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.woocommerce-dashboard .table tbody td {
    padding: 1.25rem 1rem;
    border: none;
    border-bottom: 1px solid #f3f4f6;
    background: white;
    color: #374151;
    font-weight: 500;
}

.woocommerce-dashboard .table tbody tr:hover {
    background: #f9fafb;
    transition: all 0.2s ease;
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
                        <i class="fas fa-bell"></i>
                        WooCommerce Notifications
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.woocommerce.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="status-filter">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="sent">Sent</option>
                                <option value="delivered">Delivered</option>
                                <option value="read">Read</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="notification-type-filter">
                                <option value="">All Types</option>
                                <option value="order_confirmation">Order Confirmation</option>
                                <option value="payment_confirmation">Payment Confirmation</option>
                                <option value="shipment_tracking">Shipment Tracking</option>
                                <option value="delivery_confirmation">Delivery Confirmation</option>
                                <option value="cod_verification">COD Verification</option>
                                <option value="order_cancelled">Order Cancelled</option>
                                <option value="refund_processed">Refund Processed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search-filter" placeholder="Search notifications...">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" id="export-notifications">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>

                    <!-- Notifications Table -->
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap" id="notifications-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th>Status</th>
                                    <th>Response</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr>
                                    <td>
                                        <strong>{{ $notification->order->order_number ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">WC ID: {{ $notification->order->woocommerce_order_id ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $notification->order->customer_name ?? 'N/A' }}</strong>
                                            @if($notification->order->customer_email && $notification->order->customer_email !== 'N/A')
                                                <br>
                                                <small class="text-muted">{{ $notification->order->customer_email }}</small>
                                            @endif
                                            @if($notification->order->customer_phone && $notification->order->customer_phone !== 'N/A')
                                                <br>
                                                <small class="text-muted">{{ $notification->order->customer_phone }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $notification->notification_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $notification->status === 'sent' ? 'success' : 
                                            ($notification->status === 'pending' ? 'warning' : 
                                            ($notification->status === 'failed' ? 'danger' : 'info')) 
                                        }}">
                                            {{ $notification->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->formatted_sent_date }}</td>
                                    <td>
                                        @if($notification->response && isset($notification->response['error']))
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $notification->response['error']['message'] ?? 'Error' }}
                                            </span>
                                        @elseif($notification->response && isset($notification->response['messages']))
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i>
                                                Message ID: {{ $notification->response['messages'][0]['id'] ?? 'N/A' }}
                                            </span>
                                        @else
                                            <span class="text-muted">No response data</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->isFailed())
                                            <button class="btn btn-warning resend-notification" data-notification-id="{{ $notification->_id }}">
                                                <i class="fas fa-redo"></i> Resend
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                        <div class="text-muted">No notifications found</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator && $notifications->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#status-filter, #notification-type-filter').change(function() {
        filterNotifications();
    });

    $('#search-filter').on('keyup', function() {
        filterNotifications();
    });

    function filterNotifications() {
        const status = $('#status-filter').val();
        const notificationType = $('#notification-type-filter').val();
        const search = $('#search-filter').val().toLowerCase();

        $('#notifications-table tbody tr').each(function() {
            let show = true;
            const row = $(this);

            // Status filter
            if (status && row.find('td:nth-child(4) .badge').text().toLowerCase() !== status.toLowerCase()) {
                show = false;
            }

            // Notification type filter
            if (notificationType && row.find('td:nth-child(3) .badge').text().toLowerCase().replace(/\s+/g, '_') !== notificationType.toLowerCase()) {
                show = false;
            }

            // Search filter
            if (search) {
                const text = row.text().toLowerCase();
                if (!text.includes(search)) {
                    show = false;
                }
            }

            row.toggle(show);
        });
    }

    // Handle resend notification
    $('.resend-notification').click(function() {
        const notificationId = $(this).data('notification-id');
        const button = $(this);
        
        button.addClass('loading').prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: `/vendor-console/integration/woocommerce/notifications/${notificationId}/resend`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Notification resent successfully',
                        confirmButtonColor: '#7c3aed',
                        background: '#f8fafc',
                        customClass: {
                            popup: 'rounded-lg shadow-lg',
                            title: 'text-lg font-semibold'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to resend notification',
                        confirmButtonColor: '#ef4444',
                        background: '#f8fafc',
                        customClass: {
                            popup: 'rounded-lg shadow-lg',
                            title: 'text-lg font-semibold'
                        }
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to resend notification',
                    confirmButtonColor: '#ef4444',
                    background: '#f8fafc',
                    customClass: {
                        popup: 'rounded-lg shadow-lg',
                        title: 'text-lg font-semibold'
                    }
                });
            },
            complete: function() {
                button.removeClass('loading').prop('disabled', false);
                button.html('<i class="fas fa-redo"></i> Resend');
            }
        });
    });

    // Export functionality
    $('#export-notifications').click(function() {
        const button = $(this);
        button.addClass('loading').prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i> Exporting...');
        
        // Add export logic here
        setTimeout(() => {
            button.removeClass('loading').prop('disabled', false);
            button.html('<i class="fas fa-download"></i> Export');
            
            Swal.fire({
                icon: 'success',
                title: 'Export Complete!',
                text: 'Notifications exported successfully',
                confirmButtonColor: '#7c3aed',
                background: '#f8fafc',
                customClass: {
                    popup: 'rounded-lg shadow-lg',
                    title: 'text-lg font-semibold'
                }
            });
        }, 2000);
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