@extends('layouts.app')

@section('page-title', $pageTitle)

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white text-center">
                    <h3><i class="fas fa-check-circle"></i> {{ __tr('Payment Successful!') }}</h3>
                </div>
                <div class="card-body text-center">
                    @if($order)
                        <div class="alert alert-success">
                            <h4>{{ __tr('Thank you for your payment!') }}</h4>
                            <p>{{ __tr('Your order has been confirmed and will be processed shortly.') }}</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5>{{ __tr('Order Details') }}</h5>
                                    </div>
                                    <div class="card-body text-left">
                                        <p><strong>{{ __tr('Order ID') }}:</strong> {{ $order->order_id }}</p>
                                        <p><strong>{{ __tr('Status') }}:</strong> 
                                            <span class="badge badge-success">{{ ucfirst($order->status) }}</span>
                                        </p>
                                        <p><strong>{{ __tr('Total Amount') }}:</strong> {{ $order->currency }} {{ number_format($order->total_amount, 2) }}</p>
                                        <p><strong>{{ __tr('Customer') }}:</strong> {{ $order->customer_name ?: $order->customer_phone }}</p>
                                        @if($order->delivery_address)
                                            <p><strong>{{ __tr('Delivery Address') }}:</strong><br>
                                                {{ $order->delivery_address }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($payment)
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5>{{ __tr('Payment Details') }}</h5>
                                    </div>
                                    <div class="card-body text-left">
                                        <p><strong>{{ __tr('Payment ID') }}:</strong> {{ $payment->payment_id }}</p>
                                        <p><strong>{{ __tr('Amount') }}:</strong> {{ $payment->currency }} {{ number_format($payment->amount, 2) }}</p>
                                        <p><strong>{{ __tr('Status') }}:</strong> 
                                            <span class="badge badge-success">{{ ucfirst($payment->status) }}</span>
                                        </p>
                                        <p><strong>{{ __tr('Gateway') }}:</strong> {{ $payment->gateway_label }}</p>
                                        @if($payment->payment_completed_at)
                                            <p><strong>{{ __tr('Completed At') }}:</strong> {{ $payment->payment_completed_at->format('M d, Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        @if($order->items)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>{{ __tr('Order Items') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __tr('Item') }}</th>
                                                <th>{{ __tr('Quantity') }}</th>
                                                <th>{{ __tr('Price') }}</th>
                                                <th>{{ __tr('Total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->getItemsWithProductNames() as $item)
                                            <tr>
                                                <td>{{ $item['display_name'] ?? __tr('Product Item') }}</td>
                                                <td>{{ $item['quantity'] ?? 1 }}</td>
                                                <td>{{ $order->currency }} {{ number_format($item['item_price'] ?? 0, 2) }}</td>
                                                <td>{{ $order->currency }} {{ number_format(($item['item_price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="alert alert-info">
                            <h6>{{ __tr('What happens next?') }}</h6>
                            <ul class="text-left mb-0">
                                <li>{{ __tr('You will receive a confirmation message on WhatsApp') }}</li>
                                <li>{{ __tr('Your order will be processed within 24 hours') }}</li>
                                <li>{{ __tr('You will receive tracking details once shipped') }}</li>
                                <li>{{ __tr('Expected delivery: 2-3 business days') }}</li>
                            </ul>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h4>{{ __tr('Payment Successful!') }}</h4>
                            <p>{{ __tr('Your payment was successful, but we could not find the order details.') }}</p>
                            <p>{{ __tr('Please contact support if you have any questions.') }}</p>
                            
                            @if($orderId)
                                <p><strong>{{ __tr('Reference ID') }}:</strong><br>{{ $orderId }}</p>
                            @endif
                            
                            @if($paymentId)
                                <p><strong>{{ __tr('Payment ID') }}:</strong> {{ $paymentId }}</p>
                            @endif
                        </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('vendor.whatsapp.orders.list') }}" class="btn btn-success">
                            <i class="fas fa-arrow-left"></i> {{ __tr('Back to Orders') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.badge-success {
    background-color: #28a745;
}

.alert-success {
    border-color: #28a745;
    background-color: #d4edda;
    color: #155724;
}

.alert-info {
    border-color: #17a2b8;
    background-color: #d1ecf1;
    color: #0c5460;
}

.alert-warning {
    border-color: #ffc107;
    background-color: #fff3cd;
    color: #856404;
}
</style>
@endpush
