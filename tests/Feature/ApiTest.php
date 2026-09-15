<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachinePerformance;
use App\Models\PmSchedule;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function supervisor(): User
    {
        return User::create([
            'name' => 'Sri Handayani',
            'username' => 'sri.supervisor',
            'role' => 'supervisor',
            'sub_label' => 'Supervisor · Produksi',
            'avatar' => 'SH',
            'password' => Hash::make('password'),
        ]);
    }

    protected function seedOneMachine(): Machine
    {
        $station = Station::create([
            'id' => 'st-gilingan', 'code' => 'STG', 'name' => 'Stasiun Gilingan', 'status' => 'aktif',
        ]);
        $machine = Machine::create([
            'id' => 'm-g01', 'station_id' => $station->id, 'code' => 'G01',
            'name' => 'Gilingan 01', 'status' => 'normal',
        ]);
        MachinePerformance::create([
            'machine_id' => $machine->id, 'oee' => 80, 'availability' => 90, 'reliability' => 80,
            'mttr' => 2, 'mtbf' => 20, 'downtime_bulan_ini' => 3, 'perbaikan_bulan_ini' => 1,
            'trend_oee' => [80, 80, 80, 80, 80, 80],
        ]);

        return $machine;
    }

    public function test_bootstrap_requires_authentication(): void
    {
        $this->getJson('/api/bootstrap')->assertStatus(401);
    }

    public function test_bootstrap_returns_expected_shape(): void
    {
        $user = $this->supervisor();
        $this->seedOneMachine();

        $response = $this->actingAs($user)->getJson('/api/bootstrap');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stations', 'machines', 'machinePerformance', 'pmSchedules',
            'validationHistory', 'maintenanceHistory', 'users', 'currentRole',
        ]);
        $response->assertJsonPath('currentRole', 'supervisor');
        $response->assertJsonPath('machines.0.id', 'm-g01');
    }

    public function test_can_create_new_station_and_machine(): void
    {
        $user = $this->supervisor();

        $this->actingAs($user)->postJson('/api/stations', [
            'id' => 'st-baru', 'code' => 'STX', 'name' => 'Stasiun Baru',
            'location' => 'Area X', 'description' => '', 'status' => 'aktif',
        ])->assertStatus(201);

        $this->assertDatabaseHas('stations', ['id' => 'st-baru', 'code' => 'STX']);

        $this->actingAs($user)->postJson('/api/machines', [
            'id' => 'm-baru', 'stationId' => 'st-baru', 'code' => 'MX01',
            'name' => 'Mesin Baru', 'type' => 'Unit Uji', 'status' => 'normal',
            'capacity' => '10 ton', 'year' => '2026', 'notes' => '',
        ])->assertStatus(201);

        $this->assertDatabaseHas('machines', ['id' => 'm-baru', 'station_id' => 'st-baru']);
        $this->assertDatabaseHas('machine_performances', ['machine_id' => 'm-baru']);
    }

    public function test_can_create_pm_schedule_submit_report_and_finalize_validation(): void
    {
        $user = $this->supervisor();
        $machine = $this->seedOneMachine();

        $this->actingAs($user)->postJson('/api/pm-schedules', [
            'id' => 'pm-test-1', 'machineId' => $machine->id, 'jenis' => 'Pemeriksaan rutin',
            'teknisi' => 'Budi Santoso', 'tanggal' => '2026-08-20', 'interval' => 'Mingguan',
            'estimasi' => '30 menit', 'prioritas' => 'sedang', 'catatan' => '',
        ])->assertStatus(201);

        $this->assertDatabaseHas('pm_schedules', ['id' => 'pm-test-1', 'status' => 'terjadwal']);

        $this->actingAs($user)->postJson('/api/pm-schedules/pm-test-1/laporan', [
            'pemeriksa' => 'Budi Santoso', 'kategori' => 'mekanik', 'prioritas' => 'sedang',
            'waktuMulai' => '08:00', 'waktuSelesai' => '08:30',
            'parameter' => ['getaran' => '3.0'], 'deskripsi' => 'Normal', 'tindakan' => 'Tidak ada',
            'spareparts' => [], 'rekomendasi' => '',
        ])->assertStatus(200);

        $schedule = PmSchedule::find('pm-test-1');
        $this->assertEquals('menunggu-validasi', $schedule->status);
        $this->assertNotNull($schedule->report);

        $this->actingAs($user)->postJson('/api/pm-schedules/pm-test-1/validasi', [
            'approve' => true, 'catatan' => 'Sudah sesuai.',
        ])->assertStatus(200);

        $schedule->refresh();
        $this->assertEquals('selesai', $schedule->status);
        $this->assertDatabaseHas('validation_histories', ['machine_id' => $machine->id, 'hasil' => 'disetujui']);
        $this->assertDatabaseHas('maintenance_histories', ['machine_id' => $machine->id]);
    }

    public function test_change_password_requires_correct_current_password(): void
    {
        $user = $this->supervisor();

        $this->actingAs($user)->postJson('/api/profile/password', [
            'current_password' => 'salah', 'password' => 'baru123',
        ])->assertStatus(422);

        $this->actingAs($user)->postJson('/api/profile/password', [
            'current_password' => 'password', 'password' => 'baru123',
        ])->assertStatus(200);

        $this->assertTrue(Hash::check('baru123', $user->fresh()->password));
    }
}
