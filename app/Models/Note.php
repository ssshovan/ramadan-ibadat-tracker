<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Note Model
 * 
 * Represents a note attached to an ibadat log.
 * Can be categorized as general, prayer, quran, charity, or reflection.
 */
class Note extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ibadat_log_id',
        'category',
        'content',
        'is_important',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_important' => 'boolean',
    ];

    /**
     * Get the ibadat log that owns this note.
     * Relationship: Note belongs to IbadatLog (N:1)
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
     * Mark as important.
     */
    public function markAsImportant(): void
    {
        $this->is_important = true;
        $this->save();
    }

    /**
     * Mark as not important.
     */
    public function markAsNotImportant(): void
    {
        $this->is_important = false;
        $this->save();
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            'general' => 'General',
            'prayer' => 'Prayer',
            'quran' => 'Quran',
            'charity' => 'Charity',
            'reflection' => 'Reflection',
        ];

        return $labels[$this->category] ?? 'General';
    }

    /**
     * Get category icon.
     */
    public function getCategoryIconAttribute(): string
    {
        $icons = [
            'general' => '📝',
            'prayer' => '🤲',
            'quran' => '📖',
            'charity' => '💝',
            'reflection' => '💭',
        ];

        return $icons[$this->category] ?? '📝';
    }

    /**
     * Scope: Get notes by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Get important notes.
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope: Get notes for a user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('ibadatLog', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope: Get notes for a date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereHas('ibadatLog', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('log_date', [$startDate, $endDate]);
        });
    }

    /**
     * Get excerpt of content (limited characters).
     */
    public function getExcerptAttribute(int $length = 100): string
    {
        return Str::limit($this->content, $length);
    }
}

// Need to import Str
use Illuminate\Support\Str;
