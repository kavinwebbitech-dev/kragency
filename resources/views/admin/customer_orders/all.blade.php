@extends('layouts.app')
@section('title', 'All Orders')
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
                                    <button id="exportBtnAll" class="btn btn-success mt-4">Export Filtered</button>
                                </div>
                                <div class="col-md-3">
                                    <label for="customerNameFilterAll">Customer Name</label>
                                    <input type="text" id="customerNameFilterAll" class="form-control" placeholder="Enter customer name">
                                </div>
                                <div class="col-md-3">
                                    <label for="providerFilterAll">Provider</label>
                                    <select id="providerFilterAll" class="form-control">
                                        <option value="">All</option>
                                        @php $providers = $orders->pluck('scheduleProviderSlotTime.getProvider.name')->unique()->filter(); @endphp
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider }}">{{ $provider }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="timeFilterAll">Time</label>
                                    <select id="timeFilterAll" class="form-control">
                                        <option value="">All</option>
                                        @php $times = $orders->pluck('scheduleProviderSlotTime.slot_time')->unique()->filter(); @endphp
                                        @foreach($times as $time)
                                            <option value="{{ \Carbon\Carbon::parse($time)->format('h:i A') }}">{{ \Carbon\Carbon::parse($time)->format('h:i A') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <table id="ordersTableAll" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order Date</th>
                                        <th>Customer Name</th>
                                        <th>Mobile</th>
                                        <th>Provider</th>
                                        <th width="50px">Time</th>
                                        <th width="70px">Digit Added</th>
                                        <th>Quantity</th>
                                        <th>Total Amount</th>
                                        <th>Winning Amount</th>
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
                                var table = $('#ordersTableAll').DataTable({
                                    processing: true,
                                    serverSide: true,
                                    ajax: {
                                        url: '{{ route('admin.all-customer-orders.data') }}',
                                        data: function(d) {
                                            d.provider = $('#providerFilterAll').val();
                                            d.time = $('#timeFilterAll').val();
                                            d.customer_name = $('#customerNameFilterAll').val();
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
                                        { data: 'amount', name: 'amount' },
                                        { data: 'win_amount', name: 'win_amount' }
                                    ]
                                });
                                $('#providerFilterAll, #timeFilterAll').on('change', function() {
                                    table.ajax.reload();
                                });
                                $('#customerNameFilterAll').on('keyup change', function() {
                                    table.ajax.reload();
                                });
                                // Custom export button
                                $('#exportBtnAll').on('click', function() {
                                    var provider = $('#providerFilterAll').val();
                                    var time = $('#timeFilterAll').val();
                                    var customer_name = $('#customerNameFilterAll').val();
                                    var url = '{{ route('admin.all-customer-orders.export') }}';
                                    var params = [];
                                    if (provider) params.push('provider=' + encodeURIComponent(provider));
                                    if (time) params.push('time=' + encodeURIComponent(time));
                                    if (customer_name) params.push('customer_name=' + encodeURIComponent(customer_name));
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
