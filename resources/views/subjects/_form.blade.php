@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nama Mapel</label>
        <input type="text" name="name" value="{{ old('name', $subject->name ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Kode Mapel</label>
        <input type="text" name="code" value="{{ old('code', $subject->code ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Guru Pengampu</label>
        <select name="teacher_id" class="mt-1 w-full rounded-md border-gray-300">
            <option value="">Pilih guru</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((int) old('teacher_id', $subject->teacher_id ?? 0) === $teacher->id)>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Jam Pelajaran</label>
        <input type="number" name="credit_hours" min="1" max="12" value="{{ old('credit_hours', $subject->credit_hours ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>
</div>

<div class="mt-4">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-emerald-600" @checked((bool) old('is_active', $subject->is_active ?? true))>
        Mapel Aktif
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
