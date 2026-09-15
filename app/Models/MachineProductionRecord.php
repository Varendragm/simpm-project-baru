<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineProductionRecord extends Model
{
    protected $fillable = [
        'machine_id', 'period_start', 'period_end', 'planned_minutes',
        'actual_output', 'ideal_output', 'good_output',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date:Y-m-d',
            'period_end' => 'date:Y-m-d',
            'planned_minutes' => 'float',
            'actual_output' => 'float',
            'ideal_output' => 'float',
            'good_output' => 'float',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
