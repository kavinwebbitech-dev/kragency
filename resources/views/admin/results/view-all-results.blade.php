@extends('layouts.app')
@section('title', 'View All Results')
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">View All Results</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View All Results</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header ">
                            <h3 style="margin-top: 10px;" class="card-title ">View All Results</h3>
                        </div>
                        <div class="card-body">
                            <table id ="providerTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Provider</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Result</th>
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
            $('#providerTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.view-all-results-data') }}",
                columns: [
                    { 
                        data: null, 
                        name: 'id',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'provider_name', name: 'provider_name' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'slot_time', name: 'slot_time' },
                    { data: 'result', name: 'result' }
                ]
            });
        });
    </script>
@endpush