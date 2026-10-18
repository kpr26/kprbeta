<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLogBook extends Model
{
    use HasFactory;

    protected $table = 'daily_log_books';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'status',
        'deskripsi',
        'foto_awal',
        'foto_proses',
        'foto_akhir',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke teknisi / pembuat log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * URL Foto Awal.
     */
    public function getFotoAwalUrlAttribute(): ?string
    {
        return $this->foto_awal ? asset('storage/' . $this->foto_awal) : null;
    }

    /**
     * URL Foto Proses.
     */
    public function getFotoProsesUrlAttribute(): ?string
    {
        return $this->foto_proses ? asset('storage/' . $this->foto_proses) : null;
    }

    /**
     * URL Foto Akhir.
     */
    public function getFotoAkhirUrlAttribute(): ?string
    {
        return $this->foto_akhir ? asset('storage/' . $this->foto_akhir) : null;
    }

    /**
     * Format hari dan tanggal lengkap (Bahasa Indonesia).
     */
    public function getHariTanggalAttribute(): string
    {
        return $this->tanggal ? $this->tanggal->translatedFormat('l, d F Y') : '—';
    }

    /**
     * Hitung durasi kerja dari jam mulai sampai jam selesai.
     */
    public function getDurasiKerjaAttribute(): string
    {
        if (! $this->jam_mulai || ! $this->jam_selesai) {
            return '—';
        }

        try {
            $start = Carbon::createFromFormat('H:i', substr($this->jam_mulai, 0, 5));
            $end = Carbon::createFromFormat('H:i', substr($this->jam_selesai, 0, 5));

            if ($end->lt($start)) {
                // Melewati tengah malam
                $end->addDay();
            }

            $diffMinutes = $start->diffInMinutes($end);
            $hours = floor($diffMinutes / 60);
            $minutes = $diffMinutes % 60;

            if ($hours > 0 && $minutes > 0) {
                return "{$hours} jam {$minutes} mnt";
            } elseif ($hours > 0) {
                return "{$hours} jam";
            } else {
                return "{$minutes} menit";
            }
        } catch (\Throwable $e) {
            return "{$this->jam_mulai} - {$this->jam_selesai}";
        }
    }

    /**
     * Filter query pencarian dan periode.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('lokasi', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            if (in_array($status, ['Done', 'Continue'], true)) {
                $q->where('status', $status);
            }
        });

        $query->when($filters['user_id'] ?? null, function ($q, $userId) {
            $q->where('user_id', $userId);
        });

        $query->when($filters['tanggal'] ?? null, function ($q, $date) {
            $q->whereDate('tanggal', $date);
        });

        $query->when($filters['bulan'] ?? null, function ($q, $month) {
            // format YYYY-MM
            $parts = explode('-', $month);
            if (count($parts) === 2) {
                $q->whereYear('tanggal', $parts[0])
                  ->whereMonth('tanggal', $parts[1]);
            }
        });
    }
}
