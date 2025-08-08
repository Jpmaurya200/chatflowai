{{-- Instagram API Setup Settings Page --}}

<div class="lw-section-heading-block">
    <h3 class="lw-section-heading">
        <i class="fab fa-instagram"></i> {{ __tr('Instagram API Setup') }}
    </h3>
    <div class="lw-section-description">
        {{ __tr('Configure Instagram Business API for messaging functionality') }}
    </div>
</div>

{{-- Instagram API Status Alert --}}
<div class="alert alert-info" id="lwInstagramStatusAlert">
    <div class="d-flex align-items-center">
        <div class="alert-icon">
            <i class="fab fa-instagram fa-2x"></i>
        </div>
        <div class="ml-3">
            <h4 class="alert-heading">{{ __tr('Instagram API Status') }}</h4>
            <div id="lwInstagramStatusContent">
                <p class="mb-0">{{ __tr('Loading Instagram API status...') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Configuration Form --}}
<form class="lw-ajax-form lw-form" method="post" action="{{ route('vendor.settings.write.update', ['pageType' => 'instagram_api_setup']) }}">
    <div class="form-group row">
        <div class="col-sm-6">
            <label for="lwInstagramAccountId">{{ __tr('Instagram Account ID') }} <span class="text-danger">*</span></label>
            <input type="text"
                   class="lw-form-field form-control"
                   name="instagram_account_id"
                   id="lwInstagramAccountId"
                   placeholder="{{ __tr('Enter Instagram Business Account ID') }}"
                   value="{{ getVendorSettings('instagram_account_id') }}" />
            <small class="form-text text-muted">
                {{ __tr('Your Instagram Business Account ID') }}
            </small>
        </div>
        <div class="col-sm-6">
            <label for="lwInstagramPageName">{{ __tr('Instagram Account Name') }}</label>
            <input type="text"
                   class="lw-form-field form-control"
                   name="instagram_account_name"
                   id="lwInstagramAccountName"
                   placeholder="{{ __tr('Instagram Account Name') }}"
                   value="{{ getVendorSettings('instagram_account_name') }}" />
            <small class="form-text text-muted">
                {{ __tr('Your Instagram account name (optional)') }}
            </small>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-12">
            <label for="lwInstagramAccessToken">{{ __tr('Instagram Access Token') }} <span class="text-danger">*</span></label>
            <textarea class="lw-form-field form-control"
                      name="instagram_access_token"
                      id="lwInstagramAccessToken"
                      rows="3"
                      placeholder="{{ __tr('Enter Instagram Access Token') }}">{{ getVendorSettings('instagram_access_token') }}</textarea>
            <small class="form-text text-muted">
                {{ __tr('Your Instagram Access Token with required permissions (instagram_basic, instagram_manage_messages)') }}
            </small>
        </div>
    </div>



    {{-- Messaging Settings --}}
    <div class="lw-section-heading-block">
        <h4 class="lw-section-heading">
            <i class="fas fa-cog"></i> {{ __tr('Messaging Settings') }}
        </h4>
    </div>

    <div class="form-group row">
        <div class="col-sm-12">
            <div class="custom-control custom-switch">
                <input type="hidden" name="enable_instagram_messaging" value="0">
                <input type="checkbox"
                       class="custom-control-input"
                       id="lwEnableInstagramMessaging"
                       name="enable_instagram_messaging"
                       value="1"
                       {{ getVendorSettings('enable_instagram_messaging') ? 'checked' : '' }}>
                <label class="custom-control-label" for="lwEnableInstagramMessaging">
                    {{ __tr('Enable Instagram Messaging') }}
                </label>
            </div>
            <small class="form-text text-muted">
                {{ __tr('Enable or disable Instagram messaging functionality') }}
            </small>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="form-group row">
        <div class="col-sm-12">
            <button type="button" class="btn btn-light" id="lwTestInstagramConnection">
                <i class="fas fa-check-circle"></i> {{ __tr('Test Connection') }}
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ __tr('Save Configuration') }}
            </button>
        </div>
    </div>

    <input type="hidden" name="pageType" value="instagram_api_setup" />
</form>

