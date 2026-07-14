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
                                        <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#editWhatsappForm">
                                            Edit
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Emergency Contact Number</td>
                                    <td>{{ $contact->link ?? 'Not Set' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#editEmergencyForm">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="editWhatsappForm" class="collapse mb-3">
                        <div class="card card-body">
                            <h5 class="mb-3">Edit WhatsApp Link</h5>

                            <form method="POST" action="{{ route('admin.whatsapplink.save') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">WhatsApp Link</label>
                                    <input
                                        type="text"
                                        name="link"
                                        class="form-control"
                                        value="{{ $data->link ?? '' }}"
                                        placeholder="https://wa.me/1234567890?text=Hi"
                                        required
                                    >
                                </div>

                                <button type="submit" class="btn btn-success">
                                    Save WhatsApp Link
                                </button>
                            </form>
                        </div>
                    </div>
                    <div id="editEmergencyForm" class="collapse">
                        <div class="card card-body">
                            <h5 class="mb-3">Edit Emergency Contact</h5>

                            <form method="POST" action="{{ route('admin.whatsapplink.save') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Emergency Contact Number</label>
                                    <input
                                        type="text"
                                        name="emergency_contact"
                                        class="form-control"
                                        value="{{ $contact->link ?? '' }}"
                                        placeholder="Enter Emergency Contact Number"
                                        required
                                    >
                                </div>

                                <button type="submit" class="btn btn-danger">
                                    Save Emergency Contact
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection