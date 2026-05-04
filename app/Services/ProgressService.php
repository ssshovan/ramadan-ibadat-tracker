<?php

namespace App\Services;

use App\Models\IbadatLog;
use Carbon\Carbon;

/**
 * Progress Service
 * 
 * Business logic for progress calculation and reporting.
 * Handles all progress-related operations.
 */
class ProgressService
{
    /**
     * Get today's progress for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getTodayProgress(int $userId): array
    {
        $log = IbadatLog::forUser($userId)
                        ->forDate(today())
                        ->with(['prayers', 'quranLog', 'charityRecord'])
                        ->first();

        if (!$log) {
            return [
                'prayers_completed' => 0,
                'fasting' => false,
                'quran_pages' => 0,
                'charity_amount' => 0,
                'progress_percentage' => 0,
            ];
        }

        return [
            'prayers_completed' => $log->completed_prayers_count,
            'fasting' => $log->roza_completed,
            'quran_pages' => $log->quranLog->pages_read ?? 0,
            'charity_amount' => $log->charityRecord->amount ?? 0,
            'progress_percentage' => $log->progress_percentage,
        ];
    }

    /**
     * Get weekly progress for a user.
     *
     * @param int $userId
     * @param int $weekOffset
     * @return array
     */
    public function getWeeklyProgress(int $userId, int $weekOffset = 0): array
    {
        // Sets the window to the last 7 days (today + 6 days back)
        $endDate = today()->addWeeks($weekOffset);
        $startDate = today()->addWeeks($weekOffset)->subDays(6); 
    
        // Fetch logs with relationships in a single query
        $logs = IbadatLog::forUser($userId)
                         ->forDateRange($startDate, $endDate)
                         ->with(['prayers', 'quranLog', 'charityRecord'])
                         ->get();
    
        $dailyProgress = [];
        $totalProgress = 0;
    
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Find if a log exists for this specific date in the collection
            $log = $logs->first(function ($item) use ($date) {
                return $item->log_date->format('Y-m-d') === $date->format('Y-m-d');
            });
    
            if ($log) {
                $dailyProgress[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->format('D'),
                    'progress' => $log->progress_percentage,
                    'prayers' => $log->completed_prayers_count,
                    'fasting' => $log->roza_completed,
                ];
                $totalProgress += $log->progress_percentage;
            } else {
                // Fill with zero if no data exists for this day
                $dailyProgress[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->format('D'),
                    'progress' => 0,
                    'prayers' => 0,
                    'fasting' => false,
                ];
            }
        }
    
        $daysCount = count($dailyProgress);
    
