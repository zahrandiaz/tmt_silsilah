<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    
    // Izinkan properti ini untuk diisi
    protected $fillable = ['content', 'type', 'is_active'];
}