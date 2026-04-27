<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Kepala Madrasah
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Santri Aktif</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalSantriAktif) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Tagihan Belum Lunas (Berjalan)</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ number_format($openInvoices) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Kas Masuk Bulan Ini</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">Rp {{ number_format($kasMasukBulanIni, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">Kas Keluar Bulan Ini</p>
                    <p class="mt-2 text-2xl font-bold text-rose-600">Rp {{ number_format($kasKeluarBulanIni, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-900">Absensi Hari Ini</h3>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-md bg-emerald-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-emerald-700">Hadir</p>
                            <p class="text-xl font-bold text-emerald-800">{{ number_format($attendanceToday['hadir'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-md bg-amber-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-amber-700">Izin</p>
                            <p class="text-xl font-bold text-amber-800">{{ number_format($attendanceToday['izin'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-md bg-sky-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-sky-700">Sakit</p>
                            <p class="text-xl font-bold text-sky-800">{{ number_format($attendanceToday['sakit'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-md bg-rose-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-rose-700">Alpha</p>
                            <p class="text-xl font-bold text-rose-800">{{ number_format($attendanceToday['alpha'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-900">Total Tunggakan SPP</h3>
                    <p class="mt-3 text-3xl font-bold text-amber-700">Rp {{ number_format($outstandingSpp, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-gray-500">
                        Nilai ini hanya menghitung tagihan pada periode yang sudah berjalan dengan status belum lunas dan cicilan.
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-semibold text-gray-900">Early Warning Santri Berisiko Tinggi</h3>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500 border-b">
                            <tr>
                                <th class="py-2 pe-4">NIS</th>
                                <th class="py-2 pe-4">Nama</th>
                                <th class="py-2 pe-4">Skor Risiko</th>
                                <th class="py-2 pe-4">Absensi</th>
                                <th class="py-2 pe-4">Rata Nilai</th>
                                <th class="py-2 pe-4">Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($highRiskSantri as $risk)
                                <tr class="border-b last:border-0">
                                    <td class="py-2 pe-4">{{ $risk->santri->nis ?? '-' }}</td>
                                    <td class="py-2 pe-4">{{ $risk->santri->full_name ?? '-' }}</td>
                                    <td class="py-2 pe-4 font-semibold text-rose-700">{{ number_format($risk->risk_score, 2) }}</td>
                                    <td class="py-2 pe-4">{{ $risk->attendance_rate !== null ? number_format($risk->attendance_rate, 2).'%' : '-' }}</td>
                                    <td class="py-2 pe-4">{{ $risk->avg_grade !== null ? number_format($risk->avg_grade, 2) : '-' }}</td>
                                    <td class="py-2 pe-4">{{ number_format($risk->unpaid_months) }} bulan</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-gray-500">Belum ada santri dengan risiko tinggi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
