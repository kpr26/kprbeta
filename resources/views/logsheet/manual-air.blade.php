@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $hasRecorded = $reading && $reading->stand_air !== null;
    $selectedDateCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@section('title', 'Input Manual Logsheet Air')
@section('page-title', 'Input Manual Logsheet Air')
@section('page-subtitle', 'Khusus Super Admin — Input data stand Water Meter untuk tanggal yang terlewat')

@section('panel')
<form method="POST" action="{{ route('logsheet.manual.air.store') }}" enctype="multipart/form-data" class="panel" id="manual-air-form">
    @csrf

    <div class="panel-head">
        <div>
            <h2><span style="color:#38bdf8;">💧</span> Input Manual Logsheet Air (Tanggal Terlewat)</h2>
            <p>Menu khusus <b>Super Admin</b> untuk mencatat data Water Meter (m³) dari tanggal sebelumnya yang belum sempat diinput atau perlu perbaikan.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('logsheet.hub') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                ← Pilihan Logsheet
            </a>
            <a href="{{ route('logsheet.manual.listrik', ['tanggal' => $tanggal]) }}" class="btn btn-sm" id="btn-switch-listrik" style="background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3);">
                ⚡ Input Manual Listrik
            </a>
            <a href="{{ route('logsheet.bulk.air') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.35); font-weight:700;">
                📤 Bulk Input Air (Excel)
            </a>
            <span class="badge" style="background:rgba(56,189,248,0.2); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); font-size:0.75rem; padding:4px 8px; border-radius:6px; font-weight:700;">
                ★ Super Admin Akses Penuh
            </span>
        </div>
    </div>

    {{-- Banner Status Tanggal Terpilih --}}
    <div id="date-status-box" style="margin-bottom:20px;">
        @if ($hasRecorded)
            <div class="status-banner ok" style="display:block;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                    <span class="dot"></span>
                    <strong style="font-size:1.02rem;">Data Air untuk Tanggal Ini Sudah Pernah Dicatat</strong>
                </div>
                <p style="margin:0 0 10px 20px; color:var(--text-secondary); font-size:0.86rem;">
                    Tercatat pada stand Air: <b>{{ $n($reading->stand_air) }} m³</b> 
                    @if($reading->user) oleh <b>{{ $reading->user->name }}</b> @endif
                    @if($reading->created_at) pada {{ $reading->created_at->format('d/m/Y H:i') }} @endif.
                    <br><span style="color:#38bdf8;">Menyimpan form ini akan memperbarui dan mengoreksi data water meter tanggal tersebut.</span>
                </p>
                <div style="margin-left:20px; display:flex; gap:8px;">
                    <a href="{{ route('logsheet.show', $reading) }}" target="_blank" class="btn btn-sm" style="font-size:0.78rem;">Lihat Detail Logsheet Tersebut ↗</a>
                </div>
            </div>
        @else
            <div class="status-banner" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:#93c5fd; display:flex; align-items:center; gap:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0; color:#60a5fa;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                <span>Belum ada pencatatan air pada tanggal <b>{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}</b>. Silakan lengkapi formulir di bawah.</span>
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom:20px">
            Periksa kembali isian Anda — terdapat {{ $errors->count() }} kesalahan input.
        </div>
    @endif

    {{-- Bagian Pilihan Tanggal & Acuan Stand --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-md); padding:18px; margin-bottom:24px;">
        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#38bdf8; margin-bottom:14px; display:flex; align-items:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd"/></svg>
            <span>Pilih Tanggal Logsheet Terlewat</span>
        </div>

        <div class="top-fields" style="margin-bottom:0;">
            <div class="field">
                <label for="tanggal">Tanggal Pencatatan <span class="req">*</span></label>
                <div class="input-wrapper" style="position:relative;">
                    <input type="date" id="tanggal" name="tanggal" class="input"
                           value="{{ old('tanggal', $tanggal) }}"
                           max="{{ now()->format('Y-m-d') }}" required
                           style="font-weight:600; font-size:1rem; cursor:pointer;">
                </div>
                <p class="hint" id="tanggal-help-text">
                    Tanggal terpilih: <b id="formatted-selected-date" style="color:var(--text-primary);">{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}</b>
                </p>
                @error('tanggal')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label>Status Acuan Stand Water Meter</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    {{-- Acuan Sebelum --}}
                    <div id="card-prev-ref" style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:6px; padding:10px;">
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Pencatatan Sebelumnya</div>
                        <div id="prev-date-text" style="font-size:0.85rem; font-weight:600; color:var(--text-primary); margin-top:2px;">
                            {{ $prev ? $prev->tanggal->translatedFormat('d M Y') : 'Belum Ada' }}
                        </div>
                        <div id="prev-stand-text" style="font-size:0.78rem; color:#38bdf8; margin-top:4px;">
                            @if ($prev)
                                Stand: <b>{{ $n($prev->stand_air) }} m³</b>
                            @else
                                (Awal pencatatan)
                            @endif
                        </div>
                    </div>

                    {{-- Acuan Sesudah --}}
                    <div id="card-next-ref" style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:6px; padding:10px;">
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Pencatatan Berikutnya</div>
                        <div id="next-date-text" style="font-size:0.85rem; font-weight:600; color:var(--text-primary); margin-top:2px;">
                            {{ $next ? $next->tanggal->translatedFormat('d M Y') : 'Belum Ada' }}
                        </div>
                        <div id="next-stand-text" style="font-size:0.78rem; color:#38bdf8; margin-top:4px;">
                            @if ($next)
                                Stand: <b>{{ $n($next->stand_air) }} m³</b>
                            @else
                                (Posisi terdepan)
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Input Meter Section Air --}}
    <div class="meter-section water" style="margin-top:0;">
        <h3>
            <span class="ico">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/>
                </svg>
            </span>
            Stand Water Meter Gedung
        </h3>

        {{-- Input Stand Air --}}
        <div class="field">
            <label for="stand_air">Stand Water Meter (m³) <span class="req">*</span></label>
            <div class="input-unit">
                <input type="number" step="0.01" min="0" inputmode="decimal" id="stand_air" name="stand_air" class="input"
                       value="{{ old('stand_air', $reading?->stand_air) }}"
                       data-prev="{{ $prev?->stand_air }}"
                       data-next="{{ $next?->stand_air }}"
                       data-unit="m³" placeholder="0,00" required>
                <span>m³</span>
            </div>
            <div id="hint_stand_air" class="hint" style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:6px;">
                <span>Stand sebelumnya: <b id="lbl_prev_air">{{ $prev ? $n($prev->stand_air) . ' m³' : '—' }}</b></span>
                @if($next)<span style="color:var(--text-muted);">Stand berikutnya: <b id="lbl_next_air">{{ $n($next->stand_air) }} m³</b></span>@endif
            </div>
            <p class="calc" data-calc-for="stand_air"></p>
            @error('stand_air')<p class="err">{{ $message }}</p>@enderror
        </div>

        {{-- Upload Foto Water Meter --}}
        <div class="field">
            <label>Foto Water Meter <span style="font-weight:normal; font-size:0.75rem; color:var(--text-muted);">(Opsional jika data arsip lampau tidak memiliki foto)</span></label>
            <label class="upload {{ $reading?->foto_air ? 'has-img' : '' }}" id="upload-box-air">
                <input type="file" name="foto_air" id="foto_air_input" accept="image/*">
                @if ($reading?->foto_air)
                    <img class="preview" id="air-preview-img" src="{{ asset('storage/'.$reading->foto_air) }}" alt="Foto Air">
                @endif
                <span class="upload-text" id="air-upload-text">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg><br>
                    Ambil foto / unggah dokumen fisik meter air<br><small>Format: JPG, PNG, WEBP &middot; Maksimal 5 MB</small>
                </span>
                <span class="change">Ganti foto</span>
            </label>
            @error('foto_air')<p class="err">{{ $message }}</p>@enderror
        </div>

        {{-- Catatan --}}
        <div class="field">
            <label for="catatan">Catatan / Keterangan Input Manual (opsional)</label>
            <textarea id="catatan" name="catatan" rows="2" class="input" placeholder="Contoh: Input manual susulan tanggal terlewat oleh Super Admin, data dicatat dari catatan fisik teknisi.">{{ old('catatan', $reading?->catatan) }}</textarea>
        </div>
    </div>

    <div class="form-foot">
        <a href="{{ route('logsheet.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn-primary" id="btn-simpan-manual-air">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            Simpan Stand Air Manual
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function() {
    const fmt = v => Number(v).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const dateInput = document.getElementById('tanggal');
    const switchListrikBtn = document.getElementById('btn-switch-listrik');
    const formattedSelectedDate = document.getElementById('formatted-selected-date');
    const dateStatusBox = document.getElementById('date-status-box');

    // Live stand calculation (perbedaan terhadap prev & next)
    function updateCalculations() {
        document.querySelectorAll('input[data-prev]').forEach(inp => {
            const out = document.querySelector(`[data-calc-for="${inp.id}"]`);
            if (!out) return;

            const cur = parseFloat(inp.value);
            const prev = parseFloat(inp.dataset.prev);
            const next = parseFloat(inp.dataset.next);

            if (isNaN(cur)) {
                out.textContent = '';
                return;
            }

            let msg = '';
            let isBad = false;

            if (!isNaN(prev)) {
                const diffPrev = cur - prev;
                if (diffPrev < 0) {
                    isBad = true;
                    msg += `⚠ Stand lebih kecil dari sebelumnya (${fmt(diffPrev)} ${inp.dataset.unit}). `;
                } else {
                    msg += `Pemakaian dari sebelumnya: <b>+${fmt(diffPrev)} ${inp.dataset.unit}</b>. `;
                }
            }

            if (!isNaN(next)) {
                const diffNext = next - cur;
                if (diffNext < 0) {
                    isBad = true;
                    msg += `⚠ Melebihi pencatatan berikutnya (+${fmt(Math.abs(diffNext))} ${inp.dataset.unit}). `;
                } else {
                    msg += `Margin ke pencatatan berikutnya: <b>${fmt(diffNext)} ${inp.dataset.unit}</b>.`;
                }
            }

            out.classList.toggle('bad', isBad);
            out.innerHTML = msg;
        });
    }

    // Pasang listener input
    document.querySelectorAll('input[data-prev]').forEach(inp => {
        inp.addEventListener('input', updateCalculations);
    });
    updateCalculations();

    // Preview foto upload
    const uploadBox = document.getElementById('upload-box-air');
    const fileInput = document.getElementById('foto_air_input');
    if (fileInput && uploadBox) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;
            let preview = uploadBox.querySelector('img.preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.className = 'preview';
                uploadBox.appendChild(preview);
            }
            preview.src = URL.createObjectURL(file);
            uploadBox.classList.add('has-img');
        });
    }

    // Dynamic Date Change via AJAX
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const selectedDate = this.value;
            if (!selectedDate) return;

            // Perbarui tombol switch listrik
            if (switchListrikBtn) {
                switchListrikBtn.href = "{{ route('logsheet.manual.listrik') }}?tanggal=" + encodeURIComponent(selectedDate);
            }

            // Tampilkan indikator loading halus
            if (formattedSelectedDate) {
                formattedSelectedDate.textContent = 'Memuat acuan data...';
            }

            fetch(`{{ route('logsheet.manual.reference') }}?tanggal=${encodeURIComponent(selectedDate)}&type=air`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (formattedSelectedDate) {
                    formattedSelectedDate.textContent = data.tanggal_formatted;
                }

                // Update Status Box
                if (data.exists && data.current) {
                    dateStatusBox.innerHTML = `
                        <div class="status-banner ok" style="display:block;">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                                <span class="dot"></span>
                                <strong style="font-size:1.02rem;">Data Air untuk Tanggal Ini Sudah Pernah Dicatat</strong>
                            </div>
                            <p style="margin:0 0 10px 20px; color:var(--text-secondary); font-size:0.86rem;">
                                Tercatat pada stand Air: <b>${fmt(data.current.stand_air)} m³</b> 
                                oleh <b>${data.current.user_name}</b> pukul ${data.current.created_at || '—'}.
                                <br><span style="color:#38bdf8;">Menyimpan form ini akan memperbarui dan mengoreksi data water meter tanggal tersebut.</span>
                            </p>
                        </div>
                    `;
                    // Jika field kosong, prefill nilai yang tersimpan
                    const inAir = document.getElementById('stand_air');
                    if (inAir && (!inAir.value || inAir.value == '0')) inAir.value = data.current.stand_air;

                    if (data.current.foto_air_url && uploadBox) {
                        let preview = uploadBox.querySelector('img.preview');
                        if (!preview) {
                            preview = document.createElement('img');
                            preview.className = 'preview';
                            uploadBox.appendChild(preview);
                        }
                        preview.src = data.current.foto_air_url;
                        uploadBox.classList.add('has-img');
                    }
                } else {
                    dateStatusBox.innerHTML = `
                        <div class="status-banner" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:#93c5fd; display:flex; align-items:center; gap:10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0; color:#60a5fa;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                            <span>Belum ada pencatatan air pada tanggal <b>${data.tanggal_formatted}</b>. Silakan lengkapi formulir di bawah.</span>
                        </div>
                    `;
                }

                // Update Referensi Prev
                const inAir = document.getElementById('stand_air');
                const prevDateText = document.getElementById('prev-date-text');
                const prevStandText = document.getElementById('prev-stand-text');
                const lblPrevAir = document.getElementById('lbl_prev_air');

                if (data.prev) {
                    if (prevDateText) prevDateText.textContent = data.prev.tanggal_formatted;
                    if (prevStandText) prevStandText.innerHTML = `Stand: <b>${data.prev.stand_air_fmt} m³</b>`;
                    if (lblPrevAir) lblPrevAir.textContent = data.prev.stand_air_fmt + ' m³';
                    if (inAir) inAir.dataset.prev = data.prev.stand_air;
                } else {
                    if (prevDateText) prevDateText.textContent = 'Belum Ada';
                    if (prevStandText) prevStandText.textContent = '(Awal pencatatan)';
                    if (lblPrevAir) lblPrevAir.textContent = '—';
                    if (inAir) delete inAir.dataset.prev;
                }

                // Update Referensi Next
                const nextDateText = document.getElementById('next-date-text');
                const nextStandText = document.getElementById('next-stand-text');
                const lblNextAir = document.getElementById('lbl_next_air');

                if (data.next) {
                    if (nextDateText) nextDateText.textContent = data.next.tanggal_formatted;
                    if (nextStandText) nextStandText.innerHTML = `Stand: <b>${data.next.stand_air_fmt} m³</b>`;
                    if (lblNextAir) lblNextAir.textContent = data.next.stand_air_fmt + ' m³';
                    if (inAir) inAir.dataset.next = data.next.stand_air;
                } else {
                    if (nextDateText) nextDateText.textContent = 'Belum Ada';
                    if (nextStandText) nextStandText.textContent = '(Posisi terdepan)';
                    if (lblNextAir) lblNextAir.textContent = '—';
                    if (inAir) delete inAir.dataset.next;
                }

                updateCalculations();
            })
            .catch(err => console.error('Gagal mengambil acuan logsheet air:', err));
        });
    }
})();
</script>
@endpush
