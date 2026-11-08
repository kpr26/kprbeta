@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $progressPct = $totalActive > 0 ? round(($doneCount / $totalActive) * 100) : 0;
@endphp

@section('title', 'Input Cepat Meteran Tenant - Billing System')
@section('page-title', 'Input Cepat Meteran Tenant')
@section('page-subtitle', 'Rute Checklist Bulanan: Stand Listrik (kWh) & Air (m³) Periode ' . $namaBulan . ' ' . $year)

@section('panel')
<div class="panel">
    <!-- Header & Navigasi -->
    <div class="panel-head" style="margin-bottom:16px;">
        <div>
            <h2>Checklist Rute Pencatatan Meteran Bulanan</h2>
            <p>Model penginputan terpadu agar teknisi dapat mencatat stand meter listrik &amp; air sekaligus dengan cepat, akurat, dan tanpa salah ketik.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('billing.utilities.hub', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                &larr; Kembali ke Utilities Hub
            </a>
            <a href="{{ route('billing.energy.index', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                ⚡ Data Listrik
            </a>
            <a href="{{ route('billing.photos', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#c084fc; border-color:rgba(192,132,252,0.4); font-weight:600;">
                📸 Bukti Foto Meteran
            </a>
            <a href="{{ route('billing.water.index', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                💧 Data Air
            </a>
        </div>
    </div>

    <!-- Progress Card Bulanan -->
    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px 20px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:10px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.1rem; font-weight:700; color:var(--text-primary);">Progress Rute Pencatatan:</span>
                <span class="badge {{ $progressPct === 100 ? 'badge-ok' : 'badge-wait' }}" style="font-size:0.85rem; padding:4px 10px;">
                    {{ $doneCount }} dari {{ $totalActive }} Tenant Selesai ({{ $progressPct }}%)
                </span>
            </div>
            <div style="font-size:0.8rem; color:var(--text-secondary);">
                <span>Belum Lengkap: <strong style="color:#f87171;">{{ max(0, $totalActive - $doneCount) }}</strong></span>
            </div>
        </div>
        <div style="height:10px; width:100%; background:var(--bg-main); border-radius:5px; overflow:hidden;">
            <div style="height:100%; width:{{ $progressPct }}%; background:linear-gradient(90deg, #38bdf8 0%, #22c55e 100%); border-radius:5px; transition:width 0.4s ease;"></div>
        </div>
    </div>

    <!-- Filter & Kontrol Rute -->
    <div class="filter-bar" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; background:var(--bg-card-subtle); padding:12px 16px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
        <form method="GET" action="{{ route('billing.quick-input') }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px;">
            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Periode:</span>
            <select name="month" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    @php $bNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember']; @endphp
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $bNames[$m] }}</option>
                @endfor
            </select>
            <select name="year" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-left:8px;">Lantai:</span>
            <select name="lantai" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all">Semua Lantai</option>
                @foreach($allLantai as $l)
                    <option value="{{ $l }}" {{ $lantai == $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>

            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-left:8px;">Status:</span>
            <select name="filter" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Semua ({{ $totalActive }})</option>
                <option value="pending" {{ $filter == 'pending' ? 'selected' : '' }}>🔴 Belum Selesai ({{ max(0, $totalActive - $doneCount) }})</option>
                <option value="done" {{ $filter == 'done' ? 'selected' : '' }}>🟢 Sudah Selesai ({{ $doneCount }})</option>
            </select>
        </form>

        <div style="font-size:0.82rem; color:var(--text-muted);">
            💡 <em>Klik kartu tenant untuk input / perbarui stand meter</em>
        </div>
    </div>

    <!-- Daftar Kartu Tenant (Checklist Rute) -->
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
        @forelse($checklist as $index => $item)
            @php
                $t = $item->tenant;
                $e = $item->energy_reading;
                $evReading = $item->ev_reading;
                $w = $item->water_reading;
                $isDone = $item->status === 'done';
                $isPartial = $item->status === 'partial';
            @endphp
            <div class="tenant-quick-card {{ $isDone ? 'is-done' : ($isPartial ? 'is-partial' : 'is-pending') }}"
                 id="tenant-card-{{ $t->id }}"
                 data-tenant-id="{{ $t->id }}"
                 data-tenant-nama="{{ $t->nama_tenant }}"
                 data-tenant-unit="{{ $t->unit_label }}"
                 data-tenant-meter-listrik="{{ $t->nomor_meter_listrik ?? '—' }}"
                 data-has-ev="{{ $t->has_ev_charger ? '1' : '0' }}"
                 data-tenant-meter-ev="{{ $t->nomor_meter_ev ?? 'KWH-EV' }}"
                 data-daya-ev="{{ (float)$t->daya_ev }}"
                 data-tenant-meter-air="{{ $t->nomor_meter_air ?? '—' }}"
                 data-faktor-listrik="{{ (float)$t->faktor_kali_listrik }}"
                 data-faktor-air="{{ (float)$t->faktor_kali_air }}"
                 data-prev-energy="{{ $item->prev_energy }}"
                 data-prev-energy-ev="{{ $item->prev_energy_ev }}"
                 data-prev-water="{{ $item->prev_water }}"
                 data-curr-energy="{{ $e ? (float)$e->stand_akhir : '' }}"
                 data-curr-energy-ev="{{ $evReading ? (float)$evReading->stand_akhir : '' }}"
                 data-curr-water="{{ $w ? (float)$w->stand_akhir : '' }}"
                 data-foto-energy="{{ $e?->foto_url ?? '' }}"
                 data-foto-energy-ev="{{ $evReading?->foto_url ?? '' }}"
                 data-foto-water="{{ $w?->foto_url ?? '' }}"
                 data-catatan="{{ $e?->catatan ?? ($w?->catatan ?? '') }}"
                 style="background:var(--bg-card); border:1px solid {{ $isDone ? 'rgba(34,197,94,0.3)' : ($isPartial ? 'rgba(234,179,8,0.3)' : 'var(--border-color)') }}; border-radius:var(--radius-md); padding:16px; display:flex; flex-direction:column; justify-content:space-between; position:relative; transition:transform 0.15s ease, border-color 0.15s ease;">
                
                <div>
                    <!-- Top Bar Kartu: Lokasi & Status -->
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="font-size:0.75rem; font-weight:700; color:var(--accent); text-transform:uppercase; letter-spacing:0.04em;">
                                    📍 {{ $t->unit_label }}
                                </span>
                                @if($t->has_ev_charger)
                                    <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; font-size:0.65rem; padding:1px 5px;">
                                        🚗⚡ EV Charger
                                    </span>
                                @endif
                            </div>
                            <h3 style="font-size:1.05rem; font-weight:700; margin:2px 0 0; color:var(--text-primary); line-height:1.3;">
                                {{ $t->nama_tenant }}
                            </h3>
                        </div>
                        <div>
                            @if($isDone)
                                <span class="badge badge-ok" style="font-size:0.72rem; padding:3px 8px;">✓ Lengkap</span>
                            @elseif($isPartial)
                                <span class="badge badge-wait" style="font-size:0.72rem; padding:3px 8px;">Sebagian</span>
                            @else
                                <span class="badge" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); font-size:0.72rem; padding:3px 8px;">Belum Diisi</span>
                            @endif
                        </div>
                    </div>

                    <!-- Informasi Stand Listrik & Air -->
                    <div style="display:grid; grid-template-columns:{{ $t->has_ev_charger ? '1fr 1fr 1fr' : '1fr 1fr' }}; gap:8px; margin-top:12px; padding:10px; background:var(--bg-main); border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                        <!-- Listrik Reguler -->
                        <div>
                            <div style="display:flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:700; color:#facc15; margin-bottom:4px;">
                                <span>⚡ LISTRIK</span>
                            </div>
                            <div style="font-size:0.68rem; color:var(--text-muted); margin-bottom:2px;">
                                Awal: <strong style="color:var(--text-secondary);">{{ $n($item->prev_energy) }}</strong>
                            </div>
                            @if($e)
                                <div style="font-size:0.82rem; font-weight:700; color:#facc15; font-family:monospace;">
                                    {{ $n($e->stand_akhir) }}
                                </div>
                                <div style="font-size:0.68rem; color:var(--success); font-weight:600;">
                                    +{{ $n($e->pemakaian) }} kWh
                                </div>
                            @else
                                <div style="font-size:0.72rem; color:#f87171; font-weight:600; margin-top:3px;">
                                    Belum
                                </div>
                            @endif
                        </div>

                        <!-- Listrik EV Charger jika ada -->
                        @if($t->has_ev_charger)
                            <div style="border-left:1px solid var(--border-color); padding-left:8px;">
                                <div style="display:flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:700; color:#38bdf8; margin-bottom:4px;">
                                    <span>🚗 EV CHARGER</span>
                                </div>
                                <div style="font-size:0.68rem; color:var(--text-muted); margin-bottom:2px;">
                                    Awal: <strong style="color:var(--text-secondary);">{{ $n($item->prev_energy_ev) }}</strong>
                                </div>
                                @if($evReading)
                                    <div style="font-size:0.82rem; font-weight:700; color:#38bdf8; font-family:monospace;">
                                        {{ $n($evReading->stand_akhir) }}
                                    </div>
                                    <div style="font-size:0.68rem; color:var(--success); font-weight:600;">
                                        +{{ $n($evReading->pemakaian) }} kWh
                                    </div>
                                @else
                                    <div style="font-size:0.72rem; color:#f87171; font-weight:600; margin-top:3px;">
                                        Belum
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Air -->
                        <div style="border-left:1px solid var(--border-color); padding-left:8px;">
                            <div style="display:flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:700; color:#38bdf8; margin-bottom:4px;">
                                <span>💧 AIR (m³)</span>
                            </div>
                            <div style="font-size:0.68rem; color:var(--text-muted); margin-bottom:2px;">
                                Awal: <strong style="color:var(--text-secondary);">{{ $n($item->prev_water) }}</strong>
                            </div>
                            @if($w)
                                <div style="font-size:0.82rem; font-weight:700; color:#38bdf8; font-family:monospace;">
                                    {{ $n($w->stand_akhir) }}
                                </div>
                                <div style="font-size:0.68rem; color:var(--success); font-weight:600;">
                                    +{{ $n($w->pemakaian) }} m³
                                </div>
                            @else
                                <div style="font-size:0.72rem; color:#f87171; font-weight:600; margin-top:3px;">
                                    Belum
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Bawah -->
                <div style="margin-top:14px; display:flex; gap:8px;">
                    <button type="button" class="btn btn-sm btn-primary" onclick="openQuickInputModal({{ $t->id }})" style="flex:1; justify-content:center; padding:8px 12px; font-weight:600;">
                        {{ $isDone ? '✏️ Ubah Data Stand' : '⚡ Input Stand Meter' }}
                    </button>
                    @if($e?->foto_url || $evReading?->foto_url || $w?->foto_url)
                        <button type="button" class="btn btn-sm" onclick="showTenantPhotos({{ $t->id }})" style="padding:8px 10px;" title="Lihat Foto Bukti">
                            📷
                        </button>
                    @endif
                </div>
                </div>
            </div>
        @empty
            <div style="grid-column:1 / -1; padding:40px; text-align:center; background:var(--bg-card); border-radius:var(--radius-md); border:1px dashed var(--border-color); color:var(--text-muted);">
                <p style="font-size:1.1rem; margin-bottom:8px;">Tidak ada tenant yang sesuai dengan filter.</p>
                <a href="{{ route('billing.quick-input') }}" class="btn btn-sm">Reset Filter</a>
            </div>
        @endforelse
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL INPUT CEPAT KHUSUS TEKNISI (OPTIMAL UNTUK HP & DESKTOP) -->
<!-- ============================================================== -->
<div class="quick-modal-backdrop" id="quickModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(3px); z-index:9999; overflow-y:auto; padding:16px;">
    <div class="quick-modal-dialog" style="max-width:580px; margin:20px auto; background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg, 16px); box-shadow:0 20px 40px rgba(0,0,0,0.5); overflow:hidden;">
        
        <!-- Header Modal -->
        <div style="padding:16px 20px; background:var(--bg-main); border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <span id="modalTenantUnit" style="font-size:0.75rem; font-weight:700; color:var(--accent); text-transform:uppercase;">📍 Unit</span>
                <h3 id="modalTenantNama" style="font-size:1.15rem; font-weight:700; margin:2px 0 0; color:var(--text-primary);">Nama Tenant</h3>
            </div>
            <button type="button" onclick="closeQuickInputModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; line-height:1;" aria-label="Tutup">&times;</button>
        </div>

        <!-- Form Input Terpadu -->
        <form id="quickInputForm" method="POST" action="{{ route('billing.quick-input.store') }}" enctype="multipart/form-data" onsubmit="return handleQuickFormSubmit(event)">
            @csrf
            <input type="hidden" name="tenant_id" id="formTenantId">
            <input type="hidden" name="periode_tahun" value="{{ $year }}">
            <input type="hidden" name="periode_bulan" value="{{ $month }}">
            <input type="hidden" name="tanggal_catat" value="{{ now()->toDateString() }}">

            <div style="padding:20px; max-height:calc(85vh - 140px); overflow-y:auto;">
                
                <!-- Reminder Bulan -->
                <div style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); border-radius:var(--radius-sm); padding:10px 14px; margin-bottom:16px; font-size:0.8rem; color:#38bdf8; display:flex; align-items:center; justify-content:space-between;">
                    <span>📅 Periode Input: <strong>{{ $namaBulan }} {{ $year }}</strong></span>
                    <span>Tanggal: <strong>{{ now()->translatedFormat('d F Y') }}</strong></span>
                </div>

                <!-- 1. SEKSI LISTRIK (kWh) -->
                <div style="background:var(--bg-card-subtle); border:1px solid rgba(234,179,8,0.3); border-radius:var(--radius-md); padding:16px; margin-bottom:18px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:1.2rem;">⚡</span>
                            <div>
                                <h4 style="font-size:0.95rem; font-weight:700; margin:0; color:#facc15;">Meter Listrik (kWh)</h4>
                                <span id="modalMeterListrikId" style="font-size:0.75rem; color:var(--text-muted);">No. Meter: —</span>
                            </div>
                        </div>
                        <span id="modalFaktorListrik" style="font-size:0.75rem; color:var(--text-muted); background:var(--bg-card); padding:2px 8px; border-radius:4px; border:1px solid var(--border-color);">Faktor Kali: 1x</span>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1.3fr; gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Stand Lalu (kWh):</label>
                            <input type="text" id="modalStandAwalListrikText" readonly class="form-control" style="background:var(--bg-main); font-weight:700; color:var(--text-secondary); cursor:not-allowed;" value="0.00">
                            <input type="hidden" name="stand_awal_listrik" id="modalStandAwalListrik" value="0">
                        </div>
                        <div>
                            <label style="font-size:0.78rem; font-weight:700; color:#facc15; display:block; margin-bottom:4px;">Stand Sekarang (kWh): *</label>
                            <input type="number" step="0.01" inputmode="decimal" name="stand_akhir_listrik" id="modalStandAkhirListrik" class="form-control" placeholder="Contoh: 12540.50" style="font-size:1.1rem; font-weight:700; font-family:monospace; border-color:rgba(234,179,8,0.5);" oninput="calcUsage('listrik')">
                        </div>
                    </div>

                    <!-- Live Usage & Error Warning Listrik -->
                    <div id="usageBoxListrik" style="display:none; padding:8px 12px; border-radius:var(--radius-sm); font-size:0.82rem; margin-bottom:12px; align-items:center; justify-content:space-between;"></div>

                    <!-- Kamera & Foto Listrik -->
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">
                            Foto Bukti kWh Meter:
                            @if(!auth()->user()->isSuperAdmin())
                                <span style="color:var(--danger); font-size:0.72rem;">* (Wajib bagi Teknisi)</span>
                            @endif
                        </label>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <label class="btn btn-sm" style="background:rgba(234,179,8,0.15); color:#facc15; border-color:rgba(234,179,8,0.3); cursor:pointer; margin:0; display:inline-flex; align-items:center; gap:6px;">
                                📷 Ambil / Upload Foto
                                <input type="file" name="foto_meter_listrik" id="fotoListrikInput" accept="image/*" capture="environment" style="display:none;" onchange="previewImage(this, 'previewListrikImg', 'previewListrikWrap')">
                            </label>
                            <span id="fotoListrikLabel" style="font-size:0.75rem; color:var(--text-muted);">Belum ada foto</span>
                        </div>
                        <div id="previewListrikWrap" style="display:none; margin-top:10px;">
                            <img id="previewListrikImg" src="" alt="Preview Meter Listrik" style="max-height:120px; border-radius:8px; border:1px solid var(--border-color); object-fit:cover;">
                        </div>
                    </div>
                </div>

                <!-- 1b. SEKSI LISTRIK EV CHARGER (Hanya muncul jika tenant memiliki EV Charger) -->
                <div id="modalSeksiEvWrap" style="display:none; background:var(--bg-card-subtle); border:1px solid rgba(56,189,248,0.35); border-radius:var(--radius-md); padding:16px; margin-bottom:18px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:1.2rem;">🚗⚡</span>
                            <div>
                                <h4 style="font-size:0.95rem; font-weight:700; margin:0; color:#38bdf8;">Meter Listrik EV Charger (kWh)</h4>
                                <span id="modalMeterEvId" style="font-size:0.75rem; color:var(--text-muted);">No. Meter: —</span>
                            </div>
                        </div>
                        <span id="modalDayaEv" style="font-size:0.75rem; color:#38bdf8; background:rgba(56,189,248,0.1); padding:2px 8px; border-radius:4px; border:1px solid rgba(56,189,248,0.25);">Daya: —</span>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1.3fr; gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Stand Lalu (kWh):</label>
                            <input type="text" id="modalStandAwalEvText" readonly class="form-control" style="background:var(--bg-main); font-weight:700; color:var(--text-secondary); cursor:not-allowed;" value="0.00">
                            <input type="hidden" name="stand_awal_ev" id="modalStandAwalEv" value="0">
                        </div>
                        <div>
                            <label style="font-size:0.78rem; font-weight:700; color:#38bdf8; display:block; margin-bottom:4px;">Stand Sekarang (kWh):</label>
                            <input type="number" step="0.01" inputmode="decimal" name="stand_akhir_ev" id="modalStandAkhirEv" class="form-control" placeholder="Contoh: 2851.10" style="font-size:1.1rem; font-weight:700; font-family:monospace; border-color:rgba(56,189,248,0.5);" oninput="calcUsage('ev')">
                        </div>
                    </div>

                    <!-- Live Usage & Warning EV -->
                    <div id="usageBoxEv" style="display:none; padding:8px 12px; border-radius:var(--radius-sm); font-size:0.82rem; margin-bottom:12px; align-items:center; justify-content:space-between;"></div>

                    <!-- Kamera & Foto EV -->
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">
                            Foto Bukti Meter EV Charger:
                            @if(!auth()->user()->isSuperAdmin())
                                <span style="color:var(--danger); font-size:0.72rem;">* (Wajib bagi Teknisi)</span>
                            @endif
                        </label>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <label class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:#38bdf8; border-color:rgba(56,189,248,0.3); cursor:pointer; margin:0; display:inline-flex; align-items:center; gap:6px;">
                                📷 Ambil / Upload Foto EV
                                <input type="file" name="foto_meter_ev" id="fotoEvInput" accept="image/*" capture="environment" style="display:none;" onchange="previewImage(this, 'previewEvImg', 'previewEvWrap')">
                            </label>
                            <span id="fotoEvLabel" style="font-size:0.75rem; color:var(--text-muted);">Belum ada foto</span>
                        </div>
                        <div id="previewEvWrap" style="display:none; margin-top:10px;">
                            <img id="previewEvImg" src="" alt="Preview Meter EV" style="max-height:120px; border-radius:8px; border:1px solid var(--border-color); object-fit:cover;">
                        </div>
                    </div>
                </div>

                <!-- 2. SEKSI AIR (m³) -->
                <div style="background:var(--bg-card-subtle); border:1px solid rgba(56,189,248,0.3); border-radius:var(--radius-md); padding:16px; margin-bottom:18px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:1.2rem;">💧</span>
                            <div>
                                <h4 style="font-size:0.95rem; font-weight:700; margin:0; color:#38bdf8;">Water Meter (m³)</h4>
                                <span id="modalMeterAirId" style="font-size:0.75rem; color:var(--text-muted);">No. Meter: —</span>
                            </div>
                        </div>
                        <span id="modalFaktorAir" style="font-size:0.75rem; color:var(--text-muted); background:var(--bg-card); padding:2px 8px; border-radius:4px; border:1px solid var(--border-color);">Faktor Kali: 1x</span>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1.3fr; gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Stand Lalu (m³):</label>
                            <input type="text" id="modalStandAwalAirText" readonly class="form-control" style="background:var(--bg-main); font-weight:700; color:var(--text-secondary); cursor:not-allowed;" value="0.00">
                            <input type="hidden" name="stand_awal_air" id="modalStandAwalAir" value="0">
                        </div>
                        <div>
                            <label style="font-size:0.78rem; font-weight:700; color:#38bdf8; display:block; margin-bottom:4px;">Stand Sekarang (m³): *</label>
                            <input type="number" step="0.01" inputmode="decimal" name="stand_akhir_air" id="modalStandAkhirAir" class="form-control" placeholder="Contoh: 345.20" style="font-size:1.1rem; font-weight:700; font-family:monospace; border-color:rgba(56,189,248,0.5);" oninput="calcUsage('air')">
                        </div>
                    </div>

                    <!-- Live Usage & Error Warning Air -->
                    <div id="usageBoxAir" style="display:none; padding:8px 12px; border-radius:var(--radius-sm); font-size:0.82rem; margin-bottom:12px; align-items:center; justify-content:space-between;"></div>

                    <!-- Kamera & Foto Air -->
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">
                            Foto Bukti Water Meter:
                            @if(!auth()->user()->isSuperAdmin())
                                <span style="color:var(--danger); font-size:0.72rem;">* (Wajib bagi Teknisi)</span>
                            @endif
                        </label>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <label class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:#38bdf8; border-color:rgba(56,189,248,0.3); cursor:pointer; margin:0; display:inline-flex; align-items:center; gap:6px;">
                                📷 Ambil / Upload Foto
                                <input type="file" name="foto_meter_air" id="fotoAirInput" accept="image/*" capture="environment" style="display:none;" onchange="previewImage(this, 'previewAirImg', 'previewAirWrap')">
                            </label>
                            <span id="fotoAirLabel" style="font-size:0.75rem; color:var(--text-muted);">Belum ada foto</span>
                        </div>
                        <div id="previewAirWrap" style="display:none; margin-top:10px;">
                            <img id="previewAirImg" src="" alt="Preview Water Meter" style="max-height:120px; border-radius:8px; border:1px solid var(--border-color); object-fit:cover;">
                        </div>
                    </div>
                </div>

                <!-- Catatan Tambahan -->
                <div style="margin-bottom:8px;">
                    <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Catatan Lapangan (Opsional):</label>
                    <input type="text" name="catatan" id="modalCatatan" class="form-control" placeholder="Contoh: Kondisi meteran normal, segel aman">
                </div>
            </div>

            <!-- Footer Tombol Simpan -->
            <div style="padding:14px 20px; background:var(--bg-main); border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                <button type="button" class="btn btn-sm" onclick="closeQuickInputModal()">Batal</button>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="submit_action" value="save" class="btn btn-sm btn-primary" id="btnSaveOnly">
                        💾 Simpan Data
                    </button>
                    <button type="submit" name="submit_action" value="save_and_next" class="btn btn-sm" id="btnSaveAndNext" style="background:var(--success); color:#fff; border-color:var(--success); font-weight:700;">
                        Simpan &amp; Lanjut Tenant Berikutnya &rarr;
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const isSuperAdmin = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};
let currentEditingTenantId = null;
const tenantOrderList = [
    @foreach($checklist as $item)
        {{ $item->tenant->id }},
    @endforeach
];

