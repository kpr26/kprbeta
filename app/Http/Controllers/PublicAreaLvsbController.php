<?php

namespace App\Http\Controllers;

use App\Models\PublicAreaLvsbReading;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicAreaLvsbController extends Controller
{
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
        } elseif ($request->filled('period') || $request->filled('periode')) {
            $p = $request->input('period') ?: $request->input('periode');
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
     * Tampilan Utama Sub Menu LVSB (Low Voltage Switchboard)
     * Menampilkan form input hari ini, ringkasan feeder, grafik tren, dan tabel riwayat.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();
        [$startDate, $endDate] = $this->parseInterval($request);
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();
        $preset = $request->input('preset', '');
        $year  = (int) $startDate->year;
        $month = (int) $startDate->month;

        $structure = PublicAreaLvsbReading::structure();

        // Data hari ini & data sebelumnya untuk acuan
        $todayReading = PublicAreaLvsbReading::whereDate('tanggal', $today)->first();
        $prevReading  = PublicAreaLvsbReading::whereDate('tanggal', '<', $today)
            ->latest('tanggal')
            ->first();

        // Data riwayat rentang interval terpilih
        $rows = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->forInterval($startDateStr, $endDateStr)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Hitung Metrik Rangkuman Statistik LVSB
        $ascRows = $rows->sortBy('tanggal')->values();
        $vList = [];
        $pList = [];
        $cList = [];
        $pfList = [];
        $chartLabels = [];
        $chartVolt = [];
        $chartPower = [];
        $chartCurrent = [];
        $chartPF = [];

        foreach ($ascRows as $r) {
            $v = $r->val('incoming_trafo', 'voltage_3p');
            $p = $r->val('incoming_trafo', 'power');
            $c = $r->val('incoming_trafo', 'current');
            $pf = $r->val('incoming_capacitor', 'power_factor');

            if ($v !== null) $vList[] = (float)$v;
            if ($p !== null) $pList[] = (float)$p;
            if ($c !== null) $cList[] = (float)$c;
            if ($pf !== null) $pfList[] = (float)$pf;

            $chartLabels[] = $r->tanggal->translatedFormat('d M');
            $chartVolt[] = $v !== null ? (float)$v : null;
            $chartPower[] = $p !== null ? (float)$p : null;
            $chartCurrent[] = $c !== null ? (float)$c : null;
            $chartPF[] = $pf !== null ? (float)$pf : null;
        }

        $summary = [
            'count'       => $rows->count(),
            'avg_voltage' => count($vList) > 0 ? round(array_sum($vList) / count($vList), 1) : null,
            'max_power'   => count($pList) > 0 ? max($pList) : null,
            'avg_power'   => count($pList) > 0 ? round(array_sum($pList) / count($pList), 2) : null,
            'max_current' => count($cList) > 0 ? max($cList) : null,
            'avg_pf'      => count($pfList) > 0 ? round(array_sum($pfList) / count($pfList), 2) : null,
        ];

        $chartData = [
            'labels'  => $chartLabels,
            'voltage' => $chartVolt,
            'power'   => $chartPower,
            'current' => $chartCurrent,
            'pf'      => $chartPF,
        ];

        return view('public-area.lvsb.index', compact(
            'structure',
            'todayReading',
            'prevReading',
            'rows',
            'startDate',
            'endDate',
            'preset',
            'year',
            'month',
            'summary',
            'chartData'
        ));
    }

    /**
     * Form Input Mandiri / Koreksi LVSB (Diarahkan ke index terpadu dengan card per sub-menu)
     */
    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('public-area.lvsb.index');
    }

    /**
     * Simpan / Perbarui Data LVSB (Mendukung Form Web Biasa, Input Parsial Per Sub-Menu & Sinkronisasi Offline JSON)
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $isJson = $request->wantsJson() || $request->ajax() || $request->header('X-Offline-Sync') === 'true';

        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        // Validasi dasar
        $request->validate([
            'tanggal'   => 'required|date',
            'group_key' => 'nullable|string',
            'fotos'     => 'nullable|array',
            'fotos.*'   => 'nullable|image|max:10240',
            'foto'      => 'nullable|image|max:10240',
            'catatan'   => 'nullable|string|max:1000',
        ]);

        $existing = PublicAreaLvsbReading::whereDate('tanggal', $tanggal)->first();

        // Mulai dari data readings yang sudah ada agar simpan parsial per sub-menu tidak menimpa data sebelumnya
        $structure = PublicAreaLvsbReading::structure();
        $readingsData = $existing?->readings ?? [];
        if (!is_array($readingsData)) {
            $readingsData = [];
        }

        $groupKeySubmitted = $request->input('group_key');

        if ($groupKeySubmitted && isset($structure[$groupKeySubmitted])) {
            // Update HANYA grup spesifik ini
            $group = $structure[$groupKeySubmitted];
            if ($group['has_subgroups']) {
                $readingsData[$groupKeySubmitted] = [];
                foreach ($group['subgroups'] as $subKey => $sub) {
                    $readingsData[$groupKeySubmitted][$subKey] = [];
                    foreach ($sub['fields'] as $fKey => $fMeta) {
                        $rawVal = $request->input("readings.{$groupKeySubmitted}.{$subKey}.{$fKey}");
                        $readingsData[$groupKeySubmitted][$subKey][$fKey] = ($rawVal !== null && $rawVal !== '')
                            ? (float) str_replace(',', '.', (string)$rawVal)
                            : null;
                    }
                }
            } else {
                $readingsData[$groupKeySubmitted] = [];
                foreach ($group['fields'] as $fKey => $fMeta) {
                    $rawVal = $request->input("readings.{$groupKeySubmitted}.{$fKey}");
                    $readingsData[$groupKeySubmitted][$fKey] = ($rawVal !== null && $rawVal !== '')
                        ? (float) str_replace(',', '.', (string)$rawVal)
                        : null;
                }
            }
        } else {
            // Full / Bulk Update (misal saat backdate atau sinkronisasi multi-grup)
            foreach ($structure as $groupKey => $group) {
                if ($request->has("readings.{$groupKey}")) {
                    if ($group['has_subgroups']) {
                        $readingsData[$groupKey] = [];
                        foreach ($group['subgroups'] as $subKey => $sub) {
                            $readingsData[$groupKey][$subKey] = [];
                            foreach ($sub['fields'] as $fKey => $fMeta) {
                                $rawVal = $request->input("readings.{$groupKey}.{$subKey}.{$fKey}");
                                $readingsData[$groupKey][$subKey][$fKey] = ($rawVal !== null && $rawVal !== '')
                                    ? (float) str_replace(',', '.', (string)$rawVal)
                                    : null;
                            }
                        }
                    } else {
                        $readingsData[$groupKey] = [];
                        foreach ($group['fields'] as $fKey => $fMeta) {
                            $rawVal = $request->input("readings.{$groupKey}.{$fKey}");
                            $readingsData[$groupKey][$fKey] = ($rawVal !== null && $rawVal !== '')
                                ? (float) str_replace(',', '.', (string)$rawVal)
                                : null;
                        }
                    }
                }
            }
        }

        // Kelola penyimpanan foto
        $existingFotos = $existing?->fotos ?? [];
        if (!is_array($existingFotos)) {
            $existingFotos = [];
        }

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $gKey => $uploadedFile) {
                if ($uploadedFile && $uploadedFile->isValid()) {
                    if (!empty($existingFotos[$gKey]) && Storage::disk('public')->exists($existingFotos[$gKey])) {
                        Storage::disk('public')->delete($existingFotos[$gKey]);
                    }
                    $existingFotos[$gKey] = $uploadedFile->store("logsheet/lvsb/{$gKey}", 'public');
                }
            }
        }

        $fotoPath = $existing?->foto;
        if ($request->hasFile('foto')) {
            if ($groupKeySubmitted) {
                // Form per-grup mengunggah 'foto' tunggal
                if (!empty($existingFotos[$groupKeySubmitted]) && Storage::disk('public')->exists($existingFotos[$groupKeySubmitted])) {
                    Storage::disk('public')->delete($existingFotos[$groupKeySubmitted]);
                }
                $existingFotos[$groupKeySubmitted] = $request->file('foto')->store("logsheet/lvsb/{$groupKeySubmitted}", 'public');
            } else {
                if ($existing && $existing->foto && Storage::disk('public')->exists($existing->foto)) {
                    Storage::disk('public')->delete($existing->foto);
                }
                $fotoPath = $request->file('foto')->store('logsheet/lvsb', 'public');
            }
        }

        // Catatan
        $newCatatan = $request->input('catatan');
        $finalCatatan = $newCatatan ?: ($existing?->catatan);

        // Aturan verifikasi: hanya teknisi pada hari berjalan yang membutuhkan verifikasi
        $isTeknisi  = auth()->user()->role === 'teknisi';
        $isToday    = $tanggal === Carbon::today()->toDateString();
        $autoVerify = (! $isTeknisi || ! $isToday);

        $payload = [
            'readings' => $readingsData,
            'fotos'    => !empty($existingFotos) ? $existingFotos : null,
            'foto'     => $fotoPath,
            'catatan'  => $finalCatatan,
            'user_id'  => auth()->id(),
        ];

        if ($autoVerify) {
            $payload['verified_at'] = now();
            $payload['verified_by'] = auth()->id();
        } elseif (! $existing?->verified_at) {
            $payload['verified_at'] = null;
            $payload['verified_by'] = null;
        }

        // Simpan / update record
        $reading = PublicAreaLvsbReading::updateOrCreate(
            ['tanggal' => $tanggal],
            $payload
        );

        $label = ($groupKeySubmitted && isset($structure[$groupKeySubmitted]))
            ? $structure[$groupKeySubmitted]['title']
            : 'Logsheet LVSB';

        $msg = "Data {$label} tanggal " . Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil disimpan.';

        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'target'  => route('public-area.lvsb.index'),
                'data'    => $reading,
            ]);
        }

        return redirect()->route('public-area.lvsb.index')->with('success', $msg);
    }

    /**
     * Tampilan Detail Lengkap Pembacaan Feeder LVSB
     */
    public function show(PublicAreaLvsbReading $reading): View
    {
        $structure = PublicAreaLvsbReading::structure();

        return view('public-area.lvsb.show', compact('reading', 'structure'));
    }

    /**
     * Verifikasi Logsheet LVSB (Khusus Super Admin & Supervisor)
     */
    public function verify(PublicAreaLvsbReading $reading): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isSupervisor()) {
            abort(403, 'Hanya Supervisor atau Super Admin yang dapat memverifikasi logsheet.');
        }

        $reading->update([
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Logsheet LVSB tanggal ' . $reading->tanggal->format('d/m/Y') . ' berhasil diverifikasi.');
    }

    /**
     * Hapus Data Logsheet LVSB (Khusus Super Admin)
     */
    public function destroy(PublicAreaLvsbReading $reading): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak menghapus data logsheet.');
        }

        if (is_array($reading->fotos)) {
            foreach ($reading->fotos as $f) {
                if ($f && Storage::disk('public')->exists($f)) {
                    Storage::disk('public')->delete($f);
                }
            }
        }

        if ($reading->foto && Storage::disk('public')->exists($reading->foto)) {
            Storage::disk('public')->delete($reading->foto);
        }

        $tglStr = $reading->tanggal->format('d/m/Y');
        $reading->delete();

        return redirect()->route('public-area.lvsb.index')
            ->with('success', "Data Logsheet LVSB tanggal {$tglStr} berhasil dihapus.");
    }

    /**
     * Verifikasi Masal Logsheet LVSB (Khusus Super Admin, Maksimal 1 Bulan).
     */
    public function bulkVerify(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang berhak melakukan verifikasi masal.');

        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return back()->with('warning', 'Pilih minimal satu data LVSB untuk diverifikasi.');
        }

        $readings = PublicAreaLvsbReading::whereIn('id', $ids)->get();
        if ($readings->isEmpty()) {
            return back()->with('warning', 'Data logsheet LVSB yang dipilih tidak ditemukan.');
        }

        // Batasan rentang maksimal 1 bulan (31 hari)
        $dates = $readings->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->unique()->values();
        $minDate = $dates->min();
        $maxDate = $dates->max();

        if ($minDate && $maxDate) {
            $diffDays = \Carbon\Carbon::parse($minDate)->diffInDays(\Carbon\Carbon::parse($maxDate));
            if ($diffDays > 31) {
                return back()->with('error', 'Verifikasi masal dibatasi maksimal untuk rentang 1 bulan (31 hari).');
            }
        }

        $now = now();
        $adminId = auth()->id();
        $updatedCount = 0;

        foreach ($readings as $reading) {
            $reading->update([
                'verified_at' => $now,
                'verified_by' => $adminId,
            ]);
            $updatedCount++;
        }

        $msg = "Berhasil memverifikasi {$updatedCount} data logsheet LVSB secara masal.";
        return back()->with('status', $msg)->with('success', $msg);
    }

    /**
     * Hapus Masal Logsheet LVSB (Khusus Super Admin, Maksimal 1 Bulan).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang berhak melakukan penghapusan masal.');

        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return back()->with('warning', 'Pilih minimal satu data LVSB untuk dihapus.');
        }

        $readings = PublicAreaLvsbReading::whereIn('id', $ids)->get();
        if ($readings->isEmpty()) {
            return back()->with('warning', 'Data logsheet LVSB yang dipilih tidak ditemukan.');
        }

        // Batasan rentang maksimal 1 bulan (31 hari)
        $dates = $readings->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->unique()->values();
        $minDate = $dates->min();
        $maxDate = $dates->max();

        if ($minDate && $maxDate) {
            $diffDays = \Carbon\Carbon::parse($minDate)->diffInDays(\Carbon\Carbon::parse($maxDate));
            if ($diffDays > 31) {
                return back()->with('error', 'Penghapusan masal dibatasi maksimal untuk rentang 1 bulan (31 hari) demi keamanan data.');
            }
        }

        $deletedCount = 0;
        foreach ($readings as $reading) {
            if (is_array($reading->fotos)) {
                foreach ($reading->fotos as $f) {
                    if ($f && Storage::disk('public')->exists($f)) {
                        Storage::disk('public')->delete($f);
                    }
                }
            }
            if ($reading->foto && Storage::disk('public')->exists($reading->foto)) {
                Storage::disk('public')->delete($reading->foto);
            }
            $reading->delete();
            $deletedCount++;
        }

        $msg = "Berhasil menghapus {$deletedCount} data logsheet LVSB secara permanen.";
        return back()->with('status', $msg)->with('success', $msg);
    }

    /**
     * Cetak / Ekspor Excel Logsheet LVSB (Mendukung rentang interval tanggal hingga 12 bulan)
     */
    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->parseInterval($request);
        $rows = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->forInterval($startDate->toDateString(), $endDate->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        $structure = PublicAreaLvsbReading::structure();
        $startStr = $startDate->format('d-m-Y');
        $endStr = $endDate->format('d-m-Y');
        $filename = "Logsheet_LVSB_Public_Area_{$startStr}_sd_{$endStr}.xls";

        return response()->view('public-area.lvsb.export-excel', compact('rows', 'structure', 'startDate', 'endDate'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Cetak / Ekspor PDF Logsheet LVSB (Mendukung rentang interval tanggal hingga 12 bulan)
     */
    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->parseInterval($request);
        $rows = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->forInterval($startDate->toDateString(), $endDate->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        $structure = PublicAreaLvsbReading::structure();

        return view('public-area.lvsb.export-pdf', compact('rows', 'structure', 'startDate', 'endDate'));
    }

    /**
     * Form Input Manual Back Date LVSB (Khusus Super Admin)
     */
    public function createManual(Request $request): View
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $tanggal = $request->input('tanggal', now()->subDay()->toDateString());
        if (strtotime($tanggal) > strtotime(today()->toDateString())) {
            $tanggal = today()->toDateString();
        }

        $reading = PublicAreaLvsbReading::firstOrNew(['tanggal' => $tanggal]);
        $structure = PublicAreaLvsbReading::structure();

        $prevReading = PublicAreaLvsbReading::previousOf($tanggal);
        $nextReading = PublicAreaLvsbReading::nextOf($tanggal);

        return view('public-area.manual.lvsb', compact('reading', 'structure', 'prevReading', 'nextReading', 'tanggal'));
    }

    /**
     * Simpan Input Manual Back Date LVSB (Khusus Super Admin)
     */
    public function storeManual(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'fotos'   => 'nullable|array',
            'fotos.*' => 'nullable|image|max:10240',
            'foto'    => 'nullable|image|max:10240',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $tanggal = $request->input('tanggal');
        $existing = PublicAreaLvsbReading::whereDate('tanggal', $tanggal)->first();

        // Susun payload readings sesuai struktur grup
        $structure = PublicAreaLvsbReading::structure();
        $readingsData = [];

        foreach ($structure as $groupKey => $group) {
            if ($group['has_subgroups']) {
                foreach ($group['subgroups'] as $subKey => $subMeta) {
                    foreach ($subMeta['fields'] as $fieldKey => $fieldMeta) {
                        $val = $request->input("readings.{$groupKey}.{$subKey}.{$fieldKey}");
                        if ($val !== null && $val !== '') {
                            $readingsData[$groupKey][$subKey][$fieldKey] = (float) $val;
                        }
                    }
                }
            } else {
                foreach ($group['fields'] as $fieldKey => $fieldMeta) {
                    $val = $request->input("readings.{$groupKey}.{$fieldKey}");
                    if ($val !== null && $val !== '') {
                        $readingsData[$groupKey][$fieldKey] = (float) $val;
                    }
                }
            }
        }

        // Simpan foto per grup jika dilampirkan
        $existingFotos = $existing?->fotos ?? [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $gKey => $uploadedFile) {
                if ($uploadedFile && $uploadedFile->isValid()) {
                    if (!empty($existingFotos[$gKey]) && Storage::disk('public')->exists($existingFotos[$gKey])) {
                        Storage::disk('public')->delete($existingFotos[$gKey]);
                    }
                    $existingFotos[$gKey] = $uploadedFile->store("logsheet/lvsb/{$gKey}", 'public');
                }
            }
        }

        // Simpan foto umum jika ada
        $fotoPath = $existing?->foto;
        if ($request->hasFile('foto')) {
            if ($existing && $existing->foto && Storage::disk('public')->exists($existing->foto)) {
                Storage::disk('public')->delete($existing->foto);
            }
            $fotoPath = $request->file('foto')->store('logsheet/lvsb', 'public');
        }

        PublicAreaLvsbReading::updateOrCreate(
            ['tanggal' => $tanggal],
            [
                'readings'    => $readingsData,
                'fotos'       => !empty($existingFotos) ? $existingFotos : null,
                'foto'        => $fotoPath,
                'catatan'     => $request->input('catatan'),
                'user_id'     => auth()->id(),
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]
        );

        return redirect()->route('public-area.manual.lvsb', ['tanggal' => $tanggal])
            ->with('success', 'Data Logsheet LVSB tanggal ' . Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil disimpan secara manual.');
    }

    /**
     * AJAX Reference untuk Input Manual LVSB
     */
    public function referenceManual(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $tanggal = $request->input('tanggal', today()->toDateString());
        $existing = PublicAreaLvsbReading::whereDate('tanggal', $tanggal)->first();
        $prev = PublicAreaLvsbReading::previousOf($tanggal);
        $next = PublicAreaLvsbReading::nextOf($tanggal);

        return response()->json([
            'tanggal'        => $tanggal,
            'has_existing'   => !is_null($existing),
            'existing_trafo' => $existing?->val('incoming_trafo', 'voltage_3p'),
            'prev_date'      => $prev?->tanggal?->format('d/m/Y'),
            'prev_trafo'     => $prev?->val('incoming_trafo', 'voltage_3p'),
            'next_date'      => $next?->tanggal?->format('d/m/Y'),
            'next_trafo'     => $next?->val('incoming_trafo', 'voltage_3p'),
        ]);
    }
}
