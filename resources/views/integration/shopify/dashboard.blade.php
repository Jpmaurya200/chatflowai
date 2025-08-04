@extends('layouts.app')

@section('title', 'Shopify Integration Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i>
                        Shopify Integration Dashboard
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.shopify.settings') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($integration && $integration->isActive())
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Connected!</strong> Your Shopify store is connected and active.
                            <br>
                            <small>Shop Domain: {{ $integration->shop_domain }}</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Not Connected!</strong> Please connect your Shopify store to start receiving order notifications.
                            <br>
                            <a href="{{ route('vendor.integration.shopify.settings') }}" class="btn btn-primary btn-sm mt-2">
                                Connect Shopify
                            </a>
                        </div>
                    @endif

                    @if($integration && $integration->isActive())
                        <!-- Statistics Cards -->
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ $orderStatistics['total_orders'] ?? 0 }}</h3>
                                        <p>Total Orders</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ $orderStatistics['paid_orders'] ?? 0 }}</h3>
                                        <p>Paid Orders</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ $orderStatistics['fulfilled_orders'] ?? 0 }}</h3>
                                        <p>Fulfilled Orders</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>{{ $notificationStatistics['total_notifications'] ?? 0 }}</h3>
                                        <p>Notifications Sent</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Orders</h3>
                                        <div class="card-tools">
                                            <a href="{{ route('vendor.integration.shopify.orders') }}" class="btn btn-primary btn-sm">
                                                View All Orders
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
                                                        <td>{{ $order->order_number }}</td>
                                                        <td>
                                                            @if($order->name)
                                                                {{ $order->name }}
                                                                @if($order->email)
                                                                    <br><small>{{ $order->email }}</small>
                                                                @endif
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-{{ $order->status === 'open' ? 'primary' : ($order->status === 'closed' ? 'success' : 'danger') }}">
                                                                {{ $order->status_label }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $order->formatted_total_price }}</td>
                                                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                                        <td>
                                                            <a href="{{ route('vendor.integration.shopify.order_details', $order->shopify_order_id) }}" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No orders found</td>
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
                                        <h3 class="card-title">Recent Notifications</h3>
                                        <div class="card-tools">
                                            <a href="{{ route('vendor.integration.shopify.notifications') }}" class="btn btn-primary btn-sm">
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
                                                        <td>{{ $notification->order->order_number ?? 'N/A' }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'failed' ? 'danger' : 'warning') }}">
                                                                {{ $notification->status_label }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $notification->sent_at ? $notification->sent_at->format('M d, Y H:i') : 'N/A' }}</td>
                                                        <td>
                                                            @if($notification->isFailed())
                                                                <button class="btn btn-sm btn-warning resend-notification" data-notification-id="{{ $notification->_id }}">
                                                                    <i class="fas fa-redo"></i> Resend
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">No notifications found</td>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Handle resend notification
    $('.resend-notification').click(function() {
        const notificationId = $(this).data('notification-id');
        const button = $(this);
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: `/vendor-console/integration/shopify/notifications/${notificationId}/resend`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Notification resent successfully');
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
    });
});
</script>
@endpush
@endsection 