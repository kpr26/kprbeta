<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'meter_readings',
            'public_area_meter_readings',
            'public_area_lvsb_readings',
            'public_area_mvsb_readings',
        ];

        $defaultAdminId = DB::table('users')
            ->whereIn('role', ['admin', 'superadmin'])
            ->value('id') ?? 1;

        foreach ($tables as $table) {
            // 1. Data yang diinput oleh user non-teknisi (admin, superadmin, eng_admin, eng_spv, dll)
            DB::statement("
                UPDATE {$table} t
                LEFT JOIN users u ON t.user_id = u.id
                SET 
                    t.verified_at = COALESCE(t.created_at, NOW()),
                    t.verified_by = COALESCE(t.user_id, {$defaultAdminId})
                WHERE t.verified_at IS NULL
                  AND (u.role IS NULL OR u.role != 'teknisi')
            ");

            // 2. Data yang diinput backdate (tanggal < DATE(created_at))
            DB::statement("
                UPDATE {$table}
                SET 
                    verified_at = COALESCE(created_at, NOW()),
                    verified_by = COALESCE(user_id, {$defaultAdminId})
                WHERE verified_at IS NULL
                  AND tanggal < DATE(created_at)
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
