<?php

namespace App\Services;

use App\Models\Family;
use App\Models\IbadatLog;
use Carbon\Carbon;

/**
 * Family Service
 * 
 * Business logic for family-related operations.
 * Handles family progress, member management, and shared tracking.
 */
class FamilyService
{
    /**
     * Get detailed family information.
     *
     * @param int $familyId
     * @return array
     */
    public function getFamilyDetails(int $familyId): array
    {
        $family = Family::with(['members', 'familyMembers'])->findOrFail($familyId);

        $members = $family->activeMembers;
        $memberDetails = [];

        foreach ($members as $member) {
            $memberDetails[] = [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->pivot->role,
                'joined_at' => \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y'),
                'today_progress' => $this->getMemberTodayProgress($member->id),
                'streaks' => $member->streaks->pluck('current_streak', 'streak_type'),
            ];
        }

        return [
            'id' => $family->id,
            'name' => $family->name,
            'code' => $family->family_code,
            'description' => $family->description,
            'created_by' => $family->creator->name,
            'member_count' => $members->count(),
            'members' => $memberDetails,
            'family_streak' => $family->family_streak,
            'total_charity' => $family->total_charity,
            'progress' => $family->family_progress,
        ];
    }

    /**
     * Get member's today progress.
     *
     * @param int $userId
     * @return array
     */
    public function getMemberTodayProgress(int $userId): array
    {
        $log = IbadatLog::forUser($userId)
                        ->forDate(today())
                        ->with(['prayers', 'quranLog', 'charityRecord'])
                        ->first();

        if (!$log) {
            return [
                'prayers' => 0,
                'fasting' => false,
                'quran' => false,
                'charity' => false,
                'progress' => 0,
            ];
        }

        return [
            'prayers' => $log->completed_prayers_count,
            'fasting' => $log->roza_completed,
            'quran' => $log->quran_completed,
            'charity' => $log->charity_completed,
            'progress' => $log->progress_percentage,
        ];
    }

    /**
     * Get member's weekly progress.
     *
     * @param int $userId
     * @return array
     */
    public function getMemberWeeklyProgress(int $userId): array
    {
        $endDate = today();
        $startDate = today()->subDays(6);

        $logs = IbadatLog::forUser($userId)
                         ->forDateRange($startDate, $endDate)
                         ->get();

        $totalProgress = $logs->sum('progress_percentage');
        $daysCount = $logs->count();

        return [
            'average_progress' => $daysCount > 0 ? round($totalProgress / $daysCount, 2) : 0,
            'days_tracked' => $daysCount,
            'perfect_days' => $logs->where('progress_percentage', 100)->count(),
        ];
    }

    /**
     * Get family leaderboard.
     *
     * @param int $familyId
     * @param string $period
     * @return array
     */
    public function getLeaderboard(int $familyId, string $period = 'week'): array
    {
        $family = Family::findOrFail($familyId);
        $members = $family->activeMembers;

        switch ($period) {
            case 'day':
                $startDate = today();
                $endDate = today();
                break;
            case 'week':
                $startDate = today()->startOfWeek();
                $endDate = today()->endOfWeek();
                break;
            case 'month':
                $startDate = today()->startOfMonth();
                $endDate = today()->endOfMonth();
                break;
            default:
                $startDate = today()->subDays(6);
                $endDate = today();
        }

        $leaderboard = [];

        foreach ($members as $member) {
            $logs = IbadatLog::forUser($member->id)
                             ->forDateRange($startDate, $endDate)
                             ->get();

            $totalProgress = $logs->sum('progress_percentage');
            $daysCount = max($logs->count(), 1);
            $averageProgress = round($totalProgress / $daysCount, 2);

            $leaderboard[] = [
                'user_id' => $member->id,
                'name' => $member->name,
                'avatar' => $member->avatar,
                'average_progress' => $averageProgress,
                'perfect_days' => $logs->where('progress_percentage', 100)->count(),
                'total_prayers' => $logs->sum(function ($log) {
                    return $log->completed_prayers_count;
                }),
            ];
        }

        // Sort by average progress descending
        usort($leaderboard, function ($a, $b) {
            return $b['average_progress'] <=> $a['average_progress'];
        });

        // Add rank
        foreach ($leaderboard as $index => &$entry) {
            $entry['rank'] = $index + 1;
        }

        return $leaderboard;
    }

    /**
     * Get family progress chart data.
     *
     * @param int $familyId
     * @param int $days
     * @return array
     */
    public function getFamilyChartData(int $familyId, int $days = 7): array
    {
        $family = Family::findOrFail($familyId);
        $members = $family->activeMembers;
        $endDate = today();
        $startDate = today()->subDays($days - 1);

        $labels = [];
        $datasets = [];

        // Generate labels
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $labels[] = $date->format('M d');
        }

