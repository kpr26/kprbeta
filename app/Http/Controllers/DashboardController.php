<?php

namespace App\Http\Controllers;

use App\Models\DailyLogBook;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();

        // Role teknisi hanya boleh mengakses menu logsheet
        if ($user && $user->role === 'teknisi') {
            return redirect()->route('logsheet.create');
        }

        $rows  = MeterReading::monthWithUsage(now()->year, now()->month);
        $today = $rows->first(fn ($r) => $r->tanggal->isToday());

        $summary = [
            'lwbp'     => $rows->sum('pakai_lwbp'),
            'wbp'      => $rows->sum('pakai_wbp'),
            'kwh'      => $rows->sum('pakai_kwh'),
            'air'      => $rows->sum('pakai_air'),
            'pending'  => MeterReading::whereNull('verified_at')->count(),
        ];

        $chart = [
            'labels' => $rows->map(fn ($r) => $r->tanggal->format('d'))->values(),
            'lwbp'   => $rows->pluck('pakai_lwbp')->values(),
            'wbp'    => $rows->pluck('pakai_wbp')->values(),
            'air'    => $rows->pluck('pakai_air')->values(),
        ];

        $todayLogs = DailyLogBook::with('user')->whereDate('tanggal', today())->latest()->take(4)->get();
        $dailyLogStats = [
            'today_total'    => DailyLogBook::whereDate('tanggal', today())->count(),
            'today_done'     => DailyLogBook::whereDate('tanggal', today())->where('status', 'Done')->count(),
            'today_continue' => DailyLogBook::whereDate('tanggal', today())->where('status', 'Continue')->count(),
        ];

        return view('dashboard', compact('user', 'today', 'summary', 'chart', 'todayLogs', 'dailyLogStats'));
    }
}
