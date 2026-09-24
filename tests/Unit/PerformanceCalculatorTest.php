<?php

namespace Tests\Unit;

use App\Models\Machine;
use App\Models\MachineProductionRecord;
use App\Models\MaintenanceHistory;
use App\Models\Station;
use App\Services\PerformanceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private function machine(): Machine
    {
        $station = Station::create([
            'id' => 'st-test',
            'code' => 'TST',
            'name' => 'Stasiun Test',
            'location' => 'Test',
            'description' => null,
            'status' => 'aktif',
        ]);

        return Machine::create([
            'id' => 'm-test',
            'station_id' => $station->id,
            'code' => 'T01',
            'name' => 'Mesin Test',
            'type' => 'Test',
            'status' => 'aktif',
            'kondisi' => 'perhatian',
            'capacity' => null,
            'year' => null,
            'notes' => null,
        ]);
    }

    public function test_pm_downtime_does_not_count_as_failure(): void
    {
        $machine = $this->machine();

        MachineProductionRecord::create([
            'machine_id' => $machine->id,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'planned_minutes' => 1000,
            'actual_output' => 900,
            'ideal_output' => 1000,
            'good_output' => 900,
        ]);

        MaintenanceHistory::create([
            'no_laporan' => 'PM-TEST-001',
            'machine_id' => $machine->id,
            'kategori' => 'Mekanik',
            'pekerjaan' => 'Pelumasan bearing',
            'pelaksana' => 'Budi Santoso',
            'downtime_menit' => 60,
            'hasil' => 'Baik',
            'catatan' => null,
            'tanggal' => '2026-09-10',
            'jenis_maintenance' => 'preventive',
            'downtime_type' => 'planned',
        ]);

        $result = app(PerformanceCalculator::class)->calculate($machine);

        $this->assertSame(0, $result['failureCount']);
        $this->assertNull($result['mttr']);
        $this->assertNull($result['mtbf']);
        $this->assertSame(100.0, $result['reliability']);
        $this->assertSame(90.0, $result['performance']);
        $this->assertSame(90.0, $result['quality']);
        $this->assertSame(90.0, $result['oee']);
    }

    public function test_corrective_failure_drives_mttr_and_mtbf(): void
    {
        $machine = $this->machine();

        MachineProductionRecord::create([
            'machine_id' => $machine->id,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'planned_minutes' => 1000,
            'actual_output' => 900,
            'ideal_output' => 1000,
            'good_output' => 900,
        ]);

        MaintenanceHistory::create([
            'no_laporan' => 'BR-TEST-001',
            'machine_id' => $machine->id,
            'kategori' => 'Mekanik',
            'pekerjaan' => 'Penggantian bearing rusak',
            'pelaksana' => 'Budi Santoso',
            'downtime_menit' => 120,
            'hasil' => 'Selesai',
            'catatan' => 'breakdown',
            'tanggal' => '2026-09-10',
            'jenis_maintenance' => 'corrective',
            'downtime_type' => 'unplanned',
        ]);

        $result = app(PerformanceCalculator::class)->calculate($machine);

        $this->assertSame(1, $result['failureCount']);
        $this->assertSame(2.0, $result['mttr']);
        $this->assertSame(14.67, $result['mtbf']);
        $this->assertSame(93.42, $result['reliability']);
        $this->assertSame(88.0, $result['availability']);
    }

    public function test_production_record_cannot_overlap_for_same_machine(): void
    {
        $machine = $this->machine();

        MachineProductionRecord::create([
            'machine_id' => $machine->id,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'planned_minutes' => 1000,
            'actual_output' => 900,
            'ideal_output' => 1000,
            'good_output' => 900,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        MachineProductionRecord::create([
            'machine_id' => $machine->id,
            'period_start' => '2026-09-15',
            'period_end' => '2026-10-15',
            'planned_minutes' => 1000,
            'actual_output' => 900,
            'ideal_output' => 1000,
            'good_output' => 900,
        ]);
    }
}
