<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Grade;
use App\Models\Santri;
use App\Models\Subject;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $academicYear = trim((string) $request->query('academic_year', ''));
        $semester = trim((string) $request->query('semester', ''));

        $query = Grade::query()
            ->with([
                'santri:id,nis,full_name',
                'subject:id,name,code',
                'academicClass:id,name,academic_year',
                'teacher:id,name',
            ])
            ->latest();

        if ($search !== '') {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($academicYear !== '') {
            $query->where('academic_year', $academicYear);
        }

        if (in_array($semester, ['ganjil', 'genap'], true)) {
            $query->where('semester', $semester);
        }

        $grades = $query->paginate(15)->withQueryString();

        return view('grades.index', [
            'grades' => $grades,
            'search' => $search,
            'academicYear' => $academicYear,
            'semester' => $semester,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('grades.create', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'academic_class_id' => ['nullable', 'exists:academic_classes,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'score' => ['required', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = Grade::query()
            ->where('santri_id', $validated['santri_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('academic_year', $validated['academic_year'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'subject_id' => 'Nilai untuk santri, mapel, dan semester ini sudah ada.',
            ]);
        }

        $validated['teacher_id'] = auth()->id();

        $grade = Grade::query()->create($validated);

        AuditLogger::log('grade', 'create', $grade, null, $grade->toArray());

        return redirect()
            ->route('grades.index')
            ->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grade $grade)
    {
        return redirect()->route('grades.edit', $grade);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grade $grade)
    {
        return view('grades.edit', array_merge($this->formData(), [
            'grade' => $grade,
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'academic_class_id' => ['nullable', 'exists:academic_classes,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'score' => ['required', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = Grade::query()
            ->where('id', '!=', $grade->id)
            ->where('santri_id', $validated['santri_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('academic_year', $validated['academic_year'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'subject_id' => 'Nilai untuk santri, mapel, dan semester ini sudah ada.',
            ]);
        }

        $before = $grade->toArray();
        $grade->update($validated);

        AuditLogger::log('grade', 'update', $grade, $before, $grade->fresh()->toArray());

        return redirect()
            ->route('grades.index')
            ->with('success', 'Nilai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grade $grade)
    {
        $before = $grade->toArray();
        $grade->delete();

        AuditLogger::log('grade', 'delete', $grade, $before, null);

        return redirect()
            ->route('grades.index')
            ->with('success', 'Nilai berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'santris' => Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'classes' => AcademicClass::query()->orderByDesc('academic_year')->orderBy('name')->get(['id', 'name', 'academic_year']),
        ];
    }
}
