@extends('layouts.app')

@section('title', 'Order Details')

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

.woocommerce-dashboard .table-borderless td {
    border: none;
    padding: 0.75rem 0;
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

.woocommerce-dashboard .badge-secondary {
    background: #6b7280;
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
                        <i class="fas fa-shopping-cart"></i>
                        Order Details - {{ $order->order_number }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.woocommerce.orders') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($order)
                        <!-- Order Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                        <h5 class="card-title">{{ $order->items_count }}</h5>
                                        <p class="card-text text-muted">Items</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-sort-numeric-up fa-2x text-success mb-2"></i>
                                        <h5 class="card-title">{{ $order->total_items_quantity }}</h5>
                                        <p class="card-text text-muted">Total Quantity</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                                        <h5 class="card-title">{{ $order->formatted_total_price }}</h5>
                                        <p class="card-text text-muted">Total Amount</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-bell fa-2x text-info mb-2"></i>
                                        <h5 class="card-title">{{ $notifications->count() }}</h5>
                                        <p class="card-text text-muted">Notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Order Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title text-white">Order Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Order Number:</strong></td>
                                                <td>{{ $order->order_number }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>WooCommerce Order ID:</strong></td>
                                                <td>{{ $order->woocommerce_order_id }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'processing' ? 'warning' : ($order->status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Payment Status:</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $order->isPaid() ? 'success' : 'warning' }}">
                                                        {{ $order->isPaid() ? 'Paid' : 'Pending' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total:</strong></td>
                                                <td><strong>{{ $order->formatted_total_price }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Currency:</strong></td>
                                                <td>{{ strtoupper($order->currency) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created:</strong></td>
                                                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Updated:</strong></td>
                                                <td>{{ $order->updated_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title text-white">Customer Information</h5>
                                    </div>
                                    <div class="card-body">
                                        
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td>{{ $order->customer_name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>{{ $order->customer_email }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone:</strong></td>
                                                <td>{{ $order->customer_phone }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Payment Method:</strong></td>
                                                <td>{{ $order->payment_method }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title text-white">Order Items</h5>
                                    </div>
                                    <div class="card-body">
                                        
                                        <div class="table-responsive">
                                            <table class="table table-hover text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>SKU</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($order->order_items as $item)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $item['name'] ?? 'N/A' }}</strong>
                                                            @if(isset($item['variation_id']))
                                                                <br>
                                                                <small class="text-muted">Variation ID: {{ $item['variation_id'] }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $item['sku'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['quantity'] ?? 0 }}</td>
                                                        <td>{{ isset($item['price']) ? $order->currency . ' ' . number_format($item['price'], 2) : 'N/A' }}</td>
                                                        <td>
                                                            <strong>
                                                                {{ isset($item['price'], $item['quantity']) ? $order->currency . ' ' . number_format($item['price'] * $item['quantity'], 2) : 'N/A' }}
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4">
                                                            <i class="fas fa-box fa-2x text-muted mb-2"></i>
                                                            <div class="text-muted">No items found</div>
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

                        <!-- Order Summary -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title text-white">Order Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Subtotal:</strong></td>
                                                <td>{{ $order->formatted_subtotal }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tax:</strong></td>
                                                <td>{{ $order->formatted_tax_total }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Shipping:</strong></td>
                                                <td>{{ $order->formatted_shipping_total }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Discount:</strong></td>
                                                <td>{{ $order->formatted_discount_total }}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong>Total:</strong></td>
                                                <td><strong>{{ $order->formatted_total_price }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title text-white">Shipping Information</h5>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($order->order_data['shipping']) && !empty($order->order_data['shipping']))
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td>{{ $order->order_data['shipping']['first_name'] ?? '' }} {{ $order->order_data['shipping']['last_name'] ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Address:</strong></td>
                                                <td>
                                                    {{ $order->order_data['shipping']['address_1'] ?? '' }}<br>
                                                    @if(!empty($order->order_data['shipping']['address_2']))
                                                        {{ $order->order_data['shipping']['address_2'] }}<br>
                                                    @endif
                                                    {{ $order->order_data['shipping']['city'] ?? '' }}, {{ $order->order_data['shipping']['state'] ?? '' }} {{ $order->order_data['shipping']['postcode'] ?? '' }}<br>
                                                    {{ $order->order_data['shipping']['country'] ?? '' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone:</strong></td>
                                                <td>{{ $order->order_data['shipping']['phone'] ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                        @else
                                        <div class="text-center text-muted">
                                            <i class="fas fa-shipping-fast fa-2x mb-2"></i>
                                            <div>No shipping information available</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title text-white">Billing Information</h5>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($order->order_data['billing']) && !empty($order->order_data['billing']))
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td>{{ $order->order_data['billing']['first_name'] ?? '' }} {{ $order->order_data['billing']['last_name'] ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Address:</strong></td>
                                                <td>
                                                    {{ $order->order_data['billing']['address_1'] ?? '' }}<br>
                                                    @if(!empty($order->order_data['billing']['address_2']))
                                                        {{ $order->order_data['billing']['address_2'] }}<br>
                                                    @endif
                                                    {{ $order->order_data['billing']['city'] ?? '' }}, {{ $order->order_data['billing']['state'] ?? '' }} {{ $order->order_data['billing']['postcode'] ?? '' }}<br>
                                                    {{ $order->order_data['billing']['country'] ?? '' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone:</strong></td>
                                                <td>{{ $order->order_data['billing']['phone'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>{{ $order->order_data['billing']['email'] ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                        @else
                                        <div class="text-center text-muted">
                                            <i class="fas fa-credit-card fa-2x mb-2"></i>
                                            <div>No billing information available</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title text-white">Order Notifications</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Status</th>
                                                        <th>Sent At</th>
                                                        <th>Delivered At</th>
                                                        <th>Read At</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($notifications as $notification)
                                                    <tr>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                {{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'failed' ? 'danger' : 'warning') }}">
                                                                {{ ucfirst($notification->status) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $notification->sent_at ? $notification->sent_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                        <td>{{ $notification->delivered_at ? $notification->delivered_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                        <td>{{ $notification->read_at ? $notification->read_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                        <td>
                                                            @if($notification->status === 'failed')
                                                                <button class="btn btn-warning btn-sm resend-notification" data-notification-id="{{ $notification->_id }}">
                                                                    <i class="fas fa-redo"></i> Resend
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4">
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
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                            <h5>Order Not Found</h5>
                            <p class="text-muted">The requested order could not be found.</p>
                            <a href="{{ route('vendor.integration.woocommerce.orders') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back to Orders
                            </a>
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