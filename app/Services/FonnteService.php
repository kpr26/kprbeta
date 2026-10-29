<?php

namespace App\Services;

use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected ?string $token;
    protected ?string $targetGroup;
    protected string $url;
    protected string $countryCode;

    public function __construct()
    {
        $this->token       = config('services.fonnte.token');
        $this->targetGroup = config('services.fonnte.target_group');
        $this->url         = config('services.fonnte.url', 'https://api.fonnte.com/send');
        $this->countryCode = config('services.fonnte.country_code', '62');

        // Fallback ke token di database jika belum terbaca dari config
        if (empty($this->token)) {
            try {
                $setting = WhatsappSetting::first();
                if ($setting && ! empty($setting->fonnte_token)) {
                    $this->token = trim($setting->fonnte_token);
                }
            } catch (\Throwable $e) {
                // Ignore during early boot/migration
            }
        }
    }

    /**
     * Memeriksa apakah konfigurasi token dan target grup sudah terisi.
     */
    public function isConfigured(): bool
    {
        $token = $this->getToken();
        return ! empty($token);
    }

    /**
     * Mengambil token aktif
     */
    public function getToken(): ?string
    {
        if (! empty($this->token)) {
            return $this->token;
        }

        try {
            $setting = WhatsappSetting::getSetting();
            return $setting->getFonnteToken();
        } catch (\Throwable $e) {
            return config('services.fonnte.token');
        }
    }

    /**
     * Mengambil target grup default dari konfigurasi.
     */
    public function getDefaultTarget(): ?string
    {
        return $this->targetGroup;
    }

    /**
     * Mengirim pesan teks ke WhatsApp melalui API Fonnte.
     *
     * @param string|null $target Nomor HP atau ID Grup WhatsApp (contoh: 120363024823812933@g.us)
     * @param string $message Isi pesan (mendukung format WhatsApp *bold*, _italic_, emoji, \n)
     * @return array
     */
    public function sendMessage(?string $target, string $message): array
    {
        $target = $target ?: $this->targetGroup;
        $activeToken = $this->getToken();

        if (empty($activeToken)) {
            $errorMsg = 'Fonnte token belum dikonfigurasi di pengaturan aplikasi atau .env (FONNTE_TOKEN).';
            Log::warning('[FonnteService] ' . $errorMsg);
            return [
                'success'  => false,
                'message'  => $errorMsg,
                'response' => null,
                'error'    => 'TOKEN_MISSING',
            ];
        }

        if (empty($target)) {
            $errorMsg = 'Target nomor atau grup WhatsApp tujuan tidak boleh kosong.';
            Log::warning('[FonnteService] ' . $errorMsg);
            return [
                'success'  => false,
                'message'  => $errorMsg,
                'response' => null,
                'error'    => 'TARGET_MISSING',
            ];
        }

        try {
            // Fonnte API documentation:
            // POST https://api.fonnte.com/send
            // Header: Authorization: $token
            // Body: target, message, countryCode
            $response = Http::withHeaders([
                'Authorization' => $activeToken,
            ])->timeout(20)->post($this->url, [
                'target'      => $target,
                'message'     => $message,
                'countryCode' => $this->countryCode,
            ]);

            $json = $response->json();

            if ($response->successful() && ! empty($json['status'])) {
                Log::info("[FonnteService] Pesan WhatsApp berhasil dikirim ke: {$target}", [
                    'target'   => $target,
                    'response' => $json,
                ]);

                return [
                    'success'  => true,
                    'message'  => 'Pesan berhasil dikirim via Fonnte.',
                    'response' => $json,
                    'error'    => null,
                ];
            }

            // Gagal dari sisi API Fonnte (misal: token expired, invalid target, kuota habis)
            $reason = $json['reason'] ?? $json['detail'] ?? ($response->body() ?: 'Gagal merespons dari Fonnte.');
            Log::error("[FonnteService] API Fonnte merespons error saat mengirim ke: {$target}", [
                'target'   => $target,
                'reason'   => $reason,
                'response' => $json,
            ]);

            return [
                'success'  => false,
                'message'  => 'Gagal mengirim pesan: ' . $reason,
                'response' => $json,
                'error'    => $reason,
            ];
        } catch (\Throwable $e) {
            Log::error("[FonnteService] Exception saat mengirim pesan ke: {$target}. Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success'  => false,
                'message'  => 'Terjadi kesalahan jaringan/server: ' . $e->getMessage(),
                'response' => null,
                'error'    => $e->getMessage(),
            ];
        }
    }
}
