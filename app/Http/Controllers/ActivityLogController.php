<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan halaman log aktivitas dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        // 1. Mulai query builder
        $query = Activity::query();

        // 2. Cek apakah ada input pencarian
        if ($request->has('search') && $request->search != '') {
            // Jika ada, filter berdasarkan kolom 'description'
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // 3. Ambil hasil query, urutkan dari yang terbaru, dan paginasi
        //    Jangan lupa tambahkan appends() agar parameter pencarian tetap ada saat pindah halaman
        $activities = $query->latest()->paginate(20)->withQueryString();

        // 4. Kirim data ke view
        return view('admin.activity-log.index', compact('activities'));
    }
}