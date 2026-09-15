<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachinePerformance;
use App\Models\MaintenanceHistory;
use App\Models\PmSchedule;
use App\Models\Station;
use App\Models\User;
use App\Models\ValidationHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimpmSeeder extends Seeder
{
    public function run(): void
    {
        // ================= USERS (satu akun = satu peran, sinkron SIPPM) =================
        User::create([
            'name' => 'Sri Handayani', 'username' => 'sri.supervisor', 'role' => 'supervisor',
            'sub_label' => 'Supervisor · Produksi', 'avatar' => 'SH', 'phone' => '0813-2244-5566',
            'department' => 'Produksi — Gilingan', 'joined_year' => 2019,
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => 'Budi Santoso', 'username' => 'budi.teknisi', 'role' => 'teknisi',
            'sub_label' => 'Teknisi · Maintenance', 'avatar' => 'BS', 'phone' => '0814-9988-7766',
            'department' => 'Mekanik', 'joined_year' => 2021,
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => 'Ir. Wahyu Prasetyo, M.T.', 'username' => 'wahyu.manajer', 'role' => 'manajer',
            'sub_label' => 'Manajer · Produksi', 'avatar' => 'WP', 'phone' => '0811-3344-5567',
            'department' => 'Manajer Produksi', 'joined_year' => 2015,
            'password' => Hash::make('password'),
        ]);

        // ================= STATIONS =================
        $stations = [
            ['id' => 'st-gilingan', 'code' => 'STG', 'name' => 'Stasiun Gilingan', 'location' => 'Area Produksi — Depan', 'description' => 'Ekstraksi nira dari batang tebu melalui rangkaian unit gilingan.', 'status' => 'aktif'],
            ['id' => 'st-boiler', 'code' => 'STB', 'name' => 'Stasiun Boiler', 'location' => 'Area Produksi — Tengah', 'description' => 'Penyediaan uap untuk kebutuhan proses produksi & pembangkit listrik.', 'status' => 'aktif'],
            ['id' => 'st-puteran', 'code' => 'STP', 'name' => 'Stasiun Puteran & Pemurnian', 'location' => 'Area Produksi — Belakang', 'description' => 'Pemisahan kristal gula dari larutan melalui proses puteran.', 'status' => 'aktif'],
        ];
        foreach ($stations as $s) {
            Station::create($s);
        }

        // ================= MACHINES =================
        $machines = [
            ['id' => 'm-g01', 'station_id' => 'st-gilingan', 'code' => 'G01', 'name' => 'Gilingan 01', 'type' => 'Unit Gilingan Tebu', 'status' => 'normal', 'capacity' => '120 TCD', 'year' => '2010', 'notes' => 'Unit gilingan pertama pada rangkaian ekstraksi.'],
            ['id' => 'm-g02', 'station_id' => 'st-gilingan', 'code' => 'G02', 'name' => 'Gilingan 02', 'type' => 'Unit Gilingan Tebu', 'status' => 'perhatian', 'capacity' => '120 TCD', 'year' => '2010', 'notes' => 'Riwayat getaran meningkat pada dudukan motor.'],
            ['id' => 'm-g03', 'station_id' => 'st-gilingan', 'code' => 'G03', 'name' => 'Gilingan 03', 'type' => 'Unit Gilingan Tebu', 'status' => 'normal', 'capacity' => '120 TCD', 'year' => '2012', 'notes' => ''],
            ['id' => 'm-g04', 'station_id' => 'st-gilingan', 'code' => 'G04', 'name' => 'Gilingan 04', 'type' => 'Unit Gilingan Tebu', 'status' => 'perbaikan', 'capacity' => '120 TCD', 'year' => '2012', 'notes' => 'Sedang dalam perbaikan panel motor.'],
            ['id' => 'm-b01', 'station_id' => 'st-boiler', 'code' => 'B01', 'name' => 'Boiler 01', 'type' => 'Ketel Uap Pipa Air', 'status' => 'normal', 'capacity' => '20 ton uap/jam', 'year' => '2008', 'notes' => ''],
            ['id' => 'm-b02', 'station_id' => 'st-boiler', 'code' => 'B02', 'name' => 'Boiler 02', 'type' => 'Ketel Uap Pipa Air', 'status' => 'normal', 'capacity' => '20 ton uap/jam', 'year' => '2008', 'notes' => ''],
            ['id' => 'm-b03', 'station_id' => 'st-boiler', 'code' => 'B03', 'name' => 'Boiler 03', 'type' => 'Ketel Uap Pipa Air', 'status' => 'perhatian', 'capacity' => '25 ton uap/jam', 'year' => '2015', 'notes' => 'Efisiensi pembakaran menurun, perlu pemeriksaan burner.'],
            ['id' => 'm-p01', 'station_id' => 'st-puteran', 'code' => 'P01', 'name' => 'Puteran 01', 'type' => 'Centrifugal Batch', 'status' => 'normal', 'capacity' => '1.5 ton/batch', 'year' => '2011', 'notes' => ''],
            ['id' => 'm-p02', 'station_id' => 'st-puteran', 'code' => 'P02', 'name' => 'Puteran 02', 'type' => 'Centrifugal Batch', 'status' => 'normal', 'capacity' => '1.5 ton/batch', 'year' => '2011', 'notes' => ''],
        ];
        foreach ($machines as $m) {
            Machine::create($m);
        }

        // ================= MACHINE PERFORMANCES =================
        $performances = [
            'm-g01' => ['oee' => 83, 'availability' => 94, 'reliability' => 82, 'mttr' => 2.0, 'mtbf' => 20, 'downtime_bulan_ini' => 4.0, 'perbaikan_bulan_ini' => 2, 'trend_oee' => [80, 84, 81, 86, 83, 83]],
            'm-g02' => ['oee' => 58, 'availability' => 71, 'reliability' => 45, 'mttr' => 2.9, 'mtbf' => 18, 'downtime_bulan_ini' => 10.2, 'perbaikan_bulan_ini' => 5, 'trend_oee' => [62, 58, 68, 52, 49, 58]],
            'm-g03' => ['oee' => 88, 'availability' => 96, 'reliability' => 90, 'mttr' => 1.5, 'mtbf' => 52, 'downtime_bulan_ini' => 2.5, 'perbaikan_bulan_ini' => 1, 'trend_oee' => [87, 89, 86, 91, 85, 88]],
            'm-g04' => ['oee' => 69, 'availability' => 80, 'reliability' => 63, 'mttr' => 2.5, 'mtbf' => 16, 'downtime_bulan_ini' => 7.5, 'perbaikan_bulan_ini' => 3, 'trend_oee' => [55, 60, 64, 66, 68, 69]],
            'm-b01' => ['oee' => 81, 'availability' => 92, 'reliability' => 85, 'mttr' => 1.8, 'mtbf' => 44, 'downtime_bulan_ini' => 3.2, 'perbaikan_bulan_ini' => 1, 'trend_oee' => [78, 80, 79, 83, 82, 81]],
            'm-b02' => ['oee' => 79, 'availability' => 90, 'reliability' => 80, 'mttr' => 2.1, 'mtbf' => 38, 'downtime_bulan_ini' => 3.8, 'perbaikan_bulan_ini' => 2, 'trend_oee' => [75, 77, 80, 78, 81, 79]],
            'm-b03' => ['oee' => 63, 'availability' => 75, 'reliability' => 58, 'mttr' => 3.0, 'mtbf' => 14, 'downtime_bulan_ini' => 8.4, 'perbaikan_bulan_ini' => 3, 'trend_oee' => [70, 68, 64, 60, 61, 63]],
            'm-p01' => ['oee' => 85, 'availability' => 95, 'reliability' => 88, 'mttr' => 1.6, 'mtbf' => 48, 'downtime_bulan_ini' => 2.0, 'perbaikan_bulan_ini' => 1, 'trend_oee' => [82, 84, 86, 85, 87, 85]],
            'm-p02' => ['oee' => 86, 'availability' => 96, 'reliability' => 89, 'mttr' => 1.4, 'mtbf' => 50, 'downtime_bulan_ini' => 1.6, 'perbaikan_bulan_ini' => 0, 'trend_oee' => [84, 85, 87, 88, 86, 86]],
        ];
        foreach ($performances as $machineId => $p) {
            MachinePerformance::create(array_merge(['machine_id' => $machineId], $p));
        }

        // ================= PM SCHEDULES =================
        PmSchedule::create(['id' => 'pm-001', 'machine_id' => 'm-g01', 'jenis' => 'Pelumasan bearing', 'teknisi' => 'Budi Santoso', 'tanggal' => '2026-08-20', 'interval' => 'Mingguan', 'estimasi' => '30 menit', 'prioritas' => 'sedang', 'status' => 'terjadwal', 'catatan' => '']);
        PmSchedule::create([
            'id' => 'pm-002', 'machine_id' => 'm-g02', 'jenis' => 'Pemeriksaan gearbox & getaran', 'teknisi' => 'Budi Santoso',
            'tanggal' => '2026-08-18', 'interval' => 'Mingguan', 'estimasi' => '45 menit', 'prioritas' => 'tinggi', 'status' => 'menunggu-validasi', 'catatan' => '',
            'report' => [
                'tanggalPemeriksaan' => '2026-08-18', 'dikirim' => '2026-08-18 14:20', 'pemeriksa' => 'Budi Santoso',
                'kategori' => 'mekanik', 'prioritas' => 'sedang', 'waktuMulai' => '08:10', 'waktuSelesai' => '08:52',
                'parameter' => ['getaran' => '4.2', 'suhu' => '62', 'pelumasan' => 'Baik', 'torsi' => '45'],
                'deskripsi' => 'Suhu gearbox 62°C, dalam batas normal. Terdengar sedikit getaran pada dudukan motor dan baut sedikit kendur.',
                'tindakan' => 'Mengencangkan ulang baut dudukan motor & menambahkan pelumas pada gearbox. Tidak ditemukan kebocoran oli.',
                'spareparts' => [], 'rekomendasi' => 'Getaran pada dudukan motor perlu dipantau di PM berikutnya — jika masih meningkat, disarankan penggantian bearing.',
                'jadwalBerikutnya' => '2026-08-25', 'status' => 'menunggu', 'catatanSupervisor' => '',
            ],
        ]);
        PmSchedule::create(['id' => 'pm-003', 'machine_id' => 'm-g03', 'jenis' => 'Pemeriksaan panel & kontaktor', 'teknisi' => 'Budi Santoso', 'tanggal' => '2026-08-25', 'interval' => 'Bulanan', 'estimasi' => '40 menit', 'prioritas' => 'sedang', 'status' => 'terjadwal', 'catatan' => '']);
        PmSchedule::create(['id' => 'pm-004', 'machine_id' => 'm-g04', 'jenis' => 'Kalibrasi sensor instrumentasi', 'teknisi' => 'Rudi Hartono', 'tanggal' => '2026-08-15', 'interval' => 'Bulanan', 'estimasi' => '50 menit', 'prioritas' => 'rendah', 'status' => 'selesai', 'catatan' => '']);
        PmSchedule::create(['id' => 'pm-005', 'machine_id' => 'm-b01', 'jenis' => 'Pemeriksaan tekanan & katup pengaman', 'teknisi' => 'Rudi Hartono', 'tanggal' => '2026-08-22', 'interval' => 'Mingguan', 'estimasi' => '35 menit', 'prioritas' => 'tinggi', 'status' => 'terjadwal', 'catatan' => '']);
        PmSchedule::create(['id' => 'pm-006', 'machine_id' => 'm-b03', 'jenis' => 'Pemeriksaan burner & efisiensi bahan bakar', 'teknisi' => 'Budi Santoso', 'tanggal' => '2026-08-19', 'interval' => 'Mingguan', 'estimasi' => '60 menit', 'prioritas' => 'tinggi', 'status' => 'terjadwal', 'catatan' => '']);
        PmSchedule::create(['id' => 'pm-007', 'machine_id' => 'm-p01', 'jenis' => 'Pemeriksaan bearing & saringan', 'teknisi' => 'Rudi Hartono', 'tanggal' => '2026-08-28', 'interval' => 'Bulanan', 'estimasi' => '30 menit', 'prioritas' => 'rendah', 'status' => 'terjadwal', 'catatan' => '']);

        // ================= VALIDATION HISTORY =================
        ValidationHistory::create(['machine_id' => 'm-g04', 'jenis' => 'Kalibrasi sensor instrumentasi', 'teknisi' => 'Rudi Hartono', 'divalidasi_oleh' => 'Sri Handayani', 'tanggal' => '2026-08-15', 'hasil' => 'disetujui']);
        ValidationHistory::create(['machine_id' => 'm-g03', 'jenis' => 'Pelumasan bearing', 'teknisi' => 'Budi Santoso', 'divalidasi_oleh' => 'Sri Handayani', 'tanggal' => '2026-08-11', 'hasil' => 'disetujui']);
        ValidationHistory::create(['machine_id' => 'm-g01', 'jenis' => 'Pemeriksaan panel & kontaktor', 'teknisi' => 'Rudi Hartono', 'divalidasi_oleh' => 'Sri Handayani', 'tanggal' => '2026-08-08', 'hasil' => 'disetujui']);

        // ================= MAINTENANCE HISTORY (simulasi sinkron SIPPM) =================
        $history = [
            ['no_laporan' => 'BR-2026-014', 'machine_id' => 'm-g02', 'kategori' => 'Mekanik', 'pekerjaan' => 'Bearing 6205 sisi kanan poros diganti', 'pelaksana' => 'Budi Santoso', 'downtime_menit' => 95, 'hasil' => 'Baik', 'catatan' => 'Getaran kembali normal setelah penggantian.', 'tanggal' => '2026-08-14'],
            ['no_laporan' => 'BR-2026-013', 'machine_id' => 'm-g01', 'kategori' => 'Elektrik', 'pekerjaan' => 'Kabel motor penggerak diganti', 'pelaksana' => 'Rudi Hartono', 'downtime_menit' => 70, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-13'],
            ['no_laporan' => 'BR-2026-011', 'machine_id' => 'm-g02', 'kategori' => 'Instrumentasi', 'pekerjaan' => 'Sensor getaran dikencangkan ulang', 'pelaksana' => 'Budi Santoso', 'downtime_menit' => 40, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-10'],
            ['no_laporan' => 'BR-2026-010', 'machine_id' => 'm-g02', 'kategori' => 'Elektrik', 'pekerjaan' => 'Kontaktor motor utama diganti', 'pelaksana' => 'Rudi Hartono', 'downtime_menit' => 150, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-06'],
            ['no_laporan' => 'BR-2026-009', 'machine_id' => 'm-g03', 'kategori' => 'Instrumentasi', 'pekerjaan' => 'Kalibrasi sensor tekanan', 'pelaksana' => 'Rudi Hartono', 'downtime_menit' => 45, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-05'],
            ['no_laporan' => 'BR-2026-008', 'machine_id' => 'm-g01', 'kategori' => 'Mekanik', 'pekerjaan' => 'Bearing poros utama diganti', 'pelaksana' => 'Budi Santoso', 'downtime_menit' => 120, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-05'],
            ['no_laporan' => 'BR-2026-016', 'machine_id' => 'm-g04', 'kategori' => 'Elektrik', 'pekerjaan' => 'Kontaktor & panel motor diperbaiki', 'pelaksana' => 'Rudi Hartono', 'downtime_menit' => 180, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-17'],
            ['no_laporan' => 'BR-2026-006', 'machine_id' => 'm-g02', 'kategori' => 'Mekanik', 'pekerjaan' => 'Baut sambungan roll gilingan dikencangkan', 'pelaksana' => 'Budi Santoso', 'downtime_menit' => 60, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-07-30'],
            ['no_laporan' => 'BR-2026-021', 'machine_id' => 'm-b03', 'kategori' => 'Mekanik', 'pekerjaan' => 'Pembersihan nozzle burner', 'pelaksana' => 'Budi Santoso', 'downtime_menit' => 110, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-12'],
            ['no_laporan' => 'BR-2026-019', 'machine_id' => 'm-b01', 'kategori' => 'Instrumentasi', 'pekerjaan' => 'Kalibrasi pressure gauge', 'pelaksana' => 'Rudi Hartono', 'downtime_menit' => 35, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-09'],
            ['no_laporan' => 'BR-2026-022', 'machine_id' => 'm-p02', 'kategori' => 'Mekanik', 'pekerjaan' => 'Penggantian seal poros puteran', 'pelaksana' => 'Budi Santoso', 'downtime_menit' => 75, 'hasil' => 'Baik', 'catatan' => '', 'tanggal' => '2026-08-11'],
        ];
        foreach ($history as $h) {
            MaintenanceHistory::create($h);
        }
    }
}
