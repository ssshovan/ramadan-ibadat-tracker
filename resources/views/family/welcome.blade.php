@extends('layouts.app')

@section('title', 'Join Family - Bismillahi Ramadan Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card">
            <div class="card-body py-5">
                <i class="bi bi-people-fill display-1 text-primary mb-4"></i>
                <h2 class="mb-3">Welcome to Family Tracking!</h2>
                <p class="text-muted mb-4">
                    Track your ibadat together with your family members. Motivate each other, 
                    share progress, and build stronger spiritual connections.
                </p>
                
                <div class="row justify-content-center">
                    <div class="col-md-5 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body">
                                <i class="bi bi-plus-circle fs-1 text-primary mb-3"></i>
                                <h5>Create a Family</h5>
                                <p class="text-muted small">
                                    Start a new family group and invite your loved ones to join.
                                </p>
                                <a href="{{ route('family.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> Create Family
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body">
                                <i class="bi bi-box-arrow-in-right fs-1 text-success mb-3"></i>
                                <h5>Join a Family</h5>
                                <p class="text-muted small">
                                    Have a family code? Join an existing family group.
                                </p>
                                <a href="{{ route('family.join.form') }}" class="btn btn-success">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Join Family
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
