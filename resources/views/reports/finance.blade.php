<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Keuangan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-md border-gray-300">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-md border-gray-300">
                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                    <a href="{{ route('reports.finance', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white text-center hover:bg-emerald-700">Export CSV</a>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Kas Masuk</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-2">Rp {{ number_format($kasMasuk, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Kas Keluar</p>
                    <p class="text-2xl font-bold text-rose-700 mt-2">Rp {{ number_format($kasKeluar, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Total Tagihan SPP</p>
                    <p class="text-2xl font-bold text-slate-800 mt-2">Rp {{ number_format($sppTotal, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Tunggakan SPP Berjalan</p>
                    <p class="text-2xl font-bold text-amber-700 mt-2">Rp {{ number_format($sppOutstanding, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <h3 class="font-semibold text-gray-900">Ringkasan Status Tagihan SPP</h3>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-md bg-gray-50 p-3">Belum Lunas: <span class="font-semibold">{{ number_format($statusSummary['belum_lunas'] ?? 0) }}</span></div>
                    <div class="rounded-md bg-gray-50 p-3">Cicilan: <span class="font-semibold">{{ number_format($statusSummary['cicilan'] ?? 0) }}</span></div>
                    <div class="rounded-md bg-gray-50 p-3">Lunas: <span class="font-semibold">{{ number_format($statusSummary['lunas'] ?? 0) }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Nominal</th>
                            <th class="px-4 py-3">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $transaction->transaction_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ ucfirst($transaction->type) }}</td>
                                <td class="px-4 py-3">{{ $transaction->category }}</td>
                                <td class="px-4 py-3">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $transaction->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada transaksi di periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $transactions->links() }}</div>
        </div>
    </div>
</x-app-layout>
