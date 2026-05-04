<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * IbadatLog Model
 * 
 * Represents a daily ibadat record for a user.
 * One record per user per day.
 * Relationships: User, Prayers, CharityRecord, QuranLog, Notes
 */
class IbadatLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ibadat_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'log_date',
        'roza_completed',
        
        'progress_percentage',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'log_date' => 'date',
        'roza_completed' => 'boolean',
        'progress_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns this ibadat log.
     * Relationship: IbadatLog belongs to User (N:1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get prayers for this ibadat log.
     * Relationship: IbadatLog has many Prayers (1:N)
     */
    public function prayers()
    {
        return $this->hasMany(Prayer::class);
    }

    /**
     * Get charity record for this ibadat log.
     * Relationship: IbadatLog has one CharityRecord (1:1)
     */
    public function charityRecord(): HasOne
    {
        return $this->hasOne(CharityRecord::class);
    }

    /**
     * Get Quran log for this ibadat log.
     * Relationship: IbadatLog has one QuranLog (1:1)
     */
    public function quranLog(): HasOne
    {
        return $this->hasOne(QuranLog::class);
    }

    /**
     * Get notes for this ibadat log.
     * Relationship: IbadatLog has many Notes (1:N)
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get completed prayers count.
     */
    public function getCompletedPrayersCountAttribute(): int
    {
        return $this->prayers()->where('is_completed', true)->count();
    }

    /**
     * Get total prayers count (always 5).
     */
    public function getTotalPrayersCountAttribute(): int
    {
        return 5;
    }

    /**
     * Check if all prayers are completed.
     */
    public function getAllPrayersCompletedAttribute(): bool
    {
        return $this->completed_prayers_count === $this->total_prayers_count;
    }

    /**
     * Check if Quran reading is done (pages > 0).
     */
    public function getQuranCompletedAttribute(): bool
    {
        $quranLog = $this->quranLog;
        return $quranLog && $quranLog->pages_read > 0;
    }

    /**
     * Check if charity is done (amount > 0 or acts > 0).
     */
    public function getCharityCompletedAttribute(): bool
    {
        $charityRecord = $this->charityRecord;
        return $charityRecord && ($charityRecord->amount > 0 || $charityRecord->acts_count > 0);
    }

    /**
     * Calculate progress percentage.
     * Total tasks: 5 prayers + 1 roza + 1 quran + 1 charity = 8 tasks
     */
    public function calculateProgressPercentage(): float
    {
        $completed = 0;
        $total = 8;

        // Prayers (5 tasks)
        $completed += $this->completed_prayers_count;

        // Roza (1 task)
        if ($this->roza_completed) {
            $completed += 1;
        }

        // Quran (1 task)
        if ($this->quran_completed) {
            $completed += 1;
        }

        // Charity (1 task)
        if ($this->charity_completed) {
            $completed += 1;
        }

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Update progress percentage and save.
     */
    public function updateProgress(): void
    {
        $this->progress_percentage = $this->calculateProgressPercentage();
        $this->save();
    }

    /**
     * Scope: Get logs for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('log_date', $date);
    }

    /**
     * Scope: Get logs for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('log_date', today());
    }

    /**
     * Scope: Get logs for a date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('log_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get logs for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get completed logs (100% progress).
     */
    public function scopeCompleted($query)
    {
        return $query->where('progress_percentage', 100);
    }

    /**
     * Boot method to auto-create related records.
     */
    protected static function boot()
    {
        parent::boot();

        // Create default prayers when ibadat log is created
        static::created(function ($ibadatLog) {
            $prayerNames = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
            foreach ($prayerNames as $prayerName) {
                $ibadatLog->prayers()->create([
                    'prayer_name' => $prayerName,
                    'is_completed' => false,
                ]);
            }

            // Create empty charity record
            $ibadatLog->charityRecord()->create([
                'amount' => 0,
                'acts_count' => 0,
            ]);

            // Create empty Quran log
            $ibadatLog->quranLog()->create([
                'pages_read' => 0,
            ]);
        });
    }
}
