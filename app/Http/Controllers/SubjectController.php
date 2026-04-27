<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $active = (string) $request->query('active', '');

        $query = Subject::query()
            ->with('teacher:id,name')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($active === '1') {
            $query->where('is_active', true);
        } elseif ($active === '0') {
            $query->where('is_active', false);
        }

        $subjects = $query->paginate(15)->withQueryString();

        return view('subjects.index', [
            'subjects' => $subjects,
            'search' => $search,
            'active' => $active,
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

        return view('subjects.create', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:subjects,code'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'credit_hours' => ['nullable', 'integer', 'between:1,12'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $subject = Subject::query()->create($validated);

        AuditLogger::log('subject', 'create', $subject, null, $subject->toArray());

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        return redirect()->route('subjects.edit', $subject);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        $teachers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('subjects.edit', [
            'subject' => $subject,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'credit_hours' => ['nullable', 'integer', 'between:1,12'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $before = $subject->toArray();
        $subject->update($validated);

        AuditLogger::log('subject', 'update', $subject, $before, $subject->fresh()->toArray());

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        if ($subject->grades()->exists() || $subject->lessonAttendances()->exists()) {
            return back()->withErrors([
                'subject' => 'Mapel tidak bisa dihapus karena masih dipakai pada data nilai atau absensi.',
            ]);
        }

        $before = $subject->toArray();
        $subject->delete();

        AuditLogger::log('subject', 'delete', $subject, $before, null);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
