@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Santri</label>
        <select name="santri_id" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="">Pilih santri</option>
            @foreach ($santris as $santri)
                <option value="{{ $santri->id }}" @selected((int) old('santri_id', $violation->santri_id ?? 0) === $santri->id)>
                    {{ $santri->nis }} - {{ $santri->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tanggal Kejadian</label>
        <input type="date" name="incident_date" value="{{ old('incident_date', isset($violation?->incident_date) ? $violation->incident_date->toDateString() : now()->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tingkat Pelanggaran</label>
        @php($levelValue = old('level', $violation->level ?? 'ringan'))
        <select name="level" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="ringan" @selected($levelValue === 'ringan')>Ringan</option>
            <option value="sedang" @selected($levelValue === 'sedang')>Sedang</option>
            <option value="berat" @selected($levelValue === 'berat')>Berat</option>
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Deskripsi Pelanggaran</label>
    <textarea name="description" rows="3" class="mt-1 w-full rounded-md border-gray-300" required>{{ old('description', $violation->description ?? '') }}</textarea>
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Tindakan Pembinaan</label>
    <textarea name="action_taken" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('action_taken', $violation->action_taken ?? '') }}</textarea>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('violations.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
