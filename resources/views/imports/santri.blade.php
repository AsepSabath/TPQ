<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Impor Data Santri</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if ($errors->any())
                <div class="rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-600">
                    Format header yang didukung: <strong>nis</strong>, <strong>full_name</strong> (atau <strong>nama</strong>),
                    <strong>gender</strong>, <strong>birth_place</strong>, <strong>birth_date</strong>,
                    <strong>phone</strong>, <strong>address</strong>, <strong>entry_date</strong>, <strong>status</strong>.
                </p>

                <form method="POST" action="{{ route('imports.santri.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Excel/CSV</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full text-sm text-gray-700" required>
                    </div>

                    <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Proses Impor
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
