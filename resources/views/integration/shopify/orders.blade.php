@extends('layouts.app')

@section('title', 'Shopify Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i>
                        Shopify Orders
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
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="financial-status-filter">
                                <option value="">All Payment Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="partially_paid">Partially Paid</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="fulfillment-status-filter">
                                <option value="">All Fulfillment Statuses</option>
                                <option value="unfulfilled">Unfulfilled</option>
                                <option value="fulfilled">Fulfilled</option>
                                <option value="partial">Partially Fulfilled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search-filter" placeholder="Search orders...">
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Fulfillment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->shopify_order_id }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->name }}</strong>
                                            @if($order->email)
                                                <br>
                                                <small class="text-muted">{{ $order->email }}</small>
                                            @endif
                                            @if($order->phone)
                                                <br>
                                                <small class="text-muted">{{ $order->phone }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $order->formatted_total_price }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->currency }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->status === 'open' ? 'success' : ($order->status === 'closed' ? 'secondary' : 'danger') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->financial_status === 'paid' ? 'success' : ($order->financial_status === 'pending' ? 'warning' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->financial_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->fulfillment_status === 'fulfilled' ? 'success' : ($order->fulfillment_status === 'unfulfilled' ? 'warning' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->fulfillment_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->created_at->format('M d, Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('vendor.integration.shopify.order_details', $order->shopify_order_id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                            <h5>No orders found</h5>
                                            <p class="text-muted">Orders from your Shopify store will appear here once they are synced.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $orders->links() }}
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
    // Filter functionality
    $('#status-filter, #financial-status-filter, #fulfillment-status-filter').change(function() {
        applyFilters();
    });

    $('#search-filter').on('keyup', function() {
        applyFilters();
    });

    function applyFilters() {
        const status = $('#status-filter').val();
        const financialStatus = $('#financial-status-filter').val();
        const fulfillmentStatus = $('#fulfillment-status-filter').val();
        const search = $('#search-filter').val();

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (financialStatus) params.append('financial_status', financialStatus);
        if (fulfillmentStatus) params.append('fulfillment_status', fulfillmentStatus);
        if (search) params.append('search', search);

        window.location.href = '{{ route("vendor.integration.shopify.orders") }}?' + params.toString();
    }

    // Set current filter values from URL
    const urlParams = new URLSearchParams(window.location.search);
    $('#status-filter').val(urlParams.get('status') || '');
    $('#financial-status-filter').val(urlParams.get('financial_status') || '');
    $('#fulfillment-status-filter').val(urlParams.get('fulfillment_status') || '');
    $('#search-filter').val(urlParams.get('search') || '');
});
</script>
@endpush
@endsection 