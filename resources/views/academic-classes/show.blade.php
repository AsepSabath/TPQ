<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Kelas</h2>
            <a href="{{ route('academic-classes.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Kembali</a>
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

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama Kelas</p>
                        <p class="font-semibold text-gray-900">{{ $academicClass->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Tahun Ajaran</p>
                        <p class="font-semibold text-gray-900">{{ $academicClass->academic_year }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Wali Kelas</p>
                        <p class="font-semibold text-gray-900">{{ $academicClass->homeroomTeacher->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Catatan</p>
                        <p class="font-semibold text-gray-900">{{ $academicClass->notes ?: '-' }}</p>
                    </div>
                </div>
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
