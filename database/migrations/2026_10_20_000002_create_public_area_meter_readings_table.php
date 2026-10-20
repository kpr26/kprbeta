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
        Schema::create('public_area_meter_readings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('category', 30); // 'water' atau 'electricity'
            $table->string('meter_key', 50); // 'cooling_tower', 'toilet_sky_lobby', 'pantry_office', 'kwh_public_area'
            $table->string('meter_name', 100);
            $table->string('unit', 20)->default('m³'); // 'm³' atau 'kWh'
            $table->decimal('stand_meter', 14, 2);
            $table->string('foto')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu meter per kategori hanya boleh memiliki 1 pencatatan pada tanggal yang sama
            $table->unique(['tanggal', 'category', 'meter_key'], 'uniq_pa_reading_date_cat_meter');
            $table->index(['category', 'meter_key', 'tanggal'], 'idx_pa_reading_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_area_meter_readings');
    }
};
