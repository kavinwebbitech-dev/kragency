@extends('frontend.layouts.app')
@section('title', 'Home')

@section('content')
    <section class="results mt-4">
        <div class="lottery-result result-page">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="lottery-times">
                            @foreach ($schedules as $schedule)
                                @php
                                    $scheduleTime = \Carbon\Carbon::parse($schedule->time);
                                    $compareTime = now()->addMinutes($close_time);
                                @endphp

                                @if ($scheduleTime->greaterThan($compareTime))
                                    <div onclick="window.location.href='{{ route('customer.play-now', ['id' => $schedule->betting_providers_id, 'time_id' => $schedule->id]) }}'"
                                        style="cursor:pointer;"
                                        class="lottery-card {{ $scheduleTime->lessThan($compareTime) ? 'closed' : ($schedule->id == $slot_time_id ? 'active' : 'running') }}">
                                    @else
                                        <div
                                            class="lottery-card {{ $scheduleTime->lessThan($compareTime) ? 'closed' : ($schedule->id == $slot_time_id ? 'active' : 'running') }}">
                                @endif
                                {{ date('h:i A', strtotime($schedule->time)) }} <br>
                                <small>{{ $schedule->name }}<br>
                                    {{ $scheduleTime->greaterThan($compareTime) ? 'active' : 'closed' }}
                                </small>
                        </div>
                        @endforeach
                    </div>

                    @if ($show_slot == 1)
                        @foreach ($gameSlots as $group)
                            @php
                                $first = $group->first();
                                $type = $first->digitMaster->type;
                            @endphp
                            @if(!in_array($first->digitMaster->id, [9, 10, 11], true))

                                <div class="game-box {{ $type == 1 ? 'singleDigit' : '' }}">
                                <div class="header mb-3">
                                    @if ($type == 1)
                                        <div class="title">
                                            Single Digit
                                            <span>Win ₹{{ $first->providerSlot?->winning_amount ?? 0 }}
                                            </span>
                                        </div>
                                    @elseif ($type == 2)
                                        <div class="title">
                                            Double Digit
                                            <span>Win ₹{{ $first->providerSlot?->winning_amount ?? 0 }}
                                            </span>
                                        </div>
                                    @elseif ($type == 3)
                                        <div class="title">
                                            Three Digit
                                            <span>Win ₹{{ $first->providerSlot?->winning_amount ?? 0 }}</span>
                                        </div>
                                    @elseif ($type == 4)
                                        <div class="title">
                                            Four Digit
                                            <span>Win ₹{{ $first->providerSlot?->winning_amount ?? 0 }}</span>
                                        </div>
                                    @endif

                                    <div class="price">Price : ₹{{ $first->amount }}</div>
                                </div>
                                @foreach ($group as $game_slot)
                                    <div class="gridWrap" data-type="{{ $type }}"
                                        data-game-label="{{ $game_slot->digitMaster->name }}"
                                        data-game-id="{{ $game_slot->id }}" data-amount="{{ $game_slot->amount }}">

                                        @php
                                            $gameName = $game_slot->digitMaster?->name ?? '';
                                            $gameName = preg_replace('/\s*\(.*?\)\s*/', '', $gameName);
                                        @endphp

                                        <div class="d-flex">
                                            @foreach (str_split($gameName) as $char)
                                                <div class="label" style="margin:0 0 0 2px;padding:0;">{{ $char }}
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="gridChild">
                                            <div class="d-flex justifyEnd">
                                                {{-- INPUT BOXES --}}
                                                @for ($i = 1; $i <= $type; $i++)
                                                    <input type="text" class="input-box" maxlength="1"
                                                        name="digit{{ $i }}" inputmode="numeric"
                                                        pattern="[0-9]*" autocomplete="one-time-code">
                                                @endfor
                                            </div>
                                        </div>


                                        {{-- COUNTER --}}
                                        <div class="gridChild">
                                            <div class="counter">
                                                <button type="button" class="minus">-</button>
                                                <span class="count">1</span>
                                                <button type="button" class="plus">+</button>
                                            </div>
                                        </div>

                                        {{-- ADD --}}
                                        <div class="gridChild text-center text-lg-right">
                                            <a class="custom-button2 add-to-cart">ADD</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        @endforeach
                    @endif

                </div>

                @if ($show_slot == 1)
                    <div class="col-lg-12 text-center">
                        <a href="{{ route('lottery.view.cart') }}" class="custom-button2" id="pay-now1">Pay Now</a>
                    </div>
                @endif
            </div>
        </div>
        </div>
    </section>

    <!-- Cart Summary Modal "-->
    <div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Your Selections</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="cart-items">
                        <!-- Cart items will be displayed here -->
                    </div>
                    <div class="total-amount">
                        <strong>Total: ₹<span id="cart-total">0</span></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Continue Playing</button>
                    <button type="button" class="btn btn-primary" id="confirm-payment">Confirm Payment</button>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    @push('scripts')
