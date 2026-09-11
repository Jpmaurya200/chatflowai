@extends('layouts.app', ['title' => __tr('Order Details - :orderID', ['orderID' => $order->order_id])])
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endpush
@section('content')
<div class="lw-page-content py-4">
    <!-- Page content -->
    <div class="container-fluid">
        <!-- Modern SaaS Order Header -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-left: 5px solid #10b981 !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1 small">
                                <li class="breadcrumb-item"><a href="{{ route('vendor.console') }}" class="text-decoration-none text-muted">{{ __tr('Dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('vendor.whatsapp.orders.list') }}" class="text-decoration-none text-muted">{{ __tr('WhatsApp Orders') }}</a></li>
                                <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">#{{ $order->order_id }}</li>
                            </ol>
                        </nav>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h2 class="h3 fw-bold mb-0" style="color: #0f172a; letter-spacing: -0.02em;">
                                {{ __tr('Order') }} #{{ $order->order_id }}
                            </h2>
                            <span class="badge badge-{{ getStatusColor($order->status) }} px-3 py-1.5 rounded-pill fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">
                                {{ $order->status_label }}
                            </span>
                            @if($order->payment_status === 'paid')
                                <span class="badge px-2.5 py-1 rounded-pill small" style="background-color: #ecfdf5; color: #059669;">
                                    <i class="fas fa-check-circle me-1"></i> {{ __tr('Paid') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-muted mb-0 small mt-1">
                            <i class="far fa-calendar-alt me-1"></i> {{ __tr('Placed on') }} {{ $order->created_at->format('d M Y, h:i A') }}
                            @if($order->customer_phone)
                                <span class="mx-2">•</span>
                                <i class="fab fa-whatsapp me-1 text-success"></i> +{{ $order->customer_phone }}
                            @endif
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" onclick="showUpdateStatusModal()" class="btn btn-outline-primary rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm">
                            <i class="fas fa-pen-to-square me-1.5"></i> {{ __tr('Update Status') }}
                        </button>
                        <a href="{{ route('vendor.whatsapp.orders.pdf', $order->_uid) }}"
                           target="_blank"
                           onclick="downloadPDF()"
                           class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm">
                            <i class="fa fa-file-pdf me-1.5 text-danger"></i> {{ __tr('Download Invoice') }}
                        </a>
                        @if($order->contact)
                        <a href="{{ route('vendor.chat_message.contact.view', $order->contact->_uid) }}"
                           class="btn rounded-pill px-3 py-2 btn-sm fw-semibold text-white shadow-sm"
                           style="background-color: #10b981; border: none;">
                            <i class="fab fa-whatsapp me-1.5"></i> {{ __tr('Chat with Customer') }}
                        </a>
                        @endif
                        <a href="{{ route('vendor.whatsapp.orders.list') }}"
                           class="btn btn-light border rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm">
                            <i class="fa fa-arrow-left me-1.5 text-muted"></i> {{ __tr('Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
                                        <td><h5 class="text-success mb-0">{{ $order->formatted_final_amount }}</h5></td>
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

                <!-- Order Items - Modern Table Container -->
                <div class="modern-table-container mb-4">
                    <!-- Table Header with Controls -->
                    <div class="table-header-controls">
                        <div class="table-title-section">
                            <h2 class="table-title">{{ __tr('Order Items') }}</h2>
                        </div>
                    </div>

                    <!-- Modern DataTable -->
                    <div class="table-responsive">
                        <table class="table modern-datatable" id="orderItemsTable">
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
                                    @foreach($order->getItemsWithProductNames() as $item)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $item['display_name'] ?? __tr('Product Item') }}</strong>
                                            </div>
                                            @if(isset($item['product_description']))
                                                <small class="text-muted">{{ $item['product_description'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="quantity-badge">
                                                <span class="quantity-text">{{ $item['quantity'] ?? 1 }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="price-text">{{ $order->currency }} {{ number_format($item['item_price'] ?? 0, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="total-text">{{ $order->currency }} {{ number_format(($item['item_price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot class="modern-table-footer">
                                <tr>
                                    <th colspan="3">{{ __tr('Subtotal') }}</th>
                                    <th>{{ $order->currency }} {{ number_format($order->getSubtotal(), 2) }}</th>
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
                                    <th class="discount-amount">-{{ $order->currency }} {{ number_format($order->discount_amount, 2) }}</th>
                                </tr>
                                @endif
                                <tr class="total-row">
                                    <th colspan="3">{{ __tr('Total') }}</th>
                                    <th class="final-total">{{ $order->currency }} {{ number_format($order->getFinalAmount(), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Table Footer with Info -->
                    <div class="table-footer">
                        <div class="table-info">
                            <span id="items-info-text">{{ __tr('Showing') }} {{ count($order->items ?? []) }} {{ __tr('items') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment History - Modern Table Container -->
                @if($payments->count() > 0)
                <div class="modern-table-container mb-4">
                    <!-- Table Header with Controls -->
                    <div class="table-header-controls">
                        <div class="table-title-section">
                            <h2 class="table-title">{{ __tr('Payment History') }}</h2>
                        </div>
                    </div>

                    <!-- Modern DataTable -->
                    <div class="table-responsive">
                        <table class="table modern-datatable" id="paymentHistoryTable">
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
                                    <td>
                                        <code class="payment-id">{{ $payment->payment_id }}</code>
                                    </td>
                                    <td>
                                        <span class="amount-text">{{ $payment->formatted_amount }}</span>
                                    </td>
                                    <td>
                                        @if($payment->is_successful)
                                            <div class="status-badge status-approved">
                                                <i class="fa fa-check-circle status-icon"></i>
                                                <span class="status-text">{{ $payment->status_label }}</span>
                                            </div>
                                        @elseif($payment->is_failed)
                                            <div class="status-badge status-rejected">
                                                <i class="fa fa-times-circle status-icon"></i>
                                                <span class="status-text">{{ $payment->status_label }}</span>
                                            </div>
                                        @else
                                            <div class="status-badge status-pending">
                                                <i class="fa fa-clock status-icon"></i>
                                                <span class="status-text">{{ $payment->status_label }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="method-text">{{ $payment->payment_method ?? __tr('N/A') }}</span>
                                    </td>
                                    <td>
                                        <span class="date-text">{{ $payment->created_at->format('d M Y, h:i A') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer with Info -->
                    <div class="table-footer">
                        <div class="table-info">
                            <span id="payments-info-text">{{ __tr('Showing') }} {{ $payments->count() }} {{ __tr('payments') }}</span>
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

                <!-- Order Quick Actions -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-bolt text-warning me-2"></i>{{ __tr('Quick Actions') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <button type="button"
                                class="btn btn-primary rounded-pill py-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2"
                                onclick="showUpdateStatusModal()"
                                style="background-color: #0f172a; border-color: #0f172a;">
                                <i class="fa fa-pen-to-square"></i> {{ __tr('Update Order Status') }}
                            </button>

                            @if($order->canBeCancelled())
                            <button type="button" 
                                class="btn btn-outline-danger rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2"
                                onclick="cancelOrder()">
                                <i class="fa fa-times"></i> {{ __tr('Cancel This Order') }}
                            </button>
                            @endif

                            <a href="https://wa.me/{{ $order->customer_phone }}?text={{ urlencode('Hi! Regarding your order #' . $order->order_id) }}"
                                target="_blank"
                                class="btn btn-outline-success rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="fab fa-whatsapp text-success"></i> {{ __tr('Direct WhatsApp Customer') }}
                            </a>

                            <a href="{{ route('vendor.whatsapp.orders.pdf', $order->_uid) }}"
                                target="_blank"
                                onclick="downloadPDF()"
                                class="btn btn-light border rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="fa fa-file-pdf text-danger"></i> {{ __tr('Download Invoice PDF') }}
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
<style>
    /* Modern Table Container Styles */
    .modern-table-container {
        background: #ffffff !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        overflow: hidden !important;
        margin-bottom: 2rem !important;
        border: 1px solid #e9ecef !important;
    }

    /* Table Header Controls */
    .table-header-controls {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 1.5rem 2rem !important;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .table-title-section {
        display: flex !important;
        align-items: center !important;
    }

    .table-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #495057 !important;
        margin: 0 !important;
    }

    .table-controls {
        display: flex !important;
        align-items: center !important;
        gap: 1.5rem !important;
    }

    .search-control {
        position: relative !important;
    }

    .search-input {
        padding: 0.5rem 1rem 0.5rem 2.5rem !important;
        border: 1px solid #ced4da !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
        min-width: 200px !important;
        transition: all 0.3s ease !important;
    }

    .search-input:focus {
        outline: none !important;
        border-color: #007bff !important;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1) !important;
    }

    .search-icon {
        position: absolute !important;
        left: 0.75rem !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #6c757d !important;
        font-size: 0.875rem !important;
    }

    /* Modern DataTable Styles */
    .modern-datatable {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .modern-datatable thead th {
        background: #f8f9fa !important;
        border: none !important;
        padding: 1rem 1.5rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        position: relative !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
    }

    .modern-datatable thead th:hover {
        background: #e9ecef !important;
    }

    .modern-datatable tbody tr {
        border-bottom: 1px solid #f1f3f4 !important;
        transition: all 0.3s ease !important;
    }

    .modern-datatable tbody tr:hover {
        background: #f8f9fa !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
    }

    .modern-datatable tbody td {
        padding: 1rem 1.5rem !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
        vertical-align: middle !important;
    }

    /* Modern Table Footer */
    .modern-table-footer th {
        background: #f8f9fa !important;
        border-top: 2px solid #dee2e6 !important;
        padding: 1rem 1.5rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
    }

    .total-row th {
        background: linear-gradient(135deg, #d4edda, #c3e6cb) !important;
        color: #155724 !important;
        font-size: 1rem !important;
        font-weight: 700 !important;
    }

    .final-total {
        color: #155724 !important;
        font-size: 1.1rem !important;
    }

    .discount-amount {
        color: #dc3545 !important;
        font-weight: 600 !important;
    }

    /* Status Badge Styles */
    .status-badge {
        display: inline-flex !important;
        align-items: center !important;
        padding: 0.5rem 1rem !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.5px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
        transition: all 0.3s ease !important;
        text-transform: uppercase !important;
    }

    .status-badge:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }

    .status-icon {
        margin-right: 0.5rem !important;
        font-size: 0.875rem !important;
    }

    .status-approved {
        background: linear-gradient(135deg, #d4edda, #c3e6cb) !important;
        color: #155724 !important;
        border-left: 3px solid #28a745 !important;
    }

    .status-rejected {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb) !important;
        color: #721c24 !important;
        border-left: 3px solid #dc3545 !important;
    }

    .status-pending {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7) !important;
        color: #856404 !important;
        border-left: 3px solid #ffc107 !important;
    }

    /* Special Styling for Order Items */
    .quantity-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: linear-gradient(135deg, #e9ecef, #dee2e6) !important;
        color: #495057 !important;
        padding: 0.375rem 0.75rem !important;
        border-radius: 20px !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        min-width: 40px !important;
    }

    .price-text,
    .total-text,
    .amount-text {
        font-weight: 600 !important;
        color: #155724 !important;
        font-size: 0.9rem !important;
    }

    .payment-id {
        background: #f8f9fa !important;
        color: #495057 !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 4px !important;
        font-size: 0.8rem !important;
        border: 1px solid #dee2e6 !important;
    }

    .method-text,
    .date-text {
        color: #6c757d !important;
        font-size: 0.875rem !important;
    }

    /* Table Footer */
    .table-footer {
        padding: 1rem 2rem !important;
        background: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
    }

    .table-info {
        font-size: 0.875rem !important;
        color: #6c757d !important;
    }

    /* Modern Button Styles */
    .btn-modern {
        transition: all 0.3s ease !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        font-weight: 600 !important;
        letter-spacing: 0.025em !important;
        padding: 0.5rem 1rem !important;
        margin: 0 0.2rem !important;
    }

    .btn-modern:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08) !important;
    }

    .btn-modern-secondary {
        background: linear-gradient(135deg, #6edffb, #4aa3f7) !important;
        border: none !important;
        color: white !important;
    }

    .btn-rounded {
        border-radius: 12px !important;
    }

    /* Page Title Styling */
    .page-title {
        font-size: 1.75rem !important;
        font-weight: 600 !important;
        color: #333 !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1.2 !important;
        animation: fadeInLeft 0.5s ease-out !important;
    }

    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* PDF Download Button Styles */
    .btn-pdf-download {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        border: none !important;
        color: white !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .btn-pdf-download:hover {
        background: linear-gradient(135deg, #c82333, #bd2130) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3) !important;
        color: white !important;
    }

    .btn-pdf-download:active {
        transform: translateY(0) !important;
    }

    .btn-pdf-download .fa-spinner {
        animation: spin 1s linear infinite !important;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .table-header-controls {
            flex-direction: column !important;
            gap: 1rem !important;
            align-items: stretch !important;
        }

        .table-controls {
            justify-content: center !important;
        }
    }

    @media (max-width: 768px) {
        .table-header-controls {
            padding: 1rem !important;
        }

        .search-control {
            width: 100% !important;
        }

        .search-input {
            width: 100% !important;
            min-width: auto !important;
        }

        .modern-datatable thead th,
        .modern-datatable tbody td,
        .modern-table-footer th {
            padding: 0.75rem 1rem !important;
        }

        .page-title {
            font-size: 1.5rem !important;
        }

        .d-flex.justify-content-between {
            flex-direction: column !important;
            gap: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .table-title {
            font-size: 1.125rem !important;
        }

        .modern-datatable thead th,
        .modern-datatable tbody td,
        .modern-table-footer th {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.8rem !important;
        }

        .status-badge {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.7rem !important;
        }

        .btn-modern {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
    }
</style>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize modern table features
        initializeModernTableFeatures();

        // Initialize animations for buttons
        const buttons = document.querySelectorAll('.btn-modern');
        buttons.forEach((button, index) => {
            setTimeout(() => {
                button.classList.add('animate__animated', 'animate__fadeIn');
                button.style.opacity = '1';
            }, index * 150);
        });

        // Add hover effects to modern buttons
        $(document).on('mouseenter', '.btn-modern', function() {
            $(this).addClass('animate__animated animate__pulse');
        }).on('mouseleave', '.btn-modern', function() {
            $(this).removeClass('animate__animated animate__pulse');
        });
    });

    // Initialize modern table features
    function initializeModernTableFeatures() {
        // Search functionality for Order Items
        $('#items-search').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterTableRows('#orderItemsTable', searchTerm);
            updateItemsInfo();
        });

        // Search functionality for Payment History
        $('#payments-search').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterTableRows('#paymentHistoryTable', searchTerm);
            updatePaymentsInfo();
        });

        // Add modern styling to tables
        addModernTableStyling();

        // Animate table rows
        animateTableRows();

        // Status badge animations
        animateStatusBadges();
    }

    // Filter table rows based on search term
    function filterTableRows(tableSelector, searchTerm) {
        $(tableSelector + ' tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            if (rowText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Update items info
    function updateItemsInfo() {
        const visibleRows = $('#orderItemsTable tbody tr:visible').length;
        const totalRows = $('#orderItemsTable tbody tr').length;

        let infoText = `{{ __tr('Showing') }} ${visibleRows} {{ __tr('of') }} ${totalRows} {{ __tr('items') }}`;
        $('#items-info-text').text(infoText);
    }

    // Update payments info
    function updatePaymentsInfo() {
        const visibleRows = $('#paymentHistoryTable tbody tr:visible').length;
        const totalRows = $('#paymentHistoryTable tbody tr').length;

        let infoText = `{{ __tr('Showing') }} ${visibleRows} {{ __tr('of') }} ${totalRows} {{ __tr('payments') }}`;
        $('#payments-info-text').text(infoText);
    }

    // Animate table rows
    function animateTableRows() {
        const rows = document.querySelectorAll('.modern-datatable tbody tr');
        rows.forEach((row, index) => {
            setTimeout(() => {
                row.classList.add('animate__animated', 'animate__fadeInUp');
                row.style.opacity = '1';
            }, index * 50);
        });
    }

    // Animate status badges
    function animateStatusBadges() {
        const statusBadges = document.querySelectorAll('.status-badge');
        statusBadges.forEach((badge, index) => {
            setTimeout(() => {
                badge.classList.add('animate__fadeIn');

                badge.addEventListener('mouseenter', function() {
                    this.classList.add('animate__pulse');
                });

                badge.addEventListener('mouseleave', function() {
                    this.classList.remove('animate__pulse');
                });

                if (badge.classList.contains('status-approved')) {
                    const icon = badge.querySelector('.status-icon');
                    if (icon) {
                        icon.classList.add('animate__animated', 'animate__bounceIn');
                        setTimeout(() => {
                            icon.classList.remove('animate__bounceIn');
                        }, 1000);
                    }
                }
            }, index * 150);
        });
    }

    // Add modern table styling function
    function addModernTableStyling() {
        // Add hover effects to table rows
        $('.modern-datatable tbody tr').hover(
            function() {
                $(this).addClass('table-row-hover');
            },
            function() {
                $(this).removeClass('table-row-hover');
            }
        );

        // Add click effects to table headers
        $('.modern-datatable thead th').click(function() {
            $('.modern-datatable thead th').removeClass('sort-active');
            $(this).addClass('sort-active');
        });
    }

    // Add modern table row hover class
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .table-row-hover {
                background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            }
            .sort-active {
                background: linear-gradient(135deg, #007bff, #0056b3) !important;
                color: white !important;
            }
        `)
        .appendTo('head');
</script>
@endsection

@push('appScripts')
<script>
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

function downloadPDF() {
    // Show loading state
    const pdfButtons = document.querySelectorAll('[href*="pdf"]');
    pdfButtons.forEach(button => {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>{{ __tr("Generating PDF...") }}';
        button.style.pointerEvents = 'none';
        
        // Reset after 3 seconds (PDF should be generated by then)
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.pointerEvents = 'auto';
        }, 3000);
    });
    
    // Show success message
    showSuccessMessage('{{ __tr("PDF download started") }}');
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
