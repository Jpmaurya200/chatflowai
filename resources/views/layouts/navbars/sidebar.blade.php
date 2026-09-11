<style>
    /* Modern Enterprise SaaS Sidebar (AiSensy / WATI Standard) */
    #sidenav-main {
        background: #0F172A !important;
        border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link {
        padding: 10px 14px !important;
        color: #94A3B8 !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        border-radius: 10px !important;
        margin: 3px 10px !important;
        transition: all 0.18s ease-in-out !important;
        border: none !important;
        background-color: transparent !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link:hover,
    .navbar-vertical .navbar-nav .nav-link:focus {
        background-color: rgba(255, 255, 255, 0.06) !important;
        color: #F8FAFC !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link.active {
        background-color: #10B981 !important;
        color: #FFFFFF !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35) !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link i, 
    .navbar-vertical .navbar-nav .nav-link .fa,
    .navbar-vertical .navbar-nav .nav-link .fas,
    .navbar-vertical .navbar-nav .nav-link .far,
    .navbar-vertical .navbar-nav .nav-link .fab {
        font-size: 15px !important;
        width: 22px !important;
        margin-right: 10px !important;
        text-align: center !important;
        color: inherit !important;
        transition: color 0.18s ease !important;
    }

    .navbar-vertical .navbar-nav .nav-link.active i,
    .navbar-vertical .navbar-nav .nav-link.active .fa,
    .navbar-vertical .navbar-nav .nav-link.active .fas,
    .navbar-vertical .navbar-nav .nav-link.active .far,
    .navbar-vertical .navbar-nav .nav-link.active .fab {
        color: #FFFFFF !important;
    }

    /* Category headers */
    .sidebar-category-header {
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: #64748B !important;
        padding: 16px 18px 4px !important;
    }
    
    /* Submenu styling */
    .lw-expandable-nav {
        padding-left: 12px;
        margin-top: 2px;
        margin-bottom: 4px;
    }
    
    .nav-link-ul {
        font-size: 13px !important;
        padding: 8px 14px 8px 30px !important;
        color: #94A3B8 !important;
        border-radius: 8px !important;
        margin: 2px 6px !important;
        background-color: transparent !important;
        transition: all 0.15s ease !important;
        border: none !important;
    }
    
    .nav-link-ul:hover,
    .nav-link-ul:focus {
        color: #F8FAFC !important;
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
    
    .nav-link-ul.active {
        color: #10B981 !important;
        font-weight: 600 !important;
        background-color: rgba(16, 185, 129, 0.12) !important;
        box-shadow: none !important;
    }
    
    /* Dropdown chevron indicator */
    .navbar-vertical .navbar-nav .nav-link[data-toggle="collapse"]::after {
        content: "\f054";
        font-family: 'Font Awesome 5 Free', 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        color: #64748B !important;
        transition: transform 0.2s ease, color 0.2s ease;
        pointer-events: none;
    }

    .navbar-vertical .navbar-nav .nav-link[data-toggle="collapse"]:hover::after {
        color: #F8FAFC !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link[data-toggle="collapse"][aria-expanded="true"]::after {
        transform: translateY(-50%) rotate(90deg);
        color: #10B981 !important;
    }

    .navbar-vertical .navbar-nav .nav-link[data-toggle="collapse"] {
        position: relative;
        padding-right: 36px !important;
    }
    
    /* Section dividers */
    .sidebar-section-divider {
        height: 1px;
        background-color: rgba(255, 255, 255, 0.08);
        margin: 12px 14px;
    }

    /* Logo container */
    .lw-sidebar-logo-normal,
    .lw-sidebar-logo-small {
        background: #FFFFFF;
        border-radius: 10px;
        padding: 6px 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        display: inline-block;
        max-height: 42px;
    }

    .lw-sidebar-logo-small { display: none; }
    @media (max-width: 991.98px) {
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
                <div class="sidebar-category-header">{{ __tr('Overview') }}</div>
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
                                    href="<?= route('vendor.settings.read', ['pageType' => 'general']) ?>">
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
                                    href="<?= route('vendor.settings.read', ['pageType' => 'ai-chat-bot-setup']) ?>">
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
                                    href="{{ route('vendor.chat_message.contact.view') }}">
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
                        aria-expanded="{{ $__vendorContactsOpen ? 'true' : 'false' }}" aria-controls="vendorContactSubmenuNav">
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
                        href="{{ route('vendor.whatsapp_service.templates.read.list_view') }}">
                        <i class="fa fa-layer-group icon-templates"></i>
                        {{ __tr('Templates') }}
                    </a>
                </li>
                @endif
                @if (hasVendorAccess('manage_campaigns')  )
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('vendor.campaign.read.list_view') }}"
                        href="{{ route('vendor.campaign.read.list_view') }}">
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
                        aria-expanded="{{ $__vendorFlowsOpen ? 'true' : 'false' }}" aria-controls="vendorFlowSubmenuNav">
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
                                    href="{{ route('vendor.whatsapp.orders.list') }}">
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
                                    href="<?= route('vendor.settings.read', ['pageType' => 'whatsapp-orders-setup']) ?>">
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
                        aria-expanded="{{ $__vendorAutomationOpen ? 'true' : 'false' }}" aria-controls="vendorAutomationSubmenuNav">
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
                        href="{{ route('vendor.user.read.list_view') }}">
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
            @if (hasVendorAccess() or hasVendorUserAccess())
                <div class="px-3 py-2 mt-4 mx-2 mb-3" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            @if (isWhatsAppBusinessAccountReady())
                            <span class="mr-2" style="width: 8px; height: 8px; background: #10B981; border-radius: 50%; box-shadow: 0 0 6px #10B981; display: inline-block;"></span>
                            <span style="font-size: 11px; font-weight: 600; color: #F8FAFC;">WhatsApp API</span>
                            @else
                            <span class="mr-2" style="width: 8px; height: 8px; background: #F59E0B; border-radius: 50%; box-shadow: 0 0 6px #F59E0B; display: inline-block;"></span>
                            <span style="font-size: 11px; font-weight: 600; color: #FBBF24;">API Offline</span>
                            @endif
                        </div>
                        @if (isWhatsAppBusinessAccountReady())
                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34D399; font-size: 9.5px; padding: 2px 6px;">READY</span>
                        @else
                        <a href="{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}" class="badge" style="background: rgba(245, 158, 11, 0.2); color: #FBBF24; font-size: 9.5px; padding: 2px 6px;">SETUP</a>
                        @endif
                    </div>
                    @if (getVendorSettings('current_phone_number_number'))
                    <div class="mt-1" style="font-size: 10.5px; color: #94A3B8;">
                        <i class="fab fa-whatsapp text-success mr-1"></i>{{ getVendorSettings('current_phone_number_number') }}
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</nav>