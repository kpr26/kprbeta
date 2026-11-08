<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Bukti Fisik Meteran - {{ $namaBulan }} {{ $year }}</title>
    <style>
        :root {
            --primary: #2563eb;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #cbd5e1;
            --bg-light: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            background: #e2e8f0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Top Action Bar (Hidden on Print) */
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            background: #ffffff;
            padding: 14px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-outline {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-outline:hover {
            background: #f1f5f9;
        }

        /* Printable Sheet */
        .sheet {
            max-width: 900px;
            margin: 0 auto 28px auto;
            background: #ffffff;
            padding: 36px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            page-break-after: always;
        }

        .sheet:last-child {
            page-break-after: avoid;
        }

        /* Header Document */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .doc-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .doc-meta {
            text-align: right;
            font-size: 12px;
        }

        .doc-meta .badge-period {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
            display: inline-block;
            margin-bottom: 4px;
        }

        /* Tenant Details Table */
        .tenant-box {
            background: var(--bg-light);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .tenant-field {
            display: flex;
            flex-direction: column;
        }

        .tenant-field .label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .tenant-field .value {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-dark);
            margin-top: 2px;
        }

        /* Meters Grid */
        .meters-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 24px;
        }

        .meters-grid.cols-2 {
            grid-template-columns: 1fr 1fr;
        }

        .meters-grid.cols-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .meter-card {
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
            display: flex;
            flex-direction: column;
        }

        .meter-card-header {
            padding: 10px 14px;
            font-weight: 700;
            font-size: 12.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .meter-card-header.energy {
            background: #fefce8;
            color: #854d0e;
            border-bottom: 1px solid #fef08a;
        }

        .meter-card-header.ev {
            background: #f0fdf4;
            color: #166534;
            border-bottom: 1px solid #bbf7d0;
        }

        .meter-card-header.water {
            background: #ecfeff;
            color: #155e75;
            border-bottom: 1px solid #a5f3fc;
        }

        .meter-photo-container {
            width: 100%;
            height: 240px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .meter-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .meter-photo-empty {
            color: var(--text-muted);
            font-size: 12px;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }

        .meter-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .meter-table td {
            padding: 6px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .meter-table tr:last-child td {
            border-bottom: none;
        }

        .meter-table td.td-label {
            color: var(--text-muted);
            width: 45%;
        }

        .meter-table td.td-val {
            font-weight: 700;
            color: var(--text-dark);
            text-align: right;
            font-family: monospace;
            font-size: 12px;
        }

        .keterangan-alert {
            margin-top: 8px;
            padding: 6px 10px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 4px;
            color: #9f1239;
            font-size: 11px;
            font-weight: 600;
        }

        /* Signatures Section */
        .signature-section {
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            text-align: center;
        }

        .sign-box p {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-bottom: 50px;
        }

        .sign-box .sign-line {
            border-bottom: 1px solid #0f172a;
            margin: 0 20px;
            padding-bottom: 4px;
            font-weight: 700;
            font-size: 12px;
        }

        .sign-box .sign-sub {
            font-size: 10.5px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .sheet {
                box-shadow: none;
                border-radius: 0;
                padding: 20px 24px;
                margin: 0;
                max-width: 100%;
            }

            .meter-photo-container {
                height: 220px;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Aksi Atas (Hanya tampil di layar) -->
    <div class="no-print-bar">
        <div>
            <strong style="font-size:14px; color:#0f172a;">Pratinjau Lembar Bukti Foto Meteran</strong>
            <div style="font-size:12px; color:#64748b;">
                Periode: <strong>{{ $namaBulan }} {{ $year }}</strong> &bull; Total Tenant: {{ $tenants->count() }}
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Cetak / Simpan PDF
            </button>
            <a href="{{ route('billing.photos', ['year' => $year, 'month' => $month]) }}" class="btn btn-outline">
                &larr; Kembali ke Galeri
            </a>
        </div>
    </div>

    @forelse($tenants as $t)
        @php
            $tEnergies = $energyReadings->get($t->id) ?? collect();
            $regE = $tEnergies->firstWhere('meter_type', 'regular') ?? $tEnergies->where('meter_type', '!=', 'ev')->first();
            $evE  = $tEnergies->firstWhere('meter_type', 'ev');
            $w    = $waterReadings->get($t->id);
            $petugasName = $regE?->user?->name ?? $evE?->user?->name ?? $w?->user?->name ?? 'Teknisi Engineering';
            $tglCatat    = $regE?->tanggal_catat?->format('d/m/Y') ?? $w?->tanggal_catat?->format('d/m/Y') ?? now()->format('d/m/Y');
        @endphp

        <div class="sheet">
            <!-- Header Dokumen -->
            <div class="doc-header">
                <div class="doc-title">
                    <h1>Lembar Bukti Fisik Meteran Tenant</h1>
                    <p>Building Management Engineering &bull; Billing System Utility Verification</p>
                </div>
                <div class="doc-meta">
                    <span class="badge-period">Periode: {{ $namaBulan }} {{ $year }}</span>
                    <div>Tgl Cetak: {{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <!-- Rincian Identitas Tenant -->
            <div class="tenant-box">
                <div class="tenant-field">
                    <span class="label">Nama Tenant</span>
                    <span class="value">{{ $t->nama_tenant }}</span>
                </div>
                <div class="tenant-field">
                    <span class="label">Unit / Lokasi</span>
                    <span class="value">{{ $t->unit_label }}</span>
                </div>
                <div class="tenant-field">
                    <span class="label">No. Meter Listrik</span>
                    <span class="value">{{ $t->nomor_meter_listrik ?: '—' }}</span>
                </div>
                <div class="tenant-field">
                    <span class="label">No. Meter Air</span>
                    <span class="value">{{ $t->nomor_meter_air ?: '—' }}</span>
                </div>
            </div>

            <!-- Grid Foto & Data Meteran -->
            <div class="meters-grid {{ $t->has_ev_charger ? 'cols-3' : 'cols-2' }}">
                
                <!-- 1. Meter Listrik Reguler -->
                <div class="meter-card">
                    <div class="meter-card-header energy">
                        <span>⚡ Listrik Reguler</span>
                        <span>{{ $t->daya_listrik ? number_format($t->daya_listrik, 0, ',', '.') . ' kVA' : 'Reguler' }}</span>
                    </div>
                    <div class="meter-photo-container">
                        @if($regE && $regE->foto_url)
                            <img src="{{ $regE->foto_url }}" alt="Foto Meter Listrik Reguler">
                        @else
                            <div class="meter-photo-empty">
                                Tidak ada lampiran foto fisik meter listrik reguler
                            </div>
                        @endif
                    </div>
                    <table class="meter-table">
                        <tr>
                            <td class="td-label">Stand Awal</td>
                            <td class="td-val">{{ $regE && $regE->stand_awal !== null ? number_format($regE->stand_awal, 2, ',', '.') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="td-label">Stand Akhir</td>
                            <td class="td-val" style="color:#2563eb;">{{ $regE && $regE->stand_akhir !== null ? number_format($regE->stand_akhir, 2, ',', '.') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="td-label">Pemakaian</td>
                            <td class="td-val">{{ $regE && $regE->pemakaian !== null ? number_format($regE->pemakaian, 2, ',', '.') . ' kWh' : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="td-label">Petugas / Tgl</td>
                            <td class="td-val" style="font-family:inherit; font-size:11px;">
                                {{ $regE?->user?->name ?? '—' }} ({{ $regE?->tanggal_catat?->format('d/m/Y') ?? '—' }})
                            </td>
                        </tr>
                    </table>
                    @if($regE && $regE->keterangan)
                        <div style="padding:0 12px 10px 12px;">
                            <div class="keterangan-alert">
                                ⚠️ Catatan: {{ $regE->keterangan }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 2. Meter Listrik EV Charger (Jika ada) -->
                @if($t->has_ev_charger)
                    <div class="meter-card">
                        <div class="meter-card-header ev">
                            <span>🚗⚡ Listrik EV Charger</span>
                            <span>{{ $t->daya_ev ? number_format($t->daya_ev, 0, ',', '.') . ' kVA' : 'EV' }}</span>
                        </div>
                        <div class="meter-photo-container">
                            @if($evE && $evE->foto_url)
                                <img src="{{ $evE->foto_url }}" alt="Foto Meter EV Charger">
                            @else
                                <div class="meter-photo-empty">
                                    Tidak ada lampiran foto fisik meter EV charger
                                </div>
                            @endif
                        </div>
                        <table class="meter-table">
                            <tr>
                                <td class="td-label">Stand Awal</td>
                                <td class="td-val">{{ $evE && $evE->stand_awal !== null ? number_format($evE->stand_awal, 2, ',', '.') : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="td-label">Stand Akhir</td>
                                <td class="td-val" style="color:#16a34a;">{{ $evE && $evE->stand_akhir !== null ? number_format($evE->stand_akhir, 2, ',', '.') : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="td-label">Pemakaian</td>
                                <td class="td-val">{{ $evE && $evE->pemakaian !== null ? number_format($evE->pemakaian, 2, ',', '.') . ' kWh' : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="td-label">Petugas / Tgl</td>
                                <td class="td-val" style="font-family:inherit; font-size:11px;">
                                    {{ $evE?->user?->name ?? '—' }} ({{ $evE?->tanggal_catat?->format('d/m/Y') ?? '—' }})
                                </td>
                            </tr>
                        </table>
                        @if($evE && $evE->keterangan)
                            <div style="padding:0 12px 10px 12px;">
                                <div class="keterangan-alert">
                                    ⚠️ Catatan: {{ $evE->keterangan }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- 3. Water Meter -->
                <div class="meter-card">
                    <div class="meter-card-header water">
                        <span>💧 Water Meter</span>
                        <span>m³</span>
                    </div>
                    <div class="meter-photo-container">
                        @if($w && $w->foto_url)
                            <img src="{{ $w->foto_url }}" alt="Foto Water Meter">
                        @else
                            <div class="meter-photo-empty">
                                Tidak ada lampiran foto fisik water meter
                            </div>
                        @endif
                    </div>
                    <table class="meter-table">
                        <tr>
                            <td class="td-label">Stand Awal</td>
                            <td class="td-val">{{ $w && $w->stand_awal !== null ? number_format($w->stand_awal, 3, ',', '.') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="td-label">Stand Akhir</td>
                            <td class="td-val" style="color:#0891b2;">{{ $w && $w->stand_akhir !== null ? number_format($w->stand_akhir, 3, ',', '.') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="td-label">Pemakaian</td>
                            <td class="td-val">{{ $w && $w->pemakaian !== null ? number_format($w->pemakaian, 3, ',', '.') . ' m³' : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="td-label">Petugas / Tgl</td>
                            <td class="td-val" style="font-family:inherit; font-size:11px;">
                                {{ $w?->user?->name ?? '—' }} ({{ $w?->tanggal_catat?->format('d/m/Y') ?? '—' }})
                            </td>
                        </tr>
                    </table>
                    @if($w && $w->keterangan)
                        <div style="padding:0 12px 10px 12px;">
                            <div class="keterangan-alert">
                                ⚠️ Catatan: {{ $w->keterangan }}
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Tanda Tangan Bukti Validasi -->
            <div class="signature-section">
                <div class="sign-box">
                    <p>Petugas Pencatat (Teknisi),</p>
                    <div class="sign-line">{{ $petugasName }}</div>
                    <div class="sign-sub">Tgl: {{ $tglCatat }}</div>
                </div>
                <div class="sign-box">
                    <p>Supervisor Engineering,</p>
                    <div class="sign-line">( ........................................ )</div>
                    <div class="sign-sub">Building Management</div>
                </div>
                <div class="sign-box">
                    <p>Perwakilan Tenant,</p>
                    <div class="sign-line">( ........................................ )</div>
                    <div class="sign-sub">{{ $t->nama_tenant }}</div>
                </div>
            </div>
        </div>
    @empty
        <div class="sheet" style="text-align:center; padding:60px 20px;">
            <h2>Data Tenant Tidak Ditemukan</h2>
            <p style="color:#64748b; margin-top:8px;">Tidak ada data tenant yang dapat dicetak untuk periode ini.</p>
        </div>
    @endforelse

</body>
</html>
