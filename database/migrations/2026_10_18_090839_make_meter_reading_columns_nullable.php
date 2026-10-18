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
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->decimal('stand_lwbp', 14, 2)->nullable()->change();
            $table->decimal('stand_wbp', 14, 2)->nullable()->change();
            $table->decimal('stand_air', 14, 2)->nullable()->change();
            $table->string('foto_kwh')->nullable()->change();
            $table->string('foto_air')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->decimal('stand_lwbp', 14, 2)->nullable(false)->change();
            $table->decimal('stand_wbp', 14, 2)->nullable(false)->change();
            $table->decimal('stand_air', 14, 2)->nullable(false)->change();
            $table->string('foto_kwh')->nullable(false)->change();
            $table->string('foto_air')->nullable(false)->change();
        });
    }
};
