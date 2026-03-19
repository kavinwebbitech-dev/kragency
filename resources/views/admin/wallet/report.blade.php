@extends('layouts.app')
@section('title', 'Wallet Report')

@section('content')
    <main class="app-main">

        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-sm-6">
                        <h3 class="mb-0">Wallet Transaction Report</h3>
                    </div>

                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Wallet Report</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>


        <div class="app-content">
            <div class="container-fluid">

                {{-- FILTER CARD --}}
                <div class="card mb-4">

                    <div class="card-header">
                        <h3 class="card-title">Filter Records</h3>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" id="from_date" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" id="to_date" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>User</label>
                                <select id="user_id" class="form-control">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Created By</label>
                                <select id="created_by" class="form-control">
                                    <option value="">All</option>
                                    @foreach ($admins as $admin)
                                        <option value="{{ $admin->userDetail?->id }}">
                                            {{ $admin->userDetail?->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3" style="margin-top:30px">

                                <button id="filter" class="btn btn-primary">
                                    <i class="fa fa-search"></i> Filter
                                </button>

                                <button id="export" class="btn btn-success">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>

                            </div>

                        </div>

                    </div>
                </div>


                {{-- SUMMARY CARD --}}
                {{-- <div class="row mb-4">

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Total Records</h6>
                                <h4 id="total_records">0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Total Amount</h6>
                                <h4 id="total_balance">0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Total Bonus</h6>
                                <h4 id="total_bonus">0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Max Amount</h6>
                                <h4 id="max_balance">0</h4>
                            </div>
                        </div>
                    </div>

                </div> --}}


                {{-- TABLE CARD --}}
                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">Wallet Transactions</h3>
                    </div>

                    <div class="card-body">

                        <table id="walletTable" class="table table-bordered table-striped">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Created By</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Bonus</th>
                                    <th>Description</th>
                                    <th>Date</th>
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
        $(function() {

            var table = $('#walletTable').DataTable({

                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('admin.wallet.report.data') }}",
                    data: function(d) {

                        d.user_id = $('#user_id').val();
                        d.created_by = $('#created_by').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();

                    }
                },

                columns: [

                    {
                        data: 'id'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'user_info'
                    },
                    {
                        data: 'type_fmt'
                    },
                    {
                        data: 'amount_fmt'
                    },
                    {
                        data: 'bonus_fmt'
                    },
                    {
                        data: 'description'
                    },
                    {
                        data: 'created_at_fmt'
                    }

                ]

            });

            $('#filter').click(function() {

                table.draw();
                loadSummary();

            });

           $('#export').click(function () {

                var url = "{{ route('admin.wallet.report.export') }}"
                    + "?user_id=" + $('#user_id').val()
                    + "&created_by=" + $('#created_by').val()
                    + "&from_date=" + $('#from_date').val()
                    + "&to_date=" + $('#to_date').val();

                window.location.href = url;

            });

            // function loadSummary() {

            //     $.get("{{ route('admin.wallet.report.summary') }}", {

            //         user_id: $('#user_id').val(),
            //         from_date: $('#from_date').val(),
            //         to_date: $('#to_date').val()

            //     }, function(res) {

            //         $('#total_records').text(res.total_records);
            //         $('#total_balance').text(res.total_amount);
            //         $('#total_bonus').text(res.total_bonus);
            //         $('#max_balance').text(res.max_amount);

            //     });
            // }
            // loadSummary();
        });
    </script>
@endpush
