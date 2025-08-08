@extends('layouts.app', ['title' => __tr('Facebook API Setup')])

@section('content')
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            <div class="card">
                {{-- Header --}}
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                <i class="fab fa-facebook text-primary"></i> {{ __tr('Facebook API Setup') }}
                            </h3>
                            <p class="text-sm mb-0">
                                {{ __tr('Configure Facebook Messenger API for your business page') }}
                            </p>
                        </div>
                        <div class="col-auto">
                            @if(getVendorSettings('enable_facebook_messaging'))
                                <span class="badge badge-success">{{ __tr('Enabled') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __tr('Disabled') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Configuration Form --}}
                <form class="lw-ajax-form lw-form" method="post" action="{{ route('vendor.settings.write.update', ['pageType' => 'facebook_api_setup']) }}">
                    <div class="card-body">
                        
                        {{-- Facebook Page Settings --}}
                        <div class="lw-section-heading-block">
                            <h4 class="lw-section-heading">
                                <i class="fab fa-facebook"></i> {{ __tr('Facebook Page Configuration') }}
                            </h4>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6">
                                <label for="lwFacebookPageId">{{ __tr('Facebook Page ID') }} <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="lw-form-field form-control"
                                       name="facebook_page_id"
                                       id="lwFacebookPageId"
                                       placeholder="{{ __tr('Enter Facebook Page ID') }}"
                                       value="{{ getVendorSettings('facebook_page_id') }}" />
                                <small class="form-text text-muted">
                                    {{ __tr('Your Facebook Business Page ID') }}
                                </small>
                            </div>
                            <div class="col-sm-6">
                                <label for="lwFacebookPageName">{{ __tr('Facebook Page Name') }}</label>
                                <input type="text"
                                       class="lw-form-field form-control"
                                       name="facebook_page_name"
                                       id="lwFacebookPageName"
                                       placeholder="{{ __tr('Enter Facebook Page Name') }}"
                                       value="{{ getVendorSettings('facebook_page_name') }}" />
                                <small class="form-text text-muted">
                                    {{ __tr('Your Facebook Page Name (optional)') }}
                                </small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12">
                                <label for="lwFacebookPageAccessToken">{{ __tr('Facebook Page Access Token') }} <span class="text-danger">*</span></label>
                                <textarea class="lw-form-field form-control"
                                          name="facebook_page_access_token"
                                          id="lwFacebookPageAccessToken"
                                          rows="3"
                                          placeholder="{{ __tr('Enter Facebook Page Access Token') }}">{{ getVendorSettings('facebook_page_access_token') }}</textarea>
                                <small class="form-text text-muted">
                                    {{ __tr('Your Facebook Page Access Token with required permissions (pages_messaging, pages_show_list)') }}
                                </small>
                            </div>
                        </div>

                        {{-- Facebook App Settings --}}
                        <div class="lw-section-heading-block">
                            <h4 class="lw-section-heading">
                                <i class="fas fa-cog"></i> {{ __tr('Facebook App Configuration') }}
                            </h4>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6">
                                <label for="lwFacebookAppId">{{ __tr('Facebook App ID') }} <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="lw-form-field form-control"
                                       name="facebook_app_id"
                                       id="lwFacebookAppId"
                                       placeholder="{{ __tr('Enter Facebook App ID') }}"
                                       value="{{ getVendorSettings('facebook_app_id') }}" />
                                <small class="form-text text-muted">
                                    {{ __tr('Your Facebook App ID') }}
                                </small>
                            </div>
                            <div class="col-sm-6">
                                <label for="lwFacebookAppSecret">{{ __tr('Facebook App Secret') }} <span class="text-danger">*</span></label>
                                <input type="password"
                                       class="lw-form-field form-control"
                                       name="facebook_app_secret"
                                       id="lwFacebookAppSecret"
                                       placeholder="{{ __tr('Enter Facebook App Secret') }}"
                                       value="{{ getVendorSettings('facebook_app_secret') }}" />
                                <small class="form-text text-muted">
                                    {{ __tr('Your Facebook App Secret') }}
                                </small>
                            </div>
                        </div>

                        {{-- Webhook Settings (Removed for now) --}}
                        {{-- Will be added back when webhook functionality is needed --}}

                        {{-- Messaging Settings --}}
                        <div class="lw-section-heading-block">
                            <h4 class="lw-section-heading">
                                <i class="fas fa-comments"></i> {{ __tr('Messaging Settings') }}
                            </h4>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="enable_facebook_messaging"
                                           name="enable_facebook_messaging"
                                           value="1"
                                           {{ getVendorSettings('enable_facebook_messaging') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_facebook_messaging">
                                        {{ __tr('Enable Facebook Messaging') }}
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    {{ __tr('Enable or disable Facebook messaging functionality') }}
                                </small>
                            </div>
                            <div class="col-lg-6">
                                <label for="lwFacebookTestRecipient">{{ __tr('Test Recipient') }}</label>
                                <input type="text"
                                       class="lw-form-field form-control"
                                       name="facebook_test_recipient_contact"
                                       id="lwFacebookTestRecipient"
                                       placeholder="{{ __tr('Enter Facebook User ID for testing') }}"
                                       value="{{ getVendorSettings('facebook_test_recipient_contact') }}" />
                                <small class="form-text text-muted">
                                    {{ __tr('Facebook User ID for testing messages') }}
                                </small>
                            </div>
                        </div>

                        {{-- Test Connection --}}
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="button" 
                                        class="btn btn-info btn-sm" 
                                        onclick="testFacebookConnection()">
                                    <i class="fas fa-plug"></i> {{ __tr('Test Connection') }}
                                </button>
                                <div id="facebook-test-result" class="mt-2"></div>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __tr('Save Configuration') }}
                        </button>
                        <a href="{{ route('vendor.settings.read', ['pageType' => 'general']) }}" class="btn btn-secondary">
                            {{ __tr('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Help Modal --}}
<div class="modal fade" id="facebookHelpModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle"></i> {{ __tr('Facebook API Setup Help') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>{{ __tr('How to get Facebook credentials:') }}</h6>
                <ol>
                    <li>{{ __tr('Go to Facebook Developers Console') }}</li>
                    <li>{{ __tr('Create a new app or select existing app') }}</li>
                    <li>{{ __tr('Add Messenger product to your app') }}</li>
                    <li>{{ __tr('Generate Page Access Token for your business page') }}</li>
                    <li>{{ __tr('Configure webhook with the provided URL') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
function testFacebookConnection() {
    const resultDiv = document.getElementById('facebook-test-result');
    const pageId = document.getElementById('lwFacebookPageId').value;
    const accessToken = document.getElementById('lwFacebookPageAccessToken').value;

    // Validate required fields
    if (!pageId || !accessToken) {
        resultDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ __tr("Please enter both Page ID and Access Token before testing.") }}</div>';
        return;
    }

    resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> {{ __tr("Testing connection...") }}</div>';

    // Call Facebook Graph API directly
    const apiUrl = `https://graph.facebook.com/v18.0/${pageId}?fields=id,name,category&access_token=${accessToken}`;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                let errorMessage = '{{ __tr("Connection failed!") }} ';
                if (data.error.message) {
                    errorMessage += data.error.message;
                }
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times"></i> ' + errorMessage + '</div>';
            } else if (data.id && data.name) {
                let successMessage = '{{ __tr("Connection successful!") }}';
                successMessage += ` Connected to: ${data.name}`;
                if (data.category) {
                    successMessage += ` (${data.category})`;
                }
                resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> ' + successMessage + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times"></i> {{ __tr("Unexpected response from Facebook API.") }}</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times"></i> {{ __tr("Connection test failed. Please check your network connection and try again.") }}</div>';
        });
}


function copyToClipboard(button) {
    const input = button.parentElement.previousElementSibling;
    input.select();
    document.execCommand('copy');
    
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        button.innerHTML = originalIcon;
    }, 2000);
}
</script>
@endsection
