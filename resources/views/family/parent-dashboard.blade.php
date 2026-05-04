@extends('layouts.app')

@section('title', 'Parent Dashboard')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-people-fill me-2"></i>
            Parent Dashboard
        </h2>

        <a href="{{ route('family.index') }}" class="btn btn-outline-secondary">
            Back to Family
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4>{{ $family->name }}</h4>

            <p class="text-muted mb-0">
                Monitor children's ibadat progress and streaks
            </p>
        </div>
    </div>

    @if(count($childrenProgress) > 0)

        <div class="row">

            @foreach($childrenProgress as $child)

                <div class="col-md-6 mb-4">

                    <div class="card h-100">

                        <div class="card-header d-flex justify-content-between">

                            <strong>
                                {{ $child['user']->name }}
                            </strong>

                            <span class="badge bg-primary">
                                Child
                            </span>

                        </div>

                        <div class="card-body">

                            {{-- Today Progress --}}

                            <h6 class="mb-2">
                                Today's Progress
                            </h6>

                            <div class="progress mb-3">

                            <div
    class="progress-bar"
    role="progressbar"
    style="width: {{ $child['today_progress']['progress'] ?? 0 }}%"
>

    {{ $child['today_progress']['progress'] ?? 0 }}%

</div>

                            </div>

                            {{-- Weekly Progress --}}
                            <h6 class="mb-2">Weekly Progress</h6>

<!-- <div class="progress mb-3" style="height: 22px;">
    <div
        class="progress-bar bg-success"
        role="progressbar"
        style="width: {{ $child['weekly_progress']['average_progress'] ?? 0 }}%"
    >
        {{ $child['weekly_progress']['average_progress'] ?? 0 }}%
    </div>
</div> -->

<div class="progress mb-3" style="height: 20px;">
    <div
        class="progress-bar bg-success"
        role="progressbar"
        style="width: {{ $child['weekly_progress']['average_progress'] ?? 0 }}%"
    >
        {{ $child['weekly_progress']['average_progress'] ?? 0 }}%
    </div>
</div>

                            {{-- Streaks --}}

                            <h6 class="mt-4">
                                Streaks
                            </h6>

                            @if($child['streaks']->count())

                                @foreach($child['streaks'] as $streak)

                                    <div class="d-flex justify-content-between border rounded p-2 mb-2">

                                        <span class="text-capitalize">
                                            {{ $streak->streak_type }}
                                        </span>

                                        <span>
                                            🔥 {{ $streak->current_streak }}
                                        </span>

                                    </div>

                                @endforeach

                            @else

                                <p class="text-muted">
                                    No streaks yet
                                </p>

                            @endif

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="alert alert-info">
            No children found in this family.
        </div>

    @endif

</div>

@endsection