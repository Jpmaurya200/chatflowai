@extends('layouts.app', ['title' => __tr('WhatsApp Orders')])

@section('content')
<div class="lw-page-content">
    <!-- Page header -->
    <div class="lw-page-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3>{{ __tr('WhatsApp Orders') }}</h3>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.console') }}">{{ __tr('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __tr('WhatsApp Orders') }}</li>
                    </ol>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <button type="button" class="btn btn-primary" onclick="refreshOrders()">
                            <i class="fa fa-refresh"></i> {{ __tr('Refresh') }}
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportOrders()">
                            <i class="fa fa-download"></i> {{ __tr('Export') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $orderStatistics['total_orders'] ?? 0 }}</h4>
                                <p class="mb-0">{{ __tr('Total Orders') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $orderStatistics['pending_orders'] ?? 0 }}</h4>
                                <p class="mb-0">{{ __tr('Pending Orders') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-clock-o fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $orderStatistics['completed_orders'] ?? 0 }}</h4>
                                <p class="mb-0">{{ __tr('Completed Orders') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $orderStatistics['cancelled_orders'] ?? 0 }}</h4>
                                <p class="mb-0">{{ __tr('Cancelled Orders') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Statistics -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ formatAmount($orderStatistics['total_revenue'] ?? 0) }}</h4>
                                <p class="mb-0">{{ __tr('Total Revenue') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-money fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ formatAmount($orderStatistics['pending_revenue'] ?? 0) }}</h4>
                                <p class="mb-0">{{ __tr('Pending Revenue') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-clock-o fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __tr('Filters') }}</h5>
            </div>
            <div class="card-body">
                <form id="ordersFilterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __tr('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="">{{ __tr('All Statuses') }}</option>
                                    <option value="pending">{{ __tr('Pending') }}</option>
                                    <option value="awaiting_address">{{ __tr('Awaiting Address') }}</option>
                                    <option value="awaiting_payment">{{ __tr('Awaiting Payment') }}</option>
                                    <option value="paid">{{ __tr('Paid') }}</option>
                                    <option value="confirmed">{{ __tr('Confirmed') }}</option>
                                    <option value="shipped">{{ __tr('Shipped') }}</option>
                                    <option value="delivered">{{ __tr('Delivered') }}</option>
                                    <option value="cancelled">{{ __tr('Cancelled') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __tr('Order ID') }}</label>
                                <input type="text" name="order_id" class="form-control" placeholder="{{ __tr('Search by Order ID') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __tr('Customer Phone') }}</label>
                                <input type="text" name="customer_phone" class="form-control" placeholder="{{ __tr('Search by Phone') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> {{ __tr('Filter') }}
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                        <i class="fa fa-times"></i> {{ __tr('Clear') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __tr('Orders List') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="ordersTable">
                        <thead>
                            <tr>
                                <th>{{ __tr('Order ID') }}</th>
                                <th>{{ __tr('Customer') }}</th>
                                <th>{{ __tr('Items') }}</th>
                                <th>{{ __tr('Amount') }}</th>
                                <th>{{ __tr('Status') }}</th>
                                <th>{{ __tr('Date') }}</th>
                                <th>{{ __tr('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <!-- Orders will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div id="ordersPagination" class="mt-3">
                    <!-- Pagination will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Update Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __tr('Update Order Status') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="updateStatusForm">
                <div class="modal-body">
                    <input type="hidden" id="orderUidForUpdate" name="order_uid">
                    <div class="form-group">
                        <label>{{ __tr('New Status') }}</label>
                        <select name="status" class="form-control" required>
                            <option value="pending">{{ __tr('Pending') }}</option>
                            <option value="awaiting_address">{{ __tr('Awaiting Address') }}</option>
                            <option value="awaiting_payment">{{ __tr('Awaiting Payment') }}</option>
                            <option value="paid">{{ __tr('Paid') }}</option>
                            <option value="confirmed">{{ __tr('Confirmed') }}</option>
                            <option value="shipped">{{ __tr('Shipped') }}</option>
                            <option value="delivered">{{ __tr('Delivered') }}</option>
                            <option value="cancelled">{{ __tr('Cancelled') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __tr('Update Status') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('appScripts')
<script>
let currentPage = 1;
let currentFilters = {};

$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Load initial data
    loadOrders();
    loadOrderStatistics();
    
    // Filter form submission
    $('#ordersFilterForm').on('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        currentFilters = $(this).serialize();
        loadOrders();
    });
    
    // Status update form submission
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        updateOrderStatus();
    });
});

function loadOrders(page = 1) {
    currentPage = page;
    
    $.ajax({
        url: "{{ route('vendor.whatsapp.orders.data') }}",
        method: 'GET',
        data: currentFilters + '&page=' + page,
        beforeSend: function() {
            $('#ordersTableBody').html('<tr><td colspan="7" class="text-center">{{ __tr("Loading...") }}</td></tr>');
        },
        success: function(response) {
            if (response.reaction == 1) {
                renderOrdersTable(response.data.orders);
                renderPagination(response.data.orders);
            } else {
                showErrorMessage('{{ __tr("Failed to load orders") }}');
            }
        },
        error: function() {
            showErrorMessage('{{ __tr("Error loading orders") }}');
        }
    });
}

function renderOrdersTable(orders) {
    let html = '';
    
    if (orders.data && orders.data.length > 0) {
        orders.data.forEach(function(order) {
            html += `
                <tr>
                    <td><strong>${order.order_id}</strong></td>
                    <td>
                        <div>${order.customer_name || 'N/A'}</div>
                        <small class="text-muted">${order.customer_phone}</small>
                    </td>
                    <td>${order.items ? order.items.length : 0} items</td>
                    <td><strong>${order.formatted_total_amount}</strong></td>
                    <td><span class="badge badge-${getStatusColor(order.status)}">${order.status_label}</span></td>
                    <td>${formatDate(order.created_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="viewOrder('${order._uid}')">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="showUpdateStatusModal('${order._uid}', '${order.status}')">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    } else {
        html = '<tr><td colspan="7" class="text-center">{{ __tr("No orders found") }}</td></tr>';
    }
    
    $('#ordersTableBody').html(html);
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'awaiting_address': 'info',
        'awaiting_payment': 'warning',
        'paid': 'success',
        'confirmed': 'success',
        'shipped': 'primary',
        'delivered': 'success',
        'cancelled': 'danger',
        'refunded': 'secondary'
    };
    return colors[status] || 'secondary';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString() + ' ' + new Date(dateString).toLocaleTimeString();
}

function viewOrder(orderUid) {
    window.location.href = "{{ route('vendor.whatsapp.orders.details', ':orderUid') }}".replace(':orderUid', orderUid);
}

function showUpdateStatusModal(orderUid, currentStatus) {
    $('#orderUidForUpdate').val(orderUid);
    $('#updateStatusForm select[name="status"]').val(currentStatus);
    $('#updateStatusModal').modal('show');
}

function updateOrderStatus() {
    const orderUid = $('#orderUidForUpdate').val();
    const formData = $('#updateStatusForm').serialize();
    
    $.ajax({
        url: "{{ route('vendor.whatsapp.orders.update_status', ':orderUid') }}".replace(':orderUid', orderUid),
        method: 'PATCH',
        data: formData,
        beforeSend: function() {
            // Disable submit button to prevent double submission
            $('#updateStatusForm button[type="submit"]').prop('disabled', true);
        },
        success: function(response) {
            if (response.reaction == 1) {
                $('#updateStatusModal').modal('hide');
                showSuccessMessage('{{ __tr("Order status updated successfully") }}');
                // Refresh both orders list and statistics
                loadOrders(currentPage);
                loadOrderStatistics();
            } else if (response.reaction == 2 && response.data.message === 'Token Expired, Please reload and try again.') {
                showErrorMessage('{{ __tr("Session expired. The page will refresh.") }}');
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } else {
                showErrorMessage(response.data.message || '{{ __tr("Failed to update order status") }}');
            }
        },
        error: function() {
            showErrorMessage('{{ __tr("Error updating order status") }}');
        },
        complete: function() {
            // Re-enable submit button
            $('#updateStatusForm button[type="submit"]').prop('disabled', false);
        }
    });
}

function refreshOrders() {
    loadOrders(currentPage);
    loadOrderStatistics();
}

function loadOrderStatistics() {
    $.ajax({
        url: "{{ route('vendor.whatsapp.orders.statistics') }}",
        method: 'GET',
        success: function(response) {
            if (response.reaction == 1) {
                updateStatisticsCards(response.data.order_statistics);
            }
        },
        error: function() {
            console.log('Error loading order statistics');
        }
    });
}

function updateStatisticsCards(stats) {
    // Update the statistics cards with new data
    $('.card .mb-0:contains("{{ __tr('Total Orders') }}")').prev().text(stats.total_orders || 0);
    $('.card .mb-0:contains("{{ __tr('Pending Orders') }}")').prev().text(stats.pending_orders || 0);
    $('.card .mb-0:contains("{{ __tr('Completed Orders') }}")').prev().text(stats.completed_orders || 0);
    $('.card .mb-0:contains("{{ __tr('Cancelled Orders') }}")').prev().text(stats.cancelled_orders || 0);
    
    // Format and update revenue amounts
    const totalRevenue = formatCurrency(stats.total_revenue || 0);
    const pendingRevenue = formatCurrency(stats.pending_revenue || 0);
    
    $('.card .mb-0:contains("{{ __tr('Total Revenue') }}")').prev().text(totalRevenue);
    $('.card .mb-0:contains("{{ __tr('Pending Revenue') }}")').prev().text(pendingRevenue);
}

function formatCurrency(amount) {
    // Simple currency formatting - you may want to use the actual currency from settings
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function clearFilters() {
    $('#ordersFilterForm')[0].reset();
    currentFilters = {};
    currentPage = 1;
    loadOrders();
}

function exportOrders() {
    const params = new URLSearchParams(currentFilters);
    window.location.href = "{{ route('vendor.whatsapp.orders.export') }}?" + params.toString();
}

function renderPagination(orders) {
    // Simple pagination implementation
    let html = '';
    if (orders.last_page > 1) {
        html += '<nav><ul class="pagination">';
        
        // Previous button
        if (orders.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(${orders.current_page - 1})">Previous</a></li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= orders.last_page; i++) {
            if (i == orders.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(${i})">${i}</a></li>`;
            }
        }
        
        // Next button
        if (orders.current_page < orders.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(${orders.current_page + 1})">Next</a></li>`;
        }
        
        html += '</ul></nav>';
    }
    
    $('#ordersPagination').html(html);
}
</script>
@endpush
