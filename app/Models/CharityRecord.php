<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CharityRecord Model
 * 
 * Represents charity donations and acts for a day.
 * Tracks amount donated and number of charitable acts.
 */
class CharityRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'charity_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ibadat_log_id',
        'amount',
        'acts_count',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'acts_count' => 'integer',
    ];

    /**
     * Get the ibadat log that owns this charity record.
     * Relationship: CharityRecord belongs to IbadatLog (N:1)
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
     * Add charity amount.
     */
    public function addAmount(float $amount): void
    {
        $this->amount += $amount;
        $this->save();
    }

    /**
     * Add charity act.
     */
    public function addAct(string $description = null): void
    {
        $this->acts_count += 1;
        if ($description) {
            $currentDesc = $this->description ? $this->description . "\n" : "";
            $this->description = $currentDesc . "- " . $description;
        }
        $this->save();
    }

    /**
     * Check if charity is completed.
     */
    public function isCompleted(): bool
    {
        return $this->amount > 0 || $this->acts_count > 0;
    }

    /**
     * Scope: Get records with amount > 0.
     */
    public function scopeWithAmount($query)
    {
        return $query->where('amount', '>', 0);
    }

    /**
     * Scope: Get records with acts.
     */
    public function scopeWithActs($query)
    {
        return $query->where('acts_count', '>', 0);
    }
}
