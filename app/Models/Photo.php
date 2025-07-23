<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    // Izinkan semua kolom diisi untuk kemudahan
    protected $guarded = [];

    /**
     * Mendefinisikan bahwa setiap foto dimiliki oleh satu Person.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}