<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\User;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan data statistik dan laporan untuk dashboard admin.
     */
    public function index()
    {
        // --- Widget Statistik Utama ---
        $totalPeople = Person::count();
        $totalUsers = User::count();
        $totalFamilies = Relationship::whereNotNull('family_unit_id')->distinct('family_unit_id')->count();
        
        $maxGeneration = 0;
        $peopleWithNoParentsQuery = Person::whereDoesntHave('relationships', function ($query) {
            $query->where('role_in_family', 'child');
        });

        foreach ($peopleWithNoParentsQuery->get() as $person) {
            $depth = $person->getMaxDescendantDepth();
            if ($depth > $maxGeneration) {
                $maxGeneration = $depth;
            }
        }

        // --- Widget Laporan Kualitas Data ---
        
        // 3. Individu tanpa Tanggal Lahir
        $peopleWithoutBirthDate = Person::whereNull('birth_date')->latest()->take(10)->get();
        $totalPeopleWithoutBirthDate = Person::whereNull('birth_date')->count(); // <-- HITUNG TOTAL

        // 4. Individu tanpa Orang Tua
        $peopleWithoutParents = $peopleWithNoParentsQuery->take(10)->get();
        $totalPeopleWithoutParents = $peopleWithNoParentsQuery->count(); // <-- HITUNG TOTAL
        
        // 5. Keluarga tanpa Anak
        $familyUnitsWithChildren = Relationship::where('role_in_family', 'child')->pluck('family_unit_id')->unique();
        $familiesWithoutChildrenQuery = Relationship::where('role_in_family', 'partner')
            ->whereNotIn('family_unit_id', $familyUnitsWithChildren)
            ->whereNotNull('family_unit_id')
            ->distinct('family_unit_id');

        $totalFamiliesWithoutChildren = $familiesWithoutChildrenQuery->count(); // <-- HITUNG TOTAL

        $familiesWithoutChildren = $familiesWithoutChildrenQuery
            ->with('person')
            ->get()
            ->groupBy('family_unit_id')
            ->take(10);


        return view('dashboard', compact(
            'totalPeople',
            'totalUsers',
            'totalFamilies',
            'maxGeneration',
            'peopleWithoutBirthDate',
            'totalPeopleWithoutBirthDate', // <-- KIRIM TOTAL
            'peopleWithoutParents',
            'totalPeopleWithoutParents', // <-- KIRIM TOTAL
            'familiesWithoutChildren',
            'totalFamiliesWithoutChildren' // <-- KIRIM TOTAL
        ));
    }
}