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

    protected static function booted(): void
    {
        static::saving(function (self $history) {
            if (!$history->jenis_maintenance) {
                $noLaporan = strtolower((string) $history->no_laporan);
                $text = strtolower(trim(($history->pekerjaan ?? '') . ' ' . ($history->hasil ?? '') . ' ' . ($history->catatan ?? '')));

                if (str_starts_with($noLaporan, 'pm-') || str_contains($text, 'preventive')) {
                    $history->jenis_maintenance = 'preventive';
                } elseif (str_contains($text, 'breakdown') || str_contains($text, 'kerusakan') || str_contains($text, 'rusak') || str_contains($text, 'gagal') || str_contains($text, 'failure') || str_contains($noLaporan, 'br-')) {
                    $history->jenis_maintenance = 'corrective';
                } else {
                    $history->jenis_maintenance = 'preventive';
                }
            }

            if (!$history->downtime_type) {
                $history->downtime_type = in_array($history->jenis_maintenance, ['corrective', 'breakdown'], true)
                    ? 'unplanned'
                    : 'planned';
            }
        });
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
