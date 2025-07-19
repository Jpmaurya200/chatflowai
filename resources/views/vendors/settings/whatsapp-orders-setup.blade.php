@extends('layouts.app', ['title' => __tr('WhatsApp Orders Setup')])

@section('content')
<div class="lw-page-content">
    <!-- Page header -->
    <div class="lw-page-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3>{{ __tr('WhatsApp Orders Setup') }}</h3>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.console') }}">{{ __tr('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vendor.settings.read', ['pageType' => 'general']) }}">{{ __tr('Settings') }}</a></li>
                        <li class="breadcrumb-item active">{{ __tr('WhatsApp Orders Setup') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <form class="lw-ajax-form lw-form" method="post" action="{{ route('vendor.settings.write.update') }}">
                    <input type="hidden" name="pageType" value="whatsapp_orders_setup">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fa fa-shopping-cart"></i> {{ __tr('Order Processing Settings') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Enable WhatsApp Orders -->
                            <div class="form-group">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="enable_whatsapp_orders" value="on" {{ $configurationData['enable_whatsapp_orders'] ? 'checked' : '' }}>
                                    <span class="custom-control-label">{{ __tr('Enable WhatsApp Catalog Orders') }}</span>
                                </label>
                                <small class="form-text text-muted">{{ __tr('Allow customers to place orders directly from WhatsApp catalog') }}</small>
                            </div>

                            <div id="orderSettings" style="{{ $configurationData['enable_whatsapp_orders'] ? '' : 'display: none;' }}">
                                <!-- Currency -->
                                <div class="form-group">
                                    <label for="order_currency">{{ __tr('Order Currency') }}</label>
                                    <select name="order_currency" id="order_currency" class="form-control">
                                        <option value="INR" {{ $configurationData['order_currency'] == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                        <option value="USD" {{ $configurationData['order_currency'] == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                        <option value="EUR" {{ $configurationData['order_currency'] == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                        <option value="GBP" {{ $configurationData['order_currency'] == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                        <option value="AUD" {{ $configurationData['order_currency'] == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                        <option value="CAD" {{ $configurationData['order_currency'] == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    </select>
                                    <small class="form-text text-muted">{{ __tr('Currency for order amounts') }}</small>
                                </div>

                                <!-- Tax Rate -->
                                <div class="form-group">
                                    <label for="order_tax_rate">{{ __tr('Tax Rate (%)') }}</label>
                                    <input type="number" name="order_tax_rate" id="order_tax_rate" class="form-control" 
                                           value="{{ $configurationData['order_tax_rate'] }}" min="0" max="100" step="0.01">
                                    <small class="form-text text-muted">{{ __tr('Tax percentage to be added to orders (e.g., 18 for 18% GST)') }}</small>
                                </div>

                                <!-- Shipping Amount -->
                                <div class="form-group">
                                    <label for="order_shipping_amount">{{ __tr('Shipping Amount') }}</label>
                                    <input type="number" name="order_shipping_amount" id="order_shipping_amount" class="form-control" 
                                           value="{{ $configurationData['order_shipping_amount'] }}" min="0" step="0.01">
                                    <small class="form-text text-muted">{{ __tr('Fixed shipping amount to be added to orders') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fa fa-credit-card"></i> {{ __tr('Payment Gateway Settings') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Enable Payment Gateway -->
                            <div class="form-group">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="payment_gateway_enabled" value="on" {{ $configurationData['payment_gateway_enabled'] ? 'checked' : '' }}>
                                    <span class="custom-control-label">{{ __tr('Enable Payment Gateway') }}</span>
                                </label>
                                <small class="form-text text-muted">{{ __tr('Enable online payment processing for orders') }}</small>
                            </div>

                            <div id="paymentSettings" style="{{ $configurationData['payment_gateway_enabled'] ? '' : 'display: none;' }}">
                                <!-- Payment Gateway -->
                                <div class="form-group">
                                    <label for="payment_gateway">{{ __tr('Payment Gateway') }}</label>
                                    <select name="payment_gateway" id="payment_gateway" class="form-control">
                                        <option value="razorpay" {{ $configurationData['payment_gateway'] == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                                    </select>
                                    <small class="form-text text-muted">{{ __tr('Select your payment gateway provider') }}</small>
                                </div>

                                <!-- Razorpay Settings -->
                                <div id="razorpaySettings" style="{{ $configurationData['payment_gateway'] == 'razorpay' ? '' : 'display: none;' }}">
                                    <div class="alert alert-info">
                                        <h6><i class="fa fa-info-circle"></i> {{ __tr('Razorpay Configuration') }}</h6>
                                        <p class="mb-2">{{ __tr('To configure Razorpay:') }}</p>
                                        <ol class="mb-0">
                                            <li>{{ __tr('Sign up at') }} <a href="https://razorpay.com" target="_blank">razorpay.com</a></li>
                                            <li>{{ __tr('Go to Settings > API Keys in your Razorpay dashboard') }}</li>
                                            <li>{{ __tr('Generate API keys and copy them below') }}</li>
                                            <li>{{ __tr('Set up webhook URL:') }} <code>{{ route('whatsapp.payment.webhook.razorpay') }}</code></li>
                                        </ol>
                                    </div>

                                    <div class="form-group">
                                        <label for="razorpay_key_id">{{ __tr('Razorpay Key ID') }}</label>
                                        <input type="text" name="razorpay_key_id" id="razorpay_key_id" class="form-control" 
                                               value="{{ $configurationData['razorpay_key_id'] }}" placeholder="rzp_test_xxxxxxxxxx">
                                        <small class="form-text text-muted">{{ __tr('Your Razorpay Key ID (starts with rzp_test_ or rzp_live_)') }}</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="razorpay_key_secret">{{ __tr('Razorpay Key Secret') }}</label>
                                        <input type="password" name="razorpay_key_secret" id="razorpay_key_secret" class="form-control" 
                                               value="{{ $configurationData['razorpay_key_secret'] }}" placeholder="Enter your key secret">
                                        <small class="form-text text-muted">{{ __tr('Your Razorpay Key Secret (keep this secure)') }}</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="razorpay_webhook_secret">{{ __tr('Razorpay Webhook Secret') }}</label>
                                        <input type="password" name="razorpay_webhook_secret" id="razorpay_webhook_secret" class="form-control" 
                                               value="{{ $configurationData['razorpay_webhook_secret'] }}" placeholder="Enter webhook secret">
                                        <small class="form-text text-muted">{{ __tr('Webhook secret for verifying payment notifications (optional but recommended)') }}</small>
                                    </div>

                                    <div class="alert alert-warning">
                                        <h6><i class="fa fa-exclamation-triangle"></i> {{ __tr('Webhook Configuration') }}</h6>
                                        <p class="mb-2">{{ __tr('Configure the following webhook URL in your Razorpay dashboard:') }}</p>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ route('whatsapp.payment.webhook.razorpay') }}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ route('whatsapp.payment.webhook.razorpay') }}')">
                                                    <i class="fa fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">{{ __tr('Enable these events: payment_link.paid, payment.failed') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fa fa-info-circle"></i> {{ __tr('Order Flow Information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6>{{ __tr('How WhatsApp Orders Work:') }}</h6>
                                <ol class="mb-0">
                                    <li>{{ __tr('Customer browses your WhatsApp catalog and places an order') }}</li>
                                    <li>{{ __tr('System creates order and asks customer for delivery address') }}</li>
                                    <li>{{ __tr('Payment link is generated and sent to customer') }}</li>
                                    <li>{{ __tr('Customer completes payment through secure payment gateway') }}</li>
                                    <li>{{ __tr('Order status is automatically updated and customer is notified') }}</li>
                                    <li>{{ __tr('You can manage orders from the WhatsApp Orders section') }}</li>
                                </ol>
                            </div>

                            <div class="alert alert-warning">
                                <h6><i class="fa fa-exclamation-triangle"></i> {{ __tr('Requirements:') }}</h6>
                                <ul class="mb-0">
                                    <li>{{ __tr('WhatsApp Business API must be configured') }}</li>
                                    <li>{{ __tr('WhatsApp catalog must be set up with products') }}</li>
                                    <li>{{ __tr('Payment gateway must be configured for online payments') }}</li>
                                    <li>{{ __tr('SSL certificate required for webhook security') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> {{ __tr('Save Settings') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('appScripts')
<script>
$(document).ready(function() {
    // Toggle order settings visibility
    $('input[name="enable_whatsapp_orders"]').change(function() {
        if ($(this).is(':checked')) {
            $('#orderSettings').slideDown();
        } else {
            $('#orderSettings').slideUp();
        }
    });

    // Toggle payment settings visibility
    $('input[name="payment_gateway_enabled"]').change(function() {
        if ($(this).is(':checked')) {
            $('#paymentSettings').slideDown();
        } else {
            $('#paymentSettings').slideUp();
        }
    });

    // Toggle gateway-specific settings
    $('#payment_gateway').change(function() {
        const gateway = $(this).val();
        
        // Hide all gateway settings
        $('#razorpaySettings').hide();
        
        // Show selected gateway settings
        if (gateway === 'razorpay') {
            $('#razorpaySettings').slideDown();
        }
    });
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showSuccessMessage('{{ __tr("Webhook URL copied to clipboard") }}');
    }, function(err) {
        console.error('Could not copy text: ', err);
        showErrorMessage('{{ __tr("Failed to copy to clipboard") }}');
    });
}
</script>
@endpush
