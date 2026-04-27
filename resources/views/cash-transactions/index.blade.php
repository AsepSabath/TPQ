<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kas</h2>
            <a href="{{ route('cash-transactions.create') }}" class="w-full sm:w-auto text-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Tambah Transaksi
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <select name="type" class="rounded-md border-gray-300">
                        <option value="">Semua Tipe</option>
                        <option value="masuk" @selected($type === 'masuk')>Kas Masuk</option>
                        <option value="keluar" @selected($type === 'keluar')>Kas Keluar</option>
                    </select>

                    <input type="number" name="month" min="1" max="12" value="{{ $month }}" placeholder="Bulan (1-12)" class="rounded-md border-gray-300">

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                    <a href="{{ route('cash-transactions.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 text-center hover:bg-gray-50">Reset</a>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Total Kas Masuk</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Total Kas Keluar</p>
                    <p class="mt-2 text-2xl font-bold text-rose-700">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Total Saldo Kas Saat Ini</p>
                    <p class="mt-2 text-2xl font-bold {{ $totalSaldoKas >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        Rp {{ number_format($totalSaldoKas, 0, ',', '.') }}
                    </p>
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
                            <th class="px-4 py-3">Pencatat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $transaction->transaction_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($transaction->type === 'masuk')
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Kas Masuk</span>
                                    @else
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Kas Keluar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $transaction->category }}</td>
                                <td class="px-4 py-3">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $transaction->description ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $transaction->recorder->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('cash-transactions.edit', $transaction) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('cash-transactions.destroy', $transaction) }}" onsubmit="return confirm('Hapus transaksi kas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi kas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>