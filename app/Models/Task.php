<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'completed_at',
        'taskable_id',
        'taskable_type',
        'assignee_id',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function taskable()
    {
        return $this->morphTo();
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
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

    public function scopeNotCompleted($query)
    {
        return $query->where('status', '!=', 'Completed');
    }

    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assignee_id', $userId);
    }

    public function scopeByPriority($query, $priority = null)
    {
        if ($priority) {
            return $query->where('priority', $priority);
        }
        
        return $query->orderByRaw("FIELD(priority, 'High', 'Medium', 'Low')");
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
            'user_id' => $this->assignee_id,
        ]);
    }
}