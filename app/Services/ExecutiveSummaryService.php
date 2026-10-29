<?php

namespace App\Services;

use App\Models\DailyLogBook;
use App\Models\MeterReading;
use App\Models\PublicAreaLvsbReading;
use App\Models\PublicAreaMeterReading;
use App\Models\PublicAreaMvsbReading;
use App\Models\WhatsappSetting;
use Carbon\Carbon;

class ExecutiveSummaryService
{
    /**
     * Nama hari dalam Bahasa Indonesia
     */
    protected array $daysIndo = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
    ];

    /**
     * Nama bulan dalam Bahasa Indonesia
     */
    protected array $monthsIndo = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Mengumpulkan data lengkap status logsheet dan ringkasan eksekutif untuk tanggal tertentu.
     *
     * @param Carbon|string|null $date
     * @return array
     */
    public function getData($date = null): array
    {
        $carbonDate = $date ? Carbon::parse($date)->timezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $dateString = $carbonDate->toDateString();
        $year       = (int) $carbonDate->format('Y');
        $month      = (int) $carbonDate->format('m');

        // 1. Data Gedung (AMR PLN & Air PDAM)
        $todayGedung = MeterReading::with(['user', 'verifier'])
            ->whereDate('tanggal', $dateString)
            ->first();

        $isElectricRecorded = $todayGedung && (
            $todayGedung->stand_total !== null ||
            $todayGedung->stand_bp !== null ||
            ($todayGedung->stand_lwbp !== null && $todayGedung->stand_wbp !== null) ||
            $todayGedung->foto_kwh
        );

        $isWaterRecorded = $todayGedung && (
            $todayGedung->stand_air !== null ||
            $todayGedung->foto_air
        );

        // Kalkulasi pemakaian harian jika tercatat
        $prevElectric = $isElectricRecorded ? MeterReading::previousElectricOf($dateString) : null;
        $prevWater    = $isWaterRecorded ? MeterReading::previousWaterOf($dateString) : null;

        $pakaiKwhToday = null;
        if ($isElectricRecorded && $todayGedung) {
            if ($todayGedung->stand_total !== null && $prevElectric && $prevElectric->stand_total !== null) {
                $pakaiKwhToday = round($todayGedung->stand_total - $prevElectric->stand_total, 2);
            } elseif ($todayGedung->stand_bp !== null || $todayGedung->stand_lbp1 !== null) {
                $bp = ($todayGedung->stand_bp !== null && $prevElectric && $prevElectric->stand_bp !== null) ? ($todayGedung->stand_bp - $prevElectric->stand_bp) : 0;
                $lbp1 = ($todayGedung->stand_lbp1 !== null && $prevElectric && $prevElectric->stand_lbp1 !== null) ? ($todayGedung->stand_lbp1 - $prevElectric->stand_lbp1) : 0;
                $lbp2 = ($todayGedung->stand_lbp2 !== null && $prevElectric && $prevElectric->stand_lbp2 !== null) ? ($todayGedung->stand_lbp2 - $prevElectric->stand_lbp2) : 0;
                $pakaiKwhToday = round($bp + $lbp1 + $lbp2, 2);
            } elseif ($todayGedung->stand_lwbp !== null && $todayGedung->stand_wbp !== null && $prevElectric && $prevElectric->stand_lwbp !== null && $prevElectric->stand_wbp !== null) {
                $pakaiKwhToday = round(($todayGedung->stand_lwbp - $prevElectric->stand_lwbp) + ($todayGedung->stand_wbp - $prevElectric->stand_wbp), 2);
            }
        }

        $pakaiAirToday = null;
        if ($isWaterRecorded && $todayGedung && $todayGedung->stand_air !== null && $prevWater && $prevWater->stand_air !== null) {
            $pakaiAirToday = round($todayGedung->stand_air - $prevWater->stand_air, 2);
        }

        // Akumulasi bulanan gedung (MTD)
        $monthReadings = MeterReading::monthWithUsage($year, $month);
        $mtdKwh = round($monthReadings->sum('pakai_kwh'), 2);
        $mtdAir = round($monthReadings->sum('pakai_air'), 2);
        $mtdCount = $monthReadings->count();

        // 2. Data Water Meter Public Area (3 meter)
        $waterMetersDef = PublicAreaMeterReading::waterMeters();
        $todayPaWater = PublicAreaMeterReading::with(['user'])
            ->where('category', 'water')
            ->whereDate('tanggal', $dateString)
            ->get()
            ->keyBy('meter_key');

        $paWaterDetails = [];
        $paWaterRecordedCount = 0;
        $paWaterTotalToday = 0;
        $unrecordedPaWaterNames = [];

        foreach ($waterMetersDef as $key => $meta) {
            $reading = $todayPaWater->get($key);
            $recorded = $reading && ($reading->stand_meter !== null || $reading->foto);
            $pemakaian = null;

            if ($recorded) {
                $paWaterRecordedCount++;
                $prev = PublicAreaMeterReading::previousOf($dateString, 'water', $key);
                if ($reading->stand_meter !== null && $prev && $prev->stand_meter !== null) {
                    $pemakaian = round($reading->stand_meter - $prev->stand_meter, 2);
                    $paWaterTotalToday += $pemakaian;
                }
            } else {
                $unrecordedPaWaterNames[] = $meta['label'];
            }

            $paWaterDetails[$key] = [
                'meta'        => $meta,
                'recorded'    => $recorded,
                'stand'       => $reading?->stand_meter,
                'pemakaian'   => $pemakaian,
                'user'        => $reading?->user?->name,
                'time'        => $reading?->updated_at?->timezone('Asia/Jakarta')->format('H:i'),
            ];
        }

        // 3. Data Panel LVSB
        $todayLvsb = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->whereDate('tanggal', $dateString)
            ->first();
        $isLvsbRecorded = $todayLvsb !== null;

        // 4. Data Panel MVSB 20kV
        $todayMvsb = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->whereDate('tanggal', $dateString)
            ->first();
        $isMvsbRecorded = $todayMvsb !== null;

        // 5. Data Daily Log Book (Pekerjaan Teknisi)
        $todayDailyLogs = DailyLogBook::with('user')
            ->whereDate('tanggal', $dateString)
            ->orderBy('id', 'desc')
            ->get();
        $dailyLogCount    = $todayDailyLogs->count();
        $dailyLogDone     = $todayDailyLogs->where('status', 'Done')->count();
        $dailyLogContinue = $todayDailyLogs->where('status', 'Continue')->count();
        $isDailyLogRecorded = $dailyLogCount > 0;

        // 6. Evaluasi Daftar Logsheet yang BELUM Dicatat (Reminder Checklist)
        $unrecordedItems = [];

        if (! $isElectricRecorded) {
            $unrecordedItems[] = 'Listrik AMR PLN Gedung';
        }
        if (! $isWaterRecorded) {
            $unrecordedItems[] = 'Air PDAM Gedung';
        }
        if ($paWaterRecordedCount < count($waterMetersDef)) {
            $countMissing = count($waterMetersDef) - $paWaterRecordedCount;
            $missingList = implode(', ', $unrecordedPaWaterNames);
            $unrecordedItems[] = "Water Public Area ({$countMissing}/3 belum: {$missingList})";
        }
        if (! $isLvsbRecorded) {
            $unrecordedItems[] = 'Panel LVSB (Low Voltage Switchboard)';
        }
        if (! $isMvsbRecorded) {
            $unrecordedItems[] = 'Panel MVSB (20kV Incoming PLN)';
        }
        if (! $isDailyLogRecorded) {
            $unrecordedItems[] = 'Daily Log Book (Belum ada catatan pekerjaan teknisi)';
        }

        $allCompleted = empty($unrecordedItems);

        return [
            'carbon_date'             => $carbonDate,
            'date_string'             => $dateString,
            'formatted_date'          => $this->formatIndonesianDate($carbonDate),
            'all_completed'           => $allCompleted,
            'unrecorded_items'        => $unrecordedItems,
            'unrecorded_count'        => count($unrecordedItems),
            // Gedung
            'is_electric_recorded'    => $isElectricRecorded,
            'today_gedung'            => $todayGedung,
            'pakai_kwh_today'         => $pakaiKwhToday,
            'is_water_recorded'       => $isWaterRecorded,
            'pakai_air_today'         => $pakaiAirToday,
            'mtd_kwh'                 => $mtdKwh,
            'mtd_air'                 => $mtdAir,
            'mtd_count'               => $mtdCount,
            // Public Area Water
            'pa_water_details'        => $paWaterDetails,
            'pa_water_recorded_count' => $paWaterRecordedCount,
            'pa_water_total_meters'   => count($waterMetersDef),
            'pa_water_total_today'    => round($paWaterTotalToday, 2),
            'unrecorded_pa_water'     => $unrecordedPaWaterNames,
            // LVSB & MVSB
            'is_lvsb_recorded'        => $isLvsbRecorded,
            'today_lvsb'              => $todayLvsb,
            'is_mvsb_recorded'        => $isMvsbRecorded,
            'today_mvsb'              => $todayMvsb,
            // Daily Log Book
            'is_daily_log_recorded'   => $isDailyLogRecorded,
            'daily_log_count'         => $dailyLogCount,
            'daily_log_done'          => $dailyLogDone,
            'daily_log_continue'      => $dailyLogContinue,
            'recent_daily_logs'       => $todayDailyLogs->take(3),
        ];
    }

    /**
     * Memformat data ringkasan harian menjadi teks pesan WhatsApp yang rapi dan elegan.
     *
     * @param Carbon|string|null $date
     * @return string
     */
    public function formatWhatsAppMessage($date = null): string
    {
        $d = $this->getData($date);
        $appUrl = rtrim(config('app.url', 'http://localhost'), '/');
        $setting = WhatsappSetting::getSetting();
        $timeStr = ($setting->scheduled_time ?: '18:00') . ' WIB';

        $lines = [];

        // ── Header ──
        if (! empty($setting->header_template)) {
            $header = str_replace(
                ['{tanggal}', '{waktu}', '{date}', '{time}'],
                [$d['formatted_date'], $timeStr, $d['formatted_date'], $timeStr],
                $setting->header_template
            );
            $lines[] = $header;
        } else {
            $lines[] = '🏢 *KPR TOWER - EXECUTIVE SUMMARY & LOGSHEET PULSE*';
            $lines[] = "📅 *{$d['formatted_date']}* | ⏰ *{$timeStr}*";
        }
        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';

        // ── Section 1: Reminder & Checklist Logsheet ──
        if (! $d['all_completed']) {
            $lines[] = "⚠️ *PERINGATAN: LOGSHEET BELUM LENGKAP ({$d['unrecorded_count']} Kategori)*";
            $lines[] = "Hingga pukul {$timeStr}, pencatatan berikut *belum diinput*:";
            foreach ($d['unrecorded_items'] as $item) {
                $lines[] = " • ❌ {$item}";
            }
            $lines[] = '';
            $reminderText = $setting->reminder_template ?: '👉 _Mohon teknisi / tim engineering on-duty segera melengkapi logsheet di aplikasi KPRBeta sebelum akhir shift._';
            $lines[] = $reminderText;
        } else {
            $lines[] = '✅ *STATUS PENCATATAN LOGSHEET: LENGKAP*';
            $successText = $setting->success_template ?: 'Seluruh parameter logsheet hari ini telah berhasil dicatat oleh tim on-duty.';
            $lines[] = $successText;
        }

        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';

        // ── Section 2: Status Cepat Checklist ──
        $lines[] = '📋 *PULSE PENCATATAN HARI INI:*';

        // Listrik PLN
        if ($d['is_electric_recorded']) {
            $user = $d['today_gedung']?->user?->name ?? 'Teknisi';
            $time = $d['today_gedung']?->updated_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-';
            $pakai = $d['pakai_kwh_today'] !== null ? number_format($d['pakai_kwh_today'], 2, ',', '.') . ' kWh' : 'Tercatat';
            $lines[] = "• ⚡ *Listrik PLN:* ✅ {$pakai} _({$time} WIB oleh {$user})_";
        } else {
            $lines[] = '• ⚡ *Listrik PLN:* ❌ _Belum dicatat_';
        }

        // Air PDAM
        if ($d['is_water_recorded']) {
            $user = $d['today_gedung']?->user?->name ?? 'Teknisi';
            $time = $d['today_gedung']?->updated_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-';
            $pakai = $d['pakai_air_today'] !== null ? number_format($d['pakai_air_today'], 2, ',', '.') . ' m³' : 'Tercatat';
            $lines[] = "• 💧 *Air PDAM:* ✅ {$pakai} _({$time} WIB oleh {$user})_";
        } else {
            $lines[] = '• 💧 *Air PDAM:* ❌ _Belum dicatat_';
        }

        // Water Public Area
        $paCount = $d['pa_water_recorded_count'];
        $paTotal = $d['pa_water_total_meters'];
        if ($paCount === $paTotal) {
            $lines[] = "• 🌊 *Water Public Area:* ✅ {$paCount}/{$paTotal} Meter Lengkap (Total: " . number_format($d['pa_water_total_today'], 2, ',', '.') . ' m³)';
        } elseif ($paCount > 0) {
            $lines[] = "• 🌊 *Water Public Area:* ⚠️ Sebagian ({$paCount}/{$paTotal} terisi)";
        } else {
            $lines[] = '• 🌊 *Water Public Area:* ❌ _Belum dicatat (0/3)_';
        }

        // Panel LVSB
        if ($d['is_lvsb_recorded']) {
            $user = $d['today_lvsb']?->user?->name ?? 'Teknisi';
            $time = $d['today_lvsb']?->updated_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-';
            $lvsbReadings = $d['today_lvsb']->readings ?? [];
            $trafo = $lvsbReadings['incoming_trafo'] ?? [];
            $lvsbExtra = '';
            if (! empty($trafo['voltage_3p']) || ! empty($trafo['current'])) {
                $v = isset($trafo['voltage_3p']) ? number_format($trafo['voltage_3p'], 1, ',', '.') . ' V' : '';
                $a = isset($trafo['current']) ? number_format($trafo['current'], 1, ',', '.') . ' A' : '';
                $pf = isset($trafo['power_factor']) ? 'PF ' . number_format($trafo['power_factor'], 2, ',', '.') : '';
                $lvsbExtra = ' (' . implode(' | ', array_filter([$v, $a, $pf])) . ')';
            }
            $lines[] = "• 🎛️ *Panel LVSB:* ✅ Dicatat{$lvsbExtra} _({$time} WIB oleh {$user})_";
        } else {
            $lines[] = '• 🎛️ *Panel LVSB:* ❌ _Belum dicatat_';
        }

        // Panel MVSB
        if ($d['is_mvsb_recorded']) {
            $user = $d['today_mvsb']?->user?->name ?? 'Teknisi';
            $time = $d['today_mvsb']?->updated_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-';
            $rawVolt = $d['today_mvsb']->voltage_20kv;
            if ($rawVolt !== null) {
                $volt = $rawVolt >= 1000 
                    ? number_format($rawVolt / 1000, 2, ',', '.') . ' kV'
                    : number_format($rawVolt, 1, ',', '.') . ' kV';
            } else {
                $volt = '-';
            }
            $amp = $d['today_mvsb']->current !== null ? number_format($d['today_mvsb']->current, 1, ',', '.') . ' A' : '-';
            $lines[] = "• 🔌 *Panel MVSB 20kV:* ✅ {$volt} | {$amp} _({$time} WIB oleh {$user})_";
        } else {
            $lines[] = '• 🔌 *Panel MVSB 20kV:* ❌ _Belum dicatat_';
        }

        // Daily Log Book
        if ($d['is_daily_log_recorded']) {
            $lines[] = "• 📝 *Daily Log Book:* ✅ {$d['daily_log_count']} Kegiatan ({$d['daily_log_done']} Done, {$d['daily_log_continue']} Continue)";
        } else {
            $lines[] = '• 📝 *Daily Log Book:* ⚠️ _Belum ada pekerjaan diinput_';
        }

        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';

        // ── Section 3: Executive Summary Operasional & Energi ──
        $lines[] = '📊 *RINGKASAN ENERGI & UTILITAS (EXECUTIVE SUMMARY):*';

        // Detail Listrik Gedung
        if ($d['is_electric_recorded'] && $d['pakai_kwh_today'] !== null) {
            $lines[] = '⚡ *Listrik Gedung Hari Ini:* ' . number_format($d['pakai_kwh_today'], 2, ',', '.') . ' kWh';
        } else {
            $lines[] = '⚡ *Listrik Gedung Hari Ini:* _(Menunggu input stand meter)_';
        }
        if ($d['mtd_kwh'] > 0) {
            $avgKwh = $d['mtd_count'] > 0 ? round($d['mtd_kwh'] / $d['mtd_count'], 2) : 0;
            $lines[] = '   ↳ *Akumulasi Bulan Ini (MTD):* ' . number_format($d['mtd_kwh'], 2, ',', '.') . ' kWh (Rata-rata: ' . number_format($avgKwh, 1, ',', '.') . ' kWh/hari)';
        }

        // Detail Air Gedung
        if ($d['is_water_recorded'] && $d['pakai_air_today'] !== null) {
            $lines[] = '💧 *Air PDAM Hari Ini:* ' . number_format($d['pakai_air_today'], 2, ',', '.') . ' m³';
        } else {
            $lines[] = '💧 *Air PDAM Hari Ini:* _(Menunggu input stand meter)_';
        }
        if ($d['mtd_air'] > 0) {
            $lines[] = '   ↳ *Akumulasi Bulan Ini (MTD):* ' . number_format($d['mtd_air'], 2, ',', '.') . ' m³';
        }

        // Rincian Water Public Area jika ada yang tercatat
        if ($d['pa_water_recorded_count'] > 0) {
            $lines[] = '🌊 *Rincian Water Public Area:*';
            foreach ($d['pa_water_details'] as $item) {
                $label = $item['meta']['label'];
                if ($item['recorded']) {
                    $pem = $item['pemakaian'] !== null ? number_format($item['pemakaian'], 2, ',', '.') . ' m³' : 'Stand: ' . number_format($item['stand'] ?? 0, 2, ',', '.');
                    $lines[] = "   • {$label}: {$pem}";
                } else {
                    $lines[] = "   • {$label}: _Belum diisi_";
                }
            }
        }

        // Cuplikan Log Book Teknisi
        if ($d['daily_log_count'] > 0) {
            $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';
            $lines[] = "🛠️ *AKTIVITAS TEKNISI HARI INI ({$d['daily_log_count']} Tugas):*";
            foreach ($d['recent_daily_logs'] as $log) {
                $icon = $log->status === 'Done' ? '✅' : '⏳';
                $lokasi = $log->lokasi ? "[{$log->lokasi}] " : '';
                $deskripsi = mb_strimwidth($log->deskripsi, 0, 45, '...');
                $lines[] = " {$icon} {$lokasi}{$deskripsi} _({$log->user?->name})_";
            }
        }

        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';

        // ── Footer ──
        $lines[] = "📱 *Aplikasi KPRBeta:* {$appUrl}/dashboard";
        $lines[] = "📊 *GM Executive Monitoring:* {$appUrl}/gm/monitoring";
        $lines[] = '';
        $lines[] = $setting->footer_template ?: '_Notifikasi harian otomatis via Fonnte Gateway_';

        return implode("\n", $lines);
    }

    /**
     * Helper nama hari & tanggal bahasa Indonesia.
     */
    protected function formatIndonesianDate(Carbon $date): string
    {
        $dayName   = $this->daysIndo[$date->format('l')] ?? $date->format('l');
        $monthName = $this->monthsIndo[(int) $date->format('n')] ?? $date->format('F');

        return "{$dayName}, {$date->format('j')} {$monthName} {$date->format('Y')}";
    }
}
