<?php

namespace App\Services;

class MachineStatusCalculator
{
    /**
     * Determine the current machine condition from calculated performance.
     * The machine status is not manually entered as the source of truth.
     *
     * Current demo rules:
     * - perbaikan: OEE < 65% or Availability < 70%
     * - perhatian: OEE < 85% or Availability < 85%
     * - normal: otherwise
     *
     * When performance data is unavailable, use "perhatian" so the system
     * does not label an unmeasured machine as normal.
     */
    public function determine(array $performance): string
    {
        $oee = $this->numeric($performance['oee'] ?? null);
        $availability = $this->numeric($performance['availability'] ?? null);

        if ($oee === null || $availability === null) {
            return 'perhatian';
        }

        if ($oee < 65 || $availability < 70) {
            return 'perbaikan';
        }

        if ($oee < 85 || $availability < 85) {
            return 'perhatian';
        }

        return 'normal';
    }

    public function reason(array $performance): string
    {
        $oee = $this->numeric($performance['oee'] ?? null);
        $availability = $this->numeric($performance['availability'] ?? null);

        if ($oee === null || $availability === null) {
            return 'Data performa belum tersedia lengkap.';
        }

        if ($oee < 65 || $availability < 70) {
            return 'OEE atau Availability berada di bawah batas kondisi perbaikan.';
        }

        if ($oee < 85 || $availability < 85) {
            return 'OEE atau Availability berada di bawah batas kondisi normal.';
        }

        return 'OEE dan Availability memenuhi batas kondisi normal.';
    }

    private function numeric(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
