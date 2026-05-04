@extends('layouts.app')

@section('title', 'My Family - Bismillahi Ramadan Tracker')

@section('content')
<!-- Family Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ $primaryFamily->name }}</h3>
                    <p class="text-muted mb-0">
                        <i class="bi bi-shield-check me-1"></i>
                        Created by {{ $primaryFamily->creator->name }}
                    </p>
                </div>
                <div class="text-end">
                    <p class="mb-1"><strong>Family Code:</strong></p>
                    <span class="family-code">{{ $primaryFamily->family_code }}</span>
                    <p class="text-muted small mb-0 mt-1">
                        Share this code with family members
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Family Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $familyDetails['member_count'] }}</div>
            <div class="stat-label">Members</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ $familyDetails['family_streak'] }}</div>
            <div class="stat-label">Family Streak</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">{{ round($familyDetails['progress']['overall_avg']) }}%</div>
            <div class="stat-label">Avg Progress</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-number">${{ number_format($familyDetails['total_charity'], 2) }}</div>
            <div class="stat-label">Total Charity</div>
        </div>
    </div>
</div>

<!-- Family Progress -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Family Progress
            </div>
            <div class="card-body">
                <canvas id="familyProgressChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Category Breakdown
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Prayers</span>
                        <span>{{ $familyDetails['progress']['prayer_avg'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $familyDetails['progress']['prayer_avg'] }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Fasting</span>
                        <span>{{ $familyDetails['progress']['fasting_avg'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: {{ $familyDetails['progress']['fasting_avg'] }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Quran</span>
                        <span>{{ $familyDetails['progress']['quran_avg'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: {{ $familyDetails['progress']['quran_avg'] }}%"></div>
                    </div>
                </div>
                <div class="mb-0">
                    <div class="d-flex justify-content-between">
                        <span>Charity</span>
                        <span>{{ $familyDetails['progress']['charity_avg'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-danger" style="width: {{ $familyDetails['progress']['charity_avg'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Family Members -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Family Members</span>
                @if($isParent)
                    <a href="{{ route('family.parent-dashboard', $primaryFamily->id) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-gear me-1"></i> Parent Dashboard
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Role</th>
                                <th>Today's Progress</th>
                                <th>Streaks</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($familyDetails['members'] as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 40px; height: 40px;">
                                                {{ substr($member['name'], 0, 1) }}
                                            </div>
                                            <span>{{ $member['name'] }}</span>
                                            @if($member['id'] == auth()->id())
                                                <span class="badge bg-primary ms-2">You</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $member['role'] == 'parent' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                            <i class="bi bi-{{ $member['role'] == 'parent' ? 'person-check' : 'person' }} me-1"></i>
                                            {{ ucfirst($member['role']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ $member['today_progress']['progress'] }}%">
                                                {{ $member['today_progress']['progress'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @foreach($member['streaks'] as $type => $count)
                                            @if($count > 0)
                                                <span class="badge bg-light text-dark me-1">
                                                    <i class="bi bi-fire text-warning me-1"></i>
                                                    {{ $count }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>{{ $member['joined_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <a href="{{ route('family.join.form') }}" class="btn btn-success me-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Join Another Family
                    </a>
                </div>
                <form action="{{ route('family.leave', $primaryFamily->id) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to leave this family?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-1"></i> Leave Family
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Family Progress Chart
    const ctx = document.getElementById('familyProgressChart').getContext('2d');
    const familyChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Prayers', 'Fasting', 'Quran', 'Charity'],
            datasets: [{
                data: [
                    {{ $familyDetails['progress']['prayer_avg'] }},
                    {{ $familyDetails['progress']['fasting_avg'] }},
                    {{ $familyDetails['progress']['quran_avg'] }},
                    {{ $familyDetails['progress']['charity_avg'] }}
                ],
                backgroundColor: [
                    '#1e6f5c',
                    '#ffc107',
                    '#17a2b8',
                    '#dc3545'
                ],
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
