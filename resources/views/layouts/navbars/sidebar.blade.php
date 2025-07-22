<style>
    /* Modern Sidebar Styling */
    #sidenav-main {
        background-color: #ffffff !important;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
    }
    
    .navbar-vertical .navbar-nav .nav-link {
        padding: 8px 15px;
        color:black;
        font-weight: 500;
        font-size: 12px;
        border-radius: 6px;
        margin: 4px 8px;
        transition: all 0.2s ease;
    }
    
    .navbar-vertical .navbar-nav .nav-link:hover {
        background-color: #f5f7fa;
        color: #3a4a5c;
    }
    
    .navbar-vertical .navbar-nav .nav-link.active {
        background-color: #f0f5ff;
        color: #1771E6;
        font-weight: 600;
    }
    
    .navbar-vertical .navbar-nav .nav-link i, 
    .navbar-vertical .navbar-nav .nav-link .fa,
    .navbar-vertical .navbar-nav .nav-link .fas {
        font-size: 16px;
        width: 20px;
        margin-right: 8px;
        text-align: center;
        vertical-align: middle;
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
    
    /* Submenu styling */
    .lw-expandable-nav {
        padding-left: 10px;
        margin-top: 5px;
    }
    
    .nav-link-ul {
        font-size: 12px !important;
        padding: 6px 10px 6px 30px !important;
        position: relative;
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
        background-color: #c0c6cc;
    }
    
    
    .nav-link-ul:hover::before {
        background-color: #1771E6;
    }
    
    .nav-link-ul.active {
        color: #1771E6 !important;
        font-weight: 600;
    }
   
    .nav-link-ul.active::before {
        background-color: #1771E6;
    }
    
    /* Dropdown indicators */
    .nav-link[data-toggle="collapse"]::after {
        content: '';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 20px;
        transition: transform 0.2s ease;
        
    }
    
    .nav-link[data-toggle="collapse"][aria-expanded="true"]::after {
        transform: rotate(90deg);
    }
    
    /* Section dividers */
    .sidebar-section-divider {
        height: 1px;
        background-color: #edf0f5;
        margin: 15px 20px;
    }
    
    /* Active submenu background */
   
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
               
                <li class="nav-item">
                    <a class="nav-link" href="#lwSubscriptionSubMenu" data-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="lwSubscriptionSubMenu">
                        <i class="fa fa-wallet icon-wallet"></i>
                        <span class="nav-link-text">{{ __tr('User Plans') }}</span>
                    </a>
                    <div class="collapse show lw-expandable-nav" id="lwSubscriptionSubMenu">
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
                
                <li class="nav-item {{ request('pageType') == 'other' ? 'active' : '' }}">
                    <a class="bg-primary-light nav-link nav-link-footer"
                        href="{{ route('manage.configuration.read', ['pageType' => 'other']) }}">
                        <i class="fa fa-cogs icon-settings"></i>
                        {!! __tr('Setup') !!}
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
                    <a class="nav-link" href="#configurationMenu" data-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="configurationMenu">
                        <i class="fa fa-tools icon-tools"></i>
                        <span class="nav-link-text">{{ __tr('Settings') }}</span>
                    </a>

                    <div class="collapse show lw-expandable-nav" id="configurationMenu">
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
                 @if (hasVendorAccess('messaging')  )
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('vendor.chat_message.contact.view') }}" href="{{ route('vendor.chat_message.contact.view') }}"
                    @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <span x-cloak x-show="unreadMessagesCount" class="badge badge-success rounded-pill ml--2" x-text="unreadMessagesCount"></span>
                        <i class="fa fa-comments icon-chat "></i> <span class="ml--2">{{ __tr('Live Chat') }}</span>
                    </a>
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
                    <a class="nav-link" href="#vendorFlowSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="vendorFlowSubmenuNav"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fas fa-sitemap gradient-icon-10"></i>
                        <span class="">{{ __tr('Flows') }}</span>
                    </a>
                    <div class="collapse lw-expandable-nav" id="vendorFlowSubmenuNav">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul {{ markAsActiveLink('vendor.flow.read.list_view') }}"
                                    href="{{ route('whatsapp-flows.index') }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('All Flows') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif
                @if (hasVendorAccess('manage_whatsapp_orders')  )
                <li class="nav-item">
                    <a class="nav-link {{ markAsActiveLink('vendor.whatsapp.orders.list') }}"
                        href="{{ route('vendor.whatsapp.orders.list') }}"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fa fa-shopping-cart icon-orders"></i>
                        {{ __tr('WhatsApp Orders') }}
                    </a>
                </li>
                @endif
                @if (hasVendorAccess('manage_contacts')  )
                <li class="nav-item">
                    <a class="nav-link" href="#vendorContactSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="vendorContactSubmenuNav"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fa fa-users icon-users "></i>
                        <span class="">{{ __tr('Contacts') }}</span>
                    </a>
                <div class="collapse lw-expandable-nav" id="vendorContactSubmenuNav">
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
                 @if (hasVendorAccess('manage_bot_replies')  )
                 <li class="nav-item">
                    <a class="nav-link" href="#vendorAutomationSubmenuNav" data-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="vendorAutomationSubmenuNav"
                        @if (!isWhatsAppBusinessAccountReady())
                       onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                   @endif>
                        <i class="fas fa-robot icon-chatbot "></i>
                        <span class="">{{ __tr('Chatbot') }}</span>
                    </a>
                <div class="collapse lw-expandable-nav" id="vendorAutomationSubmenuNav">
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
                <li class="nav-item">
                        <a class="nav-link @if(isWhatsAppBusinessAccountReady()) collapsed @else text-warning @endif" href="#vendorSettingsNav" data-toggle="collapse" role="button"
                            aria-expanded="@php echo !isWhatsAppBusinessAccountReady() ? 'true' : 'false'; @endphp" aria-controls="vendorSettingsNav">
                            <i class="fa fa-cog icon-settings"></i>
                            <span class="">{{ __tr('Setup') }}</span>
                        </a>
                    <div class="collapse @if(!isWhatsAppBusinessAccountReady()) show @endif lw-expandable-nav" id="vendorSettingsNav">
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
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __tr('WhatsApp Setup') }} @if(!isWhatsAppBusinessAccountReady())<i class="fas fa-exclamation-triangle ml-1"></i>@endif
                                </a></strong>
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
                                    href="<?= route('vendor.settings.read', ['pageType' => 'api-access']) ?>"
                                    >
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('API Integration') !!}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-ul <?= (isset($pageType) and $pageType == 'api-access') ? 'active' : '' ?>"
                                    href="<?= route('google-sheet-script.index', ['pageType' => 'api-access']) ?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! __tr('Sheets Integration') !!}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif
                @endif
            </ul>
        </div>
    </div>
</nav>