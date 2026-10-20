@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $bulan = \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_MVSB_Incoming_PLN_{{ sprintf('%04d-%02d', $year, $month) }}</title>
<style>
    @page { size: A4 portrait; margin: 12mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 9pt; margin: 0; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 10px 18px; display: flex; gap: 10px; justify-content: flex-end; }
    .bar button { background: #7c3aed; color: #fff; border: 0; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 8.5pt; }
    .page { padding: 15px; }
    h1 { font-size: 13pt; margin: 0; color: #581c87; }
    .sub { color: #555; margin: 3px 0 14px; font-size: 8.5pt; }
    table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-top: 8px; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
    th { background: #581c87; color: #fff; font-weight: 600; text-align: center; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    td.c { text-align: center; }
    .sign { display: flex; justify-content: space-between; margin-top: 35px; text-align: center; font-size: 8.5pt; }
    .sign div { width: 200px; }
    .sign .line { margin-top: 55px; border-top: 1px solid #000; padding-top: 4px; }
    @media print { .bar { display: none; } .page { padding: 0; } th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak</button>
</div>
<div class="page">
    <h1>LOGSHEET MVSB INCOMING FROM PLN — PUBLIC AREA</h1>
    <p class="sub">Periode: <b>{{ $bulan }}</b> · Dicetak pada: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</p>

    <table>
        <thead>
            <tr>
                <th style="width:35px;">No</th>
                <th style="width:90px;">Tanggal</th>
                <th style="width:130px;">Voltage 20kV (Volt)</th>
                <th style="width:130px;">Current (Ampere)</th>
                <th>Catatan Kondisi</th>
                <th style="width:110px;">Petugas</th>
                <th style="width:90px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $idx => $r)
                <tr>
                    <td class="c">{{ $idx + 1 }}</td>
                    <td class="c">{{ $r->tanggal->format('d/m/Y') }}</td>
                    <td class="n" style="font-weight:600; color:#581c87;">{{ $n($r->voltage_20kv, 1) }}</td>
                    <td class="n" style="font-weight:600; color:#0369a1;">{{ $n($r->current, 2) }}</td>
                    <td>{{ $r->catatan ?? '—' }}</td>
                    <td class="c">{{ $r->user?->name ?? 'Teknisi' }}</td>
                    <td class="c" style="font-size:7.5pt;">{{ $r->isVerified() ? '✓ Verified' : 'Pending' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="c" style="padding:20px; color:#888;">
                        Tidak ada data pencatatan MVSB pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="sign">
        <div>
            Dibuat oleh,<br>Teknisi Jaga
            <div class="line">( ............................................ )</div>
        </div>
        <div>
            Diperiksa oleh,<br>Chief Engineer / SPV
            <div class="line">( ............................................ )</div>
        </div>
        <div>
            Mengetahui,<br>Building Manager
            <div class="line">( ............................................ )</div>
        </div>
    </div>
</div>
</body>
</html>
