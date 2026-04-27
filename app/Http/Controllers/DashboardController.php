<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\DailyAttendance;
use App\Models\RiskAlert;
use App\Models\Santri;
use App\Models\SppInvoice;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();

        $totalSantriAktif = Santri::query()->where('status', 'aktif')->count();

        $attendanceToday = DailyAttendance::query()
            ->whereDate('attendance_date', $today)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $openInvoices = SppInvoice::query()
            ->runningPeriod()
            ->whereIn('status', ['belum_lunas', 'cicilan'])
            ->count();

        $outstandingSpp = (float) SppInvoice::query()
            ->runningPeriod()
            ->whereIn('status', ['belum_lunas', 'cicilan'])
            ->selectRaw('coalesce(sum(amount - paid_amount), 0) as total')
            ->value('total');

        $kasMasukBulanIni = (float) CashTransaction::query()
            ->where('type', 'masuk')
            ->whereDate('transaction_date', '>=', $monthStart)
            ->selectRaw('coalesce(sum(amount), 0) as total')
            ->value('total');

        $kasKeluarBulanIni = (float) CashTransaction::query()
            ->where('type', 'keluar')
            ->whereDate('transaction_date', '>=', $monthStart)
            ->selectRaw('coalesce(sum(amount), 0) as total')
            ->value('total');

        $highRiskSantri = RiskAlert::query()
            ->with('santri:id,nis,full_name')
            ->where('risk_level', 'tinggi')
            ->where('is_resolved', false)
            ->orderByDesc('risk_score')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'totalSantriAktif' => $totalSantriAktif,
            'attendanceToday' => $attendanceToday,
            'openInvoices' => $openInvoices,
            'outstandingSpp' => $outstandingSpp,
            'kasMasukBulanIni' => $kasMasukBulanIni,
            'kasKeluarBulanIni' => $kasKeluarBulanIni,
            'highRiskSantri' => $highRiskSantri,
        ]);
    }
}
