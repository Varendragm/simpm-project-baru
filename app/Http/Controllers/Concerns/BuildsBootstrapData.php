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
            // status = manual master-data state (aktif/nonaktif)
            // kondisi = automatic operational condition from performance data
            $machineData['kondisi'] = $calculatedCondition;
            $machineData['statusReason'] = $statusCalculator->reason($performance);

            $machines->push($machineData);
            $machinePerformance[$machine->id] = $performance;
        }

        $pmSchedules = PmSchedule::orderBy('tanggal')->get()->map->toBootstrapArray()->values();
        $validationHistory = ValidationHistory::orderByDesc('tanggal')->get()->map->toBootstrapArray()->values();
        $maintenanceHistory = MaintenanceHistory::orderByDesc('tanggal')->get()->map->toBootstrapArray()->values();

        $users = [];
        foreach (User::whereIn('role', ['supervisor', 'teknisi', 'manajer'])->get() as $u) {
            if (!isset($users[$u->role])) {
                $users[$u->role] = $u->toBootstrapArray();
            }
        }

        return [
            'stations' => $stations,
            'machines' => $machines->values(),
            'machinePerformance' => $machinePerformance,
            'pmSchedules' => $pmSchedules,
            'validationHistory' => $validationHistory,
            'maintenanceHistory' => $maintenanceHistory,
            'users' => $users,
            'currentRole' => auth()->check() ? auth()->user()->role : 'supervisor',
        ];
    }
}
