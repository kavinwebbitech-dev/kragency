@extends('frontend.layouts.app')
@section('title', 'Home')
@section('content')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* ================= GENERAL TABLE STYLES ================= */
    .receipt {
        margin: 20px 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px; /* ensures horizontal scroll on small screens */
    }

    .receipt-row {
        display: table-row;
    }

    .receipt-cell {
        display: table-cell;
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: middle;
        white-space: nowrap; /* prevents breaking cell content */
    }

    .receipt-header-row {
        background-color: #6f42c1;
        color: white;
        font-weight: bold;
    }

    .receipt-total-row {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    /* ================= DATATABLES STYLES ================= */
    .dataTables_wrapper {
        padding: 0;
        width: 100%;
    }

    .dataTables_filter input,
    .dataTables_length select {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px 10px;
        margin-left: 10px;
    }

    /* Pagination styles */
    .dataTables_paginate .paginate_button {
        padding: 6px 12px;
        margin: 2px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: #f8f9fa;
        color: #333 !important;
        cursor: pointer;
        transition: all 0.2s;
    }

    .dataTables_paginate .paginate_button:hover {
        background-color: #6f42c1;
        color: #fff !important;
        border-color: #6f42c1;
    }

    .dataTables_paginate .paginate_button.current {
        background-color: #6f42c1;
        color: #fff !important;
        border: 1px solid #6f42c1;
    }

    .dataTables_info {
        padding: 10px 0;
        color: #6c757d;
    }

    /* ================= ROW HOVER ================= */
    .receipt-row:not(.receipt-header-row):hover {
        background-color: #f5f5f5;
    }

    /* ================= RESPONSIVE TABLE WRAPPER ================= */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* ================= MOBILE ADJUSTMENTS ================= */
    @media (max-width: 768px) {
        .receipt-cell {
            padding: 8px 10px;
            font-size: 13px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: center;
            float: none !important;
            padding-top: 10px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            text-align: center;
            margin-bottom: 10px;
        }

        .dataTables_paginate .paginate_button {
            padding: 4px 8px;
            margin: 1px;
            font-size: 12px;
        }
    }
</style>
@endpush

<section class="results mt-4">
    <div class="lottery-result result-page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="receipt table-responsive"> <!-- Added responsive wrapper -->
                        <table id="receiptTable" class="receipt-table">
                            <thead>
                                <tr class="receipt-row receipt-header-row">
                                    <th>ID</th>
                                    <th>Order Date</th>
                                    <th>Provider</th>
                                    <th>Time</th>
                                    <th>Digit</th>
                                    <th>Entered Digit</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Winning Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $key => $result)
                                    <tr>
                                        <td class="receipt-cell">{{ $key + 1 }}</td>
                                        <td class="receipt-cell">{{ \Carbon\Carbon::parse($result->created_at)->format('M d, Y h:i A') }}</td>
                                        <td class="receipt-cell">{{ $result->provider_name }}</td>
                                        <td class="receipt-cell">
                                            @if($result->slot_time)
                                                {{ \Carbon\Carbon::parse($result->slot_time)->format('h:i A') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="receipt-cell">{{ $result->game_digits }}</td>
                                        <td class="receipt-cell">{{ $result->digits }}</td>
                                        <td class="receipt-cell">{{ $result->quantity }}</td>
                                        <td class="receipt-cell">{{ $result->particular_slot_amount }}</td>
                                        <td class="receipt-cell">{{ $result->win_amount }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#receiptTable').DataTable({
            "scrollX": true,          // Enable horizontal scrolling
            "pagingType": "full_numbers", // Full pagination (first, prev, next, last)
            "pageLength": 10,         // Default rows per page
            "lengthMenu": [5, 10, 25, 50], // Rows per page options
            "responsive": true        // Makes table responsive
        });
    });
</script>
@endpush
