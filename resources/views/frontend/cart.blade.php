@extends('frontend.layouts.app')
@section('title', 'Home')

@section('content')
<section class="results mt-4">
    <div class="lottery-result result-page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2>Your Lottery Cart</h2>
                    @if($cart && count($cart) > 0)
                        @php
                            $totalAmount = collect($cart)->reduce(function($carry, $item) {
                                return $carry + ((isset($item['quantity']) ? $item['quantity'] : 1) * (isset($item['amount']) ? $item['amount'] : 0));
                            }, 0);
                            $today = now()->format('Y-m-d'); // current date
                        @endphp

                        <!-- Total Amount Above -->
                        <div class="d-flex justify-content-end mb-2">
                            <h5>Total Amount: <span class="text-success">{{ $totalAmount }}</span></h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered mt-3">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Provider</th>
                                        <th>Game Name</th>
                                        <th>Quantity</th>
                                        <th>Numbers</th>
                                        <th>Bet Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart as $index => $item)
                                        @php
                                            $get_game = \App\Models\ScheduleProviderSlotTime::find($item['game_id']);
                                            $get_provider = \App\Models\Admin\BettingProvidersModel::find($get_game->betting_providers_id);
                                        @endphp
                                        <tr>
                                            <td>{{ $today }}</td>
                                            <td>{{ $get_provider?->name ?? '-' }}</td>
                                            <td>{{ $item['game_label'] }}</td>
                                            <td>{{ $item['quantity'] }}</td>
                                            <td>{{ $item['digits'] }}</td>
                                            <td>{{ $item['amount'] }}</td>
                                            <td>
                                                <form action="{{ route('lottery.remove-from-cart', $index) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center p-2" title="Remove">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Total Amount Below -->
                        <div class="d-flex justify-content-end mt-2">
                            <h5>Total Amount: <span class="text-success">{{ $totalAmount }}</span></h5>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button id="confirm-payment" class="btn btn-success btn-lg">Confirm Order</button>
                        </div>
                    @else
                        <p>Your cart is empty.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        $('#confirm-payment').click(function() {
            // Send cart data to server via AJAX
            $.ajax({
                url: '{{ route("lottery.place-order") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cart: cart
                },
                success: function(response) {
                    if (response.success) {
                        // Clear cart
                        cart = [];
                        sessionStorage.removeItem('lotteryCart');

                        Swal.fire({
                            icon: 'success',
                            title: 'Order placed successfully!',
                            timer: 2500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('customer-order-details') }}";
                        });
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    </script>
@endpush