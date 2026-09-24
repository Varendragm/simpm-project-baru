<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_histories', function (Blueprint $table) {
            $table->string('jenis_maintenance', 20)->nullable()->after('tanggal');
            $table->string('downtime_type', 20)->nullable()->after('jenis_maintenance');
            $table->index(['machine_id', 'jenis_maintenance'], 'maintenance_machine_type_idx');
            $table->index(['machine_id', 'tanggal'], 'maintenance_machine_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_histories', function (Blueprint $table) {
            $table->dropIndex('maintenance_machine_type_idx');
            $table->dropIndex('maintenance_machine_date_idx');
            $table->dropColumn(['jenis_maintenance', 'downtime_type']);
        });
    }
};
