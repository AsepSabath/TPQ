<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Semester</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input type="text" name="academic_year" value="{{ $academicYear }}" placeholder="Tahun ajaran" class="rounded-md border-gray-300">

                    <select name="semester" class="rounded-md border-gray-300">
                        <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($semester === 'genap')>Genap</option>
                    </select>

                    <select name="santri_id" class="rounded-md border-gray-300">
                        <option value="">Semua Santri</option>
                        @foreach ($santris as $santri)
                            <option value="{{ $santri->id }}" @selected((string) $santriId === (string) $santri->id)>
                                {{ $santri->nis }} - {{ $santri->full_name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Tampilkan</button>

                    <a href="{{ route('reports.semester', ['academic_year' => $academicYear, 'semester' => $semester, 'santri_id' => $santriId, 'export' => 'pdf']) }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white text-center hover:bg-emerald-700">
                        Export PDF
                    </a>
                </form>
            </div>

            @forelse ($grouped as $row)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <h3 class="font-semibold text-gray-900">
                            {{ $row['santri']->full_name ?? '-' }}
                            <span class="text-sm font-normal text-gray-500">({{ $row['santri']->nis ?? '-' }})</span>
                        </h3>
                        <p class="text-sm font-semibold text-slate-700">Rata-rata: {{ number_format($row['average'], 2) }}</p>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-500 border-b">
                                <tr>
                                    <th class="py-2 pe-4">Mapel</th>
                                    <th class="py-2 pe-4">Nilai</th>
                                    <th class="py-2 pe-4">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($row['grades'] as $grade)
                                    <tr class="border-b last:border-0">
                                        <td class="py-2 pe-4">{{ $grade->subject->name ?? '-' }}</td>
                                        <td class="py-2 pe-4 font-semibold">{{ number_format($grade->score, 2) }}</td>
                                        <td class="py-2 pe-4">{{ $grade->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 text-center text-gray-500">
                    Tidak ada data nilai untuk filter yang dipilih.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
