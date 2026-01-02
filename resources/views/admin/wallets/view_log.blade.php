@extends('layouts.app')
@section('title', __('customers/message.view_tranaction_logs'))
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">{{ __('customers/message.view_tranaction_logs') }}</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">List {{ __('customers/message.view_tranaction_logs') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header ">
                            <h3 style="margin-top: 10px;" class="card-title ">{{ __('customers/message.view_tranaction_logs') }}</h3>
                        </div>
                        <div class="card-body">
                            <table id ="usersTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
 <script>
        $(function () {
            $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.wallet.view-logs', ':id') }}".replace(':id', "{{ $user_id }}"),
                columns: [
                    { 
                        data: null, 
                        name: 'id',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'amount', name: 'amount' },
                    { data: 'type', name: 'type' },
                    { data: 'description', name: 'description' },
                    { data: 'created_at', name: 'created_at' }
                ]
            });
        });
    </script>
@endpush