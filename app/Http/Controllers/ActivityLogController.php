<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan halaman log aktivitas.
     */
    public function index()
    {
        // Ambil semua log, urutkan dari yang terbaru, dan paginasi
        $activities = Activity::latest()->paginate(20);

        return view('admin.activity-log.index', compact('activities'));
    }
}