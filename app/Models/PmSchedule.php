<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmSchedule extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'machine_id', 'jenis', 'teknisi', 'tanggal', 'interval',
        'estimasi', 'prioritas', 'status', 'catatan', 'report',
    ];

    protected function casts(): array
    {
        return [
            'report' => 'array',
            'tanggal' => 'date:Y-m-d',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function toBootstrapArray(): array
    {
        $data = [
            'id' => $this->id,
            'machineId' => $this->machine_id,
            'jenis' => $this->jenis,
            'teknisi' => $this->teknisi,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'interval' => $this->interval,
            'estimasi' => $this->estimasi,
            'prioritas' => $this->prioritas,
            'status' => $this->status,
            'catatan' => $this->catatan,
        ];
        if ($this->report) {
            $data['report'] = $this->report;
        }
        return $data;
    }
}
