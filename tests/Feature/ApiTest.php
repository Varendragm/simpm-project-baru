<?php

namespace Tests\Feature;

use App\Models\Machine;
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

    protected function technician(): User
    {
        return User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi.teknisi',
            'role' => 'teknisi',
            'sub_label' => 'Teknisi · Maintenance',
            'avatar' => 'BS',
            'password' => Hash::make('password'),
        ]);
    }

    protected function seedOneMachine(): Machine
    {
        $station = Station::create([
            'id' => 'st-gilingan',
            'code' => 'STG',
            'name' => 'Stasiun Gilingan',
            'status' => 'aktif',
        ]);

        return Machine::create([
            'id' => 'm-g01',
            'station_id' => $station->id,
            'code' => 'G01',
            'name' => 'Gilingan 01',
            'status' => 'aktif',
        ]);
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
            'validationHistory', 'maintenanceHistory', 'users', 'usersByRole',
            'technicians', 'currentUser', 'currentRole',
        ]);
        $response->assertJsonPath('currentRole', 'supervisor');
        $response->assertJsonPath('machines.0.id', 'm-g01');
    }

    public function test_can_create_new_station_and_machine_without_legacy_condition_status(): void
    {
        $user = $this->supervisor();

        $this->actingAs($user)->postJson('/api/stations', [
            'id' => 'st-baru',
            'code' => 'STX',
            'name' => 'Stasiun Baru',
            'location' => 'Area X',
            'description' => '',
            'status' => 'aktif',
        ])->assertStatus(201);

        $this->actingAs($user)->postJson('/api/machines', [
            'id' => 'm-baru',
            'stationId' => 'st-baru',
            'code' => 'MX01',
            'name' => 'Mesin Baru',
            'type' => 'Unit Uji',
            'status' => 'aktif',
            'capacity' => '10 ton',
            'year' => '2026',
            'notes' => '',
        ])->assertStatus(201);

        $this->assertDatabaseHas('machines', [
            'id' => 'm-baru',
            'station_id' => 'st-baru',
            'status' => 'aktif',
        ]);
    }

    public function test_pm_requires_a_valid_technician_assignment(): void
    {
        $supervisor = $this->supervisor();
        $machine = $this->seedOneMachine();

        $this->actingAs($supervisor)->postJson('/api/pm-schedules', [
            'id' => 'pm-no-tech',
            'machineId' => $machine->id,
            'jenis' => 'Pemeriksaan rutin',
            'tanggal' => '2026-09-20',
            'interval' => 'Mingguan',
            'estimasi' => '30 menit',
            'prioritas' => 'sedang',
        ])->assertStatus(422);
    }

    public function test_pm_can_be_submitted_by_assigned_technician_and_validated_by_supervisor(): void
    {
        $supervisor = $this->supervisor();
        $technician = $this->technician();
        $machine = $this->seedOneMachine();

        $this->actingAs($supervisor)->postJson('/api/pm-schedules', [
            'id' => 'pm-test-1',
            'machineId' => $machine->id,
            'jenis' => 'Pemeriksaan rutin',
            'teknisiUserId' => $technician->id,
            'tanggal' => '2026-09-20',
            'interval' => 'Mingguan',
            'estimasi' => '30 menit',
            'prioritas' => 'sedang',
            'catatan' => '',
        ])->assertStatus(201);

        $this->assertDatabaseHas('pm_schedules', [
            'id' => 'pm-test-1',
            'teknisi_user_id' => $technician->id,
            'status' => 'terjadwal',
        ]);

        $this->actingAs($technician)->postJson('/api/pm-schedules/pm-test-1/laporan', [
            'pemeriksa' => 'Budi Santoso',
            'kategori' => 'mekanik',
            'prioritas' => 'sedang',
            'waktuMulai' => '08:00',
            'waktuSelesai' => '08:30',
            'parameter' => ['getaran' => '3.0'],
            'deskripsi' => 'Normal',
            'tindakan' => 'Tidak ada',
            'spareparts' => [],
            'rekomendasi' => '',
        ])->assertStatus(200);

        $schedule = PmSchedule::find('pm-test-1');
        $this->assertSame('menunggu-validasi', $schedule->status);

        $this->actingAs($supervisor)->postJson('/api/pm-schedules/pm-test-1/validasi', [
            'approve' => true,
            'catatan' => 'Sudah sesuai.',
        ])->assertStatus(200);

        $schedule->refresh();
        $this->assertSame('selesai', $schedule->status);
        $this->assertDatabaseHas('validation_histories', [
            'machine_id' => $machine->id,
            'hasil' => 'disetujui',
        ]);
        $this->assertDatabaseHas('maintenance_histories', [
            'machine_id' => $machine->id,
            'jenis_maintenance' => 'preventive',
            'downtime_type' => 'planned',
        ]);
        $this->assertDatabaseHas('pm_schedules', [
            'machine_id' => $machine->id,
            'tanggal' => '2026-09-27',
            'interval' => 'Mingguan',
            'status' => 'terjadwal',
        ]);
    }

    public function test_unassigned_technician_cannot_submit_another_technicians_pm(): void
    {
        $supervisor = $this->supervisor();
        $technician = $this->technician();
        $otherTechnician = User::create([
            'name' => 'Andi Teknisi',
            'username' => 'andi.teknisi',
            'role' => 'teknisi',
            'password' => Hash::make('password'),
        ]);
        $machine = $this->seedOneMachine();

        $this->actingAs($supervisor)->postJson('/api/pm-schedules', [
            'id' => 'pm-test-2',
            'machineId' => $machine->id,
            'jenis' => 'Pemeriksaan rutin',
            'teknisiUserId' => $technician->id,
            'tanggal' => '2026-09-20',
            'interval' => 'Mingguan',
            'estimasi' => '30 menit',
            'prioritas' => 'sedang',
        ])->assertStatus(201);

        $this->actingAs($otherTechnician)->postJson('/api/pm-schedules/pm-test-2/laporan', [
            'pemeriksa' => 'Andi Teknisi',
            'kategori' => 'mekanik',
            'prioritas' => 'sedang',
        ])->assertStatus(403);
    }

    public function test_change_password_requires_correct_current_password(): void
    {
        $user = $this->supervisor();

        $this->actingAs($user)->postJson('/api/profile/password', [
            'current_password' => 'salah',
            'password' => 'baru123',
        ])->assertStatus(422);

        $this->actingAs($user)->postJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'baru123',
        ])->assertStatus(200);

        $this->assertTrue(Hash::check('baru123', $user->fresh()->password));
    }
}
