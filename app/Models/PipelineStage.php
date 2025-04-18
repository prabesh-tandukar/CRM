<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'probability',
        'display_order',
        'is_won',
        'is_lost',
    ];

    protected $casts = [
        'probability' => 'integer',
        'display_order' => 'integer',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];

    // Relationships
    public function deals()
    {
        return $this->hasMany(Deal::class, 'pipeline_stage', 'name');
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public function scopeWinStage($query)
    {
        return $query->where('is_won', true);
    }

    public function scopeLossStage($query)
    {
        return $query->where('is_lost', true);
    }

    public function scopeOpenStages($query)
    {
        return $query->where('is_won', false)->where('is_lost', false);
    }
}