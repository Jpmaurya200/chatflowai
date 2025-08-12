<style>
    /* Modern Sidebar Styling */
    #sidenav-main {
        background: #10B981 !important; /* fallback */
        background: linear-gradient(180deg, #10B981 0%, #237D59 100%) !important;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
    }
    
    .navbar-vertical .navbar-nav .nav-link {
        padding: 14px 16px;
        color: #ffffff;
        font-weight: 500;
        font-size: 14px;
        border-radius: 12px;
        margin: 8px 8px;
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background-color: transparent;
    }
    
    .navbar-vertical .navbar-nav .nav-link:hover,
    .navbar-vertical .navbar-nav .nav-link:focus {
        background-color: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.28);
    }
    
    .navbar-vertical .navbar-nav .nav-link.active {
        background-color: #ffffff; /* extreme white */
        color: #237D59 !important; /* contrasting green text */
        font-weight: 700;
        border-color: #ffffff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    
    .navbar-vertical .navbar-nav .nav-link i, 
    .navbar-vertical .navbar-nav .nav-link .fa,
    .navbar-vertical .navbar-nav .nav-link .fas,
    .navbar-vertical .navbar-nav .nav-link .far,
    .navbar-vertical .navbar-nav .nav-link .fab {
        font-size: 16px;
        width: 20px;
        margin-right: 8px;
        text-align: center;
        vertical-align: middle;
        color: currentColor !important; /* icons inherit link color */
    }

    /* Active icons follow text color on filled pill */
    .navbar-vertical .navbar-nav .nav-link.active i,
    .navbar-vertical .navbar-nav .nav-link.active .fa,
    .navbar-vertical .navbar-nav .nav-link.active .fas,
    .navbar-vertical .navbar-nav .nav-link.active .far,
    .navbar-vertical .navbar-nav .nav-link.active .fab {
        color: #237D59 !important;
    }
    
    /* Icon colors */
    .icon-dashboard {
        color: #1771E6;
    }
    
    .icon-users {
        color:rgb(213, 34, 132);
    }
    
    .icon-wallet {
        color: #8D5DEA;
    }
    
    .icon-pages {
        color:rgb(231, 217, 18);
    }
    
    .icon-globe {
        color: #1765C9;
    }
    
    .icon-settings {
        color: #6C757D;
    }
    
    .icon-facebook {
        color: #0866FF;
    }
    
    .icon-tools {
        color: #6C757D;
    }
    
   
    /* Vendor icon colors */
    .icon-chat {
        color: #22D571;
    }
    
    .icon-templates {
        color: #2dbcab;
    }
    
   
    .icon-chatbot{
        color: #A136E6;
    }
    .icon-campaigns {
        color: #2b4d87;
    }
    
    .icon-automation {
        color: #A136E6;
    }
    
    .icon-agents {
        color: #6C757D;
    }
    
    .icon-plan {
        color: #8D5DEA;
    }
    
    .icon-integration {
        color: #28a745;
    }
    
    .icon-shopify {
        color: #96bf47;
    }
    
    .icon-woocommerce {
        color: #7c3aed;
    }
    
    /* Submenu styling */
    .lw-expandable-nav {
        padding-left: 10px;
        margin-top: 5px;
    }
    
    .nav-link-ul {
        font-size: 13px !important;
        padding: 10px 12px 10px 34px !important;
        position: relative;
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        margin: 6px 6px;
        background-color: transparent;
    }
    .navbar-vertical.navbar-expand-md .navbar-nav .nav-link {
        padding: 10px 10px 10px 10px !important;
    }
    .nav-link-ul::before {
        content: '';
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background-color: rgba(18, 43, 29, 0.35);
    }
    
    
    .nav-link-ul:hover::before {
        background-color: #ffffff;
    }
    .nav-link-ul:hover,
    .nav-link-ul:focus {
        background-color: rgba(255,255,255,0.06);
        border-color: rgba(255,255,255,0.26);
    }
    
    .nav-link-ul.active {
        color: #237D59 !important;
        font-weight: 700;
        background-color: #ffffff; /* extreme white */
        border-color: #ffffff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
   
    .nav-link-ul.active::before {
        background-color: #ffffff;
    }
    
    /* Dropdown indicators */
    .nav-link[data-toggle="collapse"]::after {
        content: "\f054"; /* fa-chevron-right */
        font-family: 'Font Awesome 5 Free', 'Font Awesome 6 Free';
        font-weight: 900; /* solid */
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #ffffff !important; /* extreme white */
        transition: transform 0.2s ease;
        display: inline-block;
        pointer-events: none;
    }
    
    .nav-link[data-toggle="collapse"][aria-expanded="true"]::after {
        transform: translateY(-50%) rotate(90deg);
    }

    /* Ensure space for indicator and proper positioning */
    .navbar-vertical .navbar-nav .nav-link[data-toggle="collapse"] {
        position: relative;
        padding-right: 40px !important; /* room for chevron */
    }
    
    /* Section dividers */
    .sidebar-section-divider {
        height: 1px;
        background-color: rgba(255, 255, 255, 0.35);
        margin: 15px 20px;
    }

    /* Footer/secondary links contrast on gradient */
    .nav-link-footer {
        color: #0f5132;
    }
    .nav-link-footer:hover,
    .nav-link-footer:focus,
    .nav-link-footer.active {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.22);
    }

    /* Small screen tweaks to maintain readability */
    @media (max-width: 767.98px) {
        #sidenav-main {
            background: linear-gradient(180deg, #10B981 0%, #237D59 100%) !important;
        }
        .navbar-vertical .navbar-nav .nav-link {
            padding: 10px 14px;
        }
    }

    /* Logo badge on white for contrast over gradient */
    .lw-sidebar-logo-normal,
    .lw-sidebar-logo-small {
        background: #ffffff;
        border-radius: 12px;
        padding: 8px 10px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.10);
        display: inline-block;
        height: auto;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.04);
    }

    /* Reasonable sizing so it looks crisp */
    .lw-sidebar-logo-normal { max-height: 44px; }
    .lw-sidebar-logo-small { max-height: 36px; }

    /* Show only one logo at a time to avoid duplicates */
    .lw-sidebar-logo-small { display: none; }
    @media (max-width: 991.98px) { /* below lg */
        .lw-sidebar-logo-normal { display: none; }
        .lw-sidebar-logo-small { display: inline-block; }
    }
