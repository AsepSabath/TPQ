<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Nilai Santri</h2>
            <a href="{{ route('grades.create') }}" class="w-full sm:w-auto text-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Input Nilai</a>
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIS" class="rounded-md border-gray-300">
                    <input type="text" name="academic_year" value="{{ $academicYear }}" placeholder="Tahun ajaran" class="rounded-md border-gray-300">
                    <select name="semester" class="rounded-md border-gray-300">
                        <option value="">Semua Semester</option>
                        <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($semester === 'genap')>Genap</option>
                    </select>
                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Santri</th>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Tahun/Semester</th>
                            <th class="px-4 py-3">Nilai</th>
                            <th class="px-4 py-3">Guru Input</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($grades as $grade)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $grade->santri->full_name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $grade->subject->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $grade->academicClass->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $grade->academic_year }} / {{ ucfirst($grade->semester) }}</td>
                                <td class="px-4 py-3 font-semibold">{{ number_format($grade->score, 2) }}</td>
                                <td class="px-4 py-3">{{ $grade->teacher->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('grades.edit', $grade) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('grades.destroy', $grade) }}" onsubmit="return confirm('Hapus data nilai ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada data nilai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $grades->links() }}</div>
        </div>
    </div>
</x-app-layout>
