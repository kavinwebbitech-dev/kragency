@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mt-3">
            <h3>Edit Sub Admin</h3>
            <a href="{{ route('admin.subadmin.index') }}" class="btn btn-secondary">Back</a>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.subadmin.update', $subadmin->id) }}">
            @csrf

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $subadmin->name) }}" class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $subadmin->email) }}" class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Mobile</label>
                <input type="text" name="mobile" value="{{ old('mobile', $subadmin->mobile) }}" class="form-control"
                    required>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="1" {{ $subadmin->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $subadmin->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label>New Password <small class="text-muted">(Leave blank to keep current)</small></label>
                <input type="password" name="password" class="form-control" placeholder="Enter new password">
            </div>

            <button class="btn btn-primary mb-2">Update</button>
        </form>
    </div>
@endsection