</style>

<!-- Update the icon classes in the navbar -->
<nav class="navbar navbar-vertical fixed-left navbar-expand-md text-dark lw-sidebar-container" id="sidenav-main">
    <div class="container-fluid">
        <span>
            <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main"
        aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
    </button>
    <!-- Brand -->
    <a class="navbar-brand pt-0 d-none d-sm-inline" href="{{ url('/') }}">
        <img src="{{ getAppSettings('logo_image_url') }}" class="navbar-brand-img lw-sidebar-logo-normal" alt="{{ getAppSettings('name') }}">
        <img src="{{ getAppSettings('small_logo_image_url') }}" class="navbar-brand-img lw-sidebar-logo-small" alt="{{ getAppSettings('name') }}">
    </a>
        </span>
        <!-- User -->
        <ul class="nav align-items-center d-md-none">
            <li class="nav-item">
                @include('layouts.navbars.locale-menu')
              </li>
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <div class="media align-items-center">
                        <span class="avatar avatar-sm rounded-circle">
                            <i class="fa fa-user"></i>
                        </span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                    <div class=" dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ __tr('Welcome!') }}</h6>
                    </div>
                    <a href="{{ route('user.profile.edit') }}" class="dropdown-item">
                        <i class="fa fa-user"></i>
                        <span>{{ __tr('My profile') }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a data-method="post" href="{{ route('auth.logout') }}" class="dropdown-item lw-ajax-link-action">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>{{ __tr('Logout') }}</span>
                    </a>
                </div>
            </li>
        </ul>
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
            <!-- Collapse header -->
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <a href="{{ url('/') }}">
                            <img src="{{ getAppSettings('logo_image_url') }}">
                        </a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse"
                            data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false"
                            aria-label="Toggle sidenav">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Navigation -->
            <ul class="navbar-nav">
                @if (hasCentralAccess())
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('central.console') }}" href="{{ route('central.console') }}">
                        <i class="fa fa-chart-line icon-dashboard"></i> {{ __tr('Dashboard') }}
                    </a>
                </li>
                
                <li class="nav-item {{ request('pageType') == 'other' ? 'active' : '' }}">
                    <a class="bg-primary-light nav-link nav-link-footer"
                        href="{{ route('manage.configuration.read', ['pageType' => 'other']) }}">
                        <i class="fa fa-cogs icon-settings"></i>
                        {!! __tr('Setup') !!}
                    </a>
                </li>
                
                <li class="nav-item">
                    @php
                        $__centralSubscriptionOpen = request()->routeIs('central.subscriptions', 'central.subscription.manual_subscription.read.list_view');
                    @endphp
                    <a class="nav-link {{ $__centralSubscriptionOpen ? '' : 'collapsed' }}" href="#lwSubscriptionSubMenu" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__centralSubscriptionOpen ? 'true' : 'false' }}" aria-controls="lwSubscriptionSubMenu">
                        <i class="fa fa-wallet icon-wallet"></i>
                        <span class="nav-link-text">{{ __tr('User Plans') }}</span>
                    </a>
                    <div class="collapse lw-expandable-nav {{ $__centralSubscriptionOpen ? 'show' : '' }}" id="lwSubscriptionSubMenu">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item {{ markAsActiveLink('central.subscriptions') }}">
                                <a class="bg-primary-light nav-link nav-link-ul" href="{{ route('central.subscriptions') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Auto') }}
                                </a>
                            </li>
                            <li class="nav-item {{ markAsActiveLink('central.subscription.manual_subscription.read.list_view') }}">
                                <a class="nav-link nav-link-ul bg-primary-light" href="{{ route('central.subscription.manual_subscription.read.list_view') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Manual/Prepaid') }} @if(getPendingSubscriptionCount())<span class="badge badge-danger ml-2">{{ getPendingSubscriptionCount() }}</span> @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item  ">
                    <a class="nav-link"  href="{{ route('central.vendors') }}" >
                        <i class="fas fa-users icon-users"></i> {{ __tr('Users') }}
                    </a>
                    
                </li>
                
                <li class="nav-item ">
                    <a class="nav-link {{ markAsActiveLink('page.list') }}" href="{{ route('page.list') }}">
                        <i class="fas fa-copy icon-pages"></i> {{ __tr('Pages') }}
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('manage.translations.languages') }}" href="{{ route('manage.translations.languages') }}">
                        <i class="fas fa-globe icon-globe"></i> {{ __tr('Languages') }}
                    </a>
                </li>
                
                
                
                <li class="nav-item {{ request('pageType') == 'whatsapp-onboarding' ? 'active' : '' }}">
                    <a class="bg-primary-light nav-link nav-link-footer"
                        href="{{ route('manage.configuration.read', ['pageType' => 'whatsapp-onboarding']) }}">
                        <i class="fab fa-facebook icon-facebook"></i>
                        {!! __tr('Embedded Signup') !!}
                    </a>
                </li>
                
                <li class="nav-item">
                    @php
                        $__centralConfigOpen = in_array(request('pageType'), ['general','user','currency','payment','email','social-login','misc']) || request()->routeIs('manage.configuration.subscription-plans');
                    @endphp
                    <a class="nav-link {{ $__centralConfigOpen ? '' : 'collapsed' }}" href="#configurationMenu" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__centralConfigOpen ? 'true' : 'false' }}" aria-controls="configurationMenu">
                        <i class="fa fa-tools icon-tools"></i>
                        <span class="nav-link-text">{{ __tr('Settings') }}</span>
                    </a>

                    <div class="collapse lw-expandable-nav {{ $__centralConfigOpen ? 'show' : '' }}" id="configurationMenu">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="bg-primary-light nav-link nav-link-ul {{ request('pageType') == 'general' ? 'active' : '' }}"
                                    href="{{ route('manage.configuration.read', ['pageType' => 'general']) }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('General') }}
                                </a>
                            </li>
                            <li class="nav-item {{ request('pageType') == 'user' ? 'active' : '' }}">
                                <a class="bg-primary-light nav-link nav-link-ul"
                                    href="{{ route('manage.configuration.read', ['pageType' => 'user']) }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('User') !!}
                                </a>
                            </li>
                            <li class="nav-item {{ request('pageType') == 'currency' ? 'active' : '' }}">
                                <a class="bg-primary-light nav-link nav-link-ul"
                                    href="{{ route('manage.configuration.read', ['pageType' => 'currency']) }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Currency') }}
                                </a>
                            </li>
                            <li class="nav-item {{ markAsActiveLink('manage.configuration.payment') }}">
                                <a class="bg-primary-light nav-link-ul nav-link <?= (isset($pageType) and $pageType == 'payment') ? 'active' : '' ?>"
                                    href="<?= route('manage.configuration.read', ['pageType' => 'payment']) ?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Payments') }}
                                </a>
                            </li>
                            <li class="nav-item {{ markAsActiveLink('manage.configuration.subscription-plans') }}">
                                <a class="bg-primary-light nav-link nav-link-ul" href="{{ route('manage.configuration.subscription-plans') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('User Plans') }}
                                </a>
                            </li>
                            <li class="nav-item {{ request('pageType') == 'email' ? 'active' : '' }}">
                                <a class="bg-primary-light nav-link nav-link-ul"
                                    href="{{ route('manage.configuration.read', ['pageType' => 'email']) }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Email') }}
                                </a>
                            </li>
                            <li class="nav-item {{ request('pageType') == 'social-login' ? 'active' : '' }}">
                                <a class="bg-primary-light nav-link nav-link-ul"
                                    href="{{ route('manage.configuration.read', ['pageType' => 'social-login']) }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Logins') }}
                                </a>
                            </li>
                            <li class="nav-item {{ request('pageType') == 'misc' ? 'active' : '' }}">
                                <a class="bg-primary-light nav-link nav-link-ul"
                                    href="{{ route('manage.configuration.read', ['pageType' => 'misc']) }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('Apperance') !!}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <!--li class="nav-item <!--?= Request::fullUrl() == route('manage.configuration.read', ['pageType' => 'licence-information']) ? 'active' : '' ?>"-->
                    <!--a class="bg-primary-light nav-link nav-link-footer"  href="<!--?= route('manage.configuration.read', ['pageType' => 'licence-information']) ?>"-->
                        <!--i class="fas fa-shield-alt" style="color: #20C997 !important"></i-->
                        <!--span--><!--?= __tr('License') ?></span-->
                    <!--/a>
                </li---->
                
                @endif
                @if (hasVendorAccess() or hasVendorUserAccess())
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('vendor.console') }}" href="{{ route('vendor.console') }}">
                        <i class="fa fa-chart-line icon-dashboard"></i>
                        {{ __tr('Dashboard') }}
                    </a>
                </li>
                @if (hasVendorAccess('administrative'))
                <li class="nav-item">
                        @php
                            $__vendorSettingsOpen = request()->routeIs('vendor.settings.read') && in_array(request('pageType'), [
                                'general',
                                'whatsapp-cloud-api-setup',
                                'facebook-api-setup',
                                'instagram-api-setup',
                                'ai-chat-bot-setup',
                            ]);
                        @endphp
                        <a class="nav-link {{ isWhatsAppBusinessAccountReady() ? '' : 'text-warning' }} {{ $__vendorSettingsOpen ? '' : 'collapsed' }}" href="#vendorSettingsNav" data-toggle="collapse" role="button"
                            aria-expanded="{{ $__vendorSettingsOpen ? 'true' : 'false' }}" aria-controls="vendorSettingsNav">
                            <i class="fa fa-cog icon-settings"></i>
                            <span class="">{{ __tr('Setup') }}</span>
                        </a>
                    <div class="collapse lw-expandable-nav {{ $__vendorSettingsOpen ? 'show' : '' }}" id="vendorSettingsNav">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'general') ? 'active' : '' ?>"
                                    href="<?= route('vendor.settings.read', ['pageType' => 'general']) ?>"
                                    @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Basic') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <strong><a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'whatsapp-cloud-api-setup') ? 'active' : '' ?> @if(!isWhatsAppBusinessAccountReady()) text-warning @endif"
                                    href="<?= route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) ?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-whatsapp text-success"></i> {{ __tr('WhatsApp Setup') }} @if(!isWhatsAppBusinessAccountReady())<i class="fas fa-exclamation-triangle ml-1"></i>@endif
                                </a></strong>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'facebook-api-setup') ? 'active' : '' ?>"
                                    href="<?= route('vendor.settings.read', ['pageType' => 'facebook-api-setup']) ?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-facebook text-primary"></i> {{ __tr('Facebook Setup') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'instagram-api-setup') ? 'active' : '' ?>"
                                    href="<?= route('vendor.settings.read', ['pageType' => 'instagram-api-setup']) ?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-instagram text-danger"></i> {{ __tr('Instagram Setup') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'ai-chat-bot-setup') ? 'active' : '' ?>"
                                    href="<?= route('vendor.settings.read', ['pageType' => 'ai-chat-bot-setup']) ?>"
                                    @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('Chatbot Settings') !!}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif
                 @if (hasVendorAccess('messaging')  )
                <li class="nav-item">
                    @php
                        $__vendorChannelsOpen = request()->routeIs('vendor.chat_message.contact.view', 'vendor.facebook.contact.chat.view', 'vendor.instagram.contact.chat.view');
                    @endphp
                    <a class="nav-link {{ $__vendorChannelsOpen ? '' : 'collapsed' }}" href="#vendorChannelsSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__vendorChannelsOpen ? 'true' : 'false' }}" aria-controls="vendorChannelsSubmenuNav">
                        <i class="fa fa-comments icon-chat"></i>
                        <span class="">{{ __tr('All chats') }}</span>
                    </a>
                    <div class="collapse lw-expandable-nav {{ $__vendorChannelsOpen ? 'show' : '' }}" id="vendorChannelsSubmenuNav">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.chat_message.contact.view') }}"
                                    href="{{ route('vendor.chat_message.contact.view') }}"
                                    @if (!isWhatsAppBusinessAccountReady())
                                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                                   @endif>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-whatsapp text-success"></i> {{ __tr('WhatsApp Chat') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.facebook.contact.chat.view') }}"
                                    href="{{ route('vendor.facebook.contact.chat.view') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-facebook text-primary"></i> {{ __tr('Facebook Chat') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.instagram.contact.chat.view') }}"
                                    href="{{ route('vendor.instagram.contact.chat.view') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-instagram text-danger"></i> {{ __tr('Instagram Chat') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif
                @if (hasVendorAccess('manage_contacts')  )
                <li class="nav-item">
                    @php
                        $__vendorContactsOpen = request()->routeIs(
                            'vendor.contact.read.list_view',
                            'vendor.contact.group.read.list_view',
                            'vendor.contact.custom_field.read.list_view'
                        );
                    @endphp
                    <a class="nav-link {{ $__vendorContactsOpen ? '' : 'collapsed' }}" href="#vendorContactSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__vendorContactsOpen ? 'true' : 'false' }}" aria-controls="vendorContactSubmenuNav"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fa fa-users icon-users "></i>
                        <span class="">{{ __tr('Contacts') }}</span>
                    </a>
                <div class="collapse lw-expandable-nav {{ $__vendorContactsOpen ? 'show' : '' }}" id="vendorContactSubmenuNav">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.contact.read.list_view') }}"
                                href="{{ route('vendor.contact.read.list_view') }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('All Contacts') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.contact.group.read.list_view') }}"
                                href="{{ route('vendor.contact.group.read.list_view') }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Contact Groups') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.contact.custom_field.read.list_view') }}"
                                href="{{ route('vendor.contact.custom_field.read.list_view') }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Add Input') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif
                @if (hasVendorAccess('manage_templates')  )
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('vendor.whatsapp_service.templates.read.list_view') }}"
                        href="{{ route('vendor.whatsapp_service.templates.read.list_view') }}"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                    @endif>
                        <i class="fa fa-layer-group icon-templates"></i>
                        {{ __tr('Templates') }}
                    </a>
                </li>
                @endif
                @if (hasVendorAccess('manage_campaigns')  )
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('vendor.campaign.read.list_view') }}"
                        href="{{ route('vendor.campaign.read.list_view') }}"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fa fa-rocket icon-campaigns "></i>
                        {{ __tr('Campaigns') }}
                    </a>
                </li>
                @endif
                @if (hasVendorAccess('manage_flows')  )
                <li class="nav-item">
                    @php
                        $__vendorFlowsOpen = request()->routeIs('vendor.flow.read.list_view', 'whatsapp-flows.index');
                    @endphp
                    <a class="nav-link {{ $__vendorFlowsOpen ? '' : 'collapsed' }}" href="#vendorFlowSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__vendorFlowsOpen ? 'true' : 'false' }}" aria-controls="vendorFlowSubmenuNav"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fas fa-sitemap gradient-icon-10"></i>
                        <span class="">{{ __tr('Flows') }}</span>
                    </a>
                    <div class="collapse lw-expandable-nav {{ $__vendorFlowsOpen ? 'show' : '' }}" id="vendorFlowSubmenuNav">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('whatsapp-flows.index') }}"
                                    href="{{ route('whatsapp-flows.index') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('All Flows') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif
                
                <!-- E-commerce Section -->
                @if (hasVendorAccess('administrative') || hasVendorAccess('manage_whatsapp_orders'))
                <li class="nav-item">
                    @php
                        $__vendorEcommerceOpen = request()->routeIs('vendor.whatsapp.orders.list', 'vendor.integration.shopify.dashboard', 'vendor.integration.woocommerce.dashboard');
                    @endphp
                    <a class="nav-link {{ $__vendorEcommerceOpen ? '' : 'collapsed' }}" href="#vendorEcommerceSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__vendorEcommerceOpen ? 'true' : 'false' }}" aria-controls="vendorEcommerceSubmenuNav">
                        <i class="fa fa-store"></i>
                        <span class="">{{ __tr('E-commerce') }}</span>
                    </a>
                    <div class="collapse lw-expandable-nav {{ $__vendorEcommerceOpen ? 'show' : '' }}" id="vendorEcommerceSubmenuNav">
                        <ul class="nav nav-sm flex-column">
                            @if (hasVendorAccess('manage_whatsapp_orders'))
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.whatsapp.orders.list') }}"
                                    href="{{ route('vendor.whatsapp.orders.list') }}"
                                    @if (!isWhatsAppBusinessAccountReady())
                                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                                   @endif>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-shopping-cart icon-orders"></i> {{ __tr('WhatsApp Orders') }}
                                </a>
                            </li>
                            @endif
                            @if (hasVendorAccess('administrative'))
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.integration.shopify.dashboard') }}"
                                    href="{{ route('vendor.integration.shopify.dashboard') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-shopify icon-shopify"></i> {{ __tr('Shopify') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.integration.woocommerce.dashboard') }}"
                                    href="{{ route('vendor.integration.woocommerce.dashboard') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fab fa-wordpress icon-woocommerce"></i> {{ __tr('WooCommerce') }}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
                
                <!-- Integration Section -->
                @if (hasVendorAccess('administrative'))
                <li class="nav-item">
                    @php
                        $__vendorIntegrationOpen = (request()->routeIs('vendor.settings.read') && in_array(request('pageType'), ['whatsapp-orders-setup', 'api-access']))
                            || request()->routeIs('google-sheet-script.index');
                        $__sheetsActive = request()->routeIs('google-sheet-script.index');
                    @endphp
                    <a class="nav-link {{ $__vendorIntegrationOpen ? '' : 'collapsed' }}" href="#vendorIntegrationSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__vendorIntegrationOpen ? 'true' : 'false' }}" aria-controls="vendorIntegrationSubmenuNav">
                        <i class="fas fa-plug icon-integration"></i>
                        <span class="">{{ __tr('Integrations') }}</span>
                    </a>
                    <div class="collapse lw-expandable-nav {{ $__vendorIntegrationOpen ? 'show' : '' }}" id="vendorIntegrationSubmenuNav">
                        <ul class="nav nav-sm flex-column">
                            
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'whatsapp-orders-setup') ? 'active' : '' ?>"
                                    href="<?= route('vendor.settings.read', ['pageType' => 'whatsapp-orders-setup']) ?>"
                                    @if (!isWhatsAppBusinessAccountReady())
                                        onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                                    @endif>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('Orders & Payments') !!}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'api-access') ? 'active' : '' ?>"
                                    href="<?= route('vendor.settings.read', ['pageType' => 'api-access']) ?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('API Integration') !!}
                                </a>
                            </li>
                            <li class="nav-item {{ $__sheetsActive ? 'active' : '' }}">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('google-sheet-script.index') }}"
                                    href="<?= route('google-sheet-script.index', ['pageType' => 'api-access']) ?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('Sheets Integration') !!}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif
                
                 @if (hasVendorAccess('manage_bot_replies')  )
                 <li class="nav-item">
                    @php
                        $__vendorAutomationOpen = request()->routeIs('vendor.bot_reply.read.list_view', 'vendor.bot_reply.bot_flow.read.list_view');
                    @endphp
                    <a class="nav-link {{ $__vendorAutomationOpen ? '' : 'collapsed' }}" href="#vendorAutomationSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="{{ $__vendorAutomationOpen ? 'true' : 'false' }}" aria-controls="vendorAutomationSubmenuNav"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fas fa-robot icon-chatbot "></i>
                        <span class="">{{ __tr('Chatbot') }}</span>
                    </a>
                <div class="collapse lw-expandable-nav {{ $__vendorAutomationOpen ? 'show' : '' }}" id="vendorAutomationSubmenuNav">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                        
                            <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.bot_reply.read.list_view') }}"
                                href="{{ route('vendor.bot_reply.read.list_view') }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('All Chatbots') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.bot_reply.bot_flow.read.list_view') }}"
                                href="{{ route('vendor.bot_reply.bot_flow.read.list_view') }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('Flow Maker') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
                @endif
                @if (hasVendorAccess('administrative')  )
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('vendor.user.read.list_view') }}"
                        href="{{ route('vendor.user.read.list_view') }}"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fa fa-user-tie icon-agents"></i>
                        {{ __tr('Agents') }}
                    </a>
                </li>
                @endif
                @if (isWhatsAppBusinessAccountReady())
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#lwScanMeDialog">
                        <i class="fa fa-qrcode icon-qrcode"></i>
                        {{ __tr('QR Code') }}
                    </a>
                </li>
                @endif
                @if (hasVendorAccess('administrative'))
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('subscription.read.show') }}"
                        href="{{ route('subscription.read.show') }}">
                        <i class="fa fa-wallet icon-plan"></i>
                        {{ __tr('My Plan') }}
                    </a>
                </li>
                
                @endif
                @endif
            </ul>
        </div>
    </div>
</nav>