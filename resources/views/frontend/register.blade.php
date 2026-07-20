@extends('frontend.layouts.app')

@section('title', 'Register - Kumaranbooking')

@push('styles')
    <style>
        /* CSS Variables & Typography */
        :root {
            --primary-color: #4e54c8;
            --secondary-color: #8f94fb;
            --accent-color: #ff4757;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --gold-color: #ffd700;
        }

        /* Main Section */
        .kb-register-section {
            min-height: calc(100vh - 80px);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
            background-color: #ffffff;
        }

        /* Split Layout Container */
        .kb-register-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(78, 84, 200, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Left Side: Image */
        .kb-register-image {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .kb-register-image-placeholder {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
            background-image: url('{{ asset('frontend/images/loginimages.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Right Side: Form */
        .kb-register-form-side {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Header Text */
        .kb-register-header h2 {
            margin-bottom: 0.5rem;
            font-size: 2rem;
            color: var(--dark-color);
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .kb-register-header p {
            margin-bottom: 2rem;
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Form Controls */
        .kb-register-form-group {
            margin-bottom: 1.5rem;
        }

        .kb-register-form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .kb-register-input {
            width: 100% !important;
            padding: 0.875rem 1rem !important;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: 1rem !important;
            transition: all 0.3s ease !important;
            background: var(--light-color) !important;
            color: var(--dark-color) !important;
            box-sizing: border-box !important;
        }

        .kb-register-input:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 4px rgba(78, 84, 200, 0.1) !important;
            outline: none !important;
            background: #ffffff !important;
        }

        /* Phone & OTP Button Wrapper */
        .kb-register-phone-wrapper {
            display: grid !important;
            grid-template-columns: 1fr auto !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }

        .kb-register-btn-send {
            padding: 0.875rem 1.5rem !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            white-space: nowrap !important;
            transition: all 0.3s ease !important;
            border: none !important;
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
            cursor: pointer;
        }

        .kb-register-btn-send:hover {
            background-color: var(--secondary-color) !important;
        }

        /* OTP Section */
        .kb-register-otp-container {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 0.5rem;
        }

        .kb-register-otp-box {
            width: calc(25% - 9px);
            aspect-ratio: 1;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: var(--light-color);
            transition: all 0.2s ease;
            color: var(--dark-color);
        }

        .kb-register-otp-box:focus {
            border-color: var(--primary-color);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(78, 84, 200, 0.15);
            outline: none;
        }

        /* Submit Button */
        .kb-register-btn-submit {
            display: none; /* Controlled via JS */
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #ffffff;
            border: none;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            box-shadow: 0 4px 12px rgba(78, 84, 200, 0.2);
        }

        .kb-register-btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 84, 200, 0.35);
        }

        /* Separator & Social */
        .kb-register-separator {
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kb-register-separator::before,
        .kb-register-separator::after {
            content: "";
            flex: 1;
            background: #e2e8f0;
            height: 1px;
            margin: 0 10px;
        }

        .kb-register-btn-telegram {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background-color: #0088cc;
            color: #ffffff;
            padding: 1rem;
            border-radius: 12px;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .kb-register-btn-telegram:hover {
            background-color: #0077b6;
            transform: translateY(-1px);
        }

        /* Login Redirect Link */
        .kb-register-login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6c757d;
        }

        .kb-register-login-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .kb-register-login-link a:hover {
            text-decoration: underline;
        }

        /* Alert Styling */
        .kb-register-alert {
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            background-color: #ffeef0;
            color: var(--accent-color);
            border: 1px solid #ffd6db;
        }

        /* Responsive Defaults */
        @media (max-width: 768px) {
            .kb-register-container {
                grid-template-columns: 1fr;
            }

            .kb-register-image {
                display: none;
            }

            .kb-register-form-side {
                padding: 2rem 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <section class="kb-register-section">
        <div class="kb-register-container">

            <!-- Left Side Image -->
            <div class="kb-register-image">
                <div class="kb-register-image-placeholder"></div>
            </div>

            <!-- Right Side Form -->
            <div class="kb-register-form-side">
                <div class="kb-register-header">
                    <h2>Create Your Account</h2>
                    <p>Enter your details below to get started</p>
                </div>

                @if ($errors->any())
                    <div class="kb-register-alert text-left">
                        <ul class="mb-0 text-start">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.submit') }}" method="POST" id="kbRegisterForm">
                    @csrf

                    <!-- Name Field -->
                    <div class="kb-register-form-group">
                        <label for="kbNameInput">Full Name</label>
                        <input type="text" name="name" id="kbNameInput" class="kb-register-input"
                            placeholder="Enter your full name" required value="{{ old('name') }}">
                    </div>

                    <!-- Mobile Number -->
                    <div class="kb-register-form-group">
                        <label for="kbMobileInput">Mobile Number</label>
                        <div class="kb-register-phone-wrapper">
                            <input type="text" name="mobile" id="kbMobileInput" class="kb-register-input"
                                placeholder="Enter mobile number" required value="{{ old('mobile') }}">

                            <button type="button" id="kbSendOtpBtn" class="kb-register-btn-send">
                                Get OTP
                            </button>
                        </div>
                    </div>

                    <!-- OTP Boxes (Hidden until triggered) -->
                    <div class="kb-register-form-group d-none" id="kbOtpSection">
                        <label>Verify 4-Digit OTP</label>
                        <div class="kb-register-otp-container">
                            <input type="text" class="kb-register-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="kb-register-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="kb-register-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                            <input type="text" class="kb-register-otp-box" maxlength="1" pattern="\d*" inputmode="numeric">
                        </div>
                        <input type="hidden" name="otp" id="kbFinalOtp">
                    </div>

                    <button type="submit" class="kb-register-btn-submit" id="kbRegisterSubmitBtn">
                        Register Now
                    </button>

                    <div class="kb-register-separator">or</div>

                    <a href="{{ $link ?? '#' }}" class="kb-register-btn-telegram">
                        <i class="fab fa-telegram-plane"></i> Join Now on Telegram
                    </a>

                    <!-- Login Link -->
                    <div class="kb-register-login-link">
                        Already have an account? <a href="{{ route('login') }}">Login Now</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const otpBoxes = document.querySelectorAll(".kb-register-otp-box");
            const finalOtpInput = document.getElementById("kbFinalOtp");
            const form = document.getElementById("kbRegisterForm");
            const otpSection = document.getElementById("kbOtpSection");
            const registerSubmitBtn = document.getElementById("kbRegisterSubmitBtn");
            const sendOtpBtn = document.getElementById("kbSendOtpBtn");
            const mobileInput = document.getElementById("kbMobileInput");
            const nameInput = document.getElementById('kbNameInput');

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

            // --- Form Submit Validation ---
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
                const name = nameInput ? nameInput.value.trim() : '';

                if (!name) {
                    alert('Please enter your full name.');
                    return;
                }

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
                        'Accept': 'application/json', // CRITICAL: Forces JSON error responses
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        mobile: mobile,
                        name: name,
                        type: "register"
                    })
                })
                .then(async response => {
                    // Check HTTP status explicitly to gracefully handle Laravel errors (500, 422)
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

                        // Show OTP section and submit button
                        otpSection.classList.remove('d-none');
                        otpSection.style.display = 'block'; 
                        registerSubmitBtn.style.display = 'block';

                        // Make OTP fields required and focus first input
                        otpBoxes.forEach(box => box.setAttribute('required', 'true'));
                        otpBoxes[0].focus();

                        alert(data.message || 'OTP Sent Successfully!');

                    } else {
                        // Logic fallback if backend returned 200 but failed (e.g. number exists)
                        alert(data.message || 'Failed to send OTP. Please try again.');
                        
                        // Handle server-side redirects gracefully
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
                    alert(error.message || 'An error occurred while communicating with the server.');
                });
            });
        }); 
    </script>
@endpush