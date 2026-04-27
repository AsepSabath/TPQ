<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SppInvoice extends Model
{
    use HasFactory;

    public const PERIOD_STATUS_NOT_STARTED = 'belum_berjalan';
    public const PERIOD_STATUS_RUNNING = 'berjalan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'santri_id',
        'invoice_number',
        'month',
        'year',
        'amount',
        'paid_amount',
        'due_date',
        'period_status',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function scopeRunningPeriod($query)
    {
        return $query->where('period_status', self::PERIOD_STATUS_RUNNING);
    }

    public function isRunningPeriod(): bool
    {
        return $this->period_status === self::PERIOD_STATUS_RUNNING;
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refreshPaymentStatus(): void
    {
        $paidAmount = (float) $this->payments()->sum('amount');
        $status = 'belum_lunas';

        if ($paidAmount >= (float) $this->amount) {
            $status = 'lunas';
        } elseif ($paidAmount > 0) {
            $status = 'cicilan';
        }

        $this->forceFill([
            'paid_amount' => $paidAmount,
            'status' => $status,
        ])->save();
    }
}
