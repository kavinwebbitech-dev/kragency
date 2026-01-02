<header>

    {{-- ================= MOBILE TOP LOGIN BAR ================= --}}
    @if (!Auth::check())
        <div class="mobile-auth-bar d-lg-none bg-yellows">
            <div class="container d-flex justify-content-center">
                <a href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt mr-1"></i> Login / Register
                </a>
            </div>
        </div>
    @endif


    {{-- ================= MAIN NAVBAR ================= --}}
    <nav class="navbar navbar-expand-lg navbar-light main-navbar">
        <div class="container d-flex align-items-center justify-content-between p-2">

            {{-- LOGO --}}
            <a class="navbar-brand"
                href="{{ auth()->check() ? route('customer.dashboard') : route('landing-dashboard') }}">

                {{-- Mobile Logo --}}
                <img src="{{ asset('frontend/images/logoimage.png') }}" class="brand-logo brand-logo-mobile d-lg-none"
                    alt="Logo">

                {{-- Desktop Logo --}}
                <img src="{{ asset('frontend/images/logo.png') }}"
                    class="brand-logo brand-logo-desktop d-none d-lg-block" alt="Logo">
            </a>


            {{-- ================= MOBILE RIGHT ICONS ================= --}}
            <div class="d-flex justify-content-end align-items-center d-lg-none">
                <div class="mobile-icons">

                    {{-- CART --}}
                    <a href="{{ route('lottery.view.cart') }}" class="icon-btn position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge badge-danger cart-badge">
                              {{ count(session('lotteryCart.' . auth()->id(), [])) }}
                        </span>
                    </a>

                    {{-- RESULTS --}}
                    <a href="{{ route('customer.results') }}" class="icon-btn text-black">
                        <i class="fas fa-trophy"></i>
                    </a>

                    {{-- WALLET --}}
                    @if (Auth::check())
                        <div class="mobile-wallet">
                            <i class="fas fa-wallet"></i>
                            <span>₹{{ $user_detail?->wallet?->balance ?? 0 }}</span>
                        </div>
                    @endif

                    {{-- USER --}}
                    @if (Auth::check())
                        <a href="#" class="icon-btn" data-toggle="offcanvas" data-target="#userOffcanvas">
                            <i class="fas fa-user"></i>
                        </a>
                    @endif

                    {{-- MENU --}}
                    <button class="navbar-toggler" type="button" data-toggle="offcanvas" data-target="#navOffcanvas">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>

            {{-- ================= DESKTOP NAV LINKS ================= --}}
            <div class="collapse navbar-collapse d-none d-lg-flex" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-lg-center">

                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ Auth::check() ? route('customer.dashboard') : route('landing-dashboard') }}">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer.results') }}">Results</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer.rules') }}">Rules</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ $whatsapp_number ? 'https://wa.me/' . $whatsapp_number : '#' }}"
                            target="_blank">
                            Recharge
                        </a>
                    </li>




                    {{-- ================= AUTH USER DESKTOP ================= --}}
                    @if (Auth::check())
                        {{-- WALLET --}}
                        <li class="nav-item d-flex align-items-center ml-3 wallet-ui">
                            <i class="fas fa-wallet mr-1"></i>
                            <strong>{{ $user_detail?->wallet?->balance ?? 0 }}</strong>
                        </li>


                        {{-- CART DESKTOP --}}
                        <li class="nav-item ml-3">
                            <a href="{{ route('lottery.view.cart') }}" class="nav-link position-relative">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="badge badge-danger cart-badge">
                                    {{ count(session('lotteryCart.' . auth()->id(), [])) }}
                                </span>
                            </a>
                        </li>

                        {{-- USER DESKTOP DROPDOWN --}}
                        <li class="nav-item dropdown ml-3">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-user"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                <div class="px-3 py-2 border-bottom">
                                    <strong>{{ $user_detail->name ?? '' }}</strong><br>
                                    <small class="text-muted">{{ $user_detail->mobile ?? '' }}</small>
                                </div>

                                <a class="dropdown-item" href="{{ route('customer-order-details') }}">
                                    <i class="fas fa-history mr-2"></i> Order History
                                </a>
                                <a class="dropdown-item" href="{{ route('payment.history') }}">
                                    <i class="fas fa-wallet mr-2"></i> Payment History
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="{{ route('customer.withdraw') }}">
                                    <i class="fas fa-money-bill-wave mr-2"></i> Withdraw
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.withdraw.history') }}">
                                    <i class="fas fa-history mr-2"></i> Withdraw History
                                </a>
                                <a class="dropdown-item" href="{{ route('bank-details.create') }}">
                                    <i class="fas fa-university mr-2"></i> Add Bank Details
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </li>
                    @else
                        {{-- LOGIN DESKTOP --}}
                        <li class="nav-item ml-3">
                            <a class="btn btn-primary text-white px-3" href="{{ route('login') }}">
                                Login / Register
                            </a>
                        </li>
                    @endif

                </ul>
            </div>

        </div>
    </nav>
