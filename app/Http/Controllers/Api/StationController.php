<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $station = Station::create($data);
        return response()->json($station->toBootstrapArray(), 201);
    }

    public function update(Request $request, Station $station)
    {
        $data = $this->validated($request, $station->id);
        $station->update($data);
        return response()->json($station->fresh()->toBootstrapArray());
    }

    public function destroy(Station $station)
    {
        if ($station->machines()->exists()) {
            return response()->json(['message' => 'Stasiun tidak dapat dihapus karena masih memiliki mesin.'], 422);
        }

        $station->delete();
        return response()->json(['message' => 'Stasiun berhasil dihapus.']);
    }

    private function validated(Request $request, ?string $stationId = null): array
    {
        return $request->validate([
            'id' => ['required', 'string', 'max:50', Rule::unique('stations', 'id')->ignore($stationId)],
            'code' => ['required', 'string', 'max:20', Rule::unique('stations', 'code')->ignore($stationId)],
            'name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:aktif,nonaktif'],
        ]);
    }
}
