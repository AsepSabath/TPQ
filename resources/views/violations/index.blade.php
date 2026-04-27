<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pelanggaran dan Pembinaan</h2>
            <a href="{{ route('violations.create') }}" class="w-full sm:w-auto text-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Input Pelanggaran</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama/NIS" class="rounded-md border-gray-300">

                    <select name="level" class="rounded-md border-gray-300">
                        <option value="">Semua Level</option>
                        <option value="ringan" @selected($level === 'ringan')>Ringan</option>
                        <option value="sedang" @selected($level === 'sedang')>Sedang</option>
                        <option value="berat" @selected($level === 'berat')>Berat</option>
                    </select>

                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="rounded-md border-gray-300">
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="rounded-md border-gray-300">

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Santri</th>
                            <th class="px-4 py-3">Level</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Tindakan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($violations as $violation)
                            <tr class="border-t align-top">
                                <td class="px-4 py-3">{{ $violation->incident_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $violation->santri->full_name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ ucfirst($violation->level) }}</td>
                                <td class="px-4 py-3">{{ $violation->description }}</td>
                                <td class="px-4 py-3">{{ $violation->action_taken ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('violations.edit', $violation) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('violations.destroy', $violation) }}" onsubmit="return confirm('Hapus data pelanggaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data pelanggaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $violations->links() }}</div>
        </div>
    </div>
</x-app-layout>
