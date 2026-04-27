<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'spp_invoice_id',
        'received_by',
        'payment_method',
        'amount',
        'paid_at',
        'reference_no',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function sppInvoice()
    {
        return $this->belongsTo(SppInvoice::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function cashTransaction()
    {
        return $this->hasOne(CashTransaction::class, 'related_payment_id');
    }
}
