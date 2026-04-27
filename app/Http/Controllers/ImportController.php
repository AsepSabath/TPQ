<?php

namespace App\Http\Controllers;

use App\Imports\SantriImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function santriForm()
    {
        return view('imports.santri');
    }

    public function santriStore(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        Excel::import(new SantriImport(), $validated['file']);

        return redirect()
            ->route('santri.index')
            ->with('success', 'Impor data santri selesai diproses.');
    }
}
