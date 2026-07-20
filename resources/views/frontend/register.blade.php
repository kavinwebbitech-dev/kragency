@extends('frontend.layouts.app')

@section('title', 'Register - Kumaranbooking')

@push('styles')
    <style>
        /* Main Section Centering */
        .login-section {
            min-height: calc(100vh - 80px);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }

        /* Modernized Card */
        .login-card {
            background: #ffffff;
            padding: 45px 35px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(92, 39, 254, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02);
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            text-align: center;
            position: relative;
        }

        /* Header Text */
        .login-card-header h2 {
            margin-bottom: 8px;
            font-size: 28px;
            color: #1a1a1a;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-card-header p {
            margin-bottom: 30px;
            color: #6c757d;
            font-size: 14px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 24px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #343a40;
            font-size: 14px;
        }

        .form-control-custom {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            padding: 14px 16px !important;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: 15px !important;
            transition: all 0.3s ease !important;
            background: #f8fafc !important;
            box-sizing: border-box !important;
        }

        .form-control-custom:focus {
            border-color: #5c27fe !important;
            box-shadow: 0 0 0 4px rgba(92, 39, 254, 0.1) !important;
            outline: none !important;
            background: #fff !important;
        }

        /* Grid forcing input box to expand and button to wrap neatly side-by-side */
        .phone-input-wrapper {
            display: grid !important;
            grid-template-columns: 1fr auto !important;
            gap: 12px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .phone-input-wrapper input {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            padding: 14px 16px !important;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: 15px !important;
            transition: all 0.3s ease !important;
            background: #f8fafc !important;
            box-sizing: border-box !important;
        }

        .phone-input-wrapper input:focus {
            border-color: #5c27fe !important;
            box-shadow: 0 0 0 4px rgba(92, 39, 254, 0.1) !important;
            outline: none !important;
            background: #fff !important;
        }

        .btn-send-otp {
            padding: 14px 24px !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            white-space: nowrap !important;
            transition: all 0.3s ease !important;
            border: none !important;
            background-color: #007bff !important;
            color: #fff !important;
            display: inline-block !important;
            height: auto !important;
        }

        .btn-send-otp:hover {
            background-color: #0056b3 !important;
        }

        /* 4-Digit OTP Boxes */
        .otp-container {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 20px 0 28px 0;
        }

        .otp-box {
            width: 65px;
            height: 65px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
            transition: all 0.2s ease-in-out;
        }

        .otp-box:focus {
            border-color: #5c27fe;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(92, 39, 254, 0.15);
            outline: none;
        }

        /* Action Buttons */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #5c27fe 0%, #461abf 100%);
            color: #fff;
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(92, 39, 254, 0.2);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(92, 39, 254, 0.35);
        }

        .or-separator {
            margin: 25px 0;
            color: #94a3b8;
            font-size: 13px;
            position: relative;
            display: flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .or-separator::before, .or-separator::after {
            content: "";
            flex: 1;
            background: #e2e8f0;
            height: 1px;
            margin: 0 10px;
        }

        .btn-telegram {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background-color: #0088cc;
            color: #fff;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .btn-telegram:hover {
            background-color: #0077b6;
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <section class="login-section">
        <div class="container">
            <div class="login-card">

                <div class="login-card-header">
                    <h2>Create Your Account</h2>
                    <p>Enter your details below to get started</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger text-left">
                        <ul class="mb-0 text-start">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.submit') }}" method="POST" id="registerForm">
                    @csrf

                    <!-- Added Name Field for Registration -->
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="form-control-custom" 
                               placeholder="Enter your full name" 
                               required 
                               value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <div class="phone-input-wrapper">
                            <input type="text"
                                   name="mobile"
                                   id="mobile"
                                   placeholder="Enter mobile number"
                                   required
                                   value="{{ old('mobile') }}">

                            <button type="button" id="sendOtpBtn" class="btn-send-otp">
                                Get OTP
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Verify 4-Digit OTP</label>
                        <div class="otp-container">
                            <input type="text" class="otp-box" maxlength="1" pattern="\d*" inputmode="numeric" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="\d*" inputmode="numeric" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="\d*" inputmode="numeric" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        </div>
                        <input type="hidden" name="otp" id="finalOtp">
                    </div>

                    <button type="submit" class="btn-login" id="registerBtn">
                        Register Now
                    </button>

                    <div class="or-separator">or</div>

                    <a href="{{ $link ?? '#' }}" class="btn-telegram">
                        <i class="fab fa-telegram-plane"></i> Join Now on Telegram
                    </a>

                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const otpBoxes = document.querySelectorAll(".otp-box");
        const finalOtpInput = document.getElementById("finalOtp");
        const form = document.getElementById("registerForm");

        otpBoxes.forEach((box, index) => {
            // Forward jumping focus logic
            box.addEventListener("input", (e) => {
                if (e.target.value.length >= 1) {
                    if (index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                }
                combineOtp();
            });

            // Smooth backspace focus correction logic
            box.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && e.target.value === "") {
                    if (index > 0) {
                        otpBoxes[index - 1].focus();
                    }
                }
            });
        });

        // Pack values into the main hidden field sent to backend
        function combineOtp() {
            let otpValue = "";
            otpBoxes.forEach((box) => {
                otpValue += box.value;
            });
            finalOtpInput.value = otpValue;
        }

        form.addEventListener("submit", function (e) {
            combineOtp();
            if (finalOtpInput.value.length !== 4) {
                e.preventDefault();
                alert("Please fill out the full 4-digit OTP.");
            }
        });
    });
</script>
<script>
    document.getElementById('sendOtpBtn').addEventListener('click', function () {
        const mobile = document.getElementById('mobile').value.trim();

        if (!mobile) {
            alert('Please enter your mobile number.');
            return;
        }

        fetch('{{ route('send.otp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ mobile: mobile, type: "register" })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert(data.message || 'Failed to send OTP. Please try again.');
            }
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending OTP. Please try again.');
        });
    });
</script>
@endpush