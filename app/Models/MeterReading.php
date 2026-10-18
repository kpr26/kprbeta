<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    protected $fillable = [
        'tanggal', 'user_id', 'stand_lwbp', 'stand_wbp', 'stand_air',
        'foto_kwh', 'foto_air', 'catatan', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'verified_at' => 'datetime',
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
            ->whereNotNull('stand_lwbp')
            ->whereNotNull('stand_wbp')
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

        foreach ($rows as $row) {
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

            if ($row->pakai_lwbp !== null && $row->pakai_wbp !== null) {
                $row->pakai_kwh = round($row->pakai_lwbp + $row->pakai_wbp, 2);
            } else {
                $row->pakai_kwh = null;
            }

            if ($row->stand_air !== null && $prevWater && $prevWater->stand_air !== null) {
                $row->pakai_air = round($row->stand_air - $prevWater->stand_air, 2);
            } else {
                $row->pakai_air = null;
            }

            if ($row->stand_lwbp !== null && $row->stand_wbp !== null) {
                $prevElec = $row;
            }
            if ($row->stand_air !== null) {
                $prevWater = $row;
            }
        }

        return $rows;
    }
}
