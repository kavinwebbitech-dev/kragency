@extends('layouts.app')
@section('title','View KYC')
@section('content')
<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3>View KYC</h3>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.kyc.index') }}">KYC List</a>
                        </li>
                        <li class="breadcrumb-item active">
                            View
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">

        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h5>User KYC Details</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label><strong>Name</strong></label>
                            <p>{{ $kycDetail->user->name }}</p>
                        </div>

                        <div class="col-md-6">
                            <label><strong>Email</strong></label>
                            <p>{{ $kycDetail->user->email }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Aadhar Front -->
                        <div class="col-md-4 text-center mb-3">
                            <label><strong>Aadhar Front</strong></label><br>
                            <a href="{{ asset($kycDetail->aadhar_front) }}" target="_blank">
                                <img src="{{ asset($kycDetail->aadhar_front) }}"
                                    class="img-thumbnail"
                                    style="height:250px;width:100%;object-fit:contain;">
                            </a>
                        </div>

                        <!-- Aadhar Back -->
                        <div class="col-md-4 text-center mb-3">
                            <label><strong>Aadhar Back</strong></label><br>
                            <a href="{{ asset($kycDetail->aadhar_back) }}" target="_blank">
                                <img src="{{ asset($kycDetail->aadhar_back) }}"
                                    class="img-thumbnail"
                                    style="height:250px;width:100%;object-fit:contain;">
                            </a>
                        </div>

                        <!-- PAN Front -->
                        <div class="col-md-4 text-center mb-3">
                            <label><strong>PAN Front</strong></label><br>

                            <a href="{{ asset($kycDetail->pan_front) }}" target="_blank">
                                <img src="{{ asset($kycDetail->pan_front) }}"
                                    class="img-thumbnail"
                                    style="height:250px;width:100%;object-fit:contain;">
                            </a>
                        </div>

                        <!-- PAN Back -->
                        <div class="col-md-4 text-center mb-3">
                            <label><strong>PAN Back</strong></label><br>

                            <a href="{{ asset($kycDetail->pan_back) }}" target="_blank">
                                <img src="{{ asset($kycDetail->pan_back) }}"
                                    class="img-thumbnail"
                                    style="height:250px;width:100%;object-fit:contain;">
                            </a>
                        </div>

                        <!-- Photo -->
                        <div class="col-md-4 text-center mb-3">
                            <label><strong>Photo</strong></label><br>

                            <a href="{{ asset($kycDetail->photo) }}" target="_blank">
                                <img src="{{ asset($kycDetail->photo) }}"
                                    class="img-thumbnail"
                                    style="height:250px;width:100%;object-fit:contain;">
                            </a>
                        </div>
                    </div>
                    <form action="{{ route('admin.kyc.updateStatus', $kycDetail->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="status"><strong>Status</strong></label>
                            <select name="status" id="status" class="form-control">
                                <option value="pending" {{ $kycDetail->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $kycDetail->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $kycDetail->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>

                    <hr>

                    <a href="{{ route('admin.kyc.index') }}" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection