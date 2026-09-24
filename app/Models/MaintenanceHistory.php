<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceHistory extends Model
{
    protected $table = 'maintenance_histories';

    protected $fillable = [
        'no_laporan', 'machine_id', 'kategori', 'pekerjaan', 'pelaksana',
        'downtime_menit', 'hasil', 'catatan', 'tanggal', 'jenis_maintenance',
        'downtime_type',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date:Y-m-d'];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function toBootstrapArray(): array
    {
        return [
            'noLaporan' => $this->no_laporan,
            'machineId' => $this->machine_id,
            'kategori' => $this->kategori,
            'pekerjaan' => $this->pekerjaan,
            'pelaksana' => $this->pelaksana,
            'downtimeMenit' => (int) $this->downtime_menit,
            'hasil' => $this->hasil,
            'catatan' => $this->catatan,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'jenisMaintenance' => $this->jenis_maintenance,
            'downtimeType' => $this->downtime_type,
        ];
    }
}
