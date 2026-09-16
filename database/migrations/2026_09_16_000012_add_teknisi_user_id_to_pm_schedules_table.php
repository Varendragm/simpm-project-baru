<?php

use App\Models\User;
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

        // Backfill existing demo/legacy schedules that were assigned by name.
        foreach (\DB::table('pm_schedules')->whereNotNull('teknisi')->get(['id', 'teknisi']) as $schedule) {
            $userId = User::where('role', 'teknisi')->where('name', $schedule->teknisi)->value('id');
            if ($userId) {
                \DB::table('pm_schedules')->where('id', $schedule->id)->update(['teknisi_user_id' => $userId]);
            }
        }
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
