@php
    $n = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
    $bulan = \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');
    $pakaiBp = $summary['bp'] ?? $summary['wbp'] ?? 0;
    $pakaiLbp = (($summary['lbp1'] ?? 0) + ($summary['lbp2'] ?? 0)) > 0 ? (($summary['lbp1'] ?? 0) + ($summary['lbp2'] ?? 0)) : ($summary['lwbp'] ?? 0);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_AMR_PLN_{{ sprintf('%04d-%02d', $year, $month) }}</title>
<style>
    @page { size: A4 landscape; margin: 10mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 8.5pt; margin: 0; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 10px 18px; display: flex; gap: 10px; justify-content: flex-end; }
    .bar button { background: #6366f1; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .page { padding: 16px; }
    h1 { font-size: 13pt; margin: 0; }
    .sub { color: #555; margin: 3px 0 12px; font-size: 8.5pt; }
    .sum { display: flex; gap: 10px; margin-bottom: 12px; }
    .sum div { flex: 1; border: 1px solid #cbd5e1; border-radius: 5px; padding: 6px 10px; background: #f8fafc; }
    .sum b { display: block; font-size: 11pt; color: #0f172a; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; font-size: 8pt; }
    th, td { border: 1px solid #64748b; padding: 4px 5px; }
    th { background: #1e293b; color: #fff; text-align: center; }
    th.g-elec { background: #1e3a8a; }
    th.g-water { background: #0369a1; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    td.c { text-align: center; }
    tfoot td { font-weight: bold; background: #fef3c7; }
    .sign { display: flex; justify-content: space-between; margin-top: 28px; text-align: center; font-size: 8.5pt; }
    .sign div { width: 220px; }
    .sign .line { margin-top: 55px; border-top: 1px solid #000; padding-top: 4px; }
    @media print { .bar { display: none; } .page { padding: 0; } th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } tfoot td { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak</button>
</div>
<div class="page">
    <h1>LOGSHEET REKAPITULASI AMR PLN T33N &amp; AIR BERSIH GEDUNG</h1>
    <p class="sub">Periode: {{ $bulan }} · Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</p>

    <div class="sum">
        <div>Beban Puncak (BP)<b>{{ $n($pakaiBp) }} kWh</b></div>
        <div>Luar BP (LBP 1+2)<b>{{ $n($pakaiLbp) }} kWh</b></div>
        <div>Total Pemakaian Listrik<b>{{ $n($summary['kwh']) }} kWh</b></div>
        <div>Total Pemakaian Air<b>{{ $n($summary['air']) }} m³</b></div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th colspan="7" class="g-elec">AMR PLN T33N (kWh)</th>
                <th rowspan="2" class="g-elec">Total Listrik<br><small>(BP+LBP)</small></th>
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
        </thead>
        <tbody>
        @forelse ($rows as $i => $r)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $r->tanggal->format('d/m/Y') }}</td>
                <td class="n">{{ $n($r->stand_bp ?? $r->stand_wbp) }}</td>
                <td class="n">{{ $n($r->pakai_bp) }}</td>
                <td class="n" style="font-weight:600;">{{ $n($r->cum_bp) }}</td>
                <td class="n">{{ $n($r->stand_lbp1 ?? $r->stand_lwbp) }}</td>
                <td class="n">{{ $n($r->stand_lbp2) }}</td>
                <td class="n">{{ $n($r->pakai_lbp) }}</td>
                <td class="n" style="font-weight:600;">{{ $n($r->cum_lbp) }}</td>
                <td class="n" style="font-weight:bold; background:#f1f5f9;">{{ $n($r->pakai_kwh) }}</td>
                <td class="n">{{ $n($r->stand_air) }}</td>
                <td class="n">{{ $n($r->pakai_air) }}</td>
                <td class="n" style="font-weight:600;">{{ $n($r->cum_air) }}</td>
                <td>{{ $r->user?->name ?? 'User Terhapus' }}</td>
                <td class="c">{{ $r->isVerified() ? '✓' : 'Menunggu' }}</td>
            </tr>
        @empty
            <tr><td colspan="15" style="text-align:center">Tidak ada data pada periode ini.</td></tr>
        @endforelse
        </tbody>
        @if ($rows->count())
        <tfoot>
            <tr>
                <td colspan="2" class="c">TOTAL</td>
                <td>—</td>
                <td class="n">{{ $n($summary['bp']) }}</td>
                <td class="n">{{ $n($summary['bp']) }}</td>
                <td>—</td>
                <td>—</td>
                <td class="n">{{ $n($summary['lbp']) }}</td>
                <td class="n">{{ $n($summary['lbp']) }}</td>
                <td class="n">{{ $n($summary['kwh']) }} kWh</td>
                <td>—</td>
                <td class="n">{{ $n($summary['air']) }} m³</td>
                <td class="n">{{ $n($summary['air']) }} m³</td>
                <td colspan="2" class="c">{{ $summary['jumlah'] }} hari tercatat</td>
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
