<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->string('id')->primary();  // cth. m-g01
            $table->string('station_id');
            $table->string('code', 20);
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('status', 20)->default('normal'); // normal | perhatian | perbaikan
            $table->string('capacity')->nullable();
            $table->string('year', 10)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('station_id')->references('id')->on('stations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
