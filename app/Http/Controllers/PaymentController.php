<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\SppInvoice;
use App\Services\AuditLogger;
use App\Services\WhatsAppNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, SppInvoice $sppInvoice, WhatsAppNotifier $whatsAppNotifier)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'ewallet', 'lainnya'])],
            'paid_at' => ['nullable', 'date'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $remaining = (float) $sppInvoice->amount - (float) $sppInvoice->paid_amount;

        if ((float) $validated['amount'] > $remaining) {
            return back()->withErrors([
                'amount' => 'Nominal pembayaran melebihi sisa tagihan.',
            ]);
        }

        DB::transaction(function () use ($validated, $sppInvoice, $whatsAppNotifier) {
            $payment = $sppInvoice->payments()->create([
                'received_by' => auth()->id(),
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'paid_at' => $validated['paid_at'] ?? now(),
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            CashTransaction::query()->create([
                'transaction_date' => now()->toDateString(),
                'type' => 'masuk',
                'category' => 'Pembayaran SPP',
                'amount' => $validated['amount'],
                'description' => 'Pembayaran '.$sppInvoice->invoice_number,
                'recorded_by' => auth()->id(),
                'related_payment_id' => $payment->id,
            ]);

            $sppInvoice->refreshPaymentStatus();
            $sppInvoice->load('santri.guardians');

            $message = sprintf(
                'Pembayaran SPP %s sebesar Rp %s diterima. Sisa tagihan Rp %s.',
                $sppInvoice->santri->full_name,
                number_format((float) $validated['amount'], 0, ',', '.'),
                number_format((float) $sppInvoice->amount - (float) $sppInvoice->paid_amount, 0, ',', '.')
            );

            $notification = $whatsAppNotifier->queueForSantri($sppInvoice->santri, 'payment_receipt', $message);

            if ($notification) {
                $whatsAppNotifier->dispatch($notification);
            }

            AuditLogger::log('payment', 'create', $payment, null, $payment->toArray());
        });

        return redirect()
            ->route('spp-invoices.show', $sppInvoice)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }
}
