@extends('frontend.layouts.app')

@section('title', 'Login - Kumaranbooking')

@push('styles')
    <style>
        :root {
            --kb-primary: #4e54c8;
            --kb-secondary: #8f94fb;
            --kb-accent: #ff4757;
            --kb-light: #f8f9fa;
            --kb-dark: #343a40;
            --kb-success: #28a745;
            --kb-gold: #ffd700;
        }

        .kb-login-section {
            min-height: calc(100vh - 80px);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
            background-color: #ffffff;
        }

        .kb-login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(78, 84, 200, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .kb-login-image {
            background: linear-gradient(135deg, var(--kb-primary) 0%, var(--kb-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .kb-login-image-placeholder {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
            background-image: url('{{ asset('frontend/images/loginimages.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        .kb-login-form-side {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Header Text */
        .kb-login-header h2 {
            margin-bottom: 0.5rem;
            font-size: 2rem;
            color: var(--kb-dark);
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .kb-login-header p {
            margin-bottom: 2rem;
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Form Controls */
        .kb-login-form-group {
            margin-bottom: 1.5rem;
        }

        .kb-login-form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--kb-dark);
            font-size: 0.9rem;
        }

        .kb-login-phone-wrapper {
            display: grid !important;
            grid-template-columns: 1fr auto !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }

        .kb-login-input {
            width: 100% !important;
            padding: 0.875rem 1rem !important;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: 1rem !important;
            transition: all 0.3s ease !important;
            background: var(--kb-light) !important;
            color: var(--kb-dark) !important;
        }

        .kb-login-input:focus {
            border-color: var(--kb-primary) !important;
            box-shadow: 0 0 0 4px rgba(78, 84, 200, 0.1) !important;
            outline: none !important;
            background: #fff !important;
        }

        .kb-login-btn-send {
            padding: 0.875rem 1.5rem !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            white-space: nowrap !important;
            transition: all 0.3s ease !important;
            border: none !important;
            background-color: var(--kb-primary) !important;
            color: #fff !important;
            cursor: pointer;
        }

        .kb-login-btn-send:hover {
            background-color: var(--kb-secondary) !important;
        }

        /* OTP Section (Hidden by Default) */
        .kb-login-otp-section {
            display: none;
            /* Controlled via JS */
        }

        .kb-login-otp-container {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 0.5rem;
        }

        .kb-login-otp-box {
            width: calc(25% - 9px);
            aspect-ratio: 1;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: var(--kb-light);
            transition: all 0.2s ease;
            color: var(--kb-dark);
        }

        .kb-login-otp-box:focus {
            border-color: var(--kb-primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(78, 84, 200, 0.15);
            outline: none;
        }

        /* Submit Buttons */
        .kb-login-btn-submit {
            display: none;
            /* Controlled via JS */
            width: 100%;
            background: linear-gradient(135deg, var(--kb-primary) 0%, var(--kb-secondary) 100%);
            color: #fff;
            border: none;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .kb-login-btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 84, 200, 0.3);
        }

        /* Social / External Links */
        .kb-login-separator {
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kb-login-separator::before,
        .kb-login-separator::after {
            content: "";
            flex: 1;
            background: #e2e8f0;
            height: 1px;
            margin: 0 10px;
        }

        .kb-login-btn-telegram {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background-color: #0088cc;
            color: #fff;
            padding: 1rem;
            border-radius: 12px;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .kb-login-btn-telegram:hover {
            background-color: #0077b6;
            transform: translateY(-1px);
            color: #fff;
        }

        .kb-login-register {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6c757d;
        }

        .kb-login-register a {
            color: var(--kb-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .kb-login-register a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .kb-login-container {
                grid-template-columns: 1fr;
            }

            .kb-login-image {
                display: none;
            }

            .kb-login-form-side {
                padding: 2rem 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <section class="kb-login-section">
        <div class="kb-login-container">

            <!-- Left Side Image -->
            <div class="kb-login-image">
                <!-- Replace with an actual <img> tag for your project -->
                <div class="kb-login-image-placeholder"></div>
            </div>

            <!-- Right Side Form -->
            <div class="kb-login-form-side">
                <div class="kb-login-header">
                    <h2>Welcome Back</h2>
                    <p>Enter your mobile number to securely log in to your account.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger text-left" style="border-radius: 12px; font-size: 0.875rem;">
                        <ul class="mb-0 text-start">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.check') }}" method="POST" id="kbLoginForm">
                    @csrf

                    <!-- Mobile Number -->
                    <div class="kb-login-form-group">
                        <label for="kbMobileInput">Mobile Number</label>
                        <div class="kb-login-phone-wrapper">
                            <input type="text" name="mobile" id="kbMobileInput" class="kb-login-input"
                                placeholder="Enter mobile number" required value="{{ old('mobile') }}">

                            <button type="button" id="kbSendOtpBtn" class="kb-login-btn-send">
                                Send OTP
                            </button>
                        </div>
                    </div>

                    <!-- OTP Boxes (Hidden until triggered) -->
                    <div class="kb-login-form-group kb-login-otp-section" id="kbOtpSection">
                        <label>Enter 4-Digit OTP</label>
                        <div class="kb-login-otp-container">
                            <input type="text" class="kb-login-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="kb-login-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="kb-login-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="kb-login-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                        </div>
                        <input type="hidden" name="otp" id="kbFinalOtp">
                    </div>

                    <button type="submit" class="kb-login-btn-submit" id="kbLoginSubmitBtn">
                        Verify & Sign In
                    </button>

                    <div class="kb-login-separator">or</div>

                    <a href="{{ $link ?? '#' }}" class="kb-login-btn-telegram">
                        <i class="fab fa-telegram-plane"></i> Join Now on Telegram
                    </a>

                    <!-- Register Link Added Here -->
                    <div class="kb-login-register">
                        Don't have an account? <a href="{{ route('register') }}">Register Now</a>
                    </div>
                </form>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const otpBoxes = document.querySelectorAll(".kb-login-otp-box");
            const finalOtpInput = document.getElementById("kbFinalOtp");
            const form = document.getElementById("kbLoginForm");

            // UI Elements for toggling visibility
            const otpSection = document.getElementById("kbOtpSection");
            const loginSubmitBtn = document.getElementById("kbLoginSubmitBtn");
            const sendOtpBtn = document.getElementById("kbSendOtpBtn");
            const mobileInput = document.getElementById("kbMobileInput");

            // --- OTP Input Navigation Logic ---
            otpBoxes.forEach((box, index) => {
                box.addEventListener("input", (e) => {
                    if (e.target.value.length >= 1) {
                        if (index < otpBoxes.length - 1) {
                            otpBoxes[index + 1].focus();
                        }
                    }
                    combineOtp();
                });

                box.addEventListener("keydown", (e) => {
                    if (e.key === "Backspace" && e.target.value === "") {
                        if (index > 0) {
                            otpBoxes[index - 1].focus();
                        }
                    }
                });
            });

            function combineOtp() {
                let otpValue = "";
                otpBoxes.forEach((box) => {
                    otpValue += box.value;
                });
                finalOtpInput.value = otpValue;
            }

            form.addEventListener("submit", function(e) {
                combineOtp();
                if (finalOtpInput.value.length !== 4) {
                    e.preventDefault();
                    alert("Please fill out the full 4-digit OTP.");
                }
            });

            // --- Send OTP API Logic ---
            sendOtpBtn.addEventListener('click', function() {
                const mobile = mobileInput.value.trim();

                if (!mobile) {
                    alert('Please enter your mobile number.');
                    return;
                }

                // Visual feedback while loading
                const originalText = sendOtpBtn.innerHTML;
                sendOtpBtn.innerHTML = 'Sending...';
                sendOtpBtn.disabled = true;

                fetch('{{ route('send.otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json', // CRITICAL: Forces Laravel to return JSON
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            mobile: mobile,
                            type: "login"
                        })
                    })
                    .then(async response => {
                        // Handle Laravel 500 or 422 HTTP errors explicitly
                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({}));
                            throw new Error(errorData.message || 'Server error occurred.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Restore button state
                        sendOtpBtn.innerHTML = originalText;
                        sendOtpBtn.disabled = false;

                        console.log("Laravel API Response:", data); // Debugging

                        // Robust success check accommodating different backend response styles
                        if (data.success || data.status === 'success' || data.status === 200 || data.status === true) {
                            
                            // Display OTP section
                            otpSection.style.display = 'block';
                            loginSubmitBtn.style.display = 'block';

                            // Add required attributes dynamically to OTP boxes once visible
                            otpBoxes.forEach(box => box.setAttribute('required', 'true'));

                            // Focus on the first OTP box
                            otpBoxes[0].focus();

                            alert(data.message || 'OTP Sent Successfully!');
                        } else {
                            // Logic fallback if backend returned 200 but failed (e.g. invalid number)
                            alert(data.message || 'Failed to send OTP. Please try again.');
                            
                            // Only redirect on failure/error if a redirect URL was provided
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        }
                    })
                    .catch(error => {
                        // Restore button state
                        sendOtpBtn.innerHTML = originalText;
                        sendOtpBtn.disabled = false;
                        
                        console.error('Fetch Error:', error);
                        alert(error.message || 'An error occurred while sending OTP. Please try again.');
                    });
            });
        });
    </script>
@endpush