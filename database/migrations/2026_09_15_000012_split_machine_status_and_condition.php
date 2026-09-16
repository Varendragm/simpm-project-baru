<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->string('kondisi', 20)->default('perhatian')->after('status');
        });

        // Existing demo data used status for operational condition.
        // Preserve the machines as active master data and let the application
        // calculate their condition from operational/performance data.
        DB::table('machines')->update([
            'status' => 'aktif',
        ]);
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('kondisi');
        });
    }
};
