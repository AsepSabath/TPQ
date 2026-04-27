<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Absensi Harian</h2>
            <a href="{{ route('daily-attendances.create', ['date' => $date]) }}" class="w-full sm:w-auto text-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Input Absensi Harian
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
                    <input type="date" name="date" value="{{ $date }}" class="rounded-md border-gray-300">

                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIS" class="rounded-md border-gray-300">

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                    <a href="{{ route('daily-attendances.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 text-center hover:bg-gray-50">Reset</a>
                </form>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse ($attendances as $attendance)
                    @php($status = $attendance->status)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $attendance->santri->full_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">NIS: {{ $attendance->santri->nis ?? '-' }}</p>
                            </div>
                            <span @class([
                                'rounded-full px-2.5 py-1 text-xs font-semibold',
                                'bg-emerald-100 text-emerald-700' => $status === 'hadir',
                                'bg-amber-100 text-amber-700' => $status === 'izin',
                                'bg-sky-100 text-sky-700' => $status === 'sakit',
                                'bg-rose-100 text-rose-700' => $status === 'alpha',
                            ])>
                                {{ ucfirst($status) }}
                            </span>
                        </div>

                        <div class="mt-3 space-y-1 text-sm text-gray-600">
                            <p>Tanggal: {{ $attendance->attendance_date?->format('d/m/Y') ?? '-' }}</p>
                            <p>Pencatat: {{ $attendance->recorder->name ?? '-' }}</p>
                            @if ($attendance->notes)
                                <p>Catatan: {{ $attendance->notes }}</p>
                            @endif
                        </div>

                        <div class="mt-3 flex items-center gap-4 text-sm font-semibold">
                            <a href="{{ route('daily-attendances.edit', $attendance) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                            <form method="POST" action="{{ route('daily-attendances.destroy', $attendance) }}" onsubmit="return confirm('Hapus data absensi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-4 py-6 text-center text-gray-500">
                        Belum ada data absensi pada filter ini.
                    </div>
                @endforelse
            </div>

            <div class="hidden md:block bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Santri</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Catatan</th>
                            <th class="px-4 py-3">Pencatat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $attendance->attendance_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $attendance->santri->nis ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $attendance->santri->full_name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php($status = $attendance->status)
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-700' => $status === 'hadir',
                                        'bg-amber-100 text-amber-700' => $status === 'izin',
                                        'bg-sky-100 text-sky-700' => $status === 'sakit',
                                        'bg-rose-100 text-rose-700' => $status === 'alpha',
                                    ])>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $attendance->notes ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $attendance->recorder->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('daily-attendances.edit', $attendance) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('daily-attendances.destroy', $attendance) }}" onsubmit="return confirm('Hapus data absensi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada data absensi pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</x-app-layout>