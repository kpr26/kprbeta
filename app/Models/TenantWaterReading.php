<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantWaterReading extends Model
{
    use HasFactory;

    protected $table = 'tenant_water_readings';

    protected $fillable = [
        'tenant_id',
        'periode_bulan',
        'periode_tahun',
        'tanggal_catat',
        'stand_awal',
        'stand_akhir',
        'pemakaian',
        'foto_meter',
        'catatan',
        'keterangan',
        'is_special_case',
        'user_id',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'tanggal_catat'   => 'date',
        'stand_awal'      => 'decimal:3',
        'stand_akhir'     => 'decimal:3',
        'pemakaian'       => 'decimal:3',
        'is_special_case' => 'boolean',
        'verified_at'     => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Nama Bulan Bahasa Indonesia
     */
    public function getNamaBulanAttribute(): string
    {
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $bulanNames[(int) $this->periode_bulan] ?? (string)$this->periode_bulan;
    }

    public function getPeriodeLabelAttribute(): string
    {
        return $this->nama_bulan . ' ' . $this->periode_tahun;
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto_meter) {
            return null;
        }
        return asset('storage/' . $this->foto_meter);
    }

    public function getIsTerkendalaAttribute(): bool
    {
        return $this->is_special_case 
            || ($this->stand_akhir !== null && $this->stand_awal !== null && (float)$this->stand_akhir < (float)$this->stand_awal)
            || !empty($this->keterangan);
    }

    public function getDisplayKeteranganAttribute(): ?string
    {
        return $this->keterangan ?: $this->catatan;
    }
}
