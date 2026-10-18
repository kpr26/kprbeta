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
        return static::whereDate('tanggal', '<', $date)->orderByDesc('tanggal')->first();
    }

    /** Pencatatan pertama setelah tanggal tertentu. */
    public static function nextOf($date): ?self
    {
        return static::whereDate('tanggal', '>', $date)->orderBy('tanggal')->first();
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

        $prev = static::previousOf($start);
        foreach ($rows as $row) {
            $row->pakai_lwbp = $prev ? round($row->stand_lwbp - $prev->stand_lwbp, 2) : null;
            $row->pakai_wbp  = $prev ? round($row->stand_wbp - $prev->stand_wbp, 2) : null;
            $row->pakai_kwh  = $prev ? round($row->pakai_lwbp + $row->pakai_wbp, 2) : null;
            $row->pakai_air  = $prev ? round($row->stand_air - $prev->stand_air, 2) : null;
            $prev = $row;
        }

        return $rows;
    }
}
