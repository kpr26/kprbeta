<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasVerificationStatus
{
    /**
     * Apakah pencatatan ini memerlukan verifikasi oleh supervisor/admin?
     * Aturan: Hanya data yang diinput oleh teknisi pada tanggal berjalan yang memerlukan verifikasi.
     * Data yang diinput oleh admin/non-teknisi atau data backdate tidak memerlukan verifikasi.
     */
    public function needsVerification(): bool
    {
        // 1. Jika sudah memiliki timestamp verified_at, sudah terverifikasi
        if ($this->verified_at !== null) {
            return false;
        }

        // 2. Cek user pencatat
        $user = $this->relationLoaded('user') ? $this->user : $this->user()->first();

        // Jika tidak ada user atau role bukan teknisi (misal: admin, superadmin, eng_admin, eng_spv), tidak perlu verifikasi
        if (! $user || $user->role !== 'teknisi') {
            return false;
        }

        // 3. Cek apakah backdate (tanggal pencatatan lebih lampau dari tanggal input/created_at)
        if ($this->tanggal) {
            $recordDate = Carbon::parse($this->tanggal)->toDateString();
            $inputDate = $this->created_at 
                ? Carbon::parse($this->created_at)->toDateString() 
                : today()->toDateString();

            if ($recordDate < $inputDate) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cek apakah sudah terverifikasi (baik diverifikasi manual maupun otomatis untuk admin / backdate)
     */
    public function isVerified(): bool
    {
        return ! $this->needsVerification();
    }
}
