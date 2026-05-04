<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Represents a user in the system.
 * Relationships: IbadatLogs, Streaks, Families, FamilyMembers, Milestones
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'date_of_birth',
        'gender',
        'bio',
        'language',
        'timezone',
        'email_notifications',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'email_notifications' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all ibadat logs for this user.
     * Relationship: User has many IbadatLogs (1:N)
     */
    public function ibadatLogs()
    {
        return $this->hasMany(IbadatLog::class);
    }

    /**
     * Get today's ibadat log for this user.
     */
    public function todaysIbadatLog()
    {
        return $this->hasOne(IbadatLog::class)
                    ->whereDate('log_date', today());
    }

    /**
     * Get all streaks for this user.
     * Relationship: User has many Streaks (1:N)
     */
    public function streaks()
    {
        return $this->hasMany(Streak::class);
    }

    /**
     * Get specific streak type for this user.
     */
    public function streak(string $type)
    {
        return $this->hasOne(Streak::class)
                    ->where('streak_type', $type);
    }

    /**
     * Get all families this user belongs to.
     * Relationship: User belongs to many Families (N:M) via FamilyMember
     */
    public function families()
    {
        return $this->belongsToMany(Family::class, 'family_members')
                    ->withPivot('role', 'joined_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get family memberships for this user.
     * Relationship: User has many FamilyMembers (1:N)
     */
    public function familyMemberships()
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Get families created by this user.
     * Relationship: User has many Families (1:N)
     */
    public function createdFamilies()
    {
        return $this->hasMany(Family::class, 'created_by');
    }

    /**
     * Get all milestones/badges for this user.
     * Relationship: User has many Milestones (1:N)
     */
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Check if user is a parent in any family.
     */
    public function isParent(): bool
    {
        return $this->familyMemberships()
                    ->where('role', 'parent')
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Check if user belongs to a family.
     */
    public function belongsToFamily(): bool
    {
        return $this->familyMemberships()
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Get user's primary family.
     */
    public function primaryFamily()
    {
        $membership = $this->familyMemberships()
                          ->where('is_active', true)
                          ->first();
        
        return $membership ? $membership->family : null;
    }

    /**
     * Get user's full profile with stats.
     */
    public function getProfileAttribute()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'member_since' => $this->created_at->format('M Y'),
            'total_logs' => $this->ibadatLogs()->count(),
            'current_streaks' => $this->streaks()->pluck('current_streak', 'streak_type'),
            'milestones_count' => $this->milestones()->count(),
        ];
    }
}