function openQuickInputModal(tenantId) {
    const card = document.getElementById('tenant-card-' + tenantId);
    if (!card) return;

    currentEditingTenantId = tenantId;
    document.getElementById('formTenantId').value = tenantId;

    // Set Info Tenant
    document.getElementById('modalTenantNama').textContent = card.dataset.tenantNama || '—';
    document.getElementById('modalTenantUnit').textContent = '📍 ' + (card.dataset.tenantUnit || '—');
    document.getElementById('modalMeterListrikId').textContent = 'No. Meter: ' + (card.dataset.tenantMeterListrik || '—');
    document.getElementById('modalMeterAirId').textContent = 'No. Meter: ' + (card.dataset.tenantMeterAir || '—');
    document.getElementById('modalFaktorListrik').textContent = 'Faktor Kali: ' + (card.dataset.faktorListrik || '1') + 'x';
    document.getElementById('modalFaktorAir').textContent = 'Faktor Kali: ' + (card.dataset.faktorAir || '1') + 'x';

    // Stand Awal Listrik & Air
    const prevE = parseFloat(card.dataset.prevEnergy) || 0;
    const prevW = parseFloat(card.dataset.prevWater) || 0;
    document.getElementById('modalStandAwalListrik').value = prevE;
    document.getElementById('modalStandAwalListrikText').value = prevE.toLocaleString('id-ID', { minimumFractionDigits: 2 });
    document.getElementById('modalStandAwalAir').value = prevW;
    document.getElementById('modalStandAwalAirText').value = prevW.toLocaleString('id-ID', { minimumFractionDigits: 2 });

    // Stand Akhir Listrik & Air (Jika sudah dicatat sebelumnya)
    document.getElementById('modalStandAkhirListrik').value = card.dataset.currEnergy || '';
    document.getElementById('modalStandAkhirAir').value = card.dataset.currWater || '';
    document.getElementById('modalCatatan').value = card.dataset.catatan || '';

    // Penanganan EV Charger (Jika Unit ini memiliki fasilitas EV)
    const hasEv = card.dataset.hasEv === '1';
    const seksiEv = document.getElementById('modalSeksiEvWrap');
    if (hasEv) {
        seksiEv.style.display = 'block';
        document.getElementById('modalMeterEvId').textContent = 'No. Meter: ' + (card.dataset.tenantMeterEv || 'KWH-EV');
        document.getElementById('modalDayaEv').textContent = 'Daya: ' + (card.dataset.dayaEv ? card.dataset.dayaEv + ' kVA' : '—');

        const prevEv = parseFloat(card.dataset.prevEnergyEv) || 0;
        document.getElementById('modalStandAwalEv').value = prevEv;
        document.getElementById('modalStandAwalEvText').value = prevEv.toLocaleString('id-ID', { minimumFractionDigits: 2 });
        document.getElementById('modalStandAkhirEv').value = card.dataset.currEnergyEv || '';

        document.getElementById('fotoEvInput').value = '';
        if (card.dataset.fotoEnergyEv) {
            document.getElementById('previewEvImg').src = card.dataset.fotoEnergyEv;
            document.getElementById('previewEvWrap').style.display = 'block';
            document.getElementById('fotoEvLabel').textContent = '✓ Foto tersimpan sebelumnya';
        } else {
            document.getElementById('previewEvWrap').style.display = 'none';
            document.getElementById('fotoEvLabel').textContent = 'Belum ada foto';
        }
        calcUsage('ev');
    } else {
        seksiEv.style.display = 'none';
    }

    // Reset Foto Input & Preview
    document.getElementById('fotoListrikInput').value = '';
    document.getElementById('fotoAirInput').value = '';

    if (card.dataset.fotoEnergy) {
        document.getElementById('previewListrikImg').src = card.dataset.fotoEnergy;
        document.getElementById('previewListrikWrap').style.display = 'block';
        document.getElementById('fotoListrikLabel').textContent = '✓ Foto tersimpan sebelumnya';
    } else {
        document.getElementById('previewListrikWrap').style.display = 'none';
        document.getElementById('fotoListrikLabel').textContent = 'Belum ada foto';
    }

    if (card.dataset.fotoWater) {
        document.getElementById('previewAirImg').src = card.dataset.fotoWater;
        document.getElementById('previewAirWrap').style.display = 'block';
        document.getElementById('fotoAirLabel').textContent = '✓ Foto tersimpan sebelumnya';
    } else {
        document.getElementById('previewAirWrap').style.display = 'none';
        document.getElementById('fotoAirLabel').textContent = 'Belum ada foto';
    }

    calcUsage('listrik');
    calcUsage('air');

    document.getElementById('quickModal').style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Autofocus ke input listrik atau air
    setTimeout(() => {
        const inp = document.getElementById('modalStandAkhirListrik');
        if (inp) inp.focus();
    }, 150);
}

