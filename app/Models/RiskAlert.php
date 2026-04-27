<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAlert extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'santri_id',
        'risk_score',
        'risk_level',
        'attendance_rate',
        'avg_grade',
        'unpaid_months',
        'violation_count',
        'last_calculated_at',
        'notes',
        'is_resolved',
    ];

    protected function casts(): array
    {
        return [
            'risk_score' => 'decimal:2',
            'attendance_rate' => 'decimal:2',
            'avg_grade' => 'decimal:2',
            'last_calculated_at' => 'datetime',
            'is_resolved' => 'boolean',
        ];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
