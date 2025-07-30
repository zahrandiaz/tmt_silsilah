<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Menampilkan laporan individu tanpa tanggal lahir dengan paginasi.
     */
    public function peopleWithoutBirthDate()
    {
        $people = Person::whereNull('birth_date')->latest()->paginate(20);
        $title = "Laporan: Individu tanpa Tanggal Lahir";
        
        // Menggunakan view yang sama untuk berbagai laporan orang
        return view('admin.reports.people', compact('people', 'title'));
    }

    /**
     * Menampilkan laporan individu tanpa orang tua dengan paginasi.
     */
    public function peopleWithoutParents()
    {
        $people = Person::whereDoesntHave('relationships', function ($query) {
            $query->where('role_in_family', 'child');
        })->latest()->paginate(20);
        
        $title = "Laporan: Individu tanpa Orang Tua";

        return view('admin.reports.people', compact('people', 'title'));
    }

    /**
     * Menampilkan laporan keluarga tanpa anak dengan paginasi.
     */
    public function familiesWithoutChildren()
    {
        $familyUnitsWithChildren = Relationship::where('role_in_family', 'child')->pluck('family_unit_id')->unique();
        
        $families = Relationship::where('role_in_family', 'partner')
            ->whereNotIn('family_unit_id', $familyUnitsWithChildren)
            ->whereNotNull('family_unit_id')
            ->distinct('family_unit_id')
            ->with('person')
            ->get()
            ->groupBy('family_unit_id');

        // Paginasi manual untuk koleksi yang sudah di-group
        $paginatedFamilies = new \Illuminate\Pagination\LengthAwarePaginator(
            $families->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 10),
            $families->count(),
            10,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
            
        $title = "Laporan: Keluarga tanpa Anak";
        
        return view('admin.reports.families', compact('paginatedFamilies', 'title'));
    }
}