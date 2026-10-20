@php
    $n = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.') ;
    $catLabel = $category === 'water' ? 'WATER METER PUBLIC AREA' : 'KWH METER PUBLIC AREA';
    $bulan = \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');
    $unit = $category === 'water' ? 'm³' : 'kWh';
    $totalPemakaian = round($rows->sum(fn ($r) => $r->pemakaian ?? 0), 2);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_Public_Area_{{ ucfirst($category) }}_{{ sprintf('%04d-%02d', $year, $month) }}</title>
<style>
    @page { size: A4 portrait; margin: 12mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 9pt; margin: 0; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 10px 18px; display: flex; gap: 10px; justify-content: flex-end; }
    .bar button { background: #6366f1; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .page { padding: 16px; }
    h1 { font-size: 14pt; margin: 0; }
    .sub { color: #555; margin: 3px 0 14px; font-size: 9pt; }
    .sum { display: flex; gap: 10px; margin-bottom: 14px; }
    .sum div { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: #f8fafc; }
    .sum b { display: block; font-size: 13pt; color: #0f172a; margin-top: 3px; }
    table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
    th, td { border: 1px solid #64748b; padding: 5px 7px; }
    th { background: #1e293b; color: #fff; text-align: center; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    td.c { text-align: center; }
    tfoot td { font-weight: bold; background: #fef3c7; }
    .sign { display: flex; justify-content: space-between; margin-top: 35px; text-align: center; font-size: 9pt; }
    .sign div { width: 220px; }
    .sign .line { margin-top: 60px; border-top: 1px solid #000; padding-top: 4px; }
    @media print { .bar { display: none; } .page { padding: 0; } th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } tfoot td { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak</button>
</div>
<div class="page">
    <h1>LOGSHEET REKAPITULASI {{ $catLabel }}</h1>
    <p class="sub">Periode: {{ $bulan }} · Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</p>

    <div class="sum">
        <div>Total Pemakaian Bulanan<b>{{ $n($totalPemakaian) }} {{ $unit }}</b></div>
        <div>Jumlah Data Tercatat<b>{{ $rows->count() }} pencatatan</b></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Tanggal</th>
                <th>Nama Meteran</th>
                <th>Lokasi</th>
                <th>Stand ({{ $unit }})</th>
                <th>Pemakaian ({{ $unit }})</th>
                <th>Petugas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $i => $r)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $r->tanggal->format('d/m/Y') }}</td>
                <td style="font-weight:600;">{{ $r->meter_name }}</td>
                <td>{{ $meters[$r->meter_key]['location'] ?? '—' }}</td>
                <td class="n">{{ $n($r->stand_meter) }}</td>
                <td class="n">{{ $n($r->pemakaian) }}</td>
                <td>{{ $r->user?->name ?? 'User Terhapus' }}</td>
                <td class="c">{{ $r->verified_at ? '✓' : 'Menunggu' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center">Tidak ada data pencatatan pada periode ini.</td></tr>
        @endforelse
        </tbody>
        @if ($rows->count())
        <tfoot>
            <tr>
                <td colspan="5" class="c">TOTAL PEMAKAIAN</td>
                <td class="n">{{ $n($totalPemakaian) }} {{ $unit }}</td>
                <td colspan="2" class="c">{{ $rows->count() }} data</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="sign">
        <div>Dibuat oleh,<div class="line">Teknisi Pengawas</div></div>
        <div>Diperiksa &amp; Disetujui,<div class="line">{{ auth()->user()->name }} (Supervisor / Admin)</div></div>
    </div>
</div>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
</body>
</html>