{{-- Setup Instructions --}}
<div class="lw-section-heading-block mt-5">
    <h4 class="lw-section-heading">
        <i class="fas fa-info-circle"></i> {{ __tr('Setup Instructions') }}
    </h4>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>{{ __tr('1. Get Instagram Access Token') }}</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>{{ __tr('Go to Facebook Developer Console') }}</li>
                    <li>{{ __tr('Create a new app with Instagram Basic Display') }}</li>
                    <li>{{ __tr('Generate a long-lived access token') }}</li>
                    <li>{{ __tr('Make sure token has instagram_basic and instagram_manage_messages permissions') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>{{ __tr('2. Get Instagram Account ID') }}</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>{{ __tr('Use Graph API Explorer or your access token') }}</li>
                    <li>{{ __tr('Make a GET request to: /me/accounts') }}</li>
                    <li>{{ __tr('Find your Instagram Business Account ID') }}</li>
                    <li>{{ __tr('Copy the ID and paste it in the Account ID field above') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h6><i class="fas fa-lightbulb"></i> {{ __tr('Quick Tip') }}</h6>
            <p class="mb-0">
                {{ __tr('You can use the Graph API Explorer at') }}
                <a href="https://developers.facebook.com/tools/explorer/" target="_blank">https://developers.facebook.com/tools/explorer/</a>
                {{ __tr(' to test your access token and get your account ID.') }}
            </p>
        </div>
    </div>
</div>

@push('footer')
<script>
// Setup CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    // Load Instagram status on page load
    loadInstagramStatus();
    
    // Test Instagram connection
    $('#lwTestInstagramConnection').on('click', function() {
        testInstagramConnection();
    });
});

function loadInstagramStatus() {
    $.get('/vendor-console/instagram/config/status')
        .done(function(response) {
            if (response.success) {
                updateStatusDisplay(response.data);
            }
        })
        .fail(function() {
            $('#lwInstagramStatusContent').html('<p class="mb-0 text-danger">{{ __tr("Failed to load status") }}</p>');
        });
}

function updateStatusDisplay(status) {
    let statusHtml = '<div class="row">';

    // API Configuration Status
    statusHtml += '<div class="col-md-6">';
    statusHtml += '<h6>{{ __tr("API Configuration") }}</h6>';
    statusHtml += '<ul class="list-unstyled">';
    statusHtml += '<li><i class="fas fa-' + (status.access_token_valid ? 'check text-success' : 'times text-danger') + '"></i> {{ __tr("Access Token Valid") }}</li>';
    statusHtml += '<li><i class="fas fa-' + (status.account_id_valid ? 'check text-success' : 'times text-danger') + '"></i> {{ __tr("Account ID Valid") }}</li>';
    statusHtml += '</ul>';
    statusHtml += '</div>';

    // Connection Status
    statusHtml += '<div class="col-md-6">';
    statusHtml += '<h6>{{ __tr("Connection Status") }}</h6>';
    statusHtml += '<ul class="list-unstyled">';
    statusHtml += '<li><i class="fas fa-' + (status.connection_active ? 'check text-success' : 'times text-danger') + '"></i> {{ __tr("Instagram Connected") }}</li>';
    statusHtml += '<li><i class="fas fa-' + (status.messaging_enabled ? 'check text-success' : 'times text-danger') + '"></i> {{ __tr("Messaging Enabled") }}</li>';
    statusHtml += '</ul>';
    statusHtml += '</div>';

    statusHtml += '</div>';

    $('#lwInstagramStatusContent').html(statusHtml);
}

function testInstagramConnection() {
    const $btn = $('#lwTestInstagramConnection');
    const originalText = $btn.html();

    $btn.html('<i class="fas fa-spinner fa-spin"></i> {{ __tr("Testing...") }}').prop('disabled', true);

    $.post('/vendor-console/instagram/test-connection')
        .done(function(response) {
            if (response.success) {
                showSuccessMessage('{{ __tr("Instagram connection successful!") }}');

                // Update account name field if available
                if (response.data && response.data.username) {
                    $('#lwInstagramAccountName').val(response.data.username);
                }

                // Show account information
                if (response.data) {
                    let accountInfo = '<div class="alert alert-success mt-3 instagram-account-info">';
                    accountInfo += '<h6><i class="fas fa-check-circle"></i> {{ __tr("Account Information") }}</h6>';
                    accountInfo += '<ul class="mb-0">';
                    accountInfo += '<li><strong>{{ __tr("Username") }}:</strong> ' + (response.data.username || 'N/A') + '</li>';
                    accountInfo += '<li><strong>{{ __tr("Account Type") }}:</strong> ' + (response.data.account_type || 'N/A') + '</li>';
                    accountInfo += '<li><strong>{{ __tr("Media Count") }}:</strong> ' + (response.data.media_count || 0) + '</li>';
                    accountInfo += '</ul>';
                    accountInfo += '</div>';

                    // Remove any existing account info and add new one
                    $('.instagram-account-info').remove();
                    $btn.closest('.form-group').after(accountInfo);
                }

                loadInstagramStatus(); // Reload status
            } else {
                showErrorMessage(response.message || '{{ __tr("Connection test failed") }}');
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showErrorMessage(response?.message || '{{ __tr("Connection test failed") }}');
        })
        .always(function() {
            $btn.html(originalText).prop('disabled', false);
        });
}


</script>
@endpush
