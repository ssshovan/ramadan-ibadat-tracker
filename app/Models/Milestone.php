<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Milestone Model
 * 
 * Represents achievements and badges earned by users.
 * Tracks various streak milestones and special achievements.
 */
class Milestone extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'milestones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'description',
        'icon',
        'earned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'earned_at' => 'datetime',
    ];

    /**
     * Get the user that owns this milestone.
     * Relationship: Milestone belongs to User (N:1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get milestone type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'prayer_streak_7' => '7-Day Prayer Streak',
            'prayer_streak_14' => '14-Day Prayer Streak',
            'prayer_streak_21' => '21-Day Prayer Streak',
            'prayer_streak_30' => '30-Day Prayer Streak',
            'fasting_streak_7' => '7-Day Fasting Streak',
            'fasting_streak_14' => '14-Day Fasting Streak',
            'fasting_streak_21' => '21-Day Fasting Streak',
            'fasting_streak_30' => '30-Day Fasting Streak',
            'quran_streak_7' => '7-Day Quran Streak',
            'quran_streak_14' => '14-Day Quran Streak',
            'quran_streak_21' => '21-Day Quran Streak',
            'quran_streak_30' => '30-Day Quran Streak',
            'charity_streak_7' => '7-Day Charity Streak',
            'charity_streak_14' => '14-Day Charity Streak',
            'charity_streak_21' => '21-Day Charity Streak',
            'charity_streak_30' => '30-Day Charity Streak',
            'complete_ramadan' => 'Complete Ramadan',
            'perfect_day' => 'Perfect Day',
            'family_contributor' => 'Family Contributor',
        ];

        return $labels[$this->type] ?? 'Achievement';
    }

    /**
     * Get milestone icon.
     */
    public function getMilestoneIconAttribute(): string
    {
        $icons = [
            'prayer_streak_7' => '🥉',
            'prayer_streak_14' => '🥈',
            'prayer_streak_21' => '🥇',
            'prayer_streak_30' => '🏆',
            'fasting_streak_7' => '🥉',
            'fasting_streak_14' => '🥈',
            'fasting_streak_21' => '🥇',
            'fasting_streak_30' => '🏆',
            'quran_streak_7' => '🥉',
            'quran_streak_14' => '🥈',
            'quran_streak_21' => '🥇',
            'quran_streak_30' => '🏆',
            'charity_streak_7' => '🥉',
            'charity_streak_14' => '🥈',
            'charity_streak_21' => '🥇',
            'charity_streak_30' => '🏆',
            'complete_ramadan' => '🌙',
            'perfect_day' => '⭐',
            'family_contributor' => '👨‍👩‍👧‍👦',
        ];

        return $icons[$this->type] ?? '🏅';
    }

    /**
     * Get milestone description based on type.
     */
    public function getDefaultDescriptionAttribute(): string
    {
        $descriptions = [
            'prayer_streak_7' => 'Completed all 5 prayers for 7 consecutive days!',
            'prayer_streak_14' => 'Completed all 5 prayers for 14 consecutive days!',
            'prayer_streak_21' => 'Completed all 5 prayers for 21 consecutive days!',
            'prayer_streak_30' => 'Completed all 5 prayers for 30 consecutive days! MashaAllah!',
            'fasting_streak_7' => 'Fasted for 7 consecutive days!',
            'fasting_streak_14' => 'Fasted for 14 consecutive days!',
            'fasting_streak_21' => 'Fasted for 21 consecutive days!',
            'fasting_streak_30' => 'Fasted for 30 consecutive days! MashaAllah!',
            'quran_streak_7' => 'Read Quran for 7 consecutive days!',
            'quran_streak_14' => 'Read Quran for 14 consecutive days!',
            'quran_streak_21' => 'Read Quran for 21 consecutive days!',
            'quran_streak_30' => 'Read Quran for 30 consecutive days! MashaAllah!',
            'charity_streak_7' => 'Gave charity for 7 consecutive days!',
            'charity_streak_14' => 'Gave charity for 14 consecutive days!',
            'charity_streak_21' => 'Gave charity for 21 consecutive days!',
            'charity_streak_30' => 'Gave charity for 30 consecutive days! MashaAllah!',
            'complete_ramadan' => 'Tracked ibadat throughout the entire Ramadan!',
            'perfect_day' => 'Completed 100% of daily ibadat!',
            'family_contributor' => 'Actively contributed to family progress!',
        ];

        return $descriptions[$this->type] ?? 'Amazing achievement!';
    }

    /**
     * Scope: Get milestones by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get milestones for a user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get recent milestones.
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('earned_at', 'desc')->limit($limit);
    }

    /**
     * Check if user has earned a specific milestone.
     */
    public static function hasEarned(int $userId, string $type): bool
    {
        return self::where('user_id', $userId)
                   ->where('type', $type)
                   ->exists();
    }

    /**
     * Award a milestone to a user.
     */
    public static function award(int $userId, string $type): ?self
    {
        // Check if already earned
        if (self::hasEarned($userId, $type)) {
            return null;
        }

        $milestone = new self();
        $milestone->user_id = $userId;
        $milestone->type = $type;
        $milestone->name = (new self(['type' => $type]))->type_label;
        $milestone->description = (new self(['type' => $type]))->default_description;
        $milestone->icon = (new self(['type' => $type]))->milestone_icon;
        $milestone->earned_at = now();
        $milestone->save();

        return $milestone;
    }
}
