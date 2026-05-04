<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * FamilyMember Model (Pivot)
 * 
 * Represents the relationship between users and families.
 * Tracks role (parent/child) and membership status.
 */
class FamilyMember extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'family_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'family_id',
        'user_id',
        'role',
        'joined_at',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'joined_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the family.
     * Relationship: FamilyMember belongs to Family (N:1)
     */
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Get the user.
     * Relationship: FamilyMember belongs to User (N:1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if member is a parent.
     */
    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    /**
     * Check if member is a child.
     */
    public function isChild(): bool
    {
        return $this->role === 'child';
    }

    /**
     * Promote to parent.
     */
    public function promoteToParent(): void
    {
        $this->role = 'parent';
        $this->save();
    }

    /**
     * Demote to child.
     */
    public function demoteToChild(): void
    {
        $this->role = 'child';
        $this->save();
    }

    /**
     * Activate membership.
     */
    public function activate(): void
    {
        $this->is_active = true;
        $this->save();
    }

    /**
     * Deactivate membership.
     */
    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }

    /**
     * Scope: Get parents.
     */
    public function scopeParents($query)
    {
        return $query->where('role', 'parent');
    }

    /**
     * Scope: Get children.
     */
    public function scopeChildren($query)
    {
        return $query->where('role', 'child');
    }

    /**
     * Scope: Get active members.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get members of a family.
     */
    public function scopeForFamily($query, $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    /**
     * Scope: Get membership for a user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
