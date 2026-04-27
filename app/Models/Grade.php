<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'santri_id',
        'subject_id',
        'academic_class_id',
        'teacher_id',
        'academic_year',
        'semester',
        'score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
