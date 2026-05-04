@extends('layouts.app')

@section('title', 'Streaks - Bismillahi Ramadan Tracker')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0"><i class="bi bi-fire text-warning me-2"></i>Your Streaks</h2>
                <p class="text-muted mb-0">Keep your streaks alive! Consistency is key.</p>
            </div>
            <form action="{{ route('streaks.recalculate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Recalculate
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Streak Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $streakStats['total_active_streaks'] }}</div>
            <div class="stat-label">Active Streaks</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $streakStats['total_longest_streak'] }}</div>
            <div class="stat-label">Total Longest</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ round($streakStats['average_streak']) }}</div>
            <div class="stat-label">Average Streak</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number text-capitalize">{{ $streakStats['best_streak_type'] ?: '-' }}</div>
            <div class="stat-label">Best Category</div>
        </div>
    </div>
</div>

<!-- Streak Cards -->
<div class="row mb-4">
    @foreach($streaks as $streak)
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 {{ $streak->isActive() ? 'border-success' : '' }}">
                <div class="card-header text-center">
                    @if($streak->streak_type == 'prayer')
                        <i class="bi bi-mosque fs-2"></i>
                    @elseif($streak->streak_type == 'fasting')
                        <i class="bi bi-sun fs-2"></i>
                    @elseif($streak->streak_type == 'quran')
                        <i class="bi bi-book fs-2"></i>
                    @else
                        <i class="bi bi-heart fs-2"></i>
                    @endif
                    <h5 class="mt-2 mb-0 text-capitalize">{{ $streak->streak_type }}</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="display-4 fw-bold {{ $streak->isActive() ? 'text-success' : 'text-muted' }}">
                            {{ $streak->current_streak }}
                        </span>
                        <p class="text-muted mb-0">Current Streak</p>
                    </div>
                    
                    <div class="mb-3">
                        <span class="fs-5 text-primary">{{ $streak->longest_streak }}</span>
                        <p class="text-muted small mb-0">Longest Streak</p>
                    </div>

                    @if($streak->milestone_badge)
                        <div class="milestone-badge mb-3">
                            {{ $streak->milestone_badge }}
                        </div>
                    @endif

                    <div class="alert {{ $streak->isActive() ? 'alert-success' : 'alert-warning' }} mb-0">
                        <small>{{ $streak->status_message }}</small>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <!-- <a href="{{ route('streaks.show', $streak->streak_type) }}" class="btn btn-outline-primary btn-sm">
                        View Details <i class="bi bi-arrow-right ms-1"></i>
                    </a> -->
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Upcoming Milestones -->
@if(count($upcomingMilestones) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-trophy me-2"></i>Upcoming Milestones
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($upcomingMilestones as $milestone)
                            <div class="col-md-6 col-lg-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <h6 class="text-capitalize text-muted">{{ $milestone['type'] }}</h6>
                                        <div class="my-3">
                                            <span class="display-6 fw-bold text-primary">
                                                {{ $milestone['current'] }}
                                            </span>
                                            <span class="text-muted">/ {{ $milestone['target'] }}</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ ($milestone['current'] / $milestone['target']) * 100 }}%">
                                            </div>
                                        </div>
                                        <p class="text-muted small mt-2 mb-0">
                                            {{ $milestone['remaining'] }} days to go!
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

<!-- Streak Tips -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightbulb me-2"></i>Tips to Maintain Your Streaks
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Set daily reminders for each prayer time
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Read Quran right after Fajr prayer
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Keep a charity box at home for daily sadaqah
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Track your ibadat before going to bed
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Join a family to motivate each other
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Celebrate milestones to stay motivated
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
