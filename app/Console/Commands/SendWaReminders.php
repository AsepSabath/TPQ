<?php

namespace App\Console\Commands;

use App\Models\DailyAttendance;
use App\Models\SppInvoice;
use App\Services\WhatsAppNotifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-wa-reminders')]
#[Description('Kirim reminder WA otomatis untuk tunggakan SPP dan absensi alpha')]
class SendWaReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $whatsAppNotifier = app(WhatsAppNotifier::class);
        $queuedCount = 0;
        $sentCount = 0;

        $invoices = SppInvoice::query()
            ->with('santri.guardians')
            ->runningPeriod()
            ->whereIn('status', ['belum_lunas', 'cicilan'])
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->get();

        foreach ($invoices as $invoice) {
            if (! $invoice->santri) {
                continue;
            }

            $remaining = (float) $invoice->amount - (float) $invoice->paid_amount;

            if ($remaining <= 0) {
                continue;
            }

            $message = sprintf(
                'Pengingat SPP: %s masih memiliki sisa tagihan Rp %s, jatuh tempo %s.',
                $invoice->santri->full_name,
                number_format($remaining, 0, ',', '.'),
                $invoice->due_date->format('d-m-Y')
            );

            $notification = $whatsAppNotifier->queueForSantri($invoice->santri, 'spp_due_reminder', $message);

            if ($notification) {
                $queuedCount++;
                if ($whatsAppNotifier->dispatch($notification)) {
                    $sentCount++;
                }
            }
        }

        $alphaDate = now()->subDay()->toDateString();
        $alphaAttendances = DailyAttendance::query()
            ->with('santri.guardians')
            ->whereDate('attendance_date', $alphaDate)
            ->where('status', 'alpha')
            ->get();

        foreach ($alphaAttendances as $attendance) {
            if (! $attendance->santri) {
                continue;
            }

            $message = sprintf(
                'Info Absensi: %s tercatat ALPHA pada tanggal %s. Mohon konfirmasi ke pihak madrasah.',
                $attendance->santri->full_name,
                $attendance->attendance_date->format('d-m-Y')
            );

            $notification = $whatsAppNotifier->queueForSantri($attendance->santri, 'attendance_alpha_alert', $message);

            if ($notification) {
                $queuedCount++;
                if ($whatsAppNotifier->dispatch($notification)) {
                    $sentCount++;
                }
            }
        }

        $this->info("WA reminder selesai. Queued: {$queuedCount}, Sent: {$sentCount}.");

        return self::SUCCESS;
    }
}
