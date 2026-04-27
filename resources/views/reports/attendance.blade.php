<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Absensi Santri</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-md border-gray-300">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-md border-gray-300">
                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                    <a href="{{ route('reports.attendance', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white text-center hover:bg-emerald-700">Export CSV</a>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Total Record</p>
                    <p class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($totalRecords) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Hadir</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-2">{{ number_format($summary['hadir'] ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Izin</p>
                    <p class="text-2xl font-bold text-amber-700 mt-2">{{ number_format($summary['izin'] ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Sakit</p>
                    <p class="text-2xl font-bold text-sky-700 mt-2">{{ number_format($summary['sakit'] ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <p class="text-sm text-gray-500">Alpha</p>
                    <p class="text-2xl font-bold text-rose-700 mt-2">{{ number_format($summary['alpha'] ?? 0) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <h3 class="font-semibold text-gray-900">Santri Alpha Terbanyak</h3>
                    <div class="mt-3 space-y-2 text-sm">
                        @forelse ($topAlphaSantri as $alpha)
                            <div class="rounded-md bg-gray-50 px-3 py-2 flex justify-between gap-3">
                                <span>{{ $alpha->santri->full_name ?? '-' }}</span>
                                <span class="font-semibold">{{ number_format($alpha->total_alpha) }} kali</span>
                            </div>
                        @empty
                            <p class="text-gray-500">Tidak ada data alpha pada periode ini.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 overflow-x-auto">
                    <h3 class="font-semibold text-gray-900">Record Absensi Terbaru</h3>
                    <table class="min-w-full text-sm mt-3">
                        <thead class="text-left text-gray-500 border-b">
                            <tr>
                                <th class="py-2 pe-4">Tanggal</th>
                                <th class="py-2 pe-4">Santri</th>
                                <th class="py-2 pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAttendances as $attendance)
                                <tr class="border-b last:border-0">
                                    <td class="py-2 pe-4">{{ $attendance->attendance_date->format('d M Y') }}</td>
                                    <td class="py-2 pe-4">{{ $attendance->santri->full_name ?? '-' }}</td>
                                    <td class="py-2 pe-4">{{ ucfirst($attendance->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">Tidak ada record absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
