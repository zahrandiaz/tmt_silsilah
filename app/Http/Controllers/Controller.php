<?php

namespace App\Http\Controllers;

// --- TAMBAHKAN BARIS INI ---
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// --- MODIFIKASI BARIS INI ---
abstract class Controller
{
    // --- TAMBAHKAN BARIS INI ---
    use AuthorizesRequests;
}