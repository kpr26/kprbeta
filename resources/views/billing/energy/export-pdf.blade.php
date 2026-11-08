<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESIDENCES METER READING ELECTRICITY - {{ strtoupper($namaBulan) }} {{ $year }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5px; color: #1e293b; margin: 0; padding: 0; line-height: 1.3; }
        .no-print { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 12px; }
        .btn { padding: 6px 14px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 11px; }
        .btn-primary { background: #0284c7; color: #fff; }
        .btn-secondary { background: #64748b; color: #fff; }
        .title-area { margin-bottom: 10px; }
        .title-area h1 { margin: 0 0 2px; font-size: 13px; text-transform: uppercase; color: #0f172a; font-weight: 800; }
        .title-area h2 { margin: 0; font-size: 11px; color: #1e40af; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #94a3b8; padding: 3px 4px; font-size: 8px; }
        th { background-color: #e2e8f0; font-weight: 700; text-align: center; }
        .ev-row { background-color: #ecfdf5; }
        .num { text-align: right; font-family: 'Courier New', monospace; white-space: nowrap; }
        .center { text-align: center; }
        .total-row { background-color: #fef08a; font-weight: bold; }
        .sign-area { margin-top: 25px; display: flex; justify-content: space-around; page-break-inside: avoid; }
        .sign-box { text-align: center; width: 180px; }
        .sign-space { height: 45px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <div class="title-area">
        <h1>RESIDENCES METER READING ELECTRICITY &amp; WATER</h1>
        <h2>BULAN : {{ strtoupper($namaBulan) }} {{ $year }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:25px;">No</th>
                <th rowspan="2" style="width:45px;">Floor</th>
                <th rowspan="2" style="width:35px;">Unit</th>
                <th colspan="11">LISTRIK (TARIF 1.699,53)</th>
                <th rowspan="2" style="width:90px;">Keterangan</th>
            </tr>
            <tr>
                <th style="width:40px;">Daya</th>
                <th style="width:55px;">Awal</th>
                <th style="width:55px;">Akhir</th>
                <th style="width:50px;">Pemakaian</th>
                <th style="width:55px;">Min Hour</th>
                <th style="width:65px;">Min Charge</th>
                <th style="width:65px;">Actual Charge</th>
                <th style="width:65px;">Billing</th>
                <th style="width:55px;">PPJ (2,4%)</th>
                <th style="width:45px;">Materai</th>
                <th style="width:70px;">Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr @if($r->is_ev) class="ev-row" @endif>
                    <td class="center">{{ $r->no }}</td>
                    <td class="center" style="font-weight:bold;">{{ $r->floor }}</td>
                    <td class="center" style="font-weight:bold;">{{ $r->unit }}</td>
                    <td class="num">{{ number_format($r->daya, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->awal, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->akhir, 2, ',', '.') }}</td>
                    <td class="num" style="font-weight:bold;">{{ number_format($r->pemakaian, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->min_hour, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->min_charge, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->actual_charge, 2, ',', '.') }}</td>
                    <td class="num" style="font-weight:bold;">{{ number_format($r->billing, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->ppj, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->materai, 0, ',', '.') }}</td>
                    <td class="num" style="font-weight:bold; color:#047857;">{{ number_format($r->total_tagihan, 2, ',', '.') }}</td>
                    <td style="font-size:7.5px;">{{ $r->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="8" class="center">T O T A L</td>
                <td class="num">{{ number_format($totals['min_charge'], 2, ',', '.') }}</td>
                <td class="num">{{ number_format($totals['actual_charge'], 2, ',', '.') }}</td>
                <td class="num">{{ number_format($totals['billing'], 2, ',', '.') }}</td>
                <td class="num">{{ number_format($totals['ppj'], 2, ',', '.') }}</td>
                <td class="num">{{ number_format($totals['materai'], 0, ',', '.') }}</td>
                <td class="num">{{ number_format($totals['total_tagihan'], 2, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:right; font-weight:bold;">SELISIH</td>
                <td colspan="2" class="num" style="font-weight:bold; background:#dcfce7;">{{ number_format($totals['selisih'], 2, ',', '.') }}</td>
                <td colspan="5"></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:right; font-weight:bold;">(%)</td>
                <td class="center" style="font-weight:bold; background:#fee2e2;">{{ number_format($totals['pct_minus'], 2, ',', '.') }}%</td>
                <td class="center" style="font-weight:bold; background:#dcfce7;">{{ number_format($totals['pct_plus'], 2, ',', '.') }}%</td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan -->
    <div class="sign-area">
        <div class="sign-box">
            <div>Proposed by,</div>
            <div class="sign-space"></div>
            <div><strong>Frida</strong></div>
            <div style="font-size:7.5px; color:#64748b;">Admin Engineering</div>
        </div>
        <div class="sign-box">
            <div>Approved by,</div>
            <div class="sign-space"></div>
            <div><strong>Triyoto</strong></div>
            <div style="font-size:7.5px; color:#64748b;">Chief Engineering</div>
        </div>
    </div>
</body>
</html>
