<?php

namespace App\Services;

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
    }

    /**
     * Memeriksa apakah konfigurasi token dan target grup sudah terisi.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->token) && ! empty($this->targetGroup);
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

        if (empty($this->token)) {
            $errorMsg = 'Fonnte token belum dikonfigurasi di file .env (FONNTE_TOKEN).';
            Log::warning('[FonnteService] ' . $errorMsg);
            return [
                'success'  => false,
                'message'  => $errorMsg,
                'response' => null,
                'error'    => 'TOKEN_MISSING',
            ];
        }

        if (empty($target)) {
            $errorMsg = 'Target nomor atau ID grup WhatsApp belum ditentukan (FONNTE_TARGET_GROUP).';
            Log::warning('[FonnteService] ' . $errorMsg);
            return [
                'success'  => false,
                'message'  => $errorMsg,
                'response' => null,
                'error'    => 'TARGET_MISSING',
            ];
        }

        try {
            $payload = [
                'target'      => $target,
                'message'     => $message,
                'countryCode' => $this->countryCode,
            ];

            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->asForm()
                ->post($this->url, $payload);

            $result = $response->json();

            // Fonnte mengembalikan status: true atau false dalam payload json
            $isSuccess = $response->successful() && isset($result['status']) && $result['status'] === true;

            if ($isSuccess) {
                Log::info('[FonnteService] Pesan WhatsApp berhasil dikirim ke: ' . $target, [
                    'response' => $result,
                ]);

                return [
                    'success'  => true,
                    'message'  => 'Pesan WhatsApp berhasil dikirim ke ' . $target,
                    'response' => $result,
                    'error'    => null,
                ];
            }

            $reason = $result['reason'] ?? $result['message'] ?? 'Respon Fonnte gagal atau status false.';
            Log::error('[FonnteService] Gagal mengirim pesan ke ' . $target . ': ' . $reason, [
                'http_status' => $response->status(),
                'response'    => $result,
            ]);

            return [
                'success'  => false,
                'message'  => 'Gagal mengirim pesan: ' . $reason,
                'response' => $result,
                'error'    => 'API_ERROR',
            ];
        } catch (\Throwable $e) {
            Log::error('[FonnteService] Exception saat menghubungi Fonnte: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'success'  => false,
                'message'  => 'Terjadi kesalahan koneksi ke Fonnte: ' . $e->getMessage(),
                'response' => null,
                'error'    => 'CONNECTION_EXCEPTION',
            ];
        }
    }
}
