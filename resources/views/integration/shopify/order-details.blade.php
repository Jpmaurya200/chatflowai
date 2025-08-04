@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i>
                        Order Details - {{ $order->order_number }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.shopify.orders') }}" class="btn btn-secondary btn-sm">
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
                                        <h5 class="card-title">Order Information</h5>
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
                                        <h5 class="card-title">Customer Information</h5>
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
                                                    <a href="{{ route('vendor.chat_message.contact.view', $order->contact->_uid) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-comments"></i> View Chat
                                                    </a>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Financial Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Subtotal</h6>
                                                    <h4 class="text-primary">{{ $order->formatted_subtotal_price }}</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Tax</h6>
                                                    <h4 class="text-warning">{{ $order->formatted_total_tax }}</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Discounts</h6>
                                                    <h4 class="text-success">{{ $order->formatted_total_discounts }}</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Total</h6>
                                                    <h4 class="text-danger">{{ $order->formatted_total_price }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Line Items -->
                        @if($order->line_items && count($order->line_items) > 0)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Order Items</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
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
                                                    @foreach($order->line_items as $item)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $item['name'] ?? 'Unknown Product' }}</strong>
                                                            @if(isset($item['variant_title']))
                                                                <br>
                                                                <small class="text-muted">{{ $item['variant_title'] }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $item['sku'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['quantity'] ?? 1 }}</td>
                                                        <td>{{ $order->currency }} {{ number_format($item['price'] ?? 0, 2) }}</td>
                                                        <td>{{ $order->currency }} {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Notifications -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">WhatsApp Notifications</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($order->notifications && count($order->notifications) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th>Status</th>
                                                            <th>Sent At</th>
                                                            <th>Delivered At</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($order->notifications as $notification)
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-info">
                                                                    {{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'pending' ? 'warning' : 'danger') }}">
                                                                    {{ ucfirst($notification->status) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $notification->sent_at ? $notification->sent_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                            <td>{{ $notification->delivered_at ? $notification->delivered_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                            <td>
                                                                @if($notification->status === 'failed')
                                                                <button class="btn btn-sm btn-warning resend-notification" 
                                                                        data-notification-id="{{ $notification->_id }}">
                                                                    <i class="fas fa-redo"></i> Resend
                                                                </button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                                <h5>No notifications sent</h5>
                                                <p class="text-muted">WhatsApp notifications for this order will appear here.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5>Order not found</h5>
                            <p class="text-muted">The requested order could not be found.</p>
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
    // Resend notification functionality
    $('.resend-notification').click(function() {
        const notificationId = $(this).data('notification-id');
        const button = $(this);
        
        if (confirm('Are you sure you want to resend this notification?')) {
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Resending...');
            
            $.ajax({
                url: '{{ route("vendor.integration.shopify.resend_notification", "") }}/' + notificationId,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Notification resent successfully!');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message || 'Failed to resend notification');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to resend notification');
                },
                complete: function() {
                    button.prop('disabled', false).html('<i class="fas fa-redo"></i> Resend');
                }
            });
        }
    });
});
</script>
@endpush
@endsection 