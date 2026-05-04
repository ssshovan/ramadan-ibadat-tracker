<?php

namespace App\Http\Controllers;

use App\Models\Streak;
use App\Services\StreakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Streak Controller
 * 
 * Handles streak-related operations and views.
 * Displays streak stats and history.
 */
class StreakController extends Controller
{
    protected $streakService;

    /**
     * Constructor with dependency injection.
     */
    public function __construct(StreakService $streakService)
    {
        $this->streakService = $streakService;
    }

    /**
     * Display streak dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Get all streaks
        $streaks = Streak::forUser($user->id)->get();

        // Get streak history (last 30 days)
        $streakHistory = $this->getStreakHistory($user->id, 30);

        // Get streak statistics
        $streakStats = $this->getStreakStats($user->id);

        // Get upcoming milestones
        $upcomingMilestones = $this->getUpcomingMilestones($user->id);

        return view('streaks.index', compact(
            'streaks',
            'streakHistory',
            'streakStats',
            'upcomingMilestones'
        ));
    }

    /**
     * Get detailed streak information for a specific type.
     *
     * @param string $type
     * @return \Illuminate\View\View
     */
    public function show(string $type)
    {
        $user = Auth::user();

        // Validate streak type
        $validTypes = ['prayer', 'fasting', 'quran', 'charity'];
        if (!in_array($type, $validTypes)) {
            abort(404, 'Invalid streak type.');
        }

        // Get streak
        $streak = Streak::forUser($user->id)
                        ->byType($type)
                        ->first();

        if (!$streak) {
            $streak = Streak::initializeForUser($user->id, $type);
        }

        // Get streak calendar (last 30 days)
        $calendar = $this->getStreakCalendar($user->id, $type, 30);

        // Get streak insights
        $insights = $this->getStreakInsights($user->id, $type);

        return view('streaks.show', compact('streak', 'type', 'calendar', 'insights'));
    }

    /**
     * Get streak history for the user.
     *
     * @param int $userId
     * @param int $days
     * @return array
     */
    private function getStreakHistory(int $userId, int $days): array
    {
        $history = [];
        $endDate = today();
        $startDate = today()->subDays($days);

        $streaks = Streak::forUser($userId)
                         ->whereBetween('last_updated', [$startDate, $endDate])
                         ->get();

        foreach ($streaks as $streak) {
            $history[$streak->streak_type][] = [
                'date' => $streak->last_updated->format('Y-m-d'),
                'streak' => $streak->current_streak,
            ];
        }

        return $history;
    }

    /**
     * Get streak statistics.
     *
     * @param int $userId
     * @return array
     */
    private function getStreakStats(int $userId): array
    {
        $streaks = Streak::forUser($userId)->get();

        $stats = [
            'total_active_streaks' => $streaks->where('current_streak', '>', 0)->count(),
            'total_longest_streak' => $streaks->sum('longest_streak'),
            'average_streak' => $streaks->avg('current_streak') ?? 0,
            'best_streak_type' => null,
            'best_streak_count' => 0,
        ];

        // Find best streak
        $bestStreak = $streaks->sortByDesc('longest_streak')->first();
        if ($bestStreak) {
            $stats['best_streak_type'] = $bestStreak->streak_type;
            $stats['best_streak_count'] = $bestStreak->longest_streak;
        }

        return $stats;
    }

    /**
     * Get upcoming milestones for user.
     *
     * @param int $userId
     * @return array
     */
    private function getUpcomingMilestones(int $userId): array
    {
        $streaks = Streak::forUser($userId)->get();
        $milestones = [];

        $milestoneTargets = [7, 14, 21, 30];

        foreach ($streaks as $streak) {
            $current = $streak->current_streak;

            foreach ($milestoneTargets as $target) {
                if ($current < $target) {
                    $milestones[] = [
                        'type' => $streak->streak_type,
                        'target' => $target,
                        'current' => $current,
                        'remaining' => $target - $current,
                    ];
                    break; // Only show next milestone
                }
            }
        }

        return $milestones;
    }

    /**
     * Get streak calendar for visualization.
     *
     * @param int $userId
     * @param string $type
     * @param int $days
     * @return array
     */
    private function getStreakCalendar(int $userId, string $type, int $days): array
    {
        $calendar = [];
        $streak = Streak::forUser($userId)->byType($type)->first();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $calendar[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'active' => $this->wasStreakActiveOnDate($userId, $type, $date),
            ];
        }

        return $calendar;
    }

    /**
     * Check if streak was active on a specific date.
     *
     * @param int $userId
     * @param string $type
     * @param \Carbon\Carbon $date
     * @return bool
     */
    private function wasStreakActiveOnDate(int $userId, string $type, $date): bool
    {
        // This is a simplified check - in production, you'd track daily completion
        $ibadatLog = \App\Models\IbadatLog::forUser($userId)
                                          ->forDate($date)
                                          ->first();

        if (!$ibadatLog) {
            return false;
        }

        switch ($type) {
            case 'prayer':
                return $ibadatLog->all_prayers_completed;
            case 'fasting':
                return $ibadatLog->roza_completed;
            case 'quran':
                return $ibadatLog->quran_completed;
            case 'charity':
                return $ibadatLog->charity_completed;
            default:
                return false;
        }
    }

    /**
     * Get streak insights.
     *
     * @param int $userId
     * @param string $type
     * @return array
     */
    private function getStreakInsights(int $userId, string $type): array
    {
        $streak = Streak::forUser($userId)->byType($type)->first();

        if (!$streak) {
            return [
                'status' => 'not_started',
                'message' => 'Start your streak today!',
            ];
        }

        $insights = [
            'current_streak' => $streak->current_streak,
            'longest_streak' => $streak->longest_streak,
            'status' => $streak->isActive() ? 'active' : 'at_risk',
            'message' => $streak->status_message,
        ];

        // Add specific insights based on streak type
        switch ($type) {
            case 'prayer':
                $insights['tip'] = 'Try to pray at the beginning of each prayer time.';
                break;
            case 'fasting':
                $insights['tip'] = 'Stay hydrated during Suhoor and Iftar.';
                break;
            case 'quran':
                $insights['tip'] = 'Even one page a day keeps the streak alive!';
                break;
            case 'charity':
                $insights['tip'] = 'Charity can be a smile or helping someone in need.';
                break;
        }

        return $insights;
    }

    /**
     * Force recalculate all streaks (for maintenance).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recalculate()
    {
        $user = Auth::user();

        $this->streakService->recalculateAllStreaks($user->id);

        return redirect()->route('streaks.index')
                         ->with('success', 'Streaks recalculated successfully!');
    }
}
