<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pm_schedules', function (Blueprint $table) {
            $table->foreignId('teknisi_user_id')->nullable()->after('teknisi')->constrained('users')->nullOnDelete();
            $table->index('teknisi_user_id', 'pm_schedules_teknisi_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pm_schedules', function (Blueprint $table) {
            $table->dropForeign(['teknisi_user_id']);
            $table->dropIndex('pm_schedules_teknisi_user_idx');
            $table->dropColumn('teknisi_user_id');
        });
    }
};
