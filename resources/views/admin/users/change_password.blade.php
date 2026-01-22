@extends('layouts.app')
@section('title', __('customers/message.customers'))
@section('content')
<div class="card">
    <div class="card-header">
        <h4>Change Password</h4>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('danger'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card-body">
        <form method="POST" action="{{ route('admin.change-password.update') }}">
            @csrf

            <div class="mb-3">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection
