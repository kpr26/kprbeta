<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_area_mvsb_readings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->decimal('voltage_20kv', 10, 2)->nullable(); // Voltage 20kV (Volt)
            $table->decimal('current', 10, 2)->nullable();      // Current (Ampere)
            $table->string('foto')->nullable();                 // Foto fisik MVSB Incoming From PLN
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_area_mvsb_readings');
    }
};
