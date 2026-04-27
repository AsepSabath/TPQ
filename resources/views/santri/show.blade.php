<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Santri</h2>
            <a href="{{ route('santri.edit', $santri) }}" class="rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Edit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                @php
                    $primaryGuardian = $santri->guardians
                        ->sortByDesc('is_primary')
                        ->first();
                @endphp
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">NIS</dt>
                        <dd class="font-semibold text-gray-900">{{ $santri->nis }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Nama Lengkap</dt>
                        <dd class="font-semibold text-gray-900">{{ $santri->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Jenis Kelamin</dt>
                        <dd class="font-semibold text-gray-900">{{ $santri->gender }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">No HP</dt>
                        <dd class="font-semibold text-gray-900">{{ $santri->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Nama Orang Tua/Wali</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ $primaryGuardian?->name ?? '-' }}
                            @if ($primaryGuardian)
                                <span class="text-xs font-normal text-gray-500">({{ ucfirst($primaryGuardian->relation_type) }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tempat, Tanggal Lahir</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ $santri->birth_place ?? '-' }}, {{ $santri->birth_date?->format('d M Y') ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-semibold text-gray-900">{{ ucfirst($santri->status) }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">Alamat</dt>
                        <dd class="font-semibold text-gray-900">{{ $santri->address }}</dd>
                    </div>
                </dl>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900">Early Warning</h3>
                    @if ($santri->riskAlert)
                        <div class="mt-3 space-y-2 text-sm">
                            <p><span class="text-gray-500">Level:</span> <span class="font-semibold">{{ ucfirst($santri->riskAlert->risk_level) }}</span></p>
                            <p><span class="text-gray-500">Skor:</span> <span class="font-semibold">{{ number_format($santri->riskAlert->risk_score, 2) }}</span></p>
                            <p><span class="text-gray-500">Absensi:</span> <span class="font-semibold">{{ $santri->riskAlert->attendance_rate !== null ? number_format($santri->riskAlert->attendance_rate, 2).'%' : '-' }}</span></p>
                            <p><span class="text-gray-500">Rata Nilai:</span> <span class="font-semibold">{{ $santri->riskAlert->avg_grade !== null ? number_format($santri->riskAlert->avg_grade, 2) : '-' }}</span></p>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500">Belum ada data early warning. Jalankan perhitungan risiko terlebih dahulu.</p>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900">Ringkasan SPP</h3>
                    @php
                        $runningInvoices = $santri->sppInvoices->where('period_status', 'berjalan');
                        $futureInvoices = $santri->sppInvoices->where('period_status', 'belum_berjalan');
                        $totalSpp = $runningInvoices->sum('amount');
                        $totalBayar = $runningInvoices->sum('paid_amount');
                    @endphp
                    <div class="mt-3 space-y-2 text-sm">
                        <p><span class="text-gray-500">Tagihan Berjalan:</span> <span class="font-semibold">Rp {{ number_format($totalSpp, 0, ',', '.') }}</span></p>
                        <p><span class="text-gray-500">Tagihan Belum Berjalan:</span> <span class="font-semibold">{{ number_format($futureInvoices->count()) }} periode</span></p>
                        <p><span class="text-gray-500">Total Dibayar:</span> <span class="font-semibold">Rp {{ number_format($totalBayar, 0, ',', '.') }}</span></p>
                        <p><span class="text-gray-500">Sisa Tagihan Berjalan:</span> <span class="font-semibold">Rp {{ number_format($totalSpp - $totalBayar, 0, ',', '.') }}</span></p>
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('santri.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali ke daftar santri</a>
            </div>
        </div>
    </div>
</x-app-layout>
