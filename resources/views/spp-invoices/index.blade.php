<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tagihan SPP</h2>
            <a href="{{ route('spp-invoices.period-status.index') }}" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Status Periode
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

            <div id="bulk-create" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Buat Tagihan Massal</h3>
                <p class="mt-1 text-xs text-gray-500">Tagihan akan dibuat untuk semua santri aktif. Bisa dibuat untuk beberapa periode ke depan sekaligus. Periode yang sudah ada akan dilewati otomatis.</p>

                <form method="POST" action="{{ route('spp-invoices.bulk.store') }}" class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-3">
                    @csrf

                    <input type="number" name="month" value="{{ old('month', now()->month) }}" min="1" max="12" placeholder="Bulan" class="rounded-md border-gray-300" required>

                    <input type="number" name="year" value="{{ old('year', now()->year) }}" min="2000" max="2100" placeholder="Tahun" class="rounded-md border-gray-300" required>

                    <input type="number" name="period_count" value="{{ old('period_count', 1) }}" min="1" max="24" placeholder="Jumlah Periode" class="rounded-md border-gray-300" required>

                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" placeholder="Nominal" class="rounded-md border-gray-300" required>

                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="rounded-md border-gray-300" required>

                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan (opsional)" class="rounded-md border-gray-300">

                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 md:col-span-6">
                        Buat Tagihan Semua Santri
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIS" class="rounded-md border-gray-300">

                    <input type="number" name="month" value="{{ $month }}" min="1" max="12" placeholder="Bulan" class="rounded-md border-gray-300">

                    <input type="number" name="year" value="{{ $year }}" min="2000" max="2100" placeholder="Tahun" class="rounded-md border-gray-300">

                    <select name="status" class="rounded-md border-gray-300">
                        <option value="">Semua Status</option>
                        <option value="belum_lunas" @selected($status === 'belum_lunas')>Belum Lunas</option>
                        <option value="cicilan" @selected($status === 'cicilan')>Cicilan</option>
                        <option value="lunas" @selected($status === 'lunas')>Lunas</option>
                    </select>

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                </form>
            </div>

            @php
                $groupedInvoices = $invoices
                    ->getCollection()
                    ->groupBy(fn ($invoice) => sprintf('%04d-%02d', $invoice->year, $invoice->month));
            @endphp

            <div class="space-y-3">
                @forelse ($groupedInvoices as $periodKey => $periodInvoices)
                    @php
                        [$periodYear, $periodMonth] = explode('-', $periodKey);
                        $periodDate = \Carbon\Carbon::create((int) $periodYear, (int) $periodMonth, 1);
                        $periodStatus = $periodInvoices->first()?->period_status ?? 'berjalan';
                    @endphp

                    <details class="bg-white rounded-lg shadow-sm border border-gray-100 group">
                        <summary class="list-none cursor-pointer select-none px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-800">Periode {{ $periodDate->format('F Y') }}</p>
                                    <p class="text-xs text-slate-500">Klik untuk lihat detail tagihan</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="rounded-full {{ $periodStatus === 'berjalan' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-2.5 py-1 text-xs font-semibold">
                                        {{ $periodStatus === 'berjalan' ? 'Berjalan' : 'Belum Berjalan' }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ count($periodInvoices) }} tagihan</span>
                                    <span class="text-slate-400 transition-transform group-open:rotate-90">&#9656;</span>
                                </div>
                            </div>
                        </summary>

                        <div class="border-t border-gray-100 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-gray-500 text-left">
                                    <tr>
                                        <th class="px-4 py-3">No Invoice</th>
                                        <th class="px-4 py-3">Santri</th>
                                        <th class="px-4 py-3">Periode</th>
                                        <th class="px-4 py-3">Tagihan</th>
                                        <th class="px-4 py-3">Dibayar</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($periodInvoices as $invoice)
                                        <tr class="border-t">
                                            <td class="px-4 py-3">{{ $invoice->invoice_number }}</td>
                                            <td class="px-4 py-3">{{ $invoice->santri->full_name ?? '-' }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-800">{{ $periodDate->format('F Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ str_pad($invoice->month, 2, '0', STR_PAD_LEFT) }}/{{ $invoice->year }}</div>
                                            </td>
                                            <td class="px-4 py-3">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3">
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ str_replace('_', ' ', ucfirst($invoice->status)) }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex justify-end gap-3">
                                                    <a href="{{ route('spp-invoices.show', $invoice) }}" class="text-sky-700 hover:text-sky-900">Detail</a>
                                                    <a href="{{ route('spp-invoices.edit', $invoice) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                                    <form method="POST" action="{{ route('spp-invoices.destroy', $invoice) }}" onsubmit="return confirm('Hapus tagihan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @empty
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-4 py-6 text-center text-gray-500">
                        Belum ada tagihan SPP.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
