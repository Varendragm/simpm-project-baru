<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Machine;
use App\Models\MachinePerformance;
use App\Models\MaintenanceHistory;
use App\Models\PmSchedule;
use App\Models\Station;
use App\Models\User;
use App\Models\ValidationHistory;

trait BuildsBootstrapData
{
    protected function buildBootstrapData(): array
    {
        $stations = Station::orderBy('name')->get()->map->toBootstrapArray()->values();
        $machines = Machine::orderBy('code')->get()->map->toBootstrapArray()->values();

        $machinePerformance = [];
        foreach (MachinePerformance::all() as $perf) {
            $machinePerformance[$perf->machine_id] = $perf->toBootstrapArray();
        }

        $pmSchedules = PmSchedule::orderBy('tanggal')->get()->map->toBootstrapArray()->values();
        $validationHistory = ValidationHistory::orderByDesc('tanggal')->get()->map->toBootstrapArray()->values();
        $maintenanceHistory = MaintenanceHistory::orderByDesc('tanggal')->get()->map->toBootstrapArray()->values();

        $users = [];
        foreach (User::whereIn('role', ['supervisor', 'teknisi', 'manajer'])->get() as $u) {
            // Jika ada beberapa user dengan role sama, ambil yang pertama (role dipakai sebagai kunci
            // seperti pada mockup asli — satu akun demo per peran).
            if (!isset($users[$u->role])) {
                $users[$u->role] = $u->toBootstrapArray();
            }
        }

        return [
            'stations' => $stations,
            'machines' => $machines,
            'machinePerformance' => $machinePerformance,
            'pmSchedules' => $pmSchedules,
            'validationHistory' => $validationHistory,
            'maintenanceHistory' => $maintenanceHistory,
            'users' => $users,
            'currentRole' => auth()->check() ? auth()->user()->role : 'supervisor',
        ];
    }
}
