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
            color:rgb(8, 128, 54) !important; 
            transition: color 0.3s ease; 
        }
      
        .inner {
            padding: 20px;
            align-items: center;
            background-color: #ecf0ff;
            padding-top: 40px;
            position: relative;
            border-radius: 12px;
            height:100%
        }
        .card i {
            color:rgb(6, 139, 73) !important;
        }
        .go-corner {
            padding:20px;
            align-items: center;
            justify-content: center;
            position: absolute;
            
            height: 32px;
            overflow: hidden;
            display: flex;
            right: 0;
            top: 0;
            border-radius: 0 4px 0 32px;
            background-color: #00838d;
}

        .go-arrow {
            margin-right: -4px;
            margin-top: -4px;
           
            font-weight:400;
            color: white;
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
            color: #339699 !important; 
            transition: color 0.3s ease; 
        }
        .bg-lime{
            background-color:rgba(110, 210, 163, 0.42);
        }
        .accordion-button:not(.collapsed) {
        background-color:#6ed2a438 !important;
        color: black !important;
        outline : none;
    }
     .toggle-container {
            background-color: #6ed2a438; /* Light green background */
            padding: 5px;
            border-radius: 50px; /* Oval shape */
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }
        .btn-toggle {
            border: none;
            border-radius: 50px; /* Oval buttons */
            padding: 10px 20px;
            font-weight: bold;
            color: #333;
            background: transparent;
            transition: all 0.3s ease;
        }
        .btn-toggle.active {
            background-color: #6dc65c !important;
            color: white !important;
            border-radius: 50px;
        }
        .btnn{
            background: linear-gradient(
      15deg,
        rgb(0, 136, 68),
        rgb(32, 170, 129),
        rgb(13, 90, 67),
        rgb(26, 160, 131),
        rgb(8, 136, 97),
        rgb(17, 119, 68),
        rgb(63, 204, 169),
        rgb(9, 95, 62),
        rgb(0, 136, 95)
    )
            no-repeat;
            background-size: 300%;
            background-position: left center;
            transition: background 1s ease;
            color: white !important;
            animation: gradientMove 4s linear infinite;
  
}
.btnn:after {
  background-size: 320%;
  background-position: right center;
  transition: 1s ease;
}
@keyframes gradientMove {
    0% {
        background-position: right center;
    }
    100% {
        background-position: center left;
    }
    }
        
        /* Feature card hover transform effects */
        .features {
            transition: all 0.4s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .features:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
            border-color: rgba(51, 150, 153, 0.2);
        }
        
        .features .rounded-icon {
            transition: all 0.5s ease;
        }
        
        .features:hover .rounded-icon {
            transform: rotate(360deg);
        }
        
        .features h3 {
            transition: all 0.3s ease;
        }
        
        .features:hover h3 {
            transform: translateX(5px);
        }
        
        .features .text-dark {
            transition: all 0.3s ease;
        }
        
        .features:hover .text-dark {
            transform: translateY(-3px);
        }

        /* Submit button hover effect */
        .contact-submit-btn {
            background: linear-gradient(135deg, rgb(14, 133, 65), rgb(25, 135, 84));
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .contact-submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgb(25, 135, 84), rgb(14, 133, 65));
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .contact-submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(14, 133, 65, 0.3);
        }
        
        .contact-submit-btn:hover::before {
            left: 0;
        }
        
        .contact-submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(14, 133, 65, 0.3);
        }

        
       

        /* Improved scroll animation effects */
        .scroll-fade {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.9s cubic-bezier(0.215, 0.61, 0.355, 1), 
                        transform 0.9s cubic-bezier(0.215, 0.61, 0.355, 1);
            will-change: opacity, transform;
            backface-visibility: hidden;
            perspective: 1000px;
        }
        
        .scroll-fade.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Smoother section title animation */
        .section-title::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #22D571, #00bc51);
            bottom: -10px;
            left: 0;
            transition: width 0.6s cubic-bezier(0.215, 0.61, 0.355, 1);
        }
        
        .section-title.active::after {
            width: 80px;
        }
        
        /* Different delay classes for staggered animations */
        .delay-100 { transition-delay: 0.1s; }
        .delay-200 { transition-delay: 0.2s; }
        .delay-300 { transition-delay: 0.3s; }
        .delay-400 { transition-delay: 0.4s; }
        .delay-500 { transition-delay: 0.5s; }
        
        /* Add subtle animations for section titles */
        .section-title {
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #22D571, #00bc51);
            bottom: -10px;
            left: 0;
            transition: width 0.30s ease 0.3s;
        }
        
          #pricing .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }
    
     #pricing .card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }
    
    #pricing .price-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(0, 0, 0, 0.05);
        overflow: hidden;
        position: relative;
        z-index: 1;
    }
    
    #pricing .price-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 0;
        background: linear-gradient(180deg, rgba(34, 213, 113, 0.03), rgba(0, 188, 81, 0.01));
        transition: all 0.5s ease;
        z-index: -1;
    }
    
    #pricing .price-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
    }
    
    #pricing .price-card:hover::before {
        height: 100%;
    }
    
    #pricing .inner {
        padding: 20px;
        align-items: center;
        background-color: #ecf0ff;
        padding-top: 40px;
        position: relative;
        border-radius: 12px;
        height: 100%;
        transition: all 0.3s ease;
    }
    
    #pricing .price-card:hover .inner {
        transform: scale(1.02);
    }
    
    #pricing .go-corner {
        padding: 20px;
        align-items: center;
        justify-content: center;
        position: absolute;
        height: 32px;
        overflow: hidden;
        display: flex;
        right: 0;
        top: 0;
        border-radius: 0 4px 0 32px;
        background-color: #00838d;
        transition: all 0.3s ease;
    }
    
    #pricing .go-arrow {
        margin-right: -4px;
        margin-top: -4px;
        font-weight: 400;
        color: white;
    }
    
    #pricing .price-card .card-title {
        transition: all 0.3s ease;
    }
    
    #pricing .price-card:hover .card-title {
        transform: translateX(5px);
    }
    
    #pricing .amount {
        transition: all 0.3s ease;
    }
    
    #pricing .price-card:hover .amount {
        transform: scale(1.05);
        color: #339699 !important;
    }
    
    #pricing .btn {
        transition: all 0.3s ease;
    }
    
    #pricing .price-card:hover .btn {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(34, 213, 113, 0.2);
    }
    
    #pricing .fa-check-circle {
        transition: all 0.3s ease;
    }
    
    #pricing .price-card:hover .fa-check-circle {
        transform: scale(1.1);
    }
    
    #pricing .toggle-container {
        background-color: #6ed2a438;
        padding: 5px;
        border-radius: 50px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }
    
    #pricing .btn-toggle {
        border: none;
        border-radius: 50px;
        padding: 10px 20px;
        font-weight: bold;
        color: #333;
        background: transparent;
        transition: all 0.3s ease;
    }
    
    #pricing .btn-toggle.active {
        background-color: #198754;
        color: white;
        border-radius: 50px;
    }
    
    #pricing .btnn {
        background: linear-gradient(
            15deg,
            rgb(0, 136, 68),
            rgb(32, 170, 129),
            rgb(13, 90, 67),
            rgb(26, 160, 131),
            rgb(8, 136, 97),
            rgb(17, 119, 68),
            rgb(63, 204, 169),
            rgb(9, 95, 62),
            rgb(0, 136, 95)
        ) no-repeat;
        background-size: 300%;
        background-position: left center;
        transition: background 1s ease;
        color: white !important;
        animation: gradientMove 4s linear infinite;
    }
    
    #pricing .btnn:after {
        background-size: 320%;
        background-position: right center;
        transition: 1s ease;
    }
    
    @keyframes gradientMove {
        0% {
            background-position: right center;
        }
        100% {
            background-position: center left;
        }
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
                            
                               <!-- /pages -->
                            @if (!isLoggedIn())
                            <!-- Register -->
                            <li class="nav-item"><a class="nav-link me-lg-3 btn text-success" style="border: 1px solid #198754;" href="{{ route('auth.register') }}">{{ __tr('Register') }}</a></li>
                            <!-- /Register -->
                            @if (getAppSettings('enable_vendor_registration') or
                            getAppSettings('message_for_disabled_registration'))
                            <!-- Login -->
                            <li class="nav-item"><a class="nav-link me-lg-3 btn btnn text-white" href="{{ route('auth.login') }}">{{ __tr('Login') }}</a></li>
                            <!-- /Login -->
                            @endif
                            @endif
                            <!-- Dashboard -->
                            @if (isLoggedIn())
                            <li class="nav-item"><a class="nav-link me-lg-3 btn btn-success text-white fw-bold "
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
        <section class="position-relative overflow-hidden py-7">
            <div class="container mt-5">
                <div class="row align-items-center">
                    <!-- Left Content -->
                    <div class="col-lg-6 position-relative">
                        <div class="pe-lg-4">
                            <!-- Gradient Badge -->
                            <div class="d-inline-block position-relative mb-3">
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" 
                                      style="background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1));">
                                    <i class="fab fa-whatsapp me-2"></i>Official WhatsApp Business Solution
                                </span>
                        </div>

                            <!-- Main Heading with Gradient -->
                            <h1 class="display-5 fw-bold mb-3">
                                {!! __tr(' __appName__', ['__appName__' => $appName]) !!}
                                <span class="d-block text-gradient fs-2" 
                                      style="background: linear-gradient(135deg, #22D571, #00bc51); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                    Ultimate Solution for WhatsApp Marketing
                                </span>
                            </h1>

                            <!-- Description with Custom Background -->
                            <div class="p-3 rounded-3 mb-3" style="background: rgba(34, 213, 113, 0.05);">
                                <p class="mb-0 fs-6">
                            {{ __tr(
                                        'Our  __appName__  is a trusted and efficient platform designed to revolutionize the way businesses connect with their customers. With official verification by Meta our portal ensures secure reliable and compliant communication solutions.',
                                [
                                    '__appName__' => $appName,
                                ],
                            ) }}
                                </p>
                                
                        </div>

                            <!-- CTA Buttons -->
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <a href="{{ getAppSettings('whatsapp_demo_link') }}" class="btn px-4 py-2 btnn" target="_blank">
                                    <i class="fas fa-rocket me-2"></i>Book Live Demo
                                </a>
                                <a href="{{ getAppSettings('demo_video_link') }}" class="btn btn-outline-success px-4 py-2"  target="_blank">
                                    <i class="fas fa-play-circle me-2"></i>Watch Demo Video
                                </a>
                    </div>

                            <!-- Stats Section -->
                            <div class="row g-3">
                                <div class="col-4">
                                    <h4 class="fw-bold text-success mb-0">10K+</h4>
                                    <p class="text-muted small mb-0">Active Users</p>
                        </div>
                                <div class="col-4">
                                    <h4 class="fw-bold text-success mb-0">98%</h4>
                                    <p class="text-muted small mb-0">Satisfaction</p>
                    </div>
                                <div class="col-4">
                                    <h4 class="fw-bold text-success mb-0">24/7</h4>
                                    <p class="text-muted small mb-0">Support</p>
                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Image with Effects -->
                    <div class="col-lg-6 position-relative mt-4 mt-lg-0">
                        <div class="position-relative" style="max-width: 500px; margin: 0 auto;">
                            <!-- Main Image -->
                            <img class="img-fluid rounded-4 shadow-lg" 
                                 src="{{ getAppSettings('hero_image')}}"
                                 alt="Dashboard Preview"
                                 style="border-radius: 10px; box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.62);">
                            
                            <!-- Floating Elements -->
                          
                            
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Background Elements -->
            <div class="position-absolute top-0 end-0 mt-4 me-4 d-none d-lg-block">
                <div style="width: 200px; height: 200px; background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1)); border-radius: 50%; filter: blur(40px);"></div>
            </div>
            <div class="position-absolute bottom-0 start-0 mb-4 ms-4 d-none d-lg-block">
                <div style="width: 150px; height: 150px; background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1)); border-radius: 50%; filter: blur(30px);"></div>
            </div>
        </section>
        <!-- /masthead section -->
        <!-- Features Section-->
        <section id="features">
            <div class="container">
            <h2 class="text-center mb-5 fw-bold">Features of<span class="lw-highlight text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</span>
            </h2>
            <div class="row gap-5 justify-content-center mb-5 p-3">

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                            <i class="fab fa-facebook gradient-icon-8"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Embedded Signup') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Integrated Embedded Signup System that makes Customers Onboard easy.') }}</div>
                </div>

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                            <i class="fas fa-comments gradient-icon-9"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Integrated WhatsApp Chat') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Seamlessly connect with customers through Integrated WhatsApp Chat.') }}</div>
                </div>
                

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                            <i class="fas fa-qrcode gradient-icon-3"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('QR Code') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Quickly generate QR codes for your WhatsApp phone number with ease.') }}</div>
                </div>
            </div>
            

            <div class="row gap-5 justify-content-center mb-5 p-3">

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                        <i class="fas fa-robot gradient-icon-3"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Chat-Bot') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Engage customers 24/7 with intelligent chatbot responses effortlessly.') }}</div>
                </div>

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                            <i class="fa fa-layer-group gradient-icon-2"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Manage Templates') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Create and manage templates directly in the app without visiting Meta.') }}</div>
                </div>
                

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                            <i class="fas fas fa-random gradient-icon-5"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Flow Maker') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Build Bot conversions easily and effectively with Advance Flow Maker.') }}</div>
                </div>
            </div>

           <div class="row gap-5 justify-content-center mb-5 p-3">

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                           <i class="fas fa-cogs gradient-icon-7"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('API Integration') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('APIs enable smooth integration and data sharing between services.') }}</div>
                </div>

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                            <i class="fas fa-chart-line gradient-icon-1"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Live Analysis') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Get Realtime Analysis and Status of your Campaigns and Messages.s') }}</div>
                </div>
                

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                            <i class="fas fa-user-tie gradient-icon-6"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Assign Agents') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Assign Chats to Agents or your Team Members with Agents Feature.') }}</div>
                </div>
            </div>

            <div class="row gap-5 justify-content-center mb-5 p-3">

                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                        <i class="fa fa-rocket gradient-icon-10"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Campaigns') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Effortlessly manage your campaigns with Campaign Management Feature.') }}</div>
                </div>

                <!-- New Feature: AI Chatbot -->
                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                        
                        <i class="fas fa-brain gradient-icon-7"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('AI Chatbot') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Leverage advanced AI to automate customer interactions with intelligent responses.') }}</div>
                </div>

                <!-- New Feature: WhatsApp Report -->
                <div class="col-sm-10 col-lg-3 p-4 d-flex flex-column features rounded shadow bg-lime">
                    <!-- Icon and Heading on the same line -->
                    <div class="d-flex align-items-center">
                        <a class="text-decoration-none rounded-icon p-4">
                        <i class="fas fa-chart-bar gradient-icon-1"></i>
                        </a>
                        <h3 class="fw-bold text-muted ms-3 fs-5">{{ __tr('Chat Report') }}</h3>
                    </div>
                
                    <!-- Description starts exactly below the heading -->
                    <div class="text-dark mt-2">{{ __tr('Generate comprehensive analytics and reports on all your WhatsApp communications.') }}</div>
                </div>
            </div>
            </div>
        </section>
        <!-- About Us Section - Updated Design -->
        <section style="background-color: #f8f9fa; padding: 60px 0;">
            <div class="container">
            <div class="row align-items-center">
                    <!-- Image column with responsive adjustments -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="d-flex justify-content-center flex-column align-items-center">
                            <div class="position-relative" style="width: 100%; max-width: 350px;">
                                <img  src="{{ getAppSettings('whatsapp_qr_image')}}" alt="QR Code" class="img-fluid shadow-lg"  
                                     style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); width: 100%;">
                                <div class="position-absolute" style="bottom: -20px; right: -20px; width: 80px; height: 80px; 
                                     background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1)); 
                                     border-radius: 50%; z-index: -1;"></div>
                </div>
                            
                            <!-- QR code instructions with responsive width -->
                            <div class="text-center mt-3 p-2" style="background-color: rgba(34, 213, 113, 0.1); border-radius: 8px; width: 100%; max-width: 350px;">
                                <p class="mb-0 fw-bold">
                                    <i class="fas fa-qrcode text-success me-2"></i> 
                                    Scan the QR code to get our WhatsApp channel
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Text column with responsive padding -->
                <div class="col-md-6">
                        <div class="px-2 py-3 p-md-4">
                            <h2 class="fw-bold text-success mb-3">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</h2>
                            <h3 class="fw-bold text-muted mb-3">is based on</h3>
                            <h2 class="fw-bold text-muted mb-4">Official Whatsapp Cloud API <i class="fab fa-whatsapp text-success"></i></h2>
                            
                            <p class="text-muted mb-4" style="line-height: 1.6;">
                                Our platform leverages the official WhatsApp Cloud API to provide businesses with a powerful communication solution. 
                                This integration allows you to connect with customers seamlessly through WhatsApp, the world's most popular messaging app.
                            </p>
                            
                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <div class="d-flex align-items-center me-3 mb-2">
                                    <i class="fas fa-shield-alt text-success me-2"></i>
                                    <span>Meta Verified</span>
                                </div>
                                <div class="d-flex align-items-center me-3 mb-2">
                                    <i class="fas fa-lock text-success me-2"></i>
                                    <span>Secure & Compliant</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-globe text-success me-2"></i>
                                    <span>Global Reach</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- pricing block -->
     <section id="pricing" style="background-color: #fff">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="fw-bold"><span class="text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</span> User Plans</h1>
            <p class="text-muted">Choose the plan that works best for your business needs</p>
        </div>

        <!-- Pricing Toggle -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 col-md-10">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="toggle-container">
                        <button type="button" class="btn btn-toggle active" data-value="monthly" onclick="setActive(this)">Monthly</button>
                        <button type="button" class="btn btn-toggle" data-value="6_months" onclick="setActive(this)">6 Months</button>
                        <button type="button" class="btn btn-toggle" data-value="12_months" onclick="setActive(this)">12 Months</button>
                        <button type="button" class="btn btn-toggle" data-value="18_months" onclick="setActive(this)">18 Months</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="pricingTabContent">
            <!-- Monthly Plans -->
            <div class="tab-pane fade show active" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
                <div class="row justify-content-center">
                    @php
                    $freePlanDetails = getFreePlan();
                    $freePlanStructure = getConfigFreePlan();
                    $paidPlans = getPaidPlans();
                    $planStructure = getConfigPaidPlans();
                    @endphp

                    @if ($freePlanDetails['enabled'])
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                            Free
                                        </div>
                                    </div>
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-dark amount">${{ 4000/80 }}</span>
                                        <span class="text-black">/month</span>
                                        <p class="text-primary mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($freePlanStructure['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $freePlanDetails['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                           @if (isset($featureValue['type']) and ($featureValue['type'] == 'switch'))
                                               @if (isset($featureValue['limit']) and $featureValue['limit'])
                                                   <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                               @else
                                                   <i class="fas fa-times-circle fs-4 me-2 mt-1" style="color:rgb(255, 0, 0) !important;"></i>

                                               @endif
                                           @else
                                               <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                           @endif
                                           <span class="mt-2">
                                               @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                   {{ $configFeatureValue['description'] }}
                                               @else
                                                   <strong class="text-success">
                                                       @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                           {{ __tr('Unlimited') }}
                                                       @elseif(isset($featureValue['limit']))
                                                           {{ __tr($featureValue['limit']) }}
                                                       @endif
                                                   </strong>
                                                   {{ $configFeatureValue['description'] }}
                                                   {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                               @endif
                                           </span>
                                       </li>
                                       @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btn-outline-success w-100 rounded-lg py-2">Get Started Free</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach ($planStructure as $planKey => $plan)
                    @php
                    $planId = $plan['id'];
                    $features = $plan['features'];
                    $savedPlan = $paidPlans[$planKey] ?? null;
                    if (!$savedPlan || !isset($savedPlan['enabled']) || !$savedPlan['enabled']) {
                        continue;
                    }
                    $charges = $savedPlan['charges'] ?? [];
                    if (empty($charges) || !is_array($charges)) {
                        continue;
                    }
                    $monthlyCharge = null;
                    // First try exact key match
                    if (isset($charges['monthly']) && isset($charges['monthly']['enabled']) && $charges['monthly']['enabled']) {
                        $monthlyCharge = $charges['monthly']['charge'] ?? null;
                    }
                    // If not found, try matching by title
                    if ($monthlyCharge === null) {
                        foreach ($charges as $itemKey => $itemValue) {
                            if (!isset($itemValue['enabled']) || !$itemValue['enabled']) {
                                continue;
                            }
                            $chargeTitle = strtolower(Arr::get($plan['charges'], $itemKey.'.title', Arr::get($itemValue, 'title', '')));
                            if (strpos($chargeTitle, 'month') !== false && strpos($chargeTitle, '6') === false && strpos($chargeTitle, '18') === false && strpos($chargeTitle, '12') === false) {
                                $monthlyCharge = $itemValue['charge'] ?? null;
                                break;
                            }
                        }
                    }
                    if ($monthlyCharge === null || $monthlyCharge == 0) {
                        continue;
                    }
                    @endphp
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                        {{ $savedPlan['title'] ?? $plan['title'] }}
                                        </div>
                                    </div>
                                  
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-dark amount">${{$monthlyCharge/80 }}</span>
                                        <span class="text-black">/month</span>
                                        <p class="text-dark mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    
                                        <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($plan['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $savedPlan['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                           @if (isset($featureValue['type']) and ($featureValue['type'] == 'switch'))
                                               @if (isset($featureValue['limit']) and $featureValue['limit'])
                                                   <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                               @else
                                                   <i class="fas fa-times-circle fs-4 me-2 mt-1" style="color:rgb(255, 0, 0) !important;"></i>

                                               @endif
                                           @else
                                               <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                           @endif
                                           <span class="mt-2">
                                               @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                   {{ $configFeatureValue['description'] }}
                                               @else
                                                   <strong class="text-success">
                                                       @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                           {{ __tr('Unlimited') }}
                                                       @elseif(isset($featureValue['limit']))
                                                           {{ __tr($featureValue['limit']) }}
                                                       @endif
                                                   </strong>
                                                   {{ $configFeatureValue['description'] }}
                                                   {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                               @endif
                                           </span>
                                       </li>
                                       @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btnn w-100 rounded-lg py-2">Choose Plan</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 6 Months Plans -->
            <div class="tab-pane fade" id="6_months" role="tabpanel" aria-labelledby="6_months-tab">
                <div class="row justify-content-center">
                    @php
                    $freePlanDetails = getFreePlan();
                    $freePlanStructure = getConfigFreePlan();
                    $paidPlans = getPaidPlans();
                    $planStructure = getConfigPaidPlans();
                    @endphp

                    @if ($freePlanDetails['enabled'])
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                            Free
                                        </div>
                                    </div>
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-dark amount">{{ formatAmount(4000, true, true) }}</span>
                                        <span class="text-black">/month</span>
                                        <p class="text-primary mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($freePlanStructure['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $freePlanDetails['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                            <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                            <span>
                                                @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                    {{ $configFeatureValue['description'] }}
                                                @else
                                                    <strong class="text-success">
                                                        @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                            {{ __tr('Unlimited') }}
                                                        @elseif(isset($featureValue['limit']))
                                                            {{ __tr($featureValue['limit']) }}
                                                        @endif
                                                    </strong>
                                                    {{ $configFeatureValue['description'] }}
                                                    {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                                @endif
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btn-outline-success w-100 rounded-lg py-2">Get Started Free</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach ($planStructure as $planKey => $plan)
                    @php
                    $planId = $plan['id'];
                    $features = $plan['features'];
                    $savedPlan = $paidPlans[$planKey] ?? null;
                    if (!$savedPlan || !isset($savedPlan['enabled']) || !$savedPlan['enabled']) {
                        continue;
                    }
                    $charges = $savedPlan['charges'] ?? [];
                    if (empty($charges) || !is_array($charges)) {
                        continue;
                    }
                    $charge6Months = null;
                    // First try exact key match
                    if (isset($charges['6_months']) && isset($charges['6_months']['enabled']) && $charges['6_months']['enabled']) {
                        $charge6Months = $charges['6_months']['charge'] ?? null;
                    }
                    // If not found, try matching by title
                    if ($charge6Months === null) {
                        foreach ($charges as $itemKey => $itemValue) {
                            if (!isset($itemValue['enabled']) || !$itemValue['enabled']) {
                                continue;
                            }
                            $chargeTitle = strtolower(Arr::get($plan['charges'], $itemKey.'.title', Arr::get($itemValue, 'title', '')));
                            if (strpos($chargeTitle, '6 month') !== false || strpos($chargeTitle, '6months') !== false) {
                                $charge6Months = $itemValue['charge'] ?? null;
                                break;
                            }
                        }
                    }
                    if ($charge6Months === null || $charge6Months == 0) {
                        continue;
                    }
                    @endphp
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                        {{ $savedPlan['title'] ?? $plan['title'] }}
                                        </div>
                                    </div>
                                  
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-dark amount">${{$charge6Months/80 }}</span>
                                        <span class="text-black">/6 months</span>
                                                                                <p class="text-dark mt-2 mb-0 small ">Billing by Quarterly</p>

                                        <p class="text-dark mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($plan['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $savedPlan['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                            <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                            <span>
                                                @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                    {{ $configFeatureValue['description'] }}
                                                @else
                                                    <strong class="text-success">
                                                        @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                            {{ __tr('Unlimited') }}
                                                        @elseif(isset($featureValue['limit']))
                                                            {{ __tr($featureValue['limit']) }}
                                                        @endif
                                                    </strong>
                                                    {{ $configFeatureValue['description'] }}
                                                    {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                                @endif
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btnn w-100 rounded-lg py-2">Choose Plan</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 18 Months Plans -->
            <div class="tab-pane fade" id="18_months" role="tabpanel" aria-labelledby="18_months-tab">
                <div class="row justify-content-center">
                    @php
                    $freePlanDetails = getFreePlan();
                    $freePlanStructure = getConfigFreePlan();
                    $paidPlans = getPaidPlans();
                    $planStructure = getConfigPaidPlans();
                    @endphp

                    @if ($freePlanDetails['enabled'])
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                            Free
                                        </div>
                                    </div>
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-dark amount">${{ 4000/80 }}</span>
                                        <span class="text-black">/18 months</span>
                                        <p class="text-primary mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($freePlanStructure['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $freePlanDetails['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                           @if (isset($featureValue['type']) and ($featureValue['type'] == 'switch'))
                                               @if (isset($featureValue['limit']) and $featureValue['limit'])
                                                   <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                               @else
                                                   <i class="fas fa-times-circle fs-4 me-2 mt-1" style="color:rgb(255, 0, 0) !important;"></i>

                                               @endif
                                           @else
                                               <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                           @endif
                                           <span class="mt-2">
                                               @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                   {{ $configFeatureValue['description'] }}
                                               @else
                                                   <strong class="text-success">
                                                       @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                           {{ __tr('Unlimited') }}
                                                       @elseif(isset($featureValue['limit']))
                                                           {{ __tr($featureValue['limit']) }}
                                                       @endif
                                                   </strong>
                                                   {{ $configFeatureValue['description'] }}
                                                   {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                               @endif
                                           </span>
                                       </li>
                                       @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btn-outline-success w-100 rounded-lg py-2">Get Started Free</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach ($planStructure as $planKey => $plan)
                    @php
                    $planId = $plan['id'];
                    $features = $plan['features'];
                    $savedPlan = $paidPlans[$planKey] ?? null;
                    if (!$savedPlan || !isset($savedPlan['enabled']) || !$savedPlan['enabled']) {
                        continue;
                    }
                    $charges = $savedPlan['charges'] ?? [];
                    if (empty($charges) || !is_array($charges)) {
                        continue;
                    }
                    $charge18Months = null;
                    // First try exact key match
                    if (isset($charges['18_months']) && isset($charges['18_months']['enabled']) && $charges['18_months']['enabled']) {
                        $charge18Months = $charges['18_months']['charge'] ?? null;
                    }
                    // If not found, try matching by title
                    if ($charge18Months === null) {
                        foreach ($charges as $itemKey => $itemValue) {
                            if (!isset($itemValue['enabled']) || !$itemValue['enabled']) {
                                continue;
                            }
                            $chargeTitle = strtolower(Arr::get($plan['charges'], $itemKey.'.title', Arr::get($itemValue, 'title', '')));
                            if (strpos($chargeTitle, '18 month') !== false || strpos($chargeTitle, '18months') !== false) {
                                $charge18Months = $itemValue['charge'] ?? null;
                                break;
                            }
                        }
                    }
                    if ($charge18Months === null || $charge18Months == 0) {
                        continue;
                    }
                    @endphp
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                        {{ $savedPlan['title'] ?? $plan['title'] }}
                                        </div>
                                    </div>
                                  
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-dark amount">${{$charge18Months/80 }}</span>
                                        <span class="text-black">/18 months</span>
                                        <p class="text-dark mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    
                                        <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($plan['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $savedPlan['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                           @if (isset($featureValue['type']) and ($featureValue['type'] == 'switch'))
                                               @if (isset($featureValue['limit']) and $featureValue['limit'])
                                                   <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                               @else
                                                   <i class="fas fa-times-circle fs-4 me-2 mt-1" style="color:rgb(255, 0, 0) !important;"></i>

                                               @endif
                                           @else
                                               <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                           @endif
                                           <span class="mt-2">
                                               @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                   {{ $configFeatureValue['description'] }}
                                               @else
                                                   <strong class="text-success">
                                                       @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                           {{ __tr('Unlimited') }}
                                                       @elseif(isset($featureValue['limit']))
                                                           {{ __tr($featureValue['limit']) }}
                                                       @endif
                                                   </strong>
                                                   {{ $configFeatureValue['description'] }}
                                                   {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                               @endif
                                           </span>
                                       </li>
                                       @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btnn w-100 rounded-lg py-2">Choose Plan</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 12 Months Plans -->
            <div class="tab-pane fade" id="12_months" role="tabpanel" aria-labelledby="12_months-tab">
                <div class="row justify-content-center">
                    @if ($freePlanDetails['enabled'])
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                            Free
                                        </div>
                                    </div>
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-dark amount">{{ formatAmount(4000, true, true) }}</span>
                                        <span class="text-black">/12 months</span>
                                        <p class="text-primary mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($freePlanStructure['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $freePlanDetails['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                            <i class="fas fa-check-circle text-success fs-4 me-2 mt-1"></i>
                                            <span>
                                                @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                    {{ $configFeatureValue['description'] }}
                                                @else
                                                    <strong class="text-success">
                                                        @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                            {{ __tr('Unlimited') }}
                                                        @elseif(isset($featureValue['limit']))
                                                            {{ __tr($featureValue['limit']) }}
                                                        @endif
                                                    </strong>
                                                    {{ $configFeatureValue['description'] }}
                                                    {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                                @endif
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btn-outline-success w-100 rounded-lg py-2">Get Started Free</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach ($planStructure as $planKey => $plan)
                    @php
                    $planId = $plan['id'];
                    $features = $plan['features'];
                    $savedPlan = $paidPlans[$planKey] ?? null;
                    if (!$savedPlan || !isset($savedPlan['enabled']) || !$savedPlan['enabled']) {
                        continue;
                    }
                    $charges = $savedPlan['charges'] ?? [];
                    if (empty($charges) || !is_array($charges)) {
                        continue;
                    }
                    $charge12Months = null;
                    // First try exact key match
                    if (isset($charges['12_months']) && isset($charges['12_months']['enabled']) && $charges['12_months']['enabled']) {
                        $charge12Months = $charges['12_months']['charge'] ?? null;
                    }
                    // If not found, try matching by title
                    if ($charge12Months === null) {
                        foreach ($charges as $itemKey => $itemValue) {
                            if (!isset($itemValue['enabled']) || !$itemValue['enabled']) {
                                continue;
                            }
                            $chargeTitle = strtolower(Arr::get($plan['charges'], $itemKey.'.title', Arr::get($itemValue, 'title', '')));
                            if (strpos($chargeTitle, '12 month') !== false || strpos($chargeTitle, '12months') !== false) {
                                $charge12Months = $itemValue['charge'] ?? null;
                                break;
                            }
                        }
                    }
                    if ($charge12Months === null || $charge12Months == 0) {
                        continue;
                    }
                    @endphp
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-lg price-card" style="border-radius:1.25rem;">
                            <div class="card-body">
                                <div class="inner">
                                    <div class="go-corner" href="#">
                                        <div class="go-arrow">
                                        {{ $savedPlan['title'] ?? $plan['title'] }}
                                        </div>
                                    </div>
                                    <!-- <span class="badge bg-warning text-dark position-absolute top-0 end-0 mt-2 me-2">20% OFF</span> -->
                                    <div class="price-wrapper text-center my-4">
                                        <span class="h1 fw-bold text-black amount">${{ $charge12Months/80 }}</span>
                                        <span class="text-black">/12 months</span>
                                        <p class="text-black mt-2 mb-0 small fw-bold">+ WhatsApp Cloud Messaging Charges</p>
                                    </div>
                                    <ul class="list-unstyled mb-4" style="font-size:0.85em">
                                        @foreach ($plan['features'] as $featureKey => $featureValue)
                                        @php
                                        $configFeatureValue = $featureValue;
                                        $featureValue = $savedPlan['features'][$featureKey];
                                        @endphp
                                        <li class="mb-3 d-flex align-items-start">
                                            <i class="fas fa-check-circle text-white fs-4 me-2 mt-1"></i>
                                            <span class="text-black">
                                                @if (isset($featureValue['type']) and $featureValue['type'] == 'switch')
                                                    {{ $configFeatureValue['description'] }}
                                                @else
                                                    <strong>
                                                        @if (isset($featureValue['limit']) and $featureValue['limit'] < 0)
                                                            {{ __tr('Unlimited') }}
                                                        @elseif(isset($featureValue['limit']))
                                                            {{ __tr($featureValue['limit']) }}
                                                        @endif
                                                    </strong>
                                                    {{ $configFeatureValue['description'] }}
                                                    {{ $configFeatureValue['limit_duration_title'] ?? '' }}
                                                @endif
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        <a href="{{ route('auth.register') }}" class="btn btnn btn-light w-100 rounded-lg py-2">Choose Plan</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">Have questions about our pricing plans? <a href="{{ route('user.contact.form') }}" class="text-primary">Contact us</a></p>
        </div>
    </div>
</section>
        <!-- pricing block -->

         <!-- FAQ's section -->
         <section>
            <div class="container">
                <div class="text-center">
                    <h1 class="fw-bold"><span class=" text-success">{!! __tr(' __appName__', ['__appName__' => $appName]) !!}</span> FAQ's</h1>
                </div>
                <div class="accordion p-3" id="faqAccordion" style="border-radius: 10px;">
                    <div class="accordion-item" style="border-radius: 10px;">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                {{ __tr('1. How can I integrate WhatsApp with my business account?') }}
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-3" style="text-align: justify; border-radius: 10px;">
                                {{ __tr('To integrate WhatsApp with your business account, simply sign up for our app, connect your WhatsApp Business Account, and follow the step-by-step guide provided in the dashboard. The integration works directly with the WhatsApp Cloud API.') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item" style="border-radius: 10px;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                {{ __tr('2. Can I manage multiple phone numbers on the same WhatsApp Business Account?') }}
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-3" style="text-align: justify; border-radius: 10px;">
                                {{ __tr('Yes, our app supports managing multiple phone numbers for the same WhatsApp Business Account, enabling you to handle communication from different channels in a single interface.') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item" style="border-radius: 10px;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                {{ __tr('3. How do I create and manage message templates?') }}
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-3" style="text-align: justify; border-radius: 10px;">
                                {{ __tr('You can easily create and manage WhatsApp message templates directly within our app, without needing to visit Meta platform. Simply navigate to the templates section, create your template, and use it in your campaigns.') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item" style="border-radius: 10px;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                {{ __tr('4. What kind of data analytics does the app provide?') }}
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-3" style="text-align: justify; border-radius: 10px;">
                                {{ __tr('The app provides real-time analytics on message delivery, open rates, response times, and user engagement. You can track the performance of your WhatsApp campaigns and optimize them based on these insights.') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item" style="border-radius: 10px;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                {{ __tr('5. Is my data secure when using this app?') }}
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-3" style="text-align: justify; border-radius: 10px;">
                                {{ __tr('Yes, we prioritize security and use industry-standard encryption to protect your data. All communications through the app are fully compliant with WhatsApp privacy and security policies, ensuring your business and customer data is safe.') }}
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </section>
      
        <!-- Add this section after the FAQ section and before the footer -->
        <section style="background-color: #fff;" class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    <!-- Left side - Contact heading and text -->
                    <div class="col-md-5">
                        <h1 class="fw-bold mb-4">Contact <span class="text-success">Us</span></h1>
                        <p class="text-muted mb-4">Have questions about our WhatsApp Business solutions? We're here to help! Reach out to us for personalized support, demo requests, or any inquiries about our services.</p>
                        
                        <!-- Additional contact info -->
                        <div class="contact-info mt-4">
                        @if (getAppSettings('contact_details'))
                <div class="lw-ws-pre-line" style="width:188px;">
                    {!! getAppSettings('contact_details') !!}
                </div>
                    <hr>
                        <ul class="d-flex list-unstyled">
                        @include('layouts.navbars.navs.pages-menu-partial')
                        </ul>
                @endif
                        </div>
                    </div>

                    <!-- Right side - Contact form -->
                    <div class="col-md-6 offset-md-1">
                        <div class="bg-white rounded-3 shadow-sm">
                            <form class="p-4">
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="floatingName" placeholder=" " 
                                        style="background-color: #F2F4F7; border: none; height: 60px;">
                                    <label for="floatingName" class="text-muted">Name</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="tel" class="form-control" id="floatingPhone" placeholder=" " 
                                        style="background-color: #F2F4F7; border: none; height: 60px;">
                                    <label for="floatingPhone" class="text-muted">Phone Number</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <textarea class="form-control" id="floatingComment" placeholder=" " 
                                        style="background-color: #F2F4F7; border: none; height: 150px;"></textarea>
                                    <label for="floatingComment" class="text-muted">Comment</label>
                                </div>
                                <button type="submit" class="btn btn-success w-100 p-3 contact-submit-btn">
                                    <strong>Submit</strong>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
                        <div> <!-- pages -->
                         @include('layouts.navbars.navs.pages-menu-partial')    
                            <!-- /pages --></div>
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
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scroll behavior to the entire page
            document.documentElement.style.scrollBehavior = 'smooth';
            
            // Group elements by section for simultaneous animation
            const sections = document.querySelectorAll('section');
            
            sections.forEach((section, sectionIndex) => {
                // Create a unique class for this section's elements
                const sectionClass = `section-group-${sectionIndex}`;
                
                // Add scroll-fade to section titles
                
                
                // Add scroll-fade to feature cards
                const features = section.querySelectorAll('.features');
                features.forEach(feature => {
                    feature.classList.add('scroll-fade', sectionClass);
                });
                
                // Add scroll-fade to price cards
                const priceCards = section.querySelectorAll('.price-card');
                priceCards.forEach(card => {
                    card.classList.add('scroll-fade', sectionClass);
                });
                
                // Add scroll-fade to FAQ items
                const faqItems = section.querySelectorAll('.accordion-item');
                faqItems.forEach(item => {
                    item.classList.add('scroll-fade', sectionClass);
                });
                
                // Add scroll-fade to contact form elements
                const formElements = section.querySelectorAll('.form-floating, .contact-submit-btn');
                formElements.forEach(element => {
                    element.classList.add('scroll-fade', sectionClass);
                });
                
                // Add scroll-fade to paragraphs and other content
                const contentElements = section.querySelectorAll('p:not(.scroll-fade), .row > .col-md-5, .row > .col-md-6');
                contentElements.forEach(element => {
                    if (!element.closest('.features') && !element.closest('.price-card')) {
                        element.classList.add('scroll-fade', sectionClass);
                    }
                });
            });
            
            // Improved function to check if section is in viewport with threshold
            function isSectionInViewport(elements) {
                if (elements.length === 0) return false;
                
                // Check if any element in the section is in viewport
                for (let i = 0; i < elements.length; i++) {
                    const rect = elements[i].getBoundingClientRect();
                    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
                    // Adjust threshold for smoother triggering
                    const threshold = windowHeight * 0.75;
                    
                    if (rect.top <= threshold && rect.bottom >= 0) {
                        return true;
                    }
                }
                return false;
            }
            
            // Throttle function to limit how often scroll handler runs
            function throttle(func, limit) {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            }
            
            // Improved function to handle scroll animation with throttling
            const handleScrollAnimation = throttle(function() {
                // Get all section groups
                const sectionCount = sections.length;
                
                for (let i = 0; i < sectionCount; i++) {
                    const sectionElements = document.querySelectorAll(`.section-group-${i}`);
                    
                    // If any element in this section group is visible, activate all elements in the group
                    if (isSectionInViewport(sectionElements)) {
                        sectionElements.forEach(element => {
                            element.classList.add('active');
                        });
                    }
                }
            }, 100); // Throttle to run at most every 100ms
            
            // Initial check on page load
            setTimeout(handleScrollAnimation, 300); // Slight delay for initial load
            
            // Check on scroll with passive listener for better performance
            window.addEventListener('scroll', handleScrollAnimation, { passive: true });
            
            // Also check on resize
            window.addEventListener('resize', handleScrollAnimation, { passive: true });
        });
        
         function setActive(button) {
        const buttons = document.querySelectorAll('.btn-toggle');
        buttons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        const value = button.getAttribute('data-value');
        // Hide all tabs
        document.getElementById('monthly').classList.remove('show', 'active');
        document.getElementById('6_months').classList.remove('show', 'active');
        document.getElementById('12_months').classList.remove('show', 'active');
        document.getElementById('18_months').classList.remove('show', 'active');
        
        // Show selected tab
        if (value === 'monthly') {
            document.getElementById('monthly').classList.add('show', 'active');
        } else if (value === '6_months') {
            document.getElementById('6_months').classList.add('show', 'active');
        } else if (value === '12_months') {
            document.getElementById('12_months').classList.add('show', 'active');
        } else if (value === '18_months') {
            document.getElementById('18_months').classList.add('show', 'active');
        }
    }
        </script>
    </body>

</html>