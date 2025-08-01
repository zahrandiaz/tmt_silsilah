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
        // --- PERUBAHAN DIMULAI DI SINI ---

        // Ambil objek Person lengkap untuk ayah dan ibu menggunakan relasi
        $father = $person->father();
        $mother = $person->mother();

        // Kita tidak perlu lagi mengirimkan daftar $people ke view.
        // Tom Select akan mengambil data melalui API.
        // Logika `excludedIds` juga tidak diperlukan lagi di sini.

        return view('people.edit', compact('person', 'father', 'mother'));
        
        // --- PERUBAHAN SELESAI ---
    }

    public function update(UpdatePersonRequest $request, Person $person)
    {
        $validated = $request->validated();

        $personData = collect($validated)->except(['father_id', 'mother_id'])->all();
        // Tambahkan baris ini untuk menangani checkbox
        $personData['is_key_figure'] = $request->has('is_key_figure');
        $person->update($personData);

        $this->syncParents($person, $request->father_id, $request->mother_id);

        // --- PERUBAHAN DI SINI ---
        // Alihkan ke halaman detail orang yang baru saja diubah.
        return redirect()->route('people.show', $person)->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

    public function destroy(Person $person)
    {
        // --- PERBAIKAN KRITIS DI SINI ---
        // Panggil method 'delete' di PersonPolicy sebelum melakukan aksi.
        $this->authorize('delete', $person);

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

    /**
     * Menyediakan data untuk API pencarian dropdown (Tom Select).
     */
    public function searchApi(Request $request)
    {
        $query = Person::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // --- TAMBAHKAN LOGIKA FILTER GENDER ---
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }
        // ------------------------------------

        // Batasi hasil untuk performa yang lebih baik
        $people = $query->orderBy('name')->take(50)->get(['id', 'name']);

        // Ubah format agar sesuai dengan yang dibutuhkan Tom Select
        $formattedPeople = $people->map(function ($person) {
            return [
                'value' => $person->id,
                'text' => $person->name . ' (ID: ' . $person->id . ')',
            ];
        });

        return response()->json($formattedPeople);
    }
}