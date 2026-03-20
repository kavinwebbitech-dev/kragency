@extends('frontend.layouts.app')

@section('title', isset($userDetail) && $userDetail ? 'Edit Password' : 'Add Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-3">

                <div class="card-header bg-primary text-white text-center fw-bold">
                    {{ isset($userDetail) && $userDetail ? 'Change Password' : 'Set Password' }}
                </div>

                <div class="card-body p-4">

                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger text-center">
                            {{ session('error') }}
                        </div>
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

                    {{-- Form --}}
                    <form method="POST" action="{{ route('customer.password.store') }}">
                        @csrf

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" 
                                value="{{ old('name', $userDetail->name ?? '') }}" readonly>
                        </div>

                        {{-- Mobile --}}
                        <div class="mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" 
                                value="{{ old('mobile', $userDetail->mobile ?? '') }}" readonly>
                        </div>

                        {{-- Current Password --}}
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" 
                                name="current_password" placeholder="Enter current password" required>
                        </div>

                        {{-- New Password --}}
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" 
                                name="new_password" placeholder="Enter new password" required>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" 
                                name="new_password_confirmation" placeholder="Confirm new password" required>
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold">
                                Update Password
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection