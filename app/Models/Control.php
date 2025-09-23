<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Control extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'nature',
        'type',
        'frequency',
        'owner_id',
        'status',
    ];

    protected $casts = [
        // Add date casting if needed, e.g. 'next_due' => 'date'
    ];

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(ControlCategory::class, 'category_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function risks()
    {
        return $this->belongsToMany(Risk::class, 'control_risk')
            ->withPivot(['effectiveness_rating', 'residual_impact']);
    }

    public function testPlans()
    {
        return $this->hasMany(ControlTestPlan::class);
    }
}
