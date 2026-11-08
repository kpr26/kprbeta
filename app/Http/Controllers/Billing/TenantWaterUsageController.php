<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantWaterReading;
use App\Models\BillingPeriodSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantWaterUsageController extends Controller
{
    /**
     * Tampilkan data penggunaan air tenant per bulan
     */
    public function index(Request $request)
    {
        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);
        $lantai = $request->input('lantai');
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $tenantQuery = Tenant::where('is_active', true);

        if (!empty($lantai) && $lantai !== 'all') {
            $tenantQuery->where('lantai', $lantai);
        }

        if (!empty($search)) {
            $tenantQuery->where(function ($q) use ($search) {
                $q->where('nama_tenant', 'like', "%{$search}%")
                  ->orWhere('nomor_unit', 'like', "%{$search}%")
                  ->orWhere('nomor_meter_air', 'like', "%{$search}%");
            });
        }

        $allTenants = $tenantQuery->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $readings = TenantWaterReading::with(['user', 'tenant'])
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->keyBy('tenant_id');

        $tenantList = $allTenants->map(function ($tenant) use ($readings, $year, $month) {
            $reading = $readings->get($tenant->id);
            $prevStand = $tenant->getPreviousWaterStand($year, $month);

            $awal = $reading ? (float)$reading->stand_awal : (float)$prevStand;
            $akhir = $reading ? (float)$reading->stand_akhir : null;
            $pakai = $reading ? (float)$reading->pemakaian : 0;
            $isTerkendala = $reading ? $reading->is_terkendala : false;

            return (object) [
                'tenant'         => $tenant,
                'reading'        => $reading,
                'prev_stand'     => $prevStand,
                'is_recorded'    => $reading !== null,
                'stand_awal'     => $awal,
                'stand_akhir'    => $akhir,
                'pemakaian'      => $pakai,
                'foto_url'       => $reading ? $reading->foto_url : null,
                'catatan'        => $reading ? $reading->catatan : null,
                'keterangan'     => $reading ? $reading->display_keterangan : null,
                'is_terkendala'  => $isTerkendala,
                'is_special_case'=> $reading ? (bool)$reading->is_special_case : false,
                'petugas'        => $reading?->user?->name ?? '—',
                'tanggal'        => $reading?->tanggal_catat?->format('d/m/Y') ?? '—',
            ];
        });

        if ($status === 'recorded') {
            $tenantList = $tenantList->filter(fn ($item) => $item->is_recorded);
        } elseif ($status === 'pending') {
            $tenantList = $tenantList->filter(fn ($item) => ! $item->is_recorded);
        } elseif ($status === 'terkendala') {
            $tenantList = $tenantList->filter(fn ($item) => $item->is_terkendala);
        }

        $totalWater     = $readings->sum('pemakaian');
        $totalRecorded  = $readings->count();
        $totalActive    = $allTenants->count();
        $totalPending   = max(0, $totalActive - $totalRecorded);
        $totalTerkendala= $readings->filter(fn ($r) => $r->is_terkendala)->count();
        $avgWater       = $totalRecorded > 0 ? round($totalWater / $totalRecorded, 3) : 0;
        $progressPct    = $totalActive > 0 ? round(($totalRecorded / $totalActive) * 100) : 0;

        $stats = [
            'total_water'      => $totalWater,
            'total_recorded'   => $totalRecorded,
            'total_active'     => $totalActive,
            'total_pending'    => $totalPending,
            'total_terkendala' => $totalTerkendala,
            'avg_water'        => $avgWater,
            'progress_pct'     => $progressPct,
        ];

        $allLantai = Tenant::select('lantai')->distinct()->whereNotNull('lantai')->orderByRaw('CAST(lantai AS UNSIGNED), lantai')->pluck('lantai');
        $setting   = BillingPeriodSetting::current();

        return view('billing.water.index', compact(
            'tenantList',
            'year',
            'month',
            'lantai',
            'status',
            'search',
            'stats',
            'allLantai',
            'setting'
        ));
    }

    /**
     * Form input pemakaian air untuk tenant tertentu
     */
    public function create(Request $request)
    {
        $setting = BillingPeriodSetting::current();
        if (! $setting->is_open && ! Auth::user()->isSuperAdmin()) {
            return view('billing.utilities.locked', compact('setting'));
        }

        $tenantId = $request->input('tenant_id');
        $year     = (int) $request->input('year', $setting->active_year ?? now()->year);
        $month    = (int) $request->input('month', $setting->active_month ?? now()->month);

        $tenants = Tenant::where('is_active', true)->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();
        $selectedTenant = $tenantId ? Tenant::find($tenantId) : $tenants->first();

        $prevStand = $selectedTenant ? $selectedTenant->getPreviousWaterStand($year, $month) : 0.000;
        $existingReading = $selectedTenant ? $selectedTenant->getWaterReadingFor($year, $month) : null;

        return view('billing.water.create', compact('tenants', 'selectedTenant', 'year', 'month', 'prevStand', 'existingReading'));
    }

    /**
     * Simpan / Update pembacaan air bulanan tenant
     */
    public function store(Request $request)
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        if (! BillingPeriodSetting::isOpen() && ! $isSuperAdmin) {
            return back()->withInput()->with('error', 'Sesi penginputan air saat ini sedang ditutup oleh Super Admin.');
        }

        $validated = $request->validate([
            'tenant_id'       => 'required|exists:tenants,id',
            'periode_tahun'   => 'required|integer|min:2020|max:2099',
            'periode_bulan'   => 'required|integer|min:1|max:12',
            'tanggal_catat'   => 'required|date',
            'stand_awal'      => 'required|numeric|min:0',
            'stand_akhir'     => 'required|numeric|min:0',
            'pemakaian'       => 'nullable|numeric|min:0',
            'foto_meter'      => 'nullable|image|max:10240',
            'catatan'         => 'nullable|string',
            'keterangan'      => 'nullable|string',
            'is_special_case' => 'nullable|boolean',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $standAwal = (float) $validated['stand_awal'];
        $standAkhir = (float) $validated['stand_akhir'];
        $isSpecial = $request->boolean('is_special_case') || ($standAkhir < $standAwal) || !empty($validated['keterangan']);

        // KASUS KHUSUS: STAND MUNDUR / ERROR METER
        // Hanya boleh diinput oleh role SUPER ADMIN
        if ($standAkhir < $standAwal) {
            if (! $isSuperAdmin) {
                return back()->withInput()->with('error', "Nilai Stand Akhir ({$standAkhir}) lebih kecil dari Stand Awal ({$standAwal}) [Stand Mundur / Meter Error]. Penginputan stand khusus ini hanya dapat dilakukan oleh Super Admin. Silakan laporkan kendala meter ke Super Admin.");
            }
        }

        $faktor = (float) ($tenant->faktor_kali_air ?: 1.0);

        if ($request->filled('pemakaian') && $isSuperAdmin) {
            // Super Admin dapat menentukan pemakaian khusus secara manual (misal meter rollover atau penggantian meter)
            $pemakaian = (float) $request->input('pemakaian');
        } else {
            $pemakaian = ($standAkhir - $standAwal) * $faktor;
            if ($pemakaian < 0) {
                $pemakaian = 0;
            }
        }

        $keteranganText = $validated['keterangan'] ?? $validated['catatan'] ?? null;
        if ($standAkhir < $standAwal && empty($keteranganText)) {
            $keteranganText = 'Stand Mundur (Kendala Meter)';
        }

        $data = [
            'tenant_id'       => $tenant->id,
            'periode_tahun'   => $validated['periode_tahun'],
            'periode_bulan'   => $validated['periode_bulan'],
            'tanggal_catat'   => $validated['tanggal_catat'],
            'stand_awal'      => $standAwal,
            'stand_akhir'     => $standAkhir,
            'pemakaian'       => round($pemakaian, 3),
            'catatan'         => $validated['catatan'] ?? $keteranganText,
            'keterangan'      => $keteranganText,
            'is_special_case' => $isSpecial,
            'user_id'         => Auth::id(),
        ];

        $reading = TenantWaterReading::where('tenant_id', $tenant->id)
            ->where('periode_tahun', $validated['periode_tahun'])
            ->where('periode_bulan', $validated['periode_bulan'])
            ->first();

        // ATURAN WAJIB FOTO WATER METER:
        // Wajib melampirkan foto meteran fisik (kecuali input manual Super Admin)
        $hasExistingPhoto = $reading && !empty($reading->foto_meter);
        if (! $isSuperAdmin && ! $request->hasFile('foto_meter') && ! $hasExistingPhoto) {
            return back()->withInput()->with('error', 'Wajib melampirkan foto fisik water meter sebagai bukti pencatatan meteran.');
        }

        if ($request->hasFile('foto_meter')) {
            if ($reading && $reading->foto_meter) {
                Storage::disk('public')->delete($reading->foto_meter);
            }
            $data['foto_meter'] = $request->file('foto_meter')->store('billing/water', 'public');
        }

        if ($reading) {
            $reading->update($data);
            $msg = "Data air tenant '{$tenant->nama_tenant}' periode {$validated['periode_bulan']}/{$validated['periode_tahun']} berhasil diperbarui.";
        } else {
            TenantWaterReading::create($data);
            $msg = "Data air tenant '{$tenant->nama_tenant}' periode {$validated['periode_bulan']}/{$validated['periode_tahun']} berhasil dicatat.";
        }

        if ($request->has('and_next')) {
            $nextTenant = Tenant::where('is_active', true)
                ->where('id', '>', $tenant->id)
                ->orderBy('id')
                ->first();

            if (! $nextTenant) {
                $nextTenant = Tenant::where('is_active', true)->orderBy('id')->first();
            }

            return redirect()->route('billing.water.create', [
                'tenant_id' => $nextTenant?->id,
                'year'      => $validated['periode_tahun'],
                'month'     => $validated['periode_bulan'],
            ])->with('success', $msg . " Lanjut input tenant berikutnya.");
        }

        return redirect()->route('billing.water.index', [
            'year'  => $validated['periode_tahun'],
            'month' => $validated['periode_bulan'],
        ])->with('success', $msg);
    }

    /**
     * Hapus pembacaan air
     */
    public function destroy(TenantWaterReading $waterReading)
    {
        if ($waterReading->foto_meter) {
            Storage::disk('public')->delete($waterReading->foto_meter);
        }

        $tenantName = $waterReading->tenant?->nama_tenant ?? 'Tenant';
        $periode = $waterReading->periode_label;

        $waterReading->delete();

        return back()->with('success', "Data penggunaan air '{$tenantName}' periode {$periode} berhasil dihapus.");
    }

    /**
     * Tampilkan Halaman Input Bulk Manual Air (Khusus Super Admin)
     */
    public function bulkCreate(Request $request)
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Fitur Input Bulk Manual hanya untuk Super Admin.');
        }

        $year         = (int) $request->input('year', now()->year);
        $month        = (int) $request->input('month', now()->month);
        $totalTenants = Tenant::where('is_active', true)->count();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$month] ?? (string)$month;

        return view('billing.water.bulk', compact('year', 'month', 'namaBulan', 'totalTenants'));
    }

    /**
     * Simpan Data Input Bulk Manual Air (Khusus Super Admin)
     */
    public function bulkStore(Request $request)
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Fitur Input Bulk Manual hanya untuk Super Admin.');
        }

        $request->validate([
            'periode_tahun' => 'required|integer|min:2020|max:2099',
            'periode_bulan' => 'required|integer|min:1|max:12',
            'tanggal_catat' => 'required|date',
            'file_csv'      => 'nullable|file|mimes:csv,txt,xls,xlsx|max:10240',
            'raw_csv'       => 'nullable|string',
        ]);

        $year    = (int) $request->input('periode_tahun');
        $month   = (int) $request->input('periode_bulan');
        $tanggal = $request->input('tanggal_catat');
        $userId  = Auth::id();

        $content = '';
        if ($request->hasFile('file_csv')) {
            $content = file_get_contents($request->file('file_csv')->getRealPath());
        } elseif ($request->filled('raw_csv')) {
            $content = $request->input('raw_csv');
        } else {
            return back()->withInput()->with('error', 'Harap unggah file CSV/Excel atau tempel teks data.');
        }

        $parsedRows = $this->parseRawWaterCsvRows($content);

        if (empty($parsedRows)) {
            return back()->withInput()->with('error', 'Gagal memproses data air. Format baris tidak dikenali atau kosong.');
        }

        $imported = 0;
        $terkendalaCount = 0;

        DB::beginTransaction();
        try {
            foreach ($parsedRows as $r) {
                $cleanFloor = $r['clean_floor'];
                $unitCode   = $r['unit'];
                $awal       = $r['stand_awal'];
                $akhir      = $r['stand_akhir'];
                $pemakaian  = $r['pemakaian'];
                $ket        = $r['keterangan'];
                $isSpecial  = $r['is_special_case'];

                // Cari tenant yang sudah ada
                $tenant = Tenant::where('lantai', $cleanFloor)
                    ->where('nomor_unit', $unitCode)
                    ->first();

                if (! $tenant) {
                    $kodeTenant = 'TNT-' . $cleanFloor . '-' . str_replace(' ', '', $unitCode);
                    $namaTenant = 'Unit ' . $cleanFloor . ' ' . $unitCode;

                    $tenant = Tenant::create([
                        'nama_tenant'         => $namaTenant,
                        'kode_tenant'         => $kodeTenant,
                        'lantai'              => $cleanFloor,
                        'nomor_unit'          => $unitCode,
                        'nomor_meter_air'     => 'PAM-' . $cleanFloor . '-' . $unitCode,
                        'faktor_kali_air'     => 1.0000,
                        'is_active'           => true,
                    ]);
                }

                if ($isSpecial) {
                    $terkendalaCount++;
                }

                // Simpan atau perbarui pembacaan air
                TenantWaterReading::updateOrCreate(
                    [
                        'tenant_id'     => $tenant->id,
                        'periode_tahun' => $year,
                        'periode_bulan' => $month,
                    ],
                    [
                        'tanggal_catat'   => $tanggal,
                        'stand_awal'      => $awal,
                        'stand_akhir'     => $akhir,
                        'pemakaian'       => $pemakaian,
                        'catatan'         => $ket ?: null,
                        'keterangan'      => $ket ?: null,
                        'is_special_case' => $isSpecial,
                        'user_id'         => $userId,
                        'verified_at'     => now(),
                        'verified_by'     => $userId,
                    ]
                );

                $imported++;
            }

            DB::commit();

            $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month] ?? $month;
            $ketNote = $terkendalaCount > 0 ? " (Termasuk {$terkendalaCount} meter berstatus kendala/stand khusus)." : "";

            return redirect()->route('billing.water.index', ['year' => $year, 'month' => $month])
                ->with('success', "Input Bulk Air Berhasil! Tersimpan {$imported} data pencatatan air untuk periode {$namaBulan} {$year}{$ketNote}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data bulk air: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Template CSV Air Sesuai Format File Terlampir
     */
    public function downloadBulkTemplate(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $tenants = Tenant::where('is_active', true)->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();
        $namaBulan = [1 => 'JANUARY', 2 => 'FEBRUARY', 3 => 'MARCH', 4 => 'APRIL', 5 => 'MAY', 6 => 'JUNE', 7 => 'JULY', 8 => 'AUGUST', 9 => 'SEPTEMBER', 10 => 'OCTOBER', 11 => 'NOVEMBER', 12 => 'DECEMBER'][$month] ?? (string)$month;

        $lines = [];
        $lines[] = ",,RESIDENCES METER READING WATER,,,,,,,,,,,,,,,";
        $lines[] = ",,BULAN   : {$namaBulan} {$year},,,,,,,,Daftar EV Charging Tenant,,,,,,,";
        $lines[] = ",,,,,,,,,,,,,,,,,";
        $lines[] = ",No,Floor,Unit,AIR,,,Keterangan,,,No.,Unit,,,,,,";
        $lines[] = ",,,,Awal,Akhir,Pemakaian,,,,,,,,,,,";

        $evTenants = Tenant::where('has_ev_charger', true)->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();
        $evCount = $evTenants->count();

        $no = 1;
        foreach ($tenants as $index => $t) {
            $prevStand = $t->getPreviousWaterStand($year, $month);
            $awalStr = number_format($prevStand, 3, '.', '');

            $evRight = '';
            if ($index < $evCount) {
                $ev = $evTenants[$index];
                $evRight = ",,,," . ($index + 1) . "," . $ev->unit_label . ",,,,,,";
            } else {
                $evRight = ",,,,,,,,,,,";
            }

            $lines[] = sprintf(",%d,%s,%s,%s,,,,%s", $no++, $t->lantai, $t->nomor_unit, $awalStr, $evRight);
        }

        $lines[] = ",,,,,,,,,,,,,,,,,";
        $lines[] = ",,\"Proposed by,\",,,,,\"Approved by,\",,,,,,,,,,";
        $lines[] = ",,,,,,,,,,,,,,,,,";
        $lines[] = ",,,Frida ,,,,Triyoto ,,,,,,,,,,";
        $lines[] = ",,Admin Engineering ,,,,,Chief Engineering,,,,,,,,,,";

        $filename = "Template_Bulk_Water_{$year}_{$month}.csv";

        return response(implode("\r\n", $lines), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Export Excel (.xls) Laporan Pemakaian Air Tenant
     */
    public function exportExcel(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $reportData = $this->prepareWaterReportRows($year, $month);
        $filename   = "RESIDENCES_METER_READING_WATER_{$year}_{$month}.xls";

        $html = view('billing.water.export-excel', array_merge($reportData, [
            'year'  => $year,
            'month' => $month,
        ]))->render();

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Export CSV Sesuai Format File Terlampir
     */
    public function exportCsv(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $reportData = $this->prepareWaterReportRows($year, $month);
        $namaBulanUpper = strtoupper($reportData['namaBulan']);

        $lines = [];
        $lines[] = ",,RESIDENCES METER READING WATER,,,,,,,,,,,,,,,";
        $lines[] = ",,BULAN   : {$namaBulanUpper} {$year},,,,,,,,Daftar EV Charging Tenant,,,,,,,";
        $lines[] = ",,,,,,,,,,,,,,,,,";
        $lines[] = ",No,Floor,Unit,AIR,,,Keterangan,,,No.,Unit,,,,,,";
        $lines[] = ",,,,Awal,Akhir,Pemakaian,,,,,,,,,,,";

        $evTenants = $reportData['evTenants'];
        $evCount   = count($evTenants);

        foreach ($reportData['rows'] as $index => $r) {
            $evRight = '';
            if ($index < $evCount) {
                $ev = $evTenants[$index];
                $evRight = ",,,," . ($index + 1) . "," . $ev->unit_label . ",,,,,,";
            } else {
                $evRight = ",,,,,,,,,,,";
            }

            $ketEscaped = $r->keterangan ? '"' . str_replace('"', '""', $r->keterangan) . '"' : '';

            $line = sprintf(
                ",%d,%s,%s,%s,%s,%s,%s%s",
                $r->no,
                $r->floor,
                $r->unit,
                $r->awal,
                $r->akhir,
                $r->pemakaian,
                $ketEscaped,
                $evRight
            );
            $lines[] = $line;
        }

        // Baris Total
        $lines[] = sprintf(",,T O T A L,,%s,%s,%s,,,,,,,,,,,,", $reportData['totalAwal'], $reportData['totalAkhir'], $reportData['totalPemakaian']);
        $lines[] = ",,,,,,,,,,,,,,,,,";
        $lines[] = ",,\"Proposed by,\",,,,,\"Approved by,\",,,,,,,,,,";
        $lines[] = ",,,,,,,,,,,,,,,,,";
        $lines[] = ",,,Frida ,,,,Triyoto ,,,,,,,,,,";
        $lines[] = ",,Admin Engineering ,,,,,Chief Engineering,,,,,,,,,,";

        $filename = "RESIDENCES_METER_READING_WATER_{$year}_{$month}.csv";

        return response(implode("\r\n", $lines), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Export PDF (Print View) Sesuai Format File Terlampir
     */
    public function exportPdf(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $reportData = $this->prepareWaterReportRows($year, $month);

        return view('billing.water.export-pdf', array_merge($reportData, [
            'year'  => $year,
            'month' => $month,
        ]));
    }

    /**
     * Siapkan baris laporan air lengkap dengan kalkulasi dan tabel EV
     */
    private function prepareWaterReportRows(int $year, int $month): array
    {
        $tenants = Tenant::where('is_active', true)->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $readings = TenantWaterReading::with(['user', 'tenant'])
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->keyBy('tenant_id');

        $rows = [];
        $no = 1;
        $totalAwal = 0.0;
        $totalAkhir = 0.0;
        $totalPemakaian = 0.0;
        $countTerkendala = 0;

        foreach ($tenants as $t) {
            $reading = $readings->get($t->id);
            $prevStand = $t->getPreviousWaterStand($year, $month);

            $awal = $reading ? (float)$reading->stand_awal : (float)$prevStand;
            $akhir = $reading ? (float)$reading->stand_akhir : $awal;
            $pemakaian = $reading ? (float)$reading->pemakaian : ($akhir >= $awal ? ($akhir - $awal) : 0);
            $keterangan = $reading ? $reading->display_keterangan : null;
            $isSpecial = $reading ? $reading->is_terkendala : false;

            if ($isSpecial) {
                $countTerkendala++;
            }

            $totalAwal += $awal;
            $totalAkhir += $akhir;
            $totalPemakaian += $pemakaian;

            $rows[] = (object) [
                'no'          => $no++,
                'floor'       => $t->lantai,
                'unit'        => $t->nomor_unit,
                'awal'        => number_format($awal, 3, '.', ''),
                'akhir'       => number_format($akhir, 3, '.', ''),
                'pemakaian'   => number_format($pemakaian, 3, '.', ''),
                'keterangan'  => $keterangan,
                'is_special'  => $isSpecial,
                'reading'     => $reading,
                'tenant'      => $t,
            ];
        }

        $evTenants = Tenant::where('has_ev_charger', true)
            ->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')
            ->get();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$month] ?? (string)$month;

        return [
            'rows'           => $rows,
            'evTenants'      => $evTenants,
            'namaBulan'      => $namaBulan,
            'year'           => $year,
            'month'          => $month,
            'totalAwal'      => number_format($totalAwal, 3, '.', ''),
            'totalAkhir'     => number_format($totalAkhir, 3, '.', ''),
            'totalPemakaian' => number_format($totalPemakaian, 3, '.', ''),
            'countTerkendala'=> $countTerkendala,
        ];
    }

    /**
     * Parser cerdas untuk data CSV / Excel Copy-Paste Water Meter
     */
    private function parseRawWaterCsvRows(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        $parsed = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;

            // Abaikan header non-data
            if (stripos($trimmed, 'RESIDENCES METER READING') !== false) continue;
            if (stripos($trimmed, 'BULAN') !== false) continue;
            if (stripos($trimmed, 'Proposed by') !== false || stripos($trimmed, 'Approved by') !== false) continue;
            if (stripos($trimmed, 'Admin Engineering') !== false || stripos($trimmed, 'Chief Engineering') !== false) continue;
            if (stripos($trimmed, 'T O T A L') !== false) continue;

            $delimiter = (strpos($line, "\t") !== false) ? "\t" : ",";
            $cols = str_getcsv($line, $delimiter);

            // Bersihkan kolom
            $cols = array_map(function ($c) {
                return trim($c, " \t\n\r\0\x0B\"");
            }, $cols);

            if (empty(array_filter($cols, fn ($v) => $v !== ''))) continue;

            // Jika baris header kolom
            if (in_array('Unit', $cols, true) || in_array('UNIT', $cols, true) || in_array('Floor', $cols, true)) continue;
            if (in_array('Awal', $cols, true) || in_array('AWAL', $cols, true)) continue;

            // Format 1: Seperti attachment
            // ,No,Floor,Unit,Awal,Akhir,Pemakaian,Keterangan...
            // atau No,Floor,Unit,Awal,Akhir,Pemakaian,Keterangan
            $floorIdx = -1;
            $unitIdx  = -1;
            $awalIdx  = -1;
            $akhirIdx = -1;
            $pakaiIdx = -1;
            $ketIdx   = -1;

            if (isset($cols[0]) && is_numeric($cols[0])) {
                // No, Floor, Unit, Awal, Akhir, Pemakaian, Keterangan
                $floorIdx = 1;
                $unitIdx  = 2;
                $awalIdx  = 3;
                $akhirIdx = 4;
                $pakaiIdx = 5;
                $ketIdx   = 6;
            } elseif (isset($cols[1]) && is_numeric($cols[1])) {
                // ,No,Floor,Unit,Awal,Akhir,Pemakaian,Keterangan
                $floorIdx = 2;
                $unitIdx  = 3;
                $awalIdx  = 4;
                $akhirIdx = 5;
                $pakaiIdx = 6;
                $ketIdx   = 7;
            } elseif (count($cols) >= 4) {
                // Default floor di index 0 atau 1
                $floorIdx = 0;
                $unitIdx  = 1;
                $awalIdx  = 2;
                $akhirIdx = 3;
                $pakaiIdx = 4;
                $ketIdx   = 5;
            } else {
                continue;
            }

            if (! isset($cols[$floorIdx]) || ! isset($cols[$unitIdx])) continue;

            $floorRaw = $cols[$floorIdx];
            $unitCode = $cols[$unitIdx];

            // Abaikan slot EV Car karena tidak ada meteran air
            if (stripos($floorRaw, 'CAR') !== false || stripos($unitCode, 'CAR') !== false) {
                continue;
            }

            if (empty($floorRaw) || empty($unitCode)) continue;

            $cleanFloor = trim(str_ireplace(['/ CAR', '/CAR', 'CAR', '/'], '', $floorRaw));
            if (! is_numeric($cleanFloor)) {
                // jika floor bukan angka (misal header atau kosong)
                continue;
            }

            $rawAwal  = isset($cols[$awalIdx]) ? str_replace([',', ' '], '', $cols[$awalIdx]) : '0';
            $rawAkhir = isset($cols[$akhirIdx]) ? str_replace([',', ' '], '', $cols[$akhirIdx]) : '0';
            $rawPakai = isset($cols[$pakaiIdx]) ? str_replace([',', ' '], '', $cols[$pakaiIdx]) : '0';
            $ket      = isset($cols[$ketIdx]) && !empty($cols[$ketIdx]) ? $cols[$ketIdx] : null;

            // Validasi numeric
            $awal  = is_numeric($rawAwal) ? (float) $rawAwal : 0.0;
            $akhir = is_numeric($rawAkhir) ? (float) $rawAkhir : $awal;
            $pakai = is_numeric($rawPakai) ? (float) $rawPakai : ($akhir >= $awal ? ($akhir - $awal) : 0.0);

            $isSpecial = ($akhir < $awal) || !empty($ket);

            $parsed[] = [
                'clean_floor'     => $cleanFloor,
                'unit'            => $unitCode,
                'stand_awal'      => round($awal, 3),
                'stand_akhir'     => round($akhir, 3),
                'pemakaian'       => round($pakai, 3),
                'keterangan'      => $ket,
                'is_special_case' => $isSpecial,
            ];
        }

        return $parsed;
    }
}
