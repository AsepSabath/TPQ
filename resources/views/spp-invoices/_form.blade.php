@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Santri</label>
        <select name="santri_id" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="">Pilih santri</option>
            @foreach ($santris as $santriOption)
                <option value="{{ $santriOption->id }}" @selected((int) old('santri_id', $invoice->santri_id ?? 0) === $santriOption->id)>
                    {{ $santriOption->nis }} - {{ $santriOption->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nominal Tagihan</label>
        <input type="number" step="0.01" name="amount" value="{{ old('amount', $invoice->amount ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Bulan</label>
        <input type="number" name="month" min="1" max="12" value="{{ old('month', $invoice->month ?? now()->month) }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tahun</label>
        <input type="number" name="year" min="2000" max="2100" value="{{ old('year', $invoice->year ?? now()->year) }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    @if (! isset($invoice))
        <div>
            <label class="block text-sm font-medium text-gray-700">Jumlah Periode</label>
            <input type="number" name="period_count" min="1" max="24" value="{{ old('period_count', 1) }}" class="mt-1 w-full rounded-md border-gray-300" required>
            <p class="mt-1 text-xs text-gray-500">Contoh: isi 3 untuk membuat tagihan bulan ini sampai 2 bulan ke depan.</p>
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700">Jatuh Tempo</label>
        <input type="date" name="due_date" value="{{ old('due_date', isset($invoice?->due_date) ? $invoice->due_date->toDateString() : '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
        @if (! isset($invoice))
            <p class="mt-1 text-xs text-gray-500">Periode yang berada di masa depan otomatis ditandai belum berjalan, sehingga tidak masuk tunggakan.</p>
        @endif
    </div>

    @if (isset($invoice))
        <div>
            <label class="block text-sm font-medium text-gray-700">Status Periode</label>
            @php($periodStatusValue = old('period_status', $invoice->period_status))
            <select name="period_status" class="mt-1 w-full rounded-md border-gray-300" required>
                <option value="berjalan" @selected($periodStatusValue === 'berjalan')>Berjalan</option>
                <option value="belum_berjalan" @selected($periodStatusValue === 'belum_berjalan')>Belum Berjalan</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status Pembayaran</label>
            @php($statusValue = old('status', $invoice->status))
            <select name="status" class="mt-1 w-full rounded-md border-gray-300" required>
                <option value="belum_lunas" @selected($statusValue === 'belum_lunas')>Belum Lunas</option>
                <option value="cicilan" @selected($statusValue === 'cicilan')>Cicilan</option>
                <option value="lunas" @selected($statusValue === 'lunas')>Lunas</option>
            </select>
        </div>
    @endif
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Catatan</label>
    <textarea name="notes" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes', $invoice->notes ?? '') }}</textarea>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('spp-invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
