<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('role', 20); // supervisor | teknisi | manajer
            $table->string('sub_label')->nullable();  // cth. "Supervisor · Produksi"
            $table->string('avatar', 4)->nullable();   // inisial, cth. "SH"
            $table->string('phone', 30)->nullable();
            $table->string('department')->nullable();  // Bagian / Jabatan / Keahlian
            $table->unsignedSmallInteger('joined_year')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
