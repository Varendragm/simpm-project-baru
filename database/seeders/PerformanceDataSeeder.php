<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachineProductionRecord;
use Illuminate\Database\Seeder;

class PerformanceDataSeeder extends Seeder
{
    public function run(): void
    {
        // Demo master status is manual and starts as active.
        // Operational condition is calculated by MachineStatusCalculator.
        Machine::query()->update(['status' => 'aktif']);

        // Dummy production data for the last 6 months.
        // The application calculates Availability, Performance, Quality,
        // OEE, MTTR, MTBF and Reliability from these inputs plus maintenance data.
        $months = [
            ['start' => '2026-03-01', 'end' => '2026-03-31', 'days' => 31],
            ['start' => '2026-04-01', 'end' => '2026-04-30', 'days' => 30],
            ['start' => '2026-05-01', 'end' => '2026-05-31', 'days' => 31],
            ['start' => '2026-06-01', 'end' => '2026-06-30', 'days' => 30],
            ['start' => '2026-07-01', 'end' => '2026-07-31', 'days' => 31],
            ['start' => '2026-08-01', 'end' => '2026-08-31', 'days' => 31],
        ];

        $machines = Machine::pluck('id')->all();

        // Actual output is intentionally varied to create realistic demo results.
        // [actual, ideal, good] per month, based on each machine's nominal demo capacity.
        $base = [
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

        $variation = [
            [1.00, 1.00, 1.00],
            [0.98, 1.00, 0.99],
            [1.02, 1.00, 1.01],
            [0.96, 1.00, 0.98],
            [1.01, 1.00, 1.00],
            [1.00, 1.00, 1.00],
        ];

        foreach ($machines as $machineId) {
            // Keep unknown/new machines usable with a generic dummy production row.
            $baseValues = $base[$machineId] ?? [38000, 43200, 36500];

            foreach ($months as $index => $month) {
                $planned = $month['days'] * 24 * 60;
                $ideal = $baseValues[1] / 31 * $month['days'];
                $actual = $baseValues[0] / 31 * $month['days'] * $variation[$index][0];
                $good = $baseValues[2] / 31 * $month['days'] * $variation[$index][2];

                MachineProductionRecord::create([
                    'machine_id' => $machineId,
                    'period_start' => $month['start'],
                    'period_end' => $month['end'],
                    'planned_minutes' => $planned,
                    'actual_output' => round($actual, 2),
                    'ideal_output' => round($ideal, 2),
                    'good_output' => round(min($actual, $good), 2),
                ]);
            }
        }
    }
}