        // Generate dataset for each member
        foreach ($members as $member) {
            $logs = IbadatLog::forUser($member->id)
                             ->forDateRange($startDate, $endDate)
                             ->get()
                             ->keyBy(function ($log) {
                                 return $log->log_date->format('Y-m-d');
                             });

            $data = [];
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                $log = $logs->get($dateKey);
                $data[] = $log ? $log->progress_percentage : 0;
            }

            $datasets[] = [
                'label' => $member->name,
                'data' => $data,
            ];
        }

        // Add family average
        $familyAverage = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $dayTotal = 0;
            $memberCount = 0;

            foreach ($members as $member) {
                $log = IbadatLog::forUser($member->id)
                                ->forDate($date)
                                ->first();
                if ($log) {
                    $dayTotal += $log->progress_percentage;
                    $memberCount++;
                }
            }

            $familyAverage[] = $memberCount > 0 ? round($dayTotal / $memberCount, 2) : 0;
        }

        $datasets[] = [
            'label' => 'Family Average',
            'data' => $familyAverage,
            'borderDash' => [5, 5],
        ];

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Update family streak.
     *
     * @param int $familyId
     * @return void
     */
    public function updateFamilyStreak(int $familyId): void
    {
        $family = Family::findOrFail($familyId);
        $members = $family->activeMembers;

        // Check if all members completed their ibadat today
        $allCompleted = true;

        foreach ($members as $member) {
            $log = IbadatLog::forUser($member->id)
                            ->forDate(today())
                            ->first();

            if (!$log || $log->progress_percentage < 100) {
                $allCompleted = false;
                break;
            }
        }

        // Update family streak
        if ($allCompleted && $members->count() > 0) {
            $family->family_streak += 1;
        } else {
            $family->family_streak = 0;
        }

        $family->save();
    }

    /**
     * Get family charity summary.
     *
     * @param int $familyId
     * @return array
     */
    public function getFamilyCharitySummary(int $familyId): array
    {
        $family = Family::findOrFail($familyId);
        $members = $family->activeMembers;

        $memberCharity = [];
        $totalAmount = 0;
        $totalActs = 0;

        foreach ($members as $member) {
            $charityRecords = \App\Models\CharityRecord::whereHas('ibadatLog', function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })->get();

            $amount = $charityRecords->sum('amount');
            $acts = $charityRecords->sum('acts_count');

            $memberCharity[] = [
                'user_id' => $member->id,
                'name' => $member->name,
                'amount' => $amount,
                'acts' => $acts,
            ];

            $totalAmount += $amount;
            $totalActs += $acts;
        }

        // Sort by amount
        usort($memberCharity, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return [
            'total_amount' => $totalAmount,
            'total_acts' => $totalActs,
            'member_contributions' => $memberCharity,
        ];
    }

    /**
     * Get family statistics.
     *
     * @param int $familyId
     * @return array
     */
    public function getFamilyStatistics(int $familyId): array
    {
        $family = Family::findOrFail($familyId);
        $members = $family->activeMembers;

        $totalMembers = $members->count();
        $totalPrayers = 0;
        $totalFastingDays = 0;
        $totalQuranPages = 0;
        $totalCharity = 0;

        foreach ($members as $member) {
            $logs = IbadatLog::forUser($member->id)->get();

            $totalPrayers += $logs->sum(function ($log) {
                return $log->completed_prayers_count;
            });

            $totalFastingDays += $logs->where('roza_completed', true)->count();

            $totalQuranPages += \App\Models\QuranLog::whereHas('ibadatLog', function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })->sum('pages_read');

            $totalCharity += \App\Models\CharityRecord::whereHas('ibadatLog', function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })->sum('amount');
        }

        return [
            'total_members' => $totalMembers,
            'total_prayers' => $totalPrayers,
            'total_fasting_days' => $totalFastingDays,
            'total_quran_pages' => $totalQuranPages,
            'total_charity' => $totalCharity,
            'family_streak' => $family->family_streak,
        ];
    }

    /**
     * Check if user can join family.
     *
     * @param int $userId
     * @param int $familyId
     * @return array
     */
    public function canJoinFamily(int $userId, int $familyId): array
    {
        $family = Family::find($familyId);

        if (!$family) {
            return ['can_join' => false, 'reason' => 'Family not found.'];
        }

        if ($family->hasMember($userId)) {
            return ['can_join' => false, 'reason' => 'You are already a member.'];
        }

        return ['can_join' => true, 'reason' => null];
    }
}
