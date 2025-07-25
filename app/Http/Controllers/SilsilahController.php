<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SilsilahController extends Controller
{
    /**
     * Menampilkan halaman utama silsilah (pintu gerbang).
     */
    public function index(Request $request) // Add Request $request here
    {
        $searchQuery = $request->input('search');

        // Modify the Key Figures query
        $keyFiguresQuery = Person::with('photos')
            ->orderBy('name');

        // If there is a search query, filter the results
        if ($searchQuery) {
            $keyFiguresQuery->where('name', 'like', '%' . $searchQuery . '%');
        } else {
            // If not searching, only show designated key figures
            $keyFiguresQuery->where('is_key_figure', true);
        }

        $keyFigures = $keyFiguresQuery->get();

        // --- STATS LOGIC (remains the same) ---
        $totalPeople = Person::count();
        $totalGenerations = $this->calculateTotalGenerations();

        return view('welcome', compact('keyFigures', 'totalPeople', 'totalGenerations', 'searchQuery'));
    }

    /**
     * Menghitung jumlah generasi terpanjang dalam silsilah.
     */
    private function calculateTotalGenerations(): int
    {
        // Temukan semua "leluhur utama" (orang yang tidak tercatat sebagai anak)
        $progenitors = Person::whereNotIn('id', function ($query) {
            $query->select('person_id')->from('relationships')->where('role_in_family', 'child');
        })->get();

        if ($progenitors->isEmpty()) {
            // Jika tidak ada leluhur, mungkin ada data tapi belum terhubung,
            // atau database benar-benar kosong.
            return Person::count() > 0 ? 1 : 0;
        }

        $maxGeneration = 0;
        foreach ($progenitors as $progenitor) {
            // Hitung kedalaman silsilah dari setiap leluhur utama
            $depth = $progenitor->getMaxDescendantDepth();
            if ($depth > $maxGeneration) {
                $maxGeneration = $depth;
            }
        }

        return $maxGeneration;
    }
}