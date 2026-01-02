@extends('layouts.app')
@section('title', 'Import Result')
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Import Result</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Import Result</li>
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
                            <h3 class="card-title">Imported Customers</h3>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3" id="importResultTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="imported-tab" data-bs-toggle="tab" data-bs-target="#imported" type="button" role="tab" aria-controls="imported" aria-selected="true">Imported</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failed" type="button" role="tab" aria-controls="failed" aria-selected="false">Failed</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="importResultTabContent">
                                <div class="tab-pane fade show active" id="imported" role="tabpanel" aria-labelledby="imported-tab">
                                    @if(count($imported))
                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Mobile</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($imported as $i => $user)
                                                        <tr>
                                                            <td>{{ $i + 1 }}</td>
                                                            <td>{{ $user->name }}</td>
                                                            <td>{{ $user->mobile ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p>No customers were imported.</p>
                                    @endif
                                </div>
                                <div class="tab-pane fade" id="failed" role="tabpanel" aria-labelledby="failed-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">Failed Imports</h5>
                                        @if(count($failed))
                                            <form method="POST" action="{{ route('admin.customers.import.exportFailed') }}">
                                                @csrf
                                                <input type="hidden" name="failed_data" value="{{ base64_encode(json_encode($failed)) }}">
                                                <button type="submit" class="btn btn-warning btn-sm">Export Failed Records</button>
                                            </form>
                                        @endif
                                    </div>
                                    @if(count($failed))
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Mobile</th>
                                                        <th>Reason</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($failed as $i => $row)
                                                        <tr>
                                                            <td>{{ $i + 1 }}</td>
                                                            <td>{{ $row['name'] ?? '-' }}</td>
                                                            <td>{{ $row['mobile'] ?? '-' }}</td>
                                                            <td>{{ $row['reason'] ?? 'Duplicate or Invalid' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p>No failed imports.</p>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-primary mt-4">Back to Customers</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
