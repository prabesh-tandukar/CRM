<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'remindable_id',
        'remindable_type',
        'remind_at',
        'reminded',
        'user_id',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'reminded' => 'boolean',
    ];

    // Relationships
    public function remindable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('reminded', false)
            ->where('remind_at', '<=', now());
    }

    public function scopeUpcoming($query, $minutes = 60)
    {
        return $query->where('reminded', false)
            ->whereBetween('remind_at', [now(), now()->addMinutes($minutes)]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('reminded', true);
    }

    // Methods
    public function markAsReminded()
    {
        return $this->update(['reminded' => true]);
    }
}