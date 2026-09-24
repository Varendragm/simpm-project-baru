<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MachineProductionRecord;
use App\Models\MaintenanceHistory;
use Carbon\Carbon;

class PerformanceCalculator
{
    /**
     * Calculate OEE and reliability indicators from operational source data.
     * Performance values are never hardcoded here.
     */
    public function calculate(Machine $machine, ?Carbon $start = null, ?Carbon $end = null): array
    {
        [$start, $end] = $this->resolvePeriod($machine, $start, $end);

        $maintenance = MaintenanceHistory::query()
            ->where('machine_id', $machine->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get();

        $production = $this->productionForPeriod($machine, $start, $end);

        // Only unplanned downtime reduces OEE Availability. Preventive/planned
        // maintenance remains visible in maintenance history but is not treated
        // as an unexpected availability loss.
        $unplannedMaintenance = $maintenance->filter(fn ($row) => $this->isUnplanned($row));
        $failureMaintenance = $maintenance->filter(fn ($row) => $this->isFailure($row));

        $downtimeMinutes = (float) $unplannedMaintenance->sum('downtime_menit');
        $failureCount = $failureMaintenance->count();
        $repairMinutes = (float) $failureMaintenance->sum('downtime_menit');

        $plannedMinutes = (float) $production->sum('planned_minutes');
        $actualOutput = (float) $production->sum('actual_output');
        $idealOutput = (float) $production->sum('ideal_output');
        $goodOutput = (float) $production->sum('good_output');
        $operatingMinutes = max(0, $plannedMinutes - $downtimeMinutes);

        $availability = $plannedMinutes > 0
            ? $this->percent($operatingMinutes, $plannedMinutes)
            : null;

        $performance = $idealOutput > 0
            ? $this->percent($actualOutput, $idealOutput)
            : null;

        $quality = $actualOutput > 0
            ? $this->percent($goodOutput, $actualOutput)
            : null;

        $oee = $availability !== null && $performance !== null && $quality !== null
            ? round(($availability / 100) * ($performance / 100) * ($quality / 100) * 100, 2)
            : null;

        // MTTR = total corrective/breakdown repair time / number of failures.
        $mttr = $failureCount > 0
            ? round($repairMinutes / $failureCount / 60, 2)
            : null;

        // MTBF = operating time / number of failures.
        $mtbf = $failureCount > 0
            ? round($operatingMinutes / $failureCount / 60, 2)
            : null;

        // Reliability at one hour. With no observed failure in the period,
        // reliability is reported as 100% rather than inventing an MTBF of 0.
        $reliability = $mtbf !== null && $mtbf > 0
            ? round(exp(-1 / $mtbf) * 100, 2)
            : ($failureCount === 0 ? 100.0 : null);

        return [
            'oee' => $oee,
            'availability' => $availability,
            'performance' => $performance,
            'quality' => $quality,
            'reliability' => $reliability,
            'reliabilityPeriodHours' => 1,
            'mttr' => $mttr,
            'mtbf' => $mtbf,
            'downtimeBulanIni' => round($downtimeMinutes / 60, 2),
            'perbaikanBulanIni' => $failureCount,
            'failureCount' => $failureCount,
            'plannedMinutes' => $plannedMinutes,
            'operatingMinutes' => $operatingMinutes,
            'downtimeMinutes' => $downtimeMinutes,
            'repairMinutes' => $repairMinutes,
            'actualOutput' => $actualOutput,
            'idealOutput' => $idealOutput,
            'goodOutput' => $goodOutput,
            'hasProductionData' => $production->isNotEmpty(),
            'hasFailureData' => $failureCount > 0,
            'trendOee' => $this->monthlyTrend($machine, $end),
            'periodStart' => $start->toDateString(),
            'periodEnd' => $end->toDateString(),
        ];
    }

    private function productionForPeriod(Machine $machine, Carbon $start, Carbon $end)
    {
        return MachineProductionRecord::query()
            ->where('machine_id', $machine->id)
            // A production record must overlap the requested period. Overlap
            // validation is enforced when records are created; this query only
            // reads the records that belong to the selected reporting period.
            ->whereDate('period_start', '<=', $end->toDateString())
            ->whereDate('period_end', '>=', $start->toDateString())
            ->orderBy('period_start')
            ->get();
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

        $production = $this->productionForPeriod($machine, $start, $end);

        $planned = (float) $production->sum('planned_minutes');
        $actual = (float) $production->sum('actual_output');
        $ideal = (float) $production->sum('ideal_output');
        $good = (float) $production->sum('good_output');
        $downtime = (float) $maintenance->filter(fn ($row) => $this->isUnplanned($row))->sum('downtime_menit');

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

    private function maintenanceType($row): string
    {
        $type = strtolower(trim((string) ($row->jenis_maintenance ?? '')));
        if (in_array($type, ['preventive', 'corrective', 'breakdown'], true)) {
            return $type;
        }

        // Backward-compatible fallback for existing demo records.
        $noLaporan = strtolower((string) ($row->no_laporan ?? ''));
        $text = strtolower(trim(($row->pekerjaan ?? '') . ' ' . ($row->hasil ?? '') . ' ' . ($row->catatan ?? '')));

        if (str_starts_with($noLaporan, 'pm-') || str_contains($text, 'preventive')) {
            return 'preventive';
        }

        if (str_contains($text, 'breakdown')) {
            return 'breakdown';
        }

        if (str_contains($text, 'kerusakan') || str_contains($text, 'rusak') || str_contains($text, 'gagal') || str_contains($text, 'failure') || str_contains($noLaporan, 'br-')) {
            return 'corrective';
        }

        return 'preventive';
    }

    private function isFailure($row): bool
    {
        return in_array($this->maintenanceType($row), ['corrective', 'breakdown'], true);
    }

    private function isUnplanned($row): bool
    {
        return in_array($this->maintenanceType($row), ['corrective', 'breakdown'], true);
    }
}
