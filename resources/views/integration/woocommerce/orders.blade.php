@extends('layouts.app')

@section('title', 'WooCommerce Orders')

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
                        WooCommerce Orders
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
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="payment-status-filter">
                                <option value="">All Payment Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="failed">Failed</option>
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
                        <table class="table table-hover text-nowrap" id="orders-table">
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
                                        <small class="text-muted">{{ $order->woocommerce_order_id }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->customer_name }}</strong>
                                            @if($order->customer_email && $order->customer_email !== 'N/A')
                                                <br>
                                                <small class="text-muted">{{ $order->customer_email }}</small>
                                            @endif
                                            @if($order->customer_phone && $order->customer_phone !== 'N/A')
                                                <br>
                                                <small class="text-muted">{{ $order->customer_phone }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $order->formatted_total_price }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->currency }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'processing' ? 'warning' : ($order->status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->fulfillment_status === 'fulfilled' ? 'success' : ($order->fulfillment_status === 'unfulfilled' ? 'warning' : 'info') }}">
                                            {{ ucfirst($order->fulfillment_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('vendor.integration.woocommerce.order_details', $order->woocommerce_order_id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <div class="text-muted">No orders found</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->links() }}
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
    $('#status-filter, #payment-status-filter, #fulfillment-status-filter').change(function() {
        filterOrders();
    });

    $('#search-filter').on('keyup', function() {
        filterOrders();
    });

    function filterOrders() {
        const status = $('#status-filter').val();
        const paymentStatus = $('#payment-status-filter').val();
        const fulfillmentStatus = $('#fulfillment-status-filter').val();
        const search = $('#search-filter').val().toLowerCase();

        $('#orders-table tbody tr').each(function() {
            let show = true;
            const row = $(this);

            // Status filter
            if (status && row.find('td:nth-child(4) .badge').text().toLowerCase() !== status.toLowerCase()) {
                show = false;
            }

            // Payment status filter
            if (paymentStatus && row.find('td:nth-child(5) .badge').text().toLowerCase().replace(/\s+/g, '_') !== paymentStatus.toLowerCase()) {
                show = false;
            }

            // Fulfillment status filter
            if (fulfillmentStatus && row.find('td:nth-child(6) .badge').text().toLowerCase().replace(/\s+/g, '_') !== fulfillmentStatus.toLowerCase()) {
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