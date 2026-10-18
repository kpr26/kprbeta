<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Role pengguna: admin / teknisi
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('teknisi')->after('username');
            });
        }

        // Logsheet harian: 1 gedung, 1 kWh meter (LWBP & WBP), 1 water meter
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();           // hanya 1x catat per hari
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('stand_lwbp', 14, 2);        // stand kWh LWBP
            $table->decimal('stand_wbp', 14, 2);         // stand kWh WBP
            $table->decimal('stand_air', 14, 2);         // stand air (m3)
            $table->string('foto_kwh');                  // bukti foto kWh meter
            $table->string('foto_air');                  // bukti foto water meter
            $table->text('catatan')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
        }
    }
};
