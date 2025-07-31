<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// --- TAMBAHKAN INI ---
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relationship extends Model
{
    use HasFactory;
    protected $guarded = [];

    // --- TAMBAHKAN METODE RELASI INI ---
    /**
     * Mendefinisikan relasi 'belongsTo' ke model Person.
     * Setiap entri Relationship dimiliki oleh satu Person.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
    // ------------------------------------
}