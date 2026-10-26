<?php

namespace App\Http\Controllers;

use App\Models\PublicAreaMeterReading;
use App\Services\ExcelReaderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicAreaLogsheetController extends Controller
{
    /**
     * Hub Utama Menu Logsheet Public Area
     * Menampilkan 2 Sub Menu: Water Meter Public Area & KWh Meter Public Area
     */
    public function hub(): View
    {
        $waterMeters = PublicAreaMeterReading::waterMeters();
        $elecMeters  = PublicAreaMeterReading::electricityMeters();

        $today = today()->toDateString();

        // Ambil data hari ini untuk masing-masing meteran air
        $todayWater = PublicAreaMeterReading::where('category', 'water')
            ->whereDate('tanggal', $today)
            ->get()
            ->keyBy('meter_key');

        // Ambil data hari ini untuk LVSB
        $todayLvsb = \App\Models\PublicAreaLvsbReading::whereDate('tanggal', $today)->first();

        // Ambil data hari ini untuk MVSB Incoming From PLN
        $todayMvsb = \App\Models\PublicAreaMvsbReading::whereDate('tanggal', $today)->first();

        // Stand terakhir sebelumnya
        $prevWater = [];
        foreach ($waterMeters as $key => $meta) {
            $prevWater[$key] = PublicAreaMeterReading::previousOf($today, 'water', $key);
        }

        $waterCountDone = $todayWater->count();
        $waterTotalCount = count($waterMeters);

        return view('public-area.hub', compact(
            'waterMeters',
            'elecMeters',
            'todayWater',
            'todayLvsb',
            'todayMvsb',
            'prevWater',
            'waterCountDone',
            'waterTotalCount'
        ));
    }

    /**
     * Parse interval tanggal (maksimal 12 bulan / 366 hari)
     */
    protected function parseInterval(Request $request): array
    {
        $today = Carbon::today();
        $preset = $request->input('preset');

        if ($preset === '3m') {
            $startDate = $today->copy()->subMonths(3)->startOfDay();
            $endDate   = $today->copy()->endOfDay();
        } elseif ($preset === '6m') {
            $startDate = $today->copy()->subMonths(6)->startOfDay();
            $endDate   = $today->copy()->endOfDay();
        } elseif ($preset === '12m' || $preset === '1y') {
            $startDate = $today->copy()->subYear()->startOfDay();
            $endDate   = $today->copy()->endOfDay();
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate   = Carbon::parse($request->input('end_date'))->endOfDay();
        } elseif ($request->filled('periode') || $request->filled('period')) {
            $p = $request->input('periode') ?: $request->input('period');
            try {
                $d = Carbon::createFromFormat('Y-m', $p);
                $startDate = $d->copy()->startOfMonth();
                $endDate   = $d->copy()->endOfMonth();
            } catch (\Exception $e) {
                $startDate = $today->copy()->startOfMonth();
                $endDate   = $today->copy()->endOfMonth();
            }
        } elseif ($request->filled('year') && $request->filled('month')) {
            $d = Carbon::createFromDate((int)$request->input('year'), (int)$request->input('month'), 1);
            $startDate = $d->copy()->startOfMonth();
            $endDate   = $d->copy()->endOfMonth();
        } else {
            // Default: Bulan ini
            $startDate = $today->copy()->startOfMonth();
            $endDate   = $today->copy()->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            $tmp = $startDate;
            $startDate = $endDate->copy()->startOfDay();
            $endDate = $tmp->endOfDay();
        }

        // Batasi rentang maksimal 12 bulan (366 hari)
        if ($startDate->diffInDays($endDate) > 366) {
            $startDate = $endDate->copy()->subDays(366)->startOfDay();
        }

        return [$startDate, $endDate];
    }

    /**
     * Sub Menu 1: Water Meter Public Area (Index & Rekapitulasi)
     */
    public function waterIndex(Request $request): View
    {
        $waterMeters = PublicAreaMeterReading::waterMeters();
        $todayStr = today()->toDateString();

        [$startDate, $endDate] = $this->parseInterval($request);
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();
        $preset = $request->input('preset', '');
        $periode = $startDate->format('Y-m');
        $year = (int) $startDate->year;
        $month = (int) $startDate->month;

        // Status hari ini untuk masing-masing dari 3 water meter
        $todayReadings = PublicAreaMeterReading::where('category', 'water')
            ->whereDate('tanggal', $todayStr)
            ->get()
            ->keyBy('meter_key');

        $prevReadings = [];
        foreach ($waterMeters as $key => $meta) {
            $prevReadings[$key] = PublicAreaMeterReading::previousOf($todayStr, 'water', $key);
        }

        // Data rekapitulasi interval beserta pemakaian
        $rows = PublicAreaMeterReading::intervalWithUsage($startDateStr, $endDateStr, 'water');

        // Ringkasan per meter untuk periode ini
        $summaryPerMeter = [];
        $grandTotalUsage = 0;
        foreach ($waterMeters as $key => $meta) {
            $meterRows = $rows->where('meter_key', $key);
            $usages = $meterRows->pluck('pemakaian')->filter(fn ($v) => $v !== null && $v > 0);
            $totalUsage = $meterRows->sum(fn ($r) => $r->pemakaian ?? 0);
            $grandTotalUsage += $totalUsage;
            $summaryPerMeter[$key] = [
                'count'      => $meterRows->count(),
                'totalUsage' => round($totalUsage, 2),
                'avgUsage'   => $usages->count() > 0 ? round($usages->avg(), 2) : 0,
                'maxUsage'   => $usages->count() > 0 ? round($usages->max(), 2) : 0,
                'minUsage'   => $usages->count() > 0 ? round($usages->min(), 2) : 0,
            ];
        }

        // Chart Data Harian & Kumulatif
        $dates = $rows->pluck('tanggal')->map(fn($d) => $d->format('Y-m-d'))->unique()->sort()->values();
        $chartDates = [];
        $chartCT = [];
        $chartSL = [];
        $chartPO = [];
        $chartTotal = [];
        $chartCum = [];
        $runningCum = 0;

        foreach ($dates as $d) {
            $dayRows = $rows->filter(fn($r) => $r->tanggal->format('Y-m-d') === $d);
            $uCT = (float) ($dayRows->firstWhere('meter_key', 'cooling_tower')?->pemakaian ?? 0);
            $uSL = (float) ($dayRows->firstWhere('meter_key', 'toilet_sky_lobby')?->pemakaian ?? 0);
            $uPO = (float) ($dayRows->firstWhere('meter_key', 'pantry_office')?->pemakaian ?? 0);
            $dayTotal = round($uCT + $uSL + $uPO, 2);
            $runningCum += $dayTotal;

            $chartDates[] = Carbon::parse($d)->translatedFormat('d M');
            $chartCT[] = $uCT;
            $chartSL[] = $uSL;
            $chartPO[] = $uPO;
            $chartTotal[] = $dayTotal;
            $chartCum[] = round($runningCum, 2);
        }

        $chartData = [
            'labels'        => $chartDates,
            'cooling_tower' => $chartCT,
            'toilet_sky'    => $chartSL,
            'pantry_office' => $chartPO,
            'total_daily'   => $chartTotal,
            'cum_usage'     => $chartCum,
        ];

        return view('public-area.water.index', compact(
            'waterMeters',
            'todayReadings',
            'prevReadings',
            'rows',
            'year',
            'month',
            'periode',
            'startDate',
            'endDate',
            'preset',
            'summaryPerMeter',
            'grandTotalUsage',
            'chartData'
        ));
    }

    /**
     * Form Input Khusus untuk 1 Water Meter Public Area
     */
    public function waterCreate(Request $request, string $meterKey): View
    {
        $waterMeters = PublicAreaMeterReading::waterMeters();
        if (! isset($waterMeters[$meterKey])) {
            abort(404, 'Water meter public area tidak ditemukan.');
        }

        $meter = $waterMeters[$meterKey];
        $todayStr = today()->toDateString();

        $reading = PublicAreaMeterReading::where('category', 'water')
            ->where('meter_key', $meterKey)
            ->whereDate('tanggal', $todayStr)
            ->first() ?? new PublicAreaMeterReading(['category' => 'water', 'meter_key' => $meterKey, 'tanggal' => today()]);

        $last = PublicAreaMeterReading::previousOf($todayStr, 'water', $meterKey);

        return view('public-area.water.create', compact('meter', 'meterKey', 'reading', 'last'));
    }

    /**
     * Simpan Input Stand Water Meter Public Area (Dukungan Online & Offline Sync)
     */
    public function waterStore(Request $request): RedirectResponse|JsonResponse
    {
        $canManage = in_array(auth()->user()->role, ['admin', 'superadmin', 'eng_admin', 'eng_spv'], true);
        $waterMeters = PublicAreaMeterReading::waterMeters();

        $data = $request->validate([
            'meter_key'          => 'required|string|in:' . implode(',', array_keys($waterMeters)),
            'stand_meter'        => 'required|numeric|min:0',
            'foto'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'catatan'            => 'nullable|string|max:1000',
            'tanggal_pencatatan' => 'nullable|date',
        ], [
            'stand_meter.required' => 'Angka stand water meter wajib diisi.',
            'stand_meter.numeric'  => 'Angka stand meter harus berupa nilai angka.',
            'foto.image'           => 'File harus berupa gambar (jpg, png, webp).',
            'foto.max'             => 'Ukuran foto maksimal 5 MB.',
        ]);

        $meterMeta = $waterMeters[$data['meter_key']];
        $targetDate = $data['tanggal_pencatatan'] ?? today()->toDateString();

        // Validasi pembatasan teknisi (hanya boleh input hari ini)
        if (! $canManage && $targetDate !== today()->toDateString()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Teknisi hanya dapat mencatat untuk tanggal hari ini.'], 403);
            }
            return back()->withInput()->withErrors(['tanggal_pencatatan' => 'Teknisi hanya dapat mencatat untuk tanggal hari ini.']);
        }

        $existing = PublicAreaMeterReading::where('category', 'water')
            ->where('meter_key', $data['meter_key'])
            ->whereDate('tanggal', $targetDate)
            ->first();

        // Foto wajib jika pencatatan baru
        if (! $existing && ! $request->hasFile('foto')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Foto fisik meteran wajib dilampirkan.'], 422);
            }
            return back()->withInput()->withErrors(['foto' => 'Foto fisik meteran wajib dilampirkan sebagai bukti.']);
        }

        // Validasi stand tidak boleh mundur dari pencatatan sebelumnya
        $prev = PublicAreaMeterReading::previousOf($targetDate, 'water', $data['meter_key']);
        if ($prev && $prev->stand_meter !== null && $data['stand_meter'] < $prev->stand_meter) {
            $msg = 'Stand tidak boleh lebih kecil dari stand sebelumnya (' . number_format($prev->stand_meter, 2, ',', '.') . ' m³).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->withInput()->withErrors(['stand_meter' => $msg]);
        }

        $reading = $existing ?? new PublicAreaMeterReading();
        $reading->tanggal     = $targetDate;
        $reading->category    = 'water';
        $reading->meter_key   = $data['meter_key'];
        $reading->meter_name  = $meterMeta['name'];
        $reading->unit        = $meterMeta['unit'] ?? 'm³';
        $reading->stand_meter = $data['stand_meter'];
        $reading->catatan     = $data['catatan'] ?? null;
        $reading->user_id     = auth()->id();

        $isTeknisi = auth()->user()->role === 'teknisi';
        $isToday   = $targetDate === today()->toDateString();
        if ($isTeknisi && $isToday) {
            if (! $existing || ! $reading->verified_at) {
                $reading->verified_at = null;
                $reading->verified_by = null;
            }
        } else {
            // Data input admin atau backdate otomatis terverifikasi
            $reading->verified_at = now();
            $reading->verified_by = auth()->id();
        }

        if ($request->hasFile('foto')) {
            if ($reading->foto) {
                Storage::disk('public')->delete($reading->foto);
            }
            $reading->foto = $request->file('foto')->store('public_area/water', 'public');
        }

        $reading->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pencatatan ' . $meterMeta['name'] . ' berhasil disimpan.',
                'target'  => route('public-area.water.index'),
                'data'    => $reading,
            ]);
        }

        return redirect()->route('public-area.water.index')
            ->with('status', 'Pencatatan ' . $meterMeta['name'] . ' berhasil disimpan!');
    }

    /**
     * Sub Menu 2: KWh Meter Public Area (Index & Rekapitulasi)
     */
    public function electricityIndex(Request $request): View
    {
        $elecMeters = PublicAreaMeterReading::electricityMeters();
        $todayStr = today()->toDateString();

        $periode = $request->query('periode', now()->format('Y-m'));
        try {
            $carbonPeriod = Carbon::createFromFormat('Y-m', $periode);
            $year  = (int) $carbonPeriod->year;
            $month = (int) $carbonPeriod->month;
        } catch (\Exception $e) {
            $year  = (int) now()->year;
            $month = (int) now()->month;
            $periode = now()->format('Y-m');
        }

        $todayReading = PublicAreaMeterReading::where('category', 'electricity')
            ->whereDate('tanggal', $todayStr)
            ->first();

        $prevReading = PublicAreaMeterReading::previousOf($todayStr, 'electricity', 'kwh_public_area');

        $rows = PublicAreaMeterReading::monthWithUsage($year, $month, 'electricity');
        $totalKwhUsage = round($rows->sum(fn ($r) => $r->pemakaian ?? 0), 2);

        return view('public-area.electricity.index', compact(
            'elecMeters',
            'todayReading',
            'prevReading',
            'rows',
            'year',
            'month',
            'periode',
            'totalKwhUsage'
        ));
    }

    /**
     * Form Input KWh Meter Public Area
     */
    public function electricityCreate(): View
    {
        $elecMeters = PublicAreaMeterReading::electricityMeters();
        $meter = $elecMeters['kwh_public_area'];
        $todayStr = today()->toDateString();

        $reading = PublicAreaMeterReading::where('category', 'electricity')
            ->whereDate('tanggal', $todayStr)
            ->first() ?? new PublicAreaMeterReading(['category' => 'electricity', 'meter_key' => 'kwh_public_area', 'tanggal' => today()]);

        $last = PublicAreaMeterReading::previousOf($todayStr, 'electricity', 'kwh_public_area');

        return view('public-area.electricity.create', compact('meter', 'reading', 'last'));
    }

    /**
     * Simpan Stand KWh Meter Public Area
     */
    public function electricityStore(Request $request): RedirectResponse|JsonResponse
    {
        $canManage = in_array(auth()->user()->role, ['admin', 'superadmin', 'eng_admin', 'eng_spv'], true);

        $data = $request->validate([
            'stand_meter'        => 'required|numeric|min:0',
            'foto'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'catatan'            => 'nullable|string|max:1000',
            'tanggal_pencatatan' => 'nullable|date',
        ], [
            'stand_meter.required' => 'Angka stand KWh meter wajib diisi.',
            'stand_meter.numeric'  => 'Angka stand meter harus berupa nilai angka.',
            'foto.image'           => 'File harus berupa gambar (jpg, png, webp).',
            'foto.max'             => 'Ukuran foto maksimal 5 MB.',
        ]);

        $targetDate = $data['tanggal_pencatatan'] ?? today()->toDateString();

        if (! $canManage && $targetDate !== today()->toDateString()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Teknisi hanya dapat mencatat untuk tanggal hari ini.'], 403);
            }
            return back()->withInput()->withErrors(['tanggal_pencatatan' => 'Teknisi hanya dapat mencatat untuk tanggal hari ini.']);
        }

        $existing = PublicAreaMeterReading::where('category', 'electricity')
            ->whereDate('tanggal', $targetDate)
            ->first();

        if (! $existing && ! $request->hasFile('foto')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Foto fisik meteran listrik wajib dilampirkan.'], 422);
            }
            return back()->withInput()->withErrors(['foto' => 'Foto fisik meteran listrik wajib dilampirkan sebagai bukti.']);
        }

        $prev = PublicAreaMeterReading::previousOf($targetDate, 'electricity', 'kwh_public_area');
        if ($prev && $prev->stand_meter !== null && $data['stand_meter'] < $prev->stand_meter) {
            $msg = 'Stand tidak boleh lebih kecil dari stand sebelumnya (' . number_format($prev->stand_meter, 2, ',', '.') . ' kWh).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->withInput()->withErrors(['stand_meter' => $msg]);
        }

        $reading = $existing ?? new PublicAreaMeterReading();
        $reading->tanggal     = $targetDate;
        $reading->category    = 'electricity';
        $reading->meter_key   = 'kwh_public_area';
        $reading->meter_name  = 'KWh Meter Public Area';
        $reading->unit        = 'kWh';
        $reading->stand_meter = $data['stand_meter'];
        $reading->catatan     = $data['catatan'] ?? null;
        $reading->user_id     = auth()->id();

        $isTeknisi = auth()->user()->role === 'teknisi';
        $isToday   = $targetDate === today()->toDateString();
        if ($isTeknisi && $isToday) {
            if (! $existing || ! $reading->verified_at) {
                $reading->verified_at = null;
                $reading->verified_by = null;
            }
        } else {
            // Data input admin atau backdate otomatis terverifikasi
            $reading->verified_at = now();
            $reading->verified_by = auth()->id();
        }

        if ($request->hasFile('foto')) {
            if ($reading->foto) {
                Storage::disk('public')->delete($reading->foto);
            }
            $reading->foto = $request->file('foto')->store('public_area/electricity', 'public');
        }

        $reading->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pencatatan KWh Meter Public Area berhasil disimpan.',
                'target'  => route('public-area.electricity.index'),
                'data'    => $reading,
            ]);
        }

        return redirect()->route('public-area.electricity.index')
            ->with('status', 'Pencatatan KWh Meter Public Area berhasil disimpan!');
    }

    /**
     * Detail Modal / Halaman Pencatatan
     */
    public function show(PublicAreaMeterReading $reading): View
    {
        $reading->load(['user', 'verifier']);
        $prev = PublicAreaMeterReading::previousOf($reading->tanggal->toDateString(), $reading->category, $reading->meter_key);
        $pemakaian = ($prev && $reading->stand_meter !== null && $prev->stand_meter !== null)
            ? round($reading->stand_meter - $prev->stand_meter, 2)
            : null;

        return view('public-area.show', compact('reading', 'prev', 'pemakaian'));
    }

    /**
     * Verifikasi oleh Supervisor / Admin
     */
    public function verify(PublicAreaMeterReading $reading): RedirectResponse
    {
        $reading->update([
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('status', 'Pencatatan meteran berhasil diverifikasi.');
    }

    /**
     * Hapus Pencatatan
     */
    public function destroy(PublicAreaMeterReading $reading): RedirectResponse
    {
        if ($reading->foto) {
            Storage::disk('public')->delete($reading->foto);
        }
        $reading->delete();

        return back()->with('status', 'Pencatatan meteran berhasil dihapus.');
    }

    /**
     * Export Excel (Mendukung rentang interval tanggal hingga 12 bulan)
     */
    public function exportExcel(Request $request, string $category)
    {
        [$startDate, $endDate] = $this->parseInterval($request);
        $rows = PublicAreaMeterReading::intervalWithUsage($startDate->toDateString(), $endDate->toDateString(), $category);
        $meters = $category === 'water' ? PublicAreaMeterReading::waterMeters() : PublicAreaMeterReading::electricityMeters();

        $summaryPerMeter = [];
        foreach ($meters as $key => $meta) {
            $mRows = $rows->where('meter_key', $key);
            $usages = $mRows->pluck('pemakaian')->filter(fn($v) => $v !== null && $v > 0);
            $summaryPerMeter[$key] = [
                'totalUsage' => round($mRows->sum(fn($r) => $r->pemakaian ?? 0), 2),
                'avgUsage'   => $usages->count() > 0 ? round($usages->avg(), 2) : 0,
                'maxUsage'   => $usages->count() > 0 ? round($usages->max(), 2) : 0,
                'minUsage'   => $usages->count() > 0 ? round($usages->min(), 2) : 0,
            ];
        }

        $startStr = $startDate->format('d-m-Y');
        $endStr = $endDate->format('d-m-Y');
        $filename = 'Logsheet_Public_Area_' . ucfirst($category) . "_{$startStr}_sd_{$endStr}.xls";

        return response()->view('public-area.export-excel', compact('rows', 'meters', 'startDate', 'endDate', 'summaryPerMeter', 'category'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Export PDF (Mendukung rentang interval tanggal hingga 12 bulan)
     */
    public function exportPdf(Request $request, string $category)
    {
        [$startDate, $endDate] = $this->parseInterval($request);
        $rows = PublicAreaMeterReading::intervalWithUsage($startDate->toDateString(), $endDate->toDateString(), $category);
        $meters = $category === 'water' ? PublicAreaMeterReading::waterMeters() : PublicAreaMeterReading::electricityMeters();

        $summaryPerMeter = [];
        foreach ($meters as $key => $meta) {
            $mRows = $rows->where('meter_key', $key);
            $usages = $mRows->pluck('pemakaian')->filter(fn($v) => $v !== null && $v > 0);
            $summaryPerMeter[$key] = [
                'totalUsage' => round($mRows->sum(fn($r) => $r->pemakaian ?? 0), 2),
                'avgUsage'   => $usages->count() > 0 ? round($usages->avg(), 2) : 0,
                'maxUsage'   => $usages->count() > 0 ? round($usages->max(), 2) : 0,
                'minUsage'   => $usages->count() > 0 ? round($usages->min(), 2) : 0,
            ];
        }

        return view('public-area.export-pdf', compact('rows', 'meters', 'startDate', 'endDate', 'summaryPerMeter', 'category'));
    }

    /**
     * Form Input Manual Back Date Water Public Area (Khusus Super Admin)
     */
    public function createManualWater(Request $request): View
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $waterMeters = PublicAreaMeterReading::waterMeters();
        $tanggal = $request->input('tanggal', now()->subDay()->toDateString());
        if (strtotime($tanggal) > strtotime(today()->toDateString())) {
            $tanggal = today()->toDateString();
        }

        $existing = PublicAreaMeterReading::where('category', 'water')
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('meter_key');

        $prevReadings = [];
        $nextReadings = [];
        foreach ($waterMeters as $key => $meta) {
            $prevReadings[$key] = PublicAreaMeterReading::previousOf($tanggal, 'water', $key);
            $nextReadings[$key] = PublicAreaMeterReading::nextOf($tanggal, 'water', $key);
        }

        return view('public-area.manual.water', compact(
            'waterMeters',
            'tanggal',
            'existing',
            'prevReadings',
            'nextReadings'
        ));
    }

    /**
     * Simpan Input Manual Back Date Water Public Area (Khusus Super Admin)
     */
    public function storeManualWater(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'stand'   => 'nullable|array',
            'fotos'   => 'nullable|array',
            'catatan' => 'nullable|array',
        ]);

        $tanggal = $request->input('tanggal');
        $waterMeters = PublicAreaMeterReading::waterMeters();
        $stands = $request->input('stand', []);
        $catatan = $request->input('catatan', []);
        $savedCount = 0;

        foreach ($waterMeters as $key => $meta) {
            $val = $stands[$key] ?? null;
            if ($val !== null && $val !== '') {
                $existing = PublicAreaMeterReading::where('category', 'water')
                    ->where('meter_key', $key)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                $fotoPath = $existing?->foto;
                if ($request->hasFile("fotos.{$key}")) {
                    if ($existing && $existing->foto && Storage::disk('public')->exists($existing->foto)) {
                        Storage::disk('public')->delete($existing->foto);
                    }
                    $fotoPath = $request->file("fotos.{$key}")->store("logsheet/water/{$key}", 'public');
                }

                PublicAreaMeterReading::updateOrCreate(
                    [
                        'tanggal'   => $tanggal,
                        'category'  => 'water',
                        'meter_key' => $key,
                    ],
                    [
                        'meter_name'  => $meta['name'],
                        'unit'        => $meta['unit'],
                        'stand_meter' => (float)$val,
                        'foto'        => $fotoPath,
                        'catatan'     => $catatan[$key] ?? null,
                        'user_id'     => auth()->id(),
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ]
                );
                $savedCount++;
            }
        }

        return redirect()->route('public-area.manual.water', ['tanggal' => $tanggal])
            ->with('status', "Berhasil menyimpan {$savedCount} data Water Public Area untuk tanggal " . Carbon::parse($tanggal)->format('d/m/Y') . ".");
    }

    /**
     * AJAX Reference untuk Input Manual Water
     */
    public function referenceManualWater(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $tanggal = $request->input('tanggal', today()->toDateString());
        $waterMeters = PublicAreaMeterReading::waterMeters();

        $existing = PublicAreaMeterReading::where('category', 'water')
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('meter_key');

        $data = [];
        foreach ($waterMeters as $key => $meta) {
            $prev = PublicAreaMeterReading::previousOf($tanggal, 'water', $key);
            $next = PublicAreaMeterReading::nextOf($tanggal, 'water', $key);
            $rec = $existing->get($key);

            $data[$key] = [
                'existing_stand' => $rec?->stand_meter,
                'existing_foto'  => $rec?->foto ? asset('storage/'.$rec->foto) : null,
                'existing_notes' => $rec?->catatan,
                'prev_date'      => $prev?->tanggal?->format('d/m/Y'),
                'prev_stand'     => $prev?->stand_meter,
                'next_date'      => $next?->tanggal?->format('d/m/Y'),
                'next_stand'     => $next?->stand_meter,
            ];
        }

        return response()->json([
            'tanggal' => $tanggal,
            'meters'  => $data,
        ]);
    }

    /**
     * Tampilkan Halaman Form Bulk Input Manual (Excel) Water Meter Public Area (Khusus Super Admin)
     */
    public function createBulkWater(Request $request): View
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $waterMeters = PublicAreaMeterReading::waterMeters();
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('n');

        return view('public-area.manual.bulk-water', compact('waterMeters', 'currentYear', 'currentMonth'));
    }

    /**
     * Download Template File Excel (.xlsx) untuk Bulk Input Water Meter
     */
    public function downloadBulkWaterTemplate(Request $request, ExcelReaderService $excelReader)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $year = (int)$request->input('year', date('Y'));
        $month = (int)$request->input('month', date('n'));
        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }

        $xlsxContent = $excelReader->generateWaterTemplateXlsx($year, $month);
        $filename = sprintf('Template_Bulk_Input_Water_PA_%04d_%02d.xlsx', $year, $month);

        return response($xlsxContent, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Proses File Excel yang Diunggah untuk Bulk Input Manual Water Meter Public Area (1 Bulan)
     */
    public function storeBulkWater(Request $request, ExcelReaderService $excelReader): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB
        ], [
            'file.required' => 'Silakan pilih file Excel (.xlsx, .xls, atau .csv) terlebih dahulu.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $request->file('file');

        try {
            $rows = $excelReader->parse($file->getRealPath(), $file->getClientOriginalName());
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal membaca file Excel: ' . $e->getMessage()])->withInput();
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'File Excel kosong atau tidak memiliki baris data (minimal 1 baris header dan 1 baris data).'])->withInput();
        }

        $waterMeters = PublicAreaMeterReading::waterMeters();

        // Deteksi Kolom dari Baris Header (Baris 0)
        $header = $rows[0] ?? [];
        $colDate = null;
        $colMapping = [];

        foreach ($header as $colIdx => $colName) {
            $nameLower = strtolower(trim((string)$colName));
            if (preg_match('/tanggal|date|tgl/i', $nameLower) && $colDate === null) {
                $colDate = $colIdx;
            } elseif (preg_match('/cooling\s*tower|ct\b/i', $nameLower)) {
                $colMapping['cooling_tower'] = $colIdx;
            } elseif (preg_match('/toilet|sky\s*lobby|mushol?a/i', $nameLower)) {
                $colMapping['toilet_sky_lobby'] = $colIdx;
            } elseif (preg_match('/pantry|office/i', $nameLower)) {
                $colMapping['pantry_office'] = $colIdx;
            }
        }

        // Fallback default index jika pola header tidak dikenali (0=Tanggal, 1=CT, 2=Toilet, 3=Pantry)
        if ($colDate === null) {
            $colDate = 0;
        }
        if (!isset($colMapping['cooling_tower'])) {
            $colMapping['cooling_tower'] = 1;
        }
        if (!isset($colMapping['toilet_sky_lobby'])) {
            $colMapping['toilet_sky_lobby'] = 2;
        }
        if (!isset($colMapping['pantry_office'])) {
            $colMapping['pantry_office'] = 3;
        }

        $processedDates = [];
        $savedCount = 0;
        $skippedRows = 0;
        $emptyCells = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rawDate = $row[$colDate] ?? null;
            if ($rawDate === null || trim((string)$rawDate) === '') {
                $skippedRows++;
                continue;
            }

            $dateStr = ExcelReaderService::parseDate($rawDate);
            if (! $dateStr) {
                $skippedRows++;
                continue;
            }

            $processedDates[] = $dateStr;

            foreach ($waterMeters as $meterKey => $meta) {
                $colIdx = $colMapping[$meterKey] ?? null;
                $rawVal = ($colIdx !== null && isset($row[$colIdx])) ? $row[$colIdx] : null;

                if ($rawVal === null || trim((string)$rawVal) === '') {
                    $emptyCells++;
                    continue; // Sel kosong dilewati tanpa menggagalkan data lainnya
                }

                $standVal = ExcelReaderService::parseDecimal($rawVal);
                if ($standVal === null) {
                    $emptyCells++;
                    continue;
                }

                // Simpan atau perbarui data stand, langsung terverifikasi tanpa verifikasi manual
                PublicAreaMeterReading::updateOrCreate(
                    [
                        'tanggal'   => $dateStr,
                        'category'  => 'water',
                        'meter_key' => $meterKey,
                    ],
                    [
                        'meter_name'  => $meta['name'],
                        'unit'        => $meta['unit'],
                        'stand_meter' => $standVal,
                        'catatan'     => 'Bulk Input Excel (Super Admin)',
                        'user_id'     => auth()->id(),
                        'verified_at' => now(), // langsung terverifikasi
                        'verified_by' => auth()->id(),
                    ]
                );
                $savedCount++;
            }
        }

        if (count($processedDates) === 0 || $savedCount === 0) {
            return back()->withErrors(['file' => 'Tidak ditemukan data tanggal atau nilai stand meter yang valid di dalam file Excel. Silakan periksa kembali file Anda atau unduh template yang disediakan.'])->withInput();
        }

        sort($processedDates);
        $minDate = $processedDates[0];
        $maxDate = end($processedDates);
        $minFormatted = Carbon::parse($minDate)->format('d/m/Y');
        $maxFormatted = Carbon::parse($maxDate)->format('d/m/Y');
        $uniqueDatesCount = count(array_unique($processedDates));

        $msg = "Berhasil melakukan Bulk Input: {$savedCount} data stand meteran air dari {$uniqueDatesCount} baris tanggal berhasil disimpan dan langsung terverifikasi (Periode: {$minFormatted} s/d {$maxFormatted}).";
        if ($emptyCells > 0) {
            $msg .= " ({$emptyCells} sel kosong dilewati dan dapat dikoreksi/dilengkapi secara manual pada riwayat logsheet).";
        }

        return redirect()->route('public-area.water.index', [
            'start_date' => $minDate,
            'end_date'   => $maxDate,
        ])->with('status', $msg);
    }
}
