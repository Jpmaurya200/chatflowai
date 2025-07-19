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
    <title>{{ __tr('About Us') }} - {{ $appName }}</title>
    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ __tr('About Us') }} - {{ $appName }}" />
    <meta name="description" content="{{ getAppSettings('description') }}" />
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $appName }}" />
    <meta property="og:url" content="{{ url('/about-us') }}" />
    <meta property="og:title" content="{{ __tr('About Us') }} - {{ $appName }}" />
    <meta property="og:description" content="{{ getAppSettings('description') }}" />
    <meta property="og:image" content="{{ getAppSettings('logo_image_url') }}" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="{{ url('/about-us') }}" />
    <meta property="twitter:title" content="{{ __tr('About Us') }} - {{ $appName }}" />
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
    </style>
</head>

<body class="lw-outer-home-page">
    {!! __yesset(['dist/css/app-home.css'], true) !!}
    <body id="page-top">
        <!-- Navigation-->
        <header class="lw-top-navbar">
            <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top border-bottom" id="mainNav">
                <div class="container px-5">
                    <!-- Logo -->
                    <!-- Brand -->
                    <a class="navbar-brand pt-0" href="/">
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
                            <!-- Home -->
                            <li class="nav-item"><a class="nav-link me-lg-3" href="/">{{ __tr('Home') }}</a>
                            </li>
                            <!-- /Home -->
                            <!-- About Us -->
                            <li class="nav-item"><a class="nav-link me-lg-3 active text-success fw-bold" href="{{ route('user.about.form') }}">{{ __tr('About Us') }}</a></li>
                            <!-- /About Us -->
                            <!-- Pricing -->
                            <li class="nav-item"><a class="nav-link me-lg-3" href="/#pricing">{{ __tr('Pricing') }}</a>
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

        <!-- Hero Section -->
        <section class="position-relative overflow-hidden py-7" style="background: linear-gradient(135deg, rgba(34, 213, 113, 0.05), rgba(0, 188, 81, 0.05));">
            <div class="container mt-5">
                <div class="row align-items-center">
                    <div class="col-lg-12 text-center">
                        <div class="mb-4">
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill mb-3"
                                  style="background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1));">
                                <i class="fas fa-info-circle me-2"></i>About Our Company
                            </span>
                        </div>
                        <h1 class="display-4 fw-bold mb-4">
                            About <span class="text-gradient"
                                      style="background: linear-gradient(135deg, #22D571, #00bc51); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                {!! __tr(' __appName__', ['__appName__' => $appName]) !!}
                            </span>
                        </h1>
                        <p class="lead text-muted mb-4 mx-auto" style="max-width: 600px;">
                            Revolutionizing business communication through innovative WhatsApp solutions, powered by official Meta technology.
                        </p>
                        <div class="mx-auto mb-4" style="width: 100px; height: 3px; background: linear-gradient(90deg, #22D571, #00bc51); border-radius: 2px;"></div>
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

        

        <!-- Main About Content -->
        <section style="background-color: #f8f9fa; padding: 80px 0;">
            <div class="container">
                <div class="row align-items-center mb-5">
                    <!-- Left Content -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="pe-lg-4">
                            <h2 class="fw-bold text-muted mb-4">Who We Are</h2>
                            <p class="text-muted mb-4" style="line-height: 1.8;">
                                We are a leading technology company specializing in WhatsApp Business solutions. Our platform is built on the official WhatsApp Cloud API, ensuring secure, reliable, and compliant communication for businesses of all sizes.
                            </p>
                            <p class="text-muted mb-4" style="line-height: 1.8;">
                                With years of experience in digital communication and a deep understanding of business needs, we've created a comprehensive solution that empowers companies to connect with their customers more effectively than ever before.
                            </p>

                            <!-- Key Points -->
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-icon me-3" style="background-color: rgba(34, 213, 113, 0.1); width: 50px; height: 50px;">
                                            <i class="fas fa-award gradient-icon-1 fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0">Meta Verified</h6>
                                            <small class="text-muted">Official Partner</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-icon me-3" style="background-color: rgba(34, 213, 113, 0.1); width: 50px; height: 50px;">
                                            <i class="fas fa-users gradient-icon-2 fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0">10K+ Users</h6>
                                            <small class="text-muted">Trusted Globally</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <div class="mt-4">
                                <a href="{{ getAppSettings('whatsapp_demo_link') }}" class="btn px-4 py-2 btnn" target="_blank">
                                    <i class="fas fa-rocket me-2"></i>Book Live Demo
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Image -->
                    <div class="col-lg-6">
                        <div class="position-relative">
                            <img class="img-fluid rounded-4 shadow-lg"
                                 src="{{ getAppSettings('hero_image')}}"
                                 alt="About Us"
                                 style="border-radius: 15px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);">

                            <!-- Floating Badge -->
                            <div class="position-absolute top-0 end-0 mt-3 me-3">
                                <span class="badge bg-success px-3 py-2 rounded-pill">
                                    <i class="fab fa-whatsapp me-2"></i>Official API
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section style="background-color: #f8f9fa; padding: 80px 0;">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-muted mb-3">Our Mission & Vision</h2>
                    <p class="text-muted fs-5 mb-4">Driving innovation in business communication</p>
                    <div class="mx-auto" style="width: 80px; height: 3px; background: linear-gradient(90deg, #22D571, #00bc51); border-radius: 2px;"></div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="h-100 p-4 rounded-4 features" style="background: rgba(34, 213, 113, 0.05); border: 1px solid rgba(34, 213, 113, 0.1);">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-icon me-3">
                                    <i class="fas fa-bullseye gradient-icon-3"></i>
                                </div>
                                <h4 class="fw-bold text-muted mb-0">Our Mission</h4>
                            </div>
                            <p class="text-muted mb-0" style="line-height: 1.7;">
                                To empower businesses with cutting-edge WhatsApp communication tools that enhance customer engagement, streamline operations, and drive growth through innovative technology solutions.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="h-100 p-4 rounded-4 features" style="background: rgba(34, 213, 113, 0.05); border: 1px solid rgba(34, 213, 113, 0.1);">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-icon me-3">
                                    <i class="fas fa-eye gradient-icon-4"></i>
                                </div>
                                <h4 class="fw-bold text-muted mb-0">Our Vision</h4>
                            </div>
                            <p class="text-muted mb-0" style="line-height: 1.7;">
                                To become the global leader in WhatsApp Business solutions, making professional communication accessible, efficient, and meaningful for businesses worldwide.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section style="background-color: #fff; padding: 80px 0;">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-muted mb-3">Why Choose Us?</h2>
                    <p class="text-muted fs-5 mb-4">What makes us different from the rest</p>
                    <div class="mx-auto" style="width: 80px; height: 3px; background: linear-gradient(90deg, #22D571, #00bc51); border-radius: 2px;"></div>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center p-4 h-100 features rounded-4" style="background: rgba(34, 213, 113, 0.05);">
                            <div class="rounded-icon mx-auto mb-3">
                                <i class="fas fa-shield-alt gradient-icon-5"></i>
                            </div>
                            <h5 class="fw-bold text-muted mb-3">Secure & Compliant</h5>
                            <p class="text-muted mb-0">Built with enterprise-grade security and full compliance with WhatsApp Business policies and data protection regulations.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-4 h-100 features rounded-4" style="background: rgba(34, 213, 113, 0.05);">
                            <div class="rounded-icon mx-auto mb-3">
                                <i class="fas fa-rocket gradient-icon-6"></i>
                            </div>
                            <h5 class="fw-bold text-muted mb-3">Easy to Use</h5>
                            <p class="text-muted mb-0">Intuitive interface designed for businesses of all sizes. Get started in minutes with our user-friendly dashboard and tools.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-4 h-100 features rounded-4" style="background: rgba(34, 213, 113, 0.05);">
                            <div class="rounded-icon mx-auto mb-3">
                                <i class="fas fa-headset gradient-icon-7"></i>
                            </div>
                            <h5 class="fw-bold text-muted mb-3">24/7 Support</h5>
                            <p class="text-muted mb-0">Dedicated customer support team available round the clock to help you maximize your WhatsApp Business potential.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Company Stats Section -->
        <section style="background-color: #f8f9fa; padding: 60px 0;">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-muted mb-3">Our Achievements</h2>
                    <p class="text-muted">Numbers that speak for our success</p>
                </div>
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6">
                        <div class="p-3">
                            <h2 class="fw-bold text-success mb-2">10K+</h2>
                            <p class="text-muted mb-0">Active Users</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3">
                            <h2 class="fw-bold text-success mb-2">50M+</h2>
                            <p class="text-muted mb-0">Messages Sent</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3">
                            <h2 class="fw-bold text-success mb-2">98%</h2>
                            <p class="text-muted mb-0">Satisfaction Rate</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3">
                            <h2 class="fw-bold text-success mb-2">24/7</h2>
                            <p class="text-muted mb-0">Support Available</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Technology Section -->
        <!-- <section style="background-color: #fff; padding: 80px 0;">
            <div class="container">
                <div class="row align-items-center"> -->
                    <!-- Image column with responsive adjustments -->
                    <!-- <div class="col-md-6 mb-4 mb-md-0">
                        <div class="d-flex justify-content-center flex-column align-items-center">
                            <div class="position-relative" style="width: 100%; max-width: 350px;">
                                <img  src="{{ getAppSettings('whatsapp_qr_image')}}" alt="QR Code" class="img-fluid shadow-lg"
                                     style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); width: 100%;">
                                <div class="position-absolute" style="bottom: -20px; right: -20px; width: 80px; height: 80px;
                                     background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1));
                                     border-radius: 50%; z-index: -1;"></div>
                            </div> -->

                            <!-- QR code instructions with responsive width -->
                            <!-- <div class="text-center mt-3 p-2" style="background-color: rgba(34, 213, 113, 0.1); border-radius: 8px; width: 100%; max-width: 350px;">
                                <p class="mb-0 fw-bold">
                                    <i class="fas fa-qrcode text-success me-2"></i>
                                    Scan the QR code to get our WhatsApp channel
                                </p>
                            </div>
                        </div>
                    </div> -->

                    <!-- Text column with responsive padding -->
                    <!-- <div class="col-md-6">
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
        </section> -->

        <!-- Call to Action Section -->
        <section style="background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1)); padding: 80px 0;">
            <div class="container text-center">
                <h2 class="fw-bold text-muted mb-3">Ready to Get Started?</h2>
                <p class="text-muted fs-5 mb-4">Join thousands of businesses already using our platform</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ route('auth.register') }}" class="btn btn-success px-4 py-2">
                        <i class="fas fa-user-plus me-2"></i>Get Started Free
                    </a>
                    <a href="{{ getAppSettings('whatsapp_demo_link') }}" class="btn px-4 py-2 btnn" target="_blank">
                        <i class="fas fa-rocket me-2"></i>Book Live Demo
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-success text-white py-4">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; {{ date('Y') }} {!! __tr(' __appName__', ['__appName__' => $appName]) !!}. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="/" class="text-white-50 me-3">Home</a>
                        <a href="{{ route('user.about.form') }}" class="text-white me-3">About Us</a>
                        <a href="{{ route('user.contact.form') }}" class="text-white-50">Contact</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scripts -->
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
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Add scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, observerOptions);

            // Observe elements for animation
            document.querySelectorAll('.scroll-fade, .section-title').forEach(el => {
                observer.observe(el);
            });
        </script>
    </body>
</html>