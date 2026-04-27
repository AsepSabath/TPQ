<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Enrollment;
use App\Models\Santri;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $year = trim((string) $request->query('year', ''));

        $query = AcademicClass::query()
            ->with('homeroomTeacher:id,name')
            ->latest('academic_year')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('homeroomTeacher', function ($teacherQuery) use ($search) {
                        $teacherQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($year !== '') {
            $query->where('academic_year', $year);
        }

        $classes = $query->paginate(15)->withQueryString();

        return view('academic-classes.index', [
            'classes' => $classes,
            'search' => $search,
            'year' => $year,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teachers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('academic-classes.create', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = AcademicClass::query()
            ->where('name', $validated['name'])
            ->where('academic_year', $validated['academic_year'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Kelas untuk tahun ajaran tersebut sudah ada.']);
        }

        $academicClass = AcademicClass::query()->create($validated);

        AuditLogger::log('academic_class', 'create', $academicClass, null, $academicClass->toArray());

        return redirect()
            ->route('academic-classes.index')
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicClass $academicClass)
    {
        $academicClass->load([
            'homeroomTeacher:id,name',
            'enrollments' => function ($query) {
                $query->with('santri:id,nis,full_name,status')
                    ->orderByRaw("CASE WHEN semester = 'ganjil' THEN 0 ELSE 1 END")
                    ->orderBy('santri_id');
            },
        ]);

        return view('academic-classes.show', [
            'academicClass' => $academicClass,
        ]);
    }

    public function storeEnrollment(Request $request, AcademicClass $academicClass)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'started_at' => ['nullable', 'date'],
        ]);

        $existingEnrollment = Enrollment::query()
            ->where('santri_id', $validated['santri_id'])
            ->where('academic_year', $academicClass->academic_year)
            ->where('semester', $validated['semester'])
            ->with('academicClass:id,name')
            ->first();

        if ($existingEnrollment !== null) {
            $message = $existingEnrollment->academic_class_id === $academicClass->id
                ? 'Santri sudah terdaftar di kelas ini untuk tahun ajaran dan semester tersebut.'
                : 'Santri sudah terdaftar di kelas '.$existingEnrollment->academicClass->name.' untuk tahun ajaran dan semester tersebut.';

            return redirect()
                ->route('academic-classes.edit', $academicClass)
                ->withErrors(['enrollment' => $message])
                ->withInput();
        }

        $enrollment = $academicClass->enrollments()->create([
            'santri_id' => $validated['santri_id'],
            'academic_year' => $academicClass->academic_year,
            'semester' => $validated['semester'],
            'status' => 'aktif',
            'started_at' => $validated['started_at'] ?? null,
        ]);

        AuditLogger::log('enrollment', 'create', $enrollment, null, $enrollment->toArray());

        return redirect()
            ->route('academic-classes.edit', $academicClass)
            ->with('success', 'Santri berhasil ditambahkan ke kelas.');
    }

    public function destroyEnrollment(AcademicClass $academicClass, Enrollment $enrollment)
    {
        if ($enrollment->academic_class_id !== $academicClass->id) {
            abort(404);
        }

        $before = $enrollment->toArray();
        $enrollment->delete();

        AuditLogger::log('enrollment', 'delete', $enrollment, $before, null);

        return redirect()
            ->route('academic-classes.edit', $academicClass)
            ->with('success', 'Santri berhasil dihapus dari kelas.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicClass $academicClass)
    {
        $academicClass->load([
            'enrollments' => function ($query) {
                $query->with('santri:id,nis,full_name,status')
                    ->orderByRaw("CASE WHEN semester = 'ganjil' THEN 0 ELSE 1 END")
                    ->orderBy('santri_id');
            },
        ]);

        $teachers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $santris = Santri::query()
            ->orderBy('full_name')
            ->get(['id', 'nis', 'full_name', 'status']);

        return view('academic-classes.edit', [
            'academicClass' => $academicClass,
            'teachers' => $teachers,
            'santris' => $santris,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicClass $academicClass)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = AcademicClass::query()
            ->where('id', '!=', $academicClass->id)
            ->where('name', $validated['name'])
            ->where('academic_year', $validated['academic_year'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Kelas untuk tahun ajaran tersebut sudah ada.']);
        }

        $before = $academicClass->toArray();
        $academicClass->update($validated);

        AuditLogger::log('academic_class', 'update', $academicClass, $before, $academicClass->fresh()->toArray());

        return redirect()
            ->route('academic-classes.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicClass $academicClass)
    {
        if ($academicClass->enrollments()->exists() || $academicClass->grades()->exists()) {
            return back()->withErrors([
                'class' => 'Kelas tidak bisa dihapus karena masih dipakai pada data lain.',
            ]);
        }

        $before = $academicClass->toArray();
        $academicClass->delete();

        AuditLogger::log('academic_class', 'delete', $academicClass, $before, null);

        return redirect()
            ->route('academic-classes.index')
            ->with('success', 'Data kelas berhasil dihapus.');
    }
}
