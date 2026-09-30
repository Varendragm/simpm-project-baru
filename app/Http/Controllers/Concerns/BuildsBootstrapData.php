<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Machine;
use App\Models\MaintenanceHistory;
use App\Models\PmSchedule;
use App\Models\SippmHistory;
use App\Models\Station;
use App\Models\User;
use App\Models\ValidationHistory;
use App\Services\MachineStatusCalculator;
use App\Services\PerformanceCalculator;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait BuildsBootstrapData
{
    protected function buildBootstrapData(): array
    {
        $stations = Station::orderBy('name')->get()->map->toBootstrapArray()->values();

        $calculator = app(PerformanceCalculator::class);
        $statusCalculator = app(MachineStatusCalculator::class);

        $machines = collect();
        $machinePerformance = [];

        foreach (Machine::with('productionRecords')->orderBy('code')->get() as $machine) {
            $performance = $calculator->calculate($machine);
            $calculatedCondition = $statusCalculator->determine($performance);

            $machineData = $machine->toBootstrapArray();
            // status = master state (aktif/nonaktif); kondisi = calculated condition.
            $machineData['kondisi'] = $calculatedCondition;
            $machineData['statusReason'] = $statusCalculator->reason($performance);

            $machines->push($machineData);
            $machinePerformance[$machine->id] = $performance;
        }

        $pmSchedules = PmSchedule::with('teknisiUser')->orderBy('tanggal')->get()->map->toBootstrapArray()->values();
        $validationHistory = ValidationHistory::orderByDesc('tanggal')->get()->map->toBootstrapArray()->values();
        $maintenanceHistory = MaintenanceHistory::orderByDesc('tanggal')->get()->map->toBootstrapArray()->values();

        // SIPPM is the source of truth for finalized corrective/breakdown history.
        // Read only from the separate database connection; never write back to SIPPM.
        $machineMap = $machines->keyBy(function (array $machine) {
            return Str::lower(trim((string) ($machine['name'] ?? '')));
        });

        $sippmHistories = SippmHistory::finalHistory()
            ->orderByDesc('final_validated_at')
            ->get();

        $technicianIds = $sippmHistories->pluck('technician_id')->filter()->unique()->values();
        $sippmTechnicians = $technicianIds->isEmpty()
            ? collect()
            : SippmHistory::on('sippm')
                ->newQuery()
                ->from('users')
                ->whereIn('id', $technicianIds)
                ->pluck('name', 'id');

        $sippmMaintenanceHistory = $sippmHistories->map(function ($report) use ($machineMap, $sippmTechnicians) {
            $machineName = trim((string) $report->machine);
            $machine = $machineMap->get(Str::lower($machineName));

            $downtime = 0;
            if ($report->incident_date && $report->incident_time && $report->work_end_time) {
                try {
                    $start = Carbon::parse($report->incident_date . ' ' . $report->incident_time);
                    $end = Carbon::parse($report->incident_date . ' ' . $report->work_end_time);
                    if ($end->lessThan($start)) {
                        $end->addDay();
                    }
                    $downtime = max(0, $start->diffInMinutes($end));
                } catch (\Throwable) {
                    $downtime = 0;
                }
            }

            return [
                'noLaporan' => $report->kode,
                'machineId' => $machine['id'] ?? null,
                'machineName' => $machineName,
                'kategori' => Str::lower((string) $report->category),
                'pekerjaan' => $report->action_taken ?: $report->description,
                'pelaksana' => $sippmTechnicians->get($report->technician_id, 'Teknisi SIPPM'),
                'downtimeMenit' => $downtime,
                'hasil' => $report->inspection_result ?: $report->condition_text,
                'catatan' => trim(implode(' | ', array_filter([
                    $report->root_cause,
                    $report->components_text,
                    $report->additional_note,
                ]))),
                'tanggal' => $report->incident_date,
                'jenisMaintenance' => 'corrective',
                'downtimeType' => 'unplanned',
                'source' => 'sippm',
                'sippmLaporanId' => $report->id,
            ];
        })->values();

        // Keep existing SIMPM PM history and append finalized SIPPM history.
        $maintenanceHistory = $maintenanceHistory
            ->concat($sippmMaintenanceHistory)
            ->sortByDesc('tanggal')
            ->values();

        $users = [];
        $usersByRole = [
            'supervisor' => [],
            'teknisi' => [],
            'manajer' => [],
        ];

        foreach (User::whereIn('role', ['supervisor', 'teknisi', 'manajer'])->orderBy('name')->get() as $u) {
            $userData = $u->toBootstrapArray();
            if (!isset($users[$u->role])) {
                $users[$u->role] = $userData;
            }
            $usersByRole[$u->role][] = $userData;
        }

        $technicians = $usersByRole['teknisi'];
        $currentUser = auth()->user();

        return [
            'stations' => $stations,
            'machines' => $machines->values(),
            'machinePerformance' => $machinePerformance,
            'pmSchedules' => $pmSchedules,
            'validationHistory' => $validationHistory,
            'maintenanceHistory' => $maintenanceHistory,
            'users' => $users,
            'usersByRole' => $usersByRole,
            'technicians' => $technicians,
            'currentUser' => $currentUser ? $currentUser->toBootstrapArray() : null,
            'currentRole' => $currentUser?->role ?? 'supervisor',
        ];
    }
}
