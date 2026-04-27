@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Santri</label>
        <select name="santri_id" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="">Pilih santri</option>
            @foreach ($santris as $santri)
                <option value="{{ $santri->id }}" @selected((int) old('santri_id', $attendance->santri_id ?? 0) === $santri->id)>
                    {{ $santri->nis }} - {{ $santri->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
        <select name="subject_id" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="">Pilih mapel</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((int) old('subject_id', $attendance->subject_id ?? 0) === $subject->id)>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
        <input type="date" name="attendance_date" value="{{ old('attendance_date', isset($attendance?->attendance_date) ? $attendance->attendance_date->toDateString() : now()->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        @php($statusValue = old('status', $attendance->status ?? 'hadir'))
        <select name="status" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="hadir" @selected($statusValue === 'hadir')>Hadir</option>
            <option value="izin" @selected($statusValue === 'izin')>Izin</option>
            <option value="sakit" @selected($statusValue === 'sakit')>Sakit</option>
            <option value="alpha" @selected($statusValue === 'alpha')>Alpha</option>
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Catatan</label>
    <textarea name="notes" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes', $attendance->notes ?? '') }}</textarea>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('lesson-attendances.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
