<!-- Top navbar -->
<style>
    /* Modern SaaS Top Navbar */
    #navbar-main {
        background: #FFFFFF !important;
        border-bottom: 1px solid #E2E8F0 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04) !important;
    }
    #navbar-main .navbar-nav .nav-link,
    #navbar-main .navbar-brand,
    #navbar-main .h1,
    #navbar-main .h4,
    #navbar-main .h5 {
        color: #0F172A !important;
    }
    #navbar-main .nav-link:hover,
    #navbar-main .nav-link:focus {
        color: #10B981 !important;
    }
    #navbar-main .circular-icon {
        background-color: #F1F5F9;
        color: #475569 !important;
        transition: all 0.15s ease;
    }
    #navbar-main .circular-icon:hover {
        background-color: #E2E8F0;
        color: #0F172A !important;
    }
    #navbar-main .dropdown-menu {
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        padding: 8px;
    }
    #navbar-main .dropdown-item {
        border-radius: 8px;
        font-weight: 500;
        font-size: 13.5px;
        padding: 8px 14px;
        color: #334155;
        transition: all 0.15s ease;
    }
    #navbar-main .dropdown-item:hover {
        background-color: #F8FAFC;
        color: #10B981;
    }
</style>
<nav class="navbar navbar-top shadow navbar-expand-md navbar-dark d-lg-flex d-none" id="navbar-main">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="h1 mb-0 d-none d-lg-inline-block" href="{{ route('home') }}">
            <!-- <strong>{{ __tr('Dashboard') }}</strong> -->
        </a>
        
        @if(session('loggedByVendor'))
        <a data-method="post" href="{{ route('vendor.user.write.logout_as') }}" class="h4 mb-0 d-none d-lg-inline-block lw-ajax-link-action px-5"><i class="fa fa-arrow-left"></i> {{ __tr('You (__userFullName__) are logged to this user account , click here to go back to your account', [
            '__userFullName__' => session('loggedByVendor.name')
        ]) }}</a>
        @elseif(session('loggedBySuperAdmin'))
        <a data-method="post" href="{{ route('central.vendors.user.write.logout_as') }}" class="h4 mb-0 d-none d-lg-inline-block lw-ajax-link-action px-5" ><i class="fa fa-arrow-left"></i> {{ __tr('You (__userFullName__) are logged to this vendor admin account , click here to go back to super admin section', [
            '__userFullName__' => session('loggedBySuperAdmin.name')
        ]) }}</a>
        @endif
        <!-- User -->
        <ul class="navbar-nav align-items-center d-none d-md-flex">
            @if(hasVendorAccess('messaging'))
            <li class="nav-item">
                <a class="nav-link lw-ajax-link-action" href="{{ route('vendor.disable.sound_message_sound_notification.write') }}"><i class="fa " :title="disableSoundForMessageNotification ? '{{ __tr('Sound Notifications are disabled for incoming messages') }}' : '{{ __tr('Sound Notifications are enabled for incoming messages') }}'" :class="disableSoundForMessageNotification ? 'fa-bell-slash' : 'fa-bell'"></i></a>
            </li>
            @endif
            <li class="nav-item">
            @include('layouts.navbars.locale-menu')
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="media align-items-center">
                        <div class="media-body ml-2 d-none d-lg-block">
                            <i class="fa fa-user circular-icon"style="font-size: 15px;" ></i> <span><strong>{{ getUserAuthInfo('profile.full_name') }}</strong></span>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                    <div class=" dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ __tr('Welcome __firstName__', [
                            '__firstName' => getUserAuthInfo('profile.first_name')
                        ]) }}</h6>
                    </div>
                    <a href="{{ route('user.profile.edit') }}" class="dropdown-item">
                        <i class="fa fa-user mr-1 circular-icon"></i>
                        <span><strong>{{ __tr('My Profile') }}</strong></span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a data-method="post" href="{{ route('auth.logout') }}" class="dropdown-item lw-ajax-link-action">
                        <i class="fas fa-sign-out-alt mr-1 circular-icon"></i>
                        <span><strong>{{ __tr('Logout') }}</strong></span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>