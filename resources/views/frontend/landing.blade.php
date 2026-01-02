@extends('frontend.layouts.app')

@section('title', 'Home')

@section('content')

    <!-- ==========Banner-Section========== -->
    <section class="banner-section">
        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                @foreach ($sliders as $key => $slider)
                    <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                        <img class="d-block w-100" src="{{ asset($slider->image_path) }}"
                            alt="{{ $slider->title ?? 'Slider Image' }}">
                        @if ($slider->title || $slider->description)
                            <div class="carousel-caption d-none d-md-block">
                                @if ($slider->title)
                                    <h5>{{ $slider->title }}</h5>
                                @endif
                                @if ($slider->description)
                                    <p>{{ $slider->description }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </section>

    <!-- ==========Draw-Section========== -->
    <section class="draw-section overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="content">
                        <div class="section-header">
                            <h2 class="title">3 & 4 Digits Game</h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">

                    <!-- DESKTOP & LAPTOP : OWL CAROUSEL -->
                    {{-- <div class="bet-draw-slider betDrawOwl owl-carousel d-none d-lg-block">
                        @foreach ($schedules as $index => $provider)
                            @if ($provider->is_default == 0)
                                <div class="bet-draw-slide">
                                    <a href="{{ !empty($provider->next_slot_time)
                                        ? route('customer.play-now', ['id' => $provider->betting_providers_id, 'time_id' => $provider->next_slot_id])
                                        : 'javascript:void(0)' }}"
                                        class="bet-draw-card-link">
                                        <div class="bet-draw-card">
                                            <img class="bet-draw-card__overlay"
                                                src="{{ asset('frontend/images/overlaymask1.png') }}" alt="">

                                            <div class="bet-draw-card__icon">
                                                <img src="{{ asset('../storage/app/public/' . $provider->imagepath) }}"
                                                    alt="">
                                            </div>

                                            <h4 class="bet-draw-card__title">{{ $provider->name }}</h4>

                                            @if (!empty($provider->next_slot_time))
                                                <a href="{{ route('customer.play-now', ['id' => $provider->betting_providers_id, 'time_id' => $provider->next_slot_id]) }}"
                                                    class="bet-draw-card__btn">
                                                    Play Now
                                                </a>

                                                <div class="bet-draw-card__next">
                                                    <span>Next Draw</span>
                                                    <div class="bet-draw-card__time">
                                                        <img src="{{ asset('frontend/images/time.png') }}" alt="">
                                                        <h6 id="countdown-{{ $index }}"
                                                            data-end-time="{{ $provider->next_slot_time }}">
                                                            {{ $provider->next_slot_time }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            @else
                                                <a class="bet-draw-card__btn hidden">Play Now</a>

                                                <div class="bet-draw-card__next">
                                                    <span>Next Draw</span>
                                                    <div class="bet-draw-card__time">
                                                        <img src="{{ asset('frontend/images/time.png') }}" alt="">
                                                        <h6>Tomorrow</h6>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div> --}}



   
                    <div class="draw-mobile-grid">
                        <div class="draw-grid">

                            @foreach ($schedules as $index => $provider)
                                @if ($provider->is_default == 0)
                                    <a href="{{ !empty($provider->next_slot_time)
                                        ? route('customer.play-now', [
                                            'id' => $provider->betting_providers_id,
                                            'time_id' => $provider->next_slot_id,
                                        ])
                                        : 'javascript:void(0)' }}"
                                        class="draw-card-link">

                                        <div class="draw-card">
                                            <img class="overlay" src="{{ asset('frontend/images/overlaymask1.png') }}"
                                                alt="">

                                            <div class="draw-card__icon">
                                                <img src="{{ asset('../storage/app/public/' . $provider->imagepath) }}"
                                                    alt="">
                                            </div>

                                            <h4 class="draw-card__title">
                                                {{ $provider->name }}
                                            </h4>

                                            {{-- BUTTON AS DIV (NOT LINK) --}}
                                            <div class="draw-card__btn">
                                                Play Now
                                            </div>

                                            <div class="draw-card__next">
                                                <span>Next Draw</span>
                                                <div class="draw-card__time">
                                                    <img src="{{ asset('frontend/images/time.png') }}" alt="">

                                                    @if (!empty($provider->next_slot_time))
                                                        <h6 id="countdown-{{ $index }}"
                                                            data-end-time="{{ $provider->next_slot_time }}">
                                                            {{ $provider->next_slot_time }}
                                                        </h6>
                                                    @else
                                                        <h6>Tomorrow</h6>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>

                                    </a>
                                @endif
                            @endforeach

                        </div>
                    </div>


                </div>

            </div>
        </div>
    </section>

    <!-- ==========Features-Section========== -->
    <section class="features-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="single-feature">
                        <div class="icon"><img src="{{ asset('frontend/images/f1.png') }}" alt=""></div>
                        <h4 class="title">Trust</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="single-feature">
                        <div class="icon"><img src="{{ asset('frontend/images/f2.png') }}" alt=""></div>
                        <h4 class="title">Safe & Security</h4>
                    </div>
                </div>
                <!--<div class="col-lg-3 col-md-4 col-sm-6">-->
                <!--    <div class="single-feature">-->
                <!--        <div class="icon"><img src="{{ asset('frontend/images/f3.png') }}" alt=""></div>-->
                <!--        <h4 class="title">Zero commission</h4>-->
                <!--    </div>-->
                <!--</div>-->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="single-feature">
                        <div class="icon"><img src="{{ asset('frontend/images/f4.png') }}" alt=""></div>
                        <h4 class="title">24/7 Support</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========Check-Number-Section========== -->
    @if (!empty($default_provider))
        <section class="check-number pt-0">
            <img class="img-left" src="{{ asset('frontend/images/check-num-left.png') }}" alt="">
            <img class="img-right" src="{{ asset('frontend/images/check-num-right.png') }}" alt="">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="content">
                            <div class="section-header">
                                <h2 class="title">{{ $default_provider->name }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="check-box">
                            <a
                                href="{{ route('customer.play-now', ['id' => $default_provider->betting_providers_id, 'time_id' => $default_provider->next_slot_id]) }}">
                                <img src="{{ asset('frontend/images/09.jpg') }}" alt="" class="w-100">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <style>
            /* Mobile card styling */
            .mobile-grid .single-draw {
                background: #ffffff;
                border-radius: 12px;
                padding: 15px;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
                position: relative;
                overflow: hidden;
            }

            /* Card spacing */
            .mobile-grid .col-12 {
                padding-left: 12px;
                padding-right: 12px;
            }

            /* Overlay image */
            .mobile-grid .single-draw .overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0.15;
                object-fit: cover;
                pointer-events: none;
            }

            /* Icon */
            .mobile-grid .single-draw .icon img {
                width: 60px;
                height: 60px;
                object-fit: contain;
                margin-bottom: 8px;
            }

            /* Title */
            .mobile-grid .single-draw h6 {
                font-weight: 700;
                font-size: 16px;
                margin-bottom: 8px;
            }

            /* Play button small */
            .mobile-grid .single-draw .custom-button1.small {
                padding: 6px 16px;
                border-radius: 20px;
                font-size: 14px;
                display: inline-block;
            }

            /* Increase gap between cards */
            .mobile-grid .row.g-3 {
                row-gap: 18px !important;
            }
        </style>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find all countdown elements
            const countdownElements = document.querySelectorAll('[id^="countdown-"]');
            countdownElements.forEach(element => {
                const endTimeString = element.getAttribute('data-end-time');
                // Parse the end time (assuming format like "14:30:00" or similar)
                const now = new Date();
                const [hours, minutes, seconds] = endTimeString.split(':').map(Number);
                // Create a Date object for the end time today
                const endTime = new Date();
                endTime.setHours(hours, minutes, seconds, 0);

                // If the end time has already passed today, set it for tomorrow
                if (endTime <= now) {
                    endTime.setDate(endTime.getDate() + 1);
                }

                // Calculate the difference in seconds
                const diffInSeconds = Math.floor((endTime - now) / 1000);

                // Start the countdown
                startCountdown(element.id, diffInSeconds);
            });
        });

        function startCountdown(elementId, seconds) {
            let el = document.getElementById(elementId);
            if (!el) return;

            let timer = setInterval(() => {
                let hrs = Math.floor(seconds / 3600);
                let mins = Math.floor((seconds % 3600) / 60);
                let secs = seconds % 60;

                el.textContent =
                    (hrs > 0 ? String(hrs).padStart(2, '0') + ":" : "") +
                    String(mins).padStart(2, '0') + ":" +
                    String(secs).padStart(2, '0');

                if (seconds <= 0) {
                    clearInterval(timer);
                    el.textContent = "closed";
                }
                seconds--;
            }, 1000);
        }
    </script>

    <script>
        $(function() {
            $('.betDrawOwl').owlCarousel({
                loop: true,
                margin: 20,
                nav: true,
                dots: false,
                autoplay: true,
                autoplayTimeout: 3500,
                autoplayHoverPause: true,
                smartSpeed: 700,
                navText: [
                    '<span>&#10094;</span>',
                    '<span>&#10095;</span>'
                ],
                responsive: {
                    0: {
                        items: 1
                    },
                    992: {
                        items: 3
                    },
                    1200: {
                        items: 4
                    }
                }
            });
        });
    </script>
@endpush
