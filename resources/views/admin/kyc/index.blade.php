@extends('layouts.app')
@section('title', 'KYC List')

@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">KYC List</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">KYC List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">KYC Details</h5>
                </div>

                <div class="card-body">
                    <table class="table table-bordered" id="kycTable">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>User Name</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
$(function () {

    $('#kycTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.kyc.index') }}",
        columns: [
            {
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta){
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data:'name',
                name:'name'
            },
            {
                data:'created_at',
                name:'created_at'
            },
            {
                data:'status',
                name:'status',
                orderable:false,
                searchable:false
            },
            {
                data:'action',
                name:'action',
                orderable:false,
                searchable:false
            }
        ]
    });

});
</script>
@endpush