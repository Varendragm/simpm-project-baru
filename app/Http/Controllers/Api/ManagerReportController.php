<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Services\PerformanceCalculator;
use Illuminate\Http\Request;

class ManagerReportController extends Controller
{
    public function __invoke(Request $request, PerformanceCalculator $calculator)
    {
        return response()->json($this->buildReport($request, $calculator));
    }

    public function print(Request $request, PerformanceCalculator $calculator)
    {
        return view('manager.report-print', $this->buildReport($request, $calculator));
    }

    private function buildReport(Request $request, PerformanceCalculator $calculator): array
    {
        $period = $request->string('period')->toString() ?: 'month';
        if (!in_array($period, ['month', 'quarter', 'year'], true)) {
            $period = 'month';
        }

        $reference = now();
        $start = match ($period) {
            'year' => $reference->copy()->startOfYear(),
            'quarter' => $reference->copy()->firstOfQuarter(),
            default => $reference->copy()->startOfMonth(),
        };
        $end = match ($period) {
            'year' => $reference->copy()->endOfYear(),
            'quarter' => $reference->copy()->lastOfQuarter(),
            default => $reference->copy()->endOfMonth(),
        };

        $query = Machine::with('station')->orderBy('code');

        if ($request->filled('station_id') && $request->string('station_id')->toString() !== 'all') {
            $query->where('station_id', $request->string('station_id')->toString());
        }

        if ($request->filled('machine_id') && $request->string('machine_id')->toString() !== 'all') {
            $query->whereKey($request->string('machine_id')->toString());
        }

        $rows = $query->get()->map(function (Machine $machine) use ($calculator, $start, $end) {
            $performance = $calculator->calculate($machine, $start->copy(), $end->copy());
            $hasProductionData = (bool) ($performance['hasProductionData'] ?? false);

            return [
                'machineId' => $machine->id,
                'machine' => $machine->name,
                'station' => $machine->station?->name ?? '-',
                'oee' => $performance['oee'],
                'availability' => $performance['availability'],
                'performance' => $performance['performance'],
                'quality' => $performance['quality'],
                'reliability' => $performance['reliability'],
                'mttr' => $performance['mttr'],
                'mtbf' => $performance['mtbf'],
                'downtime' => $performance['downtimeBulanIni'],
                'kondisi' => $hasProductionData ? $machine->kondisi : 'Belum ada data',
                'hasProductionData' => $hasProductionData,
                'hasFailureData' => (bool) ($performance['hasFailureData'] ?? false),
                'periodStart' => $start->toDateString(),
                'periodEnd' => $end->toDateString(),
            ];
        })->values();

        return [
            'period' => $period,
            'periodStart' => $start->toDateString(),
            'periodEnd' => $end->toDateString(),
            'hasProductionData' => $rows->contains(fn ($row) => $row['hasProductionData']),
            'rows' => $rows->all(),
        ];
    }
}
