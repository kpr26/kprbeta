<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = [
        'nama_tenant',
        'kode_tenant',
        'lantai',
        'nomor_unit',
        'nomor_meter_listrik',
        'nomor_meter_air',
        'faktor_kali_listrik',
        'faktor_kali_air',
        'daya_listrik_va',
        'daya_listrik',
        'has_ev_charger',
        'daya_ev',
        'nomor_meter_ev',
        'kontak_person',
        'no_telepon',
        'email',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'faktor_kali_listrik' => 'decimal:4',
        'faktor_kali_air'     => 'decimal:4',
        'daya_listrik_va'     => 'integer',
        'daya_listrik'        => 'decimal:2',
        'has_ev_charger'      => 'boolean',
        'daya_ev'             => 'decimal:2',
    ];

    /**
     * Relasi ke pencatatan listrik bulanan
     */
    public function energyReadings(): HasMany
    {
        return $this->hasMany(TenantEnergyReading::class, 'tenant_id')
            ->orderBy('periode_tahun', 'desc')
            ->orderBy('periode_bulan', 'desc');
    }

    /**
     * Relasi ke pencatatan air bulanan
     */
    public function waterReadings(): HasMany
    {
        return $this->hasMany(TenantWaterReading::class, 'tenant_id')
            ->orderBy('periode_tahun', 'desc')
            ->orderBy('periode_bulan', 'desc');
    }

    /**
     * Ambil pembacaan listrik untuk periode tertentu dan tipe meter ('regular' atau 'ev')
     */
    public function getEnergyReadingFor(int $year, int $month, string $meterType = 'regular'): ?TenantEnergyReading
    {
        return $this->energyReadings()
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->where('meter_type', $meterType)
            ->first();
    }

    /**
     * Ambil pembacaan air untuk periode tertentu
     */
    public function getWaterReadingFor(int $year, int $month): ?TenantWaterReading
    {
        return $this->waterReadings()
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->first();
    }

    /**
     * Cari stand akhir listrik dari bulan sebelumnya (atau rekaman terakhir)
     * untuk dijadikan stand awal otomatis bulan ini.
     */
    public function getPreviousEnergyStand(int $year, int $month, string $meterType = 'regular'): float
    {
        // Hitung bulan & tahun sebelumnya
        $prevMonth = $month - 1;
        $prevYear  = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        // Cek apakah ada record di bulan sebelumnya
        $reading = $this->energyReadings()
            ->where('periode_tahun', $prevYear)
            ->where('periode_bulan', $prevMonth)
            ->where('meter_type', $meterType)
            ->first();

        if ($reading) {
            return (float) $reading->stand_akhir;
        }

        // Jika tidak ada bulan sebelumnya persis, ambil pembacaan terakhir sebelum periode ini
        $lastBefore = $this->energyReadings()
            ->where('meter_type', $meterType)
            ->where(function ($q) use ($year, $month) {
                $q->where('periode_tahun', '<', $year)
                  ->orWhere(function ($q2) use ($year, $month) {
                      $q2->where('periode_tahun', $year)
                         ->where('periode_bulan', '<', $month);
                  });
            })
            ->orderBy('periode_tahun', 'desc')
            ->orderBy('periode_bulan', 'desc')
            ->first();

        return $lastBefore ? (float) $lastBefore->stand_akhir : 0.00;
    }

    /**
     * Cari stand akhir air dari bulan sebelumnya (atau rekaman terakhir)
     * untuk dijadikan stand awal otomatis bulan ini.
     */
    public function getPreviousWaterStand(int $year, int $month): float
    {
        $prevMonth = $month - 1;
        $prevYear  = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $reading = $this->waterReadings()
            ->where('periode_tahun', $prevYear)
            ->where('periode_bulan', $prevMonth)
            ->first();

        if ($reading) {
            return (float) $reading->stand_akhir;
        }

        $lastBefore = $this->waterReadings()
            ->where(function ($q) use ($year, $month) {
                $q->where('periode_tahun', '<', $year)
                  ->orWhere(function ($q2) use ($year, $month) {
                      $q2->where('periode_tahun', $year)
                         ->where('periode_bulan', '<', $month);
                  });
            })
            ->orderBy('periode_tahun', 'desc')
            ->orderBy('periode_bulan', 'desc')
            ->first();

        return $lastBefore ? (float) $lastBefore->stand_akhir : 0.00;
    }

    /**
     * Label unit lengkap
     */
    public function getUnitLabelAttribute(): string
    {
        $parts = array_filter([$this->lantai, $this->nomor_unit]);
        return implode(' - ', $parts) ?: '—';
    }
}
