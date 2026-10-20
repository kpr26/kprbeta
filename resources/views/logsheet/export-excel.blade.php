@php
    $n = fn ($v) => $v === null ? '' : number_format($v, 2, ',', '.');
    $judul = 'REKAPITULASI LOGSHEET AMR PLN T33N & AIR BERSIH — ' . strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y'));
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 5px 8px; font-family: Calibri, Arial; font-size: 10pt; }
    th { background: #1e293b; color: #fff; font-weight: bold; text-align: center; }
    th.g-elec { background: #1e3a8a; }
    th.g-water { background: #0369a1; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .tot td { font-weight: bold; background: #fef3c7; }
</style>
</head>
<body>
<table>
    <tr><td colspan="15" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="15" style="border:0;font-size:10pt;">Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="15" style="border:0"></td></tr>
    <tr>
        <th rowspan="2">No</th>
        <th rowspan="2">Tanggal</th>
        <th colspan="7" class="g-elec">AMR PLN T33N (kWh)</th>
        <th rowspan="2" class="g-elec">Total Listrik (kWh)</th>
        <th colspan="3" class="g-water">Air Bersih (m³)</th>
        <th rowspan="2">Teknisi</th>
        <th rowspan="2">Status</th>
    </tr>
    <tr>
        <th>Stand BP</th>
        <th>Pakai BP</th>
        <th>Total BP (Akum.)</th>
        <th>Stand LBP 1</th>
        <th>Stand LBP 2</th>
        <th>Pakai LBP 1+2</th>
        <th>Total LBP (Akum.)</th>
        <th>Stand Air</th>
        <th>Pakai Air</th>
        <th>Total Air (Akum.)</th>
    </tr>
    @foreach ($rows as $i => $r)
    <tr>
        <td style="text-align:center;">{{ $i + 1 }}</td>
        <td>{{ $r->tanggal->format('d/m/Y') }}</td>
        <td class="num">{{ $r->stand_bp ?? $r->stand_wbp }}</td>
        <td class="num">{{ $r->pakai_bp }}</td>
        <td class="num">{{ $r->cum_bp }}</td>
        <td class="num">{{ $r->stand_lbp1 ?? $r->stand_lwbp }}</td>
        <td class="num">{{ $r->stand_lbp2 }}</td>
        <td class="num">{{ $r->pakai_lbp }}</td>
        <td class="num">{{ $r->cum_lbp }}</td>
        <td class="num" style="font-weight:bold;">{{ $r->pakai_kwh }}</td>
        <td class="num">{{ $r->stand_air }}</td>
        <td class="num">{{ $r->pakai_air }}</td>
        <td class="num">{{ $r->cum_air }}</td>
        <td>{{ $r->user?->name ?? 'User Terhapus' }}</td>
        <td style="text-align:center;">{{ $r->verified_at ? 'Terverifikasi' : 'Menunggu' }}</td>
    </tr>
    @endforeach
    <tr class="tot">
        <td colspan="2" style="text-align:center;">TOTAL</td>
        <td></td>
        <td class="num">{{ $summary['bp'] }}</td>
        <td class="num">{{ $summary['bp'] }}</td>
        <td></td>
        <td></td>
        <td class="num">{{ $summary['lbp'] }}</td>
        <td class="num">{{ $summary['lbp'] }}</td>
        <td class="num">{{ $summary['kwh'] }}</td>
        <td></td>
        <td class="num">{{ $summary['air'] }}</td>
        <td class="num">{{ $summary['air'] }}</td>
        <td colspan="2" style="text-align:center;">{{ $summary['jumlah'] }} hari tercatat</td>
    </tr>
</table>
</body>
</html>
