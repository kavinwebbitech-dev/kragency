@extends('frontend.layouts.app')
@section('title', 'Home')

@section('content')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

<style>
/* ================= RECEIPT CONTAINER ================= */
.receipt {
    margin: 20px 0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    overflow: hidden;
}

/* ================= TABLE ================= */
.receipt-table {
    width: 100%;
    min-width: 650px; /* IMPORTANT for mobile scroll */
    border-collapse: collapse;
}

.receipt-cell {
    padding: 14px 16px;
    border-bottom: 1px solid #e9ecef;
    white-space: nowrap;
    font-size: 14px;
}

/* ================= HEADER ================= */
.receipt-header-row th {
    background: linear-gradient(135deg, #6f42c1, #5a34a3);
    color: #fff;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* ================= ROW HOVER ================= */
tbody tr:hover {
    background-color: #f8f9ff;
}

/* ================= AMOUNT COLORS ================= */
.transaction-positive {
    color: #198754;
    font-weight: 600;
}

.transaction-negative {
    color: #dc3545;
    font-weight: 600;
}

/* ================= DATATABLES CLEANUP ================= */
.dataTables_wrapper {
    padding: 10px;
}

.dataTables_filter input,
.dataTables_length select {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 14px;
}

.dataTables_paginate .paginate_button {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    margin: 0 3px;
}

.dataTables_paginate .paginate_button.current {
    background: #6f42c1 !important;
    color: #fff !important;
    border-color: #6f42c1;
}

/* ================= MOBILE SCROLL ================= */
.table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* ================= CUSTOM SCROLLBAR ================= */
.table-scroll::-webkit-scrollbar {
    height: 6px;
}

.table-scroll::-webkit-scrollbar-thumb {
    background: #6f42c1;
    border-radius: 10px;
}

.table-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
}

/* ================= MOBILE TWEAKS ================= */
@media (max-width: 768px) {

    .receipt {
        border-radius: 10px;
    }

    .receipt-cell {
        padding: 12px;
        font-size: 13px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        text-align: center;
        float: none !important;
        margin-top: 10px;
    }

    .dataTables_filter,
    .dataTables_length {
        text-align: center;
        margin-bottom: 10px;
    }
}
</style>
@endpush


<section class="results mt-4">
    <div class="lottery-result result-page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">

                    <div class="receipt">
                        <div class="table-scroll">
                            <table id="receiptTable" class="receipt-table">
                                <thead>
                                    <tr class="receipt-header-row">
                                        <th class="receipt-cell">Date</th>
                                        <th class="receipt-cell">Description</th>
                                        <th class="receipt-cell text-end">Amount ₹</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td class="receipt-cell">
                                                {{ $transaction->created_at->format('M d, Y h:i A') }}
                                            </td>
                                            <td class="receipt-cell">
                                                {{ $transaction->description }}
                                            </td>
                                            <td class="receipt-cell text-end {{ $transaction->amount >= 0 ? 'transaction-positive' : 'transaction-negative' }}">
                                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

@endsection


@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#receiptTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        responsive: false, // IMPORTANT: keeps same layout
        autoWidth: false
    });
});
</script>
@endpush
