<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\Violation;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViolationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $level = trim((string) $request->query('level', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $query = Violation::query()
            ->with(['santri:id,nis,full_name', 'recorder:id,name'])
            ->latest('incident_date');

        if ($search !== '') {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if (in_array($level, ['ringan', 'sedang', 'berat'], true)) {
            $query->where('level', $level);
        }

        if ($dateFrom !== '') {
            $query->whereDate('incident_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('incident_date', '<=', $dateTo);
        }

        $violations = $query->paginate(15)->withQueryString();

        return view('violations.index', [
            'violations' => $violations,
            'search' => $search,
            'level' => $level,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']);

        return view('violations.create', compact('santris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'incident_date' => ['required', 'date'],
            'level' => ['required', Rule::in(['ringan', 'sedang', 'berat'])],
            'description' => ['required', 'string'],
            'action_taken' => ['nullable', 'string'],
        ]);

        $validated['recorded_by'] = auth()->id();

        $violation = Violation::query()->create($validated);

        AuditLogger::log('violation', 'create', $violation, null, $violation->toArray());

        return redirect()
            ->route('violations.index')
            ->with('success', 'Data pelanggaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Violation $violation)
    {
        return redirect()->route('violations.edit', $violation);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Violation $violation)
    {
        $santris = Santri::query()->where('status', 'aktif')->orderBy('full_name')->get(['id', 'nis', 'full_name']);

        return view('violations.edit', [
            'violation' => $violation,
            'santris' => $santris,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Violation $violation)
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'incident_date' => ['required', 'date'],
            'level' => ['required', Rule::in(['ringan', 'sedang', 'berat'])],
            'description' => ['required', 'string'],
            'action_taken' => ['nullable', 'string'],
        ]);

        $validated['recorded_by'] = auth()->id();

        $before = $violation->toArray();
        $violation->update($validated);

        AuditLogger::log('violation', 'update', $violation, $before, $violation->fresh()->toArray());

        return redirect()
            ->route('violations.index')
            ->with('success', 'Data pelanggaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Violation $violation)
    {
        $before = $violation->toArray();
        $violation->delete();

        AuditLogger::log('violation', 'delete', $violation, $before, null);

        return redirect()
            ->route('violations.index')
            ->with('success', 'Data pelanggaran berhasil dihapus.');
    }
}
