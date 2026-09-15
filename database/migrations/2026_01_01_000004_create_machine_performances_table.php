<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_performances', function (Blueprint $table) {
            $table->string('machine_id')->primary();
            $table->float('oee')->default(0);
            $table->float('availability')->default(0);
            $table->float('reliability')->default(0);
            $table->float('mttr')->default(0);
            $table->float('mtbf')->default(0);
            $table->float('downtime_bulan_ini')->default(0);
            $table->unsignedInteger('perbaikan_bulan_ini')->default(0);
            $table->json('trend_oee')->nullable(); // array 6 minggu terakhir
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_performances');
    }
};
