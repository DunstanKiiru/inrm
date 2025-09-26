<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TprRuleSuppression extends Model
{
    protected $fillable = ['rule_id', 'vendor_id', 'until', 'reason'];

    public function rule()
    {
        return $this->belongsTo(TprRule::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
