<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'currency',
        'pipeline_stage',
        'probability',
        'expected_close_date',
        'actual_close_date',
        'description',
        'company_id',
        'primary_contact_id',
        'lead_id',
        'owner_id',
        'created_by',
        'won',
        'lost_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'won' => 'boolean',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function primaryContact()
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'deal_contact')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'deal_product')
            ->withPivot(['quantity', 'unit_price', 'discount_amount', 'discount_percent', 'total_price'])
            ->withTimestamps();
    }

    public function pipelineStage()
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage', 'name');
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
    public function scopeInStage($query, $stage)
    {
        return $query->where('pipeline_stage', $stage);
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeOfCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeOfContact($query, $contactId)
    {
        return $query->whereHas('contacts', function ($q) use ($contactId) {
            $q->where('contacts.id', $contactId);
        })->orWhere('primary_contact_id', $contactId);
    }

    public function scopeWon($query)
    {
        return $query->where('won', true);
    }

    public function scopeLost($query)
    {
        return $query->where('won', false)->whereNotNull('lost_reason');
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('won');
    }

    public function scopeClosingBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('expected_close_date', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where('name', 'like', "%{$search}%");
    }

    // Methods
    public function markAsWon()
    {
        return $this->update([
            'won' => true,
            'pipeline_stage' => 'Closed Won',
            'actual_close_date' => now(),
            'lost_reason' => null,
        ]);
    }

    public function markAsLost($reason)
    {
        return $this->update([
            'won' => false,
            'pipeline_stage' => 'Closed Lost',
            'actual_close_date' => now(),
            'lost_reason' => $reason,
        ]);
    }

    public function moveToStage($stageName)
    {
        $stage = PipelineStage::where('name', $stageName)->first();
        
        if (!$stage) {
            return false;
        }

        $data = [
            'pipeline_stage' => $stageName,
            'probability' => $stage->probability,
        ];

        // If moving to a closing stage, update won/lost status
        if ($stage->is_won) {
            $data['won'] = true;
            $data['actual_close_date'] = now();
            $data['lost_reason'] = null;
        } else if ($stage->is_lost) {
            $data['won'] = false;
            $data['actual_close_date'] = now();
        } else {
            $data['won'] = null;
            $data['actual_close_date'] = null;
            $data['lost_reason'] = null;
        }

        return $this->update($data);
    }

    public function calculateTotalValue()
    {
        $productTotal = $this->products->sum(function ($product) {
            return $product->pivot->total_price;
        });

        // If products exist, use their total, otherwise use deal amount
        return $productTotal > 0 ? $productTotal : $this->amount;
    }

    // Accessor for total value - makes it available as $deal->total_value
    public function getTotalValueAttribute()
    {
        return $this->calculateTotalValue();
    }

    // Accessor for stage color
    public function getStageColorAttribute()
    {
        switch ($this->pipeline_stage) {
            case 'Qualification':
                return 'blue';
            case 'Needs Analysis':
                return 'indigo';
            case 'Proposal':
                return 'purple';
            case 'Negotiation':
                return 'yellow';
            case 'Closed Won':
                return 'green';
            case 'Closed Lost':
                return 'red';
            default:
                return 'gray';
        }
    }

    // In App\Models\Deal.php
public function convertedFromLead()
{
    return $this->belongsTo(Lead::class, 'lead_id');
}
}