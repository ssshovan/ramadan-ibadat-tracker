<?php

namespace App\Services;

use App\Models\Streak;
use App\Models\IbadatLog;
use App\Models\Milestone;
use Carbon\Carbon;

/**
 * Streak Service
 * 
 * Business logic for streak calculation and management.
 * Handles all streak-related operations.
 */
class StreakService
{
    /**
     * Get all streaks for a user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllStreaks(int $userId)
    {
        $streakTypes = ['prayer', 'fasting', 'quran', 'charity'];
        $streaks = collect();

        foreach ($streakTypes as $type) {
            $streak = Streak::initializeForUser($userId, $type);
            $streaks->push($streak);
        }

        return $streaks;
    }

    /**
     * Update prayer streak for user.
     *
     * @param int $userId
     * @return Streak
     */
    public function updatePrayerStreak(int $userId): Streak
    {
        $streak = Streak::initializeForUser($userId, 'prayer');

        // Check if all prayers completed today
        $todayLog = IbadatLog::forUser($userId)
                             ->forDate(today())
                             ->with('prayers')
                             ->first();

        if (!$todayLog) {
            return $streak;
        }

        $allPrayersCompleted = $todayLog->all_prayers_completed;

        // Check yesterday's status for streak continuity
        $yesterdayLog = IbadatLog::forUser($userId)
                                 ->forDate(today()->subDay())
                                 ->with('prayers')
                                 ->first();

        $yesterdayCompleted = $yesterdayLog ? $yesterdayLog->all_prayers_completed : false;

        $this->updateStreak($streak, $allPrayersCompleted, $yesterdayCompleted);

        // Check for milestones
        $this->checkMilestone($userId, 'prayer', $streak->current_streak);

        return $streak;
    }

    /**
     * Update fasting streak for user.
     *
     * @param int $userId
     * @return Streak
     */
    public function updateFastingStreak(int $userId): Streak
    {
        $streak = Streak::initializeForUser($userId, 'fasting');

        // Check today's fasting status
        $todayLog = IbadatLog::forUser($userId)
                             ->forDate(today())
                             ->first();

        if (!$todayLog) {
            return $streak;
        }

        $fastingCompleted = $todayLog->roza_completed;

        // Check yesterday's status
        $yesterdayLog = IbadatLog::forUser($userId)
                                 ->forDate(today()->subDay())
                                 ->first();

        $yesterdayCompleted = $yesterdayLog ? $yesterdayLog->roza_completed : false;

        $this->updateStreak($streak, $fastingCompleted, $yesterdayCompleted);

        // Check for milestones
        $this->checkMilestone($userId, 'fasting', $streak->current_streak);

        return $streak;
    }

    /**
     * Update Quran streak for user.
     *
     * @param int $userId
     * @return Streak
     */
    public function updateQuranStreak(int $userId): Streak
    {
        $streak = Streak::initializeForUser($userId, 'quran');

        // Check today's Quran status
        $todayLog = IbadatLog::forUser($userId)
                             ->forDate(today())
                             ->with('quranLog')
                             ->first();

        if (!$todayLog) {
            return $streak;
        }

        $quranCompleted = $todayLog->quran_completed;

        // Check yesterday's status
        $yesterdayLog = IbadatLog::forUser($userId)
                                 ->forDate(today()->subDay())
                                 ->with('quranLog')
                                 ->first();

        $yesterdayCompleted = $yesterdayLog ? $yesterdayLog->quran_completed : false;

        $this->updateStreak($streak, $quranCompleted, $yesterdayCompleted);

        // Check for milestones
        $this->checkMilestone($userId, 'quran', $streak->current_streak);

        return $streak;
    }

    /**
     * Update charity streak for user.
     *
     * @param int $userId
     * @return Streak
     */
    public function updateCharityStreak(int $userId): Streak
    {
        $streak = Streak::initializeForUser($userId, 'charity');

        // Check today's charity status
        $todayLog = IbadatLog::forUser($userId)
                             ->forDate(today())
                             ->with('charityRecord')
                             ->first();

        if (!$todayLog) {
            return $streak;
        }

        $charityCompleted = $todayLog->charity_completed;

        // Check yesterday's status
        $yesterdayLog = IbadatLog::forUser($userId)
                                 ->forDate(today()->subDay())
                                 ->with('charityRecord')
                                 ->first();

        $yesterdayCompleted = $yesterdayLog ? $yesterdayLog->charity_completed : false;

        $this->updateStreak($streak, $charityCompleted, $yesterdayCompleted);

        // Check for milestones
        $this->checkMilestone($userId, 'charity', $streak->current_streak);

        return $streak;
    }

