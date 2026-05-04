@extends('layouts.app')

@section('title', 'Reports - Bismillahi Ramadan Tracker')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Reports & Insights</h2>
                <p class="text-muted mb-0">Track your progress and celebrate your achievements.</p>
            </div>
            <div>
            <a href="{{ route('reports.daily') }}" class="btn btn-outline-primary me-2">
    <i class="bi bi-calendar-week me-1"></i> Daily Report
</a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="stat-number">{{ $statistics['total_days_tracked'] }}</div>
            <div class="stat-label">Days Tracked</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="stat-number">{{ $statistics['perfect_days'] }}</div>
            <div class="stat-label">Perfect Days</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="stat-number">{{ $statistics['total_prayers'] }}</div>
            <div class="stat-label">Prayers Done</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="stat-number">{{ $statistics['total_quran_pages'] }}</div>
            <div class="stat-label">Quran Pages</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="stat-number">${{ number_format($statistics['total_charity'], 0) }}</div>
            <div class="stat-label">Charity Given</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="stat-number">{{ round($statistics['average_progress']) }}%</div>
            <div class="stat-label">Avg Progress</div>
        </div>
    </div>
</div>

<!-- Weekly Progress Chart -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Weekly Progress Trend
            </div>
            <div class="card-body">
                <canvas id="weeklyProgressChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i>Category Breakdown
            </div>
            <div class="card-body">
                <canvas id="categoryPieChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Breakdown -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-check me-2"></i>Weekly Breakdown ({{ $startDate->format('M d') }} - {{ $endDate->format('M d') }})
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-mosque fs-2 text-primary"></i>
                            <h4 class="mt-2 mb-1">{{ $weeklyBreakdown['prayer']['percentage'] }}%</h4>
                            <p class="text-muted mb-0">
                                {{ $weeklyBreakdown['prayer']['completed'] }}/{{ $weeklyBreakdown['prayer']['total'] }} Prayers
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-sun fs-2 text-warning"></i>
                            <h4 class="mt-2 mb-1">{{ $weeklyBreakdown['fasting']['percentage'] }}%</h4>
                            <p class="text-muted mb-0">
                                {{ $weeklyBreakdown['fasting']['completed'] }}/{{ $weeklyBreakdown['fasting']['total'] }} Days
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-book fs-2 text-info"></i>
                            <h4 class="mt-2 mb-1">{{ $weeklyBreakdown['quran']['percentage'] }}%</h4>
                            <p class="text-muted mb-0">
                                {{ $weeklyBreakdown['quran']['completed'] }}/{{ $weeklyBreakdown['quran']['total'] }} Days
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-heart fs-2 text-danger"></i>
                            <h4 class="mt-2 mb-1">{{ $weeklyBreakdown['charity']['percentage'] }}%</h4>
                            <p class="text-muted mb-0">
                                {{ $weeklyBreakdown['charity']['completed'] }}/{{ $weeklyBreakdown['charity']['total'] }} Days
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Summary Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check me-2"></i>Daily Summary
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Prayers</th>
                                <th>Fasting</th>
                                <th>Quran Pages</th>
                                <th>Charity</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailySummaries as $summary)
                                <tr>
                                    <td>
                                        <strong>{{ $summary['day'] }}</strong>
                                        <small class="text-muted d-block">{{ $summary['date'] }}</small>
                                    </td>
                                    <td>{{ $summary['prayers'] }}</td>
                                    <td>
                                        @if($summary['fasting'] == 'Yes')
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        @elseif($summary['fasting'] == 'No')
                                            <i class="bi bi-x-circle-fill text-muted"></i>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $summary['quran_pages'] }}</td>
                                    <td>
                                        @if($summary['charity'] == 'Yes')
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        @elseif($summary['charity'] == 'No')
                                            <i class="bi bi-x-circle-fill text-muted"></i>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ $summary['progress'] }}%">
                                                {{ $summary['progress'] }}%
                                            </div>
                                        </div>
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

<!-- Milestones -->
@if($milestones->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-trophy me-2"></i>Your Achievements
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($milestones as $milestone)
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="card h-100 border-warning">
                                    <div class="card-body text-center">
                                        <span class="display-4">{{ $milestone->milestone_icon }}</span>
                                        <h6 class="mt-2 mb-1">{{ $milestone->name }}</h6>
                                        <p class="text-muted small mb-0">
                                            Earned {{ $milestone->earned_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    // Weekly Progress Chart
    const progressCtx = document.getElementById('weeklyProgressChart').getContext('2d');
    new Chart(progressCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                label: 'Progress %',
                data: {!! json_encode($chartData['progress']) !!},
                borderColor: '#1e6f5c',
                backgroundColor: 'rgba(30, 111, 92, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Prayers',
                data: {!! json_encode($chartData['prayers']) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Category Pie Chart
    const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Prayers', 'Fasting', 'Quran', 'Charity'],
            datasets: [{
                data: [
                    {{ $weeklyBreakdown['prayer']['percentage'] }},
                    {{ $weeklyBreakdown['fasting']['percentage'] }},
                    {{ $weeklyBreakdown['quran']['percentage'] }},
                    {{ $weeklyBreakdown['charity']['percentage'] }}
                ],
                backgroundColor: ['#0d6efd', '#ffc107', '#17a2b8', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
