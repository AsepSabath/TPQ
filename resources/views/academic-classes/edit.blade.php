<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Kelas</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('academic-classes.update', $academicClass) }}">
                    @method('PUT')
                    @include('academic-classes._form', ['submitLabel' => 'Perbarui'])
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Tambah Santri ke Kelas</h3>

                <form method="POST" action="{{ route('academic-classes.enrollments.store', $academicClass) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    @csrf

                    <div class="md:col-span-2">
                        <label for="santri_id" class="block text-sm text-gray-700 mb-1">Santri</label>
                        <select id="santri_id" name="santri_id" class="w-full rounded-md border-gray-300" required>
                            <option value="">Pilih santri</option>
                            @foreach ($santris as $santri)
                                <option value="{{ $santri->id }}" @selected((string) old('santri_id') === (string) $santri->id)>
                                    {{ $santri->full_name }} ({{ $santri->nis }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="semester" class="block text-sm text-gray-700 mb-1">Semester</label>
                        <select id="semester" name="semester" class="w-full rounded-md border-gray-300" required>
                            <option value="ganjil" @selected(old('semester') === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected(old('semester') === 'genap')>Genap</option>
                        </select>
                    </div>

                    <div>
                        <label for="started_at" class="block text-sm text-gray-700 mb-1">Tanggal Masuk (opsional)</label>
                        <input id="started_at" type="date" name="started_at" value="{{ old('started_at') }}" class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="md:col-span-4">
                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Tambah ke Kelas</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
                <div class="px-6 pt-6">
                    <h3 class="text-base font-semibold text-gray-900">Daftar Santri di Kelas</h3>
                </div>

                <table class="min-w-full text-sm mt-4">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Nama Santri</th>
                            <th class="px-4 py-3">Status Santri</th>
                            <th class="px-4 py-3">Semester</th>
                            <th class="px-4 py-3">Tanggal Masuk</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($academicClass->enrollments as $enrollment)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $enrollment->santri->nis ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $enrollment->santri->full_name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $enrollment->santri->status ?? '-' }}</td>
                                <td class="px-4 py-3">{{ ucfirst($enrollment->semester) }}</td>
                                <td class="px-4 py-3">{{ $enrollment->started_at?->format('d-m-Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end">
                                        <form method="POST" action="{{ route('academic-classes.enrollments.destroy', [$academicClass, $enrollment]) }}" onsubmit="return confirm('Hapus santri dari kelas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-700 hover:text-rose-900">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada santri di kelas ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
