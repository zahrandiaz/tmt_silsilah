<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // [MODIFIKASI] Menggunakan $fillable untuk keamanan dan menambahkan is_profile_picture
    protected $fillable = [
        'person_id', 
        'image_path', 
        'category', 
        'description', 
        'is_profile_picture'
    ];

    /**
     * Mendefinisikan bahwa setiap foto dimiliki oleh satu Person.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}