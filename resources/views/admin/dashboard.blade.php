@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<main class="app-main" style="margin-top: 5%;">
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="app-content">
                        <!--begin::Container-->
                        <div class="container-fluid">
                            <!--begin::Row-->
                            <div class="row">
                                <!--begin::Col-->
                                <div class="col-lg-4 col-12 mb-4">
                                    <div class="small-box text-bg-primary">
                                        <div class="inner">
                                            <h3>{{ $activeUsers ?? 0 }}</h3>
                                            <p>Active Users</p>
                                        </div>
                                        <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                            More info <i class="bi bi-link-45deg"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-12 mb-4">
                                    <div class="small-box text-bg-success">
                                        <div class="inner">
                                            <h3>₹{{ number_format($totalOrderedAmount ?? 0, 2) }}</h3>
                                            <p>Total Ordered Amount</p>
                                        </div>
                                        <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                            More info <i class="bi bi-link-45deg"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-12 mb-4">
                                    <div class="small-box text-bg-warning">
                                        <div class="inner">
                                            <h3>₹{{ number_format($totalWinningAmount ?? 0, 2) }}</h3>
                                            <p>Total Winning Amount</p>
                                        </div>
                                        <a href="#" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                                            More info <i class="bi bi-link-45deg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')

@endpush