<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Santri</h2>
            <a href="{{ route('santri.create') }}" class="w-full sm:w-auto text-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Tambah Santri
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIS" class="rounded-md border-gray-300">

                    <select name="status" class="rounded-md border-gray-300">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                        <option value="cuti" @selected($status === 'cuti')>Cuti</option>
                        <option value="lulus" @selected($status === 'lulus')>Lulus</option>
                        <option value="pindah" @selected($status === 'pindah')>Pindah</option>
                        <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                    </select>

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                    <a href="{{ route('santri.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 text-center hover:bg-gray-50">Reset</a>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">JK</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($santris as $santri)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $santri->nis }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $santri->full_name }}</td>
                                <td class="px-4 py-3">{{ $santri->gender }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($santri->status) }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $santri->phone ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('santri.show', $santri) }}" class="text-sky-700 hover:text-sky-900">Detail</a>
                                        <a href="{{ route('santri.edit', $santri) }}" class="text-amber-700 hover:text-amber-900">Edit</a>
                                        <form method="POST" action="{{ route('santri.destroy', $santri) }}" onsubmit="return confirm('Hapus data santri ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data santri.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $santris->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
