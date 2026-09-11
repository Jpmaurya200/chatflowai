@php
$vendorId = getVendorId();
$vendorPlanDetails = vendorPlanDetails('api_access', 0, $vendorId);
$apiToken = getVendorSettings('vendor_api_access_token');
$vendorUid = getVendorUid();
$apiBaseUrl = route('api.base_url');
$exampleEndpoint = route('api.vendor.chat_message.send.process', ['vendorUid' => $vendorUid]) . '?token=' . $apiToken;
@endphp

<!-- Developer Portal Header Banner -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background-color: #10b981; color: #ffffff; font-size: 11px;">
                        <i class="fas fa-terminal me-1"></i> {{ __tr('REST API Suite') }}
                    </span>
                    <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background-color: #334155; color: #94a3b8; font-size: 11px;">
                        v1.0 Public API
                    </span>
                </div>
                <h2 class="h3 fw-bold mb-1 text-white" style="letter-spacing: -0.02em;">
                    {{ __tr('Developer API & Outgoing Webhook Engine') }}
                </h2>
                <p class="mb-0 small" style="color: #94a3b8;">
                    {{ __tr('Connect your CRM, ERP, e-commerce stores, or custom applications using authenticated REST endpoints and real-time webhook event triggers.') }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('google-sheet-script.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm">
                    <i class="fas fa-table me-1.5 text-success"></i> {{ __tr('Google Sheets Script') }}
                </a>
                <a href="{{ getAppSettings('api_documentation_url', 'https://documenter.getpostman.com') }}" target="_blank" class="btn btn-success rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm" style="background-color: #10b981; border: none;">
                    <i class="fas fa-book me-1.5"></i> {{ __tr('API Documentation') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Credentials, Webhook & cURL Explorer -->
    <div class="col-xl-8 col-lg-7" x-cloak>
        @if (!$vendorPlanDetails['is_limit_available'])
            <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #fef3c7; color: #d97706; flex-shrink: 0;">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ __tr('API Access Locked') }}</h6>
                    <small class="text-muted">{{ __tr('Developer API & Webhook forwarding is not enabled in your current plan. Upgrade your subscription to unlock API keys.') }}</small>
                </div>
            </div>
        @endif

        <!-- Card 1: API Authentication Credentials -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-key text-warning me-2"></i>{{ __tr('API Authentication & Keys') }}
                </h5>
                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small">Bearer Token Auth</span>
            </div>
            <div class="card-body p-4">
                <!-- API Base URL -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __tr('API Base URL') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-globe text-muted"></i></span>
                        <input type="text" class="form-control font-monospace" readonly id="lwApiBaseUrl" value="{{ $apiBaseUrl }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="lwCopyToClipboard('lwApiBaseUrl')" title="{{ __tr('Copy URL') }}">
                            <i class="far fa-copy"></i> {{ __tr('Copy') }}
                        </button>
                    </div>
                </div>

                <!-- Vendor UID -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __tr('Your Vendor UID') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-id-badge text-muted"></i></span>
                        <input type="text" class="form-control font-monospace" readonly id="lwVendorUid" value="{{ $vendorUid }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="lwCopyToClipboard('lwVendorUid')" title="{{ __tr('Copy UID') }}">
                            <i class="far fa-copy"></i> {{ __tr('Copy') }}
                        </button>
                    </div>
                </div>

                <!-- API Access Token -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __tr('Secret API Access Token') }}</label>
                    @if($apiToken)
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-shield-alt text-success"></i></span>
                            <input type="password" class="form-control font-monospace" readonly id="lwAccessToken" value="{{ $apiToken }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()" title="{{ __tr('Show / Hide') }}" id="toggleTokenBtn">
                                <i class="far fa-eye" id="toggleTokenIcon"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" onclick="lwCopyToClipboard('lwAccessToken')" title="{{ __tr('Copy Token') }}">
                                <i class="far fa-copy"></i> {{ __tr('Copy') }}
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> {{ __tr('Pass this token as Bearer header Authorization: Bearer <TOKEN> or URL parameter ?token=<TOKEN>') }}
                        </small>
                    @else
                        <div class="alert alert-light border p-2 small mb-2 text-muted">
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i> {{ __tr('No API token generated yet. Click Generate below.') }}
                        </div>
                    @endif
                </div>

                <!-- Regenerate Token Alert Template -->
                <script type="text/template" id="lwRegenerateTokenAlert">
                    <h2>{{ __tr('Generate New API Token?') }}</h2>
                    <p>{{ __tr('Generating a new token will immediately void your existing token. Any external scripts or integrations using the old token will cease to function until updated.') }}</p>
                </script>

                @if ($vendorPlanDetails['is_limit_available'])
                <form class="lw-ajax-form lw-form mt-3" @if($apiToken) data-confirm="#lwRegenerateTokenAlert" @endif method="post" action="{{ route('vendor.settings.write.update', ['pageType' => 'internals']) }}">
                    <input type="hidden" name="vendor_api_access_token" value="{{ Str::random(64) }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm">
                        <i class="fas fa-sync-alt me-1.5"></i> {{ $apiToken ? __tr('Regenerate API Token') : __tr('Generate API Token') }}
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Card 2: Outbound Webhook Forwarding Engine -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-satellite-dish text-primary me-2"></i>{{ __tr('Real-Time Webhook Forwarding Engine') }}
                </h5>
                <span class="badge px-2.5 py-1 rounded-pill small" style="background-color: #ecfdf5; color: #059669;">
                    HTTP POST Dispatch
                </span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    {{ __tr('When new WhatsApp messages, media attachments, or status updates arrive, our engine forwards the parsed JSON payload to your custom server URL in real time.') }}
                </p>

                @if ($vendorPlanDetails['is_limit_available'])
                <form class="lw-ajax-form lw-form" method="post" action="{{ route('vendor.settings.write.update', ['pageType' => 'vendor_webhook']) }}">
                    <div x-data="{ lwVendorEndpointShow: {{ getVendorSettings('enable_vendor_webhook') ? 1 : 0 }} }">
                        <div class="mb-3">
                            <x-lw.checkbox @click="lwVendorEndpointShow = !lwVendorEndpointShow" id="enableWebhookEndpoint" name="enable_vendor_webhook" :checked="getVendorSettings('enable_vendor_webhook')" data-lw-plugin="lwSwitchery" :label="__tr('Enable Outbound Webhook Dispatch')" />
                        </div>
                        <div x-show="lwVendorEndpointShow" class="mb-3">
                            <label class="form-label small fw-semibold text-muted mb-1">{{ __tr('Your Webhook Target URL') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fas fa-link text-muted"></i></span>
                                <input type="url" class="form-control font-monospace" id="lwWebhookEndpoint" name="vendor_webhook_endpoint" value="{{ getVendorSettings('vendor_webhook_endpoint') }}" placeholder="https://api.yourdomain.com/webhooks/whatsapp">
                            </div>
                            <small class="text-muted mt-1 d-block">
                                {{ __tr('Target endpoint must accept HTTPS POST requests and respond with an HTTP 200 OK status within 5 seconds.') }}
                            </small>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold shadow-sm" style="background-color: #0f172a; border-color: #0f172a;">
                            <i class="fas fa-save me-1"></i> {{ __tr('Save Webhook Settings') }}
                        </button>
                    </div>
                </form>
                @endif

                <!-- Sample Webhook Payload Schema -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-dark"><i class="fas fa-code me-1 text-muted"></i> {{ __tr('Forwarded Payload Format (JSON)') }}</span>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 small" onclick="lwCopyToClipboard('webhookPayloadSnippet')">
                            <i class="far fa-copy"></i> {{ __tr('Copy JSON') }}
                        </button>
                    </div>
                    <pre class="bg-dark text-white rounded-3 p-3 small mb-0 font-monospace overflow-auto" style="max-height: 220px; font-size: 11px;"><code id="webhookPayloadSnippet">{
  "contact": {
    "status": "existing",
    "phone_number": "919876543210",
    "uid": "a1b2c3d4-...",
    "first_name": "Rahul",
    "last_name": "Sharma",
    "email": "customer@example.com",
    "language_code": "en",
    "country": "India"
  },
  "message": {
    "whatsapp_business_phone_number_id": "100234567890123",
    "whatsapp_message_id": "wamid.HBgMOTE5ODc...",
    "replied_to_whatsapp_message_id": null,
    "is_new_message": true,
    "body": "Hello, I want to track my order.",
    "status": null,
    "media": null
  },
  "whatsapp_webhook_payload": {
    "object": "whatsapp_business_account",
    "entry": [...]
  }
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Card 3: cURL Quickstart & Dynamic Attributes -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-code text-success me-2"></i>{{ __tr('cURL API Quickstart: Send Template Message') }}
                </h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-2">
                    {{ __tr('Send WhatsApp templates programmatically to any customer phone number using standard cURL:') }}
                </p>
                <pre class="bg-dark text-white rounded-3 p-3 small mb-3 font-monospace overflow-auto" style="font-size: 11.5px; line-height: 1.5;"><code>curl -X POST "{{ route('api.vendor.chat_message.send.process', ['vendorUid' => $vendorUid]) }}" \
  -H "Authorization: Bearer {{ $apiToken ?: 'YOUR_API_TOKEN' }}" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "919876543210",
    "template_name": "order_confirmation_v1",
    "template_language": "en",
    "header_field_1": "Rahul",
    "field_1": "ORD-99824",
    "field_2": "₹1,499"
  }'</code></pre>

                <!-- Dynamic Variables -->
                <div class="border rounded-3 p-3 bg-light">
                    <h6 class="fw-bold small text-dark mb-2">
                        <i class="fas fa-tags text-primary me-1"></i> {{ __tr('Supported Dynamic Contact Attributes') }}
                    </h6>
                    <p class="text-muted small mb-2">
                        {{ __tr('Use these dynamic tags inside external payloads or webhooks to auto-substitute customer properties:') }}
                    </p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ($dynamicFields ?? ['{first_name}', '{last_name}', '{phone_number}', '{email}', '{country}', '{language_code}', '{full_name}'] as $tag)
                            <code class="bg-white border rounded px-2 py-0.5 small text-dark">{{ $tag }}</code>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Integration Guides & Security Best Practices -->
    <div class="col-xl-4 col-lg-5">
        <!-- 1-Click Integrations Ecosystem -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-puzzle-piece text-info me-2"></i>{{ __tr('Pre-Built Integrations') }}
                </h6>
            </div>
            <div class="card-body p-3">
                <!-- Google Sheets -->
                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-2 border bg-white hover-shadow transition">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #ecfdf5; color: #059669;">
                            <i class="fas fa-file-excel fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small text-dark">Google Sheets</div>
                            <div class="text-muted" style="font-size: 11px;">Auto-send on new rows</div>
                        </div>
                    </div>
                    <a href="{{ route('google-sheet-script.index') }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 small">
                        Setup <i class="fas fa-chevron-right ms-1" style="font-size: 10px;"></i>
                    </a>
                </div>

                <!-- Zapier / Make.com -->
                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-2 border bg-white">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #fff7ed; color: #ea580c;">
                            <i class="fas fa-bolt fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small text-dark">Zapier & Make.com</div>
                            <div class="text-muted" style="font-size: 11px;">Connect 5,000+ cloud apps</div>
                        </div>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">Via Webhook</span>
                </div>

                <!-- E-Commerce Webhooks -->
                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 border bg-white">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #e0e7ff; color: #4338ca;">
                            <i class="fas fa-shopping-bag fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small text-dark">Shopify & WooCommerce</div>
                            <div class="text-muted" style="font-size: 11px;">Order & abandoned cart sync</div>
                        </div>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">REST API</span>
                </div>
            </div>
        </div>

        <!-- Developer Security & Rate Limits -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-shield-alt text-success me-2"></i>{{ __tr('Security & Rate Limits') }}
                </h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <ul class="list-unstyled mb-0 d-grid gap-2.5">
                    <li class="d-flex align-items-start gap-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <div><strong>Keep Token Secret:</strong> Never commit your API Token to public GitHub repositories or frontend client code.</div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <div><strong>Throughput Limits:</strong> Meta Cloud API enforces up to 80 TPS (transactions per second). Batched requests are automatically paced.</div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <div><strong>24-Hour Policy:</strong> Free-form text messages require an active 24h conversation session. Otherwise, approved Meta templates must be invoked.</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('appScripts')
<script>
function toggleTokenVisibility() {
    var input = document.getElementById('lwAccessToken');
    var icon = document.getElementById('toggleTokenIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush