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
    <title>Thank You - {{ $appName }}</title>
    <!-- Primary Meta Tags -->
    <meta name="title" content="Thank You - {{ $appName }}" />
    <meta name="description" content="Thank you for your interest in {{ $appName }}. We'll get back to you within 24 hours." />
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $appName }}" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:title" content="Thank You - {{ $appName }}" />
    <meta property="og:description" content="Thank you for your interest in {{ $appName }}. We'll get back to you within 24 hours." />
    <meta property="og:image" content="{{ getAppSettings('logo_image_url') }}" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="{{ url('/') }}" />
    <meta property="twitter:title" content="Thank You - {{ $appName }}" />
    <meta property="twitter:description" content="Thank you for your interest in {{ $appName }}. We'll get back to you within 24 hours." />
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
        .text-success {
            color: #22D571 !important;
        }
        .bg-success {
            background-color: #22D571 !important;
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

        /* Scroll animation effects */
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

        .thank-you-card {
            background: linear-gradient(135deg, rgba(34, 213, 113, 0.05), rgba(0, 188, 81, 0.02));
            border: 1px solid rgba(34, 213, 113, 0.1);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .thank-you-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #22D571, #00bc51);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(34, 213, 113, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(34, 213, 113, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(34, 213, 113, 0);
            }
        }

        .step-card {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(34, 213, 113, 0.1);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: rgba(34, 213, 113, 0.3);
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #22D571, #00bc51);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(34, 213, 113, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: rgba(34, 213, 113, 0.3);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(34, 213, 113, 0.1), rgba(0, 188, 81, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
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
                            <li class="nav-item"><a class="nav-link me-lg-3" href="/">{{ __tr('Home') }}</a></li>
                            <li class="nav-item"><a class="nav-link me-lg-3" href="/#pricing">{{ __tr('Pricing') }}</a></li>
                            <!-- <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('user.about.form') }}">{{ __tr('About Us') }}</a></li> -->
                            <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('user.contact.form') }}">{{ __tr('Contact') }}</a></li>

                            @if (!isLoggedIn())
                            <li class="nav-item"><a class="nav-link me-lg-3 btn text-success" style="border: 1px solid #198754;" href="{{ route('auth.register') }}">{{ __tr('Register') }}</a></li>
                            @if (getAppSettings('enable_vendor_registration') or getAppSettings('message_for_disabled_registration'))
                            <li class="nav-item"><a class="nav-link me-lg-3 btn btnn text-white" href="{{ route('auth.login') }}">{{ __tr('Login') }}</a></li>
                            @endif
                            @endif

                            @if (isLoggedIn())
                            <li class="nav-item"><a class="nav-link me-lg-3 btn btn-success text-white fw-bold " href="{{ route('central.console') }}">{{ __tr('Dashboard') }}</a></li>
                            @endif

                            @include('layouts.navbars.locale-menu')
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <!-- /Navigation -->

        <!-- Thank You Hero Section -->
        <section class="position-relative overflow-hidden py-7">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="thank-you-card p-5 text-center scroll-fade">
                            <div class="success-icon">
                                <i class="fas fa-check text-white" style="font-size: 40px;"></i>
                            </div>

                            <h1 class="display-5 fw-bold mb-3 text-success">Thank You for Your Interest!</h1>
                            <p class="lead text-muted mb-4">
                                We've received your inquiry and will get back to you within <strong class="text-success">24 hours</strong>
                                with pricing and purchase details.
                            </p>

                            <!-- Confirmation Badge -->
                            <div class="d-inline-block position-relative mb-4">
                                <span class="badge bg-success-subtle text-success  py-3 rounded-pill fs-6">
                                    Inquiry Received Successfully
                                </span>
                            </div>
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
        <!-- /Thank You Hero Section -->

        <!-- What's Next Section -->
        <section style="background-color: #fff; padding: 60px 0;">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-dark scroll-fade">What's <span class="text-success">Next?</span></h2>
                    <p class="text-muted scroll-fade">Here's what you can expect from us</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="step-card scroll-fade">
                            <div class="step-number">1</div>
                            <h5 class="fw-bold text-dark mb-3">Product Specialist Contact</h5>
                            <p class="text-muted">One of our product specialists will contact you to clarify your requirements and understand your business needs.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="step-card scroll-fade">
                            <div class="step-number">2</div>
                            <h5 class="fw-bold text-dark mb-3">Secure Payment & Documentation</h5>
                            <p class="text-muted">We will provide you with a secure payment link and all necessary product documentation for your review.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="step-card scroll-fade">
                            <div class="step-number">3</div>
                            <h5 class="fw-bold text-dark mb-3">Unlock Your Potential</h5>
                            <p class="text-muted">Get ready to unlock the potential of owning your own software source code and grow your business!</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /What's Next Section -->

        <!-- Meanwhile Section -->
        <section style="background-color: #f8f9fa; padding: 60px 0;">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-dark scroll-fade">Meanwhile, Feel Free To:</h2>
                    <p class="text-muted scroll-fade">Explore more while you wait for our response</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="action-card scroll-fade">
                            <div class="action-icon">
                                <i class="fas fa-question-circle gradient-icon-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-3">Explore our FAQs</h5>
                            <p class="text-muted mb-3">Find answers to commonly asked questions about our services and products.</p>
                            <a href="/#faqAccordion" class="btn btn-outline-success btn-sm">View FAQs</a>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="action-card scroll-fade">
                            <div class="action-icon">
                                <i class="fas fa-headset gradient-icon-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-3">Contact Support</h5>
                            <p class="text-muted mb-3">Have urgent queries? Our support team is here to help you immediately.</p>
                            <a href="{{ route('user.contact.form') }}" class="btn btn-outline-success btn-sm">Contact Support</a>
                        </div>
                    </div>

                    <!-- <div class="col-md-4">
                        <div class="action-card scroll-fade">
                            <div class="action-icon">
                                <i class="fas fa-share-alt gradient-icon-3"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-3">Follow Us</h5>
                            <p class="text-muted mb-3">Stay updated with our latest news, tips, and product updates on social media.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="#" class="btn btn-outline-success btn-sm"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="btn btn-outline-success btn-sm"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="btn btn-outline-success btn-sm"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </section>
        <!-- /Meanwhile Section -->

        <!-- Final Message Section -->
        <section style="background-color: #fff; padding: 40px 0;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center">
                        <div class="p-4 scroll-fade">
                            <h3 class="fw-bold text-success mb-3">Thank you for choosing us!</h3>
                            <p class="text-muted lead">
                                We're excited to help you grow your business with exclusive software ownership.
                                Your success is our priority, and we look forward to partnering with you on this journey.
                            </p>
                            <div class="mt-4">
                                <a href="/" class="btn btnn px-4 py-2">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Final Message Section -->

        <!-- Footer -->
        <footer class="bg-success text-white py-4 px-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} {!! __tr(' __appName__', ['__appName__' => $appName]) !!}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/" class="text-white-50 me-3">Home</a>
                    <!-- <a href="{{ route('user.about.form') }}" class="text-white me-3">About Us</a> -->
                    <a href="{{ route('user.contact.form') }}" class="text-white-50">Contact</a>
                </div>
            </div>
        </footer>
        <!-- /Footer -->

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

                // Add scroll-fade to all elements with scroll-fade class
                const scrollElements = section.querySelectorAll('.scroll-fade');
                scrollElements.forEach(element => {
                    element.classList.add(sectionClass);
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
        </script>
    </body>

</html>