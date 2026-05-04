<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Family Model
 * 
 * Represents a family group in the system.
 * Families have a unique code for joining.
 */
class Family extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'families';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'family_code',
        'description',
        'created_by',
        'family_streak',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'family_streak' => 'integer',
    ];

    /**
     * Boot method to auto-generate family code.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($family) {
            if (empty($family->family_code)) {
                $family->family_code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique family code.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('family_code', $code)->exists());

        return $code;
    }

    /**
     * Get the creator of this family.
     * Relationship: Family belongs to User (creator) (N:1)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all members of this family.
     * Relationship: Family belongs to many Users (N:M) via FamilyMember
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'family_members')
                    ->withPivot('role', 'joined_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get family memberships.
     * Relationship: Family has many FamilyMembers (1:N)
     */
    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Get parents in the family.
     */
    public function parents()
    {
        return $this->members()->wherePivot('role', 'parent');
    }

    /**
     * Get children in the family.
     */
    public function children()
    {
        return $this->members()->wherePivot('role', 'child');
    }

    /**
     * Get active members.
     */
    public function activeMembers()
    {
        return $this->members()->wherePivot('is_active', true);
    }

    /**
     * Check if user is a member.
     */
    public function hasMember(int $userId): bool
    {
        return $this->familyMembers()
                    ->where('user_id', $userId)
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Check if user is a parent.
     */
    public function hasParent(int $userId): bool
    {
        return $this->familyMembers()
                    ->where('user_id', $userId)
                    ->where('role', 'parent')
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Add a member to the family.
     */
    public function addMember(int $userId, string $role = 'child'): FamilyMember
    {
        return $this->familyMembers()->create([
            'user_id' => $userId,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    /**
     * Remove a member from the family.
     */
    public function removeMember(int $userId): void
    {
        $this->familyMembers()
             ->where('user_id', $userId)
             ->update(['is_active' => false]);
    }

    /**
     * Get family progress (average of all members).
     */
    public function getFamilyProgressAttribute(): array
    {
        $members = $this->activeMembers;
        $totalMembers = $members->count();

        if ($totalMembers === 0) {
            return [
                'prayer_avg' => 0,
                'fasting_avg' => 0,
                'quran_avg' => 0,
                'charity_avg' => 0,
                'overall_avg' => 0,
            ];
        }

        $prayerTotal = 0;
        $fastingTotal = 0;
        $quranTotal = 0;
        $charityTotal = 0;
        $overallTotal = 0;

        foreach ($members as $member) {
            $todayLog = $member->todaysIbadatLog;
            if ($todayLog) {
                $prayerTotal += $todayLog->completed_prayers_count;
                $fastingTotal += $todayLog->roza_completed ? 1 : 0;
                $quranTotal += $todayLog->quran_completed ? 1 : 0;
                $charityTotal += $todayLog->charity_completed ? 1 : 0;
                $overallTotal += $todayLog->progress_percentage;
            }
        }

        return [
            'prayer_avg' => round(($prayerTotal / ($totalMembers * 5)) * 100, 2),
            'fasting_avg' => round(($fastingTotal / $totalMembers) * 100, 2),
            'quran_avg' => round(($quranTotal / $totalMembers) * 100, 2),
            'charity_avg' => round(($charityTotal / $totalMembers) * 100, 2),
            'overall_avg' => round($overallTotal / $totalMembers, 2),
        ];
    }

    /**
     * Get total charity contributed by family.
     */
    public function getTotalCharityAttribute(): float
    {
        $memberIds = $this->activeMembers->pluck('id');
        
        return CharityRecord::whereHas('ibadatLog', function ($query) use ($memberIds) {
            $query->whereIn('user_id', $memberIds);
        })->sum('amount');
    }

    /**
     * Scope: Find by family code.
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('family_code', $code);
    }

    /**
     * Scope: Find by creator.
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
