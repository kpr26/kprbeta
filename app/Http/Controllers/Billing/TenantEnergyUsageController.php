<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantEnergyReading;
use App\Models\BillingPeriodSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantEnergyUsageController extends Controller
{
    /**
     * Tampilkan data penggunaan listrik tenant per bulan
     */
    public function index(Request $request)
    {
        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);
        $lantai = $request->input('lantai');
        $status = $request->input('status', 'all'); // all, recorded, pending
        $search = $request->input('search');

        // Ambil semua tenant aktif
        $tenantQuery = Tenant::where('is_active', true);

        if (!empty($lantai) && $lantai !== 'all') {
            $tenantQuery->where('lantai', $lantai);
        }

        if (!empty($search)) {
            $tenantQuery->where(function ($q) use ($search) {
                $q->where('nama_tenant', 'like', "%{$search}%")
                  ->orWhere('nomor_unit', 'like', "%{$search}%")
                  ->orWhere('nomor_meter_listrik', 'like', "%{$search}%")
                  ->orWhere('nomor_meter_ev', 'like', "%{$search}%");
            });
        }

        $allTenants = $tenantQuery->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        // Ambil semua pembacaan pada periode ini
        $readings = TenantEnergyReading::with(['user', 'tenant'])
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->groupBy('tenant_id');

        // Gabungkan data tenant dengan pembacaan bulanan (Regular & EV)
        $tenantList = $allTenants->map(function ($tenant) use ($readings, $year, $month) {
            $tReadings = $readings->get($tenant->id, collect());
            $regularReading = $tReadings->firstWhere('meter_type', 'regular');
            $evReading      = $tReadings->firstWhere('meter_type', 'ev');

            $prevStandRegular = $tenant->getPreviousEnergyStand($year, $month, 'regular');
            $prevStandEv      = $tenant->has_ev_charger ? $tenant->getPreviousEnergyStand($year, $month, 'ev') : 0.00;

            $isRecordedRegular = $regularReading !== null;
            $isRecordedEv      = $tenant->has_ev_charger ? ($evReading !== null) : true;
            $isFullyRecorded   = $isRecordedRegular && $isRecordedEv;

            return (object) [
                'tenant'               => $tenant,
                'regular_reading'      => $regularReading,
                'ev_reading'           => $evReading,
                'prev_stand_regular'   => $prevStandRegular,
                'prev_stand_ev'        => $prevStandEv,
                'is_recorded'          => $isRecordedRegular,
                'is_fully_recorded'    => $isFullyRecorded,
                'stand_awal_regular'   => $regularReading ? $regularReading->stand_awal : $prevStandRegular,
                'stand_akhir_regular'  => $regularReading ? $regularReading->stand_akhir : null,
                'pemakaian_regular'    => $regularReading ? $regularReading->pemakaian : 0,
                'foto_url_regular'     => $regularReading ? $regularReading->foto_url : null,
                'stand_awal_ev'        => $evReading ? $evReading->stand_awal : $prevStandEv,
                'stand_akhir_ev'       => $evReading ? $evReading->stand_akhir : null,
                'pemakaian_ev'         => $evReading ? $evReading->pemakaian : 0,
                'foto_url_ev'          => $evReading ? $evReading->foto_url : null,
                'catatan'              => $regularReading ? $regularReading->catatan : ($evReading?->catatan ?? null),
                'petugas'              => $regularReading?->user?->name ?? ($evReading?->user?->name ?? '—'),
                'tanggal'              => $regularReading?->tanggal_catat?->format('d/m/Y') ?? ($evReading?->tanggal_catat?->format('d/m/Y') ?? '—'),
            ];
        });

        // Filter status jika dipilih
        if ($status === 'recorded') {
            $tenantList = $tenantList->filter(fn ($item) => $item->is_recorded);
        } elseif ($status === 'pending') {
            $tenantList = $tenantList->filter(fn ($item) => ! $item->is_recorded);
        }

        // Hitung statistik
        $allFlatReadings = $readings->flatten();
        $totalKwh        = $allFlatReadings->sum('pemakaian');
        $totalBilling    = $allFlatReadings->sum(fn ($r) => $r->billing);
        $totalRecorded   = $allTenants->filter(function ($t) use ($readings) {
            return $readings->has($t->id);
        })->count();
        $totalActive     = $allTenants->count();
        $totalPending    = max(0, $totalActive - $totalRecorded);
        $avgKwh          = $totalRecorded > 0 ? round($totalKwh / $totalRecorded, 2) : 0;
        $progressPct     = $totalActive > 0 ? round(($totalRecorded / $totalActive) * 100) : 0;

        $stats = [
            'total_kwh'      => $totalKwh,
            'total_billing'  => $totalBilling,
            'total_recorded' => $totalRecorded,
            'total_active'   => $totalActive,
            'total_pending'  => $totalPending,
            'avg_kwh'        => $avgKwh,
            'progress_pct'   => $progressPct,
        ];

        $allLantai = Tenant::select('lantai')->distinct()->whereNotNull('lantai')->orderByRaw('CAST(lantai AS UNSIGNED), lantai')->pluck('lantai');
        $setting   = BillingPeriodSetting::current();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$month] ?? (string)$month;

        return view('billing.energy.index', compact(
            'tenantList',
            'year',
            'month',
            'namaBulan',
            'lantai',
            'status',
            'search',
            'stats',
            'allLantai',
            'setting'
        ));
    }

    /**
     * Form input pemakaian listrik untuk tenant tertentu
     */
    public function create(Request $request)
    {
        $setting = BillingPeriodSetting::current();
        if (! $setting->is_open && ! Auth::user()->isSuperAdmin()) {
            return view('billing.utilities.locked', compact('setting'));
        }

        $tenantId  = $request->input('tenant_id');
        $meterType = $request->input('meter_type', 'regular'); // regular atau ev
        $year      = (int) $request->input('year', $setting->active_year ?? now()->year);
        $month     = (int) $request->input('month', $setting->active_month ?? now()->month);

        $tenants = Tenant::where('is_active', true)->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();
        $selectedTenant = $tenantId ? Tenant::find($tenantId) : $tenants->first();

        $prevStand = $selectedTenant ? $selectedTenant->getPreviousEnergyStand($year, $month, $meterType) : 0.00;
        $existingReading = $selectedTenant ? $selectedTenant->getEnergyReadingFor($year, $month, $meterType) : null;

        return view('billing.energy.create', compact('tenants', 'selectedTenant', 'meterType', 'year', 'month', 'prevStand', 'existingReading'));
    }

    /**
     * Simpan / Update pembacaan listrik bulanan tenant
     */
    public function store(Request $request)
    {
        if (! BillingPeriodSetting::isOpen() && ! Auth::user()->isSuperAdmin()) {
            return back()->withInput()->with('error', 'Sesi penginputan listrik saat ini sedang ditutup oleh Super Admin.');
        }

        $validated = $request->validate([
            'tenant_id'     => 'required|exists:tenants,id',
            'meter_type'    => 'nullable|in:regular,ev',
            'periode_tahun' => 'required|integer|min:2020|max:2099',
            'periode_bulan' => 'required|integer|min:1|max:12',
            'tanggal_catat' => 'required|date',
            'stand_awal'    => 'required|numeric|min:0',
            'stand_akhir'   => 'required|numeric|min:0',
            'foto_meter'    => 'nullable|image|max:10240',
            'catatan'       => 'nullable|string',
        ]);

        $meterType = $validated['meter_type'] ?? 'regular';
        $tenant    = Tenant::findOrFail($validated['tenant_id']);
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        $standAwal = (float) $validated['stand_awal'];
        $standAkhir = (float) $validated['stand_akhir'];
        $isSpecial = $request->boolean('is_special_case') || ($standAkhir < $standAwal) || !empty($request->input('keterangan'));

        // KASUS KHUSUS: STAND MUNDUR / KWH METER ERROR
        // Hanya boleh diinput oleh role SUPER ADMIN
        if ($standAkhir < $standAwal) {
            if (! $isSuperAdmin) {
                return back()->withInput()->with('error', "Nilai Stand Akhir ({$standAkhir}) lebih kecil dari Stand Awal ({$standAwal}) [Stand Mundur / kWh Meter Error]. Penginputan stand khusus ini hanya dapat dilakukan oleh Super Admin. Silakan laporkan kendala meter ke Super Admin.");
            }
        }

        $faktor = (float) ($tenant->faktor_kali_listrik ?: 1.0);

        if ($request->filled('pemakaian') && $isSuperAdmin) {
            $pemakaian = (float) $request->input('pemakaian');
        } else {
            $pemakaian = ($standAkhir - $standAwal) * $faktor;
            if ($pemakaian < 0) {
                $pemakaian = 0; // Proteksi rollover
            }
        }

        $keteranganText = $request->input('keterangan') ?: ($validated['catatan'] ?? null);
        if ($standAkhir < $standAwal && empty($keteranganText)) {
            $keteranganText = 'Stand Mundur (Kendala kWh Meter)';
        }

        $daya = ($meterType === 'ev') ? ($tenant->daya_ev ?: 0.00) : ($tenant->daya_listrik ?: 0.00);

        $data = [
            'tenant_id'       => $tenant->id,
            'meter_type'      => $meterType,
            'periode_tahun'   => $validated['periode_tahun'],
            'periode_bulan'   => $validated['periode_bulan'],
            'tanggal_catat'   => $validated['tanggal_catat'],
            'daya'            => $daya,
            'stand_awal'      => $standAwal,
            'stand_akhir'     => $standAkhir,
            'pemakaian'       => round($pemakaian, 2),
            'tarif_per_kwh'   => 1699.53,
            'catatan'         => $validated['catatan'] ?? $keteranganText,
            'keterangan'      => $keteranganText,
            'is_special_case' => $isSpecial,
            'user_id'         => Auth::id(),
        ];

        // Cari apakah sudah ada data untuk periode dan meter_type ini
        $reading = TenantEnergyReading::where('tenant_id', $tenant->id)
            ->where('periode_tahun', $validated['periode_tahun'])
            ->where('periode_bulan', $validated['periode_bulan'])
            ->where('meter_type', $meterType)
            ->first();

        // ATURAN WAJIB FOTO KWH METER:
        // Wajib melampirkan foto meteran fisik (kecuali input manual Super Admin)
        $hasExistingPhoto = $reading && !empty($reading->foto_meter);
        if (! $isSuperAdmin && ! $request->hasFile('foto_meter') && ! $hasExistingPhoto) {
            return back()->withInput()->with('error', 'Wajib melampirkan foto fisik kWh meter sebagai bukti pencatatan meteran.');
        }

        // Upload foto meteran
        if ($request->hasFile('foto_meter')) {
            if ($reading && $reading->foto_meter) {
                Storage::disk('public')->delete($reading->foto_meter);
            }
            $data['foto_meter'] = $request->file('foto_meter')->store('billing/energy', 'public');
        }

        $meterLabel = ($meterType === 'ev') ? 'EV Charger' : 'Reguler';

        if ($reading) {
            $reading->update($data);
            $msg = "Data listrik ({$meterLabel}) tenant '{$tenant->nama_tenant}' periode {$validated['periode_bulan']}/{$validated['periode_tahun']} berhasil diperbarui.";
        } else {
            TenantEnergyReading::create($data);
            $msg = "Data listrik ({$meterLabel}) tenant '{$tenant->nama_tenant}' periode {$validated['periode_bulan']}/{$validated['periode_tahun']} berhasil dicatat.";
        }

        if ($request->has('and_next')) {
            $nextTenant = Tenant::where('is_active', true)
                ->where('id', '>', $tenant->id)
                ->orderBy('id')
                ->first();

            if (! $nextTenant) {
                $nextTenant = Tenant::where('is_active', true)->orderBy('id')->first();
            }

            return redirect()->route('billing.energy.create', [
                'tenant_id'  => $nextTenant?->id,
                'meter_type' => $meterType,
                'year'       => $validated['periode_tahun'],
                'month'      => $validated['periode_bulan'],
            ])->with('success', $msg . " Lanjut input tenant berikutnya.");
        }

        return redirect()->route('billing.energy.index', [
            'year'  => $validated['periode_tahun'],
            'month' => $validated['periode_bulan'],
        ])->with('success', $msg);
    }

    /**
     * Hapus pembacaan listrik
     */
    public function destroy(TenantEnergyReading $energyReading)
    {
        if ($energyReading->foto_meter) {
            Storage::disk('public')->delete($energyReading->foto_meter);
        }

        $tenantName = $energyReading->tenant?->nama_tenant ?? 'Tenant';
        $meterLabel = $energyReading->meter_type === 'ev' ? 'EV Charger' : 'Reguler';
        $periode = $energyReading->periode_label;

        $energyReading->delete();

        return back()->with('success', "Data penggunaan listrik ({$meterLabel}) '{$tenantName}' periode {$periode} berhasil dihapus.");
    }

    /**
     * Tampilkan Halaman Input Bulk Manual Listrik (Khusus Super Admin)
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

        return view('billing.energy.bulk', compact('year', 'month', 'namaBulan', 'totalTenants'));
    }

    /**
     * Simpan Data Input Bulk Manual Listrik (Khusus Super Admin)
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
            return back()->withInput()->with('error', 'Harap unggah file CSV atau tempel teks data CSV.');
        }

        $parsedRows = $this->parseRawCsvRows($content);

        if (empty($parsedRows)) {
            return back()->withInput()->with('error', 'Gagal memproses data CSV. Format baris tidak dikenali atau kosong.');
        }

        $importedRegular = 0;
        $importedEv      = 0;

        DB::beginTransaction();
        try {
            foreach ($parsedRows as $r) {
                $cleanFloor = $r['clean_floor'];
                $unitCode   = $r['unit'];
                $isEv       = $r['is_ev'];
                $daya       = $r['daya'];
                $awal       = $r['stand_awal'];
                $akhir      = $r['stand_akhir'];
                $pemakaian  = $r['pemakaian'];
                $ket        = $r['keterangan'];

                // Cari tenant yang sudah ada
                $tenant = Tenant::where('lantai', $cleanFloor)
                    ->where('nomor_unit', $unitCode)
                    ->first();

                if (! $tenant) {
                    // Buat tenant baru jika belum ada
                    $kodeTenant = 'TNT-' . $cleanFloor . '-' . str_replace(' ', '', $unitCode);
                    $namaTenant = 'Unit ' . $cleanFloor . ' ' . $unitCode;

                    $tenant = Tenant::create([
                        'nama_tenant'         => $namaTenant,
                        'kode_tenant'         => $kodeTenant,
                        'lantai'              => $cleanFloor,
                        'nomor_unit'          => $unitCode,
                        'daya_listrik'        => $isEv ? 0.00 : $daya,
                        'daya_listrik_va'     => (int) round(($isEv ? 0.00 : $daya) * 1000),
                        'has_ev_charger'      => $isEv,
                        'daya_ev'             => $isEv ? $daya : null,
                        'nomor_meter_listrik' => 'KWH-' . $cleanFloor . '-' . $unitCode,
                        'nomor_meter_ev'      => $isEv ? 'KWH-' . $cleanFloor . '-' . $unitCode . '-EV' : null,
                        'nomor_meter_air'     => 'PAM-' . $cleanFloor . '-' . $unitCode,
                        'is_active'           => true,
                    ]);
                } else {
                    // Update daya / fasilitas EV jika belum terdata
                    if ($isEv) {
                        $tenant->update([
                            'has_ev_charger' => true,
                            'daya_ev'        => $daya > 0 ? $daya : $tenant->daya_ev,
                            'nomor_meter_ev' => $tenant->nomor_meter_ev ?: 'KWH-' . $cleanFloor . '-' . $unitCode . '-EV',
                        ]);
                    } else {
                        if ($daya > 0 && (float)$tenant->daya_listrik == 0) {
                            $tenant->update([
                                'daya_listrik'    => $daya,
                                'daya_listrik_va' => (int) round($daya * 1000),
                            ]);
                        }
                    }
                }

                $meterType = $isEv ? 'ev' : 'regular';

                // Simpan atau perbarui pembacaan
                TenantEnergyReading::updateOrCreate(
                    [
                        'tenant_id'     => $tenant->id,
                        'periode_tahun' => $year,
                        'periode_bulan' => $month,
                        'meter_type'    => $meterType,
                    ],
                    [
                        'tanggal_catat' => $tanggal,
                        'daya'          => $daya > 0 ? $daya : ($isEv ? $tenant->daya_ev : $tenant->daya_listrik),
                        'stand_awal'    => $awal,
                        'stand_akhir'   => $akhir,
                        'pemakaian'     => $pemakaian,
                        'tarif_per_kwh' => 1699.53,
                        'catatan'       => $ket ?: null,
                        'user_id'       => $userId,
                        'verified_at'   => now(),
                        'verified_by'   => $userId,
                    ]
                );

                if ($isEv) {
                    $importedEv++;
                } else {
                    $importedRegular++;
                }
            }

            DB::commit();

            $total = $importedRegular + $importedEv;
            $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month] ?? $month;

            return redirect()->route('billing.energy.index', ['year' => $year, 'month' => $month])
                ->with('success', "Input Bulk Berhasil! Tersimpan {$total} data pencatatan listrik ({$importedRegular} meteran reguler & {$importedEv} meteran EV Charger) untuk periode {$namaBulan} {$year}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data bulk: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Template CSV Sesuai Format File Terlampir
     */
    public function downloadBulkTemplate(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $tenants = Tenant::where('is_active', true)->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month] ?? $month;
        $filename = "Template_Input_Listrik_Tenant_{$year}_{$month}.csv";

        $output = [];
        $output[] = ",,RESIDENCES METER READING ELECTRICITY & WATER,,,,,,,,,,,,,,,,,,,,,,,";
        $output[] = ",,BULAN   : " . strtoupper($namaBulan) . " {$year},,,,,,,,,,,,,,,,Daftar EV Charging Tenant,,,,,,,";
        $output[] = ",No,Floor,Unit,LISTRIK,,,,\"TARIF (1699,53)\",,,,,,,Keterangan";
        $output[] = ",,,,Daya,Awal,Akhir,Pemakaian,Min Hour (40 Jam),Min Charge (40 Jam),Actual Charge,Billing,\"PPJ (2,40%)\",Biaya Materai,Total Tagihan";

        $rowNo = 1;
        foreach ($tenants as $t) {
            $prevStandReg = $t->getPreviousEnergyStand($year, $month, 'regular');
            $output[] = ",{$rowNo},{$t->lantai},{$t->nomor_unit},{$t->daya_listrik},{$prevStandReg},,";
            $rowNo++;

            if ($t->has_ev_charger) {
                $prevStandEv = $t->getPreviousEnergyStand($year, $month, 'ev');
                $output[] = ",{$rowNo},{$t->lantai}/ CAR,{$t->nomor_unit},{$t->daya_ev},{$prevStandEv},,";
                $rowNo++;
            }
        }

        $csvString = implode("\r\n", $output);

        return response($csvString, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Export Excel (.xls) Laporan Pemakaian Listrik Tenant Sesuai Format File Terlampir
     */
    public function exportExcel(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $reportData = $this->prepareReportRows($year, $month);

        $filename = "RESIDENCES_METER_READING_ELECTRICITY_{$year}_{$month}.xls";

        $html = view('billing.energy.export-excel', array_merge($reportData, [
            'year'      => $year,
            'month'     => $month,
        ]))->render();

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Export CSV Persis Sesuai File Terlampir
     */
    public function exportCsv(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $reportData = $this->prepareReportRows($year, $month);
        $namaBulanUpper = strtoupper($reportData['namaBulan']);

        $lines = [];
        $lines[] = ",,RESIDENCES METER READING ELECTRICITY & WATER,,,,,,,,,,,,,,,,,,,,,,,";
        $lines[] = ",,BULAN   : {$namaBulanUpper} {$year},,,,,,,,,,,,,,,,Daftar EV Charging Tenant,,,,,,,";
        $lines[] = ",,,,,,,,,,,,,,,,,,,,,,,,,";
        $lines[] = ",,,,,,,,,,,,,,,,,,,,,,,,,";
        $lines[] = ",No,Floor,Unit,LISTRIK,,,,\"TARIF (1699,53)\",,,,,,,Keterangan,,,No.,Unit,,,,,,";
        $lines[] = ",,,,Daya,Awal,Akhir,Pemakaian,Min Hour (40 Jam),Min Charge (40 Jam),Actual Charge,Billing,\"PPJ (2,40%)\",Biaya Materai,Total Tagihan,,,,,,,,,,,";

        $evList = $reportData['evList'];
        $evCount = count($evList);

        foreach ($reportData['rows'] as $index => $r) {
            $evRight = '';
            if ($index < $evCount) {
                $evItem = $evList[$index];
                $evRight = ",,,," . ($index + 1) . "," . $evItem->unit_label . ",,,,,,";
            } else {
                $evRight = ",,,,,,,,,,,";
            }

            $line = sprintf(
                ",%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s%s",
                $r->no,
                $r->floor,
                $r->unit,
                $r->daya,
                $r->awal,
                $r->akhir,
                $r->pemakaian,
                $r->min_hour,
                $r->min_charge,
                $r->actual_charge,
                $r->billing,
                $r->ppj,
                $r->materai,
                $r->total_tagihan,
                $r->keterangan ? '"' . str_replace('"', '""', $r->keterangan) . '"' : '',
                $evRight
            );
            $lines[] = $line;
        }

        // Baris Total
        $t = $reportData['totals'];
        $lines[] = sprintf(",,T O T A L,,,,,,,%s,%s,%s,,,%s,,,,,,,,,,,", $t['min_charge'], $t['actual_charge'], $t['billing'], $t['total_tagihan']);
        $lines[] = sprintf(",,,,,,,,,SELISIH,,%s,,,,,,,,,,,,,,", $t['selisih']);
        $lines[] = sprintf(",,,,,,,,,(%%),%s%%,%s%%,,,,,,,,,,,,,,", $t['pct_minus'], $t['pct_plus']);
        $lines[] = ",,,,,,,,,,,,,,,,,,,,,,,,,";
        $lines[] = ",,\"Proposed by,\",,,,,,,,,,,,,\"Approved by,\",,,,,,,,,,";
        $lines[] = ",,,,,,,,,,,,,,,,,,,,,,,,,";
        $lines[] = ",,,Frida ,,,,,,,,,,,,Triyoto ,,,,,,,,,,";
        $lines[] = ",,Admin Engineering ,,,,,,,,,,,,,Chief Engineering,,,,,,,,,,";

        $filename = "RESIDENCES_METER_READING_ELECTRICITY_{$year}_{$month}.csv";

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

        $reportData = $this->prepareReportRows($year, $month);

        return view('billing.energy.export-pdf', array_merge($reportData, [
            'year'  => $year,
            'month' => $month,
        ]));
    }

    /**
     * Siapkan baris laporan lengkap dengan rumus perhitungan persis seperti file CSV
     */
    private function prepareReportRows(int $year, int $month): array
    {
        $tenants = Tenant::where('is_active', true)->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $readings = TenantEnergyReading::with(['user', 'tenant'])
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->groupBy('tenant_id');

        $rows = [];
        $evList = [];
        $no = 1;

        $totMinCharge    = 0.0;
        $totActualCharge = 0.0;
        $totBilling      = 0.0;
        $totPpj          = 0.0;
        $totMaterai      = 0.0;
        $totTagihan      = 0.0;

        foreach ($tenants as $t) {
            $tReadings = $readings->get($t->id, collect());
            $regReading = $tReadings->firstWhere('meter_type', 'regular');
            $evReading  = $tReadings->firstWhere('meter_type', 'ev');

            $prevStandReg = $t->getPreviousEnergyStand($year, $month, 'regular');
            $awalReg      = $regReading ? (float)$regReading->stand_awal : $prevStandReg;
            $akhirReg     = $regReading ? (float)$regReading->stand_akhir : $awalReg;
            $pakaiReg     = $regReading ? (float)$regReading->pemakaian : ($akhirReg - $awalReg);
            $dayaReg      = $regReading?->daya ?: (float)$t->daya_listrik;
            $ketReg       = $regReading?->catatan;

            $minHourReg   = round($dayaReg * 40, 2);
            $minChgReg    = round($minHourReg * 1699.53, 2);
            $actChgReg    = round($pakaiReg * 1699.53, 2);
            $billingReg   = max($minChgReg, $actChgReg);
            $ppjReg       = round($billingReg * 0.024, 2);
            $matReg       = 10000.00;
            $totReg       = round($billingReg + $ppjReg + $matReg, 2);

            $rows[] = (object) [
                'no'            => $no++,
                'floor'         => $t->lantai,
                'unit'          => $t->nomor_unit,
                'is_ev'         => false,
                'daya'          => $dayaReg,
                'awal'          => $awalReg,
                'akhir'         => $akhirReg,
                'pemakaian'     => $pakaiReg,
                'min_hour'      => $minHourReg,
                'min_charge'    => $minChgReg,
                'actual_charge' => $actChgReg,
                'billing'       => $billingReg,
                'ppj'           => $ppjReg,
                'materai'       => $matReg,
                'total_tagihan' => $totReg,
                'keterangan'    => $ketReg,
            ];

            $totMinCharge    += $minChgReg;
            $totActualCharge += $actChgReg;
            $totBilling      += $billingReg;
            $totPpj          += $ppjReg;
            $totMaterai      += $matReg;
            $totTagihan      += $totReg;

            // Jika tenant memiliki EV Charger
            if ($t->has_ev_charger) {
                $evList[] = (object) [
                    'unit_label' => $t->lantai . ' ' . $t->nomor_unit,
                ];

                $prevStandEv = $t->getPreviousEnergyStand($year, $month, 'ev');
                $awalEv      = $evReading ? (float)$evReading->stand_awal : $prevStandEv;
                $akhirEv     = $evReading ? (float)$evReading->stand_akhir : $awalEv;
                $pakaiEv     = $evReading ? (float)$evReading->pemakaian : ($akhirEv - $awalEv);
                $dayaEv      = $evReading?->daya ?: (float)$t->daya_ev;
                $ketEv       = $evReading?->catatan;

                $minHourEv   = round($dayaEv * 40, 2);
                $minChgEv    = round($minHourEv * 1699.53, 2);
                $actChgEv    = round($pakaiEv * 1699.53, 2);
                $billingEv   = max($minChgEv, $actChgEv);
                $ppjEv       = round($billingEv * 0.024, 2);
                $matEv       = 10000.00;
                $totEv       = round($billingEv + $ppjEv + $matEv, 2);

                $rows[] = (object) [
                    'no'            => $no++,
                    'floor'         => $t->lantai . '/ CAR',
                    'unit'          => $t->nomor_unit,
                    'is_ev'         => true,
                    'daya'          => $dayaEv,
                    'awal'          => $awalEv,
                    'akhir'         => $akhirEv,
                    'pemakaian'     => $pakaiEv,
                    'min_hour'      => $minHourEv,
                    'min_charge'    => $minChgEv,
                    'actual_charge' => $actChgEv,
                    'billing'       => $billingEv,
                    'ppj'           => $ppjEv,
                    'materai'       => $matEv,
                    'total_tagihan' => $totEv,
                    'keterangan'    => $ketEv,
                ];

                $totMinCharge    += $minChgEv;
                $totActualCharge += $actChgEv;
                $totBilling      += $billingEv;
                $totPpj          += $ppjEv;
                $totMaterai      += $matEv;
                $totTagihan      += $totEv;
            }
        }

        $selisih = $totBilling - $totActualCharge;
        $pctMinus = $totBilling > 0 ? round(($totActualCharge - $totBilling) / $totBilling * 100, 2) : 0;
        $pctPlus  = $totBilling > 0 ? round(($selisih / $totBilling) * 100, 2) : 0;

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$month] ?? (string)$month;

        return [
            'rows'      => $rows,
            'evList'    => $evList,
            'namaBulan' => $namaBulan,
            'totals'    => [
                'min_charge'    => $totMinCharge,
                'actual_charge' => $totActualCharge,
                'billing'       => $totBilling,
                'ppj'           => $totPpj,
                'materai'       => $totMaterai,
                'total_tagihan' => $totTagihan,
                'selisih'       => $selisih,
                'pct_minus'     => $pctMinus,
                'pct_plus'      => $pctPlus,
            ],
        ];
    }

    /**
     * Parser cerdas untuk CSV / Excel text
     */
    private function parseRawCsvRows(string $content): array
    {
        $lines = preg_split('/[\r\n]+/', trim($content));
        $parsed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $delimiter = strpos($line, "\t") !== false ? "\t" : ",";
            $row = str_getcsv($line, $delimiter);

            // Bersihkan cell kosong di awal (misal format Excel ",No,Floor...")
            while (!empty($row) && trim($row[0]) === '') {
                array_shift($row);
            }

            if (empty($row)) continue;

            $firstCell = strtoupper(trim($row[0]));
            if (in_array($firstCell, ['RESIDENCES METER READING ELECTRICITY & WATER', 'BULAN', 'NO', 'NO.', 'FLOOR', 'T O T A L', 'TOTAL', 'SELISIH', '(%)', 'PROPOSED BY,', 'APPROVED BY,', ''])) {
                continue;
            }

            if (stripos($firstCell, 'RESIDENCES') !== false || stripos($firstCell, 'BULAN') !== false || stripos($firstCell, 'PROPOSED') !== false || stripos($firstCell, 'APPROVED') !== false) {
                continue;
            }

            $floorRaw = '';
            $unit = '';
            $daya = 0.0;
            $awal = 0.0;
            $akhir = 0.0;
            $keterangan = null;

            if (is_numeric($firstCell) && count($row) >= 5) {
                // Kolom 0 = No
                $floorRaw = trim($row[1]);
                $unit     = trim($row[2]);
                $daya     = isset($row[3]) ? (float) str_replace(',', '.', trim($row[3])) : 0.0;
                $awal     = isset($row[4]) ? (float) str_replace(',', '.', trim($row[4])) : 0.0;
                $akhir    = isset($row[5]) ? (float) str_replace(',', '.', trim($row[5])) : 0.0;
                if (isset($row[14]) && !empty(trim($row[14]))) {
                    $keterangan = trim($row[14]);
                } elseif (count($row) > 6 && !is_numeric(end($row))) {
                    $keterangan = trim(end($row));
                }
            } elseif (count($row) >= 4) {
                // Kolom 0 = Floor
                $floorRaw = trim($row[0]);
                $unit     = trim($row[1]);
                $daya     = isset($row[2]) ? (float) str_replace(',', '.', trim($row[2])) : 0.0;
                $awal     = isset($row[3]) ? (float) str_replace(',', '.', trim($row[3])) : 0.0;
                $akhir    = isset($row[4]) ? (float) str_replace(',', '.', trim($row[4])) : 0.0;
                if (isset($row[5]) && !empty(trim($row[5]))) {
                    $keterangan = trim($row[5]);
                }
            } else {
                continue;
            }

            if (empty($floorRaw) || empty($unit)) {
                continue;
            }

            $isEv = (stripos($floorRaw, 'CAR') !== false);
            $cleanFloor = trim(str_ireplace(['/ CAR', '/CAR', 'CAR', '/'], '', $floorRaw));

            $parsed[] = [
                'floor_raw'   => $floorRaw,
                'clean_floor' => $cleanFloor,
                'unit'        => $unit,
                'is_ev'       => $isEv,
                'daya'        => $daya,
                'stand_awal'  => $awal,
                'stand_akhir' => $akhir,
                'pemakaian'   => round($akhir - $awal, 2),
                'keterangan'  => $keterangan,
            ];
        }

        return $parsed;
    }
}
