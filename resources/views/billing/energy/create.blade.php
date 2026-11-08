@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ][$month] ?? (string)$month;
@endphp

@section('title', 'Input Stand Listrik Tenant - Billing System')
@section('page-title', 'Input Stand Meter Listrik')
@section('page-subtitle', 'Pencatatan Stand kWh Meter Tenant Periode ' . $namaBulan . ' ' . $year)

@section('panel')
<div class="panel" style="max-width:700px; margin:0 auto;">
    <div class="panel-head">
        <div>
            <h2>Input Stand kWh Meter Listrik Tenant</h2>
            <p>Masukkan angka stand meter bulan ini dan lampirkan foto meteran yang jelas.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('billing.energy.index', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                &larr; Data Listrik
            </a>
            <a href="{{ route('billing.quick-input', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                ⚡ Mode Cepat Semua Tenant
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('billing.energy.store') }}" enctype="multipart/form-data" style="margin-top:20px;">
        @csrf
        <input type="hidden" name="periode_tahun" value="{{ $year }}">
        <input type="hidden" name="periode_bulan" value="{{ $month }}">

        <!-- Card Pilih Tenant & Periode -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-bottom:20px;">
            <div class="form-row" style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:14px;">
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:6px;">Pilih Tenant: *</label>
                    <select name="tenant_id" id="tenantSelector" class="form-control" style="font-size:0.95rem; font-weight:600;" onchange="window.location.href='{{ route('billing.energy.create') }}?year={{ $year }}&month={{ $month }}&tenant_id=' + this.value">
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ $selectedTenant && $selectedTenant->id == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_tenant }} ({{ $t->unit_label }}) - Meter: {{ $t->nomor_meter_listrik ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:6px;">Tanggal Pencatatan:</label>
                    <input type="date" name="tanggal_catat" class="form-control" value="{{ old('tanggal_catat', $existingReading?->tanggal_catat?->toDateString() ?? now()->toDateString()) }}" required>
                </div>
            </div>

            <!-- Detail Info Tenant Terpilih -->
            @if($selectedTenant)
                <div style="background:var(--bg-main); border:1px solid rgba(234,179,8,0.3); border-radius:var(--radius-sm); padding:12px 14px; font-size:0.83rem; display:flex; flex-wrap:wrap; justify-content:space-between; gap:10px;">
                    <div>
                        <span style="color:var(--text-muted);">No. Meter ({{ $meterType === 'ev' ? 'EV' : 'Reguler' }}):</span>
                        <strong style="color:{{ $meterType === 'ev' ? '#38bdf8' : '#facc15' }}; font-family:monospace; margin-left:4px;">
                            {{ $meterType === 'ev' ? ($selectedTenant->nomor_meter_ev ?? 'KWH-EV') : ($selectedTenant->nomor_meter_listrik ?? '—') }}
                        </strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);">Daya:</span>
                        <strong style="color:var(--text-primary); margin-left:4px;">
                            {{ $meterType === 'ev' ? ((float)$selectedTenant->daya_ev . ' kVA') : ($selectedTenant->daya_listrik ? (float)$selectedTenant->daya_listrik . ' kVA' : ($selectedTenant->daya_listrik_va ? number_format($selectedTenant->daya_listrik_va, 0, ',', '.') . ' VA' : '—')) }}
                        </strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);">Faktor Kali (CT):</span>
                        <strong style="color:var(--text-primary); margin-left:4px;">{{ (float)$selectedTenant->faktor_kali_listrik }}x</strong>
                    </div>
                </div>

                @if($selectedTenant->has_ev_charger)
                    <div style="margin-top:12px; padding:10px 14px; background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <span style="font-size:0.82rem; font-weight:700; color:#38bdf8;">
                            🚗⚡ Unit ini memiliki Fasilitas EV Charger:
                        </span>
                        <div style="display:inline-flex; gap:12px;">
                            <label style="display:inline-flex; align-items:center; gap:6px; font-size:0.82rem; font-weight:600; cursor:pointer;">
                                <input type="radio" name="meter_type_switch" value="regular" {{ $meterType === 'regular' ? 'checked' : '' }} onchange="window.location.href='{{ route('billing.energy.create') }}?year={{ $year }}&month={{ $month }}&tenant_id={{ $selectedTenant->id }}&meter_type=regular'">
                                <span>⚡ Meteran Reguler</span>
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:6px; font-size:0.82rem; font-weight:600; cursor:pointer; color:#38bdf8;">
                                <input type="radio" name="meter_type_switch" value="ev" {{ $meterType === 'ev' ? 'checked' : '' }} onchange="window.location.href='{{ route('billing.energy.create') }}?year={{ $year }}&month={{ $month }}&tenant_id={{ $selectedTenant->id }}&meter_type=ev'">
                                <span>🚗 Meteran EV Charger</span>
                            </label>
                        </div>
                    </div>
                @endif
                <input type="hidden" name="meter_type" value="{{ $meterType }}">
            @else
                <input type="hidden" name="meter_type" value="regular">
            @endif
        </div>

        <!-- Card Input Angka Stand & Kalkulasi Otomatis -->
        <div style="background:var(--bg-card); border:1px solid rgba(234,179,8,0.4); border-radius:var(--radius-md); padding:20px; margin-bottom:20px;">
            <h3 style="font-size:0.95rem; font-weight:700; color:#facc15; text-transform:uppercase; margin:0 0 16px; display:flex; align-items:center; gap:8px;">
                <span>⚡ Angka Stand kWh Meter</span>
            </h3>

            <div class="form-row" style="display:grid; grid-template-columns:1fr 1.3fr; gap:16px; margin-bottom:14px;">
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:6px;">
                        Stand Awal (Bulan Lalu):
                    </label>
                    <input type="text" readonly class="form-control" style="background:var(--bg-main); font-weight:700; font-family:monospace; font-size:1.1rem; color:var(--text-secondary); cursor:not-allowed;" value="{{ number_format($prevStand, 2, ',', '.') }}">
                    <input type="hidden" name="stand_awal" id="standAwal" value="{{ $prevStand }}">
                    <small style="color:var(--text-muted); font-size:0.72rem;">*Diambil otomatis dari akhir bulan lalu</small>
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:700; color:#facc15; display:block; margin-bottom:6px;">
                        Stand Akhir Bulan Ini: *
                    </label>
                    <input type="number" step="0.01" inputmode="decimal" name="stand_akhir" id="standAkhir" class="form-control" style="font-size:1.3rem; font-weight:800; font-family:monospace; border-color:#facc15; padding:10px 14px;" placeholder="Contoh: 12540.50" value="{{ old('stand_akhir', $existingReading?->stand_akhir) }}" required oninput="calcLiveUsage()">
                    @error('stand_akhir')<span style="color:var(--danger); font-size:0.75rem;">{{ $message }}</span>@enderror
                </div>
            </div>

            <!-- Live Calculation & Error Warning -->
            <div id="liveCalcBox" style="display:none; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:16px;"></div>

            <!-- Box Khusus Super Admin: Penanganan Stand Khusus / Mundur / Rollover -->
            <div id="specialCaseBox" style="{{ old('is_special_case', $existingReading?->is_special_case) ? 'display:block;' : 'display:none;' }} margin-bottom:16px; padding:14px 16px; background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.3); border-radius:var(--radius-sm);">
                <div style="font-weight:700; color:#f59e0b; margin-bottom:8px; font-size:0.85rem;">
                    ⚙️ Opsi Penginputan Khusus Super Admin (Meter Mundur / Rollover / Penggantian Meter / Error)
                </div>
                <input type="hidden" name="is_special_case" id="isSpecialCaseInput" value="{{ old('is_special_case', $existingReading?->is_special_case ? 1 : 0) }}">

                <div style="display:grid; grid-template-columns:1fr 1.5fr; gap:12px; margin-top:8px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:4px;">
                            Pemakaian Manual (kWh):
                        </label>
                        <input type="number" step="0.01" name="pemakaian" id="manualPemakaian" class="form-control" style="font-family:monospace; font-weight:700;" placeholder="Opsional jika rollover" value="{{ old('pemakaian', $existingReading?->pemakaian) }}">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:4px;">
                            Keterangan Kendala / Alasan Khusus:
                        </label>
                        <input type="text" name="keterangan" id="keteranganKendala" class="form-control" placeholder="Contoh: Segel, Ganti Meter Baru, Meter Mundur" value="{{ old('keterangan', $existingReading?->display_keterangan) }}">
                    </div>
                </div>
            </div>

            <!-- Foto Meteran -->
            <div style="margin-top:16px; padding-top:16px; border-top:1px dashed var(--border-color);">
                <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:6px;">
                    Foto Bukti Fisik kWh Meter:
                    @if(!auth()->user()->isSuperAdmin())
                        <span style="color:var(--danger); font-size:0.75rem;">* (Wajib bagi Teknisi)</span>
                    @else
                        <span style="color:var(--text-muted); font-size:0.75rem;">(Opsional bagi Super Admin)</span>
                    @endif
                </label>
                <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                    <label class="btn btn-sm" style="background:rgba(234,179,8,0.15); color:#facc15; border-color:rgba(234,179,8,0.3); cursor:pointer; display:inline-flex; align-items:center; gap:8px; padding:8px 16px;">
                        📷 Ambil Foto Kamera / Upload
                        <input type="file" name="foto_meter" id="fotoInput" accept="image/*" capture="environment" style="display:none;" onchange="previewKwhPhoto(this)">
                    </label>
                    <span id="fotoStatusText" style="font-size:0.8rem; color:var(--text-muted);">
                        {{ $existingReading?->foto_meter ? '✓ Foto sebelumnya sudah ada' : 'Belum ada foto yang dipilih' }}
                    </span>
                </div>

                <div id="previewWrap" style="{{ $existingReading?->foto_url ? 'display:block;' : 'display:none;' }} margin-top:12px;">
                    <img id="previewImg" src="{{ $existingReading?->foto_url ?? '' }}" alt="Foto Meter" style="max-height:160px; border-radius:8px; border:1px solid var(--border-color); object-fit:cover;">
                </div>
            </div>

            <!-- Catatan -->
            <div style="margin-top:16px;">
                <label style="font-size:0.8rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Catatan (Opsional):</label>
                <input type="text" name="catatan" class="form-control" placeholder="Contoh: Kondisi segel aman, meteran berputar normal" value="{{ old('catatan', $existingReading?->catatan) }}">
            </div>
        </div>

        <!-- Tombol Aksi Simpan -->
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <a href="{{ route('billing.energy.index', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">Batal</a>
            <div style="display:flex; gap:10px;">
                <button type="submit" id="btnSubmitEnergy" class="btn btn-primary">
                    💾 Simpan Stand Listrik
                </button>
                <button type="submit" id="btnSubmitEnergyNext" name="and_next" value="1" class="btn" style="background:var(--success); color:#fff; border-color:var(--success); font-weight:700;">
                    Simpan &amp; Lanjut Tenant Berikutnya &rarr;
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
const isSuperAdmin = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};
const hasExistingPhoto = {{ ($existingReading && $existingReading->foto_meter) ? 'true' : 'false' }};
const faktor = {{ $selectedTenant ? (float)$selectedTenant->faktor_kali_listrik : 1.0 }};
const standAwalVal = {{ (float)$prevStand }};

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fotoInput = document.getElementById('fotoInput');
            if (!isSuperAdmin && !hasExistingPhoto && (!fotoInput.files || fotoInput.files.length === 0)) {
                e.preventDefault();
                alert('Wajib melampirkan foto fisik kWh meter sebagai bukti pencatatan meteran!');
                fotoInput.click();
                return false;
            }
        });
    }
});

