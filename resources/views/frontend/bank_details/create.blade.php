@extends('frontend.layouts.app')
@section('title', isset($bankDetail) && $bankDetail ? 'Edit Bank Details' : 'Add Bank Details')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">{{ isset($bankDetail) && $bankDetail ? 'Edit Bank Details' : 'Add Bank Details' }}</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bank-details.store') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $bankDetail->bank_name ?? '') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="ifsc_code">IFSC Code</label>
                            <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $bankDetail->ifsc_code ?? '') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="branch_name">Branch Name</label>
                            <input type="text" class="form-control" id="branch_name" name="branch_name" value="{{ old('branch_name', $bankDetail->branch_name ?? '') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="account_number">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number', $bankDetail->account_number ?? '') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes">{{ old('notes', $bankDetail->notes ?? '') }}</textarea>
                        </div>

                        @if(isset($bankDetail) && $bankDetail)
                            <button type="submit" class="btn btn-primary">Update</button>
                        @else
                            <button type="submit" class="btn btn-primary">Save</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
