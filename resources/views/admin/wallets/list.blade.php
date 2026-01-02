@extends('layouts.app')
@section('title', __('customers/message.customer_wallets'))
@section('content')
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">{{ __('customers/message.customer_wallets') }}</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">List
                                {{ __('customers/message.customer_wallets') }}</li>
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
                                <h3 style="margin-top: 10px;" class="card-title ">
                                    {{ __('customers/message.customer_wallets') }}</h3>
                                <a href="{{ route('admin.wallet.add') }}" class="btn btn-primary float-end" name="save"
                                    value="create">Add Amount</a>
                                @php
                                    $userType = auth()->user()->user_type ?? null;
                                @endphp

                                @if ($userType === 'admin')
                                    <a href="{{ route('admin.wallet.deduct') }}" class="btn btn-danger float-end"
                                        style="margin-right:10px;">
                                        Deduct Amount
                                    </a>
                                @endif

                            </div>
                            <div class="card-body">
                                <table id ="usersTable" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Mobile</th>
                                            <th>Amount</th>
                                            <th>Date Created</th>
                                            <th>Action</th>
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
        $(function() {
            $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.wallet.get-record') }}",
                columns: [{
                        data: null,
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'balance',
                        name: 'balance'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(data, type, row) {
                            const viewUrl = `{{ route('admin.wallet.view-logs', ':id') }}`.replace(
                                ':id', row.user_id);
                            const editUrl = `{{ route('admin.wallet.view-logs', ':id') }}`.replace(
                                ':id', row.user_id);
                            const deleteUrl = `{{ route('admin.wallet.view-logs', ':id') }}`
                                .replace(':id', row.user_id);
                            return `
                                <a href="${viewUrl}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            `;
                        }
                    }
                ]
            });
        });
    </script>
@endpush
