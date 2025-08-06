@extends('layouts.app')

@section('title', 'Order Details')

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

.shopify-dashboard .btn-info {
    background: #06b6d4;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(6, 182, 212, 0.2);
}

.shopify-dashboard .btn-info:hover {
    background: #0891b2;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(6, 182, 212, 0.3);
    color: white;
}

/* Modern Table Styling */
.shopify-dashboard .table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
}

.shopify-dashboard .table thead th {
    background: #10b981;
    color: white;
    border: none;
    padding: 1.25rem 1rem;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.shopify-dashboard .table tbody td {
    padding: 1.25rem 1rem;
    border: none;
    border-bottom: 1px solid #f3f4f6;
    background: white;
    color: #374151;
    font-weight: 500;
}

.shopify-dashboard .table tbody tr:hover {
    background: #f9fafb;
    transition: all 0.2s ease;
}

.shopify-dashboard .table-borderless td {
    border: none;
    padding: 0.75rem 0;
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

.shopify-dashboard .badge-secondary {
    background: #6b7280;
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
                        <i class="fas fa-shopping-cart"></i>
                        Order Details - {{ $order->order_number }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.shopify.orders') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($order)
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
                                                <td><strong>Shopify Order ID:</strong></td>
                                                <td>{{ $order->shopify_order_id }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $order->status === 'open' ? 'success' : ($order->status === 'closed' ? 'secondary' : 'danger') }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Payment Status:</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $order->financial_status === 'paid' ? 'success' : ($order->financial_status === 'pending' ? 'warning' : 'info') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $order->financial_status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Fulfillment Status:</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $order->fulfillment_status === 'fulfilled' ? 'success' : ($order->fulfillment_status === 'unfulfilled' ? 'warning' : 'info') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $order->fulfillment_status)) }}
                                                    </span>
                                                </td>
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
                                                <td>{{ $order->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>{{ $order->email ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone:</strong></td>
                                                <td>{{ $order->phone ?: 'N/A' }}</td>
                                            </tr>
                                            @if($order->contact)
                                            <tr>
                                                <td><strong>Contact:</strong></td>
                                                <td>
                                                    <a href="{{ route('vendor.contact.read.update.data', $order->contact->_uid) }}" class="btn btn-info btn-sm" data-toggle="modal" data-target="#lwEditContact">
                                                        <i class="fas fa-user"></i> View Contact
                                                    </a>
                                                </td>
                                            </tr>
                                            @endif
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
                                                    @forelse(($order->getLineItems() ?: $order->forceDecodeData()['line_items'] ?? []) as $item)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $item['title'] ?? $item['name'] ?? 'N/A' }}</strong>
                                                            @if(isset($item['variant_title']) && $item['variant_title'])
                                                                <br>
                                                                <small class="text-muted">{{ $item['variant_title'] }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $item['sku'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['quantity'] ?? 0 }}</td>
                                                        <td>{{ $item['price'] ? $order->currency . ' ' . number_format($item['price'], 2) : 'N/A' }}</td>
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
                                                <td>{{ $order->formatted_subtotal_price ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tax:</strong></td>
                                                <td>{{ $order->formatted_total_tax ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Shipping:</strong></td>
                                                <td>{{ $order->formatted_total_shipping_price ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Discount:</strong></td>
                                                <td>{{ $order->formatted_total_discounts ?? 'N/A' }}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong>Total:</strong></td>
                                                <td><strong>{{ $order->formatted_total_price ?? 'N/A' }}</strong></td>
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
                                        @if($order->shipping_address)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td>{{ $order->shipping_address['name'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Address:</strong></td>
                                                <td>
                                                    {{ $order->shipping_address['address1'] ?? '' }}<br>
                                                    @if($order->shipping_address['address2'])
                                                        {{ $order->shipping_address['address2'] }}<br>
                                                    @endif
                                                    {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['province'] ?? '' }} {{ $order->shipping_address['zip'] ?? '' }}<br>
                                                    {{ $order->shipping_address['country'] ?? '' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone:</strong></td>
                                                <td>{{ $order->shipping_address['phone'] ?? 'N/A' }}</td>
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
                                                    @forelse($order->notifications ?? [] as $notification)
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
                                                            @if($notification->isFailed())
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
                            <a href="{{ route('vendor.integration.shopify.orders') }}" class="btn btn-primary">
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
            url: `/vendor-console/integration/shopify/notifications/${notificationId}/resend`,
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
                        confirmButtonColor: '#10b981',
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
    $('.shopify-dashboard .card').each(function(index) {
        $(this).css({
            'animation-delay': (index * 0.1) + 's',
            'animation': 'fadeInUp 0.6s ease forwards'
        });
    });

    // Add hover effects to table rows
    $('.shopify-dashboard .table tbody tr').hover(
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

{{-- Include Contact Edit Modal --}}
@include('contact.contact-edit-modal-partial')

@endsection 