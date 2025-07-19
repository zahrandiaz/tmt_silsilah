<?php

namespace App\Http\Controllers;

use App\Models\Person; // 1. Tambahkan ini untuk menggunakan model Person
use App\Models\Relationship;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 2. Ambil data orang dari database, urutkan dari yang terbaru, 10 data per halaman
        $people = Person::latest()->paginate(10);

        // 3. Kirim data tersebut ke view 'people.index'
        return view('people.index', compact('people'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua data orang untuk pilihan dropdown
        $people = Person::orderBy('name')->get();
        return view('people.create', compact('people'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi data, termasuk parent id
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'father_id' => 'nullable|exists:people,id',
            'mother_id' => 'nullable|exists:people,id',
        ]);

        // 2. Buat data orang baru
        $person = Person::create($validated);

        // 3. Panggil method untuk sinkronisasi orang tua
        $this->syncParents($person, $request->father_id, $request->mother_id);

        // 4. Redirect
        return redirect()->route('people.index')->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Person $person)
    {
        // Ambil semua data orang untuk pilihan dropdown
        $people = Person::orderBy('name')->get();
        // Di sini kita juga perlu mengambil data orang tua yang sudah ada
        // (Akan kita implementasikan logikanya nanti)
        return view('people.edit', compact('person', 'people'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Person $person)
    {
        // 1. Validasi data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'father_id' => 'nullable|exists:people,id',
            'mother_id' => 'nullable|exists:people,id',
        ]);

        // 2. Update data orang
        $person->update($validated);
        
        // 3. Panggil method untuk sinkronisasi orang tua
        $this->syncParents($person, $request->father_id, $request->mother_id);

        // 4. Redirect
        return redirect()->route('people.index')->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('people.index')->with('success', 'Data anggota keluarga berhasil dihapus.');
    }

    /**
     * Mengatur hubungan orang tua untuk seorang anak.
     */
    private function syncParents(Person $child, $fatherId, $motherId)
    {
        // Hapus relasi 'anak' yang lama terlebih dahulu jika ada
        Relationship::where('person_id', $child->id)->where('role_in_family', 'child')->delete();

        // Hanya proses jika setidaknya salah satu orang tua dipilih
        if (!$fatherId && !$motherId) {
            return;
        }

        // Cari unit keluarga yang sudah ada untuk pasangan ini
        $existingFamily = Relationship::where('role_in_family', 'partner')
            ->whereIn('person_id', [$fatherId, $motherId])
            ->select('family_unit_id')
            ->groupBy('family_unit_id')
            ->havingRaw('COUNT(DISTINCT person_id) = ?', [($fatherId && $motherId) ? 2 : 1])
            ->first();

        $familyUnitId = null;
        if ($existingFamily) {
            $familyUnitId = $existingFamily->family_unit_id;
        } else {
            // Jika tidak ada, buat unit keluarga baru
            $familyUnitId = (Relationship::max('family_unit_id') ?? 0) + 1;
            if($fatherId) Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $fatherId, 'role_in_family' => 'partner']);
            if($motherId) Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $motherId, 'role_in_family' => 'partner']);
        }

        // Tetapkan anak ke unit keluarga
        Relationship::create(['family_unit_id' => $familyUnitId, 'person_id' => $child->id, 'role_in_family' => 'child']);
    }

}