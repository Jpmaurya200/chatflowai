@php
if(!isset($vendorViewBySuperAdmin))
$vendorViewBySuperAdmin = false;
@endphp
@if (hasCentralAccess() and !$vendorViewBySuperAdmin )
<div class="header pb-5 pt-2 pt-md-7">
    <div class="container-fluid">
    </div>
    
</div>
{{-- show.dropdown.result --}}
@elseif(hasVendorAccess() or hasVendorUserAccess() or $vendorViewBySuperAdmin )
<div class="header">
    <div class="container-fluid">
    <div class="container-fluid ">
        <div class="header-body">
            <!-- Welcome card -->
            <div class="row pb-0 pt-3 pt-lg-6 d-flex align-items-stretch mt-4">
                <div class="col-12 p-0">
                    <div class="welcome-card" style="
                        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
                        border: 1px solid rgba(255, 255, 255, 0.08);
                        border-radius: 16px;
                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
                        overflow: hidden;
                        position: relative;
                    ">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <h1 class="mb-0 font-weight-bold" style="font-size: 1.85rem; color: #FFFFFF;">
                                            Welcome, {{ getUserAuthInfo('profile.first_name') }}!
                                        </h1>
                                        <span class="badge ml-3" style="background: rgba(16, 185, 129, 0.2); color: #34D399; font-size: 11px;">PRO</span>
                                    </div>
                                    <p class="mb-0" style="font-size: 0.95rem; color: #94A3B8;">
                                        Manage your WhatsApp Cloud API, launch high-conversion campaigns, and automate customer conversations.
                                    </p>
                                </div>
                                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                                    <div class="d-flex flex-wrap justify-content-lg-end" style="gap: 10px;">
                                        <a href="{{ route('subscription.read.show') }}" class="btn btn-sm btn-secondary" style="border-radius: 8px;">
                                            <i class="fas fa-crown text-warning mr-1"></i> View Plan
                                        </a>
                                        <a href="{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}" class="btn btn-sm btn-primary" style="border-radius: 8px;">
                                            <i class="fab fa-whatsapp mr-1"></i> API Setup
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Decorative Elements -->
                        <div class="welcome-shape-1" style="
                            position: absolute;
                            top: -50px;
                            right: -50px;
                            width: 200px;
                            height: 200px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.08);
                        "></div>

                        <div class="welcome-shape-2" style="
                            position: absolute;
                            bottom: -80px;
                            left: -80px;
                            width: 250px;
                            height: 250px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.08);
                        "></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid p-0">
    <div class="row d-flex align-items-stretch">
        <div class="col-12 mb-xl-3">
            <div class="row">
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-blue">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="stat-card-content">
                                <div style="font-size:15px;">
                                  Total<br>
                                    <span class="font-weight-bold text-primary total" style="font-size:20px;">{{ __tr($totalMessagesProcessed + $messagesInQueue) }}</span>
                                     <span class="font-weight-bold text-primary"> Messages</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-green">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="stat-card-content">
                            <div style="font-size:15px;">
                                    Sent<br>
                                    <span class="font-weight-bold text-success total" style="font-size:20px;">{{ __tr($totalMessagesProcessed) }}</span>
                                    <span class="font-weight-bold text-success"> Messages</span>
                                </div>
                            </div>
                        </div>
                    </div>                    
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-pink">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-hourglass-half"></i>
                                    </div>
                            <div class="stat-card-content">
                                <div style="font-size:15px;">
                                     Pending<br>
                                     <span class="font-weight-bold text-muted total" style="font-size:20px;">{{ __tr($messagesInQueue) }}</span>
                                    <span class="font-weight-bold text-muted"> Messages</span>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>        
        </div>
    </div>
</div>

