<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nis',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'phone',
        'address',
        'entry_date',
        'status',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'entry_date' => 'date',
        ];
    }

    public function guardians()
    {
        return $this->hasMany(SantriGuardian::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function dailyAttendances()
    {
        return $this->hasMany(DailyAttendance::class);
    }

    public function lessonAttendances()
    {
        return $this->hasMany(LessonAttendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    public function sppInvoices()
    {
        return $this->hasMany(SppInvoice::class);
    }

    public function riskAlert()
    {
        return $this->hasOne(RiskAlert::class);
    }
}
