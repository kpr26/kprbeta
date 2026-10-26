@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $hasRecorded = $reading && ($reading->stand_total !== null || $reading->stand_bp !== null || $reading->stand_lwbp !== null || $reading->stand_wbp !== null);
    $selectedDateCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@section('title', 'Input Manual Logsheet AMR PLN T33N')
@section('page-title', 'Input Manual AMR PLN T33N')
@section('page-subtitle', 'Khusus Super Admin — Input data stand kWh meter AMR PLN T33N untuk tanggal yang terlewat')

@section('panel')
<form method="POST" action="{{ route('logsheet.manual.listrik.store') }}" enctype="multipart/form-data" class="panel" id="manual-listrik-form">
    @csrf

    <div class="panel-head">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; border-radius:10px; background:rgba(255,201,14,0.12); border:1px solid rgba(255,201,14,0.3); padding:3px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                {{-- Logo PLN Indonesia --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:30px; height:30px;">
                    <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M14 36c4-2 7 2 11 0s7-2 11 0 7 2 11 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M16 41c4-2 7 2 11 0s7-2 11 0 7 2 9 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M19 46c3.5-1.8 6.5 1.8 10 0s6.5-1.8 10 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                </svg>
            </div>
            <div>
                <h2>Input Manual Logsheet AMR PLN T33N (Tanggal Terlewat)</h2>
                <p>Menu khusus <b>Super Admin</b> untuk mencatat data kWh meter (BP, LBP1, LBP2, Total) dari tanggal sebelumnya yang belum sempat diinput atau perlu perbaikan.</p>
            </div>
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
                    <strong style="font-size:1.02rem;">Data AMR PLN untuk Tanggal Ini Sudah Pernah Dicatat</strong>
                </div>
                <p style="margin:0 0 10px 20px; color:var(--text-secondary); font-size:0.86rem; line-height:1.5;">
                    @if($reading->stand_bp !== null || $reading->stand_total !== null)
                        Tercatat pada Stand BP: <b>{{ $n($reading->stand_bp) }} kWh</b> · LBP1: <b>{{ $n($reading->stand_lbp1) }} kWh</b> · LBP2: <b>{{ $n($reading->stand_lbp2) }} kWh</b> · Total: <b>{{ $n($reading->stand_total) }} kWh</b>
                    @else
                        Tercatat pada Stand LWBP: <b>{{ $n($reading->stand_lwbp) }} kWh</b> · WBP: <b>{{ $n($reading->stand_wbp) }} kWh</b>
                    @endif
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
                <span>Belum ada pencatatan AMR PLN pada tanggal <b>{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}</b>. Silakan lengkapi formulir di bawah.</span>
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
        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--accent); margin-bottom:14px; display:flex; align-items:center; gap:6px;">
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
                <label>Status Acuan Data AMR PLN</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    {{-- Acuan Sebelum --}}
                    <div id="card-prev-ref" style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:6px; padding:10px;">
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Pencatatan Sebelumnya</div>
                        <div id="prev-date-text" style="font-size:0.85rem; font-weight:600; color:var(--text-primary); margin-top:2px;">
                            {{ $prev ? $prev->tanggal->translatedFormat('d M Y') : 'Belum Ada' }}
                        </div>
                        <div id="prev-stand-text" style="font-size:0.78rem; color:var(--accent); margin-top:4px; line-height:1.4;">
                            @if ($prev)
                                @if($prev->stand_total !== null)
                                    Total: <b>{{ $n($prev->stand_total) }}</b><br>
                                    BP: {{ $n($prev->stand_bp) }} · LBP1: {{ $n($prev->stand_lbp1) }} · LBP2: {{ $n($prev->stand_lbp2) }}
                                @else
                                    LWBP: <b>{{ $n($prev->stand_lwbp) }}</b> · WBP: <b>{{ $n($prev->stand_wbp) }}</b>
                                @endif
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
                        <div id="next-stand-text" style="font-size:0.78rem; color:var(--accent-light); margin-top:4px; line-height:1.4;">
                            @if ($next)
                                @if($next->stand_total !== null)
                                    Total: <b>{{ $n($next->stand_total) }}</b><br>
                                    BP: {{ $n($next->stand_bp) }} · LBP1: {{ $n($next->stand_lbp1) }} · LBP2: {{ $n($next->stand_lbp2) }}
                                @else
                                    LWBP: <b>{{ $n($next->stand_lwbp) }}</b> · WBP: <b>{{ $n($next->stand_wbp) }}</b>
                                @endif
                            @else
                                (Posisi terdepan)
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Input Meter Section AMR PLN --}}
    <div class="meter-section elec" style="margin-top:0;">
        <h3>
            <span class="ico" style="background:rgba(255,201,14,0.12); padding:3px; border-radius:6px; display:inline-flex;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:20px; height:20px;">
                    <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M14 36c4-2 7 2 11 0s7-2 11 0 7 2 11 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                </svg>
            </span>
            Stand Meteran AMR PLN T33N (kWh)
        </h3>

        @php
            $amrManualFields = [
                'stand_bp'    => ['label' => 'Stand BP (Beban Puncak)', 'fallback' => 'stand_wbp'],
                'stand_lbp1'  => ['label' => 'Stand LBP 1 (Luar Beban Puncak 1)', 'fallback' => null],
                'stand_lbp2'  => ['label' => 'Stand LBP 2 (Luar Beban Puncak 2)', 'fallback' => null],
                'stand_total' => ['label' => 'Stand Total', 'fallback' => null],
            ];
        @endphp

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
            @foreach ($amrManualFields as $key => $info)
            @php
                $prevVal = $prev ? ($prev->$key ?? ($info['fallback'] ? $prev->{$info['fallback']} : null)) : null;
                $nextVal = $next ? ($next->$key ?? ($info['fallback'] ? $next->{$info['fallback']} : null)) : null;
                $curVal = old($key, $reading?->$key ?? ($info['fallback'] ? $reading?->{$info['fallback']} : null));
            @endphp
            <div class="field" style="margin-bottom:0;">
                <label for="{{ $key }}">{{ $info['label'] }} <span class="req">*</span></label>
                <div class="input-unit">
                    <input type="number" step="0.01" min="0" inputmode="decimal" id="{{ $key }}" name="{{ $key }}" class="input"
                           value="{{ $curVal }}"
                           data-prev="{{ $prevVal }}"
                           data-next="{{ $nextVal }}"
                           data-unit="kWh" placeholder="0,00" required>
                    <span>kWh</span>
                </div>
                <div id="hint_{{ $key }}" class="hint" style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:4px; font-size:0.75rem;">
                    <span>Sebelum: <b id="lbl_prev_{{ $key }}">{{ $prevVal !== null ? $n($prevVal) . ' kWh' : '—' }}</b></span>
                    @if($nextVal !== null)<span style="color:var(--text-muted);">Berikut: <b id="lbl_next_{{ $key }}">{{ $n($nextVal) }} kWh</b></span>@endif
                </div>
                <p class="calc" data-calc-for="{{ $key }}"></p>
                @error($key)<p class="err">{{ $message }}</p>@enderror
            </div>
            @endforeach
        </div>

        {{-- Upload 1 Foto Fisik kWh Meter AMR PLN --}}
        <div class="field" style="margin-top:20px;">
            <label>Foto Fisik Meteran AMR PLN T33N <span style="font-weight:normal; font-size:0.75rem; color:var(--text-muted);">(Cukup 1 foto mencakup register stand · Opsional jika data arsip lampau tidak memiliki foto)</span></label>
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
            <textarea id="catatan" name="catatan" rows="2" class="input" placeholder="Contoh: Input manual susulan tanggal terlewat oleh Super Admin, data diambil dari buku catatan fisik teknisi.">{{ old('catatan', $reading?->catatan) }}</textarea>
        </div>
    </div>

    <div class="form-foot">
        <a href="{{ route('logsheet.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn-primary" id="btn-simpan-manual-listrik">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            Simpan Stand AMR PLN Manual
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

    // Live stand calculation
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

            if (switchAirBtn) {
                switchAirBtn.href = "{{ route('logsheet.manual.air') }}?tanggal=" + encodeURIComponent(selectedDate);
            }

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
                                <strong style="font-size:1.02rem;">Data AMR PLN untuk Tanggal Ini Sudah Pernah Dicatat</strong>
                            </div>
                            <p style="margin:0 0 10px 20px; color:var(--text-secondary); font-size:0.86rem; line-height:1.5;">
                                Tercatat pada Stand BP: <b>${fmt(data.current.stand_bp)} kWh</b> · LBP 1: <b>${fmt(data.current.stand_lbp1)} kWh</b> · LBP 2: <b>${fmt(data.current.stand_lbp2)} kWh</b> · Total: <b>${fmt(data.current.stand_total)} kWh</b> 
                                oleh <b>${data.current.user_name}</b> pukul ${data.current.created_at || '—'}.
                                <br><span style="color:#fbbf24;">Menyimpan form ini akan memperbarui dan mengoreksi data listrik tanggal tersebut.</span>
                            </p>
                        </div>
                    `;
                    ['stand_bp', 'stand_lbp1', 'stand_lbp2', 'stand_total'].forEach(f => {
                        const el = document.getElementById(f);
                        if (el && (!el.value || el.value == '0') && data.current[f] !== undefined) {
                            el.value = data.current[f];
                        }
                    });

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
                            <span>Belum ada pencatatan AMR PLN pada tanggal <b>${data.tanggal_formatted}</b>. Silakan lengkapi formulir di bawah.</span>
                        </div>
                    `;
                }

                // Update Referensi Prev
                const prevDateText = document.getElementById('prev-date-text');
                const prevStandText = document.getElementById('prev-stand-text');
                const fields = ['stand_bp', 'stand_lbp1', 'stand_lbp2', 'stand_total'];

                if (data.prev) {
                    if (prevDateText) prevDateText.textContent = data.prev.tanggal_formatted;
                    if (prevStandText) {
                        prevStandText.innerHTML = `Total: <b>${data.prev.stand_total_fmt}</b><br>BP: ${data.prev.stand_bp_fmt} · LBP1: ${data.prev.stand_lbp1_fmt} · LBP2: ${data.prev.stand_lbp2_fmt}`;
                    }
                    fields.forEach(f => {
                        const inEl = document.getElementById(f);
                        const lblEl = document.getElementById(`lbl_prev_${f}`);
                        if (lblEl) lblEl.textContent = (data.prev[`${f}_fmt`] || '—') + ' kWh';
                        if (inEl && data.prev[f] !== undefined) inEl.dataset.prev = data.prev[f];
                    });
                } else {
                    if (prevDateText) prevDateText.textContent = 'Belum Ada';
                    if (prevStandText) prevStandText.textContent = '(Awal pencatatan)';
                    fields.forEach(f => {
                        const inEl = document.getElementById(f);
                        const lblEl = document.getElementById(`lbl_prev_${f}`);
                        if (lblEl) lblEl.textContent = '—';
                        if (inEl) delete inEl.dataset.prev;
                    });
                }

                // Update Referensi Next
                const nextDateText = document.getElementById('next-date-text');
                const nextStandText = document.getElementById('next-stand-text');

                if (data.next) {
                    if (nextDateText) nextDateText.textContent = data.next.tanggal_formatted;
                    if (nextStandText) {
                        nextStandText.innerHTML = `Total: <b>${data.next.stand_total_fmt}</b><br>BP: ${data.next.stand_bp_fmt} · LBP1: ${data.next.stand_lbp1_fmt} · LBP2: ${data.next.stand_lbp2_fmt}`;
                    }
                    fields.forEach(f => {
                        const inEl = document.getElementById(f);
                        const lblEl = document.getElementById(`lbl_next_${f}`);
                        if (lblEl) lblEl.textContent = (data.next[`${f}_fmt`] || '—') + ' kWh';
                        if (inEl && data.next[f] !== undefined) inEl.dataset.next = data.next[f];
                    });
                } else {
                    if (nextDateText) nextDateText.textContent = 'Belum Ada';
                    if (nextStandText) nextStandText.textContent = '(Posisi terdepan)';
                    fields.forEach(f => {
                        const inEl = document.getElementById(f);
                        const lblEl = document.getElementById(`lbl_next_${f}`);
                        if (lblEl) lblEl.textContent = '—';
                        if (inEl) delete inEl.dataset.next;
                    });
                }

                updateCalculations();
            })
            .catch(err => console.error('Gagal mengambil acuan logsheet:', err));
        });
    }
})();
</script>
@endpush
