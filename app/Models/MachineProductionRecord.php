<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::saving(function (self $record) {
            if (!$record->period_start || !$record->period_end) {
                return;
            }

            if ($record->period_start->gt($record->period_end)) {
                throw ValidationException::withMessages([
                    'period_end' => 'Periode produksi berakhir sebelum tanggal mulai.',
                ]);
            }

            $query = static::query()
                ->where('machine_id', $record->machine_id)
                ->whereDate('period_start', '<=', $record->period_end->toDateString())
                ->whereDate('period_end', '>=', $record->period_start->toDateString());

            if ($record->exists) {
                $query->where($record->getKeyName(), '!=', $record->getKey());
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'period_start' => 'Periode produksi mesin bertabrakan dengan record produksi yang sudah ada.',
                ]);
            }

            if ($record->actual_output < 0 || $record->ideal_output < 0 || $record->good_output < 0 || $record->planned_minutes < 0) {
                throw ValidationException::withMessages([
                    'production' => 'Nilai produksi dan waktu terencana tidak boleh negatif.',
                ]);
            }

            if ($record->good_output > $record->actual_output) {
                throw ValidationException::withMessages([
                    'good_output' => 'Good output tidak boleh lebih besar dari actual output.',
                ]);
            }
        });
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
