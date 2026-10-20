<?php

namespace App\Http\Controllers;

use App\Models\PublicAreaMeterReading;
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
            'prevWater',
            'waterCountDone',
            'waterTotalCount'
        ));
    }

    /**
     * Sub Menu 1: Water Meter Public Area (Index & Rekapitulasi)
     */
    public function waterIndex(Request $request): View
    {
        $waterMeters = PublicAreaMeterReading::waterMeters();
        $todayStr = today()->toDateString();

        // Periode bulan (default bulan ini)
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

        // Status hari ini untuk masing-masing dari 3 water meter
        $todayReadings = PublicAreaMeterReading::where('category', 'water')
            ->whereDate('tanggal', $todayStr)
            ->get()
            ->keyBy('meter_key');

        $prevReadings = [];
        foreach ($waterMeters as $key => $meta) {
            $prevReadings[$key] = PublicAreaMeterReading::previousOf($todayStr, 'water', $key);
        }

        // Data rekapitulasi bulanan beserta pemakaian
        $rows = PublicAreaMeterReading::monthWithUsage($year, $month, 'water');

        // Ringkasan per meter untuk periode ini
        $summaryPerMeter = [];
        foreach ($waterMeters as $key => $meta) {
            $meterRows = $rows->where('meter_key', $key);
            $totalUsage = $meterRows->sum(fn ($r) => $r->pemakaian ?? 0);
            $summaryPerMeter[$key] = [
                'count'      => $meterRows->count(),
                'totalUsage' => round($totalUsage, 2),
            ];
        }

        return view('public-area.water.index', compact(
            'waterMeters',
            'todayReadings',
            'prevReadings',
            'rows',
            'year',
            'month',
            'periode',
            'summaryPerMeter'
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
     * Export Excel
     */
    public function exportExcel(Request $request, string $category)
    {
        $periode = $request->query('periode', now()->format('Y-m'));
        $carbon = Carbon::createFromFormat('Y-m', $periode);
        $year = (int)$carbon->year;
        $month = (int)$carbon->month;

        $rows = PublicAreaMeterReading::monthWithUsage($year, $month, $category);
        $meters = $category === 'water' ? PublicAreaMeterReading::waterMeters() : PublicAreaMeterReading::electricityMeters();

        $filename = 'Logsheet_Public_Area_' . ucfirst($category) . '_' . $periode . '.xls';

        return response()->view('public-area.export-excel', compact('rows', 'meters', 'year', 'month', 'category'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request, string $category)
    {
        $periode = $request->query('periode', now()->format('Y-m'));
        $carbon = Carbon::createFromFormat('Y-m', $periode);
        $year = (int)$carbon->year;
        $month = (int)$carbon->month;

        $rows = PublicAreaMeterReading::monthWithUsage($year, $month, $category);
        $meters = $category === 'water' ? PublicAreaMeterReading::waterMeters() : PublicAreaMeterReading::electricityMeters();

        return view('public-area.export-pdf', compact('rows', 'meters', 'year', 'month', 'category'));
    }
}
