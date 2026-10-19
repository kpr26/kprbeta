@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $hasRecorded = $reading && ($reading->stand_lwbp !== null || $reading->stand_wbp !== null);
    $selectedDateCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@section('title', 'Input Manual Logsheet Listrik')
@section('page-title', 'Input Manual Logsheet Listrik')
@section('page-subtitle', 'Khusus Super Admin — Input data stand kWh meter untuk tanggal yang terlewat')

@section('panel')
<form method="POST" action="{{ route('logsheet.manual.listrik.store') }}" enctype="multipart/form-data" class="panel" id="manual-listrik-form">
    @csrf

    <div class="panel-head">
        <div>
            <h2><span style="color:#fbbf24;">⚡</span> Input Manual Logsheet Listrik (Tanggal Terlewat)</h2>
            <p>Menu khusus <b>Super Admin</b> untuk mencatat data kWh meter (LWBP &amp; WBP) dari tanggal sebelumnya yang belum sempat diinput atau perlu perbaikan.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('logsheet.hub') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                ← Pilihan Logsheet
            </a>
            <a href="{{ route('logsheet.manual.air', ['tanggal' => $tanggal]) }}" class="btn btn-sm" id="btn-switch-air" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Input Manual Air
            </a>
            <span class="badge" style="background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); font-size:0.75rem; padding:4px 8px; border-radius:6px; font-weight:700;">
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
                    <strong style="font-size:1.02rem;">Data Listrik untuk Tanggal Ini Sudah Pernah Dicatat</strong>
                </div>
                <p style="margin:0 0 10px 20px; color:var(--text-secondary); font-size:0.86rem;">
                    Tercatat pada stand LWBP: <b>{{ $n($reading->stand_lwbp) }} kWh</b>, WBP: <b>{{ $n($reading->stand_wbp) }} kWh</b> 
                    @if($reading->user) oleh <b>{{ $reading->user->name }}</b> @endif
                    @if($reading->created_at) pada {{ $reading->created_at->format('d/m/Y H:i') }} @endif.
                    <br><span style="color:#fbbf24;">Menyimpan form ini akan memperbarui dan mengoreksi data listrik tanggal tersebut.</span>
                </p>
                <div style="margin-left:20px; display:flex; gap:8px;">
                    <a href="{{ route('logsheet.show', $reading) }}" target="_blank" class="btn btn-sm" style="font-size:0.78rem;">Lihat Detail Logsheet Tersebut ↗</a>
                </div>
            </div>
        @else
            <div class="status-banner" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:#93c5fd; display:flex; align-items:center; gap:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0; color:#60a5fa;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                <span>Belum ada pencatatan listrik pada tanggal <b>{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}</b>. Silakan lengkapi formulir di bawah.</span>
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom:20px">
            Periksa kembali isian Anda — terdapat {{ $errors->count() }} kesalahan input.
        </div>
    @endif

    {{-- Bagian Pilihan Tanggal & Acuan Stand --}}
    <div style="background:rgba(9, 13, 21, 0.7); border:1px solid rgba(200, 170, 110, 0.2); border-radius:var(--radius-md); padding:18px; margin-bottom:24px;">
        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--accent-light); margin-bottom:14px; display:flex; align-items:center; gap:6px;">
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
                           style="color-scheme: dark; font-weight:600; font-size:1rem; cursor:pointer;">
                </div>
                <p class="hint" id="tanggal-help-text">
                    Tanggal terpilih: <b id="formatted-selected-date" style="color:var(--text-primary);">{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}</b>
                </p>
                @error('tanggal')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label>Status Acuan Data Logsheet</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    {{-- Acuan Sebelum --}}
                    <div id="card-prev-ref" style="background:rgba(15,22,35,0.85); border:1px solid rgba(200,170,110,0.15); border-radius:6px; padding:10px;">
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Pencatatan Sebelumnya</div>
                        <div id="prev-date-text" style="font-size:0.85rem; font-weight:600; color:var(--text-primary); margin-top:2px;">
                            {{ $prev ? $prev->tanggal->translatedFormat('d M Y') : 'Belum Ada' }}
                        </div>
                        <div id="prev-stand-text" style="font-size:0.78rem; color:var(--accent-light); margin-top:4px;">
                            @if ($prev)
                                LWBP: <b>{{ $n($prev->stand_lwbp) }}</b><br>WBP: <b>{{ $n($prev->stand_wbp) }}</b>
                            @else
                                (Awal pencatatan)
                            @endif
                        </div>
                    </div>

                    {{-- Acuan Sesudah --}}
                    <div id="card-next-ref" style="background:rgba(15,22,35,0.85); border:1px solid rgba(200,170,110,0.15); border-radius:6px; padding:10px;">
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Pencatatan Berikutnya</div>
                        <div id="next-date-text" style="font-size:0.85rem; font-weight:600; color:var(--text-primary); margin-top:2px;">
                            {{ $next ? $next->tanggal->translatedFormat('d M Y') : 'Belum Ada' }}
                        </div>
                        <div id="next-stand-text" style="font-size:0.78rem; color:var(--accent-light); margin-top:4px;">
                            @if ($next)
                                LWBP: <b>{{ $n($next->stand_lwbp) }}</b><br>WBP: <b>{{ $n($next->stand_wbp) }}</b>
                            @else
                                (Posisi terdepan)
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Input Meter Section Listrik --}}
    <div class="meter-section elec" style="margin-top:0;">
        <h3>
            <span class="ico">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/>
                </svg>
            </span>
            Stand Meteran Listrik
        </h3>

        {{-- Input Stand LWBP --}}
        <div class="field">
            <label for="stand_lwbp">Stand kWh LWBP (Luar Waktu Beban Puncak) <span class="req">*</span></label>
            <div class="input-unit">
                <input type="number" step="0.01" min="0" inputmode="decimal" id="stand_lwbp" name="stand_lwbp" class="input"
                       value="{{ old('stand_lwbp', $reading?->stand_lwbp) }}"
                       data-prev="{{ $prev?->stand_lwbp }}"
                       data-next="{{ $next?->stand_lwbp }}"
                       data-unit="kWh" placeholder="0,00" required>
                <span>kWh</span>
            </div>
            <div id="hint_stand_lwbp" class="hint" style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:6px;">
                <span>Stand sebelumnya: <b id="lbl_prev_lwbp">{{ $prev ? $n($prev->stand_lwbp) . ' kWh' : '—' }}</b></span>
                @if($next)<span style="color:var(--text-muted);">Stand berikutnya: <b id="lbl_next_lwbp">{{ $n($next->stand_lwbp) }} kWh</b></span>@endif
            </div>
            <p class="calc" data-calc-for="stand_lwbp"></p>
            @error('stand_lwbp')<p class="err">{{ $message }}</p>@enderror
        </div>

        {{-- Input Stand WBP --}}
        <div class="field">
            <label for="stand_wbp">Stand kWh WBP (Waktu Beban Puncak) <span class="req">*</span></label>
            <div class="input-unit">
                <input type="number" step="0.01" min="0" inputmode="decimal" id="stand_wbp" name="stand_wbp" class="input"
                       value="{{ old('stand_wbp', $reading?->stand_wbp) }}"
                       data-prev="{{ $prev?->stand_wbp }}"
                       data-next="{{ $next?->stand_wbp }}"
                       data-unit="kWh" placeholder="0,00" required>
                <span>kWh</span>
            </div>
            <div id="hint_stand_wbp" class="hint" style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:6px;">
                <span>Stand sebelumnya: <b id="lbl_prev_wbp">{{ $prev ? $n($prev->stand_wbp) . ' kWh' : '—' }}</b></span>
                @if($next)<span style="color:var(--text-muted);">Stand berikutnya: <b id="lbl_next_wbp">{{ $n($next->stand_wbp) }} kWh</b></span>@endif
            </div>
            <p class="calc" data-calc-for="stand_wbp"></p>
            @error('stand_wbp')<p class="err">{{ $message }}</p>@enderror
        </div>

        {{-- Upload Foto kWh Meter --}}
        <div class="field">
            <label>Foto kWh Meter <span style="font-weight:normal; font-size:0.75rem; color:var(--text-muted);">(Opsional jika data arsip lampau tidak memiliki foto)</span></label>
            <label class="upload {{ $reading?->foto_kwh ? 'has-img' : '' }}" id="upload-box-kwh">
                <input type="file" name="foto_kwh" id="foto_kwh_input" accept="image/*">
                @if ($reading?->foto_kwh)
                    <img class="preview" id="kwh-preview-img" src="{{ asset('storage/'.$reading->foto_kwh) }}" alt="Foto kWh">
                @endif
                <span class="upload-text" id="kwh-upload-text">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg><br>
                    Ambil foto / unggah dokumen fisik<br><small>Format: JPG, PNG, WEBP &middot; Maksimal 5 MB</small>
                </span>
                <span class="change">Ganti foto</span>
            </label>
            @error('foto_kwh')<p class="err">{{ $message }}</p>@enderror
        </div>

        {{-- Catatan --}}
        <div class="field">
            <label for="catatan">Catatan / Keterangan Input Manual (opsional)</label>
            <textarea id="catatan" name="catatan" rows="2" class="input" placeholder="Contoh: Input manual susulan tanggal terlewat oleh Super Admin, data diambil dari buku catatan teknisi.">{{ old('catatan', $reading?->catatan) }}</textarea>
        </div>
    </div>

    <div class="form-foot">
        <a href="{{ route('logsheet.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn-primary" id="btn-simpan-manual-listrik">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            Simpan Stand Listrik Manual
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function() {
    const fmt = v => Number(v).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const dateInput = document.getElementById('tanggal');
    const switchAirBtn = document.getElementById('btn-switch-air');
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
    const uploadBox = document.getElementById('upload-box-kwh');
    const fileInput = document.getElementById('foto_kwh_input');
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

            // Perbarui tombol switch air
            if (switchAirBtn) {
                switchAirBtn.href = "{{ route('logsheet.manual.air') }}?tanggal=" + encodeURIComponent(selectedDate);
            }

            // Tampilkan indikator loading halus
            if (formattedSelectedDate) {
                formattedSelectedDate.textContent = 'Memuat acuan data...';
            }

            fetch(`{{ route('logsheet.manual.reference') }}?tanggal=${encodeURIComponent(selectedDate)}&type=listrik`, {
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
                                <strong style="font-size:1.02rem;">Data Listrik untuk Tanggal Ini Sudah Pernah Dicatat</strong>
                            </div>
                            <p style="margin:0 0 10px 20px; color:var(--text-secondary); font-size:0.86rem;">
                                Tercatat pada stand LWBP: <b>${fmt(data.current.stand_lwbp)} kWh</b>, WBP: <b>${fmt(data.current.stand_wbp)} kWh</b> 
                                oleh <b>${data.current.user_name}</b> pukul ${data.current.created_at || '—'}.
                                <br><span style="color:#fbbf24;">Menyimpan form ini akan memperbarui dan mengoreksi data listrik tanggal tersebut.</span>
                            </p>
                        </div>
                    `;
                    // Jika field kosong, prefill nilai yang tersimpan
                    const inLwbp = document.getElementById('stand_lwbp');
                    const inWbp = document.getElementById('stand_wbp');
                    if (inLwbp && (!inLwbp.value || inLwbp.value == '0')) inLwbp.value = data.current.stand_lwbp;
                    if (inWbp && (!inWbp.value || inWbp.value == '0')) inWbp.value = data.current.stand_wbp;

                    if (data.current.foto_kwh_url && uploadBox) {
                        let preview = uploadBox.querySelector('img.preview');
                        if (!preview) {
                            preview = document.createElement('img');
                            preview.className = 'preview';
                            uploadBox.appendChild(preview);
                        }
                        preview.src = data.current.foto_kwh_url;
                        uploadBox.classList.add('has-img');
                    }
                } else {
                    dateStatusBox.innerHTML = `
                        <div class="status-banner" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:#93c5fd; display:flex; align-items:center; gap:10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0; color:#60a5fa;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                            <span>Belum ada pencatatan listrik pada tanggal <b>${data.tanggal_formatted}</b>. Silakan lengkapi formulir di bawah.</span>
                        </div>
                    `;
                }

                // Update Referensi Prev
                const inLwbp = document.getElementById('stand_lwbp');
                const inWbp = document.getElementById('stand_wbp');
                const prevDateText = document.getElementById('prev-date-text');
                const prevStandText = document.getElementById('prev-stand-text');
                const lblPrevLwbp = document.getElementById('lbl_prev_lwbp');
                const lblPrevWbp = document.getElementById('lbl_prev_wbp');

                if (data.prev) {
                    if (prevDateText) prevDateText.textContent = data.prev.tanggal_formatted;
                    if (prevStandText) prevStandText.innerHTML = `LWBP: <b>${data.prev.stand_lwbp_fmt}</b><br>WBP: <b>${data.prev.stand_wbp_fmt}</b>`;
                    if (lblPrevLwbp) lblPrevLwbp.textContent = data.prev.stand_lwbp_fmt + ' kWh';
                    if (lblPrevWbp) lblPrevWbp.textContent = data.prev.stand_wbp_fmt + ' kWh';
                    if (inLwbp) inLwbp.dataset.prev = data.prev.stand_lwbp;
                    if (inWbp) inWbp.dataset.prev = data.prev.stand_wbp;
                } else {
                    if (prevDateText) prevDateText.textContent = 'Belum Ada';
                    if (prevStandText) prevStandText.textContent = '(Awal pencatatan)';
                    if (lblPrevLwbp) lblPrevLwbp.textContent = '—';
                    if (lblPrevWbp) lblPrevWbp.textContent = '—';
                    if (inLwbp) delete inLwbp.dataset.prev;
                    if (inWbp) delete inWbp.dataset.prev;
                }

                // Update Referensi Next
                const nextDateText = document.getElementById('next-date-text');
                const nextStandText = document.getElementById('next-stand-text');
                const lblNextLwbp = document.getElementById('lbl_next_lwbp');
                const lblNextWbp = document.getElementById('lbl_next_wbp');

                if (data.next) {
                    if (nextDateText) nextDateText.textContent = data.next.tanggal_formatted;
                    if (nextStandText) nextStandText.innerHTML = `LWBP: <b>${data.next.stand_lwbp_fmt}</b><br>WBP: <b>${data.next.stand_wbp_fmt}</b>`;
                    if (lblNextLwbp) lblNextLwbp.textContent = data.next.stand_lwbp_fmt + ' kWh';
                    if (lblNextWbp) lblNextWbp.textContent = data.next.stand_wbp_fmt + ' kWh';
                    if (inLwbp) inLwbp.dataset.next = data.next.stand_lwbp;
                    if (inWbp) inWbp.dataset.next = data.next.stand_wbp;
                } else {
                    if (nextDateText) nextDateText.textContent = 'Belum Ada';
                    if (nextStandText) nextStandText.textContent = '(Posisi terdepan)';
                    if (lblNextLwbp) lblNextLwbp.textContent = '—';
                    if (lblNextWbp) lblNextWbp.textContent = '—';
                    if (inLwbp) delete inLwbp.dataset.next;
                    if (inWbp) delete inWbp.dataset.next;
                }

                updateCalculations();
            })
            .catch(err => console.error('Gagal mengambil acuan logsheet:', err));
        });
    }
})();
</script>
@endpush
