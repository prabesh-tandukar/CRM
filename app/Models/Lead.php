<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'website',
        'lead_source',
        'lead_status',
        'industry',
        'rating',
        'estimated_budget',
        'description',
        'address',
        'owner_id',
        'created_by',
        'converted_at',
        'converted_to_contact_id',
        'converted_to_deal_id',
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
        'converted_at' => 'datetime',
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
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedContact()
    {
        return $this->belongsTo(Contact::class, 'converted_to_contact_id');
    }

    public function convertedDeal()
    {
        return $this->belongsTo(Deal::class, 'converted_to_deal_id');
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

    // Scopes
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeStatus($query, $status)
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('lead_status', $status);
    }

    public function scopeNotConverted($query)
    {
        return $query->whereNull('converted_at');
    }

    public function scopeConverted($query)
    {
        return $query->whereNotNull('converted_at');
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
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    // Methods
    public function convert($contactId, $dealId = null)
    {
        return $this->update([
            'converted_at' => now(),
            'converted_to_contact_id' => $contactId,
            'converted_to_deal_id' => $dealId,
        ]);
    }
}