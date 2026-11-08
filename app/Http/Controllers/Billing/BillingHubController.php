<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantEnergyReading;
use App\Models\TenantWaterReading;
use App\Models\BillingPeriodSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BillingHubController extends Controller
{
    /**
     * Halaman Utama / Utilities Hub Menu
     */
    public function index(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $totalTenants = Tenant::where('is_active', true)->count();

        // Data Energy
        $energyReadings = TenantEnergyReading::where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get();
        $energyCount = $energyReadings->count();
        $totalKwh    = $energyReadings->sum('pemakaian');

        // Data Water
        $waterReadings = TenantWaterReading::where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get();
        $waterCount  = $waterReadings->count();
        $totalWater  = $waterReadings->sum('pemakaian');

        // Both complete
        $energyTenantIds = $energyReadings->pluck('tenant_id')->toArray();
        $waterTenantIds  = $waterReadings->pluck('tenant_id')->toArray();
        $completeBoth    = count(array_intersect($energyTenantIds, $waterTenantIds));

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$month] ?? (string)$month;

        $setting = BillingPeriodSetting::current();

        return view('billing.utilities.hub', compact(
            'year',
            'month',
            'namaBulan',
            'totalTenants',
            'energyCount',
            'totalKwh',
            'waterCount',
            'totalWater',
            'completeBoth',
            'setting'
        ));
    }

    /**
     * Buka / Tutup Sesi Penginputan Utilitas (Khusus Super Admin)
     */
    public function togglePeriodSession(Request $request)
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan mengatur sesi penginputan utilitas.');
        }

        $action = $request->input('action');
        $year   = (int) $request->input('active_year', now()->year);
        $month  = (int) $request->input('active_month', now()->month);
        $note   = $request->input('keterangan');

        if ($action === 'open') {
            BillingPeriodSetting::openSession($year, $month, Auth::id(), $note);
            $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month] ?? $month;
            return back()->with('success', "Sesi penginputan meteran tenant periode {$namaBulan} {$year} BERHASIL DIBUKA untuk teknisi.");
        } else {
            BillingPeriodSetting::closeSession(Auth::id(), $note);
            return back()->with('warning', "Sesi penginputan meteran tenant BERHASIL DITUTUP (Terkunci). Teknisi tidak dapat menginput data baru.");
        }
    }

    /**
     * Mode Penginputan Cepat & Mudah untuk Teknisi (Quick Route / Checklist Input)
     */
    public function quickInput(Request $request)
    {
        $setting = BillingPeriodSetting::current();
        if (! $setting->is_open && ! Auth::user()->isSuperAdmin()) {
            return view('billing.utilities.locked', compact('setting'));
        }

        $year   = (int) $request->input('year', $setting->active_year ?? now()->year);
        $month  = (int) $request->input('month', $setting->active_month ?? now()->month);
        $lantai = $request->input('lantai');
        $filter = $request->input('filter', 'all'); // all, pending, done

        $tenantQuery = Tenant::where('is_active', true);
        if (!empty($lantai) && $lantai !== 'all') {
            $tenantQuery->where('lantai', $lantai);
        }

        $allTenants = $tenantQuery->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $energyReadings = TenantEnergyReading::where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->groupBy('tenant_id');

        $waterReadings = TenantWaterReading::where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->keyBy('tenant_id');

        $checklist = $allTenants->map(function ($tenant) use ($energyReadings, $waterReadings, $year, $month) {
            $tReadings = $energyReadings->get($tenant->id, collect());
            $regE = $tReadings->firstWhere('meter_type', 'regular');
            $evE  = $tReadings->firstWhere('meter_type', 'ev');
            $w    = $waterReadings->get($tenant->id);

            $hasRegular = $regE !== null;
            $hasEv      = $tenant->has_ev_charger ? ($evE !== null) : true;
            $hasEnergy  = $hasRegular && $hasEv;
            $hasWater   = $w !== null;

            $status = 'pending';
            if ($hasEnergy && $hasWater) {
                $status = 'done';
            } elseif ($hasRegular || ($tenant->has_ev_charger && $evE !== null) || $hasWater) {
                $status = 'partial';
            }

            return (object) [
                'tenant'          => $tenant,
                'status'          => $status,
                'has_energy'      => $hasEnergy,
                'has_regular'     => $hasRegular,
                'has_ev'          => $hasEv,
                'has_water'       => $hasWater,
                'energy_reading'  => $regE,
                'ev_reading'      => $evE,
                'water_reading'   => $w,
                'prev_energy'     => $tenant->getPreviousEnergyStand($year, $month, 'regular'),
                'prev_energy_ev'  => $tenant->has_ev_charger ? $tenant->getPreviousEnergyStand($year, $month, 'ev') : 0.00,
                'prev_water'      => $tenant->getPreviousWaterStand($year, $month),
            ];
        });

        if ($filter === 'pending') {
            $checklist = $checklist->filter(fn ($item) => $item->status !== 'done');
        } elseif ($filter === 'done') {
            $checklist = $checklist->filter(fn ($item) => $item->status === 'done');
        }

        $allLantai = Tenant::select('lantai')->distinct()->whereNotNull('lantai')->orderByRaw('CAST(lantai AS UNSIGNED), lantai')->pluck('lantai');

        $totalActive = $allTenants->count();
        $doneCount   = $checklist->filter(fn ($i) => $i->status === 'done')->count();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$month] ?? (string)$month;

        return view('billing.utilities.quick-input', compact(
            'checklist',
            'year',
            'month',
            'namaBulan',
            'lantai',
            'filter',
            'allLantai',
            'totalActive',
            'doneCount'
        ));
    }

    /**
     * Simpan Sekaligus (All-in-One: Listrik & Air) per Tenant
     */
    public function storeQuickInput(Request $request)
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();
        if (! BillingPeriodSetting::isOpen() && ! $isSuperAdmin) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi penginputan utilitas saat ini sedang ditutup oleh Super Admin.',
                ], 403);
            }
            return back()->withInput()->with('error', 'Sesi penginputan utilitas saat ini sedang ditutup oleh Super Admin.');
        }

        $validated = $request->validate([
            'tenant_id'          => 'required|exists:tenants,id',
            'periode_tahun'      => 'required|integer|min:2020|max:2099',
            'periode_bulan'      => 'required|integer|min:1|max:12',
            'tanggal_catat'      => 'required|date',
            // Listrik Reguler (Unit)
            'stand_awal_listrik' => 'nullable|numeric|min:0',
            'stand_akhir_listrik'=> 'nullable|numeric|min:0',
            'foto_meter_listrik' => 'nullable|image|max:10240',
            // Listrik EV Charger (Fasilitas EV)
            'stand_awal_ev'      => 'nullable|numeric|min:0',
            'stand_akhir_ev'     => 'nullable|numeric|min:0',
            'foto_meter_ev'      => 'nullable|image|max:10240',
            // Air
            'stand_awal_air'     => 'nullable|numeric|min:0',
            'stand_akhir_air'    => 'nullable|numeric|min:0',
            'foto_meter_air'     => 'nullable|image|max:10240',
            'catatan'            => 'nullable|string',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $userId = Auth::id();

        // VALIDASI STAND MUNDUR / ERROR METER (SEBELUM MEMULAI TRANSAKSI DATABASE)
        if (! $isSuperAdmin) {
            if ($request->filled('stand_akhir_listrik')) {
                $awalListrik = (float) ($validated['stand_awal_listrik'] ?? $tenant->getPreviousEnergyStand($validated['periode_tahun'], $validated['periode_bulan'], 'regular'));
                $akhirListrik = (float) $validated['stand_akhir_listrik'];
                if ($akhirListrik < $awalListrik) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stand akhir listrik ({$akhirListrik}) lebih kecil dari stand awal ({$awalListrik}) [Stand Mundur/Error]. Peran Teknisi tidak diizinkan menginput stand khusus ini. Harap hubungi Super Admin.",
                        ], 422);
                    }
                    return back()->withInput()->with('error', "Stand akhir listrik ({$akhirListrik}) lebih kecil dari stand awal ({$awalListrik}) [Stand Mundur/Error]. Peran Teknisi tidak diizinkan menginput stand khusus ini. Harap hubungi Super Admin.");
                }
            }

            if ($request->filled('stand_akhir_ev')) {
                $awalEv = (float) ($validated['stand_awal_ev'] ?? $tenant->getPreviousEnergyStand($validated['periode_tahun'], $validated['periode_bulan'], 'ev'));
                $akhirEv = (float) $validated['stand_akhir_ev'];
                if ($akhirEv < $awalEv) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stand akhir EV ({$akhirEv}) lebih kecil dari stand awal ({$awalEv}) [Stand Mundur/Error]. Peran Teknisi tidak diizinkan menginput stand khusus ini. Harap hubungi Super Admin.",
                        ], 422);
                    }
                    return back()->withInput()->with('error', "Stand akhir EV ({$akhirEv}) lebih kecil dari stand awal ({$awalEv}) [Stand Mundur/Error]. Peran Teknisi tidak diizinkan menginput stand khusus ini. Harap hubungi Super Admin.");
                }
            }

            if ($request->filled('stand_akhir_air')) {
                $awalAir = (float) ($validated['stand_awal_air'] ?? $tenant->getPreviousWaterStand($validated['periode_tahun'], $validated['periode_bulan']));
                $akhirAir = (float) $validated['stand_akhir_air'];
                if ($akhirAir < $awalAir) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stand akhir air ({$akhirAir}) lebih kecil dari stand awal ({$awalAir}) [Stand Mundur/Error Meter]. Peran Teknisi tidak diizinkan menginput stand khusus ini. Harap hubungi Super Admin.",
                        ], 422);
                    }
                    return back()->withInput()->with('error', "Stand akhir air ({$akhirAir}) lebih kecil dari stand awal ({$awalAir}) [Stand Mundur/Error Meter]. Peran Teknisi tidak diizinkan menginput stand khusus ini. Harap hubungi Super Admin.");
                }
            }

            // ATURAN WAJIB FOTO METERAN FISIK BAGI TEKNISI:
            if ($request->filled('stand_akhir_listrik')) {
                $eReading = TenantEnergyReading::where('tenant_id', $tenant->id)
                    ->where('periode_tahun', $validated['periode_tahun'])
                    ->where('periode_bulan', $validated['periode_bulan'])
                    ->where('meter_type', 'regular')
                    ->first();
                $hasEPhoto = ($eReading && !empty($eReading->foto_meter)) || $request->hasFile('foto_meter_listrik');
                if (! $hasEPhoto) {
                    $errMsg = "Wajib melampirkan foto fisik meteran listrik untuk unit {$tenant->unit_label}.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 422);
                    }
                    return back()->withInput()->with('error', $errMsg);
                }
            }

            if ($request->filled('stand_akhir_ev')) {
                $evReading = TenantEnergyReading::where('tenant_id', $tenant->id)
                    ->where('periode_tahun', $validated['periode_tahun'])
                    ->where('periode_bulan', $validated['periode_bulan'])
                    ->where('meter_type', 'ev')
                    ->first();
                $hasEvPhoto = ($evReading && !empty($evReading->foto_meter)) || $request->hasFile('foto_meter_ev');
                if (! $hasEvPhoto) {
                    $errMsg = "Wajib melampirkan foto fisik meteran EV Charger untuk unit {$tenant->unit_label}.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 422);
                    }
                    return back()->withInput()->with('error', $errMsg);
                }
            }

            if ($request->filled('stand_akhir_air')) {
                $wReading = TenantWaterReading::where('tenant_id', $tenant->id)
                    ->where('periode_tahun', $validated['periode_tahun'])
                    ->where('periode_bulan', $validated['periode_bulan'])
                    ->first();
                $hasWPhoto = ($wReading && !empty($wReading->foto_meter)) || $request->hasFile('foto_meter_air');
                if (! $hasWPhoto) {
                    $errMsg = "Wajib melampirkan foto fisik water meter untuk unit {$tenant->unit_label}.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 422);
                    }
                    return back()->withInput()->with('error', $errMsg);
                }
            }
        }

        DB::beginTransaction();
        try {
            // 1. Simpan Listrik Reguler jika ada stand akhir
            if ($request->filled('stand_akhir_listrik')) {
                $awalListrik = (float) ($validated['stand_awal_listrik'] ?? $tenant->getPreviousEnergyStand($validated['periode_tahun'], $validated['periode_bulan'], 'regular'));
                $akhirListrik = (float) $validated['stand_akhir_listrik'];

                $faktorListrik = (float) ($tenant->faktor_kali_listrik ?: 1.0);
                $pakaiListrik = max(0, ($akhirListrik - $awalListrik) * $faktorListrik);
                $isSpecialListrik = ($akhirListrik < $awalListrik) || !empty($validated['catatan']);

                $eReading = TenantEnergyReading::where('tenant_id', $tenant->id)
                    ->where('periode_tahun', $validated['periode_tahun'])
                    ->where('periode_bulan', $validated['periode_bulan'])
                    ->where('meter_type', 'regular')
                    ->first();

                $eData = [
                    'tenant_id'       => $tenant->id,
                    'meter_type'      => 'regular',
                    'periode_tahun'   => $validated['periode_tahun'],
                    'periode_bulan'   => $validated['periode_bulan'],
                    'tanggal_catat'   => $validated['tanggal_catat'],
                    'daya'            => $tenant->daya_listrik ?: 0.00,
                    'stand_awal'      => $awalListrik,
                    'stand_akhir'     => $akhirListrik,
                    'pemakaian'       => round($pakaiListrik, 2),
                    'tarif_per_kwh'   => 1699.53,
                    'catatan'         => $validated['catatan'] ?? null,
                    'keterangan'      => ($akhirListrik < $awalListrik) ? 'Stand Mundur/Error Listrik' : ($validated['catatan'] ?? null),
                    'is_special_case' => $isSpecialListrik,
                    'user_id'         => $userId,
                ];

                if ($request->hasFile('foto_meter_listrik')) {
                    if ($eReading && $eReading->foto_meter) {
                        Storage::disk('public')->delete($eReading->foto_meter);
                    }
                    $eData['foto_meter'] = $request->file('foto_meter_listrik')->store('billing/energy', 'public');
                }

                if ($eReading) {
                    $eReading->update($eData);
                } else {
                    TenantEnergyReading::create($eData);
                }
            }

            // 2. Simpan Listrik EV Charger jika diisi
            if ($request->filled('stand_akhir_ev')) {
                $awalEv = (float) ($validated['stand_awal_ev'] ?? $tenant->getPreviousEnergyStand($validated['periode_tahun'], $validated['periode_bulan'], 'ev'));
                $akhirEv = (float) $validated['stand_akhir_ev'];

                $pakaiEv = max(0, $akhirEv - $awalEv);
                $isSpecialEv = ($akhirEv < $awalEv);

                $evReading = TenantEnergyReading::where('tenant_id', $tenant->id)
                    ->where('periode_tahun', $validated['periode_tahun'])
                    ->where('periode_bulan', $validated['periode_bulan'])
                    ->where('meter_type', 'ev')
                    ->first();

                $evData = [
                    'tenant_id'       => $tenant->id,
                    'meter_type'      => 'ev',
                    'periode_tahun'   => $validated['periode_tahun'],
                    'periode_bulan'   => $validated['periode_bulan'],
                    'tanggal_catat'   => $validated['tanggal_catat'],
                    'daya'            => $tenant->daya_ev ?: 0.00,
                    'stand_awal'      => $awalEv,
                    'stand_akhir'     => $akhirEv,
                    'pemakaian'       => round($pakaiEv, 2),
                    'tarif_per_kwh'   => 1699.53,
                    'catatan'         => 'Meteran EV Charger',
                    'keterangan'      => ($akhirEv < $awalEv) ? 'Stand Mundur EV Charger' : 'Fasilitas EV Charging',
                    'is_special_case' => $isSpecialEv,
                    'user_id'         => $userId,
                ];

                if ($request->hasFile('foto_meter_ev')) {
                    if ($evReading && $evReading->foto_meter) {
                        Storage::disk('public')->delete($evReading->foto_meter);
                    }
                    $evData['foto_meter'] = $request->file('foto_meter_ev')->store('billing/energy', 'public');
                }

                if ($evReading) {
                    $evReading->update($evData);
                } else {
                    TenantEnergyReading::create($evData);
                }
            }

            // 3. Simpan Air jika ada stand akhir
            if ($request->filled('stand_akhir_air')) {
                $awalAir = (float) ($validated['stand_awal_air'] ?? $tenant->getPreviousWaterStand($validated['periode_tahun'], $validated['periode_bulan']));
                $akhirAir = (float) $validated['stand_akhir_air'];

                $faktorAir = (float) ($tenant->faktor_kali_air ?: 1.0);
                $pakaiAir = max(0, ($akhirAir - $awalAir) * $faktorAir);
                $isSpecialAir = ($akhirAir < $awalAir) || !empty($validated['catatan']);

                $wReading = TenantWaterReading::where('tenant_id', $tenant->id)
                    ->where('periode_tahun', $validated['periode_tahun'])
                    ->where('periode_bulan', $validated['periode_bulan'])
                    ->first();

                $wData = [
                    'tenant_id'       => $tenant->id,
                    'periode_tahun'   => $validated['periode_tahun'],
                    'periode_bulan'   => $validated['periode_bulan'],
                    'tanggal_catat'   => $validated['tanggal_catat'],
                    'stand_awal'      => $awalAir,
                    'stand_akhir'     => $akhirAir,
                    'pemakaian'       => round($pakaiAir, 3),
                    'catatan'         => $validated['catatan'] ?? null,
                    'keterangan'      => ($akhirAir < $awalAir) ? 'Stand Mundur/Error Water Meter' : ($validated['catatan'] ?? null),
                    'is_special_case' => $isSpecialAir,
                    'user_id'         => $userId,
                ];

                if ($request->hasFile('foto_meter_air')) {
                    if ($wReading && $wReading->foto_meter) {
                        Storage::disk('public')->delete($wReading->foto_meter);
                    }
                    $wData['foto_meter'] = $request->file('foto_meter_air')->store('billing/water', 'public');
                }

                if ($wReading) {
                    $wReading->update($wData);
                } else {
                    TenantWaterReading::create($wData);
                }
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Data meteran {$tenant->nama_tenant} berhasil disimpan!",
                    'tenant_id' => $tenant->id,
                ]);
            }

            return back()->with('success', "Data utilitas '{$tenant->nama_tenant}' berhasil disimpan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint: Mengambil data stand awal & pembacaan terkini tenant
     */
    public function getTenantStand(Request $request)
    {
        $tenantId = (int) $request->input('tenant_id');
        $year     = (int) $request->input('year', now()->year);
        $month    = (int) $request->input('month', now()->month);

        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            return response()->json(['error' => 'Tenant tidak ditemukan'], 404);
        }

        $prevEnergy = $tenant->getPreviousEnergyStand($year, $month, 'regular');
        $currEnergy = $tenant->getEnergyReadingFor($year, $month, 'regular');

        $prevEv     = $tenant->has_ev_charger ? $tenant->getPreviousEnergyStand($year, $month, 'ev') : 0.00;
        $currEv     = $tenant->has_ev_charger ? $tenant->getEnergyReadingFor($year, $month, 'ev') : null;

        $prevWater  = $tenant->getPreviousWaterStand($year, $month);
        $currWater  = $tenant->getWaterReadingFor($year, $month);

        return response()->json([
            'tenant' => [
                'id'                  => $tenant->id,
                'nama'                => $tenant->nama_tenant,
                'lantai'              => $tenant->lantai,
                'unit'                => $tenant->nomor_unit,
                'meter_listrik'       => $tenant->nomor_meter_listrik,
                'has_ev_charger'      => (bool) $tenant->has_ev_charger,
                'meter_ev'            => $tenant->nomor_meter_ev,
                'daya_ev'             => (float) $tenant->daya_ev,
                'meter_air'           => $tenant->nomor_meter_air,
                'faktor_listrik'      => (float) $tenant->faktor_kali_listrik,
                'faktor_air'          => (float) $tenant->faktor_kali_air,
            ],
            'energy' => [
                'prev_stand'   => $prevEnergy,
                'curr_stand'   => $currEnergy ? (float)$currEnergy->stand_akhir : null,
                'stand_awal'   => $currEnergy ? (float)$currEnergy->stand_awal : $prevEnergy,
                'pemakaian'    => $currEnergy ? (float)$currEnergy->pemakaian : null,
                'foto_url'     => $currEnergy ? $currEnergy->foto_url : null,
                'catatan'      => $currEnergy?->catatan,
                'is_recorded'  => $currEnergy !== null,
            ],
            'ev' => [
                'has_ev'       => (bool) $tenant->has_ev_charger,
                'prev_stand'   => $prevEv,
                'curr_stand'   => $currEv ? (float)$currEv->stand_akhir : null,
                'stand_awal'   => $currEv ? (float)$currEv->stand_awal : $prevEv,
                'pemakaian'    => $currEv ? (float)$currEv->pemakaian : null,
                'foto_url'     => $currEv ? $currEv->foto_url : null,
                'catatan'      => $currEv?->catatan,
                'is_recorded'  => $currEv !== null,
            ],
            'water' => [
                'prev_stand'   => $prevWater,
                'curr_stand'   => $currWater ? (float)$currWater->stand_akhir : null,
                'stand_awal'   => $currWater ? (float)$currWater->stand_awal : $prevWater,
                'pemakaian'    => $currWater ? (float)$currWater->pemakaian : null,
                'foto_url'     => $currWater ? $currWater->foto_url : null,
                'catatan'      => $currWater?->catatan,
                'is_recorded'  => $currWater !== null,
            ],
        ]);
    }

    /**
     * Galeri & Monitoring Bukti Foto Meteran Tenant
     */
    public function photoGallery(Request $request)
    {
        $year        = (int) $request->input('year', now()->year);
        $month       = (int) $request->input('month', now()->month);
        $lantai      = $request->input('lantai');
        $search      = $request->input('search');
        $filterPhoto = $request->input('photo_status', 'all');

        $tenantQuery = Tenant::where('is_active', true);
        if (!empty($lantai) && $lantai !== 'all') {
            $tenantQuery->where('lantai', $lantai);
        }
        if (!empty($search)) {
            $tenantQuery->where(function ($q) use ($search) {
                $q->where('nama_tenant', 'like', "%{$search}%")
                  ->orWhere('nomor_unit', 'like', "%{$search}%")
                  ->orWhere('nomor_meter_listrik', 'like', "%{$search}%")
                  ->orWhere('nomor_meter_air', 'like', "%{$search}%");
            });
        }
        $allTenants = $tenantQuery->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $energyReadings = TenantEnergyReading::with('user')
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->groupBy('tenant_id');

        $waterReadings = TenantWaterReading::with('user')
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->keyBy('tenant_id');

        $galleryList = $allTenants->map(function ($tenant) use ($energyReadings, $waterReadings) {
            $tEnergy = $energyReadings->get($tenant->id, collect());
            $regE = $tEnergy->firstWhere('meter_type', 'regular') ?? $tEnergy->where('meter_type', '!=', 'ev')->first() ?? $tEnergy->first();
            $evE  = $tEnergy->firstWhere('meter_type', 'ev') ?? $tEnergy->firstWhere('meter_type', 'ev_charger');
            $w    = $waterReadings->get($tenant->id);

            $hasAnyPhoto = ($regE && $regE->foto_meter) || ($evE && $evE->foto_meter) || ($w && $w->foto_meter);
            $hasAllPhotos = ($regE && $regE->foto_meter) && ($w && $w->foto_meter);
            if ($tenant->has_ev_charger) {
                $hasAllPhotos = $hasAllPhotos && ($evE && $evE->foto_meter);
            }

            return (object) [
                'tenant'         => $tenant,
                'energy'         => $regE,
                'ev'             => $evE,
                'water'          => $w,
                'has_any_photo'  => $hasAnyPhoto,
                'has_all_photos' => $hasAllPhotos,
            ];
        });

        if ($filterPhoto === 'has_photo') {
            $galleryList = $galleryList->filter(fn ($i) => $i->has_any_photo);
        } elseif ($filterPhoto === 'missing_photo') {
            $galleryList = $galleryList->filter(fn ($i) => ! $i->has_all_photos);
        }

        $allLantai = Tenant::select('lantai')->distinct()->whereNotNull('lantai')->orderByRaw('CAST(lantai AS UNSIGNED), lantai')->pluck('lantai');

        $totalTenants     = $allTenants->count();
        $totalPhotoEnergy = TenantEnergyReading::where('periode_tahun', $year)->where('periode_bulan', $month)->whereNotNull('foto_meter')->count();
        $totalPhotoWater  = TenantWaterReading::where('periode_tahun', $year)->where('periode_bulan', $month)->whereNotNull('foto_meter')->count();

        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month] ?? (string)$month;

        return view('billing.photos.index', compact(
            'galleryList',
            'year',
            'month',
            'namaBulan',
            'lantai',
            'search',
            'filterPhoto',
            'allLantai',
            'totalTenants',
            'totalPhotoEnergy',
            'totalPhotoWater'
        ));
    }

    /**
     * Export / Download ZIP Backup Semua Foto Meteran Bulanan
     */
    public function downloadPhotosZip(Request $request)
    {
        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);
        $type   = $request->input('type', 'all'); // 'all', 'energy', 'ev', 'water'
        $lantai = $request->input('lantai');

        $tenantQuery = Tenant::where('is_active', true);
        if (!empty($lantai) && $lantai !== 'all') {
            $tenantQuery->where('lantai', $lantai);
        }
        $tenants = $tenantQuery->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $energyReadings = TenantEnergyReading::with('user')
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->groupBy('tenant_id');

        $waterReadings = TenantWaterReading::with('user')
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->keyBy('tenant_id');

        $tempFile = tempnam(sys_get_temp_dir(), 'kpr_meter_photos_');
        $zip = new \ZipArchive();

        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file arsip ZIP foto.');
        }

        $manifestRows = [];
        $manifestRows[] = ['Jenis Meter', 'Lantai', 'Nomor Unit', 'No. Seri Meter', 'Stand Akhir', 'Pemakaian', 'Petugas', 'Tanggal Catat', 'File Foto'];
        $photosCount = 0;

        foreach ($tenants as $t) {
            $tEnergy = $energyReadings->get($t->id, collect());
            $regE = $tEnergy->firstWhere('meter_type', 'regular') ?? $tEnergy->where('meter_type', '!=', 'ev')->first() ?? $tEnergy->first();
            $evE  = $tEnergy->firstWhere('meter_type', 'ev') ?? $tEnergy->firstWhere('meter_type', 'ev_charger');
            $w    = $waterReadings->get($t->id);

            // 1. Foto Listrik Reguler
            if (($type === 'all' || $type === 'energy') && $regE && $regE->foto_meter) {
                if (Storage::disk('public')->exists($regE->foto_meter)) {
                    $content = Storage::disk('public')->get($regE->foto_meter);
                    $ext = pathinfo($regE->foto_meter, PATHINFO_EXTENSION) ?: 'jpg';
                    $zipEntry = "Listrik_Reguler/Lantai_{$t->lantai}/Unit_{$t->nomor_unit}_KWH_{$regE->stand_akhir}kwh.{$ext}";
                    $zip->addFromString($zipEntry, $content);
                    $photosCount++;
                    $manifestRows[] = [
                        'Listrik Reguler',
                        $t->lantai,
                        $t->nomor_unit,
                        $t->nomor_meter_listrik ?? '—',
                        $regE->stand_akhir,
                        $regE->pemakaian,
                        $regE->user?->name ?? '—',
                        $regE->tanggal_catat?->format('d/m/Y') ?? '—',
                        $zipEntry,
                    ];
                }
            }

            // 2. Foto Listrik EV
            if (($type === 'all' || $type === 'ev' || $type === 'energy') && $evE && $evE->foto_meter) {
                if (Storage::disk('public')->exists($evE->foto_meter)) {
                    $content = Storage::disk('public')->get($evE->foto_meter);
                    $ext = pathinfo($evE->foto_meter, PATHINFO_EXTENSION) ?: 'jpg';
                    $zipEntry = "Listrik_EV_Charger/Lantai_{$t->lantai}/Unit_{$t->nomor_unit}_EV_{$evE->stand_akhir}kwh.{$ext}";
                    $zip->addFromString($zipEntry, $content);
                    $photosCount++;
                    $manifestRows[] = [
                        'Listrik EV Charger',
                        $t->lantai,
                        $t->nomor_unit,
                        $t->nomor_meter_ev ?? '—',
                        $evE->stand_akhir,
                        $evE->pemakaian,
                        $evE->user?->name ?? '—',
                        $evE->tanggal_catat?->format('d/m/Y') ?? '—',
                        $zipEntry,
                    ];
                }
            }

            // 3. Foto Water Meter
            if (($type === 'all' || $type === 'water') && $w && $w->foto_meter) {
                if (Storage::disk('public')->exists($w->foto_meter)) {
                    $content = Storage::disk('public')->get($w->foto_meter);
                    $ext = pathinfo($w->foto_meter, PATHINFO_EXTENSION) ?: 'jpg';
                    $zipEntry = "Water_Meter/Lantai_{$t->lantai}/Unit_{$t->nomor_unit}_PAM_{$w->stand_akhir}m3.{$ext}";
                    $zip->addFromString($zipEntry, $content);
                    $photosCount++;
                    $manifestRows[] = [
                        'Water Meter',
                        $t->lantai,
                        $t->nomor_unit,
                        $t->nomor_meter_air ?? '—',
                        $w->stand_akhir,
                        $w->pemakaian,
                        $w->user?->name ?? '—',
                        $w->tanggal_catat?->format('d/m/Y') ?? '—',
                        $zipEntry,
                    ];
                }
            }
        }

        // Tulis manifest CSV ke dalam ZIP
        $csvBuffer = fopen('php://temp', 'r+');
        foreach ($manifestRows as $row) {
            fputcsv($csvBuffer, $row);
        }
        rewind($csvBuffer);
        $manifestContent = stream_get_contents($csvBuffer);
        fclose($csvBuffer);

        $zip->addFromString("MANIFEST_BUKTI_FOTO_{$year}_{$month}.csv", $manifestContent);

        if ($photosCount === 0) {
            $zip->addFromString("README.txt", "Belum ada bukti foto fisik meteran yang tersimpan untuk periode {$month}/{$year}. Foto dapat diunggah melalui menu Quick Input atau form pencatatan bulanan.");
        }

        $zip->close();

        $zipData = file_get_contents($tempFile);
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }

        $namaBulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'][$month] ?? $month;
        $downloadName = "Backup_Foto_Meter_{$namaBulan}_{$year}.zip";

        return response($zipData, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$downloadName}\"",
            'Content-Length'      => strlen($zipData),
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * Lembar Cetak Bukti Foto Meteran / Simpan PDF (Untuk Arsip & Bukti ke Tenant)
     */
    public function printPhotoReport(Request $request)
    {
        $year     = (int) $request->input('year', now()->year);
        $month    = (int) $request->input('month', now()->month);
        $tenantId = $request->input('tenant_id');

        $tenantQuery = Tenant::where('is_active', true);
        if ($tenantId) {
            $tenantQuery->where('id', $tenantId);
        }
        $tenants = $tenantQuery->orderByRaw('CAST(lantai AS UNSIGNED), lantai, nomor_unit')->get();

        $energyReadings = TenantEnergyReading::with('user')
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->groupBy('tenant_id');

        $waterReadings = TenantWaterReading::with('user')
            ->where('periode_tahun', $year)
            ->where('periode_bulan', $month)
            ->get()
            ->keyBy('tenant_id');

        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month] ?? (string)$month;

        return view('billing.photos.print', compact(
            'tenants',
            'energyReadings',
            'waterReadings',
            'year',
            'month',
            'namaBulan',
            'tenantId'
        ));
    }
}