<!-- Analytics + QR row moved just below the welcome section -->
<div class="container-fluid ">
    <div class="row d-flex align-items-stretch">
        <div class="col-xl-6">
            <div class="graph-card shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="graph-card-body p-3">
                <div class="graph-header mb-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white fw-semibold">Message Analytics</h5>
                    <span class="badge bg-success-subtle text-success fw-medium">Live</span>
                </div>
                <div class="chart-wrapper" style="position: relative;">
                    <canvas id="donutChart" width="400" height="400" style="max-height:220px;"></canvas>
                </div>
                <div class="chart-legend mt-4 d-flex justify-content-center gap-4 flex-wrap">
                    <div class="legend-item d-flex align-items-center">
                    <span class="legend-dot" style="background-color: #22D571;"></span>
                    <span class="text-white ms-2" style="margin-right: 8px;">Sent Messages</span>
                    </div>
                    <div class="legend-item d-flex align-items-center">
                    <span class="legend-dot" style="background-color: #FFD166;"></span>
                    <span class="text-white ms-2">Pending Messages</span>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 d-flex align-items-center mt-4 mt-xl-0">
            <div class="card h-100 w-100" style="border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-body ">
                    <div class="">
                    @if (!empty(getVendorSettings('whatsapp_phone_numbers')))
                    @foreach (getVendorSettings('whatsapp_phone_numbers') as $whatsappPhoneNumber)
                        @php
                            $qualityRating = strtoupper($whatsappPhoneNumber['quality_rating'] ?? 'GREEN');
                            $qualityBg = ($qualityRating === 'GREEN') ? '#ecfdf5' : (($qualityRating === 'YELLOW') ? '#fef3c7' : '#fee2e2');
                            $qualityColor = ($qualityRating === 'GREEN') ? '#059669' : (($qualityRating === 'YELLOW') ? '#d97706' : '#dc2626');
                            $qualityDot = ($qualityRating === 'GREEN') ? '#10b981' : (($qualityRating === 'YELLOW') ? '#f59e0b' : '#ef4444');
                        @endphp
                        <div class="text-center">
                            <img class="lw-qr-image mb-2" src="{{ route('vendor.whatsapp_qr', [
                            'vendorUid' => getVendorUid(),
                            'phoneNumber' => cleanDisplayPhoneNumber($whatsappPhoneNumber['display_phone_number']),
                        ]) }}">
                            <div class="h3 text-dark font-weight-bold mb-0">{{ $whatsappPhoneNumber['verified_name'] }}</div>
                            <div class="h5 text-primary font-weight-bold mb-2">{{ $whatsappPhoneNumber['display_phone_number'] }}</div>
                            <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill" style="background-color: {{ $qualityBg }}; border: 1px solid {{ $qualityColor }}30;">
                                <span class="rounded-circle d-inline-block mr-1" style="width: 7px; height: 7px; background-color: {{ $qualityDot }};"></span>
                                <span class="small font-weight-bold" style="color: {{ $qualityColor }}; font-size: 11px;">
                                    {{ __tr('Meta Quality Health') }}: {{ $qualityRating }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    @endif
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- features section  -->
<div class="container-fluid mt-4 ">
    <div class="row features-row">
        <div class="col-md-4 col-sm-6">
            <div style="
                background-color: #ffffff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.12)'"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.05)'"
            >
                <!-- Top: Icon and Dynamic Count -->
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                ">
                    <div style="font-size: 16px; font-weight: 600; color: #333; display: flex; align-items: center;">
                        <i class="fas fa-comments" style="font-size: 20px; color: #28a745; margin-right: 8px;"></i>
                        Live Chat
                    </div>
                    <div x-cloak x-show="unreadMessagesCount"
                        x-text="unreadMessagesCount"
                        style="font-size: 15px; font-weight: bold; color: #111;">
                    </div>
                </div>

                <!-- Description -->
                <div style="font-size: 14px; color: #666; margin-bottom: 16px;">
                    Centralize WhatsApp chats in one team inbox for seamless support.
                </div>

                <!-- CTA Button -->
                <div style="margin-top: auto;">
                    <a href="{{ route('vendor.chat_message.contact.view') }}"
                        style="
                            display: inline-flex;
                            align-items: center;
                            font-size: 14px;
                            font-weight: 500;
                            margin-top: 10px;
                            color: #fff;
                            background-color: #28a745;
                            padding: 10px 16px;
                            border-radius: 8px;
                            text-decoration: none;
                            transition: background-color 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='#246A48'"
                        onmouseout="this.style.backgroundColor='#28a745'"
                        class="chat-button"
                        @if (!isWhatsAppBusinessAccountReady())
                            onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                        @endif>
                        <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>
                        Go To
                    </a>
                </div>
            </div>
        </div>

   
        <div class="col-md-4 col-sm-6">
            <div style="
                background-color: #ffffff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.12)'"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.05)'"
            >
                <!-- Top Row: Title and Count -->
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                ">
                    <div style="font-size: 16px; font-weight: 600; color: #333; display: flex; align-items: center;">
                        <i class="fa fa-layer-group" style="font-size: 20px; color: #28a745; margin-right: 8px;"></i>
                        Template
                    </div>
                    <div style="font-size: 15px; font-weight: bold; color: #111;">
                        {{ __tr($totalTemplates) }}
                    </div>
                </div>

                <!-- Description -->
                <div style="font-size: 14px; color: #666; margin-bottom: 16px;">
                    Create and manage approved templates for consistent, compliant WhatsApp messaging.
                </div>

                <!-- CTA Button -->
                <div style="margin-top: 12px;">
                    <a href="{{ route('vendor.whatsapp_service.templates.read.new_view') }}"
                        style="
                            display: inline-flex;
                            align-items: center;
                            font-size: 14px;
                            font-weight: 500;
                            color: #fff;
                            background-color: #4CAF50;
                            padding: 10px 16px;
                            border-radius: 8px;
                            text-decoration: none;
                            transition: background-color 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='#388e3c'"
                        onmouseout="this.style.backgroundColor='#4CAF50'"
                        class="template-button"
                        @if (!isWhatsAppBusinessAccountReady())
                            onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                        @endif>
                        <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>
                        {{ __tr('Create') }}
                    </a>
                </div>
            </div>
        </div>

 
        <div class="col-md-4 col-sm-6">
            <div style="
                background-color: #ffffff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.12)'"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.05)'"
            >
                <!-- Top Row: Icon & Campaign Count -->
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                ">
                    <div style="font-size: 16px; font-weight: 600; color: #333; display: flex; align-items: center;">
                        <i class="fa fa-rocket" style="font-size: 20px; color: #2B4D87; margin-right: 8px;"></i>
                        Campaign
                    </div>
                    <div style="font-size: 15px; font-weight: bold; color: #111;">
                        {{ __tr($totalCampaigns) }}
                    </div>
                </div>

                <!-- Description -->
                <div style="font-size: 14px; color: #666; margin-bottom: 16px;">
                    Run targeted campaigns with personalized content at scale.
                </div>

                <!-- CTA Button -->
                <div style="margin-top: auto;">
                    <a href="{{ route('vendor.campaign.new.view') }}"
                        style="
                            display: inline-flex;
                            align-items: center;
                            font-size: 14px;
                            font-weight: 500;
                            color: #fff;
                            background-color: #2B4D87;
                            padding: 10px 16px;
                            border-radius: 8px;
                            text-decoration: none;
                            transition: background-color 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='#1f3b64'"
                        onmouseout="this.style.backgroundColor='#2B4D87'"
                        class="campaign-button"
                        @if (!isWhatsAppBusinessAccountReady())
                            onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                        @endif>
                        <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>
                        Create
                    </a>
                </div>
            </div>
        </div>


        <div class="col-md-4 col-sm-6">
            <div style="
                background-color: #ffffff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.12)'"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.05)'"
            >
                <!-- Top: Icon and Contact Count -->
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                ">
                    <div style="font-size: 16px; font-weight: 600; color: #333; display: flex; align-items: center;">
                        <i class="fa fa-users" style="font-size: 20px; color: #D52284; margin-right: 8px;"></i>
                        Contacts
                    </div>
                    <div style="font-size: 15px; font-weight: bold; color: #111;">
                        {{ __tr($totalContacts) }}
                    </div>
                </div>

                <!-- Description -->
                <div style="font-size: 14px; color: #666; margin-bottom: 16px;">
                    Segment and manage customers with custom fields and tags.
                </div>

                <!-- CTA Button -->
                <div style="margin-top: auto;">
                    <a href="{{ route('vendor.contact.read.list_view') }}"
                        style="
                            display: inline-flex;
                            align-items: center;
                            font-size: 14px;
                            font-weight: 500;
                            color: #fff;
                            background-color: #D52284;
                            padding: 10px 16px;
                            border-radius: 8px;
                            text-decoration: none;
                            transition: background-color 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='#b51e70'"
                        onmouseout="this.style.backgroundColor='#D52284'"
                        class="contact-button"
                        @if (!isWhatsAppBusinessAccountReady())
                            onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                        @endif>
                        <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>
                        Create
                    </a>
                </div>
            </div>
        </div>


       <div class="col-md-4 col-sm-6">
            <div style="
                background: #ffffff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.12)'"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.05)'"
            >
                <!-- Top: Icon + Count -->
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                ">
                    <div style="font-size: 16px; font-weight: 600; color: #333; display: flex; align-items: center;">
                        <i class="fas fa-robot" style="font-size: 20px; color: #6f42c1; margin-right: 8px;"></i>
                        Bot Reply
                    </div>
                    <div style="font-size: 15px; font-weight: bold; color: #111;">
                        {{ __tr($totalBotReplies) }}
                    </div>
                </div>

                <div style="font-size: 14px; color: #666; margin-bottom: 16px;">
                    Automate replies to handle FAQs and offer 24/7 WhatsApp support.
                </div>

                <div style="margin-top: auto;">
                    <a href="{{ route('vendor.bot_reply.read.list_view') }}"
                        style="
                            display: inline-flex;
                            align-items: center;
                            font-size: 14px;
                            font-weight: 500;
                            color: #fff;
                            background-color: #6f42c1;
                            padding: 10px 16px;
                            border-radius: 8px;
                            text-decoration: none;
                            transition: background-color 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='#5936a9'"
                        onmouseout="this.style.backgroundColor='#6f42c1'"
                        class="bot-button"
                        @if (!isWhatsAppBusinessAccountReady())
                            onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                        @endif>
                        <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>
                        Create
                    </a>
                </div>
            </div>
        </div>


        <div class="col-md-4 col-sm-6">
            <div style="
                background: #ffffff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.12)'"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0, 0, 0, 0.05)'">
            
                <!-- Top Row: Icon & Count -->
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                ">
                    <div style="font-size: 16px; font-weight: 600; color: #333; display: flex; align-items: center;">
                        <i class="fa fa-user-tie" style="font-size: 20px; color: #fd7e14; margin-right: 8px;"></i>
                        Agents
                    </div>
                    <div style="font-size: 15px; font-weight: bold; color: #111;">
                        {{ __tr($activeTeamMembers) }}
                    </div>
                </div>

                <div style="font-size: 14px; color: #666; margin-bottom: 16px;">
                    Control team access and track agent performance for better support.
                </div>

                <div style="margin-top: auto;">
                    <a href="{{ route('vendor.user.read.list_view') }}"
                        style="
                            display: inline-flex;
                            align-items: center;
                            font-size: 14px;
                            font-weight: 500;
                            color: #fff;
                            background-color: #fd7e14;
                            padding: 10px 16px;
                            border-radius: 8px;
                            text-decoration: none;
                            transition: background-color 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='#e96a00'"
                        onmouseout="this.style.backgroundColor='#fd7e14'"
                        class="agent-button"
                        @if (!isWhatsAppBusinessAccountReady())
                            onclick="alertAndRedirect(event, '{{ route('vendor.settings.read', ['pageType' => 'whatsapp-cloud-api-setup']) }}')"
                        @endif>
                        <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>
                        Create
                    </a>
                </div>
            </div>
        </div>
</div>
        <!--feature section end  -->

 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get the chart canvas
        const ctx = document.getElementById('donutChart').getContext('2d');

        // Calculate percentages
        const sentMessages = {{ $totalMessagesProcessed }};
        const pendingMessages = {{ $messagesInQueue }};
        const total = sentMessages + pendingMessages;
        
        let sentPercentage = 0;
        let pendingPercentage = 0;
        
        if (total > 0) {
            sentPercentage = Math.round((sentMessages / total) * 100);
            pendingPercentage = Math.round((pendingMessages / total) * 100);
        }

        // Data for the chart
        const data = {
            labels: ['Sent Messages', 'Pending Messages'],
            datasets: [{
                data: [sentPercentage, pendingPercentage],
                backgroundColor: ['#22D571', '#FFD166'],
                hoverBackgroundColor: ['#1ebe64', '#f5c759'],
                borderWidth: 0,
            }]
        };

        // Chart configuration
        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        // Render the chart
        new Chart(ctx, config);
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const featureCounter = document.querySelectorAll('.feature-counter, .total');
    featureCounter.forEach(counter => {
        const target = parseInt(counter.innerText, 10);
        // If target is 0 or invalid, just show 0
        if (isNaN(target) || target === 0) {
            counter.innerText = 0;
            return;
        }
        
        // Make animation much faster - reduced duration
        const duration = 1; // Reduced from 0.5 to 0.2 seconds
        
        // For larger numbers, use fewer steps to make animation faster
        const steps = target > 100 ? 10 : target > 50 ? 5 : target;
        const increment = Math.ceil(target / steps);
        const incrementTime = Math.floor((duration * 1000) / steps);
        
        let count = 0;
        const interval = setInterval(() => {
            count += increment;
            // Ensure we don't exceed the target
            if (count >= target) {
                counter.innerText = target;
                clearInterval(interval);
            } else {
                counter.innerText = count;
            }
        }, incrementTime);
    });
});

