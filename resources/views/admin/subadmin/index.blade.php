@extends('layouts.app')

@section('title', 'Sub Admins')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mt-2">
        <h4 class="mb-0">Sub Admins</h4>
        <a href="{{ route('admin.subadmin.create') }}" class="btn btn-primary">
            Add Sub Admin
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mt-2">
        <div class="card-header">
            <h5 class="mb-0">Sub Admin List</h5>
        </div>

        <div class="card-body ">

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th style="width:80px;">Status</th>
                            <th style="width:140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subadmins as $i => $admin)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->mobile }}</td>
                            <td>
                                <span class="badge {{ $admin->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $admin->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.subadmin.edit', $admin->id) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.subadmin.destroy', $admin->id) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
