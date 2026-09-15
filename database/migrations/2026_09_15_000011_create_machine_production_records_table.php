<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_production_records', function (Blueprint $table) {
            $table->id();
            $table->string('machine_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('planned_minutes', 12, 2);
            $table->decimal('actual_output', 14, 2);
            $table->decimal('ideal_output', 14, 2);
            $table->decimal('good_output', 14, 2);
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines')->cascadeOnDelete();
            $table->index(
                ['machine_id', 'period_start', 'period_end'],
                'mpr_machine_period_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_production_records');
    }
};
