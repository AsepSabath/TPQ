<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaNotification extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'santri_id',
        'guardian_phone',
        'template_key',
        'message',
        'status',
        'sent_at',
        'failure_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
