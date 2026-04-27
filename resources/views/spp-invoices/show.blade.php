<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Tagihan SPP</h2>
            <a href="{{ route('spp-invoices.edit', $invoice) }}" class="rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Edit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900">Informasi Tagihan</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">No Invoice</dt><dd class="font-semibold">{{ $invoice->invoice_number }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Santri</dt><dd class="font-semibold">{{ $invoice->santri->full_name ?? '-' }}</dd></div>
                        @php($periodDate = \Carbon\Carbon::create($invoice->year, $invoice->month, 1))
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Periode</dt><dd class="font-semibold">{{ $periodDate->format('F Y') }} ({{ str_pad($invoice->month, 2, '0', STR_PAD_LEFT) }}/{{ $invoice->year }})</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Status Periode</dt><dd class="font-semibold">{{ $invoice->period_status === 'berjalan' ? 'Berjalan' : 'Belum Berjalan' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Jatuh Tempo</dt><dd class="font-semibold">{{ $invoice->due_date->format('d M Y') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Tagihan</dt><dd class="font-semibold">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Dibayar</dt><dd class="font-semibold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Sisa</dt><dd class="font-semibold">Rp {{ number_format($invoice->amount - $invoice->paid_amount, 0, ',', '.') }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900">Input Pembayaran</h3>
                    <form method="POST" action="{{ route('spp-invoices.payments.store', $invoice) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nominal Bayar</label>
                            <input type="number" step="0.01" name="amount" class="mt-1 w-full rounded-md border-gray-300" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Metode</label>
                            <select name="payment_method" class="mt-1 w-full rounded-md border-gray-300" required>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="ewallet">E-Wallet</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Bayar</label>
                            <input type="datetime-local" name="paid_at" class="mt-1 w-full rounded-md border-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">No Referensi</label>
                            <input type="text" name="reference_no" class="mt-1 w-full rounded-md border-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea name="note" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                        </div>

                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Pembayaran</button>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 overflow-x-auto">
                <h3 class="font-semibold text-gray-900">Riwayat Pembayaran</h3>
                <table class="min-w-full text-sm mt-3">
                    <thead class="text-left text-gray-500 border-b">
                        <tr>
                            <th class="py-2 pe-4">Tanggal</th>
                            <th class="py-2 pe-4">Nominal</th>
                            <th class="py-2 pe-4">Metode</th>
                            <th class="py-2 pe-4">Petugas</th>
                            <th class="py-2 pe-4">Referensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->payments as $payment)
                            <tr class="border-b last:border-0">
                                <td class="py-2 pe-4">{{ $payment->paid_at->format('d M Y H:i') }}</td>
                                <td class="py-2 pe-4">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="py-2 pe-4">{{ strtoupper($payment->payment_method) }}</td>
                                <td class="py-2 pe-4">{{ $payment->receiver->name ?? '-' }}</td>
                                <td class="py-2 pe-4">{{ $payment->reference_no ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-500">Belum ada pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                <a href="{{ route('spp-invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali ke daftar tagihan</a>
            </div>
        </div>
    </div>
</x-app-layout>
