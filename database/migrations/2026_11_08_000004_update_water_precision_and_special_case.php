<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenant_water_readings', function (Blueprint $table) {
            $table->decimal('stand_awal', 14, 3)->default(0.000)->change();
            $table->decimal('stand_akhir', 14, 3)->change();
            $table->decimal('pemakaian', 14, 3)->change();
            if (!Schema::hasColumn('tenant_water_readings', 'is_special_case')) {
                $table->boolean('is_special_case')->default(false)->after('pemakaian');
            }
            if (!Schema::hasColumn('tenant_water_readings', 'keterangan')) {
                $table->string('keterangan', 255)->nullable()->after('catatan');
            }
        });

        Schema::table('tenant_energy_readings', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_energy_readings', 'is_special_case')) {
                $table->boolean('is_special_case')->default(false)->after('pemakaian');
            }
            if (!Schema::hasColumn('tenant_energy_readings', 'keterangan')) {
                $table->string('keterangan', 255)->nullable()->after('catatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_water_readings', function (Blueprint $table) {
            $table->decimal('stand_awal', 14, 2)->default(0.00)->change();
            $table->decimal('stand_akhir', 14, 2)->change();
            $table->decimal('pemakaian', 14, 2)->change();
            if (Schema::hasColumn('tenant_water_readings', 'is_special_case')) {
                $table->dropColumn('is_special_case');
            }
            if (Schema::hasColumn('tenant_water_readings', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });

        Schema::table('tenant_energy_readings', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_energy_readings', 'is_special_case')) {
                $table->dropColumn('is_special_case');
            }
            if (Schema::hasColumn('tenant_energy_readings', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};
