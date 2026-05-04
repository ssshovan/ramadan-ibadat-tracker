@extends('layouts.app')

@section('title', 'Daily Ibadat Tracking - Bismillahi Ramadan Tracker')

@section('content')
<!-- Date Navigation -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <a href="{{ route('ibadat.date', $selectedDate->copy()->subDay()->format('Y-m-d')) }}" 
                   class="btn btn-outline-primary">
                    <i class="bi bi-chevron-left"></i> Previous Day
                </a>
                
                <div class="text-center">
                    <h4 class="mb-0">{{ $selectedDate->format('l, F d, Y') }}</h4>
                    @if($selectedDate->isToday())
                        <span class="badge bg-success">Today</span>
                    @elseif($selectedDate->isYesterday())
                        <span class="badge bg-secondary">Yesterday</span>
                    @endif
                </div>
                
                <a href="{{ route('ibadat.date', $selectedDate->copy()->addDay()->format('Y-m-d')) }}" 
                   class="btn btn-outline-primary {{ $selectedDate->isToday() ? 'disabled' : '' }}">
                    Next Day <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Progress Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Daily Progress</h5>
                    <span class="fs-4 fw-bold text-primary">{{ $progress }}%</span>
                </div>
                <div class="progress" style="height: 30px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: {{ $progress }}%"
                         aria-valuenow="{{ $progress }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ $progress }}%
                    </div>
                </div>
                @if($progress == 100)
                    <div class="text-center mt-2">
                        <span class="badge bg-success fs-6">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            MashaAllah! Perfect Day!
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Prayers Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-mosque me-2"></i>5 Daily Prayers
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($prayers as $prayer)
                        <div class="col-md-4 col-lg-2 mb-3">
                            <form action="{{ route('ibadat.prayer.toggle', $prayer->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn w-100 h-100 p-3 prayer-checkbox {{ $prayer->is_completed ? 'prayer-completed' : 'btn-outline-primary' }}">
                                    <div class="text-center">
                                        @if($prayer->is_completed)
                                            <i class="bi bi-check-circle-fill fs-2 text-success mb-2"></i>
                                        @else
                                            <i class="bi bi-circle fs-2 text-muted mb-2"></i>
                                        @endif
                                        <h6 class="mb-1">{{ $prayer->prayer_name }}</h6>
                                        <small class="arabic-text">{{ $prayer->arabic_name }}</small>
                                        @if($prayer->completed_at)
                                            <br><small class="text-muted">
                                                {{ \Carbon\Carbon::parse($prayer->completed_at)->format('h:i A') }}
                                            </small>
                                        @endif
                                    </div>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-2">
                    <span class="badge bg-info">
                        {{ $ibadatLog->completed_prayers_count }}/5 Prayers Completed
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fasting, Quran & Charity -->
<div class="row mb-4">
    <!-- Fasting -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-sun me-2"></i>Fasting (Roza)
            </div>
            <div class="card-body text-center">
                <form action="{{ route('ibadat.fasting.update', $ibadatLog->id) }}" method="POST">
                    @csrf
                    <div class="form-check form-switch d-flex justify-content-center mb-3">
                        <input class="form-check-input" type="checkbox" id="roza_completed" 
                               name="roza_completed" value="1"
                               {{ $ibadatLog->roza_completed ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               style="width: 60px; height: 30px;">
                    </div>
                    <label for="roza_completed" class="form-label">
                        @if($ibadatLog->roza_completed)
                            <span class="text-success fw-bold">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Fasting Completed
                            </span>
                        @else
                            <span class="text-muted">Mark as completed</span>
                        @endif
                    </label>
                </form>
                <p class="text-muted small mt-3">
                    "Fasting is a shield, so the fasting person should not observe foolishness or obscenity."
                </p>
            </div>
        </div>
    </div>

    <!-- Quran -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-book me-2"></i>Quran Recitation
            </div>
            <div class="card-body">
                <form action="{{ route('ibadat.quran.update', $ibadatLog->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="pages_read" class="form-label">Pages Read Today</label>
                        <input type="number" class="form-control form-control-lg text-center" 
                               id="pages_read" name="pages_read" 
                               value="{{ $quran->pages_read ?? 0 }}"
                               min="0" max="100">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" class="form-control" name="start_surah" 
                                   placeholder="From Surah" 
                                   value="{{ $quran->start_surah ?? '' }}">
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control" name="end_surah" 
                                   placeholder="To Surah" 
                                   value="{{ $quran->end_surah ?? '' }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-save me-1"></i> Save Quran Progress
                    </button>
                </form>
                @if($quran && $quran->pages_read > 0)
                    <div class="alert alert-success mt-3 mb-0">
                        <small>
                            <i class="bi bi-check-circle me-1"></i>
                            Great! You've read {{ $quran->pages_read }} pages today!
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Charity -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-heart me-2"></i>Charity (Sadaqah)
            </div>
            <div class="card-body">
                <form action="{{ route('ibadat.charity.update', $ibadatLog->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount Donated</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   value="{{ $charity->amount ?? 0 }}"
                                   min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="acts_count" class="form-label">Charitable Acts</label>
                        <input type="number" class="form-control" id="acts_count" name="acts_count" 
                               value="{{ $charity->acts_count ?? 0 }}"
                               min="0">
                    </div>
                    <!-- <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="2" placeholder="What did you do?">{{ $charity->description ?? '' }}</textarea>
                    </div> -->
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Save Charity
                    </button>
                </form>
                @if($charity && ($charity->amount > 0 || $charity->acts_count > 0))
                    <div class="alert alert-success mt-3 mb-0">
                        <small>
                            <i class="bi bi-check-circle me-1"></i>
                            May Allah reward you for your charity!
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Notes Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-journal-text me-2"></i>Daily Notes & Reflections
            </div>
            <div class="card-body">
                <!-- Add Note Form -->
                <form action="{{ route('ibadat.notes.add', $ibadatLog->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="general">General</option>
                                <option value="prayer">Prayer</option>
                                <option value="quran">Quran</option>
                                <option value="charity">Charity</option>
                                <option value="reflection">Reflection</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <textarea class="form-control" name="content" rows="2" 
                                      placeholder="Write your thoughts, reflections, or notes for today..."
                                      required></textarea>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 h-100">
                                <i class="bi bi-plus-lg me-1"></i> Add Note
                            </button>
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_important" value="1" id="is_important">
                        <label class="form-check-label" for="is_important">
                            <i class="bi bi-star-fill text-warning me-1"></i> Mark as important
                        </label>
                    </div>
                </form>

                <!-- Notes List -->
                <div class="row">
                

@forelse($notes as $note)
    <div class="col-md-6 mb-3">

        <div class="card {{ $note->is_important ? 'border-warning' : 'border-light' }}">
            <div class="card-body">

                <!-- NOTE HEADER -->
                <div class="d-flex justify-content-between align-items-start">

                    <div>
                        <span class="badge bg-secondary mb-2">
                            {{ $note->category_icon }} {{ $note->category_label }}
                        </span>

                        @if($note->is_important)
                            <i class="bi bi-star-fill text-warning ms-1"></i>
                        @endif

                        <p class="mb-0 mt-2">{{ $note->content }}</p>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="d-flex">

                        <!-- EDIT BUTTON -->
                        <button class="btn btn-sm btn-outline-primary me-1"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#editNote{{ $note->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <!-- DELETE BUTTON -->
                        <form action="{{ route('ibadat.notes.delete', $note->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Delete this note?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>

                    </div>
                </div>

                <!-- EDIT FORM (HIDDEN COLLAPSE) -->
                <div class="collapse mt-3" id="editNote{{ $note->id }}">

                    <form action="{{ route('ibadat.notes.update', $note->id) }}" method="POST">
                        @csrf

                        <!-- CATEGORY -->
                        <select name="category" class="form-select mb-2">
                            <option value="general" {{ $note->category == 'general' ? 'selected' : '' }}>General</option>
                            <option value="prayer" {{ $note->category == 'prayer' ? 'selected' : '' }}>Prayer</option>
                            <option value="quran" {{ $note->category == 'quran' ? 'selected' : '' }}>Quran</option>
                            <option value="charity" {{ $note->category == 'charity' ? 'selected' : '' }}>Charity</option>
                            <option value="reflection" {{ $note->category == 'reflection' ? 'selected' : '' }}>Reflection</option>
                        </select>

                        <!-- CONTENT -->
                        <textarea name="content"
                                  class="form-control mb-2"
                                  rows="3">{{ $note->content }}</textarea>

                        <!-- IMPORTANT -->
                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_important"
                                   value="1"
                                   {{ $note->is_important ? 'checked' : '' }}>
                            <label class="form-check-label">Important</label>
                        </div>

                        <!-- SAVE BUTTON -->
                        <button class="btn btn-success btn-sm w-100">
                            Save Changes
                        </button>

                    </form>

                </div>

            </div>
        </div>

    </div>

@empty
    <div class="text-center text-muted py-3">
        No notes yet
    </div>
@endforelse

</div> <!-- close row -->
</div>
</div>
</div>
</div>
@endsection
