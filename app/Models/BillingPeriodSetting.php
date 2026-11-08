<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPeriodSetting extends Model
{
    use HasFactory;

    protected $table = 'billing_period_settings';

    protected $fillable = [
        'is_open',
        'active_year',
        'active_month',
        'opened_at',
        'opened_by',
        'closed_at',
        'closed_by',
        'keterangan',
    ];

    protected $casts = [
        'is_open'      => 'boolean',
        'active_year'  => 'integer',
        'active_month' => 'integer',
        'opened_at'    => 'datetime',
        'closed_at'    => 'datetime',
    ];

    public function openedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Ambil record pengaturan sesi saat ini (Singleton row)
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'is_open'      => true, // Default terbuka untuk November 2026 agar siap diuji
                'active_year'  => now()->year,
                'active_month' => now()->month,
                'opened_at'    => now(),
                'keterangan'   => 'Sesi penginputan utilitas bulanan aktif',
            ]
        );
    }

    /**
     * Cek apakah penginputan teknisi saat ini diizinkan
     */
    public static function isOpen(): bool
    {
        return static::current()->is_open;
    }

    public static function activeYear(): int
    {
        return static::current()->active_year;
    }

    public static function activeMonth(): int
    {
        return static::current()->active_month;
    }

    /**
     * Buka sesi penginputan (Khusus Super Admin)
     */
    public static function openSession(int $year, int $month, int $userId, ?string $note = null): self
    {
        $setting = static::current();
        $setting->update([
            'is_open'      => true,
            'active_year'  => $year,
            'active_month' => $month,
            'opened_at'    => now(),
            'opened_by'    => $userId,
            'keterangan'   => $note ?: 'Sesi penginputan periode ' . $month . '/' . $year . ' dibuka oleh Super Admin.',
        ]);
        return $setting;
    }

    /**
     * Tutup / Kunci sesi penginputan (Khusus Super Admin)
     */
    public static function closeSession(int $userId, ?string $note = null): self
    {
        $setting = static::current();
        $setting->update([
            'is_open'    => false,
            'closed_at'  => now(),
            'closed_by'  => $userId,
            'keterangan' => $note ?: 'Sesi penginputan ditutup oleh Super Admin. Penginputan teknisi dikunci.',
        ]);
        return $setting;
    }

    public function getNamaBulanAktifAttribute(): string
    {
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $bulanNames[(int) $this->active_month] ?? (string)$this->active_month;
    }
}
