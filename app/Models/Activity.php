<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'activity_type',
        'subject',
        'description',
        'due_date',
        'duration',
        'status',
        'priority',
        'completed_at',
        'activityable_id',
        'activityable_type',
        'owner_id',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'duration' => 'integer',
    ];

    // Relationships
    public function activityable()
    {
        return $this->morphTo();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reminders()
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>=', now())
            ->where('status', '!=', 'Completed')
            ->orderBy('due_date');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'Completed')
            ->orderBy('due_date');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeOfType($query, $type)
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('activity_type', $type);
    }

    // Methods
    public function markAsCompleted()
    {
        return $this->update([
            'status' => 'Completed',
            'completed_at' => now(),
        ]);
    }

    public function addReminder($remind_at)
    {
        return $this->reminders()->create([
            'remind_at' => $remind_at,
            'user_id' => $this->owner_id,
        ]);
    }
}