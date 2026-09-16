<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceHistory;
use App\Models\PmSchedule;
use App\Models\User;
use App\Models\ValidationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PmScheduleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'max:50', 'unique:pm_schedules,id'],
            'machineId' => ['required', 'string', 'exists:machines,id'],
            'jenis' => ['required', 'string', 'max:150'],
            'teknisi' => ['nullable', 'string', 'max:150'],
            'teknisiUserId' => ['nullable', 'integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'interval' => ['nullable', 'string', 'max:30'],
            'estimasi' => ['nullable', 'string', 'max:30'],
            'prioritas' => ['required', 'string', 'in:rendah,sedang,tinggi,kritis'],
            'catatan' => ['nullable', 'string'],
        ]);

        $technician = null;
        if (!empty($data['teknisiUserId'])) {
            $technician = User::whereKey($data['teknisiUserId'])->where('role', 'teknisi')->first();
            if (!$technician) {
                return response()->json(['message' => 'Teknisi yang dipilih tidak valid.'], 422);
            }
        } elseif (!empty($data['teknisi'])) {
            $technician = User::where('role', 'teknisi')->where('name', $data['teknisi'])->first();
        }

        $schedule = PmSchedule::create([
            'id' => $data['id'],
            'machine_id' => $data['machineId'],
            'teknisi_user_id' => $technician?->id,
            'jenis' => $data['jenis'],
            'teknisi' => $technician?->name ?? ($data['teknisi'] ?? ''),
            'tanggal' => $data['tanggal'],
            'interval' => $data['interval'] ?? null,
            'estimasi' => $data['estimasi'] ?? null,
            'prioritas' => $data['prioritas'],
            'status' => 'terjadwal',
            'catatan' => $data['catatan'] ?? null,
        ]);

        return response()->json($schedule->toBootstrapArray(), 201);
    }

    public function submitLaporan(Request $request, PmSchedule $pmSchedule)
    {
        if ($pmSchedule->status === 'selesai') {
            return response()->json(['message' => 'Jadwal ini sudah selesai divalidasi.'], 422);
        }

        if ($pmSchedule->teknisi_user_id && $pmSchedule->teknisi_user_id !== auth()->id()) {
            return response()->json(['message' => 'Jadwal ini bukan ditugaskan kepada akun Teknisi yang sedang login.'], 403);
        }

        $report = $request->validate([
            'tanggalPemeriksaan' => ['nullable', 'date'],
            'dikirim' => ['nullable', 'date'],
            'pemeriksa' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'in:mekanik,elektrik,instrumentasi'],
            'prioritas' => ['required', 'string', 'in:rendah,sedang,tinggi,kritis'],
            'waktuMulai' => ['nullable', 'date_format:H:i'],
            'waktuSelesai' => ['nullable', 'date_format:H:i'],
            'parameter' => ['nullable', 'array'],
            'deskripsi' => ['nullable', 'string'],
            'tindakan' => ['nullable', 'string'],
            'spareparts' => ['nullable', 'array'],
            'rekomendasi' => ['nullable', 'string'],
            'jadwalBerikutnya' => ['nullable', 'date'],
            'catatanSupervisor' => ['nullable', 'string'],
        ]);

        $report['tanggalPemeriksaan'] = $report['tanggalPemeriksaan'] ?? Carbon::today()->toDateString();
        $report['dikirim'] = $report['dikirim'] ?? Carbon::today()->toDateString();
        $report['pemeriksa'] = auth()->user()->name;
        $report['status'] = 'menunggu';
        $report['catatanSupervisor'] = '';

        $pmSchedule->update([
            'report' => $report,
            'status' => 'menunggu-validasi',
        ]);

        return response()->json($pmSchedule->fresh()->toBootstrapArray());
    }

    public function validasi(Request $request, PmSchedule $pmSchedule)
    {
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ]);

        if (!$pmSchedule->report) {
            return response()->json(['message' => 'Belum ada laporan pemeriksaan untuk jadwal ini.'], 422);
        }

        if ($pmSchedule->status !== 'menunggu-validasi') {
            return response()->json(['message' => 'Jadwal ini tidak sedang menunggu validasi.'], 422);
        }

        $report = $pmSchedule->report;
        $report['catatanSupervisor'] = $data['catatan'] ?? '';
        $report['status'] = $data['approve'] ? 'disetujui' : 'ditolak';

        $pmSchedule = DB::transaction(function () use ($pmSchedule, $report, $data) {
            $pmSchedule->update([
                'report' => $report,
                'status' => $data['approve'] ? 'selesai' : 'ditolak',
            ]);

            ValidationHistory::create([
                'machine_id' => $pmSchedule->machine_id,
                'jenis' => $pmSchedule->jenis,
                'teknisi' => $report['pemeriksa'] ?? $pmSchedule->teknisi,
                'divalidasi_oleh' => auth()->user()->name,
                'tanggal' => Carbon::parse($report['tanggalPemeriksaan'] ?? Carbon::today())->toDateString(),
                'hasil' => $data['approve'] ? 'disetujui' : 'ditolak',
            ]);

            if ($data['approve']) {
                $downtime = $this->calculateDowntime($report['waktuMulai'] ?? null, $report['waktuSelesai'] ?? null);

                MaintenanceHistory::create([
                    'no_laporan' => 'PM-' . strtoupper($pmSchedule->id),
                    'machine_id' => $pmSchedule->machine_id,
                    'kategori' => ucfirst($report['kategori'] ?? 'mekanik'),
                    'pekerjaan' => $pmSchedule->jenis,
                    'pelaksana' => $report['pemeriksa'] ?? $pmSchedule->teknisi,
                    'downtime_menit' => $downtime,
                    'hasil' => 'Baik',
                    'catatan' => $data['catatan'] ?? '',
                    'tanggal' => Carbon::parse($report['tanggalPemeriksaan'] ?? Carbon::today())->toDateString(),
                ]);
            }

            return $pmSchedule->fresh();
        });

        return response()->json($pmSchedule->toBootstrapArray());
    }

    private function calculateDowntime(?string $start, ?string $end): int
    {
        if (!$start || !$end) {
            return 0;
        }

        [$startHour, $startMinute] = array_map('intval', explode(':', $start));
        [$endHour, $endMinute] = array_map('intval', explode(':', $end));
        $minutes = ($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute);

        return $minutes < 0 ? $minutes + (24 * 60) : $minutes;
    }
}
