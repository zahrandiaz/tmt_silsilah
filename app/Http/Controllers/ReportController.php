<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ReportController extends Controller
{
    /**
     * Menampilkan laporan individu tanpa tanggal lahir dengan paginasi dan pencarian.
     */
    public function peopleWithoutBirthDate(Request $request)
    {
        $query = Person::whereNull('birth_date');

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $people = $query->latest()->paginate(20)->withQueryString();
        $title = "Laporan: Individu tanpa Tanggal Lahir";
        
        return view('admin.reports.people', compact('people', 'title'));
    }

    /**
     * Menampilkan laporan individu tanpa orang tua dengan paginasi dan pencarian.
     */
    public function peopleWithoutParents(Request $request)
    {
        $query = Person::whereDoesntHave('relationships', function ($q) {
            $q->where('role_in_family', 'child');
        });

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $people = $query->latest()->paginate(20)->withQueryString();
        $title = "Laporan: Individu tanpa Orang Tua";

        return view('admin.reports.people', compact('people', 'title'));
    }

    /**
     * Menampilkan laporan keluarga tanpa anak dengan paginasi dan pencarian.
     */
    public function familiesWithoutChildren(Request $request)
    {
        $familyUnitsWithChildren = Relationship::where('role_in_family', 'child')->pluck('family_unit_id')->unique();
        
        $query = Relationship::where('role_in_family', 'partner')
            ->whereNotIn('family_unit_id', $familyUnitsWithChildren)
            ->whereNotNull('family_unit_id');

        if ($request->has('search') && $request->search != '') {
            // Cari family_unit_id berdasarkan nama partner
            $matchingFamilyIds = Relationship::whereHas('person', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->pluck('family_unit_id');

            $query->whereIn('family_unit_id', $matchingFamilyIds);
        }

        $families = $query->with('person')->get()->groupBy('family_unit_id');

        // Paginasi manual
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $currentPageItems = $families->slice(($currentPage - 1) * $perPage, $perPage);
        
        $paginatedFamilies = new LengthAwarePaginator(
            $currentPageItems,
            $families->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        // Tambahkan query string ke link paginasi
        $paginatedFamilies->withQueryString();
            
        $title = "Laporan: Keluarga tanpa Anak";
        
        return view('admin.reports.families', compact('paginatedFamilies', 'title'));
    }
}