<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $machine = Machine::create($this->mapData($data));
        return response()->json($machine->fresh()->toBootstrapArray(), 201);
    }

    public function update(Request $request, Machine $machine)
    {
        $data = $this->validated($request, $machine->id);
        $machine->update($this->mapData($data));
        return response()->json($machine->fresh()->toBootstrapArray());
    }

    public function destroy(Machine $machine)
    {
        if ($machine->pmSchedules()->exists() || $machine->maintenanceHistories()->exists() || $machine->productionRecords()->exists()) {
            return response()->json(['message' => 'Mesin tidak dapat dihapus karena sudah memiliki data operasional.'], 422);
        }

        $machine->delete();
        return response()->json(['message' => 'Mesin berhasil dihapus.']);
    }

    private function validated(Request $request, ?string $machineId = null): array
    {
        return $request->validate([
            'id' => ['required', 'string', 'max:50', Rule::unique('machines', 'id')->ignore($machineId)],
            'stationId' => ['required', 'string', 'exists:stations,id'],
            'code' => ['required', 'string', 'max:20', Rule::unique('machines', 'code')->ignore($machineId)],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:aktif,nonaktif'],
            'capacity' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function mapData(array $data): array
    {
        return [
            'id' => $data['id'],
            'station_id' => $data['stationId'],
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'status' => $data['status'],
            'capacity' => $data['capacity'] ?? null,
            'year' => $data['year'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
