<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
// --- TAMBAHKAN DUA BARIS INI ---
use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::latest()->paginate(10);
        return view('people.index', compact('people'));
    }

    public function create()
    {
        $people = Person::orderBy('name')->get();
        return view('people.create', compact('people'));
    }

    // --- AWAL PERUBAHAN ---
    public function store(StorePersonRequest $request)
    {
        // Validasi sudah berjalan otomatis. Kita bisa langsung ambil data yang tervalidasi.
        $validated = $request->validated();

        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();
        $person = Person::create($personData);
        
        $this->syncParents($person, $request->father_id, $request->mother_id);

        return redirect()->route('people.index')->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }
    // --- AKHIR PERUBAHAN ---

    public function show(Person $person)
    {
        $breadcrumbs = $person->getBreadcrumbs();
        return view('people.show', compact('person', 'breadcrumbs'));
    }

    public function edit(Person $person)
    {
        $descendantIds = $person->getDescendantIds();
        $excludedIds = array_merge([$person->id], $descendantIds);
        $people = Person::whereNotIn('id', $excludedIds)->orderBy('name')->get();
        
        $fatherId = null;
        $motherId = null;
        
        $childRelation = Relationship::where('person_id', $person->id)
            ->where('role_in_family', 'child')
            ->first();

        if ($childRelation) {
            $parents = Relationship::where('family_unit_id', $childRelation->family_unit_id)
                ->where('role_in_family', 'partner')
                ->join('people', 'relationships.person_id', '=', 'people.id')
                ->get(['people.id', 'people.gender']);
            
            $fatherId = $parents->firstWhere('gender', 'Laki-laki')->id ?? null;
            $motherId = $parents->firstWhere('gender', 'Perempuan')->id ?? null;
        }

        return view('people.edit', compact('person', 'people', 'fatherId', 'motherId'));
    }

    // --- AWAL PERUBAHAN ---
    public function update(UpdatePersonRequest $request, Person $person)
    {
        // Validasi sudah berjalan otomatis.
        $validated = $request->validated();

        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();
        $person->update($personData);

        $this->syncParents($person, $request->father_id, $request->mother_id);

        return redirect()->route('people.index')->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }
    // --- AKHIR PERUBAHAN ---

    public function destroy(Person $person)
    {
        $person->delete();
        return redirect()->route('people.index')->with('success', 'Data anggota keluarga berhasil dihapus.');
    }

    private function syncParents(Person $child, $fatherId, $motherId)
    {
        Relationship::where('person_id', $child->id)->where('role_in_family', 'child')->delete();

        if (!$fatherId && !$motherId) {
            return;
        }

        $familyUnitId = null;
        if ($fatherId && $motherId) {
            $commonFamilies = Relationship::whereIn('person_id', [$fatherId, $motherId])
                ->where('role_in_family', 'partner')
                ->select('family_unit_id')
                ->groupBy('family_unit_id')
                ->havingRaw('COUNT(DISTINCT person_id) = 2')
                ->pluck('family_unit_id');
            if($commonFamilies->isNotEmpty()) {
                $familyUnitId = $commonFamilies->first();
            }
        } else {
            $parentId = $fatherId ?: $motherId;
            $singleParentFamily = Relationship::where('person_id', $parentId)
                ->where('role_in_family', 'partner')
                ->first();
            if($singleParentFamily) {
                $familyUnitId = $singleParentFamily->family_unit_id;
            }
        }

        if (!$familyUnitId) {
            $familyUnitId = (Relationship::max('family_unit_id') ?? 0) + 1;
            if ($fatherId) {
                Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $fatherId, 'role_in_family' => 'partner']);
            }
            if ($motherId) {
                Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $motherId, 'role_in_family' => 'partner']);
            }
        }
        
        Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $child->id, 'role_in_family' => 'child']);
    }
}