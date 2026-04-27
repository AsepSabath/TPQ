<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantriGuardian extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'santri_id',
        'relation_type',
        'name',
        'phone',
        'is_whatsapp',
        'occupation',
        'address',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_whatsapp' => 'boolean',
            'is_primary' => 'boolean',
        ];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
