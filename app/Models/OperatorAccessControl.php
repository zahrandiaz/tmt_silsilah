<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorAccessControl extends Model
{
    use HasFactory;

    // Izinkan properti ini untuk diisi
    protected $fillable = [
        'user_id',
        'person_id',
        'generations_up',
        'generations_down',
    ];

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Person (sebagai akar silsilah).
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}