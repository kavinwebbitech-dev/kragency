@extends('layouts.app')
@section('title', 'Send Notification')
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Send Notification</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Send Notification</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show auto-hide-alert" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show auto-hide-alert" role="alert">
            {{ session('error') }}
        </div>
    @endif
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Send Notification</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.notification.send') }}" method="POST">
                                @csrf

                                <div class="row">

                                    <!-- Title -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Notification Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>

                                    <!-- Type -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Notification Type</label>
                                        <select name="type" class="form-control">
                                            <option value="general">General</option>
                                            <option value="offer">Offer</option>
                                            <option value="reminder">Reminder</option>
                                            <option value="alert">Alert</option>
                                        </select>
                                    </div>

                                    <!-- Body -->
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea name="body" class="form-control" rows="3" required></textarea>
                                    </div>

                                    <!-- Screen -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Screen Name</label>
                                        <select name="screen" class="form-control" required>
                                            <option value="">-- Select Screen --</option>
                                            <option value="Home">Home</option>
                                            <option value="Results">Results</option>
                                        </select>
                                    </div>

                                    <!-- Tab -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tab Name</label>
                                        <select name="tab" class="form-control" required>
                                            <option value="">-- Select Tab --</option>
                                            <option value="Home">Home</option>
                                            <option value="Results">Results</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        Send Notification
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    setTimeout(function () {
        let alerts = document.querySelectorAll('.auto-hide-alert');
        alerts.forEach(function(alert) {
            alert.classList.remove('show');
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000); // 5 seconds
</script>
@endsection