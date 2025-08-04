@extends('layouts.app')

@section('title', 'Shopify Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i>
                        Shopify Notifications
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vendor.integration.shopify.dashboard') }}" class="btn btn-secondary btn-sm">
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
                        <table class="table table-striped" id="notifications-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
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
                                        <strong>{{ $notification->order->order_number ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $notification->order->shopify_order_id ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $notification->order->name ?? 'N/A' }}</strong>
                                            @if($notification->order->email)
                                                <br>
                                                <small class="text-muted">{{ $notification->order->email }}</small>
                                            @endif
                                            @if($notification->order->phone)
                                                <br>
                                                <small class="text-muted">{{ $notification->order->phone }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $notification->status === 'sent' ? 'success' : 
                                            ($notification->status === 'pending' ? 'warning' : 
                                            ($notification->status === 'failed' ? 'danger' : 'info')) 
                                        }}">
                                            {{ ucfirst($notification->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($notification->sent_at)
                                            <div>
                                                <strong>{{ $notification->sent_at->format('M d, Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $notification->sent_at->format('H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Not sent</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->delivered_at)
                                            <div>
                                                <strong>{{ $notification->delivered_at->format('M d, Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $notification->delivered_at->format('H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Not delivered</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->read_at)
                                            <div>
                                                <strong>{{ $notification->read_at->format('M d, Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $notification->read_at->format('H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Not read</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($notification->status === 'failed')
                                                <button class="btn btn-sm btn-warning resend-notification" 
                                                        data-notification-id="{{ $notification->_id }}">
                                                    <i class="fas fa-redo"></i> Resend
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-info view-notification-details" 
                                                    data-notification-id="{{ $notification->_id }}">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                            <h5>No notifications found</h5>
                                            <p class="text-muted">WhatsApp notifications for your Shopify orders will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator && $notifications->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $notifications->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notificationDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#status-filter, #notification-type-filter').change(function() {
        applyFilters();
    });

    $('#search-filter').on('keyup', function() {
        applyFilters();
    });

    function applyFilters() {
        const status = $('#status-filter').val();
        const notificationType = $('#notification-type-filter').val();
        const search = $('#search-filter').val();

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (notificationType) params.append('notification_type', notificationType);
        if (search) params.append('search', search);

        window.location.href = '{{ route("vendor.integration.shopify.notifications") }}?' + params.toString();
    }

    // Set current filter values from URL
    const urlParams = new URLSearchParams(window.location.search);
    $('#status-filter').val(urlParams.get('status') || '');
    $('#notification-type-filter').val(urlParams.get('notification_type') || '');
    $('#search-filter').val(urlParams.get('search') || '');

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

    // View notification details
    $('.view-notification-details').click(function() {
        const notificationId = $(this).data('notification-id');
        
        $.ajax({
            url: '{{ route("vendor.integration.shopify.notifications") }}',
            method: 'GET',
            data: { notification_id: notificationId },
            success: function(response) {
                $('#notificationDetailsContent').html(response);
                $('#notificationDetailsModal').modal('show');
            },
            error: function(xhr) {
                toastr.error('Failed to load notification details');
            }
        });
    });

    // Export notifications
    $('#export-notifications').click(function() {
        const status = $('#status-filter').val();
        const notificationType = $('#notification-type-filter').val();
        const search = $('#search-filter').val();

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (notificationType) params.append('notification_type', notificationType);
        if (search) params.append('search', search);
        params.append('export', '1');

        window.location.href = '{{ route("vendor.integration.shopify.notifications") }}?' + params.toString();
    });
});
</script>
@endpush
@endsection 