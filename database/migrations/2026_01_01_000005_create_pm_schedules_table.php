<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_schedules', function (Blueprint $table) {
            $table->string('id')->primary(); // cth. pm-001
            $table->string('machine_id');
            $table->string('jenis');
            $table->string('teknisi');
            $table->date('tanggal');
            $table->string('interval', 30)->nullable();     // Harian | Mingguan | Bulanan | Tidak berulang
            $table->string('estimasi', 30)->nullable();
            $table->string('prioritas', 20)->default('sedang'); // rendah | sedang | tinggi | kritis
            $table->string('status', 30)->default('terjadwal'); // terjadwal|menunggu-validasi|selesai|ditolak
            $table->text('catatan')->nullable();
            $table->json('report')->nullable(); // laporan hasil pemeriksaan dari Teknisi (lihat kontrak di app.js)
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_schedules');
    }
};
