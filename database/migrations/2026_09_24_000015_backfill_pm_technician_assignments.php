<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $technicians = DB::table('users')->where('role', 'teknisi')->orderBy('id')->get(['id', 'name']);
        if ($technicians->isEmpty()) {
            return;
        }

        foreach (DB::table('pm_schedules')->get(['id', 'teknisi', 'teknisi_user_id']) as $schedule) {
            $technician = $technicians->firstWhere('id', $schedule->teknisi_user_id);

            if (!$technician && $schedule->teknisi) {
                $technician = $technicians->firstWhere('name', $schedule->teknisi);
            }

            if (!$technician && $technicians->count() === 1) {
                $technician = $technicians->first();
            }

            if ($technician) {
                DB::table('pm_schedules')->where('id', $schedule->id)->update([
                    'teknisi_user_id' => $technician->id,
                    'teknisi' => $technician->name,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Assignment backfill is intentionally not reversed because it repairs
        // legacy/null relationships and does not change the schedule itself.
    }
};
