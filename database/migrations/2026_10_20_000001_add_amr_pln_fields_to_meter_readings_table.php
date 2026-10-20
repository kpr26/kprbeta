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
            if (! Schema::hasColumn('meter_readings', 'stand_bp')) {
                $table->decimal('stand_bp', 14, 2)->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('meter_readings', 'stand_lbp1')) {
                $table->decimal('stand_lbp1', 14, 2)->nullable()->after('stand_bp');
            }
            if (! Schema::hasColumn('meter_readings', 'stand_lbp2')) {
                $table->decimal('stand_lbp2', 14, 2)->nullable()->after('stand_lbp1');
            }
            if (! Schema::hasColumn('meter_readings', 'stand_total')) {
                $table->decimal('stand_total', 14, 2)->nullable()->after('stand_lbp2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $columns = [];
            foreach (['stand_bp', 'stand_lbp1', 'stand_lbp2', 'stand_total'] as $col) {
                if (Schema::hasColumn('meter_readings', $col)) {
                    $columns[] = $col;
                }
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