    /**
     * Update streak based on completion status.
     *
     * @param Streak $streak
     * @param bool $completedToday
     * @param bool $completedYesterday
     * @return void
     */
    private function updateStreak(Streak $streak, bool $completedToday, bool $completedYesterday): void
    {
        $lastUpdated = $streak->last_updated;

        // If streak was never updated, start fresh
        if (!$lastUpdated) {
            if ($completedToday) {
                $streak->startStreak();
            }
            return;
        }

        // Check if already updated today
        if ($lastUpdated->equalTo(today())) {
            return; // Already processed today
        }

        // Check if streak should be reset (missed more than one day)
        if ($streak->shouldReset()) {
            $streak->resetStreak();
            if ($completedToday) {
                $streak->startStreak();
            }
            return;
        }

        // Update streak based on completion
        if ($completedToday) {
            if ($completedYesterday || $lastUpdated->equalTo(today()->subDay())) {
                $streak->incrementStreak();
            } else {
                $streak->startStreak();
            }
        } else {
            // If missed today, reset streak
            $streak->resetStreak();
        }
    }

    /**
     * Check and award milestone.
     *
     * @param int $userId
     * @param string $type
     * @param int $streak
     * @return void
     */
    private function checkMilestone(int $userId, string $type, int $streak): void
    {
        $milestones = [7, 14, 21, 30];

        foreach ($milestones as $milestone) {
            if ($streak >= $milestone) {
                $milestoneType = "{$type}_streak_{$milestone}";
                Milestone::award($userId, $milestoneType);
            }
        }
    }

    /**
     * Recalculate all streaks for a user.
     *
     * @param int $userId
     * @return void
     */
    public function recalculateAllStreaks(int $userId): void
    {
        $types = ['prayer', 'fasting', 'quran', 'charity'];

        foreach ($types as $type) {
            $this->recalculateStreak($userId, $type);
        }
    }

    /**
     * Recalculate a specific streak.
     *
     * @param int $userId
     * @param string $type
     * @return void
     */
    private function recalculateStreak(int $userId, string $type): void
    {
        $streak = Streak::initializeForUser($userId, $type);

        // Get all logs ordered by date
        $logs = IbadatLog::forUser($userId)
                         ->orderBy('log_date', 'asc')
                         ->with(['prayers', 'quranLog', 'charityRecord'])
                         ->get();

        $currentStreak = 0;
        $longestStreak = 0;
        $previousDate = null;

        foreach ($logs as $log) {
            $completed = $this->checkCompletion($log, $type);

            if ($completed) {
                // Check if consecutive
                if ($previousDate && $log->log_date->equalTo($previousDate->addDay())) {
                    $currentStreak++;
                } else {
                    $currentStreak = 1;
                }

                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }

            $previousDate = $log->log_date;
        }

        // Update streak record
        $streak->current_streak = $currentStreak;
        $streak->longest_streak = $longestStreak;
        $streak->last_updated = $logs->last()?->log_date;
        $streak->save();
    }

    /**
     * Check if specific type is completed in log.
     *
     * @param IbadatLog $log
     * @param string $type
     * @return bool
     */
    private function checkCompletion(IbadatLog $log, string $type): bool
    {
        switch ($type) {
            case 'prayer':
                return $log->all_prayers_completed;
            case 'fasting':
                return $log->roza_completed;
            case 'quran':
                return $log->quran_completed;
            case 'charity':
                return $log->charity_completed;
            default:
                return false;
        }
    }

    /**
     * Get streak summary for dashboard.
     *
     * @param int $userId
     * @return array
     */
    public function getStreakSummary(int $userId): array
    {
        $streaks = $this->getAllStreaks($userId);

        $summary = [];
        foreach ($streaks as $streak) {
            $summary[$streak->streak_type] = [
                'current' => $streak->current_streak,
                'longest' => $streak->longest_streak,
                'badge' => $streak->milestone_badge,
                'status' => $streak->isActive() ? 'active' : 'inactive',
            ];
        }

        return $summary;
    }
}
