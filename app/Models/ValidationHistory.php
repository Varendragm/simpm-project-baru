<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationHistory extends Model
{
    protected $fillable = [
        'machine_id', 'jenis', 'teknisi', 'divalidasi_oleh', 'tanggal', 'hasil',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date:Y-m-d'];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function toBootstrapArray(): array
    {
        return [
            'machineId' => $this->machine_id,
            'jenis' => $this->jenis,
            'teknisi' => $this->teknisi,
            'divalidasiOleh' => $this->divalidasi_oleh,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'hasil' => $this->hasil,
        ];
    }
}
