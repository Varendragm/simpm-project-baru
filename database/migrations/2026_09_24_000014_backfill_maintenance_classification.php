<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('maintenance_histories')->get() as $row) {
            $noLaporan = strtolower((string) ($row->no_laporan ?? ''));
            $text = strtolower(trim(($row->pekerjaan ?? '') . ' ' . ($row->hasil ?? '') . ' ' . ($row->catatan ?? '')));

            if (str_starts_with($noLaporan, 'pm-') || str_contains($text, 'preventive')) {
                $type = 'preventive';
            } elseif (str_contains($text, 'breakdown') || str_contains($text, 'kerusakan') || str_contains($text, 'rusak') || str_contains($text, 'gagal') || str_contains($text, 'failure') || str_contains($noLaporan, 'br-')) {
                $type = 'corrective';
            } else {
                $type = 'preventive';
            }

            DB::table('maintenance_histories')
                ->where('id', $row->id)
                ->update([
                    'jenis_maintenance' => $type,
                    'downtime_type' => in_array($type, ['corrective', 'breakdown'], true) ? 'unplanned' : 'planned',
                ]);
        }
    }

    public function down(): void
    {
        DB::table('maintenance_histories')->update([
            'jenis_maintenance' => null,
            'downtime_type' => null,
        ]);
    }
};
