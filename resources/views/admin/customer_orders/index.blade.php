@extends('layouts.app')
@section('title', 'Today Customer Orders')
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Today's Customer Orders</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Customer Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-lg-4 col-12 mb-2">
                    <div class="small-box text-bg-primary">
                        <div class="inner">
                            <h3>{{ $todayTotalOrders ?? 0 }}</h3>
                            <p>Today's Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 mb-2">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>₹{{ number_format($todayTotalAmount ?? 0, 2) }}</h3>
                            <p>Total Amount for Today</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 mb-2">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>₹{{ number_format($todayWinningAmount ?? 0, 2) }}</h3>
                            <p>Winned Amount for Today</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Customer Orders (Today)</h3>
                        </div>
                        <div class="card-body">
                            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <button id="exportBtn" class="btn btn-success mt-4">Export Filtered</button>
                                </div>
                                <div class="col-md-3">
                                    <label for="customerNameFilter">Customer Name</label>
                                    <input type="text" id="customerNameFilter" class="form-control" placeholder="Enter customer name">
                                </div>
                                <div class="col-md-3">
                                    <label for="providerFilter">Provider</label>
                                    <select id="providerFilter" class="form-control">
                                        <option value="">All</option>
                                        @php $providers = $orders->pluck('scheduleProviderSlotTime.getProvider.name')->unique()->filter(); @endphp
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider }}">{{ $provider }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="timeFilter">Time</label>
                                    <select id="timeFilter" class="form-control">
                                        <option value="">All</option>
                                        @php $times = $orders->pluck('scheduleProviderSlotTime.slot_time')->unique()->filter(); @endphp
                                        @foreach($times as $time)
                                            <option value="{{ \Carbon\Carbon::parse($time)->format('h:i A') }}">{{ \Carbon\Carbon::parse($time)->format('h:i A') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <table id="ordersTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order Date</th>
                                        <th>Customer Name</th>
                                        <th>Mobile</th>
                                        <th>Provider</th>
                                        <th>Time</th>
                                        <th>Digit Added</th>
                                        <th>Quantity</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
                        <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
                        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
                        <script>
                            $(document).ready(function() {
                                var table = $('#ordersTable').DataTable({
                                    processing: true,
                                    serverSide: true,
                                    ajax: {
                                        url: '{{ route('admin.customer-orders.data') }}',
                                        data: function(d) {
                                            d.provider = $('#providerFilter').val();
                                            d.time = $('#timeFilter').val();
                                            d.customer_name = $('#customerNameFilter').val();
                                        }
                                    },
                                    dom: 'Bfrtip',
                                    buttons: [],
                                    columns: [
                                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                                        { data: 'order_date', name: 'order_date' },
                                        { data: 'customer_name', name: 'customer_name' },
                                        { data: 'mobile', name: 'mobile' },
                                        { data: 'provider', name: 'provider' },
                                        { data: 'time', name: 'time' },
                                        { data: 'digit_added', name: 'digit_added' },
                                        { data: 'quantity', name: 'quantity' },
                                        { data: 'amount', name: 'amount' }
                                    ]
                                });
                                $('#providerFilter, #timeFilter').on('change', function() {
                                    table.ajax.reload();
                                });
                                $('#customerNameFilter').on('keyup change', function() {
                                    table.ajax.reload();
                                });
                                // Custom export button
                                $('#exportBtn').on('click', function() {
                                    var provider = $('#providerFilter').val();
                                    var time = $('#timeFilter').val();
                                    var url = '{{ route('admin.customer-orders.export') }}';
                                    var params = [];
                                    if (provider) params.push('provider=' + encodeURIComponent(provider));
                                    if (time) params.push('time=' + encodeURIComponent(time));
                                    if (params.length) url += '?' + params.join('&');
                                    window.open(url, '_blank');
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
