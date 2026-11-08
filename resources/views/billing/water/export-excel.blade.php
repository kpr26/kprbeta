<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RESIDENCES METER READING WATER - {{ strtoupper($namaBulan) }} {{ $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .title-block { font-size: 13px; font-weight: bold; margin-bottom: 15px; }
        .total-row { background-color: #f9f9f9; font-weight: bold; }
        .special-row { background-color: #fffbeb; }
        .badge-special { color: #b45309; font-weight: bold; }
        .signature-table td { border: none; text-align: center; padding: 15px 5px; }
    </style>
</head>
<body>

    <div class="title-block">
        RESIDENCES METER READING WATER<br>
        BULAN : {{ strtoupper($namaBulan) }} {{ $year }}
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 40px;">No</th>
                <th rowspan="2" style="width: 60px;">Floor</th>
                <th rowspan="2" style="width: 70px;">Unit</th>
                <th colspan="3">AIR (m³)</th>
                <th rowspan="2" style="width: 140px;">Keterangan</th>
                <th style="border:none; width: 30px;"></th>
                <th colspan="2" style="background-color: #e0f2fe; width: 160px;">Daftar EV Charging Tenant</th>
            </tr>
            <tr>
                <th style="width: 85px;">Awal</th>
                <th style="width: 85px;">Akhir</th>
                <th style="width: 85px;">Pemakaian</th>
                <th style="border:none;"></th>
                <th style="width: 40px; background-color: #e0f2fe;">No.</th>
                <th style="width: 120px; background-color: #e0f2fe;">Unit</th>
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

    <br>

    <!-- Tanda Tangan Resmi -->
    <table class="signature-table" style="width: 70%; margin-top: 20px;">
        <tr>
            <td style="width: 50%;">Proposed by,</td>
            <td style="width: 50%;">Approved by,</td>
        </tr>
        <tr>
            <td style="height: 60px;"></td>
            <td style="height: 60px;"></td>
        </tr>
        <tr>
            <td class="bold" style="text-decoration: underline;">Frida</td>
            <td class="bold" style="text-decoration: underline;">Triyoto</td>
        </tr>
        <tr>
            <td>Admin Engineering</td>
            <td>Chief Engineering</td>
        </tr>
    </table>

</body>
</html>
