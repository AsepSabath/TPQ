@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
        <input type="date" name="transaction_date" value="{{ old('transaction_date', isset($transaction?->transaction_date) ? $transaction->transaction_date->toDateString() : now()->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tipe</label>
        @php($typeValue = old('type', $transaction->type ?? 'masuk'))
        <select name="type" class="mt-1 w-full rounded-md border-gray-300" required>
            <option value="masuk" @selected($typeValue === 'masuk')>Kas Masuk</option>
            <option value="keluar" @selected($typeValue === 'keluar')>Kas Keluar</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Kategori</label>
        <input type="text" name="category" value="{{ old('category', $transaction->category ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nominal</label>
        <input type="number" step="0.01" name="amount" value="{{ old('amount', $transaction->amount ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
    <textarea name="description" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('description', $transaction->description ?? '') }}</textarea>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('cash-transactions.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Kembali</a>
</div>
