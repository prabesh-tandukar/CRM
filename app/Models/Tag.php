<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
    ];

    // Relationships
    public function contacts()
    {
        return $this->morphedByMany(Contact::class, 'taggable');
    }

    public function companies()
    {
        return $this->morphedByMany(Company::class, 'taggable');
    }

    public function leads()
    {
        return $this->morphedByMany(Lead::class, 'taggable');
    }

    public function deals()
    {
        return $this->morphedByMany(Deal::class, 'taggable');
    }

    // Scopes
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where('name', 'like', "%{$search}%");
    }
}