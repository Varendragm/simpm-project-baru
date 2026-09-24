<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'station_id', 'code', 'name', 'type', 'status',
        'capacity', 'year', 'notes',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $machine) {
            // Legacy mockup data used condition values in the status column.
            // Keep the master status binary and let the calculator own condition.
            if (in_array(strtolower((string) $machine->status), ['normal', 'perhatian', 'perbaikan'], true)) {
                $machine->status = 'aktif';
            }

            if (!in_array(strtolower((string) $machine->status), ['aktif', 'nonaktif'], true)) {
                $machine->status = 'aktif';
            }
        });
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function performance(): HasOne
    {
        return $this->hasOne(MachinePerformance::class);
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(MachineProductionRecord::class);
    }

    public function pmSchedules(): HasMany
    {
        return $this->hasMany(PmSchedule::class);
    }

    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    public function toBootstrapArray(): array
    {
        return [
            'id' => $this->id,
            'stationId' => $this->station_id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'capacity' => $this->capacity,
            'year' => $this->year,
            'notes' => $this->notes,
        ];
    }
}
