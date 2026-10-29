<?php

namespace App\Console\Commands;

use App\Models\WhatsappSetting;
use App\Services\ExecutiveSummaryService;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailySummaryWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send-daily-summary
                            {--date= : Tanggal evaluasi logsheet (format YYYY-MM-DD, default hari ini)}
                            {--target= : Override nomor WhatsApp atau ID grup tujuan}
                            {--dry-run : Tampilkan pesan di console tanpa mengirim ke Fonnte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim otomatis executive summary dan reminder logsheet ke WhatsApp Grup via Fonnte';

    /**
     * Execute the console command.
     */
    public function handle(ExecutiveSummaryService $summaryService, FonnteService $fonnteService): int
    {
        $dateInput = $this->option('date');
        $date = $dateInput ? Carbon::parse($dateInput)->timezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');

        $setting = WhatsappSetting::getSetting();
        $isDryRun = (bool) $this->option('dry-run');

        // Jika dipicu secara terjadwal otomatis namun status non-aktif
        if (! $isDryRun && ! $this->option('target') && ! $setting->is_active) {
            $this->warn('Pengiriman notifikasi otomatis harian sedang DINONAKTIFKAN dalam pengaturan sistem.');
            return self::SUCCESS;
        }

        // Tentukan target tujuan
        if ($this->option('target')) {
            $targets = [$this->option('target')];
        } else {
            $targets = $setting->getAllActiveTargets();
            if (empty($targets) && $fonnteService->getDefaultTarget()) {
                $targets = [$fonnteService->getDefaultTarget()];
            }
        }

        $this->info("Menyiapkan ringkasan logsheet untuk tanggal: {$date->format('Y-m-d')}...");

        // Format pesan WhatsApp
        $message = $summaryService->formatWhatsAppMessage($date);

        if ($isDryRun) {
            $this->warn("\n[MODE DRY-RUN AKTIF] Pesan TIDAK dikirimkan ke Fonnte API.");
            $this->line("Target Pengiriman: " . (! empty($targets) ? implode(', ', $targets) : '(Belum dikonfigurasi)'));
            $this->line("━━━━━━━━━━━━━━━━ ISI PESAN WHATSAPP ━━━━━━━━━━━━━━━━");
            $this->line($message);
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
            $this->info("Pemeriksaan pesan selesai. Tidak ada panggilan API keluar.");
            return self::SUCCESS;
        }

        // Cek ketersediaan token (dari database atau .env)
        $activeToken = $setting->getFonnteToken() ?: $fonnteService->getToken();
        if (empty($activeToken)) {
            $this->error("ERROR: Token Fonnte belum dikonfigurasi di pengaturan aplikasi atau .env!");
            $this->warn("Silakan atur token di menu Pengaturan WhatsApp atau variabel FONNTE_TOKEN di .env.");
            return self::FAILURE;
        }

        // Cek target grup / nomor
        if (empty($targets)) {
            $this->error("ERROR: Tidak ada target nomor atau grup WhatsApp yang ditentukan!");
            \Illuminate\Support\Facades\Log::warning('[SendDailySummaryWhatsApp] Eksekusi dibatalkan: Tidak ada target aktif yang ditentukan.');
            return self::FAILURE;
        }

        \Illuminate\Support\Facades\Log::info("[SendDailySummaryWhatsApp] Memulai pengiriman ringkasan harian (" . count($targets) . " target): " . implode(', ', $targets));

        $allSuccess = true;
        $successCount = 0;
        $failCount = 0;

        foreach ($targets as $target) {
            $this->info("Mengirimkan notifikasi ke: {$target} via Fonnte...");
            $result = $fonnteService->sendMessage($target, $message);

            if ($result['success']) {
                $successCount++;
                $this->info("✔ Berhasil dikirim ke {$target}!");
            } else {
                $failCount++;
                $allSuccess = false;
                $this->error("✖ Gagal dikirim ke {$target}: " . ($result['message'] ?? 'Terjadi kesalahan Fonnte.'));
            }
        }

        \Illuminate\Support\Facades\Log::info("[SendDailySummaryWhatsApp] Selesai mengirim. Sukses: {$successCount}, Gagal: {$failCount}");

        return $allSuccess ? self::SUCCESS : self::FAILURE;
    }
}
