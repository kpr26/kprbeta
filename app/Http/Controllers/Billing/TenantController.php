<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Pastikan hanya role Super Admin dan Supervisor yang dapat mengelola data tenant
     */
    private function authorizeTenantManager(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->canManageTenants()) {
            abort(403, 'Akses Ditolak: Fitur Kelola Tenant hanya dapat diakses oleh role Super Admin dan Supervisor.');
        }
    }

    /**
     * Tampilkan daftar tenant (Master Data Tenant)
     */
    public function index(Request $request)
    {
        $this->authorizeTenantManager();

        $search = $request->input('search');
        $lantai = $request->input('lantai');
        $status = $request->input('status', 'all');

        $query = Tenant::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_tenant', 'like', "%{$search}%")
                  ->orWhere('kode_tenant', 'like', "%{$search}%")
                  ->orWhere('nomor_unit', 'like', "%{$search}%")
                  ->orWhere('nomor_meter_listrik', 'like', "%{$search}%")
                  ->orWhere('nomor_meter_air', 'like', "%{$search}%");
            });
        }

        if (!empty($lantai) && $lantai !== 'all') {
            $query->where('lantai', $lantai);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $tenants = $query->orderBy('lantai')
                         ->orderBy('nomor_unit')
                         ->orderBy('nama_tenant')
                         ->paginate(15)
                         ->withQueryString();

        $allLantai = Tenant::select('lantai')
            ->distinct()
            ->whereNotNull('lantai')
            ->orderBy('lantai')
            ->pluck('lantai');

        $stats = [
            'total'    => Tenant::count(),
            'active'   => Tenant::where('is_active', true)->count(),
            'inactive' => Tenant::where('is_active', false)->count(),
        ];

        return view('billing.tenants.index', compact('tenants', 'allLantai', 'stats', 'search', 'lantai', 'status'));
    }

    /**
     * Form tambah tenant baru
     */
    public function create()
    {
        $this->authorizeTenantManager();

        return view('billing.tenants.create');
    }

    /**
     * Simpan tenant baru
     */
    public function store(Request $request)
    {
        $this->authorizeTenantManager();

        $validated = $request->validate([
            'nama_tenant'         => 'required|string|max:255',
            'kode_tenant'         => 'nullable|string|max:50|unique:tenants,kode_tenant',
            'lantai'              => 'required|string|max:50',
            'nomor_unit'          => 'nullable|string|max:50',
            'nomor_meter_listrik' => 'nullable|string|max:100',
            'nomor_meter_air'     => 'nullable|string|max:100',
            'faktor_kali_listrik' => 'nullable|numeric|min:0.0001',
            'faktor_kali_air'     => 'nullable|numeric|min:0.0001',
            'daya_listrik_va'     => 'nullable|integer|min:0',
            'daya_listrik'        => 'nullable|numeric|min:0',
            'has_ev_charger'      => 'nullable|boolean',
            'daya_ev'             => 'nullable|numeric|min:0',
            'nomor_meter_ev'      => 'nullable|string|max:100',
            'kontak_person'       => 'nullable|string|max:255',
            'no_telepon'          => 'nullable|string|max:50',
            'email'               => 'nullable|email|max:255',
            'is_active'           => 'nullable|boolean',
            'catatan'             => 'nullable|string',
        ]);

        $validated['is_active']      = $request->has('is_active');
        $validated['has_ev_charger'] = $request->has('has_ev_charger');
        $validated['faktor_kali_listrik'] = $validated['faktor_kali_listrik'] ?? 1.0000;
        $validated['faktor_kali_air']     = $validated['faktor_kali_air'] ?? 1.0000;

        $tenant = Tenant::create($validated);

        return redirect()->route('billing.tenants.index')
            ->with('success', "Tenant '{$tenant->nama_tenant}' berhasil ditambahkan ke sistem.");
    }

    /**
     * Detail tenant & riwayat konsumsi listrik dan air
     */
    public function show(Tenant $tenant)
    {
        $this->authorizeTenantManager();

        $energyReadings = $tenant->energyReadings()
            ->with(['user'])
            ->take(12)
            ->get();

        $waterReadings = $tenant->waterReadings()
            ->with(['user'])
            ->take(12)
            ->get();

        return view('billing.tenants.show', compact('tenant', 'energyReadings', 'waterReadings'));
    }

    /**
     * Form edit tenant
     */
    public function edit(Tenant $tenant)
    {
        $this->authorizeTenantManager();

        return view('billing.tenants.edit', compact('tenant'));
    }

    /**
     * Perbarui data tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $this->authorizeTenantManager();

        $validated = $request->validate([
            'nama_tenant'         => 'required|string|max:255',
            'kode_tenant'         => 'nullable|string|max:50|unique:tenants,kode_tenant,' . $tenant->id,
            'lantai'              => 'required|string|max:50',
            'nomor_unit'          => 'nullable|string|max:50',
            'nomor_meter_listrik' => 'nullable|string|max:100',
            'nomor_meter_air'     => 'nullable|string|max:100',
            'faktor_kali_listrik' => 'nullable|numeric|min:0.0001',
            'faktor_kali_air'     => 'nullable|numeric|min:0.0001',
            'daya_listrik_va'     => 'nullable|integer|min:0',
            'daya_listrik'        => 'nullable|numeric|min:0',
            'has_ev_charger'      => 'nullable|boolean',
            'daya_ev'             => 'nullable|numeric|min:0',
            'nomor_meter_ev'      => 'nullable|string|max:100',
            'kontak_person'       => 'nullable|string|max:255',
            'no_telepon'          => 'nullable|string|max:50',
            'email'               => 'nullable|email|max:255',
            'is_active'           => 'nullable|boolean',
            'catatan'             => 'nullable|string',
        ]);

        $validated['is_active']      = $request->has('is_active');
        $validated['has_ev_charger'] = $request->has('has_ev_charger');
        $validated['faktor_kali_listrik'] = $validated['faktor_kali_listrik'] ?? 1.0000;
        $validated['faktor_kali_air']     = $validated['faktor_kali_air'] ?? 1.0000;

        $tenant->update($validated);

        return redirect()->route('billing.tenants.index')
            ->with('success', "Data tenant '{$tenant->nama_tenant}' berhasil diperbarui.");
    }

    /**
     * Hapus tenant
     */
    public function destroy(Tenant $tenant)
    {
        $this->authorizeTenantManager();

        $nama = $tenant->nama_tenant;
        $tenant->delete();

        return redirect()->route('billing.tenants.index')
            ->with('success', "Tenant '{$nama}' berhasil dihapus dari sistem.");
    }
}
