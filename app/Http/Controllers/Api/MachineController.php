<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\MachinePerformance;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'max:50'],
            'stationId' => ['required', 'string', 'exists:stations,id'],
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:normal,perhatian,perbaikan'],
            'capacity' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        $machine = Machine::updateOrCreate(
            ['id' => $data['id']],
            [
                'station_id' => $data['stationId'],
                'code' => $data['code'],
                'name' => $data['name'],
                'type' => $data['type'] ?? null,
                'status' => $data['status'],
                'capacity' => $data['capacity'] ?? null,
                'year' => $data['year'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        MachinePerformance::firstOrCreate(
            ['machine_id' => $machine->id],
            [
                'oee' => 0, 'availability' => 0, 'reliability' => 0,
                'mttr' => 0, 'mtbf' => 0, 'downtime_bulan_ini' => 0,
                'perbaikan_bulan_ini' => 0, 'trend_oee' => [0, 0, 0, 0, 0, 0],
            ]
        );

        return response()->json($machine->toBootstrapArray(), 201);
    }
}
