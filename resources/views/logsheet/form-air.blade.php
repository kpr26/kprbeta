@extends('layouts.panel')

@php
    $isAdmin = auth()->user()->role === 'admin';
    $canManage = in_array(auth()->user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $hasRecordedToday = $today && $today->foto_air;
@endphp

@section('title', 'Input Logsheet Air')
@section('page-title', 'Input Logsheet Air')
@section('page-subtitle', 'Pencatatan Stand Water Meter Gedung')

@section('panel')
<form method="POST" action="{{ route('logsheet.store.air') }}" enctype="multipart/form-data" class="panel" id="logsheet-air-form">
    @csrf

    <div class="panel-head">
        <div>
            <h2><span style="color:#38bdf8;">💧</span> Pencatatan Water Meter Air</h2>
            <p>Masukkan angka stand air (m³) yang tertera pada water meter serta lampirkan foto fisik meteran.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <a href="{{ route('logsheet.hub') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                ← Pilihan Logsheet
            </a>
            <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm" style="background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3);">
                ⚡ Pindah ke Input Listrik
            </a>
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('logsheet.manual.air') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.35);" title="Input tanggal yang sudah terlewat">
                    💧 Tanggal Terlewat
                </a>
            @endif
            @if (auth()->user()->role === 'teknisi')
                <span class="badge badge-role-teknisi">Teknisi</span>
            @endif
        </div>
    </div>

    @if ($hasRecordedToday)
        <div class="status-banner ok" style="margin-bottom:24px; display:block;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <span class="dot"></span>
                <strong style="font-size:1.05rem;">Meter Air Hari Ini Sudah Dicatat</strong>
            </div>
            <p style="margin:0 0 12px 20px; color:var(--text-secondary); font-size:0.88rem;">
                Tercatat pada stand Air: <b>{{ $n($today->stand_air) }} m³</b> oleh <b>{{ $today->user->name }}</b> pukul {{ $today->updated_at->format('H:i') }}.
            </p>
            <div style="margin-left:20px; display:flex; gap:10px; flex-wrap:wrap;">
                @if(!$today->foto_kwh)
                    <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm btn-primary">Lanjut Input kWh Meter Listrik →</a>
                @endif
                <a href="{{ route('logsheet.show', $today) }}" class="btn btn-sm">Lihat Detail Data</a>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom:20px">
            Periksa kembali isian Anda — terdapat {{ $errors->count() }} kesalahan.
        </div>
    @endif

    <div class="top-fields">
        <div class="field">
            <label>Tanggal Pencatatan</label>
            <div class="input" style="font-weight:600; color:var(--text-primary); background:rgba(255,255,255,0.03);">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
        <div class="field">
            <label>Pencatatan Air Sebelumnya</label>
            <div class="input" style="opacity:.85">
                @if ($last)
                    {{ $last->tanggal->translatedFormat('d F Y') }} (Stand: {{ $n($last->stand_air) }} m³)
                @else
                    Belum ada — ini pencatatan pertama
                @endif
            </div>
        </div>
    </div>

    <div class="meter-section water" style="margin-top:20px;">
        <h3><span class="ico"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg></span>Angka Stand Water Meter</h3>

        <div class="field">
            <label for="stand_air">Stand Water Meter <span class="req">*</span></label>
            <div class="input-unit">
                <input type="number" step="0.01" min="0" inputmode="decimal" id="stand_air" name="stand_air" class="input"
                       value="{{ old('stand_air', $reading->stand_air) }}" data-prev="{{ $last?->stand_air }}" data-unit="m³" placeholder="0,00" required>
                <span>m³</span>
            </div>
            @if ($last)<p class="hint">Stand sebelumnya: <b>{{ $n($last->stand_air) }} m³</b></p>@endif
            <p class="calc" data-calc-for="stand_air"></p>
            @error('stand_air')<p class="err">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label>Foto Water Meter <span class="req">{{ $reading->foto_air ? '' : '*' }}</span></label>
            <label class="upload {{ $reading->foto_air ? 'has-img' : '' }}">
                <input type="file" name="foto_air" accept="image/*" capture="environment" {{ $reading->foto_air ? '' : 'required' }}>
                @if ($reading->foto_air)
                    <img class="preview" src="{{ asset('storage/'.$reading->foto_air) }}" alt="Foto air">
                @endif
                <span class="upload-text">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg><br>
                    Ambil foto / pilih gambar<br><small>Pastikan angka meter terbaca jelas · maks 5 MB</small>
                </span>
                <span class="change">Ganti foto</span>
            </label>
            @error('foto_air')<p class="err">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="catatan">Catatan Meter Air (opsional)</label>
            <textarea id="catatan" name="catatan" rows="2" class="input" placeholder="Contoh: tekanan normal, tidak ada kebocoran, dsb.">{{ old('catatan') }}</textarea>
        </div>
    </div>

    <div class="form-foot">
        <a href="{{ route('logsheet.hub') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn-primary" id="btn-simpan-air">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            Simpan Stand Air
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Hitung pemakaian langsung
    const fmt = v => v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.querySelectorAll('input[data-prev]').forEach(inp => {
        const out = document.querySelector(`[data-calc-for="${inp.id}"]`);
        const update = () => {
            const prev = parseFloat(inp.dataset.prev), cur = parseFloat(inp.value);
            if (isNaN(prev) || isNaN(cur)) { out.textContent = ''; return; }
            const diff = cur - prev;
            out.classList.toggle('bad', diff < 0);
            out.innerHTML = diff < 0
                ? `⚠ Stand lebih kecil dari sebelumnya (<b>${fmt(diff)}</b>)`
                : `Pemakaian: <b>${fmt(diff)} ${inp.dataset.unit}</b>`;
        };
        inp.addEventListener('input', update); update();
    });

    // Preview foto
    document.querySelectorAll('.upload input[type=file]').forEach(inp => {
        const box = inp.closest('.upload');
        inp.addEventListener('change', () => {
            const f = inp.files[0]; if (!f) return;
            let img = box.querySelector('img.preview');
            if (!img) { img = document.createElement('img'); img.className = 'preview'; box.appendChild(img); }
            img.src = URL.createObjectURL(f);
            box.classList.add('has-img');
        });
    });
</script>
@endpush
