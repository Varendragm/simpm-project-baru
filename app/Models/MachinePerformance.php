<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachinePerformance extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'machine_id';

    protected $fillable = [
        'machine_id', 'oee', 'availability', 'reliability',
        'mttr', 'mtbf', 'downtime_bulan_ini', 'perbaikan_bulan_ini', 'trend_oee',
    ];

    protected function casts(): array
    {
        return [
            'trend_oee' => 'array',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function toBootstrapArray(): array
    {
        return [
            'oee' => (int) $this->oee,
            'availability' => (int) $this->availability,
            'reliability' => (int) $this->reliability,
            'mttr' => (float) $this->mttr,
            'mtbf' => (int) $this->mtbf,
            'downtimeBulanIni' => (float) $this->downtime_bulan_ini,
            'perbaikanBulanIni' => (int) $this->perbaikan_bulan_ini,
            'trendOee' => $this->trend_oee ?? [0, 0, 0, 0, 0, 0],
        ];
    }
}
