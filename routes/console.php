<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:fix-admin-verifications', function () {
    $this->info('Memulai verifikasi otomatis untuk data input admin dan backdate...');

    $tables = [
        'meter_readings',
        'public_area_meter_readings',
        'public_area_lvsb_readings',
        'public_area_mvsb_readings',
    ];

    $defaultAdminId = Illuminate\Support\Facades\DB::table('users')
        ->whereIn('role', ['admin', 'superadmin'])
        ->value('id') ?? 1;

    $totalUpdated = 0;

    foreach ($tables as $table) {
        $nonTeknisi = Illuminate\Support\Facades\DB::statement("
            UPDATE {$table} t
            LEFT JOIN users u ON t.user_id = u.id
            SET 
                t.verified_at = COALESCE(t.created_at, NOW()),
                t.verified_by = COALESCE(t.user_id, {$defaultAdminId})
            WHERE t.verified_at IS NULL
              AND (u.role IS NULL OR u.role != 'teknisi')
        ");

        $backdate = Illuminate\Support\Facades\DB::statement("
            UPDATE {$table}
            SET 
                verified_at = COALESCE(created_at, NOW()),
                verified_by = COALESCE(user_id, {$defaultAdminId})
            WHERE verified_at IS NULL
              AND tanggal < DATE(created_at)
        ");

        $this->line("Tabel {$table}: Selesai diperbarui.");
    }

    $this->info('Verifikasi otomatis berhasil diselesaikan.');
})->purpose('Otomatisasi verifikasi untuk pencatatan logsheet yang diinput admin atau backdate');

// ── Heartbeat Pemantauan Cron Job Server ──
Illuminate\Support\Facades\Schedule::call(function () {
    Illuminate\Support\Facades\Cache::forever('last_scheduler_run_at', now('Asia/Jakarta')->toDateTimeString());
})->everyMinute()->name('scheduler-heartbeat');

// ── Jadwal Otomatisasi Notifikasi WhatsApp (Sesuai Pengaturan Super Admin) ──
$whatsappScheduledTime = '18:00';
try {
    if (Illuminate\Support\Facades\Schema::hasTable('whatsapp_notification_settings')) {
        $setting = \App\Models\WhatsappSetting::first();
        if ($setting && ! empty($setting->scheduled_time)) {
            $whatsappScheduledTime = $setting->scheduled_time;
        }
    }
} catch (\Throwable $e) {
    // Fallback default
}

Illuminate\Support\Facades\Schedule::command('whatsapp:send-daily-summary')
    ->dailyAt($whatsappScheduledTime)
    ->timezone('Asia/Jakarta')
    ->before(function () {
        Illuminate\Support\Facades\Log::info('[Scheduler] Memulai eksekusi otomatis harian whatsapp:send-daily-summary');
    })
    ->after(function () {
        Illuminate\Support\Facades\Log::info('[Scheduler] Selesai eksekusi otomatis harian whatsapp:send-daily-summary');
    })
    ->description("Kirim executive summary dan reminder logsheet ke WhatsApp via Fonnte setiap hari pukul {$whatsappScheduledTime} WIB");



