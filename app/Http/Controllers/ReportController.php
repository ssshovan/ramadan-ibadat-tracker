<?php

namespace App\Http\Controllers;

use App\Models\IbadatLog;
use App\Models\Milestone;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Report Controller
 * 
 * Handles reporting and analytics.
 * Generates daily summaries, weekly reports, and visualizations.
 */
class ReportController extends Controller
{
    protected $progressService;

    /**
     * Constructor with dependency injection.
     */
    public function __construct(ProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Display reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Get date range (default: last 7 days)
        $endDate = today();
        $startDate = today()->subDays(6);

        // Get daily summaries
        $dailySummaries = $this->getDailySummaries($user->id, $startDate, $endDate);

        // Get weekly breakdown
        $weeklyBreakdown = $this->getWeeklyBreakdown($user->id, $startDate, $endDate);

        // Get chart data
        $chartData = $this->getChartData($user->id, $startDate, $endDate);

        // Get milestones
        $milestones = Milestone::forUser($user->id)->get();

        // Get statistics
        $statistics = $this->getStatistics($user->id);

        return view('reports.index', compact(
            'dailySummaries',
            'weeklyBreakdown',
            'chartData',
            'milestones',
            'statistics',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate weekly report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function weeklyReport(Request $request)
    {
        $user = Auth::user();

        // Get week offset (0 = current week, -1 = last week, etc.)
        $weekOffset = $request->input('week', 0);

        $endDate = today()->addWeeks($weekOffset)->endOfWeek();
        $startDate = today()->addWeeks($weekOffset)->startOfWeek();

        // Get weekly data
        $weeklyData = $this->getWeeklyData($user->id, $startDate, $endDate);

        // Calculate percentages
        $percentages = $this->calculatePercentages($weeklyData);

        // Get comparison with previous week
        $comparison = $this->getWeekComparison($user->id, $startDate, $endDate);

        return view('reports.weekly', compact(
            'weeklyData',
            'percentages',
            'comparison',
            'startDate',
            'endDate',
            'weekOffset'
        ));
    }

    /**
     * Generate custom date range report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function customReport(Request $request)
    {
        $user = Auth::user();

        // Validate dates
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        // Limit to 90 days
        if ($startDate->diffInDays($endDate) > 90) {
            return redirect()->back()
                           ->with('error', 'Date range cannot exceed 90 days.');
        }

        // Get report data
        $reportData = $this->getReportData($user->id, $startDate, $endDate);

        return view('reports.custom', compact(
            'reportData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get daily summaries for date range.
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getDailySummaries(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $summaries = [];

        $logs = IbadatLog::forUser($userId)
                         ->forDateRange($startDate, $endDate)
                         ->with(['prayers', 'charityRecord', 'quranLog'])
                         ->get()
                         ->keyBy(function ($log) {
                             return $log->log_date->format('Y-m-d');
                         });

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $log = $logs->get($dateKey);

            if ($log) {
                $summaries[] = [
                    'date' => $dateKey,
                    'day' => $date->format('D'),
                    'prayers' => $log->completed_prayers_count . '/5',
                    'fasting' => $log->roza_completed ? 'Yes' : 'No',
                    'quran_pages' => $log->quranLog->pages_read ?? 0,
                    'charity' => ($log->charityRecord->amount ?? 0) > 0 ? 'Yes' : 'No',
                    'progress' => $log->progress_percentage,
                ];
            } else {
                $summaries[] = [
                    'date' => $dateKey,
                    'day' => $date->format('D'),
                    'prayers' => '-',
                    'fasting' => '-',
                    'quran_pages' => 0,
                    'charity' => '-',
                    'progress' => 0,
                ];
            }
        }

        return $summaries;
    }

    /**
     * Get weekly breakdown.
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getWeeklyBreakdown(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $logs = IbadatLog::forUser($userId)
                         ->forDateRange($startDate, $endDate)
                         ->with(['prayers', 'charityRecord', 'quranLog'])
                         ->get();

        $totalDays = $logs->count();

        if ($totalDays === 0) {
            return [
                'prayer' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
                'fasting' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
                'quran' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
                'charity' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
            ];
        }

        $prayerCompleted = $logs->sum(function ($log) {
            return $log->completed_prayers_count;
        });

        $fastingCompleted = $logs->where('roza_completed', true)->count();

        $quranCompleted = $logs->filter(function ($log) {
            return $log->quran_completed;
        })->count();

        $charityCompleted = $logs->filter(function ($log) {
            return $log->charity_completed;
        })->count();

        return [
            'prayer' => [
                'completed' => $prayerCompleted,
                'total' => $totalDays * 5,
                'percentage' => round(($prayerCompleted / ($totalDays * 5)) * 100, 2),
            ],
            'fasting' => [
                'completed' => $fastingCompleted,
                'total' => $totalDays,
                'percentage' => round(($fastingCompleted / $totalDays) * 100, 2),
            ],
            'quran' => [
                'completed' => $quranCompleted,
                'total' => $totalDays,
                'percentage' => round(($quranCompleted / $totalDays) * 100, 2),
            ],
            'charity' => [
                'completed' => $charityCompleted,
                'total' => $totalDays,
                'percentage' => round(($charityCompleted / $totalDays) * 100, 2),
            ],
        ];
    }

    /**
     * Get chart data.
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getChartData(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $logs = IbadatLog::forUser($userId)
                         ->forDateRange($startDate, $endDate)
                         ->get()
                         ->keyBy(function ($log) {
                             return $log->log_date->format('Y-m-d');
                         });

        $labels = [];
        $progressData = [];
        $prayerData = [];
        $fastingData = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('M d');

            $log = $logs->get($dateKey);

            if ($log) {
                $progressData[] = $log->progress_percentage;
                $prayerData[] = $log->completed_prayers_count;
                $fastingData[] = $log->roza_completed ? 1 : 0;
            } else {
                $progressData[] = 0;
                $prayerData[] = 0;
                $fastingData[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'progress' => $progressData,
            'prayers' => $prayerData,
            'fasting' => $fastingData,
        ];
    }

    /**
     * Get overall statistics.
     *
     * @param int $userId
     * @return array
     */
    private function getStatistics(int $userId): array
    {
        $totalLogs = IbadatLog::forUser($userId)->count();
        $completedLogs = IbadatLog::forUser($userId)->completed()->count();

        $totalPrayers = IbadatLog::forUser($userId)
                                 ->withCount('prayers')
                                 ->get()
                                 ->sum('prayers_count');

        $totalCharity = \App\Models\CharityRecord::whereHas('ibadatLog', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->sum('amount');

        $totalQuranPages = \App\Models\QuranLog::whereHas('ibadatLog', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->sum('pages_read');

        return [
            'total_days_tracked' => $totalLogs,
            'perfect_days' => $completedLogs,
            'total_prayers' => $totalPrayers,
            'total_charity' => $totalCharity,
            'total_quran_pages' => $totalQuranPages,
            'average_progress' => $totalLogs > 0 
                ? round(IbadatLog::forUser($userId)->avg('progress_percentage'), 2) 
                : 0,
        ];
    }

    /**
     * Get weekly data.
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getWeeklyData(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        return $this->getWeeklyBreakdown($userId, $startDate, $endDate);
    }

    /**
     * Calculate percentages.
     *
     * @param array $weeklyData
     * @return array
     */
    private function calculatePercentages(array $weeklyData): array
    {
        return [
            'prayer' => $weeklyData['prayer']['percentage'],
            'fasting' => $weeklyData['fasting']['percentage'],
            'quran' => $weeklyData['quran']['percentage'],
            'charity' => $weeklyData['charity']['percentage'],
        ];
    }

    /**
     * Get week comparison with previous week.
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getWeekComparison(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $prevStartDate = $startDate->copy()->subWeek();
        $prevEndDate = $endDate->copy()->subWeek();

        $currentWeek = $this->getWeeklyBreakdown($userId, $startDate, $endDate);
        $previousWeek = $this->getWeeklyBreakdown($userId, $prevStartDate, $prevEndDate);

        return [
            'prayer' => [
                'current' => $currentWeek['prayer']['percentage'],
                'previous' => $previousWeek['prayer']['percentage'],
                'change' => round($currentWeek['prayer']['percentage'] - $previousWeek['prayer']['percentage'], 2),
            ],
            'fasting' => [
                'current' => $currentWeek['fasting']['percentage'],
                'previous' => $previousWeek['fasting']['percentage'],
                'change' => round($currentWeek['fasting']['percentage'] - $previousWeek['fasting']['percentage'], 2),
            ],
            'quran' => [
                'current' => $currentWeek['quran']['percentage'],
                'previous' => $previousWeek['quran']['percentage'],
                'change' => round($currentWeek['quran']['percentage'] - $previousWeek['quran']['percentage'], 2),
            ],
            'charity' => [
                'current' => $currentWeek['charity']['percentage'],
                'previous' => $previousWeek['charity']['percentage'],
                'change' => round($currentWeek['charity']['percentage'] - $previousWeek['charity']['percentage'], 2),
            ],
        ];
    }

    /**
     * Get report data for custom date range.
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getReportData(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'daily_summaries' => $this->getDailySummaries($userId, $startDate, $endDate),
            'breakdown' => $this->getWeeklyBreakdown($userId, $startDate, $endDate),
            'chart_data' => $this->getChartData($userId, $startDate, $endDate),
        ];
    }

    /**
     * Export report as PDF (placeholder).
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        // PDF export functionality would go here
        // Requires a PDF library like dompdf or mpdf
        return redirect()->back()->with('info', 'PDF export coming soon!');
    }
}
