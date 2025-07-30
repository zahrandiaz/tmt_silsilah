<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GedcomImportController extends Controller
{
    public function showForm()
    {
        return view('admin.import.gedcom');
    }

    public function import(Request $request)
    {
        $request->validate([
            'gedcom_file' => 'required|file|extensions:ged',
        ]);

        $path = $request->file('gedcom_file')->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        DB::beginTransaction();
        try {
            $individualsData = [];
            $familiesData = [];
            $currentRecord = null;
            $currentType = null;

            foreach ($lines as $line) {
                $parts = explode(' ', $line, 3);
                $level = $parts[0];
                $tag = $parts[1] ?? '';
                $value = $parts[2] ?? '';

                if ($level === '0' && str_starts_with($tag, '@I')) {
                    $currentType = 'INDI';
                    $currentRecord = str_replace('@', '', $tag);
                    $individualsData[$currentRecord] = ['id' => $currentRecord];
                } elseif ($level === '0' && str_starts_with($tag, '@F')) {
                    $currentType = 'FAM';
                    $currentRecord = str_replace('@', '', $tag);
                    $familiesData[$currentRecord] = ['id' => $currentRecord, 'children' => []];
                } elseif ($currentRecord) {
                    if ($currentType === 'INDI') {
                        switch ($tag) {
                            case 'NAME':
                                $individualsData[$currentRecord]['name'] = trim(str_replace('/', '', $value));
                                break;
                            case 'SEX':
                                $individualsData[$currentRecord]['gender'] = ($value === 'M') ? 'Laki-laki' : 'Perempuan';
                                break;
                            case 'BIRT':
                                $individualsData[$currentRecord]['event'] = 'BIRT';
                                break;
                            case 'DEAT':
                                $individualsData[$currentRecord]['event'] = 'DEAT';
                                break;
                            // --- TAMBAHKAN LOGIKA BARU UNTUK PLAC & NOTE ---
                            case 'PLAC':
                                if (isset($individualsData[$currentRecord]['event'])) {
                                    $eventName = ($individualsData[$currentRecord]['event'] === 'BIRT') ? 'birth_place' : 'death_place';
                                    $individualsData[$currentRecord][$eventName] = $value;
                                }
                                break;
                            case 'NOTE':
                                $individualsData[$currentRecord]['biography'] = $value;
                                break;
                            case 'CONC': // Menangani baris lanjutan dari biografi
                                if (isset($individualsData[$currentRecord]['biography'])) {
                                    $individualsData[$currentRecord]['biography'] .= $value;
                                }
                                break;
                            // ---------------------------------------------
                            case 'DATE':
                                if (isset($individualsData[$currentRecord]['event'])) {
                                    $eventName = ($individualsData[$currentRecord]['event'] === 'BIRT') ? 'birth_date' : 'death_date';
                                    $individualsData[$currentRecord][$eventName] = Carbon::createFromFormat('j M Y', $value)->format('Y-m-d');
                                }
                                break;
                        }
                    } elseif ($currentType === 'FAM') {
                        switch ($tag) {
                            case 'HUSB':
                                $familiesData[$currentRecord]['husband'] = str_replace('@', '', $value);
                                break;
                            case 'WIFE':
                                $familiesData[$currentRecord]['wife'] = str_replace('@', '', $value);
                                break;
                            case 'CHIL':
                                $familiesData[$currentRecord]['children'][] = str_replace('@', '', $value);
                                break;
                        }
                    }
                }
            }

            $indiMap = [];
            foreach ($individualsData as $gedcomId => $data) {
                // --- TAMBAHKAN FIELD BARU SAAT CREATE ---
                $person = Person::create([
                    'name' => $data['name'] ?? 'Unknown',
                    'gender' => $data['gender'] ?? 'Perempuan',
                    'birth_date' => $data['birth_date'] ?? null,
                    'birth_place' => $data['birth_place'] ?? null,
                    'death_date' => $data['death_date'] ?? null,
                    'death_place' => $data['death_place'] ?? null,
                    'biography' => $data['biography'] ?? null,
                ]);
                $indiMap[$gedcomId] = $person->id;
            }

            foreach ($familiesData as $family) {
                $husbandId = isset($family['husband']) && isset($indiMap[$family['husband']]) ? $indiMap[$family['husband']] : null;
                $wifeId = isset($family['wife']) && isset($indiMap[$family['wife']]) ? $indiMap[$family['wife']] : null;

                if ($husbandId || $wifeId) {
                    $familyUnitId = uniqid('fam_');
                    if ($husbandId) {
                        Relationship::create(['person_id' => $husbandId, 'family_unit_id' => $familyUnitId, 'role_in_family' => 'partner']);
                    }
                    if ($wifeId) {
                        Relationship::create(['person_id' => $wifeId, 'family_unit_id' => $familyUnitId, 'role_in_family' => 'partner']);
                    }
                    foreach ($family['children'] as $childGedcomId) {
                        if (isset($indiMap[$childGedcomId])) {
                            Relationship::create(['person_id' => $indiMap[$childGedcomId], 'family_unit_id' => $familyUnitId, 'role_in_family' => 'child']);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('import.gedcom.form')->with('success', 'Data GEDCOM berhasil diimpor!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GEDCOM Manual Import Failed: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return redirect()->route('import.gedcom.form')->with('error', 'Import Gagal: ' . $e->getMessage());
        }
    }
}