<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Menghasilkan dan mengunduh file GEDCOM dengan struktur lengkap dan standar.
     */
    public function exportGedcom()
    {
        $people = Person::with('relationships')->get();
        
        if ($people->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ada data silsilah untuk diekspor.');
        }

        $gedcomContent = "0 HEAD\n";
        $gedcomContent .= "1 SOUR TMT-SILSILAH\n";
        $gedcomContent .= "1 DEST ANY\n";
        $gedcomContent .= "1 DATE " . strtoupper(now()->format('j M Y')) . "\n";
        $gedcomContent .= "1 SUBM @U1@\n";
        $gedcomContent .= "1 GEDC\n";
        $gedcomContent .= "2 VERS 5.5.1\n";
        $gedcomContent .= "2 FORM LINEAGE-LINKED\n";
        $gedcomContent .= "1 CHAR UTF-8\n";
        $gedcomContent .= "0 @U1@ SUBM\n";
        $gedcomContent .= "1 NAME TMT-Silsilah Admin\n";

        foreach ($people as $person) {
            $gedcomContent .= "0 @I{$person->id}@ INDI\n";
            $gedcomContent .= "1 NAME " . str_replace(' ', ' /', $person->name) . "/\n";
            $gedcomContent .= "1 SEX " . ($person->gender === 'Laki-laki' ? 'M' : 'F') . "\n";

            if ($person->birth_date) {
                $gedcomContent .= "1 BIRT\n";
                $gedcomContent .= "2 DATE " . strtoupper(Carbon::parse($person->birth_date)->format('j M Y')) . "\n";
                // --- TAMBAHKAN INI: Ekspor Tempat Lahir ---
                if ($person->birth_place) {
                    $gedcomContent .= "2 PLAC " . $person->birth_place . "\n";
                }
            }

            if ($person->death_date) {
                $gedcomContent .= "1 DEAT\n";
                $gedcomContent .= "2 DATE " . strtoupper(Carbon::parse($person->death_date)->format('j M Y')) . "\n";
                // --- TAMBAHKAN INI: Ekspor Tempat Wafat ---
                if ($person->death_place) {
                    $gedcomContent .= "2 PLAC " . $person->death_place . "\n";
                }
            }
            
            // --- TAMBAHKAN INI: Ekspor Biografi ---
            if ($person->biography) {
                // Mengganti baris baru dengan format GEDCOM CONC (concatenate)
                $biographyLines = explode("\n", $person->biography);
                $gedcomContent .= "1 NOTE " . trim(array_shift($biographyLines)) . "\n";
                foreach ($biographyLines as $bioLine) {
                    $gedcomContent .= "2 CONC " . trim($bioLine) . "\n";
                }
            }
            
            foreach ($person->relationships as $relationship) {
                $gedcomFamilyId = str_replace('fam_', 'F', $relationship->family_unit_id);
                if ($relationship->role_in_family === 'child') {
                    $gedcomContent .= "1 FAMC @{$gedcomFamilyId}@\n";
                } elseif ($relationship->role_in_family === 'partner') {
                    $gedcomContent .= "1 FAMS @{$gedcomFamilyId}@\n";
                }
            }
        }

        $familyUnits = Relationship::whereNotNull('family_unit_id')->select('family_unit_id')->distinct()->get();

        foreach ($familyUnits as $unit) {
            $familyId = $unit->family_unit_id;
            $gedcomFamilyId = str_replace('fam_', 'F', $familyId);
            $gedcomContent .= "0 @{$gedcomFamilyId}@ FAM\n";

            $partners = Relationship::where('family_unit_id', $familyId)->where('role_in_family', 'partner')->get();
            $children = Relationship::where('family_unit_id', $familyId)->where('role_in_family', 'child')->get();

            foreach ($partners as $partnerRel) {
                $partnerPerson = $people->find($partnerRel->person_id);
                if ($partnerPerson) {
                    $role = $partnerPerson->gender === 'Laki-laki' ? 'HUSB' : 'WIFE';
                    $gedcomContent .= "1 {$role} @I{$partnerPerson->id}@\n";
                }
            }
            
            foreach ($children as $childRel) {
                $gedcomContent .= "1 CHIL @I{$childRel->person_id}@\n";
            }

            $gedcomContent .= "1 MARR\n";
        }

        $gedcomContent .= "0 TRLR\n";
        $fileName = 'tmt_silsilah_export_' . now()->format('Ymd_His') . '.ged';

        return Response::make($gedcomContent, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}