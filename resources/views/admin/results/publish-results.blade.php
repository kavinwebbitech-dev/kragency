@extends('layouts.app')
@section('title', 'Publish Results')
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Publish Results</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Publish Results</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="app-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-lg-3 col-12 mb-2">
                    <div class="small-box text-bg-primary">
                        <div class="inner">
                            <h3>{{ $todayTotalOrders ?? 0 }}</h3>
                            <p>Today's Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-12 mb-2">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>₹{{ number_format($todayTotalAmount ?? 0, 2) }}</h3>
                            <p>Total Amount for Today</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-12 mb-2">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>₹{{ number_format($todayWinningAmount ?? 0, 2) }}</h3>
                            <p>Winned Amount for Today</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-12 mb-2">
                    <div class="small-box text-bg-danger">
                        <div class="inner">
                            <h3>₹{{ number_format($todayProfit ?? 0, 2) }}</h3>
                            <p>Profit for Today</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header ">
                            <h3 style="margin-top: 10px;" class="card-title ">Publish Results</h3>
                        </div>
                        <div class="card-body">
                            <table id ="providerTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Provider</th>
                                        <th>Time</th>
                                        <th>Result</th>
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

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
@push('scripts')
    <script>
        $(function () {
            $('#providerTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.publish-result.get-record') }}",
                columns: [
                    { 
                        data: null, 
                        name: 'id',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'provider_name', name: 'provider_name' },
                    { data: 'slot_time', name: 'slot_time' },
                    { data: 'result', name: 'result' },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function (data, type, row) {
                            let actions = '';
                            if (!(row.result && row.result !== '')) {
                                const editResult = `{{ route('admin.update-result', ':provider_id') }}`.replace(':provider_id', row.id);
                                actions += `
                                    <a href="${editResult}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                `;
                            }
                            // View Stats button
                            const viewStats = `{{ route('admin.slot-digit-stats', ':provider_id') }}`.replace(':provider_id', row.id);
                            actions += `
                                <a href="${viewStats}" class="btn btn-sm btn-info ms-1" target="_blank" title="View Stats">
                                    <i class="bi bi-bar-chart"></i> Stats
                                </a>
                            `;
                            // Delete button for future times only
                            if (row.slot_time && moment(row.slot_time, 'HH:mm:ss').isAfter(moment())) {
                                actions += `
                                    <button class="btn btn-sm btn-danger ms-1 delete-provider" data-id="${row.id}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                `;
                            }
                            return actions;
                        }
                    }
                ]
            });
        });

        $(document).on('click', '.delete-provider', function() {
            var id = $(this).data('id');
            if (confirm('Are you sure you want to delete this future schedule?')) {
                $.ajax({
                    url: '/admin/publish-results/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            $('#providerTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error', 'Could not delete record.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Could not delete record.', 'error');
                    }
                });
            }
        });
    </script>
@endpush