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

    /**
     * Get deals in this pipeline stage.
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'pipeline_stage', 'name');
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Get open pipeline stages (not won or lost).
     */
    public function scopeOpen($query)
    {
        return $query->where('is_won', false)->where('is_lost', false);
    }

    /**
     * Get closing pipeline stages (won or lost).
     */
    public function scopeClosing($query)
    {
        return $query->where(function($q) {
            $q->where('is_won', true)->orWhere('is_lost', true);
        });
    }

    /**
     * Get winning pipeline stages.
     */
    public function scopeWinning($query)
    {
        return $query->where('is_won', true);
    }

    /**
     * Get losing pipeline stages.
     */
    public function scopeLosing($query)
    {
        return $query->where('is_lost', true);
    }
}