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
        // 1. Modifikasi tabel tenants
        if (! Schema::hasColumn('tenants', 'daya_listrik')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->decimal('daya_listrik', 8, 2)->default(0.00)->after('daya_listrik_va');
                $table->boolean('has_ev_charger')->default(false)->after('daya_listrik');
                $table->decimal('daya_ev', 8, 2)->nullable()->after('has_ev_charger');
                $table->string('nomor_meter_ev', 100)->nullable()->after('daya_ev');
            });
        }

        // 2. Modifikasi tabel tenant_energy_readings
        if (! Schema::hasColumn('tenant_energy_readings', 'meter_type')) {
            Schema::table('tenant_energy_readings', function (Blueprint $table) {
                $table->string('meter_type', 20)->default('regular')->after('tenant_id'); // 'regular' atau 'ev'
                $table->decimal('daya', 8, 2)->nullable()->after('tanggal_catat');
                $table->decimal('tarif_per_kwh', 10, 2)->default(1699.53)->after('pemakaian');
            });
        }

        // 3. Tambahkan index terpisah untuk tenant_id agar foreign key MySQL tidak komplain saat drop unique
        Schema::table('tenant_energy_readings', function (Blueprint $table) {
            $table->index('tenant_id', 'tenant_energy_readings_tenant_id_idx');
        });

        // 4. Update unique index pada tenant_energy_readings
        Schema::table('tenant_energy_readings', function (Blueprint $table) {
            $table->dropUnique('tenant_energy_periode_unique');
            $table->unique(['tenant_id', 'periode_tahun', 'periode_bulan', 'meter_type'], 'tenant_energy_periode_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_energy_readings', function (Blueprint $table) {
            $table->dropUnique('tenant_energy_periode_type_unique');
            $table->unique(['tenant_id', 'periode_tahun', 'periode_bulan'], 'tenant_energy_periode_unique');
            $table->dropIndex('tenant_energy_readings_tenant_id_idx');
            $table->dropColumn(['meter_type', 'daya', 'tarif_per_kwh']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['daya_listrik', 'has_ev_charger', 'daya_ev', 'nomor_meter_ev']);
        });
    }
};
