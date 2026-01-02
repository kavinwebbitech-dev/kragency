@extends('layouts.app')
@section('title', 'Whatsapp Link')

@section('content')
  

        <div class="container-fluid py-4">

            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="m-0">WhatsApp Link Settings</h3>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 25%">Name</th>
                                    <th>Link</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>WhatsApp Join Link</td>
                                    <td>{{ $data->link ?? 'Not Set' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                                            data-bs-target="#editForm">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="editForm" class="collapse">
                        <form method="POST" action="{{ route('admin.whatsapplink.save') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">WhatsApp Link</label>
                                <input type="text" name="link" class="form-control" value="{{ $data->link ?? '' }}"
                                    placeholder="https://wa.me/1234567890?text=Hi" required>
                            </div>

                            <div class="text-center">
                                <button class="btn btn-success px-4">Save</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

    </div>
@endsection

