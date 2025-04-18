<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'department',
        'company_id',
        'owner_id',
        'lead_source',
        'lead_status',
        'mailing_address',
        'alternate_address',
        'is_primary_contact',
        'description',
        'linkedin_url',
        'twitter_handle',
        'date_of_birth',
        'created_by',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'date_of_birth' => 'date',
    ];

    protected $appends = [
        'full_name',
    ];

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function primaryDeals()
    {
        return $this->hasMany(Deal::class, 'primary_contact_id');
    }

    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_contact')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activityable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function convertedFromLead()
    {
        return $this->hasOne(Lead::class, 'converted_to_contact_id');
    }

    // Scopes
    public function scopeOfCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Methods
    public function setAsPrimaryContact()
    {
        if (!$this->company_id) {
            return false;
        }

        // Reset any existing primary contacts for this company
        Contact::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->where('is_primary_contact', true)
            ->update(['is_primary_contact' => false]);

        return $this->update(['is_primary_contact' => true]);
    }
}