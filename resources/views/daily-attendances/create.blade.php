<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Absensi Harian</h2>
            <p class="text-sm text-gray-500">Mode cepat untuk wali kelas (mobile friendly)</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('daily-attendances.index', ['date' => old('attendance_date', $date)]) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Kembali ke Daftar
                    </a>
                    <button type="button" id="set-all-hadir" class="inline-flex items-center justify-center rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                        Set Semua Hadir
                    </button>
                </div>

                <form method="POST" action="{{ route('daily-attendances.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tanggal Absensi</label>
                        <input type="date" name="attendance_date" value="{{ old('attendance_date', $date) }}" class="mt-1 w-full rounded-md border-gray-300" required>
                    </div>

                    <div class="space-y-3">
                        @forelse ($santris as $index => $santri)
                            @php
                                $existing = $existingAttendances->get($santri->id);
                                $statusValue = old("entries.{$index}.status", $existing->status ?? 'hadir');
                                $notesValue = old("entries.{$index}.notes", $existing->notes ?? '');
                            @endphp

                            <div class="rounded-lg border border-gray-200 p-4">
                                <input type="hidden" name="entries[{{ $index }}][santri_id]" value="{{ $santri->id }}">

                                <div class="mb-3">
                                    <p class="font-semibold text-gray-900">{{ $santri->full_name }}</p>
                                    <p class="text-xs text-gray-500">NIS: {{ $santri->nis }}</p>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $value => $label)
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                class="sr-only attendance-status"
                                                name="entries[{{ $index }}][status]"
                                                value="{{ $value }}"
                                                @checked($statusValue === $value)
                                                required
                                            >
                                            <span class="status-chip block rounded-md border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 transition hover:border-emerald-400 hover:text-emerald-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <label class="block text-xs font-medium text-gray-600">Catatan (opsional)</label>
                                    <textarea name="entries[{{ $index }}][notes]" rows="2" class="mt-1 w-full rounded-md border-gray-300" placeholder="Contoh: datang terlambat 10 menit">{{ $notesValue }}</textarea>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                Belum ada santri aktif.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6 sticky bottom-3">
                        <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                            Simpan Absensi Harian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radioInputs = Array.from(document.querySelectorAll('.attendance-status'));

            function refreshChipState() {
                radioInputs.forEach(function (input) {
                    const chip = input.closest('label')?.querySelector('.status-chip');

                    if (!chip) {
                        return;
                    }

                    chip.classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-700');

                    if (input.checked) {
                        chip.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-700');
                    }
                });
            }

            document.getElementById('set-all-hadir')?.addEventListener('click', function () {
                radioInputs.forEach(function (input) {
                    if (input.value === 'hadir') {
                        input.checked = true;
                    }
                });

                refreshChipState();
            });

            radioInputs.forEach(function (input) {
                input.addEventListener('change', refreshChipState);
            });

            refreshChipState();
        });
    </script>
</x-app-layout>