<?php

namespace App\Http\Controllers;

use App\Models\PublicAreaMvsbReading;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicAreaMvsbController extends Controller
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
     * Tampilan Utama Sub Menu MVSB (Medium Voltage Switchboard - Incoming From PLN)
     * Menampilkan form input hari ini, kartu status, grafik tren, dan tabel riwayat.
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

        // Data hari ini & data sebelumnya untuk acuan
        $todayReading = PublicAreaMvsbReading::whereDate('tanggal', $today)->first();
        $prevReading  = PublicAreaMvsbReading::whereDate('tanggal', '<', $today)
            ->latest('tanggal')
            ->first();

        // Data riwayat rentang interval terpilih
        $rows = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->forInterval($startDateStr, $endDateStr)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Hitung Metrik Rangkuman Statistik MVSB
        $ascRows = $rows->sortBy('tanggal')->values();
        $voltList = [];
        $currList = [];
        $chartLabels = [];
        $chartVolt = [];
        $chartCurr = [];

        foreach ($ascRows as $r) {
            if ($r->voltage_20kv !== null) $voltList[] = (float)$r->voltage_20kv;
            if ($r->current !== null) $currList[] = (float)$r->current;

            $chartLabels[] = $r->tanggal->translatedFormat('d M');
            $chartVolt[] = $r->voltage_20kv !== null ? (float)$r->voltage_20kv : null;
            $chartCurr[] = $r->current !== null ? (float)$r->current : null;
        }

        $summary = [
            'count'       => $rows->count(),
            'avg_voltage' => count($voltList) > 0 ? round(array_sum($voltList) / count($voltList), 1) : null,
            'max_voltage' => count($voltList) > 0 ? max($voltList) : null,
            'min_voltage' => count($voltList) > 0 ? min($voltList) : null,
            'avg_current' => count($currList) > 0 ? round(array_sum($currList) / count($currList), 2) : null,
            'max_current' => count($currList) > 0 ? max($currList) : null,
            'min_current' => count($currList) > 0 ? min($currList) : null,
        ];

        $chartData = [
            'labels'  => $chartLabels,
            'voltage' => $chartVolt,
            'current' => $chartCurr,
        ];

        return view('public-area.mvsb.index', compact(
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
     * Form Input Mandiri / Koreksi MVSB
     */
    public function create(Request $request): View
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $reading = PublicAreaMvsbReading::firstOrNew(['tanggal' => $tanggal]);

        $prevReading = PublicAreaMvsbReading::whereDate('tanggal', '<', $tanggal)
            ->latest('tanggal')
            ->first();

        return view('public-area.mvsb.create', compact('reading', 'prevReading', 'tanggal'));
    }

    /**
     * Simpan / Perbarui Data MVSB (Mendukung Form Web Biasa dan Sinkronisasi Offline JSON)
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $isJson = $request->wantsJson() || $request->ajax() || $request->header('X-Offline-Sync') === 'true';

        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        // Validasi dasar
        $request->validate([
            'tanggal'      => 'required|date',
            'voltage_20kv' => 'nullable|numeric|min:0',
            'current'      => 'nullable|numeric|min:0',
            'foto'         => 'nullable|image|max:10240',
            'catatan'      => 'nullable|string|max:1000',
        ]);

        $existing = PublicAreaMvsbReading::whereDate('tanggal', $tanggal)->first();

        // Simpan foto jika dilampirkan
        $fotoPath = $existing?->foto;
        if ($request->hasFile('foto')) {
            if ($existing && $existing->foto && Storage::disk('public')->exists($existing->foto)) {
                Storage::disk('public')->delete($existing->foto);
            }
            $fotoPath = $request->file('foto')->store('logsheet/mvsb', 'public');
        }

        $isTeknisi  = auth()->user()->role === 'teknisi';
        $isToday    = $tanggal === Carbon::today()->toDateString();
        $autoVerify = (! $isTeknisi || ! $isToday);

        $payload = [
            'voltage_20kv' => $request->filled('voltage_20kv') ? (float) $request->input('voltage_20kv') : null,
            'current'      => $request->filled('current') ? (float) $request->input('current') : null,
            'foto'         => $fotoPath,
            'catatan'      => $request->input('catatan'),
            'user_id'      => auth()->id(),
        ];

        if ($autoVerify) {
            $payload['verified_at'] = now();
            $payload['verified_by'] = auth()->id();
        } elseif (! $existing?->verified_at) {
            $payload['verified_at'] = null;
            $payload['verified_by'] = null;
        }

        $reading = PublicAreaMvsbReading::updateOrCreate(
            ['tanggal' => $tanggal],
            $payload
        );

        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => 'Data Logsheet MVSB Incoming From PLN tanggal ' . Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil disimpan.',
                'data'    => $reading,
            ]);
        }

        return redirect()->route('public-area.mvsb.index')
            ->with('success', 'Data Logsheet MVSB Incoming From PLN tanggal ' . Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil disimpan.');
    }

    /**
     * Tampilan Detail Lengkap Pembacaan MVSB Incoming From PLN
     */
    public function show(PublicAreaMvsbReading $reading): View
    {
        return view('public-area.mvsb.show', compact('reading'));
    }

    /**
     * Verifikasi Logsheet MVSB (Khusus Super Admin & Supervisor)
     */
    public function verify(PublicAreaMvsbReading $reading): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isSupervisor()) {
            abort(403, 'Hanya Supervisor atau Super Admin yang dapat memverifikasi logsheet.');
        }

        $reading->update([
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Logsheet MVSB tanggal ' . $reading->tanggal->format('d/m/Y') . ' berhasil diverifikasi.');
    }

    /**
     * Hapus Data Logsheet MVSB (Khusus Super Admin)
     */
    public function destroy(PublicAreaMvsbReading $reading): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak menghapus data logsheet.');
        }

        if ($reading->foto && Storage::disk('public')->exists($reading->foto)) {
            Storage::disk('public')->delete($reading->foto);
        }

        $tglStr = $reading->tanggal->format('d/m/Y');
        $reading->delete();

        return redirect()->route('public-area.mvsb.index')
            ->with('success', "Data Logsheet MVSB tanggal {$tglStr} berhasil dihapus.");
    }

    /**
     * Cetak / Ekspor Excel Logsheet MVSB (Mendukung rentang interval tanggal hingga 12 bulan)
     */
    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->parseInterval($request);
        $rows = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->forInterval($startDate->toDateString(), $endDate->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        $startStr = $startDate->format('d-m-Y');
        $endStr = $endDate->format('d-m-Y');
        $filename = "Logsheet_MVSB_Incoming_PLN_{$startStr}_sd_{$endStr}.xls";

        return response()->view('public-area.mvsb.export-excel', compact('rows', 'startDate', 'endDate'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Cetak / Ekspor PDF Logsheet MVSB (Mendukung rentang interval tanggal hingga 12 bulan)
     */
    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->parseInterval($request);
        $rows = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->forInterval($startDate->toDateString(), $endDate->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('public-area.mvsb.export-pdf', compact('rows', 'startDate', 'endDate'));
    }

    /**
     * Form Input Manual Back Date MVSB (Khusus Super Admin)
     */
    public function createManual(Request $request): View
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $tanggal = $request->input('tanggal', now()->subDay()->toDateString());
        if (strtotime($tanggal) > strtotime(today()->toDateString())) {
            $tanggal = today()->toDateString();
        }

        $reading = PublicAreaMvsbReading::firstOrNew(['tanggal' => $tanggal]);
        $prevReading = PublicAreaMvsbReading::previousOf($tanggal);
        $nextReading = PublicAreaMvsbReading::nextOf($tanggal);

        return view('public-area.manual.mvsb', compact('reading', 'prevReading', 'nextReading', 'tanggal'));
    }

    /**
     * Simpan Input Manual Back Date MVSB (Khusus Super Admin)
     */
    public function storeManual(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $request->validate([
            'tanggal'      => 'required|date|before_or_equal:today',
            'voltage_20kv' => 'nullable|numeric|min:0',
            'current'      => 'nullable|numeric|min:0',
            'foto'         => 'nullable|image|max:10240',
            'catatan'      => 'nullable|string|max:1000',
        ]);

        $tanggal = $request->input('tanggal');
        $existing = PublicAreaMvsbReading::whereDate('tanggal', $tanggal)->first();

        $fotoPath = $existing?->foto;
        if ($request->hasFile('foto')) {
            if ($existing && $existing->foto && Storage::disk('public')->exists($existing->foto)) {
                Storage::disk('public')->delete($existing->foto);
            }
            $fotoPath = $request->file('foto')->store('logsheet/mvsb', 'public');
        }

        PublicAreaMvsbReading::updateOrCreate(
            ['tanggal' => $tanggal],
            [
                'voltage_20kv' => $request->filled('voltage_20kv') ? (float) $request->input('voltage_20kv') : null,
                'current'      => $request->filled('current') ? (float) $request->input('current') : null,
                'foto'         => $fotoPath,
                'catatan'      => $request->input('catatan'),
                'user_id'      => auth()->id(),
                'verified_at'  => now(),
                'verified_by'  => auth()->id(),
            ]
        );

        return redirect()->route('public-area.manual.mvsb', ['tanggal' => $tanggal])
            ->with('success', 'Data Logsheet MVSB tanggal ' . Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil disimpan secara manual.');
    }

    /**
     * AJAX Reference untuk Input Manual MVSB
     */
    public function referenceManual(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $tanggal = $request->input('tanggal', today()->toDateString());
        $existing = PublicAreaMvsbReading::whereDate('tanggal', $tanggal)->first();
        $prev = PublicAreaMvsbReading::previousOf($tanggal);
        $next = PublicAreaMvsbReading::nextOf($tanggal);

        return response()->json([
            'tanggal'          => $tanggal,
            'has_existing'     => !is_null($existing),
            'existing_voltage' => $existing?->voltage_20kv,
            'existing_current' => $existing?->current,
            'prev_date'        => $prev?->tanggal?->format('d/m/Y'),
            'prev_voltage'     => $prev?->voltage_20kv,
            'prev_current'     => $prev?->current,
            'next_date'        => $next?->tanggal?->format('d/m/Y'),
            'next_voltage'     => $next?->voltage_20kv,
            'next_current'     => $next?->current,
        ]);
    }
}
