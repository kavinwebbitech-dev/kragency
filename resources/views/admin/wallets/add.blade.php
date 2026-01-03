@extends('layouts.app')
@section('title', __('customers/message.customer_wallets'))
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">{{ __('customers/message.customer_wallets') }}</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('customers/message.list_wallets') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card card-warning card-outline mb-4">
                    <div class="card-header"><div class="card-title">{{ __('customers/message.add_wallet') }}</div></div>
                        <form action="{{ route('admin.wallet.add') }}" method="post">
                            @csrf
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="add_bonus" name="add_bonus">
                                            <label class="form-check-label" for="add_bonus">
                                                Add Bonus Amount
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="customer" class="col-sm-3 col-form-label">Select Customer</label>
                                    <div class="col-sm-9">
                                        <select class="form-select" id="customer" name="customer_id" required>
                                            <option value="">-- Select Customer --</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->name . " - ".  $customer->mobile }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div id="customer-error" class="invalid-feedback" role="alert"></div>
                                    </div>
                                </div>
                                <div class="row mb-3" id="amount_wrapper">
                                    <label for="amount" class="col-sm-3 col-form-label">Amount</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" required />
                                    </div>
                                </div>
                                <div class="row mb-3 d-none" id="bonus_amount_wrapper">
                                    <label for="bonus_amount" class="col-sm-3 col-form-label">Bonus Amount</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control"
                                            id="bonus_amount"
                                            name="bonus_amount"
                                            step="0.01"
                                            placeholder="Enter bonus amount">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="description" class="col-sm-3 col-form-label">Description</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">Add Amount</button>
                                <a href="{{ route('admin.wallet.index') }}" class="btn float-end">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {

        // Select2
        $('#customer').select2({
            placeholder: '-- Select Customer --',
            allowClear: true
        });

        // Bonus checkbox toggle
        $('#add_bonus').on('change', function () {
            if ($(this).is(':checked')) {
                $('#bonus_amount_wrapper').removeClass('d-none');
                $('#bonus_amount').attr('required', true);

                $('#amount_wrapper').addClass('d-none');
                $('#amount').removeAttr('required', true);
            } else {
                $('#bonus_amount_wrapper').addClass('d-none');
                $('#bonus_amount').removeAttr('required').val('');

                $('#amount_wrapper').removeClass('d-none');
                $('#amount').attr('required', true);
            }
        });
    });
</script>
@endpush
