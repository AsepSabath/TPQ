<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Santri;
use App\Models\SppInvoice;
use App\Models\CashTransaction;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SppInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $month = (string) $request->query('month', '');
        $year = (string) $request->query('year', '');
        $search = trim((string) $request->query('search', ''));

        $query = SppInvoice::query()
            ->with('santri:id,nis,full_name')
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('id');

        if (in_array($status, ['belum_lunas', 'cicilan', 'lunas'], true)) {
            $query->where('status', $status);
        }

        if (is_numeric($month) && (int) $month >= 1 && (int) $month <= 12) {
            $query->where('month', (int) $month);
        }

        if (is_numeric($year)) {
            $query->where('year', (int) $year);
        }

        if ($search !== '') {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('spp-invoices.index', [
            'invoices' => $invoices,
            'status' => $status,
            'month' => $month,
            'year' => $year,
            'search' => $search,
        ]);
    }

    /**
     * Show the period status management page.
     */
    public function periodStatusIndex()
    {
        $periodGroups = SppInvoice::query()
            ->select(['month', 'year', 'period_status'])
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (SppInvoice $invoice) => sprintf('%04d-%02d', $invoice->year, $invoice->month))
            ->map(function ($items, string $periodKey) {
                [$year, $month] = explode('-', $periodKey);
                $periodDate = Carbon::create((int) $year, (int) $month, 1)->startOfMonth();
                $first = $items->first();

                return [
                    'period_key' => $periodKey,
                    'month' => (int) $month,
                    'year' => (int) $year,
                    'label' => $periodDate->translatedFormat('F Y'),
                    'period_status' => $first?->period_status ?? 'berjalan',
                    'total_invoices' => $items->count(),
                    'running_count' => $items->where('period_status', 'berjalan')->count(),
                    'not_started_count' => $items->where('period_status', 'belum_berjalan')->count(),
                ];
            })
            ->values();

        return view('spp-invoices.period-status', [
            'periodGroups' => $periodGroups,
        ]);
    }

    /**
     * Update the status of all invoices in a period.
     */
    public function periodStatusUpdate(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'period_status' => ['required', Rule::in(['berjalan', 'belum_berjalan'])],
        ]);

        $periodDate = Carbon::create((int) $validated['year'], (int) $validated['month'], 1)->startOfMonth();
        $beforeStatus = $periodDate->lte(now()->startOfMonth()) ? 'berjalan' : 'belum_berjalan';

        $affected = SppInvoice::query()
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->update(['period_status' => $validated['period_status']]);

        AuditLogger::log('spp_invoice', 'update_period_status', null, [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'period_status' => $beforeStatus,
        ], [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'period_status' => $validated['period_status'],
            'affected_count' => $affected,
        ]);

        return redirect()
            ->route('spp-invoices.period-status.index')
            ->with('success', 'Status periode berhasil diperbarui.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get();

        return view('spp-invoices.create', compact('santris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'period_count' => ['required', 'integer', 'between:1,24'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $periods = $this->buildPeriods(
            (int) $validated['month'],
            (int) $validated['year'],
            (int) $validated['period_count']
        );

        $existingInvoices = SppInvoice::query()
            ->where('santri_id', $validated['santri_id'])
            ->where(function ($query) use ($periods) {
                foreach ($periods as $period) {
                    $query->orWhere(function ($subQuery) use ($period) {
                        $subQuery
                            ->where('month', $period['month'])
                            ->where('year', $period['year']);
                    });
                }
            })
            ->get(['month', 'year']);

        $existingPeriodKeys = array_fill_keys(
            $existingInvoices
                ->map(fn (SppInvoice $invoice) => sprintf('%04d-%02d', $invoice->year, $invoice->month))
                ->all(),
            true
        );

        if (count($existingPeriodKeys) === count($periods)) {
            return back()
                ->withInput()
                ->withErrors(['month' => 'Semua tagihan pada periode yang dipilih sudah ada.']);
        }

        $createdInvoices = [];
        $baseDueDate = Carbon::parse($validated['due_date'])->startOfDay();

        DB::transaction(function () use ($validated, $periods, $existingPeriodKeys, $baseDueDate, &$createdInvoices) {
            foreach ($periods as $period) {
                $periodKey = sprintf('%04d-%02d', $period['year'], $period['month']);

                if (isset($existingPeriodKeys[$periodKey])) {
                    continue;
                }

                $createdInvoices[] = SppInvoice::query()->create([
                    'santri_id' => $validated['santri_id'],
                    'invoice_number' => $this->buildInvoiceNumber(
                        (int) $period['year'],
                        (int) $period['month'],
                        (int) $validated['santri_id']
                    ),
                    'month' => $period['month'],
                    'year' => $period['year'],
                    'amount' => $validated['amount'],
                    'paid_amount' => 0,
                    'due_date' => $baseDueDate->copy()->addMonths($period['offset'])->toDateString(),
                    'period_status' => $this->resolvePeriodStatus($period['month'], $period['year']),
                    'status' => 'belum_lunas',
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        foreach ($createdInvoices as $invoice) {
            AuditLogger::log('spp_invoice', 'create', $invoice, null, $invoice->toArray());
        }

        $createdCount = count($createdInvoices);
        $skippedCount = count($periods) - $createdCount;

        return redirect()
            ->route('spp-invoices.index')
            ->with('success', "Pembuatan tagihan selesai. Dibuat: {$createdCount}, dilewati: {$skippedCount}.");
    }

    /**
     * Store newly created invoices for all active students.
     */
    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'period_count' => ['required', 'integer', 'between:1,24'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $periods = $this->buildPeriods(
            (int) $validated['month'],
            (int) $validated['year'],
            (int) $validated['period_count']
        );
        $baseDueDate = Carbon::parse($validated['due_date'])->startOfDay();

        $activeSantriIds = Santri::query()
            ->where('status', 'aktif')
            ->pluck('id')
            ->all();

        if ($activeSantriIds === []) {
            return back()->withErrors([
                'bulk' => 'Tidak ada santri aktif untuk dibuatkan tagihan.',
            ]);
        }

        $existingRecords = SppInvoice::query()
            ->whereIn('santri_id', $activeSantriIds)
            ->where(function ($query) use ($periods) {
                foreach ($periods as $period) {
                    $query->orWhere(function ($subQuery) use ($period) {
                        $subQuery
                            ->where('month', $period['month'])
                            ->where('year', $period['year']);
                    });
                }
            })
            ->get(['santri_id', 'month', 'year']);

        $existingKeySet = array_fill_keys(
            $existingRecords
                ->map(fn (SppInvoice $invoice) => sprintf('%d-%04d-%02d', $invoice->santri_id, $invoice->year, $invoice->month))
                ->all(),
            true
        );

        $existingCount = count($existingKeySet);

        $targetTotal = count($activeSantriIds) * count($periods);

        if ($existingCount >= $targetTotal) {
            return redirect()
                ->route('spp-invoices.index')
                ->with('success', 'Semua santri aktif sudah memiliki tagihan untuk seluruh periode yang dipilih.');
        }

        $createdCount = 0;

        DB::transaction(function () use ($activeSantriIds, $periods, $validated, $baseDueDate, $existingKeySet, &$createdCount) {
            foreach ($activeSantriIds as $santriId) {
                foreach ($periods as $period) {
                    $periodKey = sprintf('%d-%04d-%02d', $santriId, $period['year'], $period['month']);

                    if (isset($existingKeySet[$periodKey])) {
                        continue;
                    }

                    SppInvoice::query()->create([
                        'santri_id' => $santriId,
                        'invoice_number' => $this->buildInvoiceNumber(
                            (int) $period['year'],
                            (int) $period['month'],
                            (int) $santriId
                        ),
                        'month' => $period['month'],
                        'year' => $period['year'],
                        'amount' => $validated['amount'],
                        'paid_amount' => 0,
                        'due_date' => $baseDueDate->copy()->addMonths($period['offset'])->toDateString(),
                        'period_status' => $this->resolvePeriodStatus($period['month'], $period['year']),
                        'status' => 'belum_lunas',
                        'notes' => $validated['notes'] ?? null,
                    ]);

                    $createdCount++;
                }
            }
        });

        $skippedCount = $targetTotal - $createdCount;

        AuditLogger::log('spp_invoice', 'create_bulk', null, null, [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'period_count' => (int) $validated['period_count'],
            'created_count' => $createdCount,
            'skipped_count' => $skippedCount,
        ]);

        return redirect()
            ->route('spp-invoices.index')
            ->with('success', "Pembuatan tagihan massal selesai. Dibuat: {$createdCount}, dilewati: {$skippedCount}.");
    }

    /**
     * Display the specified resource.
     */
    public function show(SppInvoice $sppInvoice)
    {
        $sppInvoice->load(['santri:id,nis,full_name', 'payments.receiver:id,name']);

        return view('spp-invoices.show', ['invoice' => $sppInvoice]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SppInvoice $sppInvoice)
    {
        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get();

        return view('spp-invoices.edit', [
            'invoice' => $sppInvoice,
            'santris' => $santris,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SppInvoice $sppInvoice)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'period_status' => ['required', Rule::in(['belum_berjalan', 'berjalan'])],
            'status' => ['required', Rule::in(['belum_lunas', 'cicilan', 'lunas'])],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = SppInvoice::query()
            ->where('id', '!=', $sppInvoice->id)
            ->where('santri_id', $validated['santri_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['month' => 'Tagihan SPP untuk periode tersebut sudah ada.']);
        }

        $before = $sppInvoice->toArray();
        $sppInvoice->update($validated);

        AuditLogger::log('spp_invoice', 'update', $sppInvoice, $before, $sppInvoice->fresh()->toArray());

        return redirect()
            ->route('spp-invoices.index')
            ->with('success', 'Tagihan SPP berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SppInvoice $sppInvoice)
    {
        $before = $sppInvoice->toArray();
        $paymentIds = $sppInvoice->payments()->pluck('id')->all();

        DB::transaction(function () use ($sppInvoice, $paymentIds) {
            if ($paymentIds !== []) {
                CashTransaction::query()->whereIn('related_payment_id', $paymentIds)->delete();
            }

            $sppInvoice->delete();
        });

        AuditLogger::log('spp_invoice', 'delete', $sppInvoice, $before, [
            'deleted_payment_count' => count($paymentIds),
        ]);

        return redirect()
            ->route('spp-invoices.index')
            ->with('success', 'Tagihan SPP beserta pembayaran terkait berhasil dihapus untuk revisi.');
    }

    /**
     * @return array<int, array{month:int, year:int, offset:int}>
     */
    private function buildPeriods(int $month, int $year, int $periodCount): array
    {
        $startPeriod = Carbon::create($year, $month, 1)->startOfMonth();
        $periods = [];

        for ($offset = 0; $offset < $periodCount; $offset++) {
            $period = $startPeriod->copy()->addMonths($offset);

            $periods[] = [
                'month' => (int) $period->month,
                'year' => (int) $period->year,
                'offset' => $offset,
            ];
        }

        return $periods;
    }

    private function buildInvoiceNumber(int $year, int $month, int $santriId): string
    {
        return sprintf('SPP-%04d%02d-%05d', $year, $month, $santriId);
    }

    private function resolvePeriodStatus(int $month, int $year): string
    {
        $periodDate = Carbon::create($year, $month, 1)->startOfMonth();

        return $periodDate->lte(now()->startOfMonth())
            ? 'berjalan'
            : 'belum_berjalan';
    }
}
