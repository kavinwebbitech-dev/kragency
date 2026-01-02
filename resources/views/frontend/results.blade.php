@extends('frontend.layouts.app')

@section('title', 'Home')

@section('content')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
/* ================= PAGE HEADER ================= */
.page-header {
    background: linear-gradient(135deg, #4e54c8, #845ef7);
    color: #fff;
    padding: 22px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 15px 35px rgba(78,84,200,0.35);
}

/* ================= CARD ================= */
.receipt {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.08);
}

/* ================= TABLE HEADER ================= */
table.dataTable thead {
    background: linear-gradient(135deg, #4e54c8, #4e54c8);
    color: #fff;
}

table.dataTable thead th {
    padding: 16px;
    font-weight: 600;
    border-bottom: none;
}

/* ================= TABLE BODY ================= */
table.dataTable tbody td {
    padding: 14px;
    font-size: 14px;
}

table.dataTable tbody tr:hover {
    background: #f3f0ff;
}

/* ================= TOP BAR ================= */
.dt-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

/* ================= SHOW ENTRIES ================= */
.dataTables_length label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #4e54c8;
}

.dataTables_length select {
    height: 42px;
    padding: 6px 36px 6px 14px;
    border-radius: 12px;
    border: 1px solid #d0d4ff;
    font-weight: 600;
    cursor: pointer;
    appearance: none;
    background:
        linear-gradient(45deg, transparent 50%, #4e54c8 50%),
        linear-gradient(135deg, #4e54c8 50%, transparent 50%);
    background-position:
        calc(100% - 18px) 18px,
        calc(100% - 12px) 18px;
    background-size: 6px 6px;
    background-repeat: no-repeat;
}

/* ================= SEARCH ================= */
.dataTables_filter {
    max-width: 340px;
    width: 100%;
}

.dataTables_filter label {
    position: relative;
    width: 100%;
}

.dataTables_filter input {
    width: 100%;
    height: 44px;
    padding: 8px 16px 8px 46px;
    border-radius: 14px;
    border: 1px solid #d0d4ff;
}

.dataTables_filter label::before {
    content: "";
    position: absolute;
    left: 16px;
    top: 50%;
    width: 18px;
    height: 18px;
    transform: translateY(-50%);
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23888' viewBox='0 0 24 24'%3E%3Cpath d='M21 20l-5.6-5.6a7 7 0 10-1.4 1.4L20 21zM10 16a6 6 0 110-12 6 6 0 010 12z'/%3E%3C/svg%3E") no-repeat center;
    background-size: contain;
}

/* ================= PAGINATION – DESKTOP ================= */
.dataTables_paginate {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
}

.dataTables_paginate .paginate_button {
    min-width: 40px;
    padding: 8px 14px;
    border-radius: 12px;
    border: 1px solid #d0d4ff !important;
    background: #f4f6ff !important;
    color: #4e54c8 !important;
    font-weight: 600;
    transition: all 0.25s ease;
}

.dataTables_paginate .paginate_button:hover {
    background: #e4e7ff !important;
    border-color: #4e54c8 !important;
}

.dataTables_paginate .paginate_button.current {
    background: #ffffff !important;
    color: #4e54c8 !important;
    border: 2px solid #4e54c8 !important;
    box-shadow: 0 6px 18px rgba(78,84,200,0.25);
}

.dataTables_paginate .paginate_button.disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

/* ================= RESPONSIVE ICON ================= */
table.dataTable.dtr-inline.collapsed
> tbody
> tr
> td.dtr-control:before {
    background: linear-gradient(135deg, #4e54c8, #4e54c8);
    top: 50%;
    transform: translateY(-50%);
}

/* ================= MOBILE PAGINATION – BEST ================= */
/* ================= MOBILE PAGINATION – RIGHT ALIGNED ================= */
@media (max-width: 576px) {
    .dt-top-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .dataTables_info {
        text-align: center;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .dataTables_paginate {
        width: 100%;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end; /* RIGHT ALIGN */
    }

    .dataTables_paginate .paginate_button {
        min-width: 36px;
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 10px;
    }

    .dataTables_paginate .paginate_button.previous,
    .dataTables_paginate .paginate_button.next {
        font-size: 0;
        padding: 6px 12px;
    }

    .dataTables_paginate .paginate_button.previous::before {
        content: "‹";
        font-size: 18px;
        font-weight: 700;
    }

    .dataTables_paginate .paginate_button.next::before {
        content: "›";
        font-size: 18px;
        font-weight: 700;
    }
}

</style>
@endpush

<section class="results mt-4">
    <div class="container">

        <div class="page-header">
            <h3 class="mb-1 text-white">Lottery Results</h3>
            <small>Live & historical result records</small>
        </div>

        <div class="receipt">
            <table id="receiptTable" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>Provider</th>
                        <th>Time</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(function () {
    $('#receiptTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: {
            details: { type: 'column', target: 0 }
        },
        dom:
            "<'dt-top-bar'l f>" +
            "<'row'<'col-12'tr>>" +
            "<'row mt-3'<'col-12'i><'col-12'p>>",
        language: {
            search: "",
            searchPlaceholder: "Search results...",
            lengthMenu: "Show _MENU_",
            info: "Showing _START_– _END_ of _TOTAL_",
            processing: "Loading..."
        },
        pageLength: 10,
        columnDefs: [{
            className: 'dtr-control',
            orderable: false,
            searchable: false,
            targets: 0
        }],
        ajax: "{{ route('customer.get-results') }}",
        columns: [
            {
                data: null,
                render: (data, type, row, meta) =>
                    meta.row + meta.settings._iDisplayStart + 1
            },
            { data: 'provider_name', name: 'provider_name' },
            { data: 'slot_time', name: 'slot_time' },
            { data: 'result', name: 'result' }
        ]
    });
});
</script>
@endpush
