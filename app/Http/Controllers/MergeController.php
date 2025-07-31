<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MergeController extends Controller
{
    /**
     * Menampilkan form untuk menggabungkan data duplikat.
     */
    public function showForm()
    {
        // Ambil semua data orang, diurutkan berdasarkan nama
        $people = Person::orderBy('name')->get();

        return view('admin.merge.index', compact('people'));
    }

    /**
     * Menangani proses penggabungan data duplikat.
     */
    public function merge(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'master_person_id' => ['required', 'exists:people,id'],
            'duplicate_person_id' => [
                'required',
                'exists:people,id',
                'different:master_person_id' // Pastikan tidak memilih orang yang sama
            ],
        ]);

        $masterPerson = Person::findOrFail($request->master_person_id);
        $duplicatePerson = Person::findOrFail($request->duplicate_person_id);

        // 2. Gunakan Database Transaction untuk Keamanan Data
        DB::transaction(function () use ($masterPerson, $duplicatePerson) {
            
            // 3. Pindahkan semua relasi dari duplikat ke master
            Relationship::where('person_id', $duplicatePerson->id)
                ->update(['person_id' => $masterPerson->id]);

            // 4. Pindahkan semua foto dari duplikat ke master
            Photo::where('person_id', $duplicatePerson->id)
                ->update(['person_id' => $masterPerson->id]);

            // 5. Hapus data duplikat
            $duplicatePerson->delete();

            // 6. Catat aktivitas penggabungan (opsional tapi bagus)
            activity()
               ->causedBy(auth()->user())
               ->log("Menggabungkan data duplikat '{$duplicatePerson->name}' (ID: {$duplicatePerson->id}) ke dalam '{$masterPerson->name}' (ID: {$masterPerson->id})");

        });

        return redirect()->route('merge.form')->with('success', 'Data berhasil digabungkan.');
    }
}