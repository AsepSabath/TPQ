<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = (string) $request->query('type', '');
        $month = (string) $request->query('month', '');

        $query = CashTransaction::query()->latest('transaction_date');

        if (in_array($type, ['masuk', 'keluar'], true)) {
            $query->where('type', $type);
        }

        if (is_numeric($month) && (int) $month >= 1 && (int) $month <= 12) {
            $query->whereMonth('transaction_date', (int) $month);
        }

        $transactions = $query->paginate(15)->withQueryString();

        $totalKasMasuk = (float) CashTransaction::query()
            ->where('type', 'masuk')
            ->sum('amount');

        $totalKasKeluar = (float) CashTransaction::query()
            ->where('type', 'keluar')
            ->sum('amount');

        $totalSaldoKas = $totalKasMasuk - $totalKasKeluar;

        return view('cash-transactions.index', [
            'transactions' => $transactions,
            'type' => $type,
            'month' => $month,
            'totalKasMasuk' => $totalKasMasuk,
            'totalKasKeluar' => $totalKasKeluar,
            'totalSaldoKas' => $totalSaldoKas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cash-transactions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['masuk', 'keluar'])],
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['recorded_by'] = auth()->id();

        $transaction = CashTransaction::query()->create($validated);

        AuditLogger::log('cash_transaction', 'create', $transaction, null, $transaction->toArray());

        return redirect()
            ->route('cash-transactions.index')
            ->with('success', 'Transaksi kas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CashTransaction $cashTransaction)
    {
        return redirect()->route('cash-transactions.edit', $cashTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashTransaction $cashTransaction)
    {
        return view('cash-transactions.edit', ['transaction' => $cashTransaction]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashTransaction $cashTransaction)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['masuk', 'keluar'])],
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        $before = $cashTransaction->toArray();
        $cashTransaction->update($validated);

        AuditLogger::log('cash_transaction', 'update', $cashTransaction, $before, $cashTransaction->fresh()->toArray());

        return redirect()
            ->route('cash-transactions.index')
            ->with('success', 'Transaksi kas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashTransaction $cashTransaction)
    {
        $before = $cashTransaction->toArray();
        $cashTransaction->delete();

        AuditLogger::log('cash_transaction', 'delete', $cashTransaction, $before, null);

        return redirect()
            ->route('cash-transactions.index')
            ->with('success', 'Transaksi kas berhasil dihapus.');
    }
}
