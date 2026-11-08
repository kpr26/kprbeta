<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RESIDENCES METER READING WATER - {{ strtoupper($namaBulan) }} {{ $year }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
            background: #fff;
        }
        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
        }
        .header h1 {
            font-size: 14px;
            margin: 0 0 2px 0;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .meta {
            font-size: 11px;
            font-weight: 700;
            color: #0284c7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9.5px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
            color: #334155;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: 700; }
        .total-row {
            background-color: #f8fafc;
            font-weight: 700;
        }
        .special-row {
            background-color: #fef3c7;
        }
        .badge-special {
            color: #b45309;
            font-weight: 700;
            font-size: 8.5px;
        }
        .signatures {
            margin-top: 15px;
            width: 80%;
            page-break-inside: avoid;
        }
        .signatures td {
            border: none;
            text-align: center;
            padding: 4px;
        }
        .action-bar {
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            background-color: #0284c7;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        @media print {
            .action-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button class="btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <a href="{{ route('billing.water.index', ['year' => $year, 'month' => $month]) }}" class="btn" style="background:#64748b;">
            &larr; Kembali ke Sistem
        </a>
        <span style="font-size: 10px; color: #64748b; margin-left: auto;">
            Tip: Pilih layout "Portrait" dan centang "Background graphics" saat mencetak.
        </span>
    </div>

    <div class="header">
        <h1>RESIDENCES METER READING WATER</h1>
        <div class="meta">BULAN : {{ strtoupper($namaBulan) }} {{ $year }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">No</th>
                <th rowspan="2" style="width: 45px;">Floor</th>
                <th rowspan="2" style="width: 55px;">Unit</th>
                <th colspan="3">AIR (m³)</th>
                <th rowspan="2" style="width: 120px;">Keterangan</th>
                <th style="border:none; width: 15px;"></th>
                <th colspan="2" style="background-color: #e0f2fe; width: 140px;">Daftar EV Charging Tenant</th>
            </tr>
            <tr>
                <th style="width: 75px;">Awal</th>
                <th style="width: 75px;">Akhir</th>
                <th style="width: 75px;">Pemakaian</th>
                <th style="border:none;"></th>
                <th style="width: 30px; background-color: #e0f2fe;">No.</th>
                <th style="width: 110px; background-color: #e0f2fe;">Unit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $evCount = count($evTenants);
            @endphp
            @foreach($rows as $index => $r)
                @php
                    $isSpecial = $r->is_special;
                    $evItem = $index < $evCount ? $evTenants[$index] : null;
                @endphp
                <tr class="{{ $isSpecial ? 'special-row' : '' }}">
                    <td class="text-center">{{ $r->no }}</td>
                    <td class="text-center">{{ $r->floor }}</td>
                    <td class="text-center bold">{{ $r->unit }}</td>
                    <td class="text-right">{{ $r->awal }}</td>
                    <td class="text-right">{{ $r->akhir }}</td>
                    <td class="text-right bold" style="{{ $isSpecial ? 'color:#b45309;' : '' }}">{{ $r->pemakaian }}</td>
                    <td style="{{ $isSpecial ? 'color:#b45309; font-weight:bold;' : '' }}">
                        {{ $r->keterangan ?: ($isSpecial ? 'Stand Mundur/Kendala' : '') }}
                    </td>
                    <td style="border:none;"></td>
                    @if($evItem)
                        <td class="text-center" style="background-color: #f8fafc;">{{ $index + 1 }}</td>
                        <td class="text-center bold" style="background-color: #f8fafc;">{{ $evItem->unit_label }}</td>
                    @else
                        <td style="border:none;"></td>
                        <td style="border:none;"></td>
                    @endif
                </tr>
            @endforeach

            <!-- Baris Total -->
            <tr class="total-row">
                <td colspan="3" class="text-center bold">T O T A L</td>
                <td class="text-right bold">{{ $totalAwal }}</td>
                <td class="text-right bold">{{ $totalAkhir }}</td>
                <td class="text-right bold" style="color:#0284c7;">{{ $totalPemakaian }}</td>
                <td class="text-center">
                    @if($countTerkendala > 0)
                        <span class="badge-special">{{ $countTerkendala }} Unit Terkendala</span>
                    @endif
                </td>
                <td style="border:none;"></td>
                <td style="border:none;"></td>
                <td style="border:none;"></td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan Resmi -->
    <table class="signatures">
        <tr>
            <td style="width: 50%;">Proposed by,</td>
            <td style="width: 50%;">Approved by,</td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td style="height: 50px;"></td>
        </tr>
        <tr>
            <td class="bold" style="text-decoration: underline;">Frida</td>
            <td class="bold" style="text-decoration: underline;">Triyoto</td>
        </tr>
        <tr>
            <td style="color:#64748b;">Admin Engineering</td>
            <td style="color:#64748b;">Chief Engineering</td>
        </tr>
    </table>

</body>
</html>
