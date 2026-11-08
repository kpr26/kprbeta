<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RESIDENCES METER READING ELECTRICITY - {{ strtoupper($namaBulan) }} {{ $year }}</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px 6px; font-size: 10pt; }
        .no-border { border: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .header-bg { background-color: #d9e1f2; font-weight: bold; }
        .subhead-bg { background-color: #f2f2f2; font-weight: bold; }
        .ev-bg { background-color: #e2efda; }
        .total-bg { background-color: #fff2cc; font-weight: bold; }
        .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
        .num-int { mso-number-format: "\#\,\#\#0"; text-align: right; }
        .txt { mso-number-format: "\@"; text-align: center; }
    </style>
</head>
<body>

<table>
    <!-- Judul Laporan -->
    <tr>
        <td colspan="15" class="no-border font-bold" style="font-size:14pt;">
            RESIDENCES METER READING ELECTRICITY &amp; WATER
        </td>
        <td colspan="4" class="no-border font-bold text-center" style="background:#e2efda;">
            Daftar EV Charging Tenant
        </td>
    </tr>
    <tr>
        <td colspan="15" class="no-border font-bold" style="font-size:12pt; color:#1f4e78;">
            BULAN   : {{ strtoupper($namaBulan) }} {{ $year }}
        </td>
        <td class="font-bold text-center" style="background:#d9e1f2; width:40px;">No.</td>
        <td colspan="3" class="font-bold text-center" style="background:#d9e1f2;">Unit</td>
    </tr>
    <tr>
        <td colspan="19" class="no-border" style="height:10px;"></td>
    </tr>

    <!-- Table Header Kolom -->
    <tr class="header-bg text-center">
        <th rowspan="2" style="width:40px;">No</th>
        <th rowspan="2" style="width:70px;">Floor</th>
        <th rowspan="2" style="width:60px;">Unit</th>
        <th colspan="11">LISTRIK (TARIF 1.699,53)</th>
        <th rowspan="2" style="width:130px;">Keterangan</th>
        <th colspan="4" class="no-border" style="background:#fff;"></th>
    </tr>
    <tr class="header-bg text-center">
        <th style="width:65px;">Daya</th>
        <th style="width:85px;">Awal</th>
        <th style="width:85px;">Akhir</th>
        <th style="width:80px;">Pemakaian</th>
        <th style="width:90px;">Min Hour (40 Jam)</th>
        <th style="width:110px;">Min Charge (40 Jam)</th>
        <th style="width:110px;">Actual Charge</th>
        <th style="width:110px;">Billing</th>
        <th style="width:95px;">PPJ (2,40%)</th>
        <th style="width:85px;">Biaya Materai</th>
        <th style="width:120px;">Total Tagihan</th>
        <th colspan="4" class="no-border" style="background:#fff;"></th>
    </tr>

    <!-- Baris Data -->
    @php
        $evCount = count($evList);
    @endphp

    @foreach($rows as $index => $r)
        <tr @if($r->is_ev) class="ev-bg" @endif>
            <td class="text-center">{{ $r->no }}</td>
            <td class="txt font-bold">{{ $r->floor }}</td>
            <td class="txt font-bold">{{ $r->unit }}</td>
            <td class="num">{{ number_format($r->daya, 2, '.', '') }}</td>
            <td class="num">{{ number_format($r->awal, 2, '.', '') }}</td>
            <td class="num">{{ number_format($r->akhir, 2, '.', '') }}</td>
            <td class="num font-bold">{{ number_format($r->pemakaian, 2, '.', '') }}</td>
            <td class="num">{{ number_format($r->min_hour, 2, '.', '') }}</td>
            <td class="num">{{ number_format($r->min_charge, 2, '.', '') }}</td>
            <td class="num">{{ number_format($r->actual_charge, 2, '.', '') }}</td>
            <td class="num font-bold">{{ number_format($r->billing, 2, '.', '') }}</td>
            <td class="num">{{ number_format($r->ppj, 2, '.', '') }}</td>
            <td class="num-int">{{ number_format($r->materai, 0, '.', '') }}</td>
            <td class="num font-bold" style="color:#0f5132;">{{ number_format($r->total_tagihan, 2, '.', '') }}</td>
            <td class="text-left" style="font-size:9pt; font-style:italic;">{{ $r->keterangan }}</td>

            <!-- Kolom Sisi Kanan: Daftar EV Charging Tenant -->
            @if($index < $evCount)
                <td class="text-center font-bold" style="background:#f2f2f2;">{{ $index + 1 }}</td>
                <td colspan="3" class="text-center font-bold" style="background:#f9f9f9;">{{ $evList[$index]->unit_label }}</td>
            @else
                <td colspan="4" class="no-border"></td>
            @endif
        </tr>
    @endforeach

    <!-- Baris Total -->
    <tr class="total-bg">
        <td colspan="8" class="text-center font-bold" style="font-size:11pt;">T O T A L</td>
        <td class="num font-bold">{{ number_format($totals['min_charge'], 2, '.', '') }}</td>
        <td class="num font-bold">{{ number_format($totals['actual_charge'], 2, '.', '') }}</td>
        <td class="num font-bold">{{ number_format($totals['billing'], 2, '.', '') }}</td>
        <td class="num font-bold">{{ number_format($totals['ppj'], 2, '.', '') }}</td>
        <td class="num-int font-bold">{{ number_format($totals['materai'], 0, '.', '') }}</td>
        <td class="num font-bold" style="font-size:11pt;">{{ number_format($totals['total_tagihan'], 2, '.', '') }}</td>
        <td></td>
        <td colspan="4" class="no-border"></td>
    </tr>

    <!-- Baris Selisih & Persentase -->
    <tr>
        <td colspan="8" class="text-right font-bold no-border">SELISIH</td>
        <td colspan="2" class="num font-bold" style="background:#e2efda;">{{ number_format($totals['selisih'], 2, '.', '') }}</td>
        <td colspan="5" class="no-border"></td>
        <td colspan="4" class="no-border"></td>
    </tr>
    <tr>
        <td colspan="8" class="text-right font-bold no-border">(%)</td>
        <td class="text-center font-bold" style="background:#fce4d6;">{{ number_format($totals['pct_minus'], 2, '.', '') }}%</td>
        <td class="text-center font-bold" style="background:#e2efda;">{{ number_format($totals['pct_plus'], 2, '.', '') }}%</td>
        <td colspan="5" class="no-border"></td>
        <td colspan="4" class="no-border"></td>
    </tr>

    <!-- Spacing -->
    <tr><td colspan="19" class="no-border" style="height:25px;"></td></tr>

    <!-- Tanda Tangan -->
    <tr>
        <td colspan="2" class="no-border"></td>
        <td colspan="4" class="no-border font-bold text-center">Proposed by,</td>
        <td colspan="4" class="no-border"></td>
        <td colspan="4" class="no-border font-bold text-center">Approved by,</td>
        <td colspan="5" class="no-border"></td>
    </tr>
    <tr><td colspan="19" class="no-border" style="height:50px;"></td></tr>
    <tr>
        <td colspan="2" class="no-border"></td>
        <td colspan="4" class="no-border font-bold text-center" style="border-bottom:1px solid #000 !important;">Frida</td>
        <td colspan="4" class="no-border"></td>
        <td colspan="4" class="no-border font-bold text-center" style="border-bottom:1px solid #000 !important;">Triyoto</td>
        <td colspan="5" class="no-border"></td>
    </tr>
    <tr>
        <td colspan="2" class="no-border"></td>
        <td colspan="4" class="no-border text-center" style="font-size:9.5pt;">Admin Engineering</td>
        <td colspan="4" class="no-border"></td>
        <td colspan="4" class="no-border text-center" style="font-size:9.5pt;">Chief Engineering</td>
        <td colspan="5" class="no-border"></td>
    </tr>
</table>

</body>
</html>
