<?php

namespace App\Http\Controllers;

use App\Models\IbadatLog;
use App\Models\Prayer;
use App\Models\CharityRecord;
use App\Models\QuranLog;
use App\Models\Note;
use App\Services\StreakService;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Ibadat Controller
 * 
 * Handles daily ibadat tracking operations.
 * CRUD for prayers, fasting, charity, Quran, and notes.
 */
class IbadatController extends Controller
{
    protected $streakService;
    protected $progressService;

    /**
     * Constructor with dependency injection.
     */
    public function __construct(
        StreakService $streakService,
        ProgressService $progressService
    ) {
        $this->streakService = $streakService;
        $this->progressService = $progressService;
    }

    /**
     * Display daily tracking page.
     *
     * @param string|null $date
     * @return \Illuminate\View\View
     */
    public function index($date = null)
    {
        $user = Auth::user();
        $selectedDate = $date ? \Carbon\Carbon::parse($date) : today();
    
        // Get or create ibadat log for selected date
        $ibadatLog = IbadatLog::forUser($user->id)
                              ->forDate($selectedDate)
                              ->with(['prayers', 'charityRecord', 'quranLog', 'notes'])
                              ->first();
    
        if (!$ibadatLog) {
            $ibadatLog = $this->createIbadatLog($user->id, $selectedDate);
        }
    
        // --- FIX STARTS HERE ---
        
        // 1. Get notes safely (Ensure it's a collection, never null)
        $notes = $ibadatLog->notes()->latest()->get();
    
        // 2. Get other relations
        $prayers = $ibadatLog->prayers;
        $charity = $ibadatLog->charityRecord;
        $quran = $ibadatLog->quranLog;
    
        // 3. REMOVE the redundant line that was causing the error:
        // $notes = $ibadatLog->notes; <-- DELETE THIS LINE IF IT EXISTS LOWER DOWN
    
        // --- FIX ENDS HERE ---
    
        // Calculate progress
        $progress = $ibadatLog->calculateProgressPercentage();
    
        // Get available dates for navigation
        $availableDates = IbadatLog::forUser($user->id)
                                   ->orderBy('log_date', 'desc')
                                   ->pluck('log_date')
                                   ->map(function ($date) {
                                       return $date->format('Y-m-d');
                                   });
    
        return view('ibadat.index', compact(
            'ibadatLog',
            'prayers',
            'charity',
            'quran',
            'notes',
            'progress',
            'selectedDate',
            'availableDates'
        ));
    }
    public function updateNote(Request $request, $noteId)
    {
        $user = Auth::user();
        $note = Note::findOrFail($noteId);
    
        if ($note->ibadatLog->user_id !== $user->id) {
            abort(403);
        }
    
        $request->validate([
            'content' => 'required|string|max:2000',
            'category' => 'required|in:general,prayer,quran,charity,reflection',
            'is_important' => 'nullable|boolean',
        ]);
    
        $note->update([
            'content' => $request->content,
            'category' => $request->category,
            'is_important' => $request->has('is_important'),
        ]);
    
        return back()->with('success', 'Note updated!');
    }
    /**
     * Toggle prayer status.
     *
     * @param Request $request
     * @param int $prayerId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function togglePrayer(Request $request, int $prayerId)
    {
        $user = Auth::user();
        $prayer = Prayer::findOrFail($prayerId);

        // Verify ownership through ibadat log
        if ($prayer->ibadatLog->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Toggle status
        if ($prayer->is_completed) {
            $prayer->markAsNotCompleted();
        } else {
            $prayer->markAsCompleted();
        }

        // Update progress
        $prayer->ibadatLog->updateProgress();

        // Update prayer streak
        $this->streakService->updatePrayerStreak($user->id);

        return redirect()->back()
                         ->with('success', 'Prayer status updated! Alhamdulillah!');
    }

    /**
     * Update fasting status.
     *
     * @param Request $request
     * @param int $logId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFasting(Request $request, int $logId)
    {
        $user = Auth::user();
        $ibadatLog = IbadatLog::findOrFail($logId);

        // Verify ownership
        if ($ibadatLog->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Update fasting status
        $ibadatLog->roza_completed = $request->boolean('roza_completed');
        $ibadatLog->save();

        // Update progress
        $ibadatLog->updateProgress();

        // Update fasting streak
        $this->streakService->updateFastingStreak($user->id);

        $message = $ibadatLog->roza_completed 
            ? 'May Allah accept your fast!' 
            : 'Fasting status updated.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update Quran reading.
     *
     * @param Request $request
     * @param int $logId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateQuran(Request $request, int $logId)
    {
        $user = Auth::user();
        $ibadatLog = IbadatLog::findOrFail($logId);

        // Verify ownership
        if ($ibadatLog->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'pages_read' => 'required|integer|min:0|max:1000',
            'start_surah' => 'nullable|string|max:50',
            'end_surah' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Update Quran log
        $quranLog = $ibadatLog->quranLog;
        $quranLog->pages_read = $request->input('pages_read');
        $quranLog->start_surah = $request->input('start_surah');
        $quranLog->end_surah = $request->input('end_surah');
        $quranLog->save();

        // Update progress
        $ibadatLog->updateProgress();

        // Update Quran streak
        $this->streakService->updateQuranStreak($user->id);

        return redirect()->back()
                         ->with('success', 'Quran progress updated! BarakAllahu feek!');
    }

    /**
     * Update charity record.
     *
     * @param Request $request
     * @param int $logId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCharity(Request $request, int $logId)
    {
        $user = Auth::user();
        $ibadatLog = IbadatLog::findOrFail($logId);

        // Verify ownership
        if ($ibadatLog->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0',
            'acts_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Update charity record
        $charityRecord = $ibadatLog->charityRecord;
        $charityRecord->amount = $request->input('amount', 0);
        $charityRecord->acts_count = $request->input('acts_count', 0);
        $charityRecord->description = $request->input('description');
        $charityRecord->save();

        // Update progress
        $ibadatLog->updateProgress();

        // Update charity streak
        $this->streakService->updateCharityStreak($user->id);

        return redirect()->back()
                         ->with('success', 'Charity recorded! May Allah reward you!');
    }

    /**
     * Add a note.
     *
     * @param Request $request
     * @param int $logId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addNote(Request $request, int $logId)
    {
        $user = Auth::user();
        $ibadatLog = IbadatLog::findOrFail($logId);

        // Verify ownership
        if ($ibadatLog->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'category' => 'required|in:general,prayer,quran,charity,reflection',
            'is_important' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Create note
        Note::create([
            'ibadat_log_id' => $logId,
            'category' => $request->input('category'),
            'content' => $request->input('content'),
            'is_important' => $request->boolean('is_important'),
        ]);

        return redirect()->back()
                         ->with('success', 'Note added successfully!');
    }

    /**
     * Delete a note.
     *
     * @param int $noteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteNote(int $noteId)
    {
        $user = Auth::user();
        $note = Note::findOrFail($noteId);

        // Verify ownership
        if ($note->ibadatLog->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $note->delete();

        return redirect()->back()
                         ->with('success', 'Note deleted successfully!');
    }

    /**
     * Create a new ibadat log with related records.
     *
     * @param int $userId
     * @param \Carbon\Carbon $date
     * @return IbadatLog
     */
    private function createIbadatLog(int $userId, $date): IbadatLog
    {
        return IbadatLog::create([
            'user_id' => $userId,
            'log_date' => $date,
            'roza_completed' => false,
            'progress_percentage' => 0,
        ]);
    }

    /**
     * Get daily summary for AJAX.
     *
     * @param string|null $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailySummary($date = null)
    {
        $user = Auth::user();
        $selectedDate = $date ? \Carbon\Carbon::parse($date) : today();

        $ibadatLog = IbadatLog::forUser($user->id)
                              ->forDate($selectedDate)
                              ->with(['prayers', 'charityRecord', 'quranLog'])
                              ->first();

        if (!$ibadatLog) {
            return response()->json([
                'prayers_completed' => 0,
                'fasting' => false,
                'quran_pages' => 0,
                'charity_amount' => 0,
                'progress' => 0,
            ]);
        }

        return response()->json([
            'prayers_completed' => $ibadatLog->completed_prayers_count,
            'fasting' => $ibadatLog->roza_completed,
            'quran_pages' => $ibadatLog->quranLog->pages_read ?? 0,
            'charity_amount' => $ibadatLog->charityRecord->amount ?? 0,
            'progress' => $ibadatLog->progress_percentage,
        ]);
    }
}
