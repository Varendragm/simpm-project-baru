<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MachineProductionRecord;
use App\Models\MaintenanceHistory;
use Carbon\Carbon;

class PerformanceCalculator
{
    /**
     * Calculate all performance indicators from dummy/operational source data.
     * No performance result is hardcoded here.
     */
    public function calculate(Machine $machine, ?Carbon $start = null, ?Carbon $end = null): array
    {
        [$start, $end] = $this->resolvePeriod($machine, $start, $end);

        $maintenance = MaintenanceHistory::query()
            ->where('machine_id', $machine->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get();

        $production = MachineProductionRecord::query()
            ->where('machine_id', $machine->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('period_start', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('period_end', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('period_start', '<=', $start->toDateString())
                            ->where('period_end', '>=', $end->toDateString());
                    });
            })
            ->get();

        // ================= SOURCE DATA =================
        $downtimeMinutes = (float) $maintenance->sum('downtime_menit');
        $failureCount = $maintenance
            ->filter(fn ($row) => $this->isFailure($row->kategori, $row->hasil))
            ->count();

        $plannedMinutes = (float) $production->sum('planned_minutes');
        $actualOutput = (float) $production->sum('actual_output');
        $idealOutput = (float) $production->sum('ideal_output');
        $goodOutput = (float) $production->sum('good_output');
        $operatingMinutes = max(0, $plannedMinutes - $downtimeMinutes);

        // ================= OEE =================
        // Availability = (Planned Time - Downtime) / Planned Time x 100
        $availability = $plannedMinutes > 0
            ? $this->percent($operatingMinutes, $plannedMinutes)
            : null;

        // Performance = Actual Output / Ideal Output x 100
        $performance = $idealOutput > 0
            ? $this->percent($actualOutput, $idealOutput)
            : null;

        // Quality = Good Output / Actual Output x 100
        $quality = $actualOutput > 0
            ? $this->percent($goodOutput, $actualOutput)
            : null;

        // OEE = Availability x Performance x Quality
        $oee = $availability !== null && $performance !== null && $quality !== null
            ? round(($availability / 100) * ($performance / 100) * ($quality / 100) * 100, 2)
            : null;

        // ================= RELIABILITY =================
        // MTTR = Total Corrective Repair Time / Number of Failures.
        // Dalam data SIMPM saat ini downtime menjadi dummy repair time.
        $mttr = $failureCount > 0
            ? round($downtimeMinutes / $failureCount / 60, 2)
            : 0.0;

        // MTBF = Operating Time / Number of Failures.
        $mtbf = $failureCount > 0
            ? round($operatingMinutes / $failureCount / 60, 2)
            : 0.0;

        // Reliability 1-hour = e^(-1 / MTBF) x 100%.
        $reliability = $mtbf > 0
            ? round(exp(-1 / $mtbf) * 100, 2)
            : ($failureCount === 0 ? 100.0 : 0.0);

        return [
            'oee' => $oee,
            'availability' => $availability,
            'performance' => $performance,
            'quality' => $quality,
            'reliability' => $reliability,
            'mttr' => $mttr,
            'mtbf' => $mtbf,
            'downtimeBulanIni' => round($downtimeMinutes / 60, 2),
            'perbaikanBulanIni' => $failureCount,
            'failureCount' => $failureCount,
            'plannedMinutes' => $plannedMinutes,
            'operatingMinutes' => $operatingMinutes,
            'downtimeMinutes' => $downtimeMinutes,
            'actualOutput' => $actualOutput,
            'idealOutput' => $idealOutput,
            'goodOutput' => $goodOutput,
            'trendOee' => $this->monthlyTrend($machine, $end),
            'periodStart' => $start->toDateString(),
            'periodEnd' => $end->toDateString(),
            'hasProductionData' => $production->isNotEmpty(),
        ];
    }

    private function resolvePeriod(Machine $machine, ?Carbon $start, ?Carbon $end): array
    {
        if ($start === null && $end === null) {
            $latest = MachineProductionRecord::where('machine_id', $machine->id)
                ->orderByDesc('period_end')
                ->first();

            if ($latest) {
                $start = Carbon::parse($latest->period_start)->startOfMonth();
                $end = Carbon::parse($latest->period_end)->endOfMonth();
            } else {
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
            }
        } else {
            $start ??= $end->copy()->startOfMonth();
            $end ??= $start->copy()->endOfMonth();
        }

        return [$start, $end];
    }

    private function monthlyTrend(Machine $machine, Carbon $end): array
    {
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $end->copy()->subMonths($i);
            $values[] = $this->calculateWithoutTrend(
                $machine,
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth()
            );
        }

        return $values;
    }

    private function calculateWithoutTrend(Machine $machine, Carbon $start, Carbon $end): ?float
    {
        $maintenance = MaintenanceHistory::query()
            ->where('machine_id', $machine->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get();

        $production = MachineProductionRecord::query()
            ->where('machine_id', $machine->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('period_start', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('period_end', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('period_start', '<=', $start->toDateString())
                            ->where('period_end', '>=', $end->toDateString());
                    });
            })
            ->get();

        $planned = (float) $production->sum('planned_minutes');
        $actual = (float) $production->sum('actual_output');
        $ideal = (float) $production->sum('ideal_output');
        $good = (float) $production->sum('good_output');
        $downtime = (float) $maintenance->sum('downtime_menit');

        if ($planned <= 0 || $ideal <= 0 || $actual <= 0) {
            return null;
        }

        $availability = $this->percent(max(0, $planned - $downtime), $planned);
        $performance = $this->percent($actual, $ideal);
        $quality = $this->percent($good, $actual);

        return round(
            ($availability / 100) * ($performance / 100) * ($quality / 100) * 100,
            2
        );
    }

    private function percent(float $numerator, float $denominator): float
    {
        return round(min(100, max(0, ($numerator / $denominator) * 100)), 2);
    }

    private function isFailure(?string $category, ?string $result): bool
    {
        $text = strtolower(trim(($category ?? '') . ' ' . ($result ?? '')));

        return str_contains($text, 'kerusakan')
            || str_contains($text, 'rusak')
            || str_contains($text, 'gagal')
            || str_contains($text, 'failure');
    }
}
