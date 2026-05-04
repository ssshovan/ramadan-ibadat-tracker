@extends('layouts.app')

@section('title', 'Dashboard - Bismillahi Ramadan Ibadat Tracker')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card quote-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="bi bi-quote fs-1 text-primary me-3"></i>
                    <div>
                        <p class="mb-1 fst-italic fs-5">"{{ $quote['text'] }}"</p>
                        <small class="text-muted">— {{ $quote['source'] }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Reminder -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-bell-fill fs-4 me-3"></i>
            <div>
                <strong>Daily Reminder:</strong> {{ $reminder }}
            </div>
        </div>
    </div>
</div>

<!-- Today's Progress -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i>Today's Ibadat - {{ today()->format('F d, Y') }}</span>
                <a href="{{ route('ibadat.index') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-pencil me-1"></i> Update
                </a>
            </div>
            <div class="card-body">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Overall Progress</span>
                        <span class="fw-bold text-primary">{{ $todayLog->progress_percentage }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $todayLog->progress_percentage }}%"
                             aria-valuenow="{{ $todayLog->progress_percentage }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            {{ $todayLog->progress_percentage }}%
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="p-3 rounded bg-light">
                            <i class="bi bi-mosque fs-2 text-primary"></i>
                            <h5 class="mt-2 mb-1">{{ $todayLog->completed_prayers_count }}/5</h5>
                            <small class="text-muted">Prayers</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="p-3 rounded bg-light">
                            <i class="bi bi-sun fs-2 {{ $todayLog->roza_completed ? 'text-success' : 'text-muted' }}"></i>
                            <h5 class="mt-2 mb-1">
                                @if($todayLog->roza_completed)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </h5>
                            <small class="text-muted">Fasting</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="p-3 rounded bg-light">
                            <i class="bi bi-book fs-2 {{ $todayLog->quran_completed ? 'text-success' : 'text-muted' }}"></i>
                            <h5 class="mt-2 mb-1">
                                {{ $todayLog->quranLog->pages_read ?? 0 }}
                            </h5>
                            <small class="text-muted">Quran Pages</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="p-3 rounded bg-light">
                            <i class="bi bi-heart fs-2 {{ $todayLog->charity_completed ? 'text-success' : 'text-muted' }}"></i>
                            <h5 class="mt-2 mb-1">
                                @if($todayLog->charity_completed)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </h5>
                            <small class="text-muted">Charity</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Streaks Summary -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-fire me-2"></i>Your Streaks
            </div>
            <div class="card-body">
                @forelse($streaks as $streak)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded {{ $streak->isActive() ? 'bg-light' : '' }}">
                        <div class="d-flex align-items-center">
                            @if($streak->streak_type == 'prayer')
                                <i class="bi bi-mosque me-2 text-primary"></i>
                            @elseif($streak->streak_type == 'fasting')
                                <i class="bi bi-sun me-2 text-warning"></i>
                            @elseif($streak->streak_type == 'quran')
                                <i class="bi bi-book me-2 text-info"></i>
                            @else
                                <i class="bi bi-heart me-2 text-danger"></i>
                            @endif
                            <span class="text-capitalize">{{ $streak->streak_type }}</span>
                        </div>
                        <div class="text-end">
                            <span class="streak-badge {{ $streak->isActive() ? 'streak-active' : 'streak-inactive' }}">
                                <i class="bi bi-fire"></i>
                                {{ $streak->current_streak }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">Start tracking to build your streaks!</p>
                @endforelse

                <div class="text-center mt-3">
                    <a href="{{ route('streaks.index') }}" class="btn btn-outline-primary btn-sm">
                        View Details <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Progress & Family -->
<div class="row mb-4">
    <!-- Weekly Progress Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Weekly Progress
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Family Progress -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-people me-2"></i>Family
            </div>
            <div class="card-body">
                @if($family)
                    <h6 class="mb-3">{{ $family->name }}</h6>
                    
                    @if($familyProgress)
                        <div class="mb-3">
                            <small class="text-muted">Family Average</small>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $familyProgress['overall_avg'] }}%">
                                    {{ $familyProgress['overall_avg'] }}%
                                </div>
                            </div>
                        </div>

                        <div class="row text-center">
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Prayers</small>
                                <span class="fw-bold">{{ $familyProgress['prayer_avg'] }}%</span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Fasting</small>
                                <span class="fw-bold">{{ $familyProgress['fasting_avg'] }}%</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Quran</small>
                                <span class="fw-bold">{{ $familyProgress['quran_avg'] }}%</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Charity</small>
                                <span class="fw-bold">{{ $familyProgress['charity_avg'] }}%</span>
                            </div>
                        </div>
                    @endif

                    <div class="text-center mt-3">
                        <a href="{{ route('family.index') }}" class="btn btn-outline-primary btn-sm">
                          View Family <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    </div>
                    
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Join or create a family to track together!</p>
                        <a href="{{ route('family.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Create Family
                        </a>
                        <a href="{{ route('family.join.form') }}" class="btn btn-outline-primary btn-sm ms-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Join
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Milestones -->
@if($recentMilestones->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy me-2"></i>Recent Achievements
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($recentMilestones as $milestone)
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="milestone-badge w-100 justify-content-center">
                                <span>{{ $milestone->milestone_icon }}</span>
                                <span>{{ $milestone->name }}</span>
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
    // 1. Ensure the data is treated as a collection or array correctly
    const weeklyData = @json($weeklyProgress['daily_progress'] ?? []);
    
    // 2. Extract labels and values safely
    const labels = weeklyData.map(item => item.day);
    const progressValues = weeklyData.map(item => item.progress);

    const ctx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.length > 0 ? labels : ['No Data'], // Fallback label
            datasets: [{
                label: 'Progress %',
                data: progressValues.length > 0 ? progressValues : [0], // Fallback data
                borderColor: '#1e6f5c',
                backgroundColor: 'rgba(30, 111, 92, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#1e6f5c',
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
</script>
@endpush