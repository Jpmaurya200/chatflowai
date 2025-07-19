@extends('layouts.app')
@section('content')
@include('layouts.headers.cards')
@push('head')
<?= __yesset(['dist/css/dashboard.css'],true)?>
@endpush
<style>
    /* Modern Card Styling */
    .card {
        background: white;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        background: linear-gradient( rgba(34, 213, 112, 0.08), rgba(34, 213, 112, 0));
        transition: 0.30s ease-in-out;
    }
    .card2{
        background: white;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .card2:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        
    }

    /* Stats Card Styling */
    .stats-card {
        padding: 25px;
        position: relative;
        z-index: 1;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(45deg, rgba(34, 213, 113, 0.03), rgba(34, 213, 113, 0.08));
        border-radius: 50%;
        transform: translate(40%, -40%);
        z-index: -1;
        transition: all 0.3s ease;
    }

    .stats-card:hover::before {
        transform: translate(30%, -30%) scale(1.1);
    }

    .stats-icon {
        font-size: 28px !important;
        margin-bottom: 15px;
        background: linear-gradient(135deg, #22D571, #14A84E);
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
        display: inline-block; 
        filter: drop-shadow(0 2px 4px rgba(34, 213, 113, 0.2));
        transition: all 0.3s ease;
    }

    .stats-card:hover .stats-icon {
        transform: scale(1.1);
    }

    .stats-value {
        font-size: 42px;
        font-weight: 700;
        color: #2D3748;
        margin: 10px 0;
        line-height: 1;
        transition: all 0.3s ease;
    }

    .stats-card:hover .stats-value {
        color: #22D571;
    }

    .stats-title {
        font-size: 16px;
        font-weight: 600;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    /* Table Card Styling */
    .table-card {
        margin-top: 30px;
    }

    .table-card .card-header {
        background: transparent;
        padding: 25px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .table-card .card-title {
        font-size: 20px;
        font-weight: 700;
        color: #2D3748;
        margin: 0;
    }

    .table-card .btn-primary {
        background: linear-gradient(135deg, #22D571, #14A84E);
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .table-card .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(34, 213, 113, 0.2);
    }

    /* Table Styling */
    .table {
        margin: 0;
    }

    .table th {
        font-weight: 600;
        color: #718096; 
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
        padding: 16px 25px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    th{
        background-color:rgb(11, 119, 83) !important;
    }

    .table td {
        padding: 16px 25px;
        color: #2D3748;
        font-weight: 500;
        vertical-align: middle;
    }

    .table tr {
        transition: all 0.2s ease;
    }

    .table tr:hover {
        background-color: rgba(34, 213, 113, 0.02);
    }

    .table a {
        color: #22D571;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .table a:hover {
        color: #14A84E;
        text-decoration: none;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        background: rgba(34, 213, 113, 0.1);
        color: #22D571;
        transition: all 0.2s ease;
    }

    .status-badge:hover {
        background: rgba(34, 213, 113, 0.2);
    }

   

    .card-trigger-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(34, 213, 113, 0.2);
    }

    .card-content {
        padding-right: 40px;
    }

    /* Fresh Users Card Styling */
    .fresh-users-card {
        border-radius: 16px;
        background: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .fresh-users-header {
        padding: 25px 30px;
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .fresh-users-title {
        font-size: 20px;
        font-weight: 700;
        color: #2D3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .fresh-users-title i {
        font-size: 24px;
        background: linear-gradient(135deg, #22D571, #14A84E);
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
    }

    .fresh-users-btn {
        background: linear-gradient(135deg, #22D571, #14A84E);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 10px 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 14px;
    }

    .fresh-users-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(34, 213, 113, 0.2);
        color: white;
    }

    .fresh-users-table {
        margin: 0;
    }

    .fresh-users-table th {
        font-weight: 600;
        color: #718096;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
        padding: 20px 30px;
        background: #f8f9ff;
        border: none;
    }

    .fresh-users-table td {
        padding: 20px 30px;
        color: #2D3748;
        font-weight: 500;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        vertical-align: middle;
    }

    .fresh-users-table tr {
        transition: all 0.2s ease;
    }

    .fresh-users-table tr:hover {
        background-color: rgba(34, 213, 113, 0.02);
    }

    .fresh-users-table a {
        color: #2D3748;
        font-weight: 600;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .fresh-users-table a:hover {
        color: #22D571;
        text-decoration: none;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22D571, #14A84E);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        background: rgba(34, 213, 113, 0.1);
        color: #22D571;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge i {
        font-size: 12px;
    }

    /* Card Styling */
    .dashboard-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        padding: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .card-content {
        display: flex;
        flex-direction: column;
    }

    .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #2D3748;
        margin-bottom: 10px;
    }

    .card-value {
        font-size: 32px;
        font-weight: 700;
        color: #1A202C;
        margin-bottom: 5px;
    }

    .card-change {
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        color: #48BB78;
    }

    .card-change.negative {
        color: #F56565;
    }

    .card-icon {
        font-size: 40px;
        color: rgba(5, 185, 95, 0.69);
    }

    /* Modern Card Styling based on reference image */
    .stat-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
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
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if (getAppSettings('enable_queue_jobs_for_campaigns'))
                @if (!getAppSettings('queue_setup_done_at'))
                    <div class="alert alert-danger"><i class="fa fa-info-circle"></i> {{  __tr('Queue worker setup is required') }}</div>
                @endif
            @else
                @if (!getAppSettings('cron_setup_done_at'))
                    <div class="alert alert-danger"><i class="fa fa-info-circle"></i> {{  __tr('Cron job setup is required') }}</div>
                @endif
            @endif
            @if (!getAppSettings('pusher_app_id'))
                <div class="alert alert-danger"><i class="fa fa-info-circle"></i> {{  __tr('Pusher configuration is required') }}</div>
            @endif
        </div>
    </div>
    
    <div class="">

        <div class="col  mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header bg-transparent">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="mb-0 font-weight-bold text-uppercase" style="color: #0861F2;">{{  __tr('User Onboarding Analytics') }}</h2>
                            <h4 class="text-uppercase ls-1 mb-1 font-weight-bold">{{  __tr('Annual') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                <div class="col-12 mb-5 mb-xl-5">
            <div x-cloak x-data="{totalVendors:{{ $totalVendors }},totalActiveVendors:{{ $totalActiveVendors }},totalCampaigns:{{ $totalCampaigns }},messagesInQueue:{{ $messagesInQueue }},totalContacts:{{ $totalContacts }},totalMessagesProcessed:{{ $totalMessagesProcessed }} }">
                        <div class="row">
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-blue">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-value" x-text="__Utils.formatAsLocaleNumber(totalVendors)"></div>
                                <div class="stat-card-title">{{ __tr('Total Users') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-green">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-value" x-text="__Utils.formatAsLocaleNumber(totalActiveVendors)"></div>
                                <div class="stat-card-title">{{ __tr('Active Users') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-purple">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-address-book"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-value" x-text="__Utils.formatAsLocaleNumber(totalContacts)"></div>
                                <div class="stat-card-title">{{ __tr('Contacts') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-orange">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-rocket"></i>
                </div>
                            <div class="stat-card-content">
                                <div class="stat-card-value" x-text="__Utils.formatAsLocaleNumber(totalCampaigns)"></div>
                                <div class="stat-card-title">{{ __tr('Overall Campaigns') }}</div>
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
                                <div class="stat-card-value" x-text="__Utils.formatAsLocaleNumber(messagesInQueue)"></div>
                                <div class="stat-card-title">{{ __tr('Messages In-Process') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mt-3">
                        <div class="stat-card stat-card-gray">
                            <div class="stat-card-bg"></div>
                            <div class="stat-card-icon">
                                <i class="fas fa-clipboard-check"></i>
                                    </div>
                            <div class="stat-card-content">
                                <div class="stat-card-value" x-text="__Utils.formatAsLocaleNumber(totalMessagesProcessed)"></div>
                                <div class="stat-card-title">{{ __tr('Processed Messages') }}</div>
                            </div>
                        </div>
                    </div>

                </div>
                </div>
         </div>
                    <!-- Chart -->
                    <div class="chart">
                        <canvas id="lwNewVendorRegistrationGraph" class="chart-canvas" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
        
    <div class="row mt-2">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card fresh-users-card shadow mb-5">
                <div class="fresh-users-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="fresh-users-title">
                                <i class="fas fa-users-alt"></i>
                                {{  __tr('Fresh Users') }}
                            </h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('central.vendors') }}" class="fresh-users-btn">
                                <i class="fas fa-list-ul mr-2"></i>{{  __tr('Get List') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table fresh-users-table">
                        <thead>
                            <tr>
                                <th scope="col">{{  __tr('User') }}</th>
                                <th scope="col">{{  __tr('Registered Date') }}</th>
                                <th scope="col">{{  __tr('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($newVendors as $newVendor)
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.dashboard',['vendorIdOrUid'=>$newVendor->_uid])}}">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($newVendor->title, 0, 1)) }}
                                        </div>
                                        <span class="text-dark">{{ $newVendor->title }}</span>
                                    </a>
                                </td>
                                <td>{{ formatDate($newVendor->created_at) }}</td>
                                <td>
                                    <span class="status-badge">
                                        <i class="fas fa-check-circle"></i>
                                        {{ configItem("status_codes." . $newVendor->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.footers.auth')
</div>
@endsection
@push('js')
<?= __yesset(['dist/js/dashboard.js'],true)?>
@endpush
@push('appScripts')
<script>
        (function($) {
        'use strict';
    var ctx = document.getElementById("lwNewVendorRegistrationGraph").getContext("2d");
    
    // Create gradient fill for the chart
    var gradientFill = ctx.createLinearGradient(0, 0, 0, 290);
    gradientFill.addColorStop(0, "rgba(34, 213, 113, 0.5)");
    gradientFill.addColorStop(1, "rgba(34, 213, 113, 0.05)");
    
    // Get the months and data from your existing data
    var months = @json(array_pluck($vendorRegistrations, 'month_name'));
    var userData = @json(array_pluck($vendorRegistrations, 'vendors_count'));
    
    new Chart(ctx, {
        type: "line",
      data: {
            labels: months,
        datasets: [{
          label: "{{ __tr('New users') }}",
          tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: "#22D571",
                borderColor: "#22D571",
                backgroundColor: gradientFill,
          fill: true,
                data: userData,
                pointHoverRadius: 6,
                pointHoverBorderWidth: 2
            }]
      },
      options: {
            locale: window.appConfig.locale,
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
                },
                tooltip: {
                    backgroundColor: "rgba(255, 255, 255, 0.8)",
                    titleColor: "#333",
                    bodyColor: "#666",
                    borderColor: "rgba(0, 0, 0, 0.1)",
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 5,
                    usePointStyle: true
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
                        borderDash: [5, 5],
                        color: "rgba(0, 0, 0, 0.05)"
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#000'
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#000',
              padding: 20
            }
          },
        },
      },
    });
})(jQuery);

document.addEventListener('DOMContentLoaded', function() {
    // Prevent card click when clicking the trigger button
    const cardTriggers = document.querySelectorAll('.card-trigger-btn');
    cardTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const href = this.closest('.card').getAttribute('onclick').match(/'([^']+)'/)[1];
            window.location.href = href;
        });
    });

    // Add hover effect for cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
  </script>
@endpush