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
     * Tampilan Utama Sub Menu LVSB (Low Voltage Switchboard)
     * Menampilkan form input hari ini, ringkasan feeder, dan tabel riwayat bulanan.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $year  = (int) $request->input('year', $today->year);
        $month = (int) $request->input('month', $today->month);

        $structure = PublicAreaLvsbReading::structure();

        // Data hari ini & data sebelumnya untuk acuan
        $todayReading = PublicAreaLvsbReading::whereDate('tanggal', $today)->first();
        $prevReading  = PublicAreaLvsbReading::whereDate('tanggal', '<', $today)
            ->latest('tanggal')
            ->first();

        // Data riwayat bulan terpilih
        $rows = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->forMonth($year, $month)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('public-area.lvsb.index', compact(
            'structure',
            'todayReading',
            'prevReading',
            'rows',
            'year',
            'month'
        ));
    }

    /**
     * Form Input Mandiri / Koreksi LVSB
     */
    public function create(Request $request): View
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $reading = PublicAreaLvsbReading::firstOrNew(['tanggal' => $tanggal]);
        $structure = PublicAreaLvsbReading::structure();

        $prevReading = PublicAreaLvsbReading::whereDate('tanggal', '<', $tanggal)
            ->latest('tanggal')
            ->first();

        return view('public-area.lvsb.create', compact('reading', 'structure', 'prevReading', 'tanggal'));
    }

    /**
     * Simpan / Perbarui Data LVSB (Mendukung Form Web Biasa dan Sinkronisasi Offline JSON)
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $isJson = $request->wantsJson() || $request->ajax() || $request->header('X-Offline-Sync') === 'true';

        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        // Validasi dasar
        $request->validate([
            'tanggal' => 'required|date',
            'foto'    => 'nullable|image|max:10240', // Maksimal 10MB
            'catatan' => 'nullable|string|max:1000',
        ]);

        $existing = PublicAreaLvsbReading::whereDate('tanggal', $tanggal)->first();

        // Susun payload readings sesuai struktur grup
        $structure = PublicAreaLvsbReading::structure();
        $readingsData = [];

        foreach ($structure as $groupKey => $group) {
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

        // Simpan foto jika dilampirkan
        $fotoPath = $existing?->foto;
        if ($request->hasFile('foto')) {
            if ($existing && $existing->foto && Storage::disk('public')->exists($existing->foto)) {
                Storage::disk('public')->delete($existing->foto);
            }
            $fotoPath = $request->file('foto')->store('logsheet/lvsb', 'public');
        }

        // Simpan / update record
        $reading = PublicAreaLvsbReading::updateOrCreate(
            ['tanggal' => $tanggal],
            [
                'readings' => $readingsData,
                'foto'     => $fotoPath,
                'catatan'  => $request->input('catatan'),
                'user_id'  => auth()->id(),
            ]
        );

        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => 'Logsheet LVSB tanggal ' . Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil disimpan!',
                'data'    => $reading,
            ]);
        }

        return redirect()->route('public-area.lvsb.index')
            ->with('success', 'Data Logsheet LVSB tanggal ' . Carbon::parse($tanggal)->format('d/m/Y') . ' berhasil disimpan.');
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

        if ($reading->foto && Storage::disk('public')->exists($reading->foto)) {
            Storage::disk('public')->delete($reading->foto);
        }

        $tglStr = $reading->tanggal->format('d/m/Y');
        $reading->delete();

        return redirect()->route('public-area.lvsb.index')
            ->with('success', "Data Logsheet LVSB tanggal {$tglStr} berhasil dihapus.");
    }

    /**
     * Cetak / Ekspor Excel Logsheet LVSB
     */
    public function exportExcel(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $rows = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->forMonth($year, $month)
            ->orderBy('tanggal', 'asc')
            ->get();

        $structure = PublicAreaLvsbReading::structure();
        $filename = "Logsheet_LVSB_Public_Area_{$year}_" . sprintf('%02d', $month) . ".xls";

        return response()->view('public-area.lvsb.export-excel', compact('rows', 'structure', 'year', 'month'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Cetak / Ekspor PDF Logsheet LVSB
     */
    public function exportPdf(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $rows = PublicAreaLvsbReading::with(['user', 'verifier'])
            ->forMonth($year, $month)
            ->orderBy('tanggal', 'asc')
            ->get();

        $structure = PublicAreaLvsbReading::structure();

        return view('public-area.lvsb.export-pdf', compact('rows', 'structure', 'year', 'month'));
    }
}
