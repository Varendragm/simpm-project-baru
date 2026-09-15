<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_histories', function (Blueprint $table) {
            $table->id();
            $table->string('no_laporan')->unique(); // cth. BR-2026-014
            $table->string('machine_id');
            $table->string('kategori', 30); // Mekanik | Elektrik | Instrumentasi
            $table->string('pekerjaan');
            $table->string('pelaksana');
            $table->unsignedInteger('downtime_menit')->default(0);
            $table->string('hasil', 20)->default('Baik');
            $table->text('catatan')->nullable();
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_histories');
    }
};
