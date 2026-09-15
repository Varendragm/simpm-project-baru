<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MachineProductionRecord;
use App\Models\MaintenanceHistory;
use Carbon\Carbon;

class PerformanceCalculator
{
    public function calculate(Machine $machine, ?Carbon $start = null, ?Carbon $end = null): array
    {
        // Use the latest available production period by default so demo/seed data is
        // calculated even when the machine data is older than today's month.
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

        $downtime = (float) $maintenance->sum('downtime_menit');
        $failures = $maintenance->filter(fn ($row) => $this->isFailure($row->kategori, $row->hasil))->count();
        $repairCount = $maintenance->count();
        $repairMinutes = $downtime;

        $plannedMinutes = (float) $production->sum('planned_minutes');
        $actualOutput = (float) $production->sum('actual_output');
        $idealOutput = (float) $production->sum('ideal_output');
        $goodOutput = (float) $production->sum('good_output');

        $availability = $plannedMinutes > 0
            ? $this->percent(max(0, $plannedMinutes - $downtime), $plannedMinutes)
            : null;
        $performance = $idealOutput > 0 ? $this->percent($actualOutput, $idealOutput) : null;
        $quality = $actualOutput > 0 ? $this->percent($goodOutput, $actualOutput) : null;
        $oee = $availability !== null && $performance !== null && $quality !== null
            ? round(($availability / 100) * ($performance / 100) * ($quality / 100) * 100, 2)
            : null;

        $mttr = $repairCount > 0 ? round($repairMinutes / $repairCount / 60, 2) : 0.0;
        $operatingMinutes = max(0, $plannedMinutes - $downtime);
        $mtbf = $failures > 0 ? round($operatingMinutes / $failures / 60, 2) : 0.0;
        $reliability = $mtbf > 0 ? round(exp(-1 / $mtbf) * 100, 2) : ($failures === 0 ? 100.0 : 0.0);

        return [
            'oee' => $oee,
            'availability' => $availability,
            'performance' => $performance,
            'quality' => $quality,
            'reliability' => $reliability,
            'mttr' => $mttr,
            'mtbf' => $mtbf,
            'downtimeBulanIni' => round($downtime / 60, 2),
            'perbaikanBulanIni' => $repairCount,
            'trendOee' => $this->monthlyTrend($machine, $end),
            'periodStart' => $start->toDateString(),
            'periodEnd' => $end->toDateString(),
            'hasProductionData' => $production->isNotEmpty(),
        ];
    }

    private function monthlyTrend(Machine $machine, Carbon $end): array
    {
        $values = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $end->copy()->subMonths($i);
            $values[] = $this->calculateWithoutTrend($machine, $month->copy()->startOfMonth(), $month->copy()->endOfMonth());
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
                    ->orWhereBetween('period_end', [$start->toDateString(), $end->toDateString()]);
            })->get();
        $planned = (float) $production->sum('planned_minutes');
        $actual = (float) $production->sum('actual_output');
        $ideal = (float) $production->sum('ideal_output');
        $good = (float) $production->sum('good_output');
        if ($planned <= 0 || $ideal <= 0 || $actual <= 0) return null;
        $availability = $this->percent(max(0, $planned - (float) $maintenance->sum('downtime_menit')), $planned);
        $performance = $this->percent($actual, $ideal);
        $quality = $this->percent($good, $actual);
        return round(($availability / 100) * ($performance / 100) * ($quality / 100) * 100, 2);
    }

    private function percent(float $numerator, float $denominator): float
    {
        return round(min(100, max(0, ($numerator / $denominator) * 100)), 2);
    }

    private function isFailure(?string $category, ?string $result): bool
    {
        $text = strtolower(trim(($category ?? '') . ' ' . ($result ?? '')));
        return str_contains($text, 'kerusakan') || str_contains($text, 'rusak') || str_contains($text, 'gagal') || str_contains($text, 'failure');
    }
}
