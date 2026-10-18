<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogsheetController extends Controller
{
    /** Rekap bulanan. */
    public function index(Request $request)
    {
        [$year, $month] = $this->period($request);
        $rows    = MeterReading::monthWithUsage($year, $month);
        $summary = $this->summary($rows);
        $today   = MeterReading::whereDate('tanggal', today())->first();

        return view('logsheet.index', compact('rows', 'summary', 'year', 'month', 'today'));
    }

    public function create()
    {
        $last  = MeterReading::orderByDesc('tanggal')->first();
        $today = MeterReading::whereDate('tanggal', today())->first();
        return view('logsheet.form', [
            'reading' => new MeterReading(['tanggal' => today()]),
            'last'    => $last,
            'today'   => $today,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id']  = Auth::id();
        $data['foto_kwh'] = $request->file('foto_kwh')->store('logsheet', 'public');
        $data['foto_air'] = $request->file('foto_air')->store('logsheet', 'public');

        MeterReading::create($data);

        return redirect()->route('logsheet.index')->with('success', 'Logsheet tanggal ' . date('d/m/Y', strtotime($data['tanggal'])) . ' berhasil disimpan.');
    }

    public function show(MeterReading $logsheet)
    {
        $prev = MeterReading::previousOf($logsheet->tanggal);
        return view('logsheet.show', ['r' => $logsheet->load('user', 'verifier'), 'prev' => $prev]);
    }

    public function edit(MeterReading $logsheet)
    {
        $this->authorizeEdit($logsheet);
        $last = MeterReading::previousOf($logsheet->tanggal);
        return view('logsheet.form', ['reading' => $logsheet, 'last' => $last]);
    }

    public function update(Request $request, MeterReading $logsheet)
    {
        $this->authorizeEdit($logsheet);
        $data = $this->validated($request, $logsheet);

        foreach (['foto_kwh', 'foto_air'] as $f) {
            if ($request->hasFile($f)) {
                Storage::disk('public')->delete($logsheet->$f);
                $data[$f] = $request->file($f)->store('logsheet', 'public');
            }
        }
        // Data berubah → verifikasi ulang
        $data['verified_at'] = null;
        $data['verified_by'] = null;

        $logsheet->update($data);

        return redirect()->route('logsheet.index')->with('success', 'Logsheet berhasil diperbarui.');
    }

    public function destroy(MeterReading $logsheet)
    {
        Storage::disk('public')->delete([$logsheet->foto_kwh, $logsheet->foto_air]);
        $logsheet->delete();
        return back()->with('success', 'Logsheet berhasil dihapus.');
    }

    /** Verifikasi: tandai data sudah dicrosscheck dengan foto (Admin, Eng Admin, Eng Spv). */
    public function verify(MeterReading $logsheet)
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true), 403);
        $logsheet->update(['verified_at' => now(), 'verified_by' => Auth::id()]);
        return back()->with('success', 'Logsheet ' . $logsheet->tanggal->format('d/m/Y') . ' telah diverifikasi.');
    }

    /** Export Excel (.xls) */
    public function exportExcel(Request $request)
    {
        [$year, $month] = $this->period($request);
        $rows    = MeterReading::monthWithUsage($year, $month);
        $summary = $this->summary($rows);
        $html    = view('logsheet.export-excel', compact('rows', 'summary', 'year', 'month'))->render();

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="Logsheet_%04d-%02d.xls"', $year, $month),
        ]);
    }

    /** Export PDF (halaman cetak → Simpan sebagai PDF) */
    public function exportPdf(Request $request)
    {
        [$year, $month] = $this->period($request);
        $rows    = MeterReading::monthWithUsage($year, $month);
        $summary = $this->summary($rows);
        return view('logsheet.export-pdf', compact('rows', 'summary', 'year', 'month'));
    }

    // ── Helpers ───────────────────────────────────────

    private function period(Request $request): array
    {
        $p = $request->input('periode', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $p)) {
            $p = now()->format('Y-m');
        }
        return array_map('intval', explode('-', $p));
    }

    private function summary($rows): array
    {
        return [
            'lwbp'   => $rows->sum('pakai_lwbp'),
            'wbp'    => $rows->sum('pakai_wbp'),
            'kwh'    => $rows->sum('pakai_kwh'),
            'air'    => $rows->sum('pakai_air'),
            'jumlah' => $rows->count(),
        ];
    }

    private function authorizeEdit(MeterReading $r): void
    {
        $u = Auth::user();
        if (in_array($u->role, ['admin', 'eng_admin', 'eng_spv'], true)) return;
        if ($u->role === 'teknisi') {
            abort(403, 'Role Teknisi hanya memiliki hak akses untuk memasukan data logsheet dan tidak dapat merubah atau mengedit data.');
        }
        if ($r->user_id !== $u->id || ! $r->tanggal->isToday()) {
            abort(403, 'Pengguna hanya dapat mengubah logsheet miliknya pada hari yang sama.');
        }
    }

    private function validated(Request $request, ?MeterReading $current = null): array
    {
        $canManage = in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
        $foto      = ($current ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png,webp|max:5120';

        $data = $request->validate([
            'tanggal'    => ['required', 'date', 'before_or_equal:today',
                             'unique:meter_readings,tanggal' . ($current ? ',' . $current->id : '')],
            'stand_lwbp' => 'required|numeric|min:0',
            'stand_wbp'  => 'required|numeric|min:0',
            'stand_air'  => 'required|numeric|min:0',
            'foto_kwh'   => $foto,
            'foto_air'   => $foto,
            'catatan'    => 'nullable|string|max:1000',
        ], [
            'tanggal.unique'          => 'Logsheet untuk tanggal ini sudah diinput.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'foto_kwh.required'       => 'Foto kWh meter wajib diunggah sebagai bukti.',
            'foto_air.required'       => 'Foto water meter wajib diunggah sebagai bukti.',
            'image'                   => 'File harus berupa gambar (jpg, png, webp).',
            'max'                     => 'Ukuran foto maksimal 5 MB.',
        ]);

        // Teknisi hanya boleh input untuk hari ini
        if (! $canManage && $data['tanggal'] !== today()->toDateString()) {
            back()->withInput()->withErrors(['tanggal' => 'Teknisi hanya dapat menginput logsheet hari ini.'])->throwResponse();
        }

        // Stand meter tidak boleh mundur / melompati pencatatan berikutnya
        $prev   = MeterReading::previousOf($data['tanggal']);
        $next   = MeterReading::nextOf($data['tanggal']);
        $labels = ['stand_lwbp' => 'LWBP', 'stand_wbp' => 'WBP', 'stand_air' => 'Air'];
        $errors = [];
        foreach ($labels as $k => $label) {
            if ($prev && $data[$k] < $prev->$k) {
                $errors[$k] = "Stand $label tidak boleh lebih kecil dari pencatatan sebelumnya (" . number_format($prev->$k, 2, ',', '.') . ').';
            } elseif ($next && $data[$k] > $next->$k) {
                $errors[$k] = "Stand $label tidak boleh lebih besar dari pencatatan berikutnya (" . number_format($next->$k, 2, ',', '.') . ').';
            }
        }
        if ($errors) {
            back()->withInput()->withErrors($errors)->throwResponse();
        }

        return $data;
    }
}
