<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicAreaMvsbReading extends Model
{
    use HasFactory;

    protected $table = 'public_area_mvsb_readings';

    protected $fillable = [
        'tanggal',
        'voltage_20kv',
        'current',
        'foto',
        'catatan',
        'user_id',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'voltage_20kv' => 'float',
        'current'      => 'float',
        'verified_at'  => 'datetime',
    ];

    /**
     * Scope untuk mengambil pembacaan berdasarkan bulan & tahun
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('tanggal', $year)
                     ->whereMonth('tanggal', $month);
    }

    /**
     * Cari pembacaan terakhir sebelum tanggal tertentu
     */
    public static function previousOf(string $tanggal): ?self
    {
        return static::whereDate('tanggal', '<', $tanggal)
                     ->latest('tanggal')
                     ->first();
    }

    /**
     * Cek apakah sudah diverifikasi
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Relasi ke Teknisi pencatat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Verifikator (Supervisor / Super Admin)
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
