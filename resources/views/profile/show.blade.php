@extends('layouts.app')

@section('title', 'My Profile - Bismillahi Ramadan Tracker')

@section('content')
<!-- Profile Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="Profile" class="rounded-circle img-fluid" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                 style="width: 120px; height: 120px; font-size: 48px;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <h3 class="mb-1">{{ $user->name }}</h3>
                        <p class="text-muted mb-2">
                            <i class="bi bi-envelope me-1"></i> {{ $user->email }}
                        </p>
                        @if($user->bio)
                            <p class="mb-2">{{ $user->bio }}</p>
                        @endif
                        <p class="text-muted mb-0">
                            <i class="bi bi-calendar me-1"></i> Member since {{ $stats['member_since'] }}
                        </p>
                    </div>
                    <div class="col-md-3 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary mb-2">
                            <i class="bi bi-pencil me-1"></i> Edit Profile
                        </a>
                        <a href="{{ route('profile.password.form') }}" class="btn btn-outline-secondary d-block">
                            <i class="bi bi-shield-lock me-1"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $stats['total_logs'] }}</div>
            <div class="stat-label">Days Tracked</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $stats['total_milestones'] }}</div>
            <div class="stat-label">Achievements</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $stats['best_streak'] }}</div>
            <div class="stat-label">Best Streak</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $user->streaks->sum('current_streak') }}</div>
            <div class="stat-label">Active Streaks</div>
        </div>
    </div>
</div>

<!-- Current Streaks -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-fire me-2"></i>Current Streaks
            </div>
            <div class="card-body">
                @if($user->streaks->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($user->streaks as $streak)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    @if($streak->streak_type == 'prayer')
                                        <i class="bi bi-mosque text-primary me-2"></i>
                                    @elseif($streak->streak_type == 'fasting')
                                        <i class="bi bi-sun text-warning me-2"></i>
                                    @elseif($streak->streak_type == 'quran')
                                        <i class="bi bi-book text-info me-2"></i>
                                    @else
                                        <i class="bi bi-heart text-danger me-2"></i>
                                    @endif
                                    <span class="text-capitalize">{{ $streak->streak_type }}</span>
                                </div>
                                <div>
                                    <span class="badge bg-primary me-2">
                                        <i class="bi bi-fire me-1"></i> {{ $streak->current_streak }}
                                    </span>
                                    <small class="text-muted">Best: {{ $streak->longest_streak }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No active streaks yet. Start tracking!</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Milestones -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy me-2"></i>Recent Achievements
            </div>
            <div class="card-body">
                @if($user->milestones->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($user->milestones->take(5) as $milestone)
                            <div class="list-group-item d-flex align-items-center">
                                <span class="fs-4 me-3">{{ $milestone->milestone_icon }}</span>
                                <div>
                                    <h6 class="mb-0">{{ $milestone->name }}</h6>
                                    <small class="text-muted">
                                        Earned {{ $milestone->earned_at->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No achievements yet. Keep tracking!</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Account Settings -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>Account Settings
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Preferences</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-{{ $user->email_notifications ? 'check-circle-fill text-success' : 'x-circle-fill text-muted' }} me-2"></i>
                                Email Notifications: {{ $user->email_notifications ? 'Enabled' : 'Disabled' }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-globe me-2"></i>
                                Language: {{ strtoupper($user->language) }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-clock me-2"></i>
                                Timezone: {{ $user->timezone }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Account Actions</h6>
                        <form action="{{ route('profile.destroy') }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Enter password to confirm deletion" required>
                            </div>
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash me-1"></i> Delete Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
