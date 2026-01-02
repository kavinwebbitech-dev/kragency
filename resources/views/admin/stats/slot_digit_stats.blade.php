@extends('layouts.app')
@section('title', 'Slot Digit Stats')
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Slot Digit Stats</h3></div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <!-- <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Search by Schedule Provider</h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.slot-digit-stats', ['schedule_provider_id' => '']) }}" onsubmit="event.preventDefault(); if(this.schedule_provider_id.value) { window.location.href=this.action.replace(/\/$/, '') + '/' + this.schedule_provider_id.value; }">
                                <div class="mb-3">
                                    <label for="schedule_provider_id" class="form-label">Schedule Provider ID</label>
                                    <input type="number" class="form-control" id="schedule_provider_id" name="schedule_provider_id" value="{{ $scheduleProviderId ?? '' }}" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Get Stats</button>
                            </form>
                        </div>
                    </div> -->
                    @if($stats)
                        <div class="card">
                            <div class="card-header"><h4>Stats Result</h4></div>
                            <div class="card-body">
                                @if(isset($stats['total_amount']))
                                    <div class="mb-3">
                                        <strong>Total Amount Placed by All Customers:</strong>
                                        <span>{{ number_format($stats['total_amount'], 2) }}</span>
                                    </div>
                                @endif
                                @foreach($stats as $slot => $data)
                                    @if($slot === 'not_in_any_slot' || $slot === 'total_amount' || !is_array($data) || !isset($data['total']))
                                        @continue
                                    @endif
                                    <h5>Slot: {{ $slot }} (Total: {{ $data['total'] }})</h5>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Digit</th>
                                                    <th>Count</th>
                                                    <th>Percent (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['digits'] as $row)
                                                    <tr>
                                                        <td>{{ $row['digit'] }}</td>
                                                        <td>{{ $row['count'] }}</td>
                                                        <td>{{ $row['percent'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                        @if(isset($stats['not_in_any_slot']) && is_array($stats['not_in_any_slot']) && count($stats['not_in_any_slot']))
                            <div class="mb-4">
                                <strong>4-digit numbers not in any slot:</strong>
                                <div style="word-break:break-all;">{{ implode(', ', $stats['not_in_any_slot']) }}</div>
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