function closeQuickInputModal() {
    document.getElementById('quickModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Live calculation & anti-error check
function checkAllModalButtons() {
    const btnSave = document.getElementById('btnSaveOnly');
    const btnNext = document.getElementById('btnSaveAndNext');

    if (isSuperAdmin) {
        btnSave.disabled = false;
        btnNext.disabled = false;
        btnSave.style.opacity = '1';
        btnNext.style.opacity = '1';
        return;
    }

    const awalE = parseFloat(document.getElementById('modalStandAwalListrik').value) || 0;
    const akhirEVal = document.getElementById('modalStandAkhirListrik').value;
    const akhirE = akhirEVal !== '' ? parseFloat(akhirEVal) : null;

    const awalEv = parseFloat(document.getElementById('modalStandAwalEv').value) || 0;
    const akhirEvVal = document.getElementById('modalStandAkhirEv').value;
    const akhirEv = akhirEvVal !== '' ? parseFloat(akhirEvVal) : null;

    const awalW = parseFloat(document.getElementById('modalStandAwalAir').value) || 0;
    const akhirWVal = document.getElementById('modalStandAkhirAir').value;
    const akhirW = akhirWVal !== '' ? parseFloat(akhirWVal) : null;

    const hasMundurE = (akhirE !== null && akhirE < awalE);
    const hasMundurEv = (akhirEv !== null && akhirEv < awalEv);
    const hasMundurW = (akhirW !== null && akhirW < awalW);

    if (hasMundurE || hasMundurEv || hasMundurW) {
        btnSave.disabled = true;
        btnNext.disabled = true;
        btnSave.style.opacity = '0.4';
        btnNext.style.opacity = '0.4';
    } else {
        btnSave.disabled = false;
        btnNext.disabled = false;
        btnSave.style.opacity = '1';
        btnNext.style.opacity = '1';
    }
}

function calcUsage(type) {
    if (type === 'listrik') {
        const card = document.getElementById('tenant-card-' + currentEditingTenantId);
        const factor = card ? parseFloat(card.dataset.faktorListrik) || 1 : 1;
        const awal = parseFloat(document.getElementById('modalStandAwalListrik').value) || 0;
        const akhirVal = document.getElementById('modalStandAkhirListrik').value;
        const box = document.getElementById('usageBoxListrik');

        if (akhirVal === '') {
            box.style.display = 'none';
            checkAllModalButtons();
            return;
        }

        const akhir = parseFloat(akhirVal) || 0;
        const usage = (akhir - awal) * factor;

        box.style.display = 'flex';
        if (akhir < awal) {
            box.style.background = 'rgba(239,68,68,0.15)';
            box.style.border = '1px solid rgba(239,68,68,0.3)';
            box.style.color = '#f87171';
            box.innerHTML = isSuperAdmin
                ? `<span>⚠️ <strong>Mode Stand Khusus:</strong> Stand akhir lebih kecil dari stand awal (Super Admin).</span>`
                : `<span>⛔ <strong>Akses Ditolak:</strong> Stand akhir (${akhir}) lebih kecil dari awal (${awal}). Teknisi tidak diizinkan input stand mundur!</span>`;
        } else {
            box.style.background = 'rgba(34,197,94,0.12)';
            box.style.border = '1px solid rgba(34,197,94,0.3)';
            box.style.color = '#22c55e';
            box.innerHTML = `<span>Estimasi Pemakaian:</span><strong>+${usage.toLocaleString('id-ID', { minimumFractionDigits: 2 })} kWh</strong>`;
        }
    } else if (type === 'ev') {
        const awal = parseFloat(document.getElementById('modalStandAwalEv').value) || 0;
        const akhirVal = document.getElementById('modalStandAkhirEv').value;
        const box = document.getElementById('usageBoxEv');

        if (akhirVal === '') {
            box.style.display = 'none';
            checkAllModalButtons();
            return;
        }

        const akhir = parseFloat(akhirVal) || 0;
        const usage = akhir - awal;

        box.style.display = 'flex';
        if (akhir < awal) {
            box.style.background = 'rgba(239,68,68,0.15)';
            box.style.border = '1px solid rgba(239,68,68,0.3)';
            box.style.color = '#f87171';
            box.innerHTML = isSuperAdmin
                ? `<span>⚠️ <strong>Mode Stand Khusus:</strong> Stand EV lebih kecil dari stand awal (Super Admin).</span>`
                : `<span>⛔ <strong>Akses Ditolak:</strong> Stand EV (${akhir}) lebih kecil dari awal (${awal}). Teknisi tidak diizinkan input stand mundur!</span>`;
        } else {
            box.style.background = 'rgba(56,189,248,0.12)';
            box.style.border = '1px solid rgba(56,189,248,0.3)';
            box.style.color = '#38bdf8';
            box.innerHTML = `<span>Estimasi Pemakaian EV:</span><strong>+${usage.toLocaleString('id-ID', { minimumFractionDigits: 2 })} kWh</strong>`;
        }
    } else if (type === 'air') {
        const card = document.getElementById('tenant-card-' + currentEditingTenantId);
        const factor = card ? parseFloat(card.dataset.faktorAir) || 1 : 1;
        const awal = parseFloat(document.getElementById('modalStandAwalAir').value) || 0;
        const akhirVal = document.getElementById('modalStandAkhirAir').value;
        const box = document.getElementById('usageBoxAir');

        if (akhirVal === '') {
            box.style.display = 'none';
            checkAllModalButtons();
            return;
        }

        const akhir = parseFloat(akhirVal) || 0;
        const usage = (akhir - awal) * factor;

        box.style.display = 'flex';
        if (akhir < awal) {
            box.style.background = 'rgba(239,68,68,0.15)';
            box.style.border = '1px solid rgba(239,68,68,0.3)';
            box.style.color = '#f87171';
            box.innerHTML = isSuperAdmin
                ? `<span>⚠️ <strong>Mode Stand Khusus:</strong> Stand akhir air lebih kecil dari awal (Super Admin).</span>`
                : `<span>⛔ <strong>Akses Ditolak:</strong> Stand akhir air (${akhir}) lebih kecil dari awal (${awal}). Teknisi tidak diizinkan input stand mundur!</span>`;
        } else {
            box.style.background = 'rgba(56,189,248,0.12)';
            box.style.border = '1px solid rgba(56,189,248,0.3)';
            box.style.color = '#38bdf8';
            box.innerHTML = `<span>Estimasi Pemakaian:</span><strong>+${usage.toLocaleString('id-ID', { minimumFractionDigits: 3 })} m³</strong>`;
        }
    }

    checkAllModalButtons();
}

// Preview foto seketika saat dijepret kamera
function previewImage(input, imgId, wrapId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(imgId).src = e.target.result;
            document.getElementById(wrapId).style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);

        const labelId = input.id === 'fotoListrikInput' ? 'fotoListrikLabel' : 'fotoAirLabel';
        document.getElementById(labelId).textContent = '✓ Foto siap: ' + input.files[0].name.substring(0, 18) + '...';
    }
}

