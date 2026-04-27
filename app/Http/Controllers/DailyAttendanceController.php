<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\Santri;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DailyAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $date = (string) $request->query('date', now()->toDateString());
        $search = trim((string) $request->query('search', ''));

        $query = DailyAttendance::query()
            ->with(['santri:id,nis,full_name', 'recorder:id,name'])
            ->whereDate('attendance_date', $date)
            ->latest('id');

        if ($search !== '') {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $attendances = $query->paginate(15)->withQueryString();
        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']);

        return view('daily-attendances.index', [
            'attendances' => $attendances,
            'santris' => $santris,
            'date' => $date,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $date = (string) $request->query('date', now()->toDateString());

        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']);

        $existingAttendances = DailyAttendance::query()
            ->whereDate('attendance_date', $date)
            ->whereIn('santri_id', $santris->pluck('id'))
            ->get(['id', 'santri_id', 'status', 'notes'])
            ->keyBy('santri_id');

        return view('daily-attendances.create', [
            'santris' => $santris,
            'date' => $date,
            'existingAttendances' => $existingAttendances,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->has('entries')) {
            $validated = $request->validate([
                'attendance_date' => ['required', 'date'],
                'entries' => ['required', 'array', 'min:1'],
                'entries.*.santri_id' => ['required', 'exists:santris,id'],
                'entries.*.status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
                'entries.*.notes' => ['nullable', 'string'],
            ]);

            $savedCount = count($validated['entries']);
            $now = now();
            $rows = [];

            foreach ($validated['entries'] as $entry) {
                $rows[] = [
                    'santri_id' => (int) $entry['santri_id'],
                    'attendance_date' => $validated['attendance_date'],
                    'status' => $entry['status'],
                    'notes' => $entry['notes'] ?? null,
                    'recorded_by' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DailyAttendance::query()->upsert(
                $rows,
                ['santri_id', 'attendance_date'],
                ['status', 'notes', 'recorded_by', 'updated_at']
            );

            AuditLogger::log('daily_attendance', 'bulk_upsert', null, null, [
                'attendance_date' => $validated['attendance_date'],
                'saved_count' => $savedCount,
            ]);

            return redirect()
                ->route('daily-attendances.index', ['date' => $validated['attendance_date']])
                ->with('success', "Absensi {$savedCount} santri berhasil disimpan.");
        }

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
            'notes' => ['nullable', 'string'],
        ]);

        $now = now();

        DailyAttendance::query()->upsert(
            [[
                'santri_id' => (int) $validated['santri_id'],
                'attendance_date' => $validated['attendance_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['santri_id', 'attendance_date'],
            ['status', 'notes', 'recorded_by', 'updated_at']
        );

        $attendance = DailyAttendance::query()
            ->where('santri_id', $validated['santri_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->first();

        if ($attendance !== null) {
            AuditLogger::log('daily_attendance', 'upsert', $attendance, null, $attendance->toArray());
        }

        return redirect()
            ->route('daily-attendances.index', ['date' => $validated['attendance_date']])
            ->with('success', 'Absensi harian berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyAttendance $dailyAttendance)
    {
        return redirect()->route('daily-attendances.edit', $dailyAttendance);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyAttendance $dailyAttendance)
    {
        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']);

        return view('daily-attendances.edit', [
            'attendance' => $dailyAttendance,
            'santris' => $santris,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyAttendance $dailyAttendance)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = DailyAttendance::query()
            ->where('id', '!=', $dailyAttendance->id)
            ->where('santri_id', $validated['santri_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'attendance_date' => 'Absensi untuk santri dan tanggal ini sudah ada.',
            ]);
        }

        $before = $dailyAttendance->toArray();
        $dailyAttendance->update([
            'santri_id' => $validated['santri_id'],
            'attendance_date' => $validated['attendance_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        AuditLogger::log('daily_attendance', 'update', $dailyAttendance, $before, $dailyAttendance->fresh()->toArray());

        return redirect()
            ->route('daily-attendances.index', ['date' => $validated['attendance_date']])
            ->with('success', 'Absensi harian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyAttendance $dailyAttendance)
    {
        $before = $dailyAttendance->toArray();
        $date = $dailyAttendance->attendance_date->toDateString();
        $dailyAttendance->delete();

        AuditLogger::log('daily_attendance', 'delete', $dailyAttendance, $before, null);

        return redirect()
            ->route('daily-attendances.index', ['date' => $date])
            ->with('success', 'Absensi harian berhasil dihapus.');
    }
}
