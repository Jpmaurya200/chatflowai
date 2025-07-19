@extends('layouts.app', ['class' => 'main-content-has-bg'])
@section('content')
@include('layouts.headers.guest')
<style>
   
    
    .gradient-icon-1 {
        background: linear-gradient(135deg, #339699, #78c48f); 
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
        display: inline-block; 
    }
   
    .form-container{
        background: rgba(255, 255, 255, 0.9);
        font-family: 'Nunito', sans-serif;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        width: 100%;
       
    }
    .form-container .form-icon{
        text-align: center;
        width: 100px;
        height: 100px;
        margin: 0 auto 15px;
        border-radius: 50%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
    }
    .form-container .title{
        color:rgb(0, 0, 0);
        font-size: 25px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-align: center;
        margin: 0 0 20px;
    }
    .form-container .form-horizontal .form-group{ 
        margin: 0 0 25px 0; 
    }
    .form-container .form-horizontal .form-group input{
        width: 100%;
        font-size: 1rem;
        padding: 15px 20px;
        background-color: #f5f5f5;
        color: #333;
        border-radius: 10px;
        outline: none;
        transition: all 0.3s ease;
        border: 2px solid #ddd;
}
  
    .form-container .form-horizontal .form-group label{
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .form-container .form-horizontal .form-control{
        color: #333;
        background: #ecf0f3;
        font-size: 15px;
        height: 50px;
        padding: 20px;
        letter-spacing: 1px;
        border: none;
        border-radius: 25px;
        display: inline-block;
        transition: all 0.3s ease 0s;
    }
    .form-container .form-horizontal .form-control:focus{
        border-color: #58bc82;
        box-shadow: 0 0 10px rgba(88, 188, 130, 0.4);
    }
    .form-container .form-horizontal .form-control::placeholder{
        color: #808080;
        font-size: 14px;
    }
    .form-container .form-horizontal .btn{
        color: #000;
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase;
        width: 100%;
        padding: 12px 15px 11px;
        border-radius: 10px;
        box-shadow: 6px 6px 6px #cbced1, -6px -6px 6px #fff;
        border: none;
        transition: all 0.5s ease 0s;
    }
    .form-container .form-horizontal .btn:hover,
    .form-container .form-horizontal .btn:focus{
        color: #fff;
        letter-spacing: 3px;
        box-shadow: none;
        outline: none;
    }
    .btn{
        color: #000;
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase;
        width: 100%;
        padding: 12px 15px 11px;
        border-radius: 10px;
        box-shadow: 6px 6px 6px #cbced1, -6px -6px 6px #fff;
        border: none;
        transition: all 0.5s ease 0s;
    }
    .form-bg{
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      background: linear-gradient(to bottom right, #339699, #297386);
 

    canvas {
      display: block;
      width: 100%;
      height: 100vh;
      overflow:hidden;
    }
    }
</style>
<div class="form-bg py-5" > <canvas id="networkCanvas" class="position-absolute"></canvas>

    <div class="container ">
        <div class="row justify-content-center">
            <div class="col-md-6 col-md-offset-6">
                <div class="form-container">
                <div class="form-icon">
                    <img src="{{ getAppSettings('favicon_image_url') }}" alt="Logo" style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
                </div>
                    <h3 class="title">Login</h3>
                @if (isDemo())
                    <button onclick="document.getElementById('lwLoginEmail').value='demosuperadmin';document.getElementById('lwLoginPassword').value='demopass12';" class="btn btn-sm btn-danger">{{  __tr('Demo Super Admin Login') }}</button>
                    <button onclick="document.getElementById('lwLoginEmail').value='testcompany';document.getElementById('lwLoginPassword').value='demopass12';" class="btn btn-sm btn-danger">{{  __tr('Demo Company Login') }}</button>
                @endif
                    <x-lw.form id="lwLoginForm" data-secured="true" :action="route('auth.login.process')">
                        <div class="form-horizontal">
                            <div class="form-group{{ $errors->has('email') ? ' has-danger' : '' }}">
                                <label>email</label>
                                <input id="lwLoginEmail" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="{{ __tr(' Username or Email or Mobile Number') }}" type="text" name="email" value="" required autofocus autocomplete="email">
                                <h5><span class="text-light">{{__tr("Mobile number should be with country code without 0 or +")}}</span></h5>
                            </div>
                            <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
                                <label>password</label>
                                <input id="lwLoginPassword" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="{{ __tr('Password') }}" type="password" value="" required autocomplete="current-password">
                            </div>
                            <div class="custom-control custom-control-alternative custom-checkbox">
                                <input class="custom-control-input" name="remember" id="customCheckLogin" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="customCheckLogin">
                                    <span class="text-light">{{ __tr('Remember me') }}</span>
                                </label>
                                @if (Route::has('auth.password.request'))
                                <a href="{{ route('auth.password.request') }}" class="text-light float-right">
                                    <small>{{ __tr('Forgot password?') }}</small>
                                </a>
                                @endif
                            </div>
                            <div class="text-center">
                                <center>
                                    <button type="submit" class="btn btn-success my-4 btn-lg btn-block mb-5" style=" background: linear-gradient(135deg, #41C6B5, #1771E6); box-shadow:none;"><strong>{{ __tr('Login') }}</strong></button>
                                </center>
                            </div>
                    </x-lw.form>
                </div>
                @if(getAppSettings('allow_google_login'))
                <a href="<?= route('login.google') ?>" class="btn btn-google btn-user btn-block">
                    <i class="fab fa-google fa-fw"></i> <?= __tr('Continue with Google')  ?>
                </a>
                @endif
                @if(getAppSettings('allow_facebook_login'))
                <a href="<?= route('login.facebook') ?>" class="btn btn-facebook btn-user btn-block">
                    <i class="fab fa-facebook-f fa-fw"></i> <?= __tr('Continue with Facebook')  ?>
                </a>
                @endif
                @if(getAppSettings('enable_vendor_registration'))
                <!-- social login links -->
                <div class="mb-3 mt-5">
                    {{  __tr('If you don\'t have an Account yet? Create One! Its Free!!') }}
                </div>
                <center>
                    <a href="{{ route('auth.register') }}" class="btn btn-success my-4 btn-lg btn-block mb-5" style=" box-shadow:none; background: linear-gradient(135deg, #22D571, #00bc51);">
                        <strong>{{ __tr('Create New Account') }}</strong>
                    </a>
                </center>
                @elseif(getAppSettings('message_for_disabled_registration'))
                <div class="mb-3 mt-5">
                    {{  __tr('Want to create New Account?') }}
                </div>
                <a href="{{ route('auth.register') }}" class="btn btn-lg btn-warning">
                    <small>{{ __tr('More Info') }}</small>
                </a>
                @endif
            </div>
        </div>
    </div>
    
  <script>
      const canvas = document.getElementById('networkCanvas');
    const ctx = canvas.getContext('2d');

    // Set canvas to full window size
    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Particles class with enhanced animation
    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2 + 1;
            this.speedX = (Math.random() - 0.5) * 2;
            this.speedY = (Math.random() - 0.5) * 2;
            this.brightness = Math.random() * 50 + 50;
            // Add wave motion parameters
            this.angle = Math.random() * 360;
            this.angleSpeed = Math.random() * 0.5 + 0.1;
            this.waveAmplitude = Math.random() * 20 + 10;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.closePath();
            // Create a gradient effect for each particle
            const gradient = ctx.createRadialGradient(
                this.x, this.y, 0,
                this.x, this.y, this.size
            );
            gradient.addColorStop(0, `rgba(0, ${this.brightness + 150}, ${this.brightness + 300}, 1)`);
            gradient.addColorStop(1, `rgba(0, ${this.brightness + 150}, ${this.brightness + 300}, 0)`);
            ctx.fillStyle = gradient;
            ctx.fill();
        }

        update() {
            // Wave motion
            this.angle += this.angleSpeed;
            this.x += Math.sin(this.angle * Math.PI / 180) * 0.5;
            this.y += Math.cos(this.angle * Math.PI / 180) * 0.5;

            // Boundary check with smooth transition
            if (this.x < 0) this.x = canvas.width;
            if (this.x > canvas.width) this.x = 0;
            if (this.y < 0) this.y = canvas.height;
            if (this.y > canvas.height) this.y = 0;

            // Pulse size
            this.size = (Math.sin(this.angle * 0.05) + 2) * 1.5;
        }
    }

    // Initialize particles
    const numberOfParticles = 100;
    let particlesArray = [];

    function init() {
        particlesArray = [];
        for (let i = 0; i < numberOfParticles; i++) {
            particlesArray.push(new Particle());
        }
    }

    // Connect particles with enhanced lines
    function connect() {
        for (let a = 0; a < particlesArray.length; a++) {
            for (let b = a; b < particlesArray.length; b++) {
                const dx = particlesArray[a].x - particlesArray[b].x;
                const dy = particlesArray[a].y - particlesArray[b].y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 150) {
                    const opacity = (150 - distance) / 150;
                    const gradient = ctx.createLinearGradient(
                        particlesArray[a].x, particlesArray[a].y,
                        particlesArray[b].x, particlesArray[b].y
                    );
                    gradient.addColorStop(0, `rgba(0, 180, 255, ${opacity * 0.5})`);
                    gradient.addColorStop(1, `rgba(0, 255, 180, ${opacity * 0.5})`);
                    
                    ctx.strokeStyle = gradient;
                    ctx.lineWidth = opacity * 2;
                    ctx.beginPath();
                    ctx.moveTo(particlesArray[a].x, particlesArray[a].y);
                    ctx.lineTo(particlesArray[b].x, particlesArray[b].y);
                    ctx.stroke();
                }
            }
        }
    }

    // Animation loop
    function animate() {
        requestAnimationFrame(animate);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particlesArray.forEach(particle => {
            particle.update();
            particle.draw();
        });
        connect();
    }

    init();
    animate();
  </script>
</div>

@endsection