@extends('layouts.panel')

@section('title', 'Input Bulk Manual Air - Billing System')
@section('page-title', 'Input Bulk Manual Air Tenant')
@section('page-subtitle', 'Import & Pencatatan Massal Stand Water Meter Khusus Super Admin')

@section('panel')
<div class="panel">
    <!-- Header -->
    <div class="panel-head">
        <div>
            <h2>Import Massal / Input Bulk Manual Data Penggunaan Air</h2>
            <p>Fitur khusus Super Admin untuk memasukkan data pembacaan water meter bulanan sekaligus dari file CSV / Excel sesuai format template bulanan.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('billing.water.index', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                &larr; Kembali ke Daftar Air
            </a>
            <a href="{{ route('billing.water.bulk.template', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#06b6d4; font-weight:600;">
                📥 Unduh Template CSV (Sudah Terisi Stand Awal)
            </a>
        </div>
    </div>

    <!-- Info Box: Presisi & Penanganan Stand Khusus / Terkendala -->
    <div style="background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.25); border-radius:var(--radius-md); padding:16px 20px; margin:20px 0; font-size:0.88rem; line-height:1.6;">
        <div style="display:flex; align-items:center; gap:10px; font-weight:700; color:#06b6d4; margin-bottom:6px;">
            <span style="font-size:1.2rem;">💧⚙️</span>
            <span>Presisi 3 Desimal &amp; Penanganan Otomatis Stand Mundur / Segel / Error Meter</span>
        </div>
        <div style="color:var(--text-secondary);">
            Sistem mendukung angka desimal hingga <strong>3 digit (liter)</strong> sesuai pembacaan meteran air (contoh: <code>371.829</code> m³).<br>
            Jika terdapat unit dengan kondisi khusus seperti <strong>meter segel</strong>, <strong>meter diganti baru</strong>, atau <strong>stand mundur/rollover</strong> (stand akhir &lt; stand awal), sistem otomatis menandai status <strong>Terkendala</strong> dan menyematkan catatan pada laporan export.
        </div>
    </div>

    <!-- Form Upload / Paste -->
    <form action="{{ route('billing.water.bulk.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:24px;">
            <!-- Pilihan Periode -->
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px;">
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:14px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    📅 <span>1. Periode Pencatatan Air</span>
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
                        Maksimal ukuran file: 10MB. Format baris sesuai file rekapitulasi Residences bulanan (No, Floor, Unit, Awal, Akhir, Pemakaian, Keterangan).
                    </span>
                </div>

                <div style="padding:12px; background:rgba(255,255,255,0.03); border:1px dashed var(--border-color); border-radius:var(--radius-sm); font-size:0.8rem; color:var(--text-secondary);">
                    💡 <em>Tips:</em> Anda dapat langsung mengunggah file CSV rekapitulasi bulanan, atau unduh template terlebih dahulu untuk melengkapi kolom Stand Akhir.
                </div>
            </div>
        </div>

        <!-- Opsi Copy-Paste Text Area -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px; margin-bottom:24px;">
            <h3 style="font-size:1rem; font-weight:700; margin-bottom:8px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                📋 <span>3. Atau Tempel (Copy-Paste) Teks Data Excel / CSV</span>
            </h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:14px;">
                Blok baris data dari Microsoft Excel atau Google Sheets, lalu salin (Ctrl+C) dan tempel (Ctrl+V) langsung ke dalam kotak di bawah ini:
            </p>

            <textarea name="raw_csv" rows="9" class="form-control" style="width:100%; font-family:monospace; font-size:0.85rem; line-height:1.5; white-space:pre;" placeholder="Contoh format:
No	Floor	Unit	Awal	Akhir	Pemakaian	Keterangan
1	12	A1	371.829	375.535	3.706	
2	15	A1	4290.267	4290.369	0.102	
3	19	D	1798.736	1798.736	0	Segel"></textarea>
        </div>

        <!-- Tombol Eksekusi -->
        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="{{ route('billing.water.index', ['year' => $year, 'month' => $month]) }}" class="btn">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="font-weight:700; padding:10px 24px;">
                🚀 Proses &amp; Simpan Data Bulk Air
            </button>
        </div>
    </form>
</div>
@endsection
