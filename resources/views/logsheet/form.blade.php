@extends('layouts.panel')

@php
    $editing = $reading->exists;
    $isAdmin = auth()->user()->role === 'admin';
    $canManage = in_array(auth()->user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
    $n = fn ($v) => number_format($v, 2, ',', '.');
@endphp

@section('title', $editing ? 'Edit Logsheet' : 'Input Logsheet Harian')
@section('page-title', $editing ? 'Edit Logsheet' : 'Input Logsheet Harian')

@section('panel')
<form method="POST" action="{{ $editing ? route('logsheet.update', $reading) : route('logsheet.store') }}" enctype="multipart/form-data" class="panel" id="logsheet-form">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="panel-head">
        <div>
            <h2>Pencatatan Stand Meter</h2>
            <p>Masukkan angka stand yang tertera pada meter dan unggah foto meter sebagai bukti.</p>
        </div>
        @if (auth()->user()->role === 'teknisi')
            <span class="badge badge-role-teknisi">Role: Teknisi (Mode Input)</span>
        @endif
    </div>

    @if (isset($today) && $today && ! $editing && auth()->user()->role === 'teknisi')
        <div class="status-banner ok" style="margin-bottom:24px; display:block;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <span class="dot"></span>
                <strong style="font-size:1.05rem;">Logsheet Hari Ini Sudah Berhasil Dicatat</strong>
            </div>
            <p style="margin:0 0 16px 20px; color:var(--text-secondary); font-size:0.88rem;">
                Pencatatan untuk tanggal <b>{{ now()->translatedFormat('d F Y') }}</b> telah diinput oleh <b>{{ $today->user?->name ?? 'Teknisi' }}</b> pukul {{ $today->created_at?->format('H:i') ?? '—' }}. Role Teknisi hanya dapat memasukan logsheet baru dan tidak memiliki izin untuk mengedit atau merubah data yang telah tersimpan.
            </p>
            <div style="margin-left:20px; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('logsheet.show', $today) }}" class="btn btn-sm btn-primary">Lihat Detail Data Hari Ini</a>
                <a href="{{ route('logsheet.index') }}" class="btn btn-sm">Lihat Rekap Riwayat</a>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom:20px">Periksa kembali isian Anda — terdapat {{ $errors->count() }} kesalahan.</div>
    @endif

    <div class="top-fields">
        <div class="field">
            <label for="tanggal">Tanggal Pencatatan <span class="req">*</span></label>
            <input type="date" id="tanggal" name="tanggal" class="input"
                   value="{{ old('tanggal', $reading->tanggal?->format('Y-m-d')) }}"
                   max="{{ now()->format('Y-m-d') }}" {{ $canManage ? '' : 'readonly' }} required>
            @unless ($canManage)<p class="hint">Teknisi hanya dapat mencatat untuk hari ini.</p>@endunless
            @error('tanggal')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Pencatatan Sebelumnya</label>
            <div class="input" style="opacity:.8">
                @if ($last)
                    {{ $last->tanggal->translatedFormat('d F Y') }} · oleh {{ $last->user->name ?? '-' }}
                @else
                    Belum ada — ini pencatatan pertama
                @endif
            </div>
        </div>
    </div>

    <div class="form-grid">
        {{-- LISTRIK --}}
        <div class="meter-section elec">
            <h3><span class="ico"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg></span>kWh Meter Listrik</h3>

            @foreach (['stand_lwbp' => 'Stand kWh LWBP', 'stand_wbp' => 'Stand kWh WBP'] as $key => $label)
            <div class="field">
                <label for="{{ $key }}">{{ $label }} <span class="req">*</span></label>
                <div class="input-unit">
                    <input type="number" step="0.01" min="0" inputmode="decimal" id="{{ $key }}" name="{{ $key }}" class="input"
                           value="{{ old($key, $reading->$key) }}" data-prev="{{ $last?->$key }}" data-unit="kWh" placeholder="0,00" required>
                    <span>kWh</span>
                </div>
                @if ($last)<p class="hint">Stand sebelumnya: <b>{{ $n($last->$key) }}</b></p>@endif
                <p class="calc" data-calc-for="{{ $key }}"></p>
                @error($key)<p class="err">{{ $message }}</p>@enderror
            </div>
            @endforeach

            <div class="field">
                <label>Foto kWh Meter <span class="req">{{ $editing ? '' : '*' }}</span></label>
                <label class="upload {{ $editing ? 'has-img' : '' }}">
                    <input type="file" name="foto_kwh" accept="image/*" capture="environment" {{ $editing ? '' : 'required' }}>
                    @if ($editing)<img class="preview" src="{{ asset('storage/'.$reading->foto_kwh) }}" alt="Foto kWh">@endif
                    <span class="upload-text">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg><br>
                        Ambil foto / pilih gambar<br><small>Pastikan angka LWBP &amp; WBP terbaca jelas · maks 5 MB</small>
                    </span>
                    <span class="change">Ganti foto</span>
                </label>
                @error('foto_kwh')<p class="err">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- AIR --}}
        <div class="meter-section water">
            <h3><span class="ico"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg></span>Water Meter</h3>

            <div class="field">
                <label for="stand_air">Stand Water Meter <span class="req">*</span></label>
                <div class="input-unit">
                    <input type="number" step="0.01" min="0" inputmode="decimal" id="stand_air" name="stand_air" class="input"
                           value="{{ old('stand_air', $reading->stand_air) }}" data-prev="{{ $last?->stand_air }}" data-unit="m³" placeholder="0,00" required>
                    <span>m³</span>
                </div>
                @if ($last)<p class="hint">Stand sebelumnya: <b>{{ $n($last->stand_air) }}</b></p>@endif
                <p class="calc" data-calc-for="stand_air"></p>
                @error('stand_air')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label>Foto Water Meter <span class="req">{{ $editing ? '' : '*' }}</span></label>
                <label class="upload {{ $editing ? 'has-img' : '' }}">
                    <input type="file" name="foto_air" accept="image/*" capture="environment" {{ $editing ? '' : 'required' }}>
                    @if ($editing)<img class="preview" src="{{ asset('storage/'.$reading->foto_air) }}" alt="Foto air">@endif
                    <span class="upload-text">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg><br>
                        Ambil foto / pilih gambar<br><small>Pastikan angka meter terbaca jelas · maks 5 MB</small>
                    </span>
                    <span class="change">Ganti foto</span>
                </label>
                @error('foto_air')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="catatan">Catatan (opsional)</label>
                <textarea id="catatan" name="catatan" rows="3" class="input" placeholder="Contoh: meter berembun, ada kebocoran, dsb.">{{ old('catatan', $reading->catatan) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-foot">
        <a href="{{ route('logsheet.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn-primary" id="btn-simpan-logsheet">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            {{ $editing ? 'Simpan Perubahan' : 'Simpan Logsheet' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Hitung pemakaian secara langsung
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
        ['dragenter', 'dragover'].forEach(e => box.addEventListener(e, () => box.classList.add('drag')));
        ['dragleave', 'drop'].forEach(e => box.addEventListener(e, () => box.classList.remove('drag')));
    });
</script>
@endpush
