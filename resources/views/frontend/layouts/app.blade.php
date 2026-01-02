<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap-popover-x.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/responsive.css') }}">
    <link rel="shortcut icon" href="{{ asset('frontend/images/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('frontend/css/header.css') }}">
    @stack('styles')
    <title>@yield('title', 'Kragncy')</title>
</head>

<body>

    {{-- Preloader --}}
    @include('frontend.partials.preloader')

    {{-- Overlay --}}
    <div class="overlay"></div>
    <a href="#" class="scrollToTop"><i class="fas fa-angle-up"></i></a>





    {{-- Header --}}
    @include('frontend.partials.header')


    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('frontend.partials.footer')

    {{-- Scripts --}}
    <script src="{{ asset('frontend/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/modernizr-3.6.0.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins.js') }}"></script>
    <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/magnific-popup.min.js') }}"></script>
    <script src="{{ asset('frontend/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend/js/countdown.min.js') }}"></script>
    <script src="{{ asset('frontend/js/bootstrap-popover-x.min.js') }}"></script>
    <script src="{{ asset('frontend/js/amd.js') }}"></script>
    <script src="{{ asset('frontend/js/nice-select.js') }}"></script>
    <script src="{{ asset('frontend/js/main.js') }}"></script>
    <!-- SweetAlert2 CDN -->
    <script src="{{ asset('frontend/js/sweetalert.js') }}"></script>
    <script>
        // JavaScript for cart functionality (example)
        $(document).ready(function() {
            $('.header-icon[data-target="#profileModal"]').on('click', function(e) {
                e.preventDefault();
                $('#profileModal').modal('show');
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Load cart from Laravel session on page load
            $.ajax({
                url: '{{ route('lottery.get-cart') }}',
                method: 'GET',
                success: function(response) {
                    if (response.cart) {
                        cart = response.cart;
                        $('#cartCount').text(cart.length);
                    }
                }
            });
        });
    </script>

    @stack('scripts')

</body>

</html>
