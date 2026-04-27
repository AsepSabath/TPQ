<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Mata Pelajaran</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('subjects.update', $subject) }}">
                    @method('PUT')
                    @include('subjects._form', ['submitLabel' => 'Perbarui'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
