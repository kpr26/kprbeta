<?php

namespace App\Models;

use App\Traits\HasVerificationStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    use HasVerificationStatus;
    protected $fillable = [
        'tanggal', 'user_id',
        'stand_bp', 'stand_lbp1', 'stand_lbp2', 'stand_total',
        'stand_lwbp', 'stand_wbp', 'stand_air',
        'foto_kwh', 'foto_air', 'catatan', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'verified_at' => 'datetime',
        'stand_bp'    => 'float',
        'stand_lbp1'  => 'float',
        'stand_lbp2'  => 'float',
        'stand_total' => 'float',
        'stand_lwbp'  => 'float',
        'stand_wbp'   => 'float',
        'stand_air'   => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** Pencatatan terakhir sebelum tanggal tertentu. */
    public static function previousOf($date): ?self
    {
        return static::whereDate('tanggal', '<', $date)
            ->orderByDesc('tanggal')
            ->first();
    }

    /** Pencatatan berikutnya setelah tanggal tertentu. */
    public static function nextOf($date): ?self
    {
        return static::whereDate('tanggal', '>', $date)
            ->orderBy('tanggal')
            ->first();
    }

    /** Pencatatan terakhir dengan data listrik sebelum tanggal tertentu. */
    public static function previousElectricOf($date): ?self
    {
        return static::whereDate('tanggal', '<', $date)
            ->where(function ($q) {
                $q->whereNotNull('stand_total')
                  ->orWhereNotNull('stand_bp')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('stand_lwbp')
                          ->whereNotNull('stand_wbp');
                  });
            })
            ->orderByDesc('tanggal')
            ->first();
    }

    /** Pencatatan terakhir dengan data air sebelum tanggal tertentu. */
    public static function previousWaterOf($date): ?self
    {
        return static::whereDate('tanggal', '<', $date)
            ->whereNotNull('stand_air')
            ->orderByDesc('tanggal')
            ->first();
    }

    /** Pencatatan berikutnya dengan data listrik setelah tanggal tertentu. */
    public static function nextElectricOf($date): ?self
    {
        return static::whereDate('tanggal', '>', $date)
            ->where(function ($q) {
                $q->whereNotNull('stand_total')
                  ->orWhereNotNull('stand_bp')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('stand_lwbp')
                          ->whereNotNull('stand_wbp');
                  });
            })
            ->orderBy('tanggal')
            ->first();
    }

    /** Pencatatan berikutnya dengan data air setelah tanggal tertentu. */
    public static function nextWaterOf($date): ?self
    {
        return static::whereDate('tanggal', '>', $date)
            ->whereNotNull('stand_air')
            ->orderBy('tanggal')
            ->first();
    }

    /**
     * Ambil data satu bulan beserta pemakaian harian
     * (stand hari ini - stand pencatatan sebelumnya).
     */
    public static function monthWithUsage(int $year, int $month): Collection
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        $rows = static::with(['user', 'verifier'])
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal')->get();

        $prevElec = static::previousElectricOf($start);
        $prevWater = static::previousWaterOf($start);

        $cumBp = 0;
        $cumLbp = 0;
        $cumKwh = 0;
        $cumAir = 0;

        foreach ($rows as $row) {
            // AMR PLN T33N Pemakaian Stand
            if ($row->stand_bp !== null && $prevElec && $prevElec->stand_bp !== null) {
                $row->pakai_bp = round($row->stand_bp - $prevElec->stand_bp, 2);
            } else {
                $row->pakai_bp = null;
            }

            if ($row->stand_lbp1 !== null && $prevElec && $prevElec->stand_lbp1 !== null) {
                $row->pakai_lbp1 = round($row->stand_lbp1 - $prevElec->stand_lbp1, 2);
            } else {
                $row->pakai_lbp1 = null;
            }

            if ($row->stand_lbp2 !== null && $prevElec && $prevElec->stand_lbp2 !== null) {
                $row->pakai_lbp2 = round($row->stand_lbp2 - $prevElec->stand_lbp2, 2);
            } else {
                $row->pakai_lbp2 = null;
            }

            if ($row->stand_total !== null && $prevElec && $prevElec->stand_total !== null) {
                $row->pakai_total = round($row->stand_total - $prevElec->stand_total, 2);
            } else {
                $row->pakai_total = null;
            }

            // LWBP & WBP untuk kompatibilitas data lama
            if ($row->stand_lwbp !== null && $prevElec && $prevElec->stand_lwbp !== null) {
                $row->pakai_lwbp = round($row->stand_lwbp - $prevElec->stand_lwbp, 2);
            } else {
                $row->pakai_lwbp = null;
            }

            if ($row->stand_wbp !== null && $prevElec && $prevElec->stand_wbp !== null) {
                $row->pakai_wbp = round($row->stand_wbp - $prevElec->stand_wbp, 2);
            } else {
                $row->pakai_wbp = null;
            }

            // Pemakaian LBP Gabungan (LBP 1 + LBP 2) atau fallback ke LWBP
            if ($row->pakai_lbp1 !== null || $row->pakai_lbp2 !== null) {
                $row->pakai_lbp = round(($row->pakai_lbp1 ?? 0) + ($row->pakai_lbp2 ?? 0), 2);
            } else {
                $row->pakai_lbp = $row->pakai_lwbp;
            }

            // Fallback pemakaian BP jika hanya wbp yang ada
            if ($row->pakai_bp === null && $row->pakai_wbp !== null) {
                $row->pakai_bp = $row->pakai_wbp;
            }

            // Total Pemakaian Listrik kWh
            if ($row->pakai_total !== null) {
                $row->pakai_kwh = $row->pakai_total;
            } elseif ($row->pakai_bp !== null || $row->pakai_lbp !== null) {
                $row->pakai_kwh = round(($row->pakai_bp ?? 0) + ($row->pakai_lbp ?? 0), 2);
            } elseif ($row->pakai_lwbp !== null && $row->pakai_wbp !== null) {
                $row->pakai_kwh = round($row->pakai_lwbp + $row->pakai_wbp, 2);
            } else {
                $row->pakai_kwh = null;
            }

            // Pemakaian Air
            if ($row->stand_air !== null && $prevWater && $prevWater->stand_air !== null) {
                $row->pakai_air = round($row->stand_air - $prevWater->stand_air, 2);
            } else {
                $row->pakai_air = null;
            }

            // Akumulatif masing-masing nilai s/d tanggal ini
            if ($row->pakai_bp !== null) {
                $cumBp += $row->pakai_bp;
                $row->cum_bp = round($cumBp, 2);
            } else {
                $row->cum_bp = $cumBp > 0 ? round($cumBp, 2) : null;
            }

            if ($row->pakai_lbp !== null) {
                $cumLbp += $row->pakai_lbp;
                $row->cum_lbp = round($cumLbp, 2);
            } else {
                $row->cum_lbp = $cumLbp > 0 ? round($cumLbp, 2) : null;
            }

            if ($row->pakai_kwh !== null) {
                $cumKwh += $row->pakai_kwh;
                $row->cum_kwh = round($cumKwh, 2);
            } else {
                $row->cum_kwh = $cumKwh > 0 ? round($cumKwh, 2) : null;
            }

            if ($row->pakai_air !== null) {
                $cumAir += $row->pakai_air;
                $row->cum_air = round($cumAir, 2);
            } else {
                $row->cum_air = $cumAir > 0 ? round($cumAir, 2) : null;
            }

            if ($row->stand_total !== null || $row->stand_bp !== null || ($row->stand_lwbp !== null && $row->stand_wbp !== null)) {
                $prevElec = $row;
            }
            if ($row->stand_air !== null) {
                $prevWater = $row;
            }
        }

        return $rows;
    }
}
