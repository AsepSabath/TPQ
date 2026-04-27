<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SantriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $query = Santri::query()->orderBy('full_name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['aktif', 'cuti', 'lulus', 'pindah', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        $santris = $query->paginate(15)->withQueryString();

        return view('santri.index', [
            'santris' => $santris,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('santri.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guardian_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'entry_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['aktif', 'cuti', 'lulus', 'pindah', 'nonaktif'])],
        ]);

        $santri = DB::transaction(function () use ($validated) {
            $guardianName = $validated['guardian_name'];
            unset($validated['guardian_name']);

            $validated['nis'] = $this->generateNextNis($validated['entry_date'] ?? null);

            $santri = Santri::query()->create($validated);

            $santri->guardians()->create([
                'relation_type' => 'wali',
                'name' => $guardianName,
                'phone' => '-',
                'is_whatsapp' => false,
                'address' => $validated['address'],
                'is_primary' => true,
            ]);

            return $santri;
        }, 3);

        AuditLogger::log('santri', 'create', $santri, null, $santri->toArray());

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Santri $santri)
    {
        $santri->load(['guardians', 'riskAlert', 'sppInvoices']);

        return view('santri.show', compact('santri'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Santri $santri)
    {
        $santri->load('guardians');

        return view('santri.edit', compact('santri'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'guardian_name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('santris', 'nis')->ignore($santri->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'entry_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['aktif', 'cuti', 'lulus', 'pindah', 'nonaktif'])],
        ]);

        $before = $santri->toArray();

        $guardianName = $validated['guardian_name'];
        unset($validated['guardian_name']);

        DB::transaction(function () use ($santri, $validated, $guardianName) {
            $santri->update($validated);

            $guardian = $santri->guardians()
                ->orderByDesc('is_primary')
                ->first();

            if ($guardian) {
                $guardian->update([
                    'name' => $guardianName,
                ]);

                return;
            }

            $santri->guardians()->create([
                'relation_type' => 'wali',
                'name' => $guardianName,
                'phone' => '-',
                'is_whatsapp' => false,
                'address' => $validated['address'],
                'is_primary' => true,
            ]);
        }, 3);

        AuditLogger::log('santri', 'update', $santri, $before, $santri->fresh()->toArray());

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Santri $santri)
    {
        $before = $santri->toArray();
        $santri->delete();

        AuditLogger::log('santri', 'delete', $santri, $before, null);

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil dihapus.');
    }

    protected function generateNextNis(?string $entryDate = null): string
    {
        $month = $entryDate !== null
            ? (int) date('n', strtotime($entryDate))
            : (int) now()->format('n');
        $year = $entryDate !== null
            ? (int) date('y', strtotime($entryDate))
            : (int) now()->format('y');

        $prefix = sprintf('%d%02d', $month, $year);

        $lastNis = Santri::query()
            ->lockForUpdate()
            ->where('nis', 'like', $prefix.'%')
            ->orderByDesc('nis')
            ->value('nis');

        $lastNumber = 0;

        if (is_string($lastNis) && preg_match('/^'.preg_quote($prefix, '/').'(\d{4})$/', $lastNis, $matches) === 1) {
            $lastNumber = (int) $matches[1];
        }

        do {
            $lastNumber++;
            $candidate = sprintf('%s%04d', $prefix, $lastNumber);
        } while (Santri::query()->where('nis', $candidate)->exists());

        return $candidate;
    }
}
