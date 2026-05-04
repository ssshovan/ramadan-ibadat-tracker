@extends('layouts.app')

@section('title', 'Join Family - Bismillahi Ramadan Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-arrow-in-right me-2"></i>Join a Family
            </div>
            <div class="card-body">
                <form action="{{ route('family.join') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="family_code" class="form-label">Family Code</label>
                        <input type="text" class="form-control @error('family_code') is-invalid @enderror" 
                               id="family_code" name="family_code" required
                               placeholder="Enter 8-character code (e.g., ABC12345)"
                               value="{{ old('family_code') }}"
                               maxlength="8"
                               style="text-transform: uppercase; letter-spacing: 3px; font-size: 1.2rem;">
                        @error('family_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Ask your family admin for the family code.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Role</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" 
                                   id="role_parent" value="parent" {{ old('role') == 'parent' ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_parent">
                                <i class="bi bi-person-check me-1"></i> Parent
                                <small class="text-muted d-block">Can manage family members and view all progress</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" 
                                   id="role_child" value="child" {{ old('role', 'child') == 'child' ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_child">
                                <i class="bi bi-person me-1"></i> Child
                                <small class="text-muted d-block">Can view family progress and track own ibadat</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Join Family
                        </button>
                        <a href="{{ route('family.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
