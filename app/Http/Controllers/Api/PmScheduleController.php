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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        // Mendukung pengiriman JSON lama maupun multipart/form-data untuk lampiran foto.
        $reportData = $request->input('report');
        if (is_string($reportData)) {
            $reportData = json_decode($reportData, true);
            if (!is_array($reportData)) {
                return response()->json(['message' => 'Format laporan pemeriksaan tidak valid.'], 422);
            }
        } else {
            $reportData = $request->all();
        }

        $validator = Validator::make($reportData, [
            'tanggalPemeriksaan' => ['nullable', 'date'],
            'dikirim' => ['nullable', 'date'],
            'pemeriksa' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'in:mekanik,elektrik,instrumentasi'],
            'prioritas' => ['required', 'string', 'in:rendah,sedang,tinggi,kritis'],
            'waktuMulai' => ['nullable', 'date_format:H:i'],
            'waktuSelesai' => ['nullable', 'date_format:H:i'],
            'parameter' => ['nullable', 'array'],
            'checklist' => ['nullable', 'array'],
            'deskripsi' => ['nullable', 'string'],
            'tindakan' => ['nullable', 'string'],
            'spareparts' => ['nullable', 'array'],
            'rekomendasi' => ['nullable', 'string'],
            'jadwalBerikutnya' => ['nullable', 'date'],
            'catatanSupervisor' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $report = $validator->validated();
        $report['tanggalPemeriksaan'] = $report['tanggalPemeriksaan'] ?? Carbon::today()->toDateString();
        $report['dikirim'] = Carbon::today()->toDateString();
        $report['pemeriksa'] = auth()->user()->name;
        $report['status'] = 'menunggu';

        // Jika laporan ditolak lalu dikirim ulang tanpa foto baru, foto lama tetap dipertahankan.
        $oldReport = $pmSchedule->report ?? [];
        $report['catatanSupervisor'] = '';
        if (!empty($oldReport['fotoUrl']) && !$request->hasFile('foto')) {
            $report['fotoUrl'] = $oldReport['fotoUrl'];
        }

        if ($request->hasFile('foto')) {
            $request->validate([
                'foto' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
            $path = $request->file('foto')->store('pm-photos', 'public');
            $report['fotoUrl'] = Storage::disk('public')->url($path);
        }

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
            'pekerjaanTambahan' => ['nullable', 'array'],
            'pekerjaanTambahan.enabled' => ['nullable', 'boolean'],
            'pekerjaanTambahan.jenis' => ['nullable', 'string', 'max:150'],
            'pekerjaanTambahan.teknisiUserId' => ['nullable', 'integer', 'exists:users,id'],
            'pekerjaanTambahan.tanggal' => ['nullable', 'date'],
            'pekerjaanTambahan.estimasi' => ['nullable', 'string', 'max:30'],
            'pekerjaanTambahan.prioritas' => ['nullable', 'string', 'in:rendah,sedang,tinggi,kritis'],
        ]);

        // Validasi hanya boleh dilakukan setelah Teknisi benar-benar mengirim laporan.
        // Jadwal baru/terjadwal tidak boleh langsung dianggap terkonfirmasi.
        if ($pmSchedule->status === 'terjadwal') {
            return response()->json(['message' => 'Jadwal masih terjadwal. Menunggu Teknisi menyelesaikan pemeriksaan dan mengirim laporan.'], 422);
        }

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

                // Jadwal PM berikutnya dibuat OTOMATIS berdasarkan interval
                // yang sudah ditetapkan saat jadwal PM dibuat. Supervisor tidak
                // perlu memilih interval/tanggal/teknisi lagi saat validasi.
                $nextDate = $this->calculateNextPmDate($pmSchedule->tanggal, $pmSchedule->interval);

                if ($nextDate) {
                    $nextTechnicianId = $pmSchedule->teknisi_user_id;
                    $nextTechnicianName = $pmSchedule->teknisi;

                    // Jangan membuat jadwal ganda bila jadwal yang sama sudah ada.
                    $existingNextSchedule = PmSchedule::query()
                        ->where('machine_id', $pmSchedule->machine_id)
                        ->where('jenis', $pmSchedule->jenis)
                        ->whereDate('tanggal', $nextDate)
                        ->where('interval', $pmSchedule->interval)
                        ->where(function ($query) use ($nextTechnicianId, $nextTechnicianName) {
                            if ($nextTechnicianId) {
                                $query->where('teknisi_user_id', $nextTechnicianId);
                            } else {
                                $query->where('teknisi', $nextTechnicianName);
                            }
                        })
                        ->first();

                    if (!$existingNextSchedule) {
                        PmSchedule::create([
                            'id' => 'pm-' . substr((string) Str::uuid(), 0, 8),
                            'machine_id' => $pmSchedule->machine_id,
                            'teknisi_user_id' => $nextTechnicianId,
                            'jenis' => $pmSchedule->jenis,
                            'teknisi' => $nextTechnicianName,
                            'tanggal' => $nextDate,
                            'interval' => $pmSchedule->interval,
                            'estimasi' => $pmSchedule->estimasi,
                            'prioritas' => $pmSchedule->prioritas,
                            'status' => 'terjadwal',
                            'catatan' => 'Dijadwalkan otomatis setelah validasi PM ' . $pmSchedule->id,
                        ]);
                    }
                }

                // Pekerjaan tambahan adalah PM satu kali yang berdiri sendiri.
                // Pekerjaan ini tidak mengubah/mengganggu PM rutin berikutnya.
                $additional = $data['pekerjaanTambahan'] ?? [];
                if (($additional['enabled'] ?? false) === true) {
                    if (empty($additional['jenis']) || empty($additional['teknisiUserId']) || empty($additional['tanggal'])) {
                        throw new \RuntimeException('Pekerjaan tambahan harus memiliki jenis pekerjaan, teknisi, dan tanggal.');
                    }

                    $additionalTechnician = User::whereKey($additional['teknisiUserId'])
                        ->where('role', 'teknisi')
                        ->first();

                    if (!$additionalTechnician) {
                        throw new \RuntimeException('Teknisi untuk pekerjaan tambahan tidak valid.');
                    }

                    $additionalDate = Carbon::parse($additional['tanggal'])->toDateString();
                    $additionalPriority = $additional['prioritas'] ?? 'sedang';
                    $additionalJenis = trim($additional['jenis']);

                    $existingAdditional = PmSchedule::query()
                        ->where('machine_id', $pmSchedule->machine_id)
                        ->where('jenis', $additionalJenis)
                        ->whereDate('tanggal', $additionalDate)
                        ->where('interval', 'Tidak berulang')
                        ->where('teknisi_user_id', $additionalTechnician->id)
                        ->first();

                    if (!$existingAdditional) {
                        PmSchedule::create([
                            'id' => 'pm-' . substr((string) Str::uuid(), 0, 8),
                            'machine_id' => $pmSchedule->machine_id,
                            'teknisi_user_id' => $additionalTechnician->id,
                            'jenis' => $additionalJenis,
                            'teknisi' => $additionalTechnician->name,
                            'tanggal' => $additionalDate,
                            'interval' => 'Tidak berulang',
                            'estimasi' => $additional['estimasi'] ?? $pmSchedule->estimasi,
                            'prioritas' => $additionalPriority,
                            'status' => 'terjadwal',
                            'catatan' => 'Pekerjaan tambahan dari PM ' . $pmSchedule->id . '. Tidak berulang.',
                        ]);
                    }
                }
            }

            return $pmSchedule->fresh();
        });

        return response()->json($pmSchedule->toBootstrapArray());
    }

    private function calculateNextPmDate($currentDate, ?string $interval): ?string
    {
        if (!$currentDate || !$interval || $interval === 'Tidak berulang') {
            return null;
        }

        $date = Carbon::parse($currentDate);

        return match ($interval) {
            'Harian' => $date->addDay()->toDateString(),
            'Mingguan' => $date->addWeek()->toDateString(),
            'Bulanan' => $date->addMonthNoOverflow()->toDateString(),
            '3 Bulanan' => $date->addMonthsNoOverflow(3)->toDateString(),
            '6 Bulanan' => $date->addMonthsNoOverflow(6)->toDateString(),
            'Tahunan' => $date->addYearNoOverflow()->toDateString(),
            default => null,
        };
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
