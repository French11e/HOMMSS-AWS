@extends('layouts.app')

@section('content')
<main class="pt-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-center">Create Your Account</h4>

                        <form method="POST" action="{{ route('register') }}" name="register-form" class="needs-validation" novalidate>
                            @csrf

                            <!-- Honeypot (improved) -->
                            <div class="visually-hidden" aria-hidden="true" style="position: absolute; left: -9999px;">
                                <input type="text" name="honeypot" id="honeypot" value="" tabindex="-1" autocomplete="off">
                                <input type="hidden" name="timestamp" value="{{ time() }}">
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    name="name"
                                    placeholder="e.g. Juan Dela Cruz"
                                    value="{{ old('name') }}"
                                    required maxlength="255"
                                    autocomplete="name">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email"
                                    class="form-control rounded-3 @error('email') is-invalid @enderror"
                                    name="email"
                                    placeholder="e.g. juan@example.com"
                                    value="{{ old('email') }}"
                                    required autocomplete="email">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="tel"
                                    class="form-control rounded-3 @error('mobile') is-invalid @enderror"
                                    name="mobile"
                                    placeholder="e.g. +639171234567"
                                    value="{{ old('mobile') }}"
                                    required pattern="^\+?[0-9]{10,15}$"
                                    oninput="formatPhoneNumber(this)">
                                @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="position-relative">
                                    <input type="password"
                                        id="password"
                                        class="form-control rounded-3 @error('password') is-invalid @enderror"
                                        name="password"
                                        placeholder="Enter a strong password"
                                        required autocomplete="new-password"
                                        style="padding-right: 45px;">
                                    <button type="button" class="btn btn-link position-absolute" id="togglePassword"
                                        style="right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; padding: 0; z-index: 10;">
                                        <i class="fa fa-eye" id="eyeIcon" style="color: #6c757d;"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Must be 12+ characters and include uppercase, lowercase, number, and special character.
                                </div>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="mb-3">
                                <label for="password-confirm" class="form-label">Confirm Password</label>
                                <div class="position-relative">
                                    <input type="password"
                                        id="password_confirmation"
                                        class="form-control rounded-3"
                                        name="password_confirmation"
                                        placeholder="Repeat your password"
                                        required oninput="checkPasswordMatch(this)"
                                        style="padding-right: 45px;">
                                    <button type="button" class="btn btn-link position-absolute" id="togglePasswordConfirm"
                                        style="right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; padding: 0; z-index: 10;">
                                        <i class="fa fa-eye" id="eyeIconConfirm" style="color: #6c757d;"></i>
                                    </button>
                                </div>
                                <div id="password-match-feedback" class="form-text"></div>
                            </div>

                            <div class="form-text mb-3">
                                We’ll use this info to manage your account and provide a better experience.
                            </div>

			<!-- Turnstile CAPTCHA -->
			@if(config('services.turnstile.site_key'))
			<div class="mb-3 text-center">
   			 <div class="cf-turnstile d-inline-block" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
   				 @error('cf-turnstile-response')
   			     <div class="text-danger small mt-1">{{ $message }}</div>
   				 @enderror
				</div>
				@endif

                            <button type="submit" class="btn btn-primary w-100 rounded-3" id="register-button">
                                Register
                            </button>

                            <div class="text-center mt-3">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-decoration-none">Login</a>
                            </div>
                        </form>

                        <hr class="my-4">

                        <a href="{{ route('google-auth') }}"
                            class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 rounded-3">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" style="width:20px;">
                            <span>Continue with Google</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@section('scripts')
<!-- Turnstile Script -->
@if(config('services.turnstile.site_key'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

<script>
    function checkPasswordMatch(confirmField) {
        const password = document.getElementById('password').value;
        const confirmPassword = confirmField.value;
        const submitBtn = document.querySelector('button[type="submit"]');
        const matchIndicator = document.getElementById('password-match-indicator');

        if (password && confirmPassword) {
            if (password === confirmPassword) {
                confirmField.classList.remove('is-invalid');
                confirmField.classList.add('is-valid');
                if (matchIndicator) {
                    matchIndicator.textContent = 'Passwords match!';
                    matchIndicator.className = 'text-success small';
                }
                submitBtn.disabled = false;
            } else {
                confirmField.classList.remove('is-valid');
                confirmField.classList.add('is-invalid');
                if (matchIndicator) {
                    matchIndicator.textContent = 'Passwords do not match';
                    matchIndicator.className = 'text-danger small';
                }
                submitBtn.disabled = true;
            }
        } else {
            confirmField.classList.remove('is-valid', 'is-invalid');
            if (matchIndicator) {
                matchIndicator.textContent = '';
            }
            submitBtn.disabled = false;
        }
    }

    // Real-time validation
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('password_confirmation');

        if (passwordField && confirmField) {
            confirmField.addEventListener('input', function() {
                checkPasswordMatch(this);
            });

            passwordField.addEventListener('input', function() {
                if (confirmField.value) {
                    checkPasswordMatch(confirmField);
                }
            });
        }

        // Password visibility toggle for main password
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'password') {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                } else {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                }
            });
        }

        // Password visibility toggle for confirm password
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        const eyeIconConfirm = document.getElementById('eyeIconConfirm');

        if (togglePasswordConfirm && passwordConfirmInput && eyeIconConfirm) {
            togglePasswordConfirm.addEventListener('click', function() {
                const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirmInput.setAttribute('type', type);

                if (type === 'password') {
                    eyeIconConfirm.classList.remove('fa-eye-slash');
                    eyeIconConfirm.classList.add('fa-eye');
                } else {
                    eyeIconConfirm.classList.remove('fa-eye');
                    eyeIconConfirm.classList.add('fa-eye-slash');
                }
            });
        }
    });
</script>
@endsection
