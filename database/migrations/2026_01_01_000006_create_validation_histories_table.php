<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_histories', function (Blueprint $table) {
            $table->id();
            $table->string('machine_id');
            $table->string('jenis');
            $table->string('teknisi');
            $table->string('divalidasi_oleh');
            $table->date('tanggal');
            $table->string('hasil', 20); // disetujui | ditolak
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_histories');
    }
};
