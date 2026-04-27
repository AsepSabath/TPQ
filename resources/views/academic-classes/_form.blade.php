@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nama Kelas</label>
        <input type="text" name="name" value="{{ old('name', $academicClass->name ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
        <input type="text" name="academic_year" value="{{ old('academic_year', $academicClass->academic_year ?? now()->year.'/'.(now()->year + 1)) }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Wali Kelas</label>
        <select name="homeroom_teacher_id" class="mt-1 w-full rounded-md border-gray-300">
            <option value="">Pilih wali kelas</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((int) old('homeroom_teacher_id', $academicClass->homeroom_teacher_id ?? 0) === $teacher->id)>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Catatan</label>
    <textarea name="notes" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes', $academicClass->notes ?? '') }}</textarea>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('academic-classes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
