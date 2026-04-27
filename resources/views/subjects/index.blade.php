<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mata Pelajaran</h2>
            <a href="{{ route('subjects.create') }}" class="w-full sm:w-auto text-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Tambah Mapel</a>
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kode mapel" class="rounded-md border-gray-300">

                    <select name="active" class="rounded-md border-gray-300">
                        <option value="">Semua Status</option>
                        <option value="1" @selected($active === '1')>Aktif</option>
                        <option value="0" @selected($active === '0')>Nonaktif</option>
                    </select>

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">JP</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subjects as $subject)
                            <tr class="border-t">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $subject->name }}</td>
                                <td class="px-4 py-3">{{ $subject->code ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $subject->teacher->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $subject->credit_hours ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $subject->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $subject->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('subjects.edit', $subject) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus mapel ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data mata pelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $subjects->links() }}</div>
        </div>
    </div>
</x-app-layout>
