<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Gedcom\Parser;

class GedcomImportController extends Controller
{
    /**
     * Menampilkan form untuk unggah file GEDCOM.
     */
    public function showForm()
    {
        return view('admin.import.gedcom');
    }

    /**
     * Menangani proses unggah dan impor file GEDCOM.
     */
    public function import(Request $request)
    {
        $request->validate([
            'gedcom_file' => 'required|file|extensions:ged',
        ]);

        $path = $request->file('gedcom_file')->getRealPath();
        $parser = new \Gedcom\Parser();
        
        DB::beginTransaction();
        try {
            $gedcom = $parser->parse($path);
            $indiMap = [];
            
            // TAHAP 1: Impor Semua Individu (INDI)
            foreach ($gedcom->getIndi() as $individual) {
                // [PERBAIKAN FINAL] Mengambil data dengan cara yang benar
                $nameData = $individual->getName() ? current($individual->getName()) : null;
                $fullName = $nameData ? str_replace('/', '', $nameData->getName()) : 'Unknown';

                // Menggunakan getEven('BIRT') dan getEven('DEAT') untuk mengambil data peristiwa
                $birthData = $individual->getEven('BIRT') ? current($individual->getEven('BIRT')) : null;
                $deathData = $individual->getEven('DEAT') ? current($individual->getEven('DEAT')) : null;
                
                $person = Person::create([
                    'name' => trim($fullName),
                    'gender' => $individual->getSex() === 'M' ? 'Laki-laki' : 'Perempuan',
                    'birth_date' => $birthData ? $birthData->getDate() : null,
                    'birth_place' => $birthData && $birthData->getPlac() ? $birthData->getPlac()->getPlac() : null,
                    'death_date' => $deathData ? $deathData->getDate() : null,
                    'death_place' => $deathData && $deathData->getPlac() ? $deathData->getPlac()->getPlac() : null,
                ]);

                $indiMap[$individual->getId()] = $person->id;
            }

            // TAHAP 2: Impor Semua Hubungan Keluarga (FAM)
            foreach ($gedcom->getFam() as $family) {
                $husbandId = $family->getHusb() ? ($indiMap[$family->getHusb()] ?? null) : null;
                $wifeId = $family->getWife() ? ($indiMap[$family->getWife()] ?? null) : null;
                $childrenIds = [];
                if ($family->getChil()) {
                    foreach ($family->getChil() as $childId) {
                        if (isset($indiMap[$childId])) {
                            $childrenIds[] = $indiMap[$childId];
                        }
                    }
                }

                if ($husbandId || $wifeId) {
                    $familyUnitId = uniqid('fam_');
                    if ($husbandId) {
                        Relationship::create(['person_id' => $husbandId, 'family_unit_id' => $familyUnitId, 'role_in_family' => 'partner']);
                    }
                    if ($wifeId) {
                        Relationship::create(['person_id' => $wifeId, 'family_unit_id' => $familyUnitId, 'role_in_family' => 'partner']);
                    }
                    foreach ($childrenIds as $childId) {
                        Relationship::create(['person_id' => $childId, 'family_unit_id' => $familyUnitId, 'role_in_family' => 'child']);
                    }
                }
            }

            DB::commit();
            return redirect()->route('import.gedcom.form')->with('success', 'Data GEDCOM berhasil diimpor!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GEDCOM Import Failed: ' . $e->getMessage());
            return redirect()->route('import.gedcom.form')->with('error', 'Terjadi kesalahan saat proses impor. File mungkin rusak atau formatnya tidak didukung.');
        }
    }
}