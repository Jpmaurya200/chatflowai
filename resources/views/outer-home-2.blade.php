<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $CURRENT_LOCALE_DIRECTION ?? '' }}">
@php
$appName = getAppSettings('name');
@endphp
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> {{ (isset($title) and $title) ? ' - ' . $title : __tr('Welcome') }} - {{ $appName }}</title>
    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $appName }}" />
    <meta name="description" content="{{ getAppSettings('description') }}" />
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $appName }}" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:title" content="{{ $appName }}" />
    <meta property="og:description" content="{{ getAppSettings('description') }}" />
    <meta property="og:image" content="{{ getAppSettings('logo_image_url') }}" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="{{ url('/') }}" />
    <meta property="twitter:title" content="{{ $appName }}" />
    <meta property="twitter:description" content="{{ getAppSettings('description') }}" />
    <meta property="twitter:image" content="{{ getAppSettings('logo_image_url') }}" />

    <!-- FAVICON -->
    <link href="{{ getAppSettings('favicon_image_url') }}" rel="icon">
    {!! __yesset([
    'static-assets/packages/fontawesome/css/all.css',
    'static-assets/packages/bootstrap-icons/font/bootstrap-icons.css',
    ]) !!}
    <!-- Google fonts-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap"
        rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- /Google fonts-->
    <style>
        section {
            background-color: #F2F4F7;
        }
        .text-primary {
            color: #232fcc !important;
        }   
        .bg-primary {
            background-color: #232fcc !important;
        }
        .card:hover h5 {
            color: #0866FF !important; 
            transition: color 0.3s ease; 
        }
        .card i {
            color: #0866FF !important;
        }
        .gradient-icon-1 {
            font-size: 30px !important;
            background: linear-gradient(135deg, #41C6B5, #1771E6); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .rounded-icon {
            background-color: #fff;
            width: 60px;
            height: 60px; 
            border-radius: 50%; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
        }
        .gradient-icon-2 {
            font-size: 30px !important;
            background: linear-gradient(90deg,  #9eefe6, #2dbcab);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-3 {
            font-size: 30px !important;
            background: linear-gradient(135deg, #D32E9A, #8D5DEA);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-4 {
            font-size: 30px !important;
            background: linear-gradient(45deg, #F19946, #E34F95);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-5 {
            font-size: 30px !important;
            background: linear-gradient(135deg, #1765C9, #55BFF0);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-6 {
            font-size: 30px !important;
            background: linear-gradient(135deg, #707d8e, #021C42);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-7 {
            font-size: 30px !important;
            background: linear-gradient(135deg, #c4c4c4, #6C757D);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-8 {
            font-size: 30px !important;
            background: linear-gradient(135deg, #b5d1ff, #0866FF);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-9 {
            font-size: 30px !important;
            background: linear-gradient(135deg, #22D571, #21d3c7);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .gradient-icon-10 {
            font-size: 30px !important;
            background: linear-gradient(45deg, #A136E6, #5eb4ff);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            display: inline-block; 
        }
        .features:hover h3,
        .features:hover h5 {
            color: #0866FF !important; 
            transition: color 0.3s ease; 
        }
    </style>

<body class="lw-outer-home-page">
    {!! __yesset(['dist/css/app-home.css'], true) !!}
    <body id="page-top">
        <!-- Navigation-->
        <header class="lw-top-navbar">
            <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top border-bottom" id="mainNav">
                <div class="container px-5">
                    <!-- Logo -->
                    <!-- Brand -->
                    <a class="navbar-brand pt-0" href="">
                        <img src="{{ getAppSettings('logo_image_url') }}" class="navbar-brand-img" alt="">
                    </a>
                    <!-- Logo -->
                    <button class="navbar-toggler lw-btn-block-mobile" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false"
                        aria-label="{{ __tr('Toggle navigation') }}">
                        {{ __tr('Menu') }}
                        <i class="bi-list"></i>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarResponsive">
                        <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0 text-center">
                            <!-- Menu -->
                            <!-- Features -->
                            <li class="nav-item"><a class="nav-link me-lg-3" href="/">{{ __tr('Home') }}</a>
                            </li>
                            <!-- /Features -->
                            <!-- Pricing -->
                            <li class="nav-item"><a class="nav-link me-lg-3" href="#pricing">{{ __tr('Pricing') }}</a>
                            </li>
                            <!-- /Pricing -->
                            <!-- Contact -->
                            <li class="nav-item"><a class="nav-link me-lg-3"
                                    href="{{ route('user.contact.form') }}">{{ __tr('Contact') }}</a></li>
                            <!-- /Contact -->
                             <!-- pages -->
                            @include('layouts.navbars.navs.pages-menu-partial')    
                            <!-- /pages -->
                               <!-- /pages -->
                            @if (!isLoggedIn())
                            <!-- Register -->
                            <li class="nav-item"><a class="nav-link me-lg-3 btn text-success" style="border: 1px solid #198754;" href="{{ route('auth.register') }}">{{ __tr('Register') }}</a></li>
                            <!-- /Register -->
                            @if (getAppSettings('enable_vendor_registration') or
                            getAppSettings('message_for_disabled_registration'))
                            <!-- Login -->
                            <li class="nav-item"><a class="nav-link me-lg-3 btn btn-success text-white" href="{{ route('auth.login') }}">{{ __tr('Login') }}</a></li>
                            <!-- /Login -->
                            @endif
                            @endif
                            <!-- Dashboard -->
                            @if (isLoggedIn())
                            <li class="nav-item"><a class="nav-link me-lg-3 btn btn-primary text-white fw-bold "
                                    href="{{ route('central.console') }}">{{ __tr('Dashboard') }}</a></li>
                            @endif
                            <!-- /Dashboard -->
                            @include('layouts.navbars.locale-menu')
                            <!-- /Menu -->
                        </ul>

                    </div>
                </div>
            </nav>
        </header>
        <!-- /Navigation -->
        <!-- masthead section -->
        <section class="">
            <div class="container mt-5">
                <div class="row align-items-center">
                    <div class="col-sm-12 col-md-12 col-lg-6">
                        <div class="fs-1 fw-bold text-success">
                            {!! __tr(' __appName__ ', ['__appName__' => $appName]) !!}
                        </div>
                        <div class="fs-2">
                            {{ __tr('Ultimate Solution for Whatsapp Marketting ') }}
                        </div>
                        <!-- description -->
                        <hr>
                        <div class="description text-dark">
                            {{ __tr(
                                'Our  __appName__  is a trusted and efficient platform designed to revolutionize the way businesses connect with their customers. With official verification by Meta our portal ensures secure reliable and compliant communication solutions tailored to meet modern marketing needs.',
                                [
                                    '__appName__' => $appName,
                                ],
                            ) }}
                        </div>
                        <!-- buttons -->
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-6 text-center mt-4 mt-lg-0">
                        <div class="lw-image-fluid"><img class="img-fluid w-75" style="border-radius: 10px; box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.5);" src="{{ asset('imgs/outer-home/masthead.png') }}" alt="..." />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /masthead section -->
        <!-- Features Section-->
        <section id="features">
            <div class="container">
            <h2 class="text-center fw-bold">Features of<span class="lw-highlight text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</span>
            </h2>
            <div class="row">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fab fa-facebook gradient-icon-8"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Embedded Signup') }}</h3>
                        <div class="text-dark">{{ __tr('Integrated Embedded Signup System that makes Customers Onboard easy') }}</div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end  ">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features   rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fas fa-comments gradient-icon-9"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Integrated WhatsApp Chat') }}</h3>
                        <div class="text-dark">{{ __tr('Seamlessly connect with customers through Integrated WhatsApp Chat.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fas fa-qrcode gradient-icon-3"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('QR Code') }}</h3>
                        <div class="text-dark">{{ __tr('Quickly generate QR codes for your WhatsApp phone number with ease.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end  ">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features   rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fas fa-brain gradient-icon-4"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Chat-Bot') }}</h3>
                        <div class="text-dark">{{ __tr('Engage customers 24/7 with intelligent chatbot responses effortlessly.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fa fa-layer-group gradient-icon-2"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Manage Templates') }}</h3>
                        <div class="text-dark">{{ __tr('Create and manage templates directly in the app without visiting Meta.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end  ">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features   rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fas fas fa-random gradient-icon-5"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Flow Maker') }}</h3>
                        <div class="text-dark">{{ __tr('Build Bot conversions easily and effectively with Advance Flow Maker.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fas fa-cogs gradient-icon-7"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('API Integration') }}</h3>
                        <div class="text-dark">{{ __tr('APIs enable smooth integration and data sharing between services.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end  ">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features   rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fas fa-chart-line gradient-icon-1"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Live Analysis') }}</h3>
                        <div class="text-dark">{{ __tr('Get Realtime Analysis and Status of your Campaigns and Messages.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fas fa-user-tie gradient-icon-6"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Assign Agents') }}</h3>
                        <div class="text-dark">{{ __tr('Assign Chats to Agents or your Team Members with Agents Feature.') }}</div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end  ">
                <div class="  col-sm-10 col-lg-7 p-4 d-flex features   rounded shadow">
                    <a class="text-decoration-none rounded-icon"><i class="fa fa-rocket gradient-icon-10"></i></a>
                    <div class="ms-4">
                        <h3 class="fw-bold text-muted">{{ __tr('Campaigns') }}</h3>
                        <div class="text-dark">{{ __tr('Effortlessly manage your campaigns with Campaign Management Feature') }}</div>
                    </div>
                </div>
            </div>
            </div>
        </section>
        <!-- About Us Section -->
        <section style="background-color: #fff;">
            <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center">
                    <h1 class="fw-bold text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</h1>
                    <h2 class="fw-bold text-muted">is based on</h2>
                    <h1 class="fw-bold text-muted">Official Whatsapp Cloud API <i class="fab fa-whatsapp text-success"></i></h1>
                </div>
                <div class="col-md-6">
                    <div class="description text-muted" style="text-align: justify;"><span class="fw-bold text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!} </span> integrates with the <span class="fw-bold text-success">Official WhatsApp Cloud API</span>, providing a streamlined solution for businesses to enhance customer communication. This powerful integration allows for real-time messaging, automated responses, and easy management of WhatsApp interactions. With full access to the WhatsApp Cloud API, businesses can send and receive messages, share multimedia, and maintain secure conversations with customers worldwide. The panel simplifies the process of connecting to the WhatsApp platform, offering features like automated workflows, data analytics, and template management. Experience the flexibility and efficiency of <span class="fw-bold text-success">WhatsApp business communications</span>, all within one unified interface.</div>
                </div>
                </div>
            </div>
        </section>
        <!-- FAQ's section -->
        <section>
            <div class="container">
                <div class="text-center">
                    <h1 class="fw-bold"><span class=" text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</span> FAQ's</h1>
                </div>
                <div class="mt-3">
                    <ul class="row justify-content-center list-unstyled">
                        <li class="features col-lg-9 rounded shadow mt-2 p-3" >
                            <h5 class="fw-bold text-muted">{{ __tr('1. How can I integrate WhatsApp with my business account?') }}</h3>
                            <h6 class="text-muted" style="text-align: justify;">{{ __tr('To integrate WhatsApp with your business account, simply sign up for our app, connect your WhatsApp Business Account, and follow the step-by-step guide provided in the dashboard. The integration works directly with the WhatsApp Cloud API.') }}</h5>
                        </li>
                        <li class="features col-lg-9 rounded shadow mt-4 p-3" >
                            <h5 class="fw-bold text-muted">{{ __tr('2. Can I manage multiple phone numbers on the same WhatsApp Business Account?') }}</h3>
                            <h6 class="text-muted" style="text-align: justify;">{{ __tr('Yes, our app supports managing multiple phone numbers for the same WhatsApp Business Account, enabling you to handle communication from different channels in a single interface.') }}</h5>
                        </li>
                        <li class="features col-lg-9 rounded shadow mt-4 p-3" >
                            <h5 class="fw-bold text-muted">{{ __tr('3. How do I create and manage message templates?') }}</h3>
                            <h6 class="text-muted" style="text-align: justify;">{{ __tr('You can easily create and manage WhatsApp message templates directly within our app, without needing to visit Meta platform. Simply navigate to the templates section, create your template, and use it in your campaigns.') }}</h5>
                        </li>
                        <li class="features col-lg-9 rounded shadow mt-4 p-3" >
                            <h5 class="fw-bold text-muted">{{ __tr('4. What kind of data analytics does the app provide?') }}</h3>
                            <h6 class="text-muted" style="text-align: justify;">{{ __tr('The app provides real-time analytics on message delivery, open rates, response times, and user engagement. You can track the performance of your WhatsApp campaigns and optimize them based on these insights.') }}</h5>
                        </li>
                        <li class="features col-lg-9 rounded shadow mt-4 p-3" >
                            <h5 class="fw-bold text-muted">{{ __tr('5. Is my data secure when using this app?') }}</h3>
                            <h6 class="text-muted" style="text-align: justify;">{{ __tr('Yes, we prioritize security and use industry-standard encryption to protect your data. All communications through the app are fully compliant with WhatsApp privacy and security policies, ensuring your business and customer data is safe.') }}</h5>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- pricing block -->
        <section id="pricing" style="background-color: #fff">
            <div class="container">
                <div class="text-center">
                    <h1 class="fw-bold"><span class=" text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</span> User Plans</h1>
                </div>
                {{-- free plan --}}
                <div class="row justify-content-center">
                    @php
                    $freePlanDetails = getFreePlan();
                    $freePlanStructure = getConfigFreePlan();
                    $paidPlans = getPaidPlans();
                    $planStructure = getConfigPaidPlans();
                    @endphp

                    @if ($freePlanDetails['enabled'])
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-5 mb-4">
                        <div class="card border-1 p-4 h-100 shadow" style="background-color: #F2F4F7">
                            <!-- title -->
                            <h4 class="mb-4 mt-2 fw-bold text-muted">{{ $freePlanDetails['title'] }}</h4>
                            <!-- title -->
                            <!--  pricing -->
                            <div class="price mb-1 fw-bold text-dark"><span class="h1">{{ formatAmount(0, true, true) }}</span>/yearly</div>
                            <br>
                            <!--  pricing -->
                            <div><a class="text-primary fw-bold text-decoration-none" target="_blank"
                                    href="https://business.whatsapp.com/products/platform-pricing">{{ __tr('+ WhatsApp Cloud Messaging Charges') }}</a></div>
                            <hr class="mt-4">
                            {{-- features --}}
                            <ul class="list-unstyled">
                                @foreach ($freePlanStructure['features'] as $featureKey => $featureValue)
                                @php
                                $configFeatureValue = $featureValue;
                                $featureValue = $freePlanDetails['features'][$featureKey];
                                @endphp
                                <li>
                                    <i class="fas fa-check"></i>&nbsp;
                                    @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                    @if (isset($featureValue['limit']) and $featureValue['limit'])
                                    @else
                                    @endif
                                    {{ $configFeatureValue['description'] }}
                                    @else
                                    <strong class="text-success">
                                        @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                            {{ __tr('Unlimited') }} @elseif(isset($featureValue['limit']))
                                            {{ __tr($featureValue['limit']) }} @endif </strong>
                                            {{ $configFeatureValue['description'] }}
                                            {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                            @endif
                                </li>
                                @endforeach
                            </ul>
                            {{-- /features --}}
                            <div class="pricing-price"></div>
                        </div>
                    </div>
                    <!-- /free plan-->
                    @endif
                    {{-- paid plan --}}
                    @foreach ($planStructure as $planKey => $plan)
                    @php
                    $planId = $plan['id'];
                    $features = $plan['features'];
                    $savedPlan = $paidPlans[$planKey];
                    $charges = $savedPlan['charges'];
                    if (!$savedPlan['enabled']) {
                    continue;
                    }
                    @endphp
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-5 mb-4">
                        <div class="card border-1 p-4 h-100 shadow" style="background-color: #F2F4F7">
                            <!-- title -->
                            <h4 class="mb-4 fw-bold text-muted mt-2">{{ $savedPlan['title'] ?? $plan['title'] }}
                            </h4>
                            <!-- /title -->
                            <!--  pricing -->
                             @foreach ($charges as $itemKey => $itemValue)
                                @php
                                    if(!$itemValue['enabled']) {
                                        continue;
                                    }
                                @endphp
                                <div class="price mb-1 fw-bold text-dark"><span class="h1">{{ formatAmount($itemValue['charge'], true, true) }}</span>/{{ Arr::get($plan['charges'][$itemKey], 'title', '') }}</div>
                                <br>
                            @endforeach
                            <!--  /pricing -->
                            <div><a class="text-primary fw-bold text-decoration-none" target="_blank"
                                    href="https://business.whatsapp.com/products/platform-pricing">{{ __tr('+ WhatsApp Cloud Messaging Charges') }}</a></div>
                            <hr class="mt-4">
                            {{-- features --}}
                            <ul class="list-unstyled">
                                @foreach ($plan['features'] as $featureKey => $featureValue)
                                @php
                                $configFeatureValue = $featureValue;
                                $featureValue = $savedPlan['features'][$featureKey];
                                @endphp
                                <li>
                                    <i class="fas fa-check"></i>&nbsp;
                                    @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                    @if (isset($featureValue['limit']) and $featureValue['limit'])
                                    @else
                                    @endif
                                    {{ $configFeatureValue['description'] }}
                                    @else
                                    <strong class="text-success">
                                        @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                            {{ __tr('Unlimited') }} @elseif(isset($featureValue['limit']))
                                            {{ __tr($featureValue['limit']) }} @endif </strong>
                                            {{ $configFeatureValue['description'] }}
                                            {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                            @endif
                                </li>
                                @endforeach
                            </ul>
                            {{-- /features --}}
                            <div class="pricing-price"></div>
                        </div>
                    </div>
                    @endforeach
                    {{-- /paid plan --}}
                </div>
        </section>
        <!-- pricing block -->
        <!-- footer -->
        <footer class="footer-section text-center d-none">
            <div class="container">
                <div class="footer-content p-5 pb-0">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
                            <div class="footer-widget">
                                <div class="footer-logo">
                                    <a href="{{ url('/') }}"><img src="{{ getAppSettings('logo_image_url') }}"
                                            alt="{{ getAppSettings('name') }}" class="img-fluid" alt="logo"></a>
                                </div>
                                <div class="footer-social-icon my-4">
                                    <h3 class="ubuntu-bold">{{ __tr('Follow us') }}</h3>
                                    <a href="#"><i class="fab fa-facebook"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-google-plus-g"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                            <div class="footer-widget">
                                <div class="footer-widget-heading">
                                    <h3 class="ubuntu-bold"> {{ __tr('Useful Links') }}</h3>
                                    <div class="border-bottom w-25 m-auto"></div>
                                </div>
                                <ul>
                                    <li><a href="#"> {{ __tr('Home') }}</a></li>
                                    <li><a href="#"> {{ __tr('About') }}</a></li>
                                    <li><a href="#"> {{ __tr('Careers') }}</a></li>
                                    <li><a href="#"> {{ __tr('Our Services') }}</a></li>
                                    <li><a href="#"> {{ __tr('Privacy policy') }}</a></li>
                                    <li><a href="#"> {{ __tr('terms and conditions') }}</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                            <div class="footer-widget">
                                <div class="footer-widget-heading">
                                    <h3 class="ubuntu-bold"> {{ __tr('Contact') }}</h3>
                                    <div class="border-bottom w-25 m-auto"></div>
                                </div>
                                <ul>
                                    @if (getAppSettings('contact_details'))
                                    <div class="lw-ws-pre-line">
                                        <li> {!! getAppSettings('contact_details') !!}</li>
                                    </div>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="copyright-text text-center pb-3">
                    <p> &copy;{{ getAppSettings('name') }} {{ __tr(date('Y')) }}.
                        {{ __tr('All Rights Reserved.') }}</p>
                </div>
            </div>
        </footer>
        <footer class="text-center py-3 bg-success">
            <div class="container px-5">
                <div class="text-white mt-3 small">
                    <div class="mb-2">&copy; {{ getAppSettings('name') }} {{ __tr(date('Y')) }}.
                        {{ __tr('All Rights Reserved.') }}</div>
                </div>
            </div>
        </footer>
        <!-- footer -->
        <script>
        (function() {
            'use strict';
            window.appConfig = {
                debug: "{{ config('app.debug') }}",
                csrf_token: "{{ csrf_token() }}",
                locale: '{{ app()->getLocale() }}',
            }
        })();
        </script>
        {!! __yesset([
        'dist/js/common-vendorlibs.js',
        'dist/js/vendorlibs.js',
        'dist/packages/bootstrap/js/bootstrap.bundle.min.js',
        'dist/js/jsware.js',
        ]) !!}
        {!! getAppSettings('page_footer_code_all') !!}
        @if (isLoggedIn())
        {!! getAppSettings('page_footer_code_logged_user_only') !!}
        @endif
    </body>

</html>