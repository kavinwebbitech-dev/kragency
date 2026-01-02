@extends('layouts.app')
@section('title', 'Withdraw Requests')
@section('content')
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0">Withdraw Requests</h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Withdraw Requests</li>
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
                            <div class="card-header ">
                                <h3 style="margin-top: 10px;" class="card-title ">Withdraw Requests</h3>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                                <table id="usersTable" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($requests as $req)
                                            <tr>
                                                <td>{{ $req->id }}</td>
                                                <td>{{ $req->user->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($req->amount, 2) }}</td>
                                                <td>
                                                    @if($req->status == 'pending')
                                                        Pending
                                                    @elseif($req->status == 'approved')
                                                        Approved
                                                    @else
                                                        Rejected
                                                    @endif
                                                </td>
                                                <td>{{ $req->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    @if($req->status == 'pending')
                                                    <form method="POST" action="{{ route('admin.withdraw.update', $req->id) }}" style="display:inline-block;">
                                                        @csrf
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.withdraw.update', $req->id) }}" style="display:inline-block;">
                                                        @csrf
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                    </form>
                                                    @else
                                                        <span class="text-muted">-</span>
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
        </div>
    </main>
    @endsection
    @push('scripts')
    <script>
        $(function () {
            $('#usersTable').DataTable();
        });
    </script>
    @endpush
