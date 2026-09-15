<?php

namespace Tests\Unit;

use App\Models\Machine;
use App\Models\MachinePerformance;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_machine_belongs_to_station_and_has_performance(): void
    {
        $station = Station::create([
            'id' => 'st-gilingan', 'code' => 'STG', 'name' => 'Stasiun Gilingan', 'status' => 'aktif',
        ]);

        $machine = Machine::create([
            'id' => 'm-g01', 'station_id' => $station->id, 'code' => 'G01',
            'name' => 'Gilingan 01', 'status' => 'normal',
        ]);

        MachinePerformance::create([
            'machine_id' => $machine->id, 'oee' => 83, 'availability' => 94, 'reliability' => 82,
            'mttr' => 2, 'mtbf' => 20, 'downtime_bulan_ini' => 4, 'perbaikan_bulan_ini' => 2,
            'trend_oee' => [80, 84, 81, 86, 83, 83],
        ]);

        $this->assertTrue($machine->station->is($station));
        $this->assertEquals(83, $machine->performance->oee);
        $this->assertCount(1, $station->machines);
    }

    public function test_machine_bootstrap_array_uses_camel_case_keys(): void
    {
        $station = Station::create([
            'id' => 'st-gilingan', 'code' => 'STG', 'name' => 'Stasiun Gilingan', 'status' => 'aktif',
        ]);
        $machine = Machine::create([
            'id' => 'm-g01', 'station_id' => $station->id, 'code' => 'G01',
            'name' => 'Gilingan 01', 'status' => 'normal',
        ]);

        $array = $machine->toBootstrapArray();

        $this->assertArrayHasKey('stationId', $array);
        $this->assertEquals('st-gilingan', $array['stationId']);
    }
}
