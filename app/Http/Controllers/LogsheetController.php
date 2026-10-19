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

    /** Halaman Hub / Menu Pilihan Logsheet (Khusus Mobile/Teknisi) */
    public function hub()
    {
        $today = MeterReading::whereDate('tanggal', today())->first();
        $prevElec = MeterReading::previousElectricOf(today()->toDateString());
        $prevWater = MeterReading::previousWaterOf(today()->toDateString());

        return view('logsheet.hub', compact('today', 'prevElec', 'prevWater'));
    }

    public function create()
    {
        // Jika teknisi membuka menu input, arahkan ke Hub pemilihan meter
        if (Auth::user()->role === 'teknisi') {
            return redirect()->route('logsheet.hub');
        }

        $last  = MeterReading::orderByDesc('tanggal')->first();
        $today = MeterReading::whereDate('tanggal', today())->first();
        return view('logsheet.form', [
            'reading' => new MeterReading(['tanggal' => today()]),
            'last'    => $last,
            'today'   => $today,
        ]);
    }

    /** Halaman Input Khusus Listrik (kWh Meter) */
    public function createListrik()
    {
        $today = MeterReading::whereDate('tanggal', today())->first();

        // Teknisi tidak dapat mengedit / membuka form jika stand listrik hari ini sudah tercatat
        if (Auth::user()->role === 'teknisi' && $today && $today->foto_kwh) {
            return redirect()->route('logsheet.hub')->with('warning', 'Stand listrik hari ini sudah berhasil diinput. Akun teknisi tidak dapat mengedit atau mengubah data yang sudah masuk.');
        }

        $last  = MeterReading::previousElectricOf(today()->addDay()->toDateString());
        return view('logsheet.form-listrik', [
            'reading' => $today ?? new MeterReading(['tanggal' => today()]),
            'last'    => $last,
            'today'   => $today,
        ]);
    }

    /** Simpan Input Khusus Listrik */
    public function storeListrik(Request $request)
    {
        $canManage = in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
        $todayRecord = MeterReading::whereDate('tanggal', today())->first();
        $isExisting = $todayRecord && $todayRecord->foto_kwh;

        // Teknisi dilarang memperbarui / menimpa logsheet listrik yang sudah diinput
        if (Auth::user()->role === 'teknisi' && $isExisting) {
            abort(403, 'Role Teknisi tidak memiliki hak akses untuk merubah atau mengedit data logsheet yang sudah diinput.');
        }

        $data = $request->validate([
            'stand_lwbp' => 'required|numeric|min:0',
            'stand_wbp'  => 'required|numeric|min:0',
            'foto_kwh'   => ($isExisting ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png,webp|max:5120',
            'catatan'    => 'nullable|string|max:1000',
        ], [
            'stand_lwbp.required' => 'Stand kWh LWBP wajib diisi.',
            'stand_wbp.required'  => 'Stand kWh WBP wajib diisi.',
            'foto_kwh.required'   => 'Foto kWh meter wajib diunggah sebagai bukti.',
            'image'               => 'File harus berupa gambar (jpg, png, webp).',
            'max'                 => 'Ukuran foto maksimal 5 MB.',
        ]);

        // Cek stand tidak boleh lebih kecil dari stand listrik sebelumnya
        $prev = MeterReading::previousElectricOf(today()->toDateString());
        if ($prev) {
            if ($data['stand_lwbp'] < $prev->stand_lwbp) {
                return back()->withInput()->withErrors([
                    'stand_lwbp' => 'Stand LWBP tidak boleh lebih kecil dari sebelumnya (' . number_format($prev->stand_lwbp, 2, ',', '.') . ').'
                ]);
            }
            if ($data['stand_wbp'] < $prev->stand_wbp) {
                return back()->withInput()->withErrors([
                    'stand_wbp' => 'Stand WBP tidak boleh lebih kecil dari sebelumnya (' . number_format($prev->stand_wbp, 2, ',', '.') . ').'
                ]);
            }
        }

        $record = MeterReading::firstOrNew(['tanggal' => today()->toDateString()]);
        $record->user_id = Auth::id();
        $record->stand_lwbp = $data['stand_lwbp'];
        $record->stand_wbp = $data['stand_wbp'];
        if ($request->hasFile('foto_kwh')) {
            if ($record->foto_kwh) {
                Storage::disk('public')->delete($record->foto_kwh);
            }
            $record->foto_kwh = $request->file('foto_kwh')->store('logsheet', 'public');
        }
        if ($data['catatan']) {
            $record->catatan = $record->catatan 
                ? $record->catatan . ' | [Listrik]: ' . $data['catatan'] 
                : $data['catatan'];
        }
        $record->save();

        $targetRoute = Auth::user()->role === 'teknisi' ? route('logsheet.hub') : route('logsheet.index');
        return redirect($targetRoute)->with('success', 'Logsheet Listrik tanggal ' . today()->translatedFormat('d M Y') . ' berhasil disimpan.');
    }

    /** Halaman Input Khusus Air (Water Meter) */
    public function createAir()
    {
        $today = MeterReading::whereDate('tanggal', today())->first();

        // Teknisi tidak dapat mengedit / membuka form jika stand air hari ini sudah tercatat
        if (Auth::user()->role === 'teknisi' && $today && $today->foto_air) {
            return redirect()->route('logsheet.hub')->with('warning', 'Stand air hari ini sudah berhasil diinput. Akun teknisi tidak dapat mengedit atau mengubah data yang sudah masuk.');
        }

        $last  = MeterReading::previousWaterOf(today()->addDay()->toDateString());
        return view('logsheet.form-air', [
            'reading' => $today ?? new MeterReading(['tanggal' => today()]),
            'last'    => $last,
            'today'   => $today,
        ]);
    }

    /** Simpan Input Khusus Air */
    public function storeAir(Request $request)
    {
        $canManage = in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
        $todayRecord = MeterReading::whereDate('tanggal', today())->first();
        $isExisting = $todayRecord && $todayRecord->foto_air;

        // Teknisi dilarang memperbarui / menimpa logsheet air yang sudah diinput
        if (Auth::user()->role === 'teknisi' && $isExisting) {
            abort(403, 'Role Teknisi tidak memiliki hak akses untuk merubah atau mengedit data logsheet yang sudah diinput.');
        }

        $data = $request->validate([
            'stand_air' => 'required|numeric|min:0',
            'foto_air'  => ($isExisting ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png,webp|max:5120',
            'catatan'   => 'nullable|string|max:1000',
        ], [
            'stand_air.required' => 'Stand Water Meter wajib diisi.',
            'foto_air.required'  => 'Foto water meter wajib diunggah sebagai bukti.',
            'image'              => 'File harus berupa gambar (jpg, png, webp).',
            'max'                => 'Ukuran foto maksimal 5 MB.',
        ]);

        // Cek stand tidak boleh lebih kecil dari stand air sebelumnya
        $prev = MeterReading::previousWaterOf(today()->toDateString());
        if ($prev && $data['stand_air'] < $prev->stand_air) {
            return back()->withInput()->withErrors([
                'stand_air' => 'Stand Air tidak boleh lebih kecil dari sebelumnya (' . number_format($prev->stand_air, 2, ',', '.') . ').'
            ]);
        }

        $record = MeterReading::firstOrNew(['tanggal' => today()->toDateString()]);
        $record->user_id = Auth::id();
        $record->stand_air = $data['stand_air'];
        if ($request->hasFile('foto_air')) {
            if ($record->foto_air) {
                Storage::disk('public')->delete($record->foto_air);
            }
            $record->foto_air = $request->file('foto_air')->store('logsheet', 'public');
        }
        if ($data['catatan']) {
            $record->catatan = $record->catatan 
                ? $record->catatan . ' | [Air]: ' . $data['catatan'] 
                : $data['catatan'];
        }
        $record->save();

        $targetRoute = Auth::user()->role === 'teknisi' ? route('logsheet.hub') : route('logsheet.index');
        return redirect($targetRoute)->with('success', 'Logsheet Air tanggal ' . today()->translatedFormat('d M Y') . ' berhasil disimpan.');
    }

    /** Halaman Input Manual Khusus Listrik (Super Admin - Tanggal Terlewat) */
    public function createManualListrik(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $tanggal = $request->input('tanggal', now()->subDay()->format('Y-m-d'));
        if (strtotime($tanggal) > strtotime(today()->toDateString())) {
            $tanggal = today()->toDateString();
        }

        $reading = MeterReading::whereDate('tanggal', $tanggal)->first() ?? new MeterReading(['tanggal' => $tanggal]);
        $prev = MeterReading::previousElectricOf($tanggal);
        $next = MeterReading::nextElectricOf($tanggal);

        return view('logsheet.manual-listrik', compact('reading', 'tanggal', 'prev', 'next'));
    }

    /** Simpan Input Manual Khusus Listrik (Super Admin - Tanggal Terlewat) */
    public function storeManualListrik(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $data = $request->validate([
            'tanggal'    => 'required|date|before_or_equal:today',
            'stand_lwbp' => 'required|numeric|min:0',
            'stand_wbp'  => 'required|numeric|min:0',
            'foto_kwh'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'catatan'    => 'nullable|string|max:1000',
        ], [
            'tanggal.required'        => 'Tanggal pencatatan wajib dipilih.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'stand_lwbp.required'     => 'Stand kWh LWBP wajib diisi.',
            'stand_wbp.required'      => 'Stand kWh WBP wajib diisi.',
            'foto_kwh.image'          => 'File bukti harus berupa gambar (jpg, png, webp).',
            'foto_kwh.max'            => 'Ukuran foto maksimal 5 MB.',
        ]);

        $prev = MeterReading::previousElectricOf($data['tanggal']);
        $next = MeterReading::nextElectricOf($data['tanggal']);

        $errors = [];
        if ($prev) {
            $prevDate = $prev->tanggal->translatedFormat('d/m/Y');
            if ($data['stand_lwbp'] < $prev->stand_lwbp) {
                $errors['stand_lwbp'] = "Stand LWBP tidak boleh lebih kecil dari pencatatan sebelumnya pada tanggal {$prevDate} (" . number_format($prev->stand_lwbp, 2, ',', '.') . " kWh).";
            }
            if ($data['stand_wbp'] < $prev->stand_wbp) {
                $errors['stand_wbp'] = "Stand WBP tidak boleh lebih kecil dari pencatatan sebelumnya pada tanggal {$prevDate} (" . number_format($prev->stand_wbp, 2, ',', '.') . " kWh).";
            }
        }
        if ($next) {
            $nextDate = $next->tanggal->translatedFormat('d/m/Y');
            if ($data['stand_lwbp'] > $next->stand_lwbp) {
                $errors['stand_lwbp'] = "Stand LWBP tidak boleh lebih besar dari pencatatan berikutnya pada tanggal {$nextDate} (" . number_format($next->stand_lwbp, 2, ',', '.') . " kWh).";
            }
            if ($data['stand_wbp'] > $next->stand_wbp) {
                $errors['stand_wbp'] = "Stand WBP tidak boleh lebih besar dari pencatatan berikutnya pada tanggal {$nextDate} (" . number_format($next->stand_wbp, 2, ',', '.') . " kWh).";
            }
        }

        if (!empty($errors)) {
            return back()->withInput()->withErrors($errors);
        }

        $record = MeterReading::whereDate('tanggal', $data['tanggal'])->first() ?? new MeterReading(['tanggal' => $data['tanggal']]);
        if (! $record->exists) {
            $record->user_id = Auth::id();
        }
        $record->stand_lwbp = $data['stand_lwbp'];
        $record->stand_wbp  = $data['stand_wbp'];

        if ($request->hasFile('foto_kwh')) {
            if ($record->foto_kwh) {
                Storage::disk('public')->delete($record->foto_kwh);
            }
            $record->foto_kwh = $request->file('foto_kwh')->store('logsheet', 'public');
        }

        if (!empty($data['catatan'])) {
            $record->catatan = $record->catatan 
                ? $record->catatan . ' | [Manual Listrik]: ' . $data['catatan'] 
                : $data['catatan'];
        }

        $record->verified_at = null;
        $record->verified_by = null;
        $record->save();

        $periode = date('Y-m', strtotime($data['tanggal']));
        $formattedDate = \Carbon\Carbon::parse($data['tanggal'])->translatedFormat('d F Y');
        return redirect()->route('logsheet.index', ['periode' => $periode])
            ->with('success', "Logsheet Listrik manual tanggal {$formattedDate} berhasil disimpan.");
    }

    /** Halaman Input Manual Khusus Air (Super Admin - Tanggal Terlewat) */
    public function createManualAir(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $tanggal = $request->input('tanggal', now()->subDay()->format('Y-m-d'));
        if (strtotime($tanggal) > strtotime(today()->toDateString())) {
            $tanggal = today()->toDateString();
        }

        $reading = MeterReading::whereDate('tanggal', $tanggal)->first() ?? new MeterReading(['tanggal' => $tanggal]);
        $prev = MeterReading::previousWaterOf($tanggal);
        $next = MeterReading::nextWaterOf($tanggal);

        return view('logsheet.manual-air', compact('reading', 'tanggal', 'prev', 'next'));
    }

    /** Simpan Input Manual Khusus Air (Super Admin - Tanggal Terlewat) */
    public function storeManualAir(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $data = $request->validate([
            'tanggal'   => 'required|date|before_or_equal:today',
            'stand_air' => 'required|numeric|min:0',
            'foto_air'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'catatan'   => 'nullable|string|max:1000',
        ], [
            'tanggal.required'        => 'Tanggal pencatatan wajib dipilih.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'stand_air.required'      => 'Stand Water Meter (m³) wajib diisi.',
            'foto_air.image'          => 'File bukti harus berupa gambar (jpg, png, webp).',
            'foto_air.max'            => 'Ukuran foto maksimal 5 MB.',
        ]);

        $prev = MeterReading::previousWaterOf($data['tanggal']);
        $next = MeterReading::nextWaterOf($data['tanggal']);

        $errors = [];
        if ($prev) {
            $prevDate = $prev->tanggal->translatedFormat('d/m/Y');
            if ($data['stand_air'] < $prev->stand_air) {
                $errors['stand_air'] = "Stand Air tidak boleh lebih kecil dari pencatatan sebelumnya pada tanggal {$prevDate} (" . number_format($prev->stand_air, 2, ',', '.') . " m³).";
            }
        }
        if ($next) {
            $nextDate = $next->tanggal->translatedFormat('d/m/Y');
            if ($data['stand_air'] > $next->stand_air) {
                $errors['stand_air'] = "Stand Air tidak boleh lebih besar dari pencatatan berikutnya pada tanggal {$nextDate} (" . number_format($next->stand_air, 2, ',', '.') . " m³).";
            }
        }

        if (!empty($errors)) {
            return back()->withInput()->withErrors($errors);
        }

        $record = MeterReading::whereDate('tanggal', $data['tanggal'])->first() ?? new MeterReading(['tanggal' => $data['tanggal']]);
        if (! $record->exists) {
            $record->user_id = Auth::id();
        }
        $record->stand_air = $data['stand_air'];

        if ($request->hasFile('foto_air')) {
            if ($record->foto_air) {
                Storage::disk('public')->delete($record->foto_air);
            }
            $record->foto_air = $request->file('foto_air')->store('logsheet', 'public');
        }

        if (!empty($data['catatan'])) {
            $record->catatan = $record->catatan 
                ? $record->catatan . ' | [Manual Air]: ' . $data['catatan'] 
                : $data['catatan'];
        }

        $record->verified_at = null;
        $record->verified_by = null;
        $record->save();

        $periode = date('Y-m', strtotime($data['tanggal']));
        $formattedDate = \Carbon\Carbon::parse($data['tanggal'])->translatedFormat('d F Y');
        return redirect()->route('logsheet.index', ['periode' => $periode])
            ->with('success', "Logsheet Air manual tanggal {$formattedDate} berhasil disimpan.");
    }

    /** Referensi Data Acuan Sebelum & Sesudah Tanggal Terpilih (AJAX API) */
    public function referenceManual(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $tanggal = $request->input('tanggal', today()->toDateString());
        $type = $request->input('type', 'listrik');

        $record = MeterReading::with('user')->whereDate('tanggal', $tanggal)->first();
        $dateCarbon = \Carbon\Carbon::parse($tanggal);

        if ($type === 'air') {
            $prev = MeterReading::previousWaterOf($tanggal);
            $next = MeterReading::nextWaterOf($tanggal);

            return response()->json([
                'tanggal' => $tanggal,
                'tanggal_formatted' => $dateCarbon->translatedFormat('l, d F Y'),
                'is_today' => $dateCarbon->isToday(),
                'exists' => $record && $record->stand_air !== null,
                'current' => $record ? [
                    'id' => $record->id,
                    'stand_air' => $record->stand_air,
                    'foto_air_url' => $record->foto_air ? asset('storage/' . $record->foto_air) : null,
                    'catatan' => $record->catatan,
                    'user_name' => $record->user->name ?? '-',
                    'created_at' => $record->created_at?->format('H:i'),
                ] : null,
                'prev' => $prev ? [
                    'tanggal' => $prev->tanggal->format('Y-m-d'),
                    'tanggal_formatted' => $prev->tanggal->translatedFormat('d F Y'),
                    'stand_air' => $prev->stand_air,
                    'stand_air_fmt' => number_format($prev->stand_air, 2, ',', '.'),
                ] : null,
                'next' => $next ? [
                    'tanggal' => $next->tanggal->format('Y-m-d'),
                    'tanggal_formatted' => $next->tanggal->translatedFormat('d F Y'),
                    'stand_air' => $next->stand_air,
                    'stand_air_fmt' => number_format($next->stand_air, 2, ',', '.'),
                ] : null,
            ]);
        } else {
            $prev = MeterReading::previousElectricOf($tanggal);
            $next = MeterReading::nextElectricOf($tanggal);

            return response()->json([
                'tanggal' => $tanggal,
                'tanggal_formatted' => $dateCarbon->translatedFormat('l, d F Y'),
                'is_today' => $dateCarbon->isToday(),
                'exists' => $record && ($record->stand_lwbp !== null || $record->stand_wbp !== null),
                'current' => $record ? [
                    'id' => $record->id,
                    'stand_lwbp' => $record->stand_lwbp,
                    'stand_wbp' => $record->stand_wbp,
                    'foto_kwh_url' => $record->foto_kwh ? asset('storage/' . $record->foto_kwh) : null,
                    'catatan' => $record->catatan,
                    'user_name' => $record->user->name ?? '-',
                    'created_at' => $record->created_at?->format('H:i'),
                ] : null,
                'prev' => $prev ? [
                    'tanggal' => $prev->tanggal->format('Y-m-d'),
                    'tanggal_formatted' => $prev->tanggal->translatedFormat('d F Y'),
                    'stand_lwbp' => $prev->stand_lwbp,
                    'stand_lwbp_fmt' => number_format($prev->stand_lwbp, 2, ',', '.'),
                    'stand_wbp' => $prev->stand_wbp,
                    'stand_wbp_fmt' => number_format($prev->stand_wbp, 2, ',', '.'),
                ] : null,
                'next' => $next ? [
                    'tanggal' => $next->tanggal->format('Y-m-d'),
                    'tanggal_formatted' => $next->tanggal->translatedFormat('d F Y'),
                    'stand_lwbp' => $next->stand_lwbp,
                    'stand_lwbp_fmt' => number_format($next->stand_lwbp, 2, ',', '.'),
                    'stand_wbp' => $next->stand_wbp,
                    'stand_wbp_fmt' => number_format($next->stand_wbp, 2, ',', '.'),
                ] : null,
            ]);
        }
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id']  = Auth::id();
        if ($request->hasFile('foto_kwh')) {
            $data['foto_kwh'] = $request->file('foto_kwh')->store('logsheet', 'public');
        }
        if ($request->hasFile('foto_air')) {
            $data['foto_air'] = $request->file('foto_air')->store('logsheet', 'public');
        }

        MeterReading::create($data);

        return redirect()->route('logsheet.index')->with('success', 'Logsheet tanggal ' . date('d/m/Y', strtotime($data['tanggal'])) . ' berhasil disimpan.');
    }

    public function show(MeterReading $logsheet)
    {
        $prev = MeterReading::previousOf($logsheet->tanggal);
        $prevElec = MeterReading::previousElectricOf($logsheet->tanggal);
        $prevWater = MeterReading::previousWaterOf($logsheet->tanggal);
        return view('logsheet.show', [
            'r' => $logsheet->load('user', 'verifier'),
            'prev' => $prev,
            'prevElec' => $prevElec,
            'prevWater' => $prevWater,
        ]);
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
        abort_unless(in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true), 403, 'Role Teknisi tidak memiliki hak akses untuk menghapus data logsheet.');
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
