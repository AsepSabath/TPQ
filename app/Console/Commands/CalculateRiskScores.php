<?php

namespace App\Console\Commands;

use App\Models\DailyAttendance;
use App\Models\Grade;
use App\Models\RiskAlert;
use App\Models\Santri;
use App\Models\SppInvoice;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:calculate-risk-scores')]
#[Description('Hitung skor risiko santri dari absensi, nilai, tunggakan, dan pelanggaran')]
class CalculateRiskScores extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $windowStart = Carbon::now()->subDays(30)->toDateString();
        $violationWindowStart = Carbon::now()->subDays(90)->toDateString();
        $updated = 0;

        Santri::query()
            ->where('status', 'aktif')
            ->chunkById(100, function ($santris) use ($windowStart, $violationWindowStart, &$updated) {
                foreach ($santris as $santri) {
                    $attendanceTotal = DailyAttendance::query()
                        ->where('santri_id', $santri->id)
                        ->whereDate('attendance_date', '>=', $windowStart)
                        ->count();

                    $attendancePresent = DailyAttendance::query()
                        ->where('santri_id', $santri->id)
                        ->whereDate('attendance_date', '>=', $windowStart)
                        ->where('status', 'hadir')
                        ->count();

                    $attendanceRate = $attendanceTotal > 0
                        ? round(($attendancePresent / $attendanceTotal) * 100, 2)
                        : 100.00;

                    $avgGrade = (float) (Grade::query()
                        ->where('santri_id', $santri->id)
                        ->avg('score') ?? 100);

                    $unpaidMonths = SppInvoice::query()
                        ->where('santri_id', $santri->id)
                        ->runningPeriod()
                        ->whereIn('status', ['belum_lunas', 'cicilan'])
                        ->whereDate('due_date', '<=', now()->endOfMonth())
                        ->count();

                    $violationCount = Violation::query()
                        ->where('santri_id', $santri->id)
                        ->whereDate('incident_date', '>=', $violationWindowStart)
                        ->count();

                    $attendancePenalty = max(0, 100 - $attendanceRate);
                    $gradePenalty = max(0, 100 - $avgGrade);
                    $unpaidPenalty = min($unpaidMonths * 10, 100);
                    $violationPenalty = min($violationCount * 10, 100);

                    $riskScore = round(
                        ($attendancePenalty * 0.35)
                        + ($gradePenalty * 0.35)
                        + ($unpaidPenalty * 0.20)
                        + ($violationPenalty * 0.10),
                        2
                    );

                    $riskLevel = 'rendah';

                    if ($riskScore >= 60) {
                        $riskLevel = 'tinggi';
                    } elseif ($riskScore >= 35) {
                        $riskLevel = 'sedang';
                    }

                    RiskAlert::query()->updateOrCreate(
                        ['santri_id' => $santri->id],
                        [
                            'risk_score' => $riskScore,
                            'risk_level' => $riskLevel,
                            'attendance_rate' => $attendanceRate,
                            'avg_grade' => round($avgGrade, 2),
                            'unpaid_months' => $unpaidMonths,
                            'violation_count' => $violationCount,
                            'last_calculated_at' => now(),
                        ]
                    );

                    $updated++;
                }
            });

        $this->info("Risk score selesai dihitung untuk {$updated} santri.");

        return self::SUCCESS;
    }
}
