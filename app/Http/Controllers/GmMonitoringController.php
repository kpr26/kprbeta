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

        // Parameter Tarif Listrik & Air
        $faktorMeter = 5000;
        $tarifLwbp   = 1035.78; // Rp 1.035,78 / kWh
        $tarifWbp    = 1553.67; // Rp 1.553,67 / kWh
        $pajakRate   = 0.024;   // Pajak Penerangan 2,4%
        $tarifAir    = 21500;   // Rp 21.500 / m³

        // Kalkulasi nilai aktual per hari pada setiap baris pencatatan
        foreach ($meterReadings as $row) {
            $rLbp1 = $row->pakai_lbp1 !== null ? (float) $row->pakai_lbp1 : null;
            $rLbp2 = $row->pakai_lbp2 !== null ? (float) $row->pakai_lbp2 : null;
            $rBp   = $row->pakai_bp !== null ? (float) $row->pakai_bp : ($row->pakai_wbp !== null ? (float) $row->pakai_wbp : null);

            // (LBP1 x 5000) + (LBP2 x 5000) = LWBP (kWh)
            if ($rLbp1 !== null || $rLbp2 !== null) {
                $row->actual_lwbp_kwh = round((($rLbp1 ?? 0) * $faktorMeter) + (($rLbp2 ?? 0) * $faktorMeter), 2);
            } elseif ($row->pakai_lwbp !== null) {
                $row->actual_lwbp_kwh = round($row->pakai_lwbp * $faktorMeter, 2);
            } else {
                $row->actual_lwbp_kwh = null;
            }

            // (BP x 5000) = WBP (kWh)
            if ($rBp !== null) {
                $row->actual_wbp_kwh = round($rBp * $faktorMeter, 2);
            } else {
                $row->actual_wbp_kwh = null;
            }

            // Total kWh, Biaya LWBP, Biaya WBP, Pajak Penerangan 2,4%, dan Total Rupiah
            if ($row->actual_lwbp_kwh !== null || $row->actual_wbp_kwh !== null) {
                $rowLwbpKwh = $row->actual_lwbp_kwh ?? 0;
                $rowWbpKwh  = $row->actual_wbp_kwh ?? 0;

                $row->actual_kwh        = round($rowLwbpKwh + $rowWbpKwh, 2);
                $row->actual_biaya_lwbp = round($rowLwbpKwh * $tarifLwbp, 2);
                $row->actual_biaya_wbp  = round($rowWbpKwh * $tarifWbp, 2);
                $row->actual_subtotal   = round($row->actual_biaya_lwbp + $row->actual_biaya_wbp, 2);
                $row->actual_ppj        = round($row->actual_subtotal * $pajakRate, 2);
                $row->actual_total_rp   = round($row->actual_subtotal + $row->actual_ppj, 2);
            } else {
                $row->actual_kwh        = null;
                $row->actual_biaya_lwbp = null;
                $row->actual_biaya_wbp  = null;
                $row->actual_subtotal   = null;
                $row->actual_ppj        = null;
                $row->actual_total_rp   = null;
            }
        }

        // Akumulasi bulanan
        $rawLbp1Sum = (float) $meterReadings->sum('pakai_lbp1');
        $rawLbp2Sum = (float) $meterReadings->sum('pakai_lbp2');
        $rawBpSum   = (float) $meterReadings->sum('pakai_bp');
        $rawLwbpSum = (float) $meterReadings->sum('pakai_lwbp');
        $rawWbpSum  = (float) $meterReadings->sum('pakai_wbp');

        // Nilai Aktual (LBP1 x 5000) + (LBP2 x 5000) = LWBP (kWh)
        if ($rawLbp1Sum > 0 || $rawLbp2Sum > 0) {
            $totalActualLwbp = round(($rawLbp1Sum * $faktorMeter) + ($rawLbp2Sum * $faktorMeter), 2);
        } else {
            $totalActualLwbp = round($rawLwbpSum * $faktorMeter, 2);
        }

        // Nilai Aktual (BP x 5000) = WBP (kWh)
        if ($rawBpSum > 0) {
            $totalActualWbp = round($rawBpSum * $faktorMeter, 2);
        } else {
            $totalActualWbp = round($rawWbpSum * $faktorMeter, 2);
        }

        // Pemakaian Total: LWBP + WBP (kWh)
        $totalActualKwh = round($totalActualLwbp + $totalActualWbp, 2);

        // Biaya dalam Rupiah
        $totalBiayaLwbp = round($totalActualLwbp * $tarifLwbp, 2);
        $totalBiayaWbp  = round($totalActualWbp * $tarifWbp, 2);
        $subtotalBiaya  = round($totalBiayaLwbp + $totalBiayaWbp, 2);

        // Biaya Pajak Penerangan 2,4%
        $pajakPenerangan = round($subtotalBiaya * $pajakRate, 2);

        // Total Tagihan Listrik PLN (Subtotal + Pajak Penerangan)
        $totalBiayaPln = round($subtotalBiaya + $pajakPenerangan, 2);

        $jumlahHari      = $meterReadings->count();
        $rataActualKwh   = $jumlahHari > 0 ? round($totalActualKwh / $jumlahHari, 2) : 0;
        $rataActualRp    = $jumlahHari > 0 ? round($totalBiayaPln / $jumlahHari, 2) : 0;
        $rataSubtotalRp  = $jumlahHari > 0 ? round($subtotalBiaya / $jumlahHari, 2) : 0;

        $persenLwbp      = $totalActualKwh > 0 ? round(($totalActualLwbp / $totalActualKwh) * 100, 1) : 0;
        $persenWbp       = $totalActualKwh > 0 ? round(($totalActualWbp / $totalActualKwh) * 100, 1) : 0;
        $persenBiayaLwbp = $subtotalBiaya > 0 ? round(($totalBiayaLwbp / $subtotalBiaya) * 100, 1) : 0;
        $persenBiayaWbp  = $subtotalBiaya > 0 ? round(($totalBiayaWbp / $subtotalBiaya) * 100, 1) : 0;

        // Perhitungan Biaya Air Gedung Utama (Tarif Rp 21.500 / m³)
        $totalAirGedung = (float) $meterReadings->sum('pakai_air');
        $biayaAirGedung = round($totalAirGedung * $tarifAir, 2);
        $rataAir        = $jumlahHari > 0 ? round($totalAirGedung / $jumlahHari, 2) : 0;
        $rataBiayaAir   = $jumlahHari > 0 ? round($biayaAirGedung / $jumlahHari, 2) : 0;

        // Grand Total OpEx Utilitas Gedung (Listrik PLN + Air Gedung Utama)
        $grandTotalUtilitas = round($totalBiayaPln + $biayaAirGedung, 2);
        $rataGrandTotal     = $jumlahHari > 0 ? round($grandTotalUtilitas / $jumlahHari, 2) : 0;
        $persenBiayaListrik = $grandTotalUtilitas > 0 ? round(($totalBiayaPln / $grandTotalUtilitas) * 100, 1) : 0;
        $persenBiayaAir     = $grandTotalUtilitas > 0 ? round(($biayaAirGedung / $grandTotalUtilitas) * 100, 1) : 0;

        $summaryGedung = [
            // Parameter & Pengaturan
            'faktor_meter'      => $faktorMeter,
            'tarif_lwbp'        => $tarifLwbp,
            'tarif_wbp'         => $tarifWbp,
            'pajak_rate'        => $pajakRate,
            'pajak_persen'      => 2.4,
            'tarif_air'         => $tarifAir,

            // Stand / Delta Mentah Meteran
            'raw_lbp1'          => $rawLbp1Sum,
            'raw_lbp2'          => $rawLbp2Sum,
            'raw_bp'            => $rawBpSum,
            'raw_lwbp'          => $rawLwbpSum,
            'raw_wbp'           => $rawWbpSum,
            'raw_kwh'           => (float) $meterReadings->sum('pakai_kwh'),

            // Nilai Aktual (kWh)
            'lwbp'              => $totalActualLwbp,
            'wbp'               => $totalActualWbp,
            'kwh'               => $totalActualKwh,

            // Nilai Finansial Listrik (Rupiah)
            'biaya_lwbp'        => $totalBiayaLwbp,
            'biaya_wbp'         => $totalBiayaWbp,
            'subtotal_rupiah'   => $subtotalBiaya,
            'pajak_penerangan'  => $pajakPenerangan,
            'total_rupiah'      => $totalBiayaPln,

            // Nilai Air & Finansial Air (Rupiah)
            'air'               => $totalAirGedung,
            'biaya_air'         => $biayaAirGedung,
            'rata_air'          => $rataAir,
            'rata_biaya_air'    => $rataBiayaAir,

            // Grand Total Biaya Utilitas (Listrik + Air)
            'grand_total_utilitas' => $grandTotalUtilitas,
            'rata_grand_total'     => $rataGrandTotal,
            'persen_biaya_listrik' => $persenBiayaListrik,
            'persen_biaya_air'     => $persenBiayaAir,

            // Statistik & Rasio
            'jumlah_hari'       => $jumlahHari,
            'rata_kwh'          => $rataActualKwh,
            'rata_rupiah'       => $rataActualRp,
            'rata_subtotal_rp'  => $rataSubtotalRp,
            'persen_lwbp'       => $persenLwbp,
            'persen_wbp'        => $persenWbp,
            'persen_biaya_lwbp' => $persenBiayaLwbp,
            'persen_biaya_wbp'  => $persenBiayaWbp,

            // Integritas
            'terverifikasi'     => $meterReadings->filter(fn ($r) => $r->isVerified())->count(),
            'pending'           => $meterReadings->filter(fn ($r) => $r->needsVerification())->count(),
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
                'biaya'       => round($totalPakai * $tarifAir, 2),
                'count'       => $filtered->count(),
                'latest'      => $filtered->last(),
            ];
        }
        $totalWaterPa = round($waterPaReadings->sum('pemakaian'), 2);
        $biayaWaterPa = round($totalWaterPa * $tarifAir, 2);

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

        // 7. Data Chart Harian (Nilai Aktual kWh dengan Faktor Meter 5.000x & Rupiah)
        $chart = [
            'labels'      => $meterReadings->map(fn ($r) => $r->tanggal->format('d'))->values(),
            'lwbp'        => $meterReadings->map(fn ($r) => $r->actual_lwbp_kwh ?? ($r->pakai_lwbp !== null ? round($r->pakai_lwbp * $faktorMeter, 2) : 0))->values(),
            'wbp'         => $meterReadings->map(fn ($r) => $r->actual_wbp_kwh ?? ($r->pakai_wbp !== null ? round($r->pakai_wbp * $faktorMeter, 2) : 0))->values(),
            'total_kwh'   => $meterReadings->map(fn ($r) => $r->actual_kwh ?? ($r->pakai_kwh !== null ? round($r->pakai_kwh * $faktorMeter, 2) : 0))->values(),
            'biaya_total' => $meterReadings->map(fn ($r) => $r->actual_total_rp ?? 0)->values(),
            'air'         => $meterReadings->pluck('pakai_air')->values(),
            'biaya_air'   => $meterReadings->map(fn ($r) => $r->pakai_air !== null ? round($r->pakai_air * $tarifAir, 2) : 0)->values(),
        ];

        return view('gm.monitoring', compact(
            'periode', 'year', 'month',
            'meterReadings', 'summaryGedung',
            'waterPaReadings', 'waterPaByMeter', 'totalWaterPa', 'biayaWaterPa',
            'lvsbReadings', 'lvsbSummary',
            'mvsbReadings', 'mvsbSummary',
            'dailyLogs', 'dailyLogStats',
            'todayPulse', 'chart'
        ));
    }
}
