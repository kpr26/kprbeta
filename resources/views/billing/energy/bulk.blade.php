@extends('layouts.panel')

@section('title', 'Input Bulk Manual Listrik - Billing System')
@section('page-title', 'Input Bulk Manual Listrik Tenant')
@section('page-subtitle', 'Import & Pencatatan Massal Stand Listrik (Termasuk Fasilitas EV Charger) Khusus Super Admin')

@section('panel')
<div class="panel">
    <!-- Header -->
    <div class="panel-head">
        <div>
            <h2>Import Massal / Input Bulk Manual Data Listrik</h2>
            <p>Fitur khusus Super Admin untuk memasukkan data tagihan listrik sekaligus dari file CSV / Excel sesuai format template bulanan.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('billing.energy.index', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                &larr; Kembali ke Daftar Listrik
            </a>
            <a href="{{ route('billing.energy.bulk.template', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#facc15; font-weight:600;">
                📥 Unduh Template CSV (Sudah Terisi Stand Awal)
            </a>
        </div>
    </div>

    <!-- Info Box: Otomatisasi Fasilitas EV Charger -->
    <div style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); border-radius:var(--radius-md); padding:16px 20px; margin:20px 0; font-size:0.88rem; line-height:1.6;">
        <div style="display:flex; align-items:center; gap:10px; font-weight:700; color:#38bdf8; margin-bottom:6px;">
            <span style="font-size:1.2rem;">🚗⚡</span>
            <span>Mendukung 1 Unit dengan 2 Meteran Berbeda (Unit Utama &amp; EV Charger)</span>
        </div>
        <div style="color:var(--text-secondary);">
            Sistem secara cerdas mengenali baris lantai yang memiliki akhiran <code style="background:rgba(255,255,255,0.08); padding:2px 6px; border-radius:4px; color:#facc15;">/ CAR</code> (contoh: <strong>17/ CAR</strong> pada unit <strong>DE</strong>).
            Data tersebut otomatis disimpan ke unit yang sama (Unit 17 DE) sebagai fasilitas <strong>EV Charger</strong> dengan nomor meter dan daya terpisah, tanpa membuat unit duplikat.
        </div>
    </div>

    <!-- Form Upload / Paste -->
    <form action="{{ route('billing.energy.bulk.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:24px;">
            <!-- Pilihan Periode -->
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px;">
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:14px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    📅 <span>1. Periode Pencatatan</span>
                </h3>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:0.85rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Tahun Target <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="periode_tahun" class="form-control" style="width:100%;">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:0.85rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Bulan Target <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="periode_bulan" class="form-control" style="width:100%;">
                        @php
                            $namaBulanList = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        @foreach($namaBulanList as $num => $nama)
                            <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $num }} - {{ $nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label style="font-size:0.85rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Tanggal Pencatatan Resmi <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="date" name="tanggal_catat" class="form-control" style="width:100%;" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>

            <!-- Upload File File CSV / Excel -->
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px;">
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:14px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    📁 <span>2. Unggah File CSV / Excel</span>
                </h3>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:0.85rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Pilih File (.csv, .txt, .xls, .xlsx)
                    </label>
                    <input type="file" name="file_csv" class="form-control" accept=".csv,.txt,.xls,.xlsx" style="width:100%; padding:8px;">
                    <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:6px;">
                        Maksimal ukuran file: 10MB. Format baris sesuai file rekapitulasi Residences bulanan.
                    </span>
                </div>

                <div style="border-top:1px dashed var(--border-color); padding-top:14px; margin-top:14px;">
                    <span style="font-size:0.8rem; color:var(--text-secondary);">
                        💡 Anda juga dapat mengunduh format template yang sudah terisi data stand awal bulan sebelumnya agar tinggal mengisi stand akhir:
                    </span>
                    <div style="margin-top:8px;">
                        <a href="{{ route('billing.energy.bulk.template', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                            📥 Unduh Template CSV Sesuai Master Tenant
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opsi Tempel Teks CSV / Copy Paste dari Excel -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px; margin-bottom:24px;">
            <h3 style="font-size:1rem; font-weight:700; margin-bottom:10px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                📋 <span>3. Atau Tempel / Paste Teks Data (Copy Langsung dari Excel / CSV)</span>
            </h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:12px;">
                Jika tidak ingin mengunggah file, Anda bisa langsung copy baris dari Excel atau CSV lalu paste ke kolom di bawah ini:
            </p>

            <textarea name="raw_csv" rows="10" class="form-control" style="width:100%; font-family:monospace; font-size:0.82rem; line-height:1.5; padding:12px;" placeholder="Contoh format yang didukung:
1,12,A1,21.12,99222.85,99758.42,535.57
19,17,DE,33,250875.7,255316.6,4440.9
20,17/ CAR ,DE,33,2628.43,2851.1,222.67
..."></textarea>
        </div>

        <!-- Tombol Submit -->
        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="{{ route('billing.energy.index', ['year' => $year, 'month' => $month]) }}" class="btn">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px; font-weight:700;">
                ⚡ Proses &amp; Simpan Data Bulk Listrik
            </button>
        </div>
    </form>
</div>
@endsection
