<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::latest()->paginate(10);
        return view('people.index', compact('people'));
    }

    public function create()
    {
        // Untuk halaman create, kita tidak perlu filter apa pun.
        $people = Person::orderBy('name')->get();
        return view('people.create', compact('people'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            // --- AWAL PERUBAHAN VALIDASI ---
            // Validasi untuk mencegah memilih diri sendiri sebagai orang tua.
            'father_id' => 'nullable|exists:people,id|different:id',
            'mother_id' => 'nullable|exists:people,id|different:id',
            // --- AKHIR PERUBAHAN VALIDASI ---
        ]);

        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();
        $person = Person::create($personData);
        
        $this->syncParents($person, $request->father_id, $request->mother_id);

        return redirect()->route('people.index')->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    public function show(Person $person)
    {
        $ancestorTree = $person->getAncestorTree();
        return view('people.show', compact('person', 'ancestorTree'));
    }

    public function edit(Person $person)
    {
        // --- AWAL PERUBAHAN LOGIKA ---

        // 1. Ambil semua ID keturunan dari orang yang sedang diedit.
        $descendantIds = $person->getDescendantIds();

        // 2. Buat daftar ID yang tidak boleh dipilih sebagai orang tua:
        //    - Dirinya sendiri ($person->id)
        //    - Semua keturunannya ($descendantIds)
        $excludedIds = array_merge([$person->id], $descendantIds);

        // 3. Ambil daftar orang untuk dropdown, KECUALI ID yang dieksklusi.
        $people = Person::whereNotIn('id', $excludedIds)->orderBy('name')->get();
        
        // --- AKHIR PERUBAHAN LOGIKA ---

        // Logika untuk mengambil ID Ayah dan Ibu yang sudah ada (tetap sama)
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

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            // --- AWAL PERUBAHAN VALIDASI ---
            // Validasi untuk mencegah memilih diri sendiri atau keturunan sebagai orang tua.
            'father_id' => ['nullable', 'exists:people,id', 'different:id', 'not_in:'.implode(',', $person->getDescendantIds())],
            'mother_id' => ['nullable', 'exists:people,id', 'different:id', 'not_in:'.implode(',', $person->getDescendantIds())],
            // --- AKHIR PERUBAHAN VALIDASI ---
        ]);

        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();
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