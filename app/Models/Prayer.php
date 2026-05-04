<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Prayer Model
 * 
 * Represents a prayer record within an ibadat log.
 * 5 prayers per day: Fajr, Dhuhr, Asr, Maghrib, Isha
 */
class Prayer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prayers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ibadat_log_id',
        'prayer_name',
        'is_completed',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the ibadat log that owns this prayer.
     * Relationship: Prayer belongs to IbadatLog (N:1)
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
     * Mark prayer as completed.
     */
    public function markAsCompleted(): void
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Mark prayer as not completed.
     */
    public function markAsNotCompleted(): void
    {
        $this->is_completed = false;
        $this->completed_at = null;
        $this->save();
    }

    /**
     * Scope: Get completed prayers.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope: Get pending prayers.
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope: Get prayers by name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('prayer_name', $name);
    }

    /**
     * Get prayer name in Arabic.
     */
    public function getArabicNameAttribute(): string
    {
        $names = [
            'Fajr' => 'الفجر',
            'Dhuhr' => 'الظهر',
            'Asr' => 'العصر',
            'Maghrib' => 'المغرب',
            'Isha' => 'العشاء',
        ];

        return $names[$this->prayer_name] ?? $this->prayer_name;
    }
}