</script>
@endif
<style>
    /* Modern Card Styling based on reference image */
    .graph-card {
    background: linear-gradient(135deg, #084C41, #0C6E4F);
    transition: all 0.4s ease;
    box-shadow: 0 10px 30px rgba(34, 213, 113, 0.2);
  }

  .graph-card:hover {
    transform: scale(1.02);
    box-shadow: 0 15px 45px rgba(34, 213, 113, 0.35);
  }

  .graph-card-body {
    color: white;
  }

  .legend-dot {
    width: 12px;
    height: 12px;
    display: inline-block;
    border-radius: 50%;
  }

  .badge.bg-success-subtle {
    background-color: rgba(34, 213, 113, 0.15);
    color: #22D571;
  }
    .stat-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    .btn-api{
        color: white !important;
        background-color:rgb(19, 47, 75);
        border-color: #adb5bd ;
        border-radius: 5px;
        z-index: 1;
    }
    .btn-api:hover{
        background-color:rgb(131, 142, 153);
        border-color:rgb(131, 142, 153);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card-content {
        flex: 1;
    }
    
    .stat-card-value {
        font-size: 36px;
        font-weight: 700;
        color: #2D3748;
        margin-bottom: 5px;
        line-height: 1;
    }
    
    .stat-card-title {
        font-size: 14px;
        font-weight: 500;
        color: #718096;
        margin: 0;
    }
    
    .stat-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
    }
    
    .stat-card-icon i {
        font-size: 24px;
        color: white;
    }
    
    .stat-card-bg {
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        transform: translate(30%, -30%);
        z-index: 0;
        opacity: 0.1;
    }
    /* Base feature card styling */
    .feature-card {
        
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        padding: 25px;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        margin-bottom: 20px;
        
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        border-color: #d1d9e6;
    }
    
    /* Icon base styling */
    .feature-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background-color: rgba(34, 213, 113, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        position: relative;
    }
    
    .feature-icon i {
        font-size: 24px;
        color: #22D571;
    }
    
    /* Template - Teal */
    .template-icon {
        background-color: rgba(169, 240, 230, 0.1);
    }
    
    .template-icon i {
        color: #2dbcab;
    }
    
    /* Campaign - Orange */
    .campaign-icon {
        background-color: rgba(4, 0, 255, 0.1);
    }
    
    .campaign-icon i {
        color: #2b4d87;
    }
    
    /* Contact - Pink */
    .contact-icon {
        background-color: rgba(227, 79, 149, 0.1);
    }
    
    .contact-icon i {
        color: #E34F95;
    }
    
    /* Bot - Purple */
    .bot-icon {
        background-color: rgba(141, 93, 234, 0.1);
    }
    
    .bot-icon i {
        color: #8D5DEA;
    }
    
    .agent-icon i{
        color:  #5a6268;
    }
    .agent-icon{
        background-color:rgba(90, 98, 104, 0.12);
    }
    .feature-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .feature-title {
        font-size: 15px;
        font-weight: 600;
        color: #2D3748;
        margin-bottom: 10px;
    }
    
    .feature-description {
        font-size: 12px;
        color: #718096;
        line-height:1.5;   
        flex: 1;
    }
    /* Button styling */
    .feature-button {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background-color: #22D571;
        color: white;
        border-radius: 6px;
        font-weight: 500;
        font-size: 12px;
        transition: all 0.2s ease;
        text-decoration: none;
        align-self: flex-start;
        margin-top:-10px;
    }
    
    .feature-button:hover {
        background-color: #1eb863;
        color: white;
        text-decoration: none;
        transform: translateX(5px);
    }
    
    .template-button {
        background-color: #2dbcab;
    }
    
    .template-button:hover {
        background-color: rgb(37, 165, 150);
    }
    
    /* Campaign button - Orange */
    .campaign-button {
        background-color:rgb(31, 57, 104);
    }
    
    .campaign-button:hover {
        background-color: #172b4d;
    }
    
    /* Contact button - Pink */
    .contact-button {
        background-color: #E34F95;
    }
    
    .contact-button:hover {
        background-color: #d13884;
    }
    .bot-button {
        background-color: #8D5DEA;
    }
    
    .bot-button:hover {
        background-color: #7a4dd0;
    }
    
    /* Agent button - Gray */
    .agent-button {
        background-color: #6C757D;
    }
    
    .agent-button:hover {
        background-color: #5a6268;
    }
    
    .feature-button i {
        transition: transform 0.2s ease;
    }
    
    .feature-button:hover i {
        transform: translateX(3px);
    }
    /* Color variations */
    .stat-card-green .stat-card-icon {
        background-color: #22D571;
    }
    
    .stat-card-green .stat-card-bg {
        background-color: #22D571;
    }
    
    .stat-card-blue .stat-card-icon {
        background-color: #1771E6;
    }
    
    .stat-card-blue .stat-card-bg {
        background-color: #1771E6;
    }
    
    .stat-card-purple .stat-card-icon {
        background-color: #8D5DEA;
    }
    
    .stat-card-purple .stat-card-bg {
        background-color: #8D5DEA;
    }
    
    .stat-card-orange .stat-card-icon {
        background-color: #F19946;
    }
    
    .stat-card-orange .stat-card-bg {
        background-color: #F19946;
    }
    
    .stat-card-pink .stat-card-icon {
        background-color: #E34F95;
    }
    
    .stat-card-pink .stat-card-bg {
        background-color: #E34F95;
    }
    
    .stat-card-gray .stat-card-icon {
        background-color: #6C757D;
    }
    
    .stat-card-gray .stat-card-bg {
        background-color: #6C757D;
    }
    /* Action Link Styling */
    .action-link {
        display: block;
        margin-bottom: 12px;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1;
    }
    
    .action-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }
    
    .action-link-content {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        font-weight: 600;
        color: white;
        position: relative;
        z-index: 2;
    }
    
    .action-link-icon {
        margin-right: 10px;
        font-size: 18px;
    }
    
    /* Individual Link Styles */
    .template-link {
        background-color: #2dbcab;
    }
    
    .template-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, #9eefe6, #2dbcab);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }
    
    .template-link:hover::before {
        opacity: 1;
    }
    
    .campaign-link {
        background-color: #E34F95;
    }
    
    .campaign-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg,rgb(21, 24, 65),rgb(36, 14, 61));
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }
    
    .campaign-link:hover::before {
        opacity: 1;
    }
    
    .contact-link {
        background-color: #1765C9;
    }
    
    .contact-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #1765C9, #55BFF0);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }
    
    .contact-link:hover::before {
        opacity: 1;
    }
    
    .agent-link {
        background-color: #6C757D;
    }
    
    .agent-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #c4c4c4, #6C757D);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }
    
    .agent-link:hover::before {
        opacity: 1;
    }
    
    .chatbot-link {
        background-color: #0866FF;
    }
    
    .chatbot-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #b5d1ff, #0866FF);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }
    
    .chatbot-link:hover::before {
        opacity: 1;
    }
    

   
    
    /* Larger icons */
    .gradient-icon-lg-1, .gradient-icon-lg-2, .gradient-icon-lg-3, 
    .gradient-icon-lg-4, .gradient-icon-lg-5, .gradient-icon-lg-6, 
    .gradient-icon-lg-7, .gradient-icon-lg-8, .gradient-icon-lg-9, 
    .gradient-icon-lg-10 {
        font-size: 50px !important;
    }
    
    /* Feature card counter badge styling */
    .feature-counter {
        position: absolute;
        top: -10px;
        right: -10px;
        background: linear-gradient(135deg, #22D571, #1eb863);
        color: white;
        border-radius: 20px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        min-width: 22px;
        text-align: center;
        z-index: 2;
        border: 2px solid white;
        transition: all 0.3s ease;
    }
    
    .feature-card:hover .feature-counter {
        transform: scale(1.1);
    }
    
    /* Color variations for different feature types */
    .feature-icon .feature-counter {
        background: linear-gradient(135deg, #1771E6, #1461c7);
    }
    
    .template-icon .feature-counter {
        background: linear-gradient(135deg, #2dbcab, rgb(37, 165, 150));
    }
    
    .campaign-icon .feature-counter {
        background: linear-gradient(135deg,rgb(48, 38, 109),rgb(56, 8, 112));
    }
    
    .contact-icon .feature-counter {
        background: linear-gradient(135deg, #E34F95, #d13884);
    }
    
    .bot-icon .feature-counter {
        background: linear-gradient(135deg, #8D5DEA, #7a4dd0);
    }
    
    .agent-icon .feature-counter {
        background: linear-gradient(135deg, #6C757D, #5a6268);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .feature-counter {
            font-size: 10px;
            padding: 1px 5px;
            min-width: 20px;
            top: -8px;
            right: -8px;
            border-width: 1.5px;
        }
        
        .feature-icon {
            width: 45px;
            height: 45px;
        }
        
        .feature-icon i {
            font-size: 20px;
        }
    }
    
    @media (max-width: 576px) {
        .feature-counter {
            font-size: 9px;
            padding: 1px 4px;
            min-width: 18px;
            top: -6px;
            right: -6px;
            border-width: 1px;
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            margin-bottom: 15px;
        }
        
        .feature-icon i {
            font-size: 18px;
        }
        
        .feature-title {
            font-size: 16px;
        }
        
        .feature-description {
            font-size: 13px;
            margin: 0 !important;
        }
    }
    
    /* Add row gap to features section */
    .features-row {
        display: flex;
        flex-wrap: wrap;
        row-gap: 24px;
        margin-right: -12px;
        margin-left: -12px;
    }
    
    .features-row > [class*="col-"] {
        padding-right: 12px;
        padding-left: 12px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .features-row {
            row-gap: 20px;
        }
    }
    
    @media (max-width: 576px) {
        .features-row {
            row-gap: 16px;
        }
    }
</style>