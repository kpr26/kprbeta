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
     * Tampilan Utama Sub Menu MVSB (Medium Voltage Switchboard - Incoming From PLN)
     * Menampilkan form input hari ini, kartu status, dan tabel riwayat bulanan.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $year  = (int) $request->input('year', $today->year);
        $month = (int) $request->input('month', $today->month);

        // Data hari ini & data sebelumnya untuk acuan
        $todayReading = PublicAreaMvsbReading::whereDate('tanggal', $today)->first();
        $prevReading  = PublicAreaMvsbReading::whereDate('tanggal', '<', $today)
            ->latest('tanggal')
            ->first();

        // Data riwayat bulan terpilih
        $rows = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->forMonth($year, $month)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('public-area.mvsb.index', compact(
            'todayReading',
            'prevReading',
            'rows',
            'year',
            'month'
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

        $reading = PublicAreaMvsbReading::updateOrCreate(
            ['tanggal' => $tanggal],
            [
                'voltage_20kv' => $request->filled('voltage_20kv') ? (float) $request->input('voltage_20kv') : null,
                'current'      => $request->filled('current') ? (float) $request->input('current') : null,
                'foto'         => $fotoPath,
                'catatan'      => $request->input('catatan'),
                'user_id'      => auth()->id(),
            ]
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
     * Cetak / Ekspor Excel Logsheet MVSB
     */
    public function exportExcel(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $rows = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->forMonth($year, $month)
            ->orderBy('tanggal', 'asc')
            ->get();

        $filename = "Logsheet_MVSB_Incoming_PLN_{$year}_" . sprintf('%02d', $month) . ".xls";

        return response()->view('public-area.mvsb.export-excel', compact('rows', 'year', 'month'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Cetak / Ekspor PDF Logsheet MVSB
     */
    public function exportPdf(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $rows = PublicAreaMvsbReading::with(['user', 'verifier'])
            ->forMonth($year, $month)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('public-area.mvsb.export-pdf', compact('rows', 'year', 'month'));
    }
}
