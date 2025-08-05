@extends('layouts.app')

@section('title', 'WooCommerce Integration Dashboard')

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

.woocommerce-dashboard .alert-warning {
    background: #f59e0b;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);
}

/* Modern Statistics Cards */
.woocommerce-dashboard .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.woocommerce-dashboard .stat-card {
    background: #ffffff;
    color: #1f2937;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
}

.woocommerce-dashboard .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.woocommerce-dashboard .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #7c3aed;
    transition: all 0.3s ease;
}

.woocommerce-dashboard .stat-card:hover::before {
    width: 8px;
}

.woocommerce-dashboard .stat-card.stat-success {
    border-left: 4px solid #7c3aed;
}

.woocommerce-dashboard .stat-card.stat-warning {
    border-left: 4px solid #f59e0b;
}

.woocommerce-dashboard .stat-card.stat-danger {
    border-left: 4px solid #ef4444;
}

.woocommerce-dashboard .stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: block;
    color: #1f2937;
}

.woocommerce-dashboard .stat-label {
    font-size: 1rem;
    color: #6b7280;
    font-weight: 500;
}

.woocommerce-dashboard .stat-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2rem;
    opacity: 0.1;
    color: #7c3aed;
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

.woocommerce-dashboard .btn-info {
    background: #06b6d4;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(6, 182, 212, 0.2);
}

.woocommerce-dashboard .btn-info:hover {
    background: #0891b2;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(6, 182, 212, 0.3);
    color: white;
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

.woocommerce-dashboard .badge-primary {
    background: #7c3aed;
    color: white;
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
    
    .woocommerce-dashboard .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .woocommerce-dashboard .stat-number {
        font-size: 2rem;
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
                        <i class="fas fa-shopping-cart"></i>
                        WooCommerce Dashboard
                    </h3>
                    <div class="card-tools">
                            <a href="{{ route('vendor.integration.woocommerce.settings') }}" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($integration && $integration->isActive())
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Connected!</strong> Your WooCommerce store is connected and active.
                            <br>
                            <small>Site URL: {{ $integration->site_url }}</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Not Connected!</strong> Please connect your WooCommerce store to start receiving order notifications.
                            <br>
                                <a href="{{ route('vendor.integration.woocommerce.settings') }}" class="btn btn-primary mt-3">
                                Connect WooCommerce
                            </a>
                        </div>
                    @endif

                    @if($integration && $integration->isActive())
                            <!-- Modern Statistics Cards -->
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <span class="stat-number">{{ $orderStatistics['total_orders'] ?? 0 }}</span>
                                    <div class="stat-label">Total Orders</div>
                                </div>
                                <div class="stat-card stat-success">
                                    <div class="stat-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <span class="stat-number">{{ $orderStatistics['paid_orders'] ?? 0 }}</span>
                                    <div class="stat-label">Paid Orders</div>
                                </div>
                                <div class="stat-card stat-warning">
                                    <div class="stat-icon">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <span class="stat-number">{{ $orderStatistics['fulfilled_orders'] ?? 0 }}</span>
                                    <div class="stat-label">Fulfilled Orders</div>
                                </div>
                                <div class="stat-card stat-danger">
                                    <div class="stat-icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <span class="stat-number">{{ $notificationStatistics['total_notifications'] ?? 0 }}</span>
                                    <div class="stat-label">Notifications Sent</div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                            <h3 class="card-title text-white">
                                                <i class="fas fa-list"></i>
                                                Recent Orders
                                            </h3>
                                        <div class="card-tools">
                                                <a href="{{ route('vendor.integration.woocommerce.orders') }}" class="btn btn-primary">
                                                View All Orders
                                            </a>
                                                <a href="{{ route('vendor.integration.woocommerce.test_notifications') }}" class="btn btn-warning">
                                                <i class="fas fa-vial"></i> Test Notifications
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-hover text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Customer</th>
                                                    <th>Status</th>
                                                    <th>Total</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentOrders as $order)
                                                    <tr>
                                                            <td><strong>{{ $order->order_number }}</strong></td>
                                                        <td>
                                                            @if($order->customer_name)
                                                                    <div>{{ $order->customer_name }}</div>
                                                                @if($order->customer_email)
                                                                        <small class="text-muted">{{ $order->customer_email }}</small>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'completed' ? 'success' : 'primary') }}">
                                                                {{ $order->status_label }}
                                                            </span>
                                                        </td>
                                                            <td><strong>{{ $order->formatted_total_price }}</strong></td>
                                                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                                        <td>
                                                                <a href="{{ route('vendor.integration.woocommerce.order_details', $order->woocommerce_order_id) }}" class="btn btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                            <td colspan="6" class="text-center py-4">
                                                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                                <div class="text-muted">No orders found</div>
                                                            </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Notifications -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                            <h3 class="card-title text-white">
                                                <i class="fas fa-bell "></i>
                                                Recent Notifications
                                            </h3>
                                        <div class="card-tools">
                                                <a href="{{ route('vendor.integration.woocommerce.notifications') }}" class="btn btn-primary">
                                                View All Notifications
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-hover text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Order #</th>
                                                    <th>Status</th>
                                                    <th>Sent At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentNotifications as $notification)
                                                    <tr>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                {{ $notification->notification_type_label }}
                                                            </span>
                                                        </td>
                                                            <td><strong>{{ $notification->order->order_number ?? 'N/A' }}</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'failed' ? 'danger' : 'warning') }}">
                                                                {{ $notification->status_label }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $notification->sent_at ? $notification->sent_at->format('M d, Y H:i') : 'N/A' }}</td>
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
                                                            <td colspan="5" class="text-center py-4">
                                                                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                                                <div class="text-muted">No notifications found</div>
                                                            </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
    // Handle resend notification with modern loading state
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
                    // Show success message with modern styling
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