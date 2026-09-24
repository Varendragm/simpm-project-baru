<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PmSchedule extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'machine_id', 'teknisi_user_id', 'jenis', 'teknisi', 'tanggal', 'interval',
        'estimasi', 'prioritas', 'status', 'catatan', 'report',
    ];

    protected function casts(): array
    {
        return [
            'report' => 'array',
            'tanggal' => 'date:Y-m-d',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $schedule) {
            $technician = null;

            if ($schedule->teknisi_user_id) {
                $technician = User::whereKey($schedule->teknisi_user_id)
                    ->where('role', 'teknisi')
                    ->first();
            }

            if (!$technician && $schedule->teknisi) {
                $technician = User::where('role', 'teknisi')
                    ->where('name', $schedule->teknisi)
                    ->first();
            }

            // Demo/legacy data may contain an old technician name while only
            // one real technician account exists. Normalize it to that account.
            if (!$technician) {
                $technicians = User::where('role', 'teknisi')->orderBy('id')->get();
                if ($technicians->count() === 1) {
                    $technician = $technicians->first();
                }
            }

            if (!$technician) {
                throw ValidationException::withMessages([
                    'teknisiUserId' => 'Jadwal PM wajib memiliki teknisi yang valid.',
                ]);
            }

            $schedule->teknisi_user_id = $technician->id;
            $schedule->teknisi = $technician->name;
        });
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function teknisiUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_user_id');
    }

    public function toBootstrapArray(): array
    {
        $data = [
            'id' => $this->id,
            'machineId' => $this->machine_id,
            'teknisiUserId' => $this->teknisi_user_id,
            'jenis' => $this->jenis,
            'teknisi' => $this->teknisi,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'interval' => $this->interval,
            'estimasi' => $this->estimasi,
            'prioritas' => $this->prioritas,
            'status' => $this->status,
            'catatan' => $this->catatan,
        ];
        if ($this->report) {
            $data['report'] = $this->report;
        }
        return $data;
    }
}
