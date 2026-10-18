@php
    $n = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
    $bulan = \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_{{ sprintf('%04d-%02d', $year, $month) }}</title>
<style>
    @page { size: A4 landscape; margin: 14mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 10.5pt; margin: 0; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 12px 20px; display: flex; gap: 10px; justify-content: flex-end; }
    .bar button { background: #6366f1; color: #fff; border: 0; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .page { padding: 24px; }
    h1 { font-size: 15pt; margin: 0; }
    .sub { color: #555; margin: 4px 0 16px; }
    .sum { display: flex; gap: 12px; margin-bottom: 16px; }
    .sum div { flex: 1; border: 1px solid #ccc; border-radius: 6px; padding: 8px 12px; }
    .sum b { display: block; font-size: 13pt; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #444; padding: 5px 7px; }
    th { background: #1e293b; color: #fff; font-size: 9pt; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    tfoot td { font-weight: bold; background: #e0e7ff; }
    .sign { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
    .sign div { width: 220px; }
    .sign .line { margin-top: 70px; border-top: 1px solid #000; padding-top: 4px; }
    @media print { .bar { display: none; } .page { padding: 0; } th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } tfoot td { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak</button>
</div>
<div class="page">
    <h1>LOGSHEET PEMAKAIAN LISTRIK &amp; AIR GEDUNG</h1>
    <p class="sub">Periode {{ $bulan }} · Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</p>

    <div class="sum">
        <div>kWh LWBP<b>{{ $n($summary['lwbp']) }} kWh</b></div>
        <div>kWh WBP<b>{{ $n($summary['wbp']) }} kWh</b></div>
        <div>Total Listrik<b>{{ $n($summary['kwh']) }} kWh</b></div>
        <div>Pemakaian Air<b>{{ $n($summary['air']) }} m³</b></div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th><th rowspan="2">Tanggal</th>
                <th colspan="5">Listrik (kWh)</th><th colspan="2">Air (m³)</th>
                <th rowspan="2">Teknisi</th><th rowspan="2">Status</th>
            </tr>
            <tr>
                <th>Stand LWBP</th><th>Pakai LWBP</th><th>Stand WBP</th><th>Pakai WBP</th><th>Total</th>
                <th>Stand</th><th>Pakai</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->tanggal->format('d/m/Y') }}</td>
                <td class="n">{{ $n($r->stand_lwbp) }}</td>
                <td class="n">{{ $n($r->pakai_lwbp) }}</td>
                <td class="n">{{ $n($r->stand_wbp) }}</td>
                <td class="n">{{ $n($r->pakai_wbp) }}</td>
                <td class="n">{{ $n($r->pakai_kwh) }}</td>
                <td class="n">{{ $n($r->stand_air) }}</td>
                <td class="n">{{ $n($r->pakai_air) }}</td>
                <td>{{ $r->user->name }}</td>
                <td>{{ $r->verified_at ? 'Terverifikasi' : 'Menunggu' }}</td>
            </tr>
        @empty
            <tr><td colspan="11" style="text-align:center">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td></td><td class="n">{{ $n($summary['lwbp']) }}</td>
                <td></td><td class="n">{{ $n($summary['wbp']) }}</td>
                <td class="n">{{ $n($summary['kwh']) }}</td>
                <td></td><td class="n">{{ $n($summary['air']) }}</td>
                <td colspan="2">{{ $summary['jumlah'] }} hari tercatat</td>
            </tr>
        </tfoot>
    </table>

    <div class="sign">
        <div>Dibuat oleh,<div class="line">Teknisi</div></div>
        <div>Diperiksa oleh,<div class="line">{{ auth()->user()->name }}</div></div>
    </div>
</div>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
</body>
</html>
