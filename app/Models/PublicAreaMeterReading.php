<?php

namespace App\Models;

use App\Traits\HasVerificationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicAreaMeterReading extends Model
{
    use HasFactory, HasVerificationStatus;

    protected $table = 'public_area_meter_readings';

    protected $fillable = [
        'tanggal',
        'category',
        'meter_key',
        'meter_name',
        'unit',
        'stand_meter',
        'foto',
        'catatan',
        'user_id',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'stand_meter' => 'float',
        'verified_at' => 'datetime',
    ];

    /**
     * Definisi Daftar Water Meter Public Area (3 meter)
     */
    public static function waterMeters(): array
    {
        return [
            'cooling_tower' => [
                'key'      => 'cooling_tower',
                'name'     => 'Water Meter Cooling Tower 12A',
                'label'    => 'Cooling Tower 12A',
                'location' => 'Lantai 12A',
                'unit'     => 'm³',
                'color'    => '#06b6d4',
                'badge'    => 'Cooling Tower',
                'desc'     => 'Pencatatan meter air untuk operasional Cooling Tower Lantai 12A',
            ],
            'toilet_sky_lobby' => [
                'key'      => 'toilet_sky_lobby',
                'name'     => 'Water Meter Toilet Sky Lobby 11 & Mushola 12',
                'label'    => 'Toilet Sky Lobby 11 & Mushola 12',
                'location' => 'Sky Lobby Lt. 11 & Mushola Lt. 12',
                'unit'     => 'm³',
                'color'    => '#3b82f6',
                'badge'    => 'Sky Lobby & Mushola',
                'desc'     => 'Pencatatan meter air Toilet Sky Lobby Lantai 11 dan Mushola Lantai 12',
            ],
            'pantry_office' => [
                'key'      => 'pantry_office',
                'name'     => 'Water Meter Pantry Office',
                'label'    => 'Pantry Office',
                'location' => 'Area Pantry Office',
                'unit'     => 'm³',
                'color'    => '#10b981',
                'badge'    => 'Pantry Office',
                'desc'     => 'Pencatatan meter air utilitas Pantry Office',
            ],
        ];
    }

    /**
     * Definisi Daftar KWh Meter Listrik Public Area
     */
    public static function electricityMeters(): array
    {
        return [
            'kwh_public_area' => [
                'key'      => 'kwh_public_area',
                'name'     => 'KWh Meter Public Area',
                'label'    => 'KWh Meter Public Area',
                'location' => 'Panel Distribusi Public Area',
                'unit'     => 'kWh',
                'color'    => '#fbbf24',
                'badge'    => 'KWh Meter',
                'desc'     => 'Pencatatan stand meteran listrik daya & penerangan public area gedung',
            ],
        ];
    }

    /**
     * Relasi ke Teknisi pencatat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Supervisor/Admin pemverifikasi
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Ambil pencatatan sebelumnya untuk meter_key yang sama
     */
    public static function previousOf($date, string $category, string $meterKey): ?self
    {
        return static::where('category', $category)
            ->where('meter_key', $meterKey)
            ->whereDate('tanggal', '<', $date)
            ->whereNotNull('stand_meter')
            ->orderByDesc('tanggal')
            ->first();
    }

    /**
     * Ambil pencatatan berikutnya untuk meter_key yang sama
     */
    public static function nextOf($date, string $category, string $meterKey): ?self
    {
        return static::where('category', $category)
            ->where('meter_key', $meterKey)
            ->whereDate('tanggal', '>', $date)
            ->whereNotNull('stand_meter')
            ->orderBy('tanggal')
            ->first();
    }

    /**
     * Rekap data bulanan per kategori beserta kalkulasi pemakaian dan akumulatif
     */
    public static function monthWithUsage(int $year, int $month, string $category): Collection
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        return static::intervalWithUsage($start->toDateString(), $end->toDateString(), $category);
    }

    /**
     * Rekap data rentang interval tanggal (hingga 12 bulan) per kategori beserta kalkulasi pemakaian dan akumulatif
     */
    public static function intervalWithUsage(string $startDate, string $endDate, string $category): Collection
    {
        $rows = static::with(['user', 'verifier'])
            ->where('category', $category)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        // Kelompokkan per meter_key untuk menghitung selisih stand dan akumulatif
        $meters = $category === 'water' ? static::waterMeters() : static::electricityMeters();
        $prevReadings = [];
        $cumSums = [];

        foreach ($meters as $key => $meta) {
            $prevReadings[$key] = static::previousOf($startDate, $category, $key);
            $cumSums[$key] = 0;
        }

        foreach ($rows as $row) {
            $key = $row->meter_key;
            $prev = $prevReadings[$key] ?? null;

            if ($row->stand_meter !== null && $prev && $prev->stand_meter !== null) {
                $row->pemakaian = round($row->stand_meter - $prev->stand_meter, 2);
            } else {
                $row->pemakaian = null;
            }

            if ($row->pemakaian !== null) {
                $cumSums[$key] = round(($cumSums[$key] ?? 0) + $row->pemakaian, 2);
                $row->cum_usage = $cumSums[$key];
            } else {
                $row->cum_usage = ($cumSums[$key] ?? 0) > 0 ? $cumSums[$key] : null;
            }

            $prevReadings[$key] = $row;
        }

        return $rows;
    }
}
