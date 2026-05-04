<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Streak Model
 * 
 * Represents a user's streak for a specific category.
 * Categories: prayer, fasting, quran, charity
 */
class Streak extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'streaks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'streak_type',
        'current_streak',
        'longest_streak',
        'last_updated',
        'streak_start_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_updated' => 'date',
        'streak_start_date' => 'date',
    ];

    /**
     * Get the user that owns this streak.
     * Relationship: Streak belongs to User (N:1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment the streak.
     */
    public function incrementStreak(): void
    {
        $this->current_streak += 1;
        
        // Update longest streak if current is higher
        if ($this->current_streak > $this->longest_streak) {
            $this->longest_streak = $this->current_streak;
        }
        
        $this->last_updated = today();
        $this->save();
    }

    /**
     * Reset the streak to 0.
     */
    public function resetStreak(): void
    {
        $this->current_streak = 0;
        $this->streak_start_date = null;
        $this->last_updated = today();
        $this->save();
    }

    /**
     * Start a new streak.
     */
    public function startStreak(): void
    {
        $this->current_streak = 1;
        $this->streak_start_date = today();
        $this->last_updated = today();
        $this->save();
    }

    /**
     * Check if streak is active (updated today or yesterday).
     */
    public function isActive(): bool
    {
        if (!$this->last_updated) {
            return false;
        }

        $lastUpdated = Carbon::parse($this->last_updated);
        $today = today();
        $yesterday = today()->subDay();

        return $lastUpdated->equalTo($today) || $lastUpdated->equalTo($yesterday);
    }

    /**
     * Check if streak should be reset (missed a day).
     */
    public function shouldReset(): bool
    {
        if (!$this->last_updated) {
            return false;
        }

        $lastUpdated = Carbon::parse($this->last_updated);
        $yesterday = today()->subDay();

        // If last update was before yesterday, reset
        return $lastUpdated->lessThan($yesterday);
    }

    /**
     * Get streak status message.
     */
    public function getStatusMessageAttribute(): string
    {
        if ($this->current_streak === 0) {
            return "Start your {$this->streak_type} streak today!";
        }

        if ($this->isActive()) {
            return "Alhamdulillah! {$this->current_streak} day streak!";
        }

        return "Streak at risk! Complete your {$this->streak_type} today!";
    }

    /**
     * Get milestone badge based on streak.
     */
    public function getMilestoneBadgeAttribute(): ?string
    {
        $streak = $this->current_streak;

        if ($streak >= 30) return '🏆 30 Days';
        if ($streak >= 21) return '🥇 21 Days';
        if ($streak >= 14) return '🥈 14 Days';
        if ($streak >= 7) return '🥉 7 Days';

        return null;
    }

    /**
     * Scope: Get streaks by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('streak_type', $type);
    }

    /**
     * Scope: Get active streaks (current_streak > 0).
     */
    public function scopeActive($query)
    {
        return $query->where('current_streak', '>', 0);
    }

    /**
     * Scope: Get streaks for a user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Initialize streak for user if not exists.
     */
    public static function initializeForUser(int $userId, string $type): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId, 'streak_type' => $type],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
            ]
        );
    }
}
