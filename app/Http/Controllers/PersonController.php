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
        $people = Person::orderBy('name')->get();
        return view('people.create', compact('people'));
    }

    public function store(Request $request)
    {
        // Menambahkan validasi untuk data kematian
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'father_id' => 'nullable|exists:people,id',
            'mother_id' => 'nullable|exists:people,id',
        ]);

        // --- AWAL PERUBAHAN ---
        // 1. Siapkan data khusus untuk tabel 'people'.
        //    Kita ambil semua data tervalidasi KECUALI 'father_id' dan 'mother_id'.
        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();

        // 2. Buat record Person hanya dengan data yang relevan.
        $person = Person::create($personData);
        // --- AKHIR PERUBAHAN ---
        
        // 3. Panggil syncParents dengan data ID orang tua dari request asli.
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
        $people = Person::orderBy('name')->get();

        // Logika untuk mengambil ID Ayah dan Ibu yang sudah ada
        $fatherId = null;
        $motherId = null;
        
        // Cari unit keluarga di mana orang ini adalah anak
        $childRelation = Relationship::where('person_id', $person->id)
            ->where('role_in_family', 'child')
            ->first();

        if ($childRelation) {
            // Jika ditemukan, cari partner (orang tua) di unit keluarga yang sama
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
        // Menambahkan validasi untuk data kematian
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'father_id' => 'nullable|exists:people,id',
            'mother_id' => 'nullable|exists:people,id',
        ]);

        // --- AWAL PERUBAHAN ---
        // 1. Pisahkan data Person dari data relasi
        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();

        // 2. Update data Person hanya dengan data yang relevan
        $person->update($personData);
        // --- AKHIR PERUBAHAN ---

        // 3. Sinkronkan relasi orang tua menggunakan data ID dari request
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
        // Hapus relasi 'anak' yang lama untuk orang ini
        Relationship::where('person_id', $child->id)->where('role_in_family', 'child')->delete();

        // Jika tidak ada orang tua yang dipilih, selesai.
        if (!$fatherId && !$motherId) {
            return;
        }

        // Cari unit keluarga yang sudah ada untuk pasangan ini
        $familyUnitId = null;
        $query = Relationship::where('role_in_family', 'partner');

        if ($fatherId) $query->where('person_id', $fatherId);
        if ($motherId) $query->where('person_id', $motherId);
        
        // Logika untuk menemukan family_unit_id yang cocok
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

        // Jika tidak ada unit keluarga yang cocok, buat yang baru
        if (!$familyUnitId) {
            $familyUnitId = (Relationship::max('family_unit_id') ?? 0) + 1;
            if ($fatherId) {
                Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $fatherId, 'role_in_family' => 'partner']);
            }
            if ($motherId) {
                Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $motherId, 'role_in_family' => 'partner']);
            }
        }
        
        // Tetapkan anak ke unit keluarga yang benar
        Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $child->id, 'role_in_family' => 'child']);
    }
}