        return [
            'daily_progress' => $dailyProgress,
            'average_progress' => $daysCount > 0 ? round($totalProgress / $daysCount, 2) : 0,
            'total_days' => $daysCount,
            'completed_days' => $logs->where('progress_percentage', 100)->count(),
        ];
    }

    /**
     * Get weekly average progress.
     *
     * @param int $userId
     * @return float
     */
    public function getWeeklyAverage(int $userId): float
    {
        $endDate = today();
        $startDate = today()->subDays(6);

        $average = IbadatLog::forUser($userId)
                            ->forDateRange($startDate, $endDate)
                            ->avg('progress_percentage');

        return round($average ?? 0, 2);
    }

    /**
     * Get monthly progress for a user.
     *
     * @param int $userId
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getMonthlyProgress(int $userId, int $month = null, int $year = null): array
    {
        $month = $month ?? today()->month;
        $year = $year ?? today()->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $logs = IbadatLog::forUser($userId)
                         ->forDateRange($startDate, $endDate)
                         ->get();

        $totalDays = $startDate->daysInMonth;
        $trackedDays = $logs->count();
        $completedDays = $logs->where('progress_percentage', 100)->count();
        $averageProgress = $logs->avg('progress_percentage') ?? 0;

        // Calculate category averages
        $prayerSum = 0;
        $fastingDays = 0;
        $quranDays = 0;
        $charityDays = 0;

        foreach ($logs as $log) {
            $prayerSum += $log->completed_prayers_count;
            if ($log->roza_completed) $fastingDays++;
            if ($log->quran_completed) $quranDays++;
            if ($log->charity_completed) $charityDays++;
        }

        return [
            'month' => $startDate->format('F Y'),
            'total_days' => $totalDays,
            'tracked_days' => $trackedDays,
            'completed_days' => $completedDays,
            'average_progress' => round($averageProgress, 2),
            'prayer_average' => $trackedDays > 0 ? round(($prayerSum / ($trackedDays * 5)) * 100, 2) : 0,
            'fasting_percentage' => $trackedDays > 0 ? round(($fastingDays / $trackedDays) * 100, 2) : 0,
            'quran_percentage' => $trackedDays > 0 ? round(($quranDays / $trackedDays) * 100, 2) : 0,
            'charity_percentage' => $trackedDays > 0 ? round(($charityDays / $trackedDays) * 100, 2) : 0,
        ];
    }

    /**
     * Get progress comparison between periods.
     *
     * @param int $userId
     * @param Carbon $currentStart
     * @param Carbon $currentEnd
     * @param Carbon $previousStart
     * @param Carbon $previousEnd
     * @return array
     */
    public function getProgressComparison(
        int $userId,
        Carbon $currentStart,
        Carbon $currentEnd,
        Carbon $previousStart,
        Carbon $previousEnd
    ): array {
        $currentLogs = IbadatLog::forUser($userId)
                                ->forDateRange($currentStart, $currentEnd)
                                ->get();

        $previousLogs = IbadatLog::forUser($userId)
                                 ->forDateRange($previousStart, $previousEnd)
                                 ->get();

        $currentAvg = $currentLogs->avg('progress_percentage') ?? 0;
        $previousAvg = $previousLogs->avg('progress_percentage') ?? 0;

        return [
            'current' => [
                'average' => round($currentAvg, 2),
                'days_tracked' => $currentLogs->count(),
                'perfect_days' => $currentLogs->where('progress_percentage', 100)->count(),
            ],
            'previous' => [
                'average' => round($previousAvg, 2),
                'days_tracked' => $previousLogs->count(),
                'perfect_days' => $previousLogs->where('progress_percentage', 100)->count(),
            ],
            'change' => [
                'average' => round($currentAvg - $previousAvg, 2),
                'percentage_change' => $previousAvg > 0 
                    ? round((($currentAvg - $previousAvg) / $previousAvg) * 100, 2) 
                    : 0,
            ],
        ];
    }

    /**
     * Get category breakdown for a period.
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getCategoryBreakdown(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $logs = IbadatLog::forUser($userId)
                         ->forDateRange($startDate, $endDate)
                         ->with(['prayers', 'quranLog', 'charityRecord'])
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
        $quranCompleted = $logs->where('quran_completed', true)->count();
        $charityCompleted = $logs->where('charity_completed', true)->count();

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
     * Get progress chart data.
     *
     * @param int $userId
     * @param int $days
     * @return array
     */
    public function getProgressChartData(int $userId, int $days = 30): array
    {
        $endDate = today();
        $startDate = today()->subDays($days - 1);

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
        $quranData = [];
        $charityData = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('M d');

            $log = $logs->get($dateKey);

            if ($log) {
                $progressData[] = $log->progress_percentage;
                $prayerData[] = $log->completed_prayers_count;
                $fastingData[] = $log->roza_completed ? 1 : 0;
                $quranData[] = $log->quran_completed ? 1 : 0;
                $charityData[] = $log->charity_completed ? 1 : 0;
            } else {
                $progressData[] = 0;
                $prayerData[] = 0;
                $fastingData[] = 0;
                $quranData[] = 0;
                $charityData[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Overall Progress (%)',
                    'data' => $progressData,
                    'color' => 'rgba(75, 192, 192, 1)',
                ],
                [
                    'label' => 'Prayers',
                    'data' => $prayerData,
                    'color' => 'rgba(54, 162, 235, 1)',
                ],
                [
                    'label' => 'Fasting',
                    'data' => $fastingData,
                    'color' => 'rgba(255, 99, 132, 1)',
                ],
                [
                    'label' => 'Quran',
                    'data' => $quranData,
                    'color' => 'rgba(255, 206, 86, 1)',
                ],
                [
                    'label' => 'Charity',
                    'data' => $charityData,
                    'color' => 'rgba(153, 102, 255, 1)',
                ],
            ],
        ];
    }

    /**
     * Get overall statistics for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getOverallStatistics(int $userId): array
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

        $firstLog = IbadatLog::forUser($userId)->orderBy('log_date', 'asc')->first();
        $daysSinceStart = $firstLog ? $firstLog->log_date->diffInDays(today()) + 1 : 0;

        return [
            'total_days_tracked' => $totalLogs,
            'perfect_days' => $completedLogs,
            'total_prayers' => $totalPrayers,
            'total_charity' => $totalCharity,
            'total_quran_pages' => $totalQuranPages,
            'average_progress' => $totalLogs > 0 
                ? round(IbadatLog::forUser($userId)->avg('progress_percentage'), 2) 
                : 0,
            'consistency_rate' => $daysSinceStart > 0 
                ? round(($totalLogs / $daysSinceStart) * 100, 2) 
                : 0,
        ];
    }
}
