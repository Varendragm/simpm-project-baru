<?php

namespace Database\Seeders;

use App\Models\MachineProductionRecord;
use Illuminate\Database\Seeder;

class PerformanceDataSeeder extends Seeder
{
    public function run(): void
    {
        $periodStart = '2026-08-01';
        $periodEnd = '2026-08-31';
        $planned = 43200; // 30 days x 24 hours, in minutes

        $records = [
            'm-g01' => [40320, 43200, 38000],
            'm-g02' => [35000, 43200, 33000],
            'm-g03' => [41400, 43200, 40500],
            'm-g04' => [36000, 43200, 34200],
            'm-b01' => [39600, 43200, 37800],
            'm-b02' => [38800, 43200, 37000],
            'm-b03' => [36500, 43200, 34500],
            'm-p01' => [41000, 43200, 39500],
            'm-p02' => [41500, 43200, 40200],
        ];

        foreach ($records as $machineId => [$actual, $ideal, $good]) {
            MachineProductionRecord::create([
                'machine_id' => $machineId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'planned_minutes' => $planned,
                'actual_output' => $actual,
                'ideal_output' => $ideal,
                'good_output' => $good,
            ]);
        }
    }
}
