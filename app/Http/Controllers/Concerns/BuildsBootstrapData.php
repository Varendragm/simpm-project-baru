<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Machine;
use App\Models\MaintenanceHistory;
use App\Models\PmSchedule;
use App\Models\Station;
use App\Models\User;
use App\Models\ValidationHistory;
use App\Services\MachineStatusCalculator;
use App\Services\PerformanceCalculator;

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
