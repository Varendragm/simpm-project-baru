<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceHistory;
use App\Models\PmSchedule;
use App\Models\ValidationHistory;
use Illuminate\Http\Request;

class PmScheduleController extends Controller
{
    /** Tanggal "hari ini" pada data demo — sinkron dengan DEMO_TODAY di public/js/app.js */
    const DEMO_TODAY = '2026-08-18';

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'max:50'],
            'machineId' => ['required', 'string', 'exists:machines,id'],
            'jenis' => ['required', 'string', 'max:150'],
            'teknisi' => ['nullable', 'string', 'max:150'],
            'tanggal' => ['required', 'date'],
            'interval' => ['nullable', 'string', 'max:30'],
            'estimasi' => ['nullable', 'string', 'max:30'],
            'prioritas' => ['required', 'string', 'in:rendah,sedang,tinggi,kritis'],
            'status' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);

        $schedule = PmSchedule::create([
            'id' => $data['id'],
            'machine_id' => $data['machineId'],
            'jenis' => $data['jenis'],
            'teknisi' => $data['teknisi'] ?? '',
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
        $report = $request->all();
        $report['tanggalPemeriksaan'] = $report['tanggalPemeriksaan'] ?? self::DEMO_TODAY;
        $report['dikirim'] = $report['dikirim'] ?? self::DEMO_TODAY;
        $report['status'] = 'menunggu';

        $pmSchedule->update([
            'report' => $report,
            'status' => 'menunggu-validasi',
        ]);

        return response()->json($pmSchedule->toBootstrapArray());
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

        $report = $pmSchedule->report;
        $report['catatanSupervisor'] = $data['catatan'] ?? '';
        $report['status'] = $data['approve'] ? 'disetujui' : 'ditolak';

        $pmSchedule->update([
            'report' => $report,
            'status' => $data['approve'] ? 'selesai' : 'ditolak',
        ]);

        ValidationHistory::create([
            'machine_id' => $pmSchedule->machine_id,
            'jenis' => $pmSchedule->jenis,
            'teknisi' => $report['pemeriksa'] ?? $pmSchedule->teknisi,
            'divalidasi_oleh' => auth()->check() ? auth()->user()->name : 'Supervisor',
            'tanggal' => self::DEMO_TODAY,
            'hasil' => $data['approve'] ? 'disetujui' : 'ditolak',
        ]);

        if ($data['approve']) {
            $downtime = 0;
            if (!empty($report['waktuMulai']) && !empty($report['waktuSelesai'])) {
                [$ah, $am] = array_map('intval', explode(':', $report['waktuMulai']));
                [$bh, $bm] = array_map('intval', explode(':', $report['waktuSelesai']));
                $downtime = ($bh * 60 + $bm) - ($ah * 60 + $am);
                if ($downtime < 0) {
                    $downtime += 24 * 60;
                }
            }

            MaintenanceHistory::create([
                'no_laporan' => 'PM-' . strtoupper($pmSchedule->id),
                'machine_id' => $pmSchedule->machine_id,
                'kategori' => ucfirst($report['kategori'] ?? 'mekanik'),
                'pekerjaan' => $pmSchedule->jenis,
                'pelaksana' => $report['pemeriksa'] ?? $pmSchedule->teknisi,
                'downtime_menit' => $downtime,
                'hasil' => 'Baik',
                'catatan' => $data['catatan'] ?? '',
                'tanggal' => self::DEMO_TODAY,
            ]);
        }

        return response()->json($pmSchedule->toBootstrapArray());
    }
}
