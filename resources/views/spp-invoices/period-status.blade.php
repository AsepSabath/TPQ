<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Status Periode SPP</h2>
            <a href="{{ route('spp-invoices.index') }}" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Kembali ke Tagihan
            </a>
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

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <p class="text-sm text-gray-600">
                    Gunakan menu ini untuk menentukan apakah suatu periode sudah <span class="font-semibold text-emerald-700">berjalan</span> atau masih <span class="font-semibold text-amber-700">belum berjalan</span>.
                    Perubahan ini memengaruhi perhitungan tunggakan.
                </p>
            </div>

            <div class="space-y-3">
                @forelse ($periodGroups as $periodGroup)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $periodGroup['label'] }}</h3>
                                <p class="text-xs text-gray-500">
                                    {{ $periodGroup['total_invoices'] }} tagihan | {{ $periodGroup['running_count'] }} berjalan | {{ $periodGroup['not_started_count'] }} belum berjalan
                                </p>
                            </div>

                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $periodGroup['period_status'] === 'berjalan' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $periodGroup['period_status'] === 'berjalan' ? 'Berjalan' : 'Belum Berjalan' }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('spp-invoices.period-status.update') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="month" value="{{ $periodGroup['month'] }}">
                            <input type="hidden" name="year" value="{{ $periodGroup['year'] }}">

                            <div class="w-full sm:max-w-xs">
                                <label class="block text-sm font-medium text-gray-700">Status Periode</label>
                                <select name="period_status" class="mt-1 w-full rounded-md border-gray-300" required>
                                    <option value="berjalan" @selected($periodGroup['period_status'] === 'berjalan')>Berjalan</option>
                                    <option value="belum_berjalan" @selected($periodGroup['period_status'] === 'belum_berjalan')>Belum Berjalan</option>
                                </select>
                            </div>

                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                Simpan Status
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 text-center text-gray-500">
                        Belum ada tagihan SPP untuk diatur status periodenya.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>