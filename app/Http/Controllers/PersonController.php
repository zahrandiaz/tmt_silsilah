<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
// --- TAMBAHKAN BARIS INI ---
use Illuminate\Http\Request;

class PersonController extends Controller
{
    // --- AWAL PERUBAHAN ---
    public function index(Request $request)
    {
        // 1. Mulai query builder, jangan langsung ambil data.
        $query = Person::query();

        // 2. Cek apakah ada input pencarian.
        if ($request->has('search') && $request->search != '') {
            // Jika ada, tambahkan kondisi 'where' untuk mencari nama.
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 3. Ambil hasil query dengan paginasi dan urutan terbaru.
        $people = $query->latest()->paginate(10);

        // 4. Kirim data ke view.
        return view('people.index', compact('people'));
    }
    // --- AKHIR PERUBAHAN ---

    public function create()
    {
        $people = Person::orderBy('name')->get();
        return view('people.create', compact('people'));
    }

    public function store(StorePersonRequest $request)
    {
        $validated = $request->validated();

        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();
        // Tambahkan baris ini untuk menangani checkbox
        $personData['is_key_figure'] = $request->has('is_key_figure');
        $person = Person::create($personData);
        
        $this->syncParents($person, $request->father_id, $request->mother_id);

        return redirect()->route('people.index')->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

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

    public function update(UpdatePersonRequest $request, Person $person)
    {
        $validated = $request->validated();

        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();
        // Tambahkan baris ini untuk menangani checkbox
        $personData['is_key_figure'] = $request->has('is_key_figure');
        $person->update($personData);

        $this->syncParents($person, $request->father_id, $request->mother_id);

        return redirect()->route('people.index')->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

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