<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order #{{ $order->order_id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.4;
            color: #333;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 9px;
            margin: 0;
        }
        .section {
            margin-bottom: 15px;
        }
        .section h2 {
            font-size: 12px;
            margin: 0 0 8px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }
        .table th {
            background-color: #f5f5f5;
            font-size: 9px;
            font-weight: bold;
        }
        .total {
            font-weight: bold;
            background-color: #e8f5e9;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background-color: #c8e6c9; color: #2e7d32; }
        .badge-warning { background-color: #fff3e0; color: #ef6c00; }
        .badge-danger { background-color: #ffebee; color: #c62828; }
        .address {
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 9px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Details - #{{ $order->order_id }}</h1>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <!-- Order Information -->
    <div class="section">
        <h2>Order Information</h2>
        <table class="table">
            <tr>
                <th width="25%">Order ID:</th>
                <td width="25%">{{ $order->order_id }}</td>
                <th width="25%">Order Date:</th>
                <td width="25%">{{ $order->created_at->format('d M Y, h:i A') }}</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>{{ $order->status_label }}</td>
                <th>Payment Status:</th>
                <td>{{ $order->payment_status ? ucfirst($order->payment_status) : 'Pending' }}</td>
            </tr>
        </table>
    </div>

    <!-- Customer Information -->
    <div class="section">
        <h2>Customer Information</h2>
        <table class="table">
            <tr>
                <th width="25%">Name:</th>
                <td width="25%">{{ $order->customer_name ?: 'N/A' }}</td>
                <th width="25%">Phone:</th>
                <td width="25%">{{ $order->customer_phone }}</td>
            </tr>
            @if($order->delivery_address)
            <tr>
                <th>Delivery Address:</th>
                <td colspan="3">{{ $order->delivery_address }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Order Items -->
    <div class="section">
        <h2>Order Items</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->getItemsWithProductNames() as $item)
                <tr>
                    <td>
                        <strong>{{ $item['display_name'] ?? 'Product Item' }}</strong>
                        @if(isset($item['product_description']))
                        <br><small>{{ $item['product_description'] }}</small>
                        @endif
                    </td>
                    <td>{{ $item['quantity'] ?? 1 }}</td>
                    <td>{{ $order->currency }} {{ number_format($item['item_price'] ?? 0, 2) }}</td>
                    <td>{{ $order->currency }} {{ number_format(($item['item_price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Subtotal</th>
                    <td>{{ $order->currency }} {{ number_format($order->getSubtotal(), 2) }}</td>
                </tr>
                @if($order->tax_amount > 0)
                <tr>
                    <th colspan="3">Tax</th>
                    <td>{{ $order->currency }} {{ number_format($order->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($order->shipping_amount > 0)
                <tr>
                    <th colspan="3">Shipping</th>
                    <td>{{ $order->currency }} {{ number_format($order->shipping_amount, 2) }}</td>
                </tr>
                @endif
                @if($order->discount_amount > 0)
                <tr>
                    <th colspan="3">Discount</th>
                    <td>-{{ $order->currency }} {{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total">
                    <th colspan="3">Total</th>
                    <td>{{ $order->currency }} {{ number_format($order->getFinalAmount(), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($payments && $payments->count() > 0)
    <!-- Payment History -->
    <div class="section">
        <h2>Payment History</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_id }}</td>
                    <td>{{ $payment->formatted_amount }}</td>
                    <td>{{ $payment->status_label }}</td>
                    <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
