@csrf

@php
    $primaryGuardian = isset($santri)
        ? $santri->guardians->sortByDesc('is_primary')->first()
        : null;
    $guardianNameValue = old('guardian_name', $primaryGuardian?->name ?? '');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">NIS</label>
        @if (request()->routeIs('santri.create'))
            <input type="text" value="Akan dibuat otomatis saat data disimpan" class="mt-1 w-full rounded-md border-gray-300 bg-gray-100 text-gray-500" disabled>
            <p class="mt-1 text-xs text-gray-500">Nomor induk santri dibuat otomatis dengan format bulan+tahun+urutan, contoh: 4260001.</p>
        @else
            <input type="text" name="nis" value="{{ old('nis', $santri->nis ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nama Orang Tua/Wali</label>
        <input type="text" name="guardian_name" value="{{ $guardianNameValue }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
        <input type="text" name="full_name" value="{{ old('full_name', $santri->full_name ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
        <select name="gender" class="mt-1 w-full rounded-md border-gray-300" required>
            @php($gender = old('gender', $santri->gender ?? ''))
            <option value="">Pilih</option>
            <option value="L" @selected($gender === 'L')>Laki-laki</option>
            <option value="P" @selected($gender === 'P')>Perempuan</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">No HP Santri</label>
        <input type="text" name="phone" value="{{ old('phone', $santri->phone ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
        <input type="text" name="birth_place" value="{{ old('birth_place', $santri->birth_place ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
        <input type="date" name="birth_date" value="{{ old('birth_date', isset($santri?->birth_date) ? $santri->birth_date->toDateString() : '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tanggal Masuk</label>
        <input type="date" name="entry_date" value="{{ old('entry_date', isset($santri?->entry_date) ? $santri->entry_date->toDateString() : '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        @php($statusValue = old('status', $santri->status ?? 'aktif'))
        <select name="status" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="aktif" @selected($statusValue === 'aktif')>Aktif</option>
            <option value="cuti" @selected($statusValue === 'cuti')>Cuti</option>
            <option value="lulus" @selected($statusValue === 'lulus')>Lulus</option>
            <option value="pindah" @selected($statusValue === 'pindah')>Pindah</option>
            <option value="nonaktif" @selected($statusValue === 'nonaktif')>Nonaktif</option>
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Alamat</label>
    <textarea name="address" rows="3" class="mt-1 w-full rounded-md border-gray-300" required>{{ old('address', $santri->address ?? '') }}</textarea>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('santri.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
