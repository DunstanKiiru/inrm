<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_id',
        'test_execution_id',
        'description',
        'severity',
        'status',
        'owner_id',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function control()
    {
        return $this->belongsTo(Control::class);
    }

    public function execution()
    {
        return $this->belongsTo(ControlTestExecution::class, 'test_execution_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function remediations()
    {
        return $this->hasMany(ControlRemediation::class, 'issue_id');
    }
}
