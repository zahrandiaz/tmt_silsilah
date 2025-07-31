<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\User;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan data statistik dan laporan untuk dashboard.
     * Tampilan disesuaikan berdasarkan peran pengguna (admin vs operator).
     */
    public function index()
    {
        $user = Auth::user();

        // Jika pengguna adalah operator dan memiliki hak akses yang terdefinisi
        if ($user->role === 'operator' && $user->accessControl) {
            return $this->operatorDashboard($user);
        }

        // Jika pengguna adalah admin atau user biasa
        return $this->adminDashboard();
    }

    /**
     * Menyiapkan data untuk dashboard Admin (melihat semua data).
     */
    private function adminDashboard()
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
        $peopleWithoutBirthDate = Person::whereNull('birth_date')->latest()->take(10)->get();
        $totalPeopleWithoutBirthDate = Person::whereNull('birth_date')->count();

        $peopleWithoutParents = $peopleWithNoParentsQuery->take(10)->get();
        $totalPeopleWithoutParents = $peopleWithNoParentsQuery->count();
        
        $familyUnitsWithChildren = Relationship::where('role_in_family', 'child')->pluck('family_unit_id')->unique();
        $familiesWithoutChildrenQuery = Relationship::where('role_in_family', 'partner')
            ->whereNotIn('family_unit_id', $familyUnitsWithChildren)
            ->whereNotNull('family_unit_id')
            ->distinct('family_unit_id');

        $totalFamiliesWithoutChildren = $familiesWithoutChildrenQuery->count();

        $familiesWithoutChildren = $familiesWithoutChildrenQuery
            ->with('person')
            ->get()
            ->groupBy('family_unit_id')
            ->take(10);

        return view('dashboard', compact(
            'totalPeople', 'totalUsers', 'totalFamilies', 'maxGeneration',
            'peopleWithoutBirthDate', 'totalPeopleWithoutBirthDate',
            'peopleWithoutParents', 'totalPeopleWithoutParents',
            'familiesWithoutChildren', 'totalFamiliesWithoutChildren'
        ));
    }

    /**
     * Menyiapkan data untuk dashboard Operator (melihat data dalam lingkupnya saja).
     */
    private function operatorDashboard(User $operator)
    {
        // 1. Dapatkan semua ID orang yang bisa diakses oleh operator
        $accessiblePersonIds = $this->getAccessiblePersonIds($operator);

        if (empty($accessiblePersonIds)) {
            // Jika tidak ada data yang bisa diakses, tampilkan dashboard kosong
            return view('dashboard')->with('isOperatorScopeEmpty', true);
        }

        // 2. Buat query dasar yang sudah terfilter
        $peopleInScopeQuery = Person::whereIn('id', $accessiblePersonIds);

        // --- Hitung Statistik Berdasarkan Lingkup ---
        $totalPeople = $peopleInScopeQuery->count();
        $totalUsers = User::whereIn('person_id', $accessiblePersonIds)->count();
        $totalFamilies = Relationship::whereIn('person_id', $accessiblePersonIds)
            ->whereNotNull('family_unit_id')->distinct('family_unit_id')->count();
        
        $maxGeneration = 0;
        $peopleWithNoParentsQuery = Person::whereIn('id', $accessiblePersonIds)->whereDoesntHave('relationships', function ($query) {
            $query->where('role_in_family', 'child');
        });

        foreach ($peopleWithNoParentsQuery->get() as $person) {
            $depth = $person->getMaxDescendantDepth();
            if ($depth > $maxGeneration) {
                $maxGeneration = $depth;
            }
        }

        // --- Siapkan Laporan Anomali Berdasarkan Lingkup ---
        $peopleWithoutBirthDate = $peopleInScopeQuery->clone()->whereNull('birth_date')->latest()->take(10)->get();
        $totalPeopleWithoutBirthDate = $peopleInScopeQuery->clone()->whereNull('birth_date')->count();

        $peopleWithoutParents = $peopleWithNoParentsQuery->take(10)->get();
        $totalPeopleWithoutParents = $peopleWithNoParentsQuery->count();

        $familyUnitsWithChildren = Relationship::where('role_in_family', 'child')->whereIn('person_id', $accessiblePersonIds)->pluck('family_unit_id')->unique();
        $familiesWithoutChildrenQuery = Relationship::where('role_in_family', 'partner')
            ->whereIn('person_id', $accessiblePersonIds)
            ->whereNotIn('family_unit_id', $familyUnitsWithChildren)
            ->whereNotNull('family_unit_id')
            ->distinct('family_unit_id');
        
        $totalFamiliesWithoutChildren = $familiesWithoutChildrenQuery->count();
        $familiesWithoutChildren = $familiesWithoutChildrenQuery->with('person')->get()->groupBy('family_unit_id')->take(10);

        return view('dashboard', compact(
            'totalPeople', 'totalUsers', 'totalFamilies', 'maxGeneration',
            'peopleWithoutBirthDate', 'totalPeopleWithoutBirthDate',
            'peopleWithoutParents', 'totalPeopleWithoutParents',
            'familiesWithoutChildren', 'totalFamiliesWithoutChildren'
        ));
    }

    /**
     * Helper untuk mendapatkan semua ID Person dalam lingkup seorang operator.
     */
    private function getAccessiblePersonIds(User $operator): array
    {
        $rootPerson = $operator->accessControl->person;
        if (!$rootPerson) return [];

        $accessibleIds = [$rootPerson->id];

        // Ambil keturunan
        $descendants = $rootPerson->getDescendantIdsWithLevel();
        foreach ($descendants as $id => $level) {
            if ($level <= $operator->accessControl->generations_down) {
                $accessibleIds[] = $id;
            }
        }

        // Ambil leluhur
        $this->getAncestorIdsRecursive($rootPerson, $operator->accessControl->generations_up, 1, $accessibleIds);

        return array_unique($accessibleIds);
    }

    /**
     * Fungsi rekursif untuk mengambil ID leluhur hingga batas generasi tertentu.
     */
    private function getAncestorIdsRecursive($person, $limit, $currentLevel, &$ids)
    {
        if ($currentLevel > $limit) {
            return;
        }
        foreach ($person->parents() as $parent) {
            $ids[] = $parent->id;
            $this->getAncestorIdsRecursive($parent, $limit, $currentLevel + 1, $ids);
        }
    }
}