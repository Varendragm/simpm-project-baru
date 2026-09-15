<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->string('id')->primary();   // cth. st-gilingan
            $table->string('code', 20);
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif | nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
