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
        // 1. Table Tenants (Master Data Tenant)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tenant');
            $table->string('kode_tenant', 50)->nullable()->unique();
            $table->string('lantai', 50); // e.g. "Lantai 1", "Lantai 2", "LG", "Sky Lobby 11"
            $table->string('nomor_unit', 50)->nullable(); // e.g. "Unit 101", "Zone A"
            $table->string('nomor_meter_listrik', 100)->nullable();
            $table->string('nomor_meter_air', 100)->nullable();
            $table->decimal('faktor_kali_listrik', 10, 4)->default(1.0000);
            $table->decimal('faktor_kali_air', 10, 4)->default(1.0000);
            $table->unsignedInteger('daya_listrik_va')->nullable(); // e.g. 6600, 11000, 33000
            $table->string('kontak_person')->nullable();
            $table->string('no_telepon', 50)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 2. Table Tenant Energy Readings (Penggunaan Listrik Bulanan)
        Schema::create('tenant_energy_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->unsignedTinyInteger('periode_bulan'); // 1-12
            $table->unsignedSmallInteger('periode_tahun'); // e.g. 2026
            $table->date('tanggal_catat');
            $table->decimal('stand_awal', 14, 2)->default(0.00);
            $table->decimal('stand_akhir', 14, 2);
            $table->decimal('pemakaian', 14, 2);
            $table->string('foto_meter')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // 1 tenant hanya 1 record per bulan dan tahun
            $table->unique(['tenant_id', 'periode_tahun', 'periode_bulan'], 'tenant_energy_periode_unique');
        });

        // 3. Table Tenant Water Readings (Penggunaan Air Bulanan)
        Schema::create('tenant_water_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->unsignedTinyInteger('periode_bulan'); // 1-12
            $table->unsignedSmallInteger('periode_tahun'); // e.g. 2026
            $table->date('tanggal_catat');
            $table->decimal('stand_awal', 14, 2)->default(0.00);
            $table->decimal('stand_akhir', 14, 2);
            $table->decimal('pemakaian', 14, 2);
            $table->string('foto_meter')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // 1 tenant hanya 1 record per bulan dan tahun
            $table->unique(['tenant_id', 'periode_tahun', 'periode_bulan'], 'tenant_water_periode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_water_readings');
        Schema::dropIfExists('tenant_energy_readings');
        Schema::dropIfExists('tenants');
    }
};
