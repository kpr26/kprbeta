<?php

namespace App\Http\Controllers;

use App\Models\DailyLogBook;
use App\Models\MeterReading;
use App\Models\PublicAreaLvsbReading;
use App\Models\PublicAreaMeterReading;
use App\Models\PublicAreaMvsbReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GmMonitoringController extends Controller
{
    /**
     * Halaman Utama Executive Monitoring & Summary untuk General Manager
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless(
            $user && ($user->role === 'gm' || $user->isSuperAdmin() || in_array($user->role, ['eng_spv', 'eng_admin', 'user'], true)),
            403,
            'Halaman ini khusus monitoring General Manager dan Manajemen.'
        );

        // Validasi & parsing periode (format YYYY-MM)
        $periode = $request->input('periode', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $periode)) {
            $periode = now()->format('Y-m');
        }
        [$year, $month] = array_map('intval', explode('-', $periode));
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // 1. Data Logsheet Gedung (AMR PLN & Air PDAM/Gedung)
        $meterReadings = MeterReading::monthWithUsage($year, $month);
        $summaryGedung = [
            'lwbp'          => $meterReadings->sum('pakai_lwbp'),
            'wbp'           => $meterReadings->sum('pakai_wbp'),
            'kwh'           => $meterReadings->sum('pakai_kwh'),
            'air'           => $meterReadings->sum('pakai_air'),
            'jumlah_hari'   => $meterReadings->count(),
            'rata_kwh'      => $meterReadings->count() > 0 ? round($meterReadings->sum('pakai_kwh') / $meterReadings->count(), 2) : 0,
            'rata_air'      => $meterReadings->count() > 0 ? round($meterReadings->sum('pakai_air') / $meterReadings->count(), 2) : 0,
            'terverifikasi' => $meterReadings->filter(fn ($r) => $r->isVerified())->count(),
            'pending'       => $meterReadings->filter(fn ($r) => $r->needsVerification())->count(),
        ];

        // 2. Data Water Meter Public Area (Cooling Tower 12A, Toilet Sky Lobby 11 & Mushola 12, Pantry Office)
        $waterPaReadings = PublicAreaMeterReading::monthWithUsage($year, $month, 'water');
        $waterPaByMeter = [];
        foreach (PublicAreaMeterReading::waterMeters() as $key => $meta) {
            $filtered = $waterPaReadings->where('meter_key', $key);
            $totalPakai = $filtered->sum('pemakaian');
            $waterPaByMeter[$key] = [
                'meta'        => $meta,
                'total_pakai' => round($totalPakai, 2),
                'count'       => $filtered->count(),
                'latest'      => $filtered->last(),
            ];
        }
        $totalWaterPa = round($waterPaReadings->sum('pemakaian'), 2);

        // 3. Data LVSB (Low Voltage Switchboard)
        $lvsbReadings = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();
        $lvsbSummary = [
            'count'         => $lvsbReadings->count(),
            'terverifikasi' => $lvsbReadings->filter(fn ($r) => $r->isVerified())->count(),
            'pending'       => $lvsbReadings->filter(fn ($r) => $r->needsVerification())->count(),
            'latest'        => $lvsbReadings->last(),
        ];

        // 4. Data MVSB (Medium Voltage Switchboard 20kV Incoming)
        $mvsbReadings = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();
        $mvsbSummary = [
            'count'         => $mvsbReadings->count(),
            'avg_voltage'   => $mvsbReadings->count() > 0 ? round($mvsbReadings->avg('voltage_20kv'), 2) : 0,
            'avg_current'   => $mvsbReadings->count() > 0 ? round($mvsbReadings->avg('current'), 2) : 0,
            'terverifikasi' => $mvsbReadings->filter(fn ($r) => $r->isVerified())->count(),
            'pending'       => $mvsbReadings->filter(fn ($r) => $r->needsVerification())->count(),
            'latest'        => $mvsbReadings->last(),
        ];

        // 5. Data Daily Log Book (Pekerjaan Harian Teknisi)
        $dailyLogs = DailyLogBook::with('user')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->get();
        $dailyLogStats = [
            'total'           => $dailyLogs->count(),
            'done'            => $dailyLogs->where('status', 'Done')->count(),
            'continue'        => $dailyLogs->where('status', 'Continue')->count(),
            'completion_rate' => $dailyLogs->count() > 0 ? round(($dailyLogs->where('status', 'Done')->count() / $dailyLogs->count()) * 100, 1) : 0,
        ];

        // 6. Status Hari Ini (Pulse Monitoring Real-Time Hari Ini)
        $todayGedung  = MeterReading::whereDate('tanggal', today())->first();
        $todayWaterPa = PublicAreaMeterReading::where('category', 'water')->whereDate('tanggal', today())->get()->keyBy('meter_key');
        $todayLvsb    = PublicAreaLvsbReading::whereDate('tanggal', today())->first();
        $todayMvsb    = PublicAreaMvsbReading::whereDate('tanggal', today())->first();
        $todayDailyLogsCount = DailyLogBook::whereDate('tanggal', today())->count();

        $todayPulse = [
            'elec_pln' => [
                'recorded' => $todayGedung && ($todayGedung->stand_lwbp !== null || $todayGedung->foto_kwh),
                'stand'    => $todayGedung ? "LWBP: " . number_format($todayGedung->stand_lwbp ?? 0, 2, ',', '.') . " kWh · WBP: " . number_format($todayGedung->stand_wbp ?? 0, 2, ',', '.') . " kWh" : null,
                'user'     => $todayGedung?->user?->name,
                'time'     => $todayGedung?->updated_at?->format('H:i'),
            ],
            'water_gedung' => [
                'recorded' => $todayGedung && ($todayGedung->stand_air !== null || $todayGedung->foto_air),
                'stand'    => $todayGedung ? number_format($todayGedung->stand_air ?? 0, 2, ',', '.') . " m³" : null,
                'user'     => $todayGedung?->user?->name,
                'time'     => $todayGedung?->updated_at?->format('H:i'),
            ],
            'water_cooling_tower' => [
                'recorded' => isset($todayWaterPa['cooling_tower']) && $todayWaterPa['cooling_tower']->stand_meter !== null,
                'stand'    => isset($todayWaterPa['cooling_tower']) ? number_format($todayWaterPa['cooling_tower']->stand_meter ?? 0, 2, ',', '.') . " m³" : null,
            ],
            'water_toilet_sky' => [
                'recorded' => isset($todayWaterPa['toilet_sky_lobby']) && $todayWaterPa['toilet_sky_lobby']->stand_meter !== null,
                'stand'    => isset($todayWaterPa['toilet_sky_lobby']) ? number_format($todayWaterPa['toilet_sky_lobby']->stand_meter ?? 0, 2, ',', '.') . " m³" : null,
            ],
            'water_pantry' => [
                'recorded' => isset($todayWaterPa['pantry_office']) && $todayWaterPa['pantry_office']->stand_meter !== null,
                'stand'    => isset($todayWaterPa['pantry_office']) ? number_format($todayWaterPa['pantry_office']->stand_meter ?? 0, 2, ',', '.') . " m³" : null,
            ],
            'lvsb' => [
                'recorded' => $todayLvsb !== null,
                'user'     => $todayLvsb?->user?->name,
                'time'     => $todayLvsb?->updated_at?->format('H:i'),
            ],
            'mvsb' => [
                'recorded' => $todayMvsb !== null,
                'stand'    => $todayMvsb ? number_format($todayMvsb->voltage_20kv ?? 0, 2, ',', '.') . " kV · " . number_format($todayMvsb->current ?? 0, 2, ',', '.') . " A" : null,
                'user'     => $todayMvsb?->user?->name,
                'time'     => $todayMvsb?->updated_at?->format('H:i'),
            ],
            'daily_logs_count' => $todayDailyLogsCount,
        ];

        // 7. Data Chart Harian
        $chart = [
            'labels' => $meterReadings->map(fn ($r) => $r->tanggal->format('d'))->values(),
            'lwbp'   => $meterReadings->pluck('pakai_lwbp')->values(),
            'wbp'    => $meterReadings->pluck('pakai_wbp')->values(),
            'air'    => $meterReadings->pluck('pakai_air')->values(),
        ];

        return view('gm.monitoring', compact(
            'periode', 'year', 'month',
            'meterReadings', 'summaryGedung',
            'waterPaReadings', 'waterPaByMeter', 'totalWaterPa',
            'lvsbReadings', 'lvsbSummary',
            'mvsbReadings', 'mvsbSummary',
            'dailyLogs', 'dailyLogStats',
            'todayPulse', 'chart'
        ));
    }
}
