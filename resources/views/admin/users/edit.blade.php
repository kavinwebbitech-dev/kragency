@extends('layouts.app')
@section('title', __('customers/message.customers'))
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">{{ __('customers/message.customers') }}</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">List {{ __('customers/message.customers') }}</li>
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
                    <div class="card-header"><div class="card-title">{{ __('customers/message.update_customer') }}</div></div>
                        <form action="{{ route('admin.users.edit', $user->id) }}" method="post">
                            @csrf
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="name" class="col-sm-2 col-form-label">Name<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="mobile" class="col-sm-2 col-form-label">Mobile Number<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <input
                                            type="tel"
                                            class="form-control"
                                            id="mobile"
                                            name="mobile"
                                            value="{{ old('mobile', $user->mobile) }}"
                                            required
                                            pattern="[0-9]{10}"
                                            placeholder="Enter 10-digit mobile number"
                                        />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="password" class="col-sm-2 col-form-label">New Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="password" name="password" />
                                        <small class="text-muted">Leave blank if you don't want to change password.</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="password_confirmation" class="col-sm-2 col-form-label">Confirm Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="status" class="col-sm-2 col-form-label">Status<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <select class="form-select" id="status" name="status">
                                            <option value="1" {{ old('status', $user->status) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $user->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                
                            </div>
                    
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">Update</button>
                                <a href="{{ route('admin.users.index') }}" class="btn float-end">Cancel</a>
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

@endpush