</header>


{{-- ================= MOBILE MENU OFFCANVAS ================= --}}
<div class="offcanvas offcanvas-start" id="navOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            {{-- <img src="{{ asset('frontend/images/logo.png') }}" style="width: 150px;"> --}}
            Kumaran Groups
        </h5>
        <button type="button" class="close-btn" data-dismiss="offcanvas">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="offcanvas-body">
        <ul class="offcanvas-menu">
            <li>
                <a href="{{ Auth::check() ? route('customer.dashboard') : route('landing-dashboard') }}">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li>
                <a href="{{ route('customer.results') }}">
                    <i class="fas fa-trophy"></i> Results
                </a>
            </li>
            <li>
                <a href="{{ route('customer.rules') }}">
                    <i class="fas fa-book"></i> Rules
                </a>
            </li>
            <li>
                <a href="{{ $whatsapp_number ? 'https://wa.me/' . $whatsapp_number : '#' }}" target="_blank">
                    <i class="fab fa-whatsapp"></i> Recharge
                </a>
            </li>

            @guest
                <li>
                    <a href="{{ route('login') }}">
                        <i class="fas fa-sign-in-alt"></i> Login / Register
                    </a>
                </li>

            @endguest


            @if (Auth::check())
                <li class="wallet-info">
                    <i class="fas fa-wallet"></i>
                    <span>Wallet Balance: <strong>₹{{ $user_detail?->wallet?->balance ?? 0 }}</strong></span>
                </li>
            @endif
        </ul>
    </div>
</div>


{{-- ================= USER PROFILE OFFCANVAS ================= --}}
@if (Auth::check())
    <div class="offcanvas offcanvas-end" id="userOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                <i class="fas fa-user-circle"></i> My Account
            </h5>
            <button type="button" class="close-btn" data-dismiss="offcanvas">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <div class="user-profile-section">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h6 class="user-name">{{ $user_detail->name ?? '' }}</h6>
                <p class="user-mobile">{{ $user_detail->mobile ?? '' }}</p>
                <div class="user-wallet">
                    <i class="fas fa-wallet"></i> ₹{{ $user_detail?->wallet?->balance ?? 0 }}
                </div>
            </div>

            <ul class="offcanvas-menu">
                <li>
                    <a href="{{ route('customer-order-details') }}">
                        <i class="fas fa-history"></i> Order History
                    </a>
                </li>
                <li>
                    <a href="{{ route('payment.history') }}">
                        <i class="fas fa-wallet"></i> Payment History
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="{{ route('customer.withdraw') }}">
                        <i class="fas fa-money-bill-wave"></i> Withdraw
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.withdraw.history') }}">
                        <i class="fas fa-history"></i> Withdraw History
                    </a>
                </li>
                <li>
                    <a href="{{ route('bank-details.create') }}">
                        <i class="fas fa-university"></i> Add Bank Details
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="{{ route('logout') }}" class="text-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endif


{{-- OFFCANVAS BACKDROP --}}
<div class="offcanvas-backdrop"></div>


<style>
    /* ================= HEADER ================= */
    .main-navbar {
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        padding: 10px 0;
        position: relative;
        z-index: 1000;
    }

    .brand-logo {
        width: 220px;
        height: auto;
    }

    .mobile-auth-bar {
        background: #fff;
        padding: 6px 0;
        border-bottom: 1px solid #eee;
    }

    .mobile-auth-bar a {
        font-size: 13px;
        font-weight: 600;
        color: #e53935;
    }

    /* ================= MOBILE ICONS (DEFAULT – MOBILE FIRST) ================= */
    /* MOBILE ICON GROUP */
    .mobile-icons {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    /* ICON BUTTON */
    .icon-btn {
        font-size: 18px;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mobile-wallet {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        background: #f4f6ff;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        color: #4e54c8;
        white-space: nowrap;
    }

    .mobile-wallet i {
        font-size: 14px;
        color: #28a745;
    }

    /* Optional: slightly smaller text on very small phones */
    @media (max-width: 360px) {
        .mobile-wallet {
            font-size: 12px;
            padding: 0px 7px;
        }
    }



    /* ================= SMALL PHONES (≤ 360px) ================= */
    @media (max-width: 360px) {
        .mobile-icons {
            gap: 16px;
        }
    }

    /* ================= PHONES (≤ 576px) ================= */
    @media (min-width: 361px) and (max-width: 576px) {
        .mobile-icons {
            gap: 30px;
        }
    }

    /* ================= TABLETS (≥ 577px) ================= */
    @media (min-width: 577px) and (max-width: 991px) {
        .mobile-icons {
            gap: 30px;
        }
    }

    /* ================= DESKTOP (≥ 992px) ================= */
    @media (min-width: 992px) {
        .mobile-icons {
            gap: 40px;
        }
    }


    .icon-btn {
        font-size: 18px;
        color: #333;
        position: relative;
        background: none;
        border: none;
        cursor: pointer;
    }

    .cart-badge {
        position: absolute;
        top: -6px;
        right: -10px;
        font-size: 10px;
        padding: 3px 5px;
    }

    .navbar-toggler {
        border: none;
        outline: none;
        box-shadow: none;
        padding: 0;
    }

    .nav-link {
        font-weight: 500;
        white-space: nowrap;
    }


    /* ================= OFFCANVAS ================= */
    .offcanvas {
        position: fixed;
        top: 0;
        height: 100vh;
        height: 100dvh;
        width: 280px;
        background: #ffffff;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        z-index: 1050;
        transition: transform 0.3s ease-in-out;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .offcanvas-start {
        left: 0;
        right: auto;
        transform: translateX(-100%);
    }

    .offcanvas-end {
        right: 0;
        left: auto;
        transform: translateX(100%);
    }

    .offcanvas.show {
        transform: translateX(0) !important;
    }

    .offcanvas-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .offcanvas-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        color: #333;
        cursor: pointer;
        padding: 0;
        width: 10%;
        line-height: 1;
        outline: none !important;
    }

    .navbar-light .navbar-toggler {
        outline: none;
    }

    .offcanvas-body {
        padding: 20px;
    }

    .offcanvas-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .offcanvas-menu li {
        margin-bottom: 5px;
    }

    .offcanvas-menu li.divider {
        height: 1px;
        background: #eee;
        margin: 15px 0;
    }

    .offcanvas-menu li.wallet-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .offcanvas-menu li.wallet-info i {
        font-size: 20px;
        color: #4e54c8;
    }

    .offcanvas-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .offcanvas-menu a:hover {
        background: #f8f9fa;
        color: #4e54c8;
    }

    .offcanvas-menu a i {
        width: 20px;
        font-size: 16px;
    }

    .offcanvas-menu a.text-danger:hover {
        background: #fff5f5;
        color: #dc3545;
    }

    /* USER PROFILE SECTION */
    .user-profile-section {
        text-align: center;
        padding: 20px 0;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
    }

    .user-avatar {
        font-size: 60px;
        color: #4e54c8;
        margin-bottom: 10px;
    }

    .user-name {
        font-size: 18px;
        font-weight: 600;
        margin: 10px 0 5px;
    }

    .user-mobile {
        color: #6c757d;
        font-size: 14px;
        margin: 0 0 15px;
    }

    .user-wallet {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #4e54c8;
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
    }

    /* BACKDROP */
    .offcanvas-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .offcanvas-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    /* BODY LOCK */
    body.offcanvas-open {
        overflow: hidden;
    }

    /* ================= WALLET UI ================= */
    .wallet-ui {
        padding: 6px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border: 1px solid #e5e5e5;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
    }

    /* ICON */
    .wallet-ui i {
        color: #28a745;
        font-size: 15px;
    }



    /* HOVER FEEDBACK */
    .wallet-ui:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
    }

    /* MOBILE FRIENDLY */
    @media (max-width: 576px) {
        .wallet-ui {
            padding: 5px 12px;
            font-size: 13px;
        }
    }


    /* ================= RESPONSIVE ================= */
    @media (max-width: 576px) {
        .brand-logo {
            width: 160px;
        }

        .navbar {
            padding: 6px 0;
        }

        .offcanvas {
            width: 85%;
            max-width: 320px;
        }
    }

    @media (min-width: 992px) {

        .offcanvas,
        .offcanvas-backdrop {
            display: none !important;
        }
    }
</style>


<script>
    // ================= OFFCANVAS FUNCTIONALITY =================
    document.addEventListener('DOMContentLoaded', function() {

        // Get all offcanvas triggers
        const offcanvasTriggers = document.querySelectorAll('[data-toggle="offcanvas"]');
        const offcanvasBackdrop = document.querySelector('.offcanvas-backdrop');
        const closeButtons = document.querySelectorAll('[data-dismiss="offcanvas"]');

        // Open offcanvas
        offcanvasTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                const targetOffcanvas = document.querySelector(targetId);

                if (targetOffcanvas) {
                    // Close any open offcanvas first
                    closeAllOffcanvas();

                    // Open the target offcanvas
                    targetOffcanvas.classList.add('show');
                    offcanvasBackdrop.classList.add('show');
                    document.body.classList.add('offcanvas-open');
                }
            });
        });

        // Close offcanvas
        function closeAllOffcanvas() {
            const allOffcanvas = document.querySelectorAll('.offcanvas');
            allOffcanvas.forEach(offcanvas => {
                offcanvas.classList.remove('show');
            });
            offcanvasBackdrop.classList.remove('show');
            document.body.classList.remove('offcanvas-open');
        }

        // Close button click
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                closeAllOffcanvas();
            });
        });

        // Backdrop click
        offcanvasBackdrop.addEventListener('click', function() {
            closeAllOffcanvas();
        });

        // ESC key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllOffcanvas();
            }
        });

    });
</script>
