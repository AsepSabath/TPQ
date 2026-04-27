<?php

namespace App\Imports;

use App\Models\Santri;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SantriImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $nis = trim((string) ($row['nis'] ?? ''));
        $nama = trim((string) ($row['full_name'] ?? $row['nama'] ?? ''));

        if ($nis === '' || $nama === '') {
            return null;
        }

        $genderRaw = strtoupper(trim((string) ($row['gender'] ?? $row['jenis_kelamin'] ?? 'L')));
        $gender = in_array($genderRaw, ['L', 'P'], true) ? $genderRaw : 'L';

        $attributes = [
            'full_name' => $nama,
            'gender' => $gender,
            'birth_place' => $row['birth_place'] ?? $row['tempat_lahir'] ?? null,
            'birth_date' => $row['birth_date'] ?? $row['tanggal_lahir'] ?? null,
            'phone' => $row['phone'] ?? $row['telepon'] ?? null,
            'address' => $row['address'] ?? $row['alamat'] ?? '-',
            'entry_date' => $row['entry_date'] ?? $row['tanggal_masuk'] ?? null,
            'status' => $row['status'] ?? 'aktif',
        ];

        $existing = Santri::query()->where('nis', $nis)->first();

        if ($existing) {
            $existing->update($attributes);

            return null;
        }

        return new Santri(array_merge(['nis' => $nis], $attributes));
    }
}
