@extends('layouts.panel')

@section('title', 'Bulk Input Manual Water Meter (Excel)')
@section('page-title', 'Bulk Input Manual Water Meter')
@section('page-subtitle', 'Khusus Super Admin — Unggah file Excel untuk menginput data stand Water Meter Gedung 1 bulan sekaligus')

@section('panel')
<div class="panel">
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:14px;">
        <div>
            <h2><span style="color:#38bdf8;">💧</span> Bulk Input Manual Water Meter Gedung (File Excel)</h2>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Unggah dokumen spreadsheet untuk menginput data stand harian Main Water Meter Transfer Pump Lt B1 selama 1 bulan sekaligus tanpa perlu verifikasi manual.
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('logsheet.index') }}" class="btn btn-sm">
                ← Riwayat Logsheet
            </a>
            <a href="{{ route('logsheet.create') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                Input Sekaligus
            </a>
            <a href="{{ route('logsheet.manual.air') }}" class="btn btn-sm" style="background:rgba(234,179,8,0.15); color:#facc15; border:1px solid rgba(234,179,8,0.35);">
                📅 Input Manual Satuan
            </a>
            <span class="badge" style="background:rgba(6,182,212,0.15); color:var(--info); border:1px solid rgba(6,182,212,0.3); font-size:0.75rem; padding:4px 8px; border-radius:6px; font-weight:700;">
                ★ Super Admin Akses Penuh
            </span>
        </div>
    </div>

    {{-- Error / Alert Notifications --}}
    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom:20px; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; border-radius:10px; padding:14px 16px;">
            <div style="display:flex; align-items:flex-start; gap:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0; margin-top:1px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
                <div>
                    <strong style="display:block; margin-bottom:4px;">Gagal Memproses File:</strong>
                    <ul style="margin:0; padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom:20px; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:#34d399; border-radius:10px; padding:14px 16px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px; margin-bottom:28px;">
        
        {{-- Card 1: Form Upload File Excel --}}
        <div style="background:var(--bg-card); border:1px solid rgba(56,189,248,0.3); border-radius:14px; padding:24px; box-shadow:var(--shadow-sm); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#38bdf8; display:flex; align-items:center; gap:6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.567a.75.75 0 0 0 0 1.086l2.5 2.567a.75.75 0 1 0 1.004-1.11l-1.048-1.08h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                        Langkah 1: Unggah File Excel Water Meter
                    </span>
                    <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); font-size:0.7rem;">.XLSX, .XLS, .CSV</span>
                </div>

                <form method="POST" action="{{ route('logsheet.bulk.air.store') }}" enctype="multipart/form-data" id="bulk-air-form">
                    @csrf

                    {{-- Dropzone Area --}}
                    <div id="dropzone" style="border:2px dashed rgba(56,189,248,0.4); border-radius:12px; padding:32px 20px; text-align:center; background:rgba(9,13,21,0.6); cursor:pointer; transition:all 0.2s ease; margin-bottom:18px;">
                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display:none;" required>
                        <div id="dropzone-empty">
                            <div style="width:52px; height:52px; margin:0 auto 12px; border-radius:50%; background:rgba(56,189,248,0.12); display:flex; align-items:center; justify-content:center; color:#38bdf8;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:26px; height:26px;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <h4 style="font-size:0.95rem; font-weight:600; color:var(--text-primary); margin:0 0 4px;">
                                Klik untuk memilih file Excel atau seret ke sini
                            </h4>
                            <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">
                                Format yang didukung: <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong> (Maks. 10 MB)
                            </p>
                        </div>

                        <div id="dropzone-selected" style="display:none;">
                            <div style="width:48px; height:48px; margin:0 auto 8px; border-radius:50%; background:rgba(16,185,129,0.18); display:flex; align-items:center; justify-content:center; color:#34d399;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:26px; height:26px;"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd"/></svg>
                            </div>
                            <div id="selected-file-name" style="font-size:0.95rem; font-weight:700; color:#34d399; margin-bottom:2px;"></div>
                            <div id="selected-file-size" style="font-size:0.78rem; color:var(--text-muted); margin-bottom:10px;"></div>
                            <span class="btn btn-sm" style="font-size:0.75rem; padding:4px 10px; background:rgba(255,255,255,0.08); color:var(--text-secondary);">Ganti File</span>
                        </div>
                    </div>

                    {{-- Informasi Proses Otomatis --}}
                    <div style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.25); border-radius:8px; padding:12px; margin-bottom:20px; font-size:0.8rem; color:#a7f3d0; line-height:1.45;">
                        <div style="font-weight:700; display:flex; align-items:center; gap:6px; margin-bottom:4px; color:#34d399;">
                            <span>✓</span> Langsung Terverifikasi &amp; Aman
                        </div>
                        Data water meter yang diunggah akan <strong>langsung berstatus terverifikasi</strong> oleh Super Admin, otomatis tampil pada grafik &amp; tabel rekapitulasi, dan tidak menimpa data listrik jika pada tanggal tersebut sudah ada data PLN.
                    </div>

                    <button type="submit" class="btn btn-primary" id="btn-submit-upload" style="width:100%; justify-content:center; padding:12px; font-weight:700; font-size:0.95rem; background:linear-gradient(135deg, #0284c7, #0369a1); border:none; box-shadow:0 4px 12px rgba(2,132,199,0.3);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px; height:18px;"><path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd"/></svg>
                        <span>Mulai Proses Bulk Input Water Meter</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Card 2: Unduh Template & Panduan Struktur Kolom --}}
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:14px; padding:24px; box-shadow:var(--shadow-sm); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#facc15; display:flex; align-items:center; gap:6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v4.59L7.3 9.24a.75.75 0 0 0-1.1 1.02l3.25 3.5a.75.75 0 0 0 1.1 0l3.25-3.5a.75.75 0 1 0-1.1-1.02l-1.95 2.1V6.75Z" clip-rule="evenodd"/></svg>
                        Langkah 2: Unduh Template Excel
                    </span>
                    <span class="badge" style="background:rgba(234,179,8,0.15); color:#facc15; border:1px solid rgba(234,179,8,0.3); font-size:0.7rem;">Siap Pakai</span>
                </div>

                <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:14px;">
                    Unduh template resmi yang sudah terisi otomatis dengan tanggal 1 bulan penuh. Anda cukup mengisi nilai angka stand water meter pada kolom B.
                </p>

                {{-- Form Pilih Bulan Template --}}
                <form method="GET" action="{{ route('logsheet.bulk.air.template') }}" style="background:rgba(9,13,21,0.6); border:1px solid rgba(200,170,110,0.25); border-radius:10px; padding:14px; margin-bottom:18px;">
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
                        <div style="flex:1; min-width:140px;">
                            <label style="font-size:0.75rem; color:var(--text-muted); font-weight:600; display:block; margin-bottom:4px;">Bulan</label>
                            <select name="month" class="input" style="width:100%; padding:6px 10px; font-size:0.85rem; color-scheme:dark;">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div style="width:100px;">
                            <label style="font-size:0.75rem; color:var(--text-muted); font-weight:600; display:block; margin-bottom:4px;">Tahun</label>
                            <input type="number" name="year" value="{{ $currentYear }}" min="2020" max="2035" class="input" style="width:100%; padding:6px 10px; font-size:0.85rem; color-scheme:dark;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-excel" style="width:100%; justify-content:center; padding:8px 12px; font-weight:700;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px; height:15px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                        Download Template Excel (.xlsx)
                    </button>
                </form>

                {{-- Ketentuan Toleransi Data --}}
                <div style="font-size:0.78rem; color:var(--text-secondary); line-height:1.5;">
                    <div style="font-weight:700; color:var(--text-primary); margin-bottom:6px;">Ketentuan Toleransi &amp; Koreksi:</div>
                    <ul style="margin:0; padding-left:18px;">
                        <li style="margin-bottom:3px;">
                            <strong>Sel Kosong:</strong> Jika ada sel atau baris yang kosong/belum tercatat, sistem tetap menyimpan baris lainnya tanpa error.
                        </li>
                        <li style="margin-bottom:3px;">
                            <strong>Koreksi Manual:</strong> Apabila terdapat data stand yang keliru atau menurun, sistem tidak memblokir input dan tetap menyimpannya. Koreksi dapat dilakukan kapan saja di menu riwayat logsheet.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Struktur Kolom Excel Sesuai File Lampiran --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:20px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div style="font-size:0.82rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--accent-light);">
                📋 Contoh Susunan Kolom Dokumen Excel (Sesuai Lampiran Main Water Meter)
            </div>
            <span style="font-size:0.75rem; color:var(--text-muted);">
                2 Kolom: Tanggal &amp; Main Water Meter Transfer Pump Lt B1
            </span>
        </div>

        <div class="table-responsive" style="overflow-x:auto;">
            <table class="table" style="font-size:0.82rem; border-collapse:collapse; width:100%; max-width:640px;">
                <thead>
                    <tr style="background:rgba(200,170,110,0.12); color:var(--accent-light); text-align:left;">
                        <th style="padding:10px 14px; border:1px solid rgba(200,170,110,0.25); width:150px;">Kolom A<br><span style="color:#fff; font-weight:700;">Tanggal</span></th>
                        <th style="padding:10px 14px; border:1px solid rgba(200,170,110,0.25);"><span style="color:#38bdf8;">Kolom B</span><br><span style="color:#fff; font-weight:700;">Main Water meter Transfer Pump  Lt B1</span></th>
                    </tr>
                </thead>
                <tbody style="font-family:var(--font-mono, monospace);">
                    <tr style="background:rgba(9,13,21,0.5);">
                        <td style="padding:8px 14px; border:1px solid var(--border); font-weight:700;">5/1/2026</td>
                        <td style="padding:8px 14px; border:1px solid var(--border);">260907.8</td>
                    </tr>
                    <tr style="background:rgba(15,22,35,0.4);">
                        <td style="padding:8px 14px; border:1px solid var(--border); font-weight:700;">5/2/2026</td>
                        <td style="padding:8px 14px; border:1px solid var(--border);">261026.9</td>
                    </tr>
                    <tr style="background:rgba(9,13,21,0.5);">
                        <td style="padding:8px 14px; border:1px solid var(--border); font-weight:700;">5/3/2026</td>
                        <td style="padding:8px 14px; border:1px solid var(--border);">261114.2</td>
                    </tr>
                    <tr style="background:rgba(15,22,35,0.4); color:var(--text-muted);">
                        <td style="padding:8px 14px; border:1px solid var(--border); font-weight:700;">... s/d akhir bulan</td>
                        <td style="padding:8px 14px; border:1px solid var(--border);">...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const dropEmpty = document.getElementById('dropzone-empty');
    const dropSelected = document.getElementById('dropzone-selected');
    const fileNameEl = document.getElementById('selected-file-name');
    const fileSizeEl = document.getElementById('selected-file-size');

    dropzone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            handleFileSelect(fileInput.files[0]);
        }
    });

    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#38bdf8';
            dropzone.style.background = 'rgba(56,189,248,0.08)';
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = 'rgba(56,189,248,0.4)';
            dropzone.style.background = 'rgba(9,13,21,0.6)';
        });
    });

    dropzone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });

    function handleFileSelect(file) {
        dropEmpty.style.display = 'none';
        dropSelected.style.display = 'block';
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
    }
</script>
@endpush
@endsection
