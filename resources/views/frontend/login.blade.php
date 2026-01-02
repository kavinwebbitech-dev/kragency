@extends('frontend.layouts.app')

@section('title', 'Login - Kragncy')

@section('content')

    <section class="login-section">
        <div class="container">
            <div class="login-card">
                <button onclick="goBack()" class="back-btn" title="Go Back">
                    <i class="fas fa-arrow-left"></i>
                </button>

                <div class="login-card-header">
                    <h2>Login</h2>
                    <p>Access your account to continue</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger text-left">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="login-form">
                    @csrf
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="mobile" placeholder="Enter your mobile number" required value="{{ old('mobile') }}">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-login">Sign In</button>

                    <div class="or-separator">or</div>

                    <a href="{{ $link ?? '#' }}" class="btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> Join Now on WhatsApp
                    </a>
                </form>
            </div>
        </div>
    </section>

@endsection

@push('styles')
    <style>
        /* Main Section Centering */
        .login-section {
            min-height: calc(100vh - 80px); /* Adjust based on your header height */
            width: 100%;
            display: flex;
            align-items: center; /* Vertical center */
            justify-content: center; /* Horizontal center */
            padding: 40px 15px;
            background-color: #f4f7fe; /* Light background to make the card pop */
        }

        /* The Card */
        .login-card {
            background: #ffffff;
            padding: 50px 30px 40px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
            position: relative; /* Required for the absolute Back Button */
        }

        /* Back Button Style */
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #f1f1f1;
            border: none;
            color: #5c27fe;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
        }

        .back-btn:hover {
            background: #5c27fe;
            color: #ffffff;
            transform: translateX(-3px);
        }

        /* Header Text */
        .login-card-header h2 {
            margin-bottom: 5px;
            font-size: 28px;
            color: #333;
            font-weight: 700;
        }

        .login-card-header p {
            margin-bottom: 30px;
            color: #777;
            font-size: 14px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 15px;
            transition: 0.3s;
            background: #fcfcfc;
        }

        .form-group input:focus {
            border-color: #5c27fe;
            box-shadow: 0 0 8px rgba(92, 39, 254, 0.15);
            outline: none;
            background: #fff;
        }

        /* Buttons */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #5c27fe 0%, #461abf 100%);
            color: #fff;
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(92, 39, 254, 0.3);
        }

        .or-separator {
            margin: 25px 0;
            color: #bbb;
            font-size: 13px;
            position: relative;
        }

        .btn-whatsapp {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: #25D366;
            color: #fff;
            padding: 12px;
            border-radius: 10px;
            text-decoration: none !important;
            font-size: 15px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-whatsapp i {
            margin-right: 10px;
            font-size: 20px;
        }

        .btn-whatsapp:hover {
            background: #1ebe57;
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
        }

        /* Alert styling */
        .alert {
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        /* Responsive Fixes */
        @media (max-width: 576px) {
            .login-card {
                padding: 40px 20px;
            }
            .login-section {
                align-items: flex-start; /* Better view when keyboard opens */
            }
        }
    </style>
@endpush

@push('scripts')
<script>
    function goBack() {
        if (document.referrer !== "" && window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = "{{ route('landing-dashboard') }}";
        }
    }
</script>
@endpush