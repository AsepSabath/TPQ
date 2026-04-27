<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Semester</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .meta { color: #4b5563; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Laporan Semester Santri</h1>
    <p class="meta">Tahun Ajaran: {{ $academicYear }} | Semester: {{ ucfirst($semester) }}</p>

    @foreach ($grouped as $row)
        <h2>
            {{ $row['santri']->full_name ?? '-' }} ({{ $row['santri']->nis ?? '-' }})
            - Rata-rata: {{ number_format($row['average'], 2) }}
        </h2>

        <table>
            <thead>
                <tr>
                    <th>Mapel</th>
                    <th>Nilai</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($row['grades'] as $grade)
                    <tr>
                        <td>{{ $grade->subject->name ?? '-' }}</td>
                        <td>{{ number_format($grade->score, 2) }}</td>
                        <td>{{ $grade->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
