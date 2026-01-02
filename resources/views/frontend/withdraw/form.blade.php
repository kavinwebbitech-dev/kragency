@extends('frontend.layouts.app')

@section('title', 'Withdraw')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Withdraw Request
                </div>

                <div class="card-body">

                    {{-- Success --}}
                    @if (session('success'))
                        <div class="alert alert-success" style="font-size:14px">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger" style="font-size:14px">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customer.withdraw.submit') }}">
                        @csrf

                        <small class="text-muted d-block mb-2">
                            Minimum withdrawal ₹500. Only one request allowed per 24 hours.
                        </small>

                        <div class="form-group mb-3">
                            <label for="amount">Withdraw Amount</label>
                            <input
                                type="number"
                                name="amount"
                                class="form-control"
                                min="500"
                                max="{{ $wallet->balance ?? 0 }}"
                                placeholder="Enter amount (min ₹500)"
                                required
                            >
                        </div>

                        <div class="form-group mb-3">
                            <label>
                                Wallet Balance:
                                <strong>₹{{ $wallet->balance ?? 0 }}</strong>
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                            {{ ($wallet->balance ?? 0) < 500 ? 'disabled' : '' }}
                        >
                            Submit Request
                        </button>

                        @if (($wallet->balance ?? 0) < 500)
                            <small class="text-danger d-block mt-2">
                                Minimum wallet balance ₹500 required to withdraw.
                            </small>
                        @endif
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
