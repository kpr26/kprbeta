<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantEnergyReading extends Model
{
    use HasFactory;

    protected $table = 'tenant_energy_readings';

    protected $fillable = [
        'tenant_id',
        'meter_type',
        'periode_bulan',
        'periode_tahun',
        'tanggal_catat',
        'daya',
        'stand_awal',
        'stand_akhir',
        'pemakaian',
        'tarif_per_kwh',
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
        'daya'            => 'decimal:2',
        'stand_awal'      => 'decimal:2',
        'stand_akhir'     => 'decimal:2',
        'pemakaian'       => 'decimal:2',
        'tarif_per_kwh'   => 'decimal:2',
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

    /**
     * Daya efektif (kVA)
     */
    public function getEffectiveDayaAttribute(): float
    {
        if ($this->daya !== null && (float)$this->daya > 0) {
            return (float) $this->daya;
        }

        if ($this->meter_type === 'ev') {
            return (float) ($this->tenant?->daya_ev ?: 0.00);
        }

        return (float) ($this->tenant?->daya_listrik ?: 0.00);
    }

    /**
     * Min Hour (40 Jam) = Daya * 40
     */
    public function getMinHourAttribute(): float
    {
        return round($this->effective_daya * 40, 2);
    }

    /**
     * Tarif per kWh (Default: Rp 1.699,53)
     */
    public function getTarifAttribute(): float
    {
        return (float) ($this->tarif_per_kwh ?: 1699.53);
    }

    /**
     * Min Charge (40 Jam) = Min Hour * Tarif
     */
    public function getMinChargeAttribute(): float
    {
        return round($this->min_hour * $this->tarif, 2);
    }

    /**
     * Actual Charge = Pemakaian * Tarif
     */
    public function getActualChargeAttribute(): float
    {
        return round((float)$this->pemakaian * $this->tarif, 2);
    }

    /**
     * Billing = max(Min Charge, Actual Charge)
     */
    public function getBillingAttribute(): float
    {
        // Jika Actual Charge negatif (misal KWH Error), gunakan Min Charge
        if ($this->actual_charge < $this->min_charge) {
            return $this->min_charge;
        }
        return $this->actual_charge;
    }

    /**
     * PPJ (2,40%) = Billing * 0.024
     */
    public function getPpjAttribute(): float
    {
        return round($this->billing * 0.024, 2);
    }

    /**
     * Biaya Materai = Rp 10.000 (jika ada tagihan)
     */
    public function getBiayaMateraiAttribute(): float
    {
        return 10000.00;
    }

    /**
     * Total Tagihan = Billing + PPJ + Biaya Materai
     */
    public function getTotalTagihanAttribute(): float
    {
        return round($this->billing + $this->ppj + $this->biaya_materai, 2);
    }
}
