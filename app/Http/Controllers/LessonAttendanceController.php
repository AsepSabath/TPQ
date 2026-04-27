<?php

namespace App\Http\Controllers;

use App\Models\LessonAttendance;
use App\Models\Santri;
use App\Models\Subject;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $date = (string) $request->query('date', now()->toDateString());
        $subjectId = (string) $request->query('subject_id', '');

        $query = LessonAttendance::query()
            ->with(['santri:id,nis,full_name', 'subject:id,name', 'recorder:id,name'])
            ->whereDate('attendance_date', $date)
            ->latest('id');

        if (is_numeric($subjectId)) {
            $query->where('subject_id', (int) $subjectId);
        }

        $attendances = $query->paginate(20)->withQueryString();
        $subjects = Subject::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('lesson-attendances.index', [
            'attendances' => $attendances,
            'subjects' => $subjects,
            'date' => $date,
            'subjectId' => $subjectId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lesson-attendances.create', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance = LessonAttendance::query()->updateOrCreate(
            [
                'santri_id' => $validated['santri_id'],
                'subject_id' => $validated['subject_id'],
                'attendance_date' => $validated['attendance_date'],
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => auth()->id(),
            ]
        );

        AuditLogger::log(
            'lesson_attendance',
            $attendance->wasRecentlyCreated ? 'create' : 'update',
            $attendance,
            null,
            $attendance->toArray()
        );

        return redirect()
            ->route('lesson-attendances.index', [
                'date' => $validated['attendance_date'],
                'subject_id' => $validated['subject_id'],
            ])
            ->with('success', 'Absensi pelajaran berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LessonAttendance $lessonAttendance)
    {
        return redirect()->route('lesson-attendances.edit', $lessonAttendance);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LessonAttendance $lessonAttendance)
    {
        return view('lesson-attendances.edit', array_merge($this->formData(), [
            'attendance' => $lessonAttendance,
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LessonAttendance $lessonAttendance)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = LessonAttendance::query()
            ->where('id', '!=', $lessonAttendance->id)
            ->where('santri_id', $validated['santri_id'])
            ->where('subject_id', $validated['subject_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'attendance_date' => 'Absensi mapel untuk santri di tanggal ini sudah ada.',
            ]);
        }

        $before = $lessonAttendance->toArray();
        $lessonAttendance->update([
            'santri_id' => $validated['santri_id'],
            'subject_id' => $validated['subject_id'],
            'attendance_date' => $validated['attendance_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        AuditLogger::log('lesson_attendance', 'update', $lessonAttendance, $before, $lessonAttendance->fresh()->toArray());

        return redirect()
            ->route('lesson-attendances.index', [
                'date' => $validated['attendance_date'],
                'subject_id' => $validated['subject_id'],
            ])
            ->with('success', 'Absensi pelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LessonAttendance $lessonAttendance)
    {
        $before = $lessonAttendance->toArray();
        $date = $lessonAttendance->attendance_date->toDateString();
        $subjectId = $lessonAttendance->subject_id;
        $lessonAttendance->delete();

        AuditLogger::log('lesson_attendance', 'delete', $lessonAttendance, $before, null);

        return redirect()
            ->route('lesson-attendances.index', [
                'date' => $date,
                'subject_id' => $subjectId,
            ])
            ->with('success', 'Absensi pelajaran berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'santris' => Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
