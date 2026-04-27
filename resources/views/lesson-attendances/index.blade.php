<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Absensi Per Mata Pelajaran</h2>
            <a href="{{ route('lesson-attendances.create') }}" class="w-full sm:w-auto text-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Input Absensi Mapel</a>
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
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="date" name="date" value="{{ $date }}" class="rounded-md border-gray-300">

                    <select name="subject_id" class="rounded-md border-gray-300">
                        <option value="">Semua Mapel</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) $subjectId === (string) $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Santri</th>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Catatan</th>
                            <th class="px-4 py-3">Petugas</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $attendance->attendance_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $attendance->santri->full_name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $attendance->subject->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ ucfirst($attendance->status) }}</td>
                                <td class="px-4 py-3">{{ $attendance->notes ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $attendance->recorder->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('lesson-attendances.edit', $attendance) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('lesson-attendances.destroy', $attendance) }}" onsubmit="return confirm('Hapus absensi mapel ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada data absensi mapel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $attendances->links() }}</div>
        </div>
    </div>
</x-app-layout>
