<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Kelas</h2>
            <a href="{{ route('academic-classes.create') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Tambah Kelas</a>
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari kelas atau wali kelas" class="rounded-md border-gray-300">
                    <input type="text" name="year" value="{{ $year }}" placeholder="Tahun ajaran" class="rounded-md border-gray-300">
                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Nama Kelas</th>
                            <th class="px-4 py-3">Tahun Ajaran</th>
                            <th class="px-4 py-3">Wali Kelas</th>
                            <th class="px-4 py-3">Catatan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $class)
                            <tr class="border-t">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $class->name }}</td>
                                <td class="px-4 py-3">{{ $class->academic_year }}</td>
                                <td class="px-4 py-3">{{ $class->homeroomTeacher->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $class->notes ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('academic-classes.show', $class) }}" class="text-sky-700 hover:text-sky-900">Detail</a>
                                        <a href="{{ route('academic-classes.edit', $class) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('academic-classes.destroy', $class) }}" onsubmit="return confirm('Hapus kelas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $classes->links() }}</div>
        </div>
    </div>
</x-app-layout>
