<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah seluruh pemakaian listrik negatif (karena stand mundur / KWH error) menjadi 0.00 (Opsi B)
     */
    public function up(): void
    {
        DB::table('tenant_energy_readings')
            ->where('pemakaian', '<', 0)
            ->update([
                'pemakaian'       => 0.00,
                'is_special_case' => true,
                'catatan'         => DB::raw("COALESCE(catatan, 'KWH ERROR (Stand Mundur/Prorate Manual)')"),
                'keterangan'      => DB::raw("COALESCE(keterangan, 'KWH ERROR')"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu di-reverse karena pemakaian negatif adalah anomali
    }
};
