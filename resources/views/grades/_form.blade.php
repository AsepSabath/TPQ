@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Santri</label>
        <select name="santri_id" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="">Pilih santri</option>
            @foreach ($santris as $santri)
                <option value="{{ $santri->id }}" @selected((int) old('santri_id', $grade->santri_id ?? 0) === $santri->id)>
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
                <option value="{{ $subject->id }}" @selected((int) old('subject_id', $grade->subject_id ?? 0) === $subject->id)>
                    {{ $subject->name }} {{ $subject->code ? '('.$subject->code.')' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Kelas</label>
        <select name="academic_class_id" class="mt-1 w-full rounded-md border-gray-300">
            <option value="">Pilih kelas</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected((int) old('academic_class_id', $grade->academic_class_id ?? 0) === $class->id)>
                    {{ $class->name }} - {{ $class->academic_year }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
        <input type="text" name="academic_year" value="{{ old('academic_year', $grade->academic_year ?? now()->year.'/'.(now()->year + 1)) }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Semester</label>
        @php($semesterValue = old('semester', $grade->semester ?? 'ganjil'))
        <select name="semester" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="ganjil" @selected($semesterValue === 'ganjil')>Ganjil</option>
            <option value="genap" @selected($semesterValue === 'genap')>Genap</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nilai (0-100)</label>
        <input type="number" step="0.01" min="0" max="100" name="score" value="{{ old('score', $grade->score ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Catatan</label>
    <textarea name="notes" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes', $grade->notes ?? '') }}</textarea>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('grades.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
