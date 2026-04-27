<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\DailyAttendance;
use App\Models\Grade;
use App\Models\Santri;
use App\Models\SppInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function semester(Request $request)
    {
        $academicYear = (string) $request->query('academic_year', now()->year.'/'.(now()->year + 1));
        $semester = (string) $request->query('semester', 'ganjil');
        $santriId = (string) $request->query('santri_id', '');

        $gradeQuery = Grade::query()
            ->with(['santri:id,nis,full_name', 'subject:id,name,code'])
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->orderBy('santri_id');

        if (is_numeric($santriId)) {
            $gradeQuery->where('santri_id', (int) $santriId);
        }

        $grades = $gradeQuery->get();

        $grouped = $grades
            ->groupBy('santri_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'santri' => $first?->santri,
                    'grades' => $items,
                    'average' => round((float) $items->avg('score'), 2),
                ];
            })
            ->values();

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.semester-pdf', [
                'academicYear' => $academicYear,
                'semester' => $semester,
                'grouped' => $grouped,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('laporan-semester-'.$semester.'.pdf');
        }

        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']);

        return view('reports.semester', [
            'academicYear' => $academicYear,
            'semester' => $semester,
            'santriId' => $santriId,
            'grouped' => $grouped,
            'santris' => $santris,
        ]);
    }

    public function finance(Request $request)
    {
        $startDate = (string) $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = (string) $request->query('end_date', now()->endOfMonth()->toDateString());

        $transactionQuery = CashTransaction::query()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        $transactions = $transactionQuery->paginate(20)->withQueryString();

        $kasMasuk = (float) CashTransaction::query()
            ->where('type', 'masuk')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $kasKeluar = (float) CashTransaction::query()
            ->where('type', 'keluar')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $sppTotal = (float) SppInvoice::query()->sum('amount');
        $sppOutstanding = (float) SppInvoice::query()
            ->runningPeriod()
            ->whereIn('status', ['belum_lunas', 'cicilan'])
            ->selectRaw('coalesce(sum(amount - paid_amount), 0) as total')
            ->value('total');

        $statusSummary = SppInvoice::query()
            ->runningPeriod()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        if ($request->query('export') === 'csv') {
            $rows = CashTransaction::query()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get()
                ->map(function (CashTransaction $transaction) {
                    return [
                        $transaction->transaction_date->toDateString(),
                        $transaction->type,
                        $transaction->category,
                        (string) $transaction->amount,
                        $transaction->description ?? '',
                    ];
                })
                ->all();

            return $this->csvResponse('laporan-keuangan.csv', ['tanggal', 'tipe', 'kategori', 'nominal', 'deskripsi'], $rows);
        }

        return view('reports.finance', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'kasMasuk' => $kasMasuk,
            'kasKeluar' => $kasKeluar,
            'sppTotal' => $sppTotal,
            'sppOutstanding' => $sppOutstanding,
            'statusSummary' => $statusSummary,
        ]);
    }

    public function attendance(Request $request)
    {
        $startDate = (string) $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = (string) $request->query('end_date', now()->endOfMonth()->toDateString());

        $summary = DailyAttendance::query()
            ->select('status', DB::raw('count(*) as total'))
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalRecords = (int) DailyAttendance::query()
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->count();

        $topAlphaSantri = DailyAttendance::query()
            ->select('santri_id', DB::raw('count(*) as total_alpha'))
            ->where('status', 'alpha')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->with('santri:id,nis,full_name')
            ->groupBy('santri_id')
            ->orderByDesc('total_alpha')
            ->limit(10)
            ->get();

        $recentAttendances = DailyAttendance::query()
            ->with(['santri:id,nis,full_name'])
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->latest('attendance_date')
            ->latest('id')
            ->limit(30)
            ->get();

        if ($request->query('export') === 'csv') {
            $rows = DailyAttendance::query()
                ->with('santri:id,nis,full_name')
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->orderBy('attendance_date')
                ->orderBy('id')
                ->get()
                ->map(function (DailyAttendance $attendance) {
                    return [
                        $attendance->attendance_date->toDateString(),
                        $attendance->santri->nis ?? '',
                        $attendance->santri->full_name ?? '',
                        $attendance->status,
                        $attendance->notes ?? '',
                    ];
                })
                ->all();

            return $this->csvResponse('laporan-absensi.csv', ['tanggal', 'nis', 'nama_santri', 'status', 'catatan'], $rows);
        }

        return view('reports.attendance', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'totalRecords' => $totalRecords,
            'topAlphaSantri' => $topAlphaSantri,
            'recentAttendances' => $recentAttendances,
        ]);
    }

    private function csvResponse(string $filename, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
