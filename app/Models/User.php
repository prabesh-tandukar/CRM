<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'job_title',
        'department',
        'avatar',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    // Relationships
    public function contacts()
    {
        return $this->hasMany(Contact::class, 'owner_id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'owner_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'owner_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->hasRole('Admin');
    }

    public function isManager()
    {
        return $this->hasRole('Manager');
    }

    public function updateLastLogin()
    {
        return $this->update([
            'last_login_at' => now(),
        ]);
    }
}