<script>
$(document).ready(function () {

    let cart = [];

    /* =========================
       LOAD CART FROM SESSION
    ==========================*/
    $.ajax({
        url: '{{ route('lottery.get-cart') }}',
        method: 'GET',
        success: function (response) {
            if (response.cart) {
                cart = response.cart;
                $('#cartCount').text(cart.length);
            }
        }
    });

    /* =========================
       COUNTER (+ / -)
    ==========================*/
    $(document).on('click', '.plus', function () {
        let countEl = $(this).siblings('.count');
        countEl.text(parseInt(countEl.text()) + 1);
    });

    $(document).on('click', '.minus', function () {
        let countEl = $(this).siblings('.count');
        let count = parseInt(countEl.text());
        if (count > 1) countEl.text(count - 1);
    });

    /* =========================
       ADD TO CART
    ==========================*/
    $(document).on('click', '.add-to-cart', function () {
        
        let gameWrapper = $(this).closest('.gridWrap');

        let type      = Number(gameWrapper.data('type'));
        let gameLabel = gameWrapper.data('game-label');
        let gameId    = gameWrapper.data('game-id');
        let amount    = Number(gameWrapper.data('amount'));
        let quantity  = Number(gameWrapper.find('.count').text());
        let isBox     = gameWrapper.find('.box-toggle').hasClass('active');

        /* =========================
           DIGIT VALIDATION (ALL TYPES)
        ==========================*/
        let digits = '';
        let isValid = true;

        gameWrapper.find('.input-box').each(function () {
            let val = $(this).val().trim();
            if (val === '' || isNaN(val)) {
                isValid = false;
            }
            digits += val;
        });

        if (!isValid || digits.length !== type) {
            alert('Please enter valid digits');
            return;
        }

        /* =========================
           LOGIN CHECK
        ==========================*/
        let isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
        if (!isLoggedIn) {
            Swal.fire({
                icon: 'error',
                title: 'Please login to continue!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = "{{ route('login') }}";
            });
            return;
        }

        /* =========================
           PREPARE CART ITEM
        ==========================*/
        let cartItem = {
            type: type,
            game_id: gameId,
            game_label: gameLabel,
            digits: digits,
            quantity: quantity,
            amount: amount,
            is_box: isBox,
            total: amount * quantity
        };

        let newTotal = cart.reduce((sum, item) => sum + Number(item.total), 0) + cartItem.total;

        /* =========================
           WALLET CHECK
        ==========================*/
        $.ajax({
            url: '{{ route('lottery.cart.check-wallet') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                total: newTotal,
                data : cartItem
            },
            success: function (response) {

                if (!response.success) {
                    alert(response.message || 'Insufficient wallet balance');
                    return;
                }

                /* =========================
                   ADD TO CART (SERVER)
                ==========================*/
                $.ajax({
                    url: '{{ route('lottery.add-to-cart') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        item: cartItem
                    },
                    success: function (res) {
                        if (res.success) {
                            cart = res.cart;
                            $('#cartCount').text(cart.length);

                            Swal.fire({
                                icon: 'success',
                                title: 'Added to cart!',
                                timer: 1200,
                                showConfirmButton: false
                            });

                            // Reset fields
                            gameWrapper.find('.input-box').val('');
                            gameWrapper.find('.count').text('1');
                            gameWrapper.find('.box-toggle').removeClass('active');

                        } else {
                            alert('Failed to add item');
                        }
                    }
                });
            },
            error: function () {
                alert('Wallet check failed');
            }
        });
    });

});
</script>

<script>
/* =========================
   AUTO FOCUS NEXT INPUT
=========================*/
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.gridWrap .input-box').forEach((input, index, arr) => {
        input.addEventListener('input', function () {
            if (this.value.length === 1) {
                let next = arr[index + 1];
                if (next && next.closest('.gridWrap') === this.closest('.gridWrap')) {
                    next.focus();
                }
            }
        });
    });
});
</script>
@endpush

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus next input for double, triple, and four digit games
            document.querySelectorAll(
                '.gridWrap[data-type="2"] .input-box, .gridWrap[data-type="3"] .input-box, .gridWrap[data-type="4"] .input-box'
            ).forEach(function(input, idx, arr) {
                input.addEventListener('input', function(e) {
                    if (e.target.value.length === e.target.maxLength) {
                        // Find the next input in the same parent
                        let next = e.target.parentElement.querySelectorAll('.input-box')[Array
                            .prototype.indexOf.call(e.target.parentElement.querySelectorAll(
                                '.input-box'), e.target) + 1];
                        if (!next) {
                            // Try to find next input in the next sibling (for flex layouts)
                            let allInputs = Array.from(e.target.closest(
                                '.d-flex, .d-flex.justifyEnd').querySelectorAll(
                                '.input-box'));
                            let idx = allInputs.indexOf(e.target);
                            if (idx !== -1 && idx + 1 < allInputs.length) {
                                next = allInputs[idx + 1];
                            }
                        }
                        if (next) next.focus();
                    }
                });
            });
        });
    </script>
@endpush