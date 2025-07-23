@extends('layouts.app', ['title' => __tr('Order Details - :orderID', ['orderID' => $order->order_id])])

@section('content')
<div class="lw-page-content">
    <!-- Page header -->
    <div class="lw-page-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3>{{ __tr('Order Details') }} - #{{ $order->order_id }}</h3>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.console') }}">{{ __tr('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vendor.whatsapp.orders.list') }}">{{ __tr('WhatsApp Orders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __tr('Order Details') }}</li>
                    </ol>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('vendor.whatsapp.orders.list') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __tr('Back to Orders') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Order Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __tr('Order Information') }}</h5>
                        <span class="badge badge-{{ getStatusColor($order->status) }} badge-lg">
                            {{ $order->status_label }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __tr('Order ID') }}:</strong></td>
                                        <td>{{ $order->order_id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __tr('Order Date') }}:</strong></td>
                                        <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __tr('Payment Status') }}:</strong></td>
                                        <td>
                                            @if($order->payment_status)
                                                <span class="badge badge-success">{{ ucfirst($order->payment_status) }}</span>
                                            @else
                                                <span class="badge badge-warning">{{ __tr('Pending') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __tr('Currency') }}:</strong></td>
                                        <td>{{ $order->currency }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __tr('Total Amount') }}:</strong></td>
                                        <td><h5 class="text-success mb-0">{{ $order->formatted_total_amount }}</h5></td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __tr('Payment Completed') }}:</strong></td>
                                        <td>
                                            @if($order->payment_completed_at)
                                                {{ $order->payment_completed_at->format('d M Y, h:i A') }}
                                            @else
                                                {{ __tr('Not completed') }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __tr('Shipped At') }}:</strong></td>
                                        <td>
                                            @if($order->shipped_at)
                                                {{ $order->shipped_at->format('d M Y, h:i A') }}
                                            @else
                                                {{ __tr('Not shipped') }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __tr('Delivered At') }}:</strong></td>
                                        <td>
                                            @if($order->delivered_at)
                                                {{ $order->delivered_at->format('d M Y, h:i A') }}
                                            @else
                                                {{ __tr('Not delivered') }}
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __tr('Order Items') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __tr('Product') }}</th>
                                        <th>{{ __tr('Quantity') }}</th>
                                        <th>{{ __tr('Price') }}</th>
                                        <th>{{ __tr('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($order->items)
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $item['product_retailer_id'] ?? $item['name'] ?? __tr('Unknown Product') }}</strong>
                                                </div>
                                                @if(isset($item['product_description']))
                                                    <small class="text-muted">{{ $item['product_description'] }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $item['quantity'] ?? 1 }}</td>
                                            <td>{{ $order->currency }} {{ number_format($item['item_price'] ?? 0, 2) }}</td>
                                            <td>{{ $order->currency }} {{ number_format(($item['item_price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">{{ __tr('Subtotal') }}</th>
                                        <th>{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</th>
                                    </tr>
                                    @if($order->tax_amount > 0)
                                    <tr>
                                        <th colspan="3">{{ __tr('Tax') }}</th>
                                        <th>{{ $order->currency }} {{ number_format($order->tax_amount, 2) }}</th>
                                    </tr>
                                    @endif
                                    @if($order->shipping_amount > 0)
                                    <tr>
                                        <th colspan="3">{{ __tr('Shipping') }}</th>
                                        <th>{{ $order->currency }} {{ number_format($order->shipping_amount, 2) }}</th>
                                    </tr>
                                    @endif
                                    @if($order->discount_amount > 0)
                                    <tr>
                                        <th colspan="3">{{ __tr('Discount') }}</th>
                                        <th>-{{ $order->currency }} {{ number_format($order->discount_amount, 2) }}</th>
                                    </tr>
                                    @endif
                                    <tr class="table-success">
                                        <th colspan="3">{{ __tr('Total') }}</th>
                                        <th>{{ $order->currency }} {{ number_format($order->getFinalAmount(), 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                @if($payments->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __tr('Payment History') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __tr('Payment ID') }}</th>
                                        <th>{{ __tr('Amount') }}</th>
                                        <th>{{ __tr('Status') }}</th>
                                        <th>{{ __tr('Method') }}</th>
                                        <th>{{ __tr('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                    <tr>
                                        <td><code>{{ $payment->payment_id }}</code></td>
                                        <td>{{ $payment->formatted_amount }}</td>
                                        <td>
                                            <span class="badge badge-{{ $payment->is_successful ? 'success' : ($payment->is_failed ? 'danger' : 'warning') }}">
                                                {{ $payment->status_label }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->payment_method ?? __tr('N/A') }}</td>
                                        <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Customer Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __tr('Customer Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{ __tr('Name') }}:</strong></td>
                                <td>{{ $order->customer_name ?: __tr('N/A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __tr('Phone') }}:</strong></td>
                                <td>
                                    <a href="https://wa.me/{{ $order->customer_phone }}" target="_blank" class="text-success">
                                        <i class="fab fa-whatsapp"></i> {{ $order->customer_phone }}
                                    </a>
                                </td>
                            </tr>
                            @if($order->contact)
                            <tr>
                                <td><strong>{{ __tr('Contact') }}:</strong></td>
                                <td>
                                    <a href="{{ route('vendor.chat_message.contact.view', $order->contact->_uid) }}" class="btn btn-sm btn-outline-primary">
                                        {{ __tr('Chat with Customer') }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Delivery Address -->
                @if($order->delivery_address)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __tr('Delivery Address') }}</h5>
                    </div>
                    <div class="card-body">
                        <address>
                            {!! nl2br(e($order->delivery_address)) !!}
                        </address>
                    </div>
                </div>
                @endif

                <!-- Order Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __tr('Order Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-warning btn-block" onclick="showUpdateStatusModal()">
                                <i class="fa fa-edit"></i> {{ __tr('Update Status') }}
                            </button>
                            
                            <button type="button" class="btn btn-info btn-block" onclick="downloadOrderPDF()">
                                <i class="fa fa-download"></i> {{ __tr('Download PDF') }}
                            </button>

                            @if($order->canBeCancelled())
                            <button type="button" class="btn btn-danger btn-block" onclick="cancelOrder()">
                                <i class="fa fa-times"></i> {{ __tr('Cancel Order') }}
                            </button>
                            @endif
                            
                            <a href="https://wa.me/{{ $order->customer_phone }}?text={{ urlencode('Hi! Regarding your order #' . $order->order_id) }}" 
                               target="_blank" class="btn btn-success btn-block">
                                <i class="fab fa-whatsapp"></i> {{ __tr('Message Customer') }}
                            </a>
                        </div>
                    </div>
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
                    <div class="form-group">
                        <label>{{ __tr('Current Status') }}</label>
                        <input type="text" class="form-control" value="{{ $order->status_label }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>{{ __tr('New Status') }}</label>
                        <select name="status" class="form-control" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>{{ __tr('Pending') }}</option>
                            <option value="awaiting_address" {{ $order->status == 'awaiting_address' ? 'selected' : '' }}>{{ __tr('Awaiting Address') }}</option>
                            <option value="awaiting_payment" {{ $order->status == 'awaiting_payment' ? 'selected' : '' }}>{{ __tr('Awaiting Payment') }}</option>
                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>{{ __tr('Paid') }}</option>
                            <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>{{ __tr('Confirmed') }}</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>{{ __tr('Shipped') }}</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>{{ __tr('Delivered') }}</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>{{ __tr('Cancelled') }}</option>
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
function downloadOrderPDF() {
    // Show loading indicator
    showLoadingMessage('{{ __tr("Generating PDF...") }}');
    
    // Make the AJAX request to generate PDF
    $.ajax({
        url: "{{ route('vendor.whatsapp.orders.pdf', ['uid' => $order->_uid]) }}",
        method: 'GET',
        xhrFields: {
            responseType: 'blob' // Important for handling PDF response
        },
        success: function(response) {
            // Create a blob from the response
            const blob = new Blob([response], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);
            
            // Create a temporary link and trigger download
            const link = document.createElement('a');
            link.href = url;
            link.download = 'order-{{ $order->order_id }}.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            hideLoadingMessage();
        },
        error: function(xhr, status, error) {
            hideLoadingMessage();
            console.error('PDF Generation Error:', status, error);
            let errorMessage = '{{ __tr("Error generating PDF") }}';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showErrorMessage(errorMessage);
        }
    });
}

// Loading message handling functions
function showLoadingMessage(message) {
    // Create loading overlay if it doesn't exist
    if (!$('#loadingOverlay').length) {
        $('body').append(`
            <div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
                background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
                <div class="card p-3" style="max-width:300px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-2"></div>
                        <div id="loadingMessage" class="text-muted"></div>
                    </div>
                </div>
            </div>
        `);
    }
    $('#loadingMessage').text(message);
    $('#loadingOverlay').css('display', 'flex');
}

function hideLoadingMessage() {
    $('#loadingOverlay').hide();
}

function showErrorMessage(message) {
    // You can customize this to match your UI's error display
    alert(message);
}

function showSuccessMessage(message) {
    // You can customize this to match your UI's success display
    alert(message);
}

$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Status update form submission
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        updateOrderStatus();
    });
});

function showUpdateStatusModal() {
    $('#updateStatusModal').modal('show');
}

function updateOrderStatus() {
    const formData = $('#updateStatusForm').serialize();
    
    $.ajax({
        url: "{{ route('vendor.whatsapp.orders.update_status', $order->_uid) }}",
        method: 'PATCH',
        data: formData,
        success: function(response) {
            if (response.reaction == 1) {
                $('#updateStatusModal').modal('hide');
                showSuccessMessage('{{ __tr("Order status updated successfully") }}');
                setTimeout(function() {
                    location.reload();
                }, 1500);
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
        }
    });
}

function cancelOrder() {
    if (confirm('{{ __tr("Are you sure you want to cancel this order?") }}')) {
        $.ajax({
            url: "{{ route('vendor.whatsapp.orders.update_status', $order->_uid) }}",
            method: 'PATCH',
            data: { status: 'cancelled' },
            success: function(response) {
                if (response.reaction == 1) {
                    showSuccessMessage('{{ __tr("Order cancelled successfully") }}');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else if (response.reaction == 2 && response.data.message === 'Token Expired, Please reload and try again.') {
                    showErrorMessage('{{ __tr("Session expired. The page will refresh.") }}');
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    showErrorMessage(response.data.message || '{{ __tr("Failed to cancel order") }}');
                }
            },
            error: function() {
                showErrorMessage('{{ __tr("Error cancelling order") }}');
            }
        });
    }
}
</script>
@endpush

@php
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'awaiting_address' => 'info',
        'awaiting_payment' => 'warning',
        'paid' => 'success',
        'confirmed' => 'success',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'refunded' => 'secondary'
    ];
    return $colors[$status] ?? 'secondary';
}
@endphp
