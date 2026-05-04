<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * QuranLog Model
 * 
 * Represents Quran recitation progress for a day.
 * Tracks pages read, surahs, and juz completed.
 */
class QuranLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quran_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ibadat_log_id',
        'pages_read',
        'start_surah',
        'end_surah',
        'juz_completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pages_read' => 'integer',
        'juz_completed' => 'integer',
    ];

    /**
     * Get the ibadat log that owns this Quran log.
     * Relationship: QuranLog belongs to IbadatLog (N:1)
     */
    public function ibadatLog()
    {
        return $this->belongsTo(IbadatLog::class);
    }

    /**
     * Get the user through ibadat log.
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, IbadatLog::class);
    }

    /**
     * Add pages read.
     */
    public function addPages(int $pages): void
    {
        $this->pages_read += $pages;
        $this->save();
    }

    /**
     * Check if Quran reading is completed for the day.
     */
    public function isCompleted(): bool
    {
        return $this->pages_read > 0;
    }

    /**
     * Get estimated juz based on pages (20 pages = 1 juz).
     */
    public function getEstimatedJuzAttribute(): float
    {
        return round($this->pages_read / 20, 2);
    }

    /**
     * Scope: Get logs with pages read.
     */
    public function scopeWithPages($query)
    {
        return $query->where('pages_read', '>', 0);
    }

    /**
     * Scope: Get logs with juz completed.
     */
    public function scopeWithJuz($query)
    {
        return $query->whereNotNull('juz_completed');
    }

    /**
     * Get total pages read by a user.
     */
    public static function totalPagesByUser(int $userId): int
    {
        return self::whereHas('ibadatLog', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->sum('pages_read');
    }
}