// Handle Form Submission dengan AJAX untuk transisi super mulus
let lastClickedAction = 'save';
document.getElementById('btnSaveOnly').addEventListener('click', () => { lastClickedAction = 'save'; });
document.getElementById('btnSaveAndNext').addEventListener('click', () => { lastClickedAction = 'save_and_next'; });

async function handleQuickFormSubmit(event) {
    event.preventDefault();

    if (!isSuperAdmin) {
        const awalE = parseFloat(document.getElementById('modalStandAwalListrik').value) || 0;
        const akhirEVal = document.getElementById('modalStandAkhirListrik').value;
        const akhirE = akhirEVal !== '' ? parseFloat(akhirEVal) : null;

        const awalEv = parseFloat(document.getElementById('modalStandAwalEv').value) || 0;
        const akhirEvVal = document.getElementById('modalStandAkhirEv').value;
        const akhirEv = akhirEvVal !== '' ? parseFloat(akhirEvVal) : null;

        const awalW = parseFloat(document.getElementById('modalStandAwalAir').value) || 0;
        const akhirWVal = document.getElementById('modalStandAkhirAir').value;
        const akhirW = akhirWVal !== '' ? parseFloat(akhirWVal) : null;

        if ((akhirE !== null && akhirE < awalE) || (akhirEv !== null && akhirEv < awalEv) || (akhirW !== null && akhirW < awalW)) {
            alert('Penginputan Ditolak: Terdapat nilai Stand Akhir yang lebih kecil dari Stand Awal (Stand Mundur / Error Meter). Peran Teknisi tidak diizinkan menginput kondisi ini. Silakan laporkan kendala ke Super Admin.');
            return false;
        }

        // Pengecekan Wajib Foto Fisik Meteran bagi Teknisi
        const card = document.getElementById('tenant-card-' + currentEditingTenantId);
        const hasCardFotoE = card && card.dataset.fotoEnergy;
        const fotoEFiles = document.getElementById('fotoListrikInput').files;
        if (akhirE !== null && !hasCardFotoE && (!fotoEFiles || fotoEFiles.length === 0)) {
            alert('Wajib melampirkan foto fisik kWh meter sebagai bukti pencatatan meteran!');
            document.getElementById('fotoListrikInput').click();
            return false;
        }

        const hasEv = card && card.dataset.hasEv === '1';
        const hasCardFotoEv = card && card.dataset.fotoEnergyEv;
        const fotoEvFiles = document.getElementById('fotoEvInput').files;
        if (hasEv && akhirEv !== null && !hasCardFotoEv && (!fotoEvFiles || fotoEvFiles.length === 0)) {
            alert('Wajib melampirkan foto fisik meteran EV Charger sebagai bukti pencatatan meteran!');
            document.getElementById('fotoEvInput').click();
            return false;
        }

        const hasCardFotoW = card && card.dataset.fotoWater;
        const fotoWFiles = document.getElementById('fotoAirInput').files;
        if (akhirW !== null && !hasCardFotoW && (!fotoWFiles || fotoWFiles.length === 0)) {
            alert('Wajib melampirkan foto fisik water meter sebagai bukti pencatatan meteran!');
            document.getElementById('fotoAirInput').click();
            return false;
        }
    }

    const form = document.getElementById('quickInputForm');
    const formData = new FormData(form);

    const btnSave = document.getElementById('btnSaveOnly');
    const btnNext = document.getElementById('btnSaveAndNext');
    btnSave.disabled = true;
    btnNext.disabled = true;
    btnSave.textContent = 'Menyimpan...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (response.ok && result.success) {
            // Update Card di checklist
            const card = document.getElementById('tenant-card-' + currentEditingTenantId);
            if (card) {
                card.classList.remove('is-pending', 'is-partial');
                card.classList.add('is-done');
                card.style.borderColor = 'rgba(34,197,94,0.3)';
            }

            if (lastClickedAction === 'save_and_next') {
                // Cari index tenant berikutnya
                const currentIdx = tenantOrderList.indexOf(currentEditingTenantId);
                let nextTenantId = null;
                if (currentIdx !== -1 && currentIdx + 1 < tenantOrderList.length) {
                    nextTenantId = tenantOrderList[currentIdx + 1];
                } else {
                    nextTenantId = tenantOrderList[0]; // Rollback to first
                }

                if (nextTenantId && nextTenantId !== currentEditingTenantId) {
                    openQuickInputModal(nextTenantId);
                    return;
                }
            }

            // Jika pilih simpan saja atau tidak ada tenant berikutnya, reload halus
            window.location.reload();
        } else {
            alert('Gagal menyimpan: ' + (result.message || 'Terjadi kesalahan sistem'));
        }
    } catch (err) {
        // Fallback submit standar jika fetch bermasalah
        form.submit();
    } finally {
        btnSave.disabled = false;
        btnNext.disabled = false;
        btnSave.textContent = '💾 Simpan Data';
    }
}

function showTenantPhotos(tenantId) {
    const card = document.getElementById('tenant-card-' + tenantId);
    if (!card) return;
    const ePhoto = card.dataset.fotoEnergy;
    const evPhoto = card.dataset.fotoEnergyEv;
    const wPhoto = card.dataset.fotoWater;

    const lb = document.getElementById('lightbox');
    const lbImg = document.getElementById('lightbox-img');
    const lbCap = document.getElementById('lightbox-caption');

    if (ePhoto) {
        lbImg.src = ePhoto;
        lbCap.textContent = 'Foto Meter Listrik Reguler: ' + card.dataset.tenantNama;
        lb.hidden = false;
    } else if (evPhoto) {
        lbImg.src = evPhoto;
        lbCap.textContent = 'Foto Meter Listrik EV Charger: ' + card.dataset.tenantNama;
        lb.hidden = false;
    } else if (wPhoto) {
        lbImg.src = wPhoto;
        lbCap.textContent = 'Foto Water Meter: ' + card.dataset.tenantNama;
        lb.hidden = false;
    }
}
</script>
@endpush
@endsection