function calcLiveUsage() {
    const inputVal = document.getElementById('standAkhir').value;
    const box = document.getElementById('liveCalcBox');
    const specialBox = document.getElementById('specialCaseBox');
    const isSpecialInput = document.getElementById('isSpecialCaseInput');
    const btnSubmit = document.getElementById('btnSubmitEnergy');
    const btnSubmitNext = document.getElementById('btnSubmitEnergyNext');

    if (inputVal === '') {
        box.style.display = 'none';
        if (specialBox) specialBox.style.display = 'none';
        btnSubmit.disabled = false;
        btnSubmitNext.disabled = false;
        return;
    }

    const akhir = parseFloat(inputVal) || 0;
    const usage = (akhir - standAwalVal) * faktor;

    box.style.display = 'block';

    if (akhir < standAwalVal) {
        if (!isSuperAdmin) {
            box.style.background = 'rgba(239,68,68,0.18)';
            box.style.border = '1px solid rgba(239,68,68,0.4)';
            box.style.color = '#f87171';
            box.innerHTML = `
                <div style="display:flex; align-items:flex-start; gap:10px;">
                    <span style="font-size:1.3rem;">⛔</span>
                    <div>
                        <strong style="display:block; margin-bottom:4px;">Akses Ditolak: Stand Akhir (${akhir}) Lebih Kecil dari Stand Awal (${standAwalVal})</strong>
                        <span>Kondisi stand mundur, meter rollover, atau kwh meter rusak/error <strong>hanya dapat diinput oleh Super Admin</strong>. Harap laporkan kendala meter ke Super Admin!</span>
                    </div>
                </div>
            `;
            btnSubmit.disabled = true;
            btnSubmitNext.disabled = true;
            btnSubmit.style.opacity = '0.4';
            btnSubmitNext.style.opacity = '0.4';
            if (specialBox) specialBox.style.display = 'none';
        } else {
            box.style.background = 'rgba(245,158,11,0.15)';
            box.style.border = '1px solid rgba(245,158,11,0.35)';
            box.style.color = '#f59e0b';
            box.innerHTML = `
                <div style="display:flex; align-items:flex-start; gap:10px;">
                    <span style="font-size:1.3rem;">⚠️</span>
                    <div>
                        <strong style="display:block; margin-bottom:4px;">Mode Stand Khusus (Super Admin)</strong>
                        <span>Stand akhir (${akhir}) lebih kecil dari stand awal (${standAwalVal}). Silakan lengkapi Pemakaian Manual &amp; Keterangan Kendala di bawah.</span>
                    </div>
                </div>
            `;
            btnSubmit.disabled = false;
            btnSubmitNext.disabled = false;
            btnSubmit.style.opacity = '1';
            btnSubmitNext.style.opacity = '1';
            if (specialBox) {
                specialBox.style.display = 'block';
                isSpecialInput.value = '1';
            }
        }
    } else {
        box.style.background = 'rgba(34,197,94,0.12)';
        box.style.border = '1px solid rgba(34,197,94,0.3)';
        box.style.color = '#22c55e';
        box.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span>Estimasi Pemakaian Listrik:</span>
                <strong style="font-size:1.15rem; font-family:monospace;">+${usage.toLocaleString('id-ID', { minimumFractionDigits: 2 })} kWh</strong>
            </div>
        `;
        btnSubmit.disabled = false;
        btnSubmitNext.disabled = false;
        btnSubmit.style.opacity = '1';
        btnSubmitNext.style.opacity = '1';
        if (specialBox && !document.getElementById('keteranganKendala').value) {
            specialBox.style.display = 'none';
            isSpecialInput.value = '0';
        }
    }
}

function previewKwhPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewWrap').style.display = 'block';
            document.getElementById('fotoStatusText').textContent = '✓ Foto siap: ' + input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Jalankan kalkulasi saat halaman termuat jika sudah ada value
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('standAkhir').value !== '') {
        calcLiveUsage();
    }
});
</script>
@endpush
@endsection
