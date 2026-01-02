@extends('layouts.app')
@section('title', 'Import Customers')
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Import Customers</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Import Customers</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Upload Excel File</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <a href="{{ asset('sample_customer_import.csv') }}" class="btn btn-info btn-sm" download>Download Sample Format</a>
                            </div>
                            <form action="{{ route('admin.customers.import.process') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="import_file" class="form-label">Excel File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="import_file" name="import_file" required accept=".xlsx,.xls,.csv">
                                    @error('import_file')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-success">Import</button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
