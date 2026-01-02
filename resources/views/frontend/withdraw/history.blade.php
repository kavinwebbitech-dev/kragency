@extends('frontend.layouts.app')

@section('title', 'Withdraw History')

@section('content')

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <style>
            /* Your custom CSS for this view */
            .receipt {
                margin: 20px 0;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            }

            .receipt-table {
                width: 100%;
                border-collapse: collapse;
            }

            .receipt-row {
                display: table-row;
            }

            .receipt-cell {
                display: table-cell;
                padding: 12px 15px;
                border-bottom: 1px solid #e0e0e0;
                vertical-align: middle;
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

            .dataTables_wrapper {
                padding: 0;
            }

            .dataTables_filter input {
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 5px 10px;
                margin-left: 10px;
            }

            .dataTables_length select {
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 5px;
            }

            .dataTables_paginate .paginate_button {
                padding: 5px 10px;
                margin: 0 2px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f8f9fa;
            }

            .dataTables_paginate .paginate_button.current {
                background: #6f42c1;
                color: white !important;
                border: 1px solid #6f42c1;
            }

            .dataTables_info {
                padding: 10px 0;
                color: #6c757d;
            }

            /* Highlight row on hover */
            .receipt-row:not(.receipt-header-row):hover {
                background-color: #f5f5f5;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .receipt-cell {
                    padding: 8px 10px;
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
            }

            /* More custom styles */
        </style>
        </style>
    @endpush


    <section class="results mt-4">
        <div class="lottery-result result-page">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="receipt">
                            <table id="withdrawTable" class="receipt-table">
                                <thead>
                                    <tr class="receipt-row receipt-header-row">
                                        <th class="receipt-cell">Date</th>
                                        <th class="receipt-cell">Amount</th>
                                        <th class="receipt-cell">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($withdraws as $withdraw)
                                        <tr>
                                            <td class="receipt-cell">{{ $withdraw->created_at->format('M d, Y h:i A') }}
                                            </td>
                                            <td class="receipt-cell">{{ number_format($withdraw->amount, 2) }}</td>
                                            <td class="receipt-cell">
                                                @if ($withdraw->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($withdraw->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @else
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
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
    </section>

@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#withdrawTable').DataTable({
                ordering: false,
                pageLength: 10
            });
        });
    </script>
@endpush
