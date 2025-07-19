<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportGedcom()
    {
        $people = Person::all();
        $relationships = Relationship::all();
        $families = $relationships->groupBy('family_unit_id');

        $gedcomContent = "0 HEAD\n";
        $gedcomContent .= "1 SOUR TMT Silsilah\n";
        $gedcomContent .= "1 CHAR UTF-8\n";
        $gedcomContent .= "1 GEDC\n";
        $gedcomContent .= "2 VERS 5.5.1\n";
        $gedcomContent .= "2 FORM LINEAGE-LINKED\n";

        // 1. Tambahkan semua individu (INDI)
        foreach ($people as $person) {
            $gedcomContent .= "0 @I{$person->id}@ INDI\n";
            $gedcomContent .= "1 NAME {$person->name}\n";
            $gedcomContent .= "1 SEX " . ($person->gender === 'Laki-laki' ? 'M' : 'F') . "\n";

            if ($person->birth_date) {
                $gedcomContent .= "1 BIRT\n";
                $gedcomContent .= "2 DATE " . strtoupper(date('d M Y', strtotime($person->birth_date))) . "\n";
                if ($person->birth_place) {
                    $gedcomContent .= "2 PLAC {$person->birth_place}\n";
                }
            }

            if ($person->death_date) {
                $gedcomContent .= "1 DEAT\n";
                $gedcomContent .= "2 DATE " . strtoupper(date('d M Y', strtotime($person->death_date))) . "\n";
                if ($person->death_place) {
                    $gedcomContent .= "2 PLAC {$person->death_place}\n";
                }
            }
        }

        // 2. Tambahkan semua keluarga (FAM) dan hubungannya
        foreach ($families as $familyUnitId => $members) {
            $gedcomContent .= "0 @F{$familyUnitId}@ FAM\n";

            $partners = $members->where('role_in_family', 'partner');
            $children = $members->where('role_in_family', 'child');

            foreach ($partners as $partnerRel) {
                $partnerPerson = $people->find($partnerRel->person_id);
                if ($partnerPerson) {
                    if ($partnerPerson->gender === 'Laki-laki') {
                        $gedcomContent .= "1 HUSB @I{$partnerRel->person_id}@\n";
                    } else {
                        $gedcomContent .= "1 WIFE @I{$partnerRel->person_id}@\n";
                    }
                }
            }
            
            foreach ($children as $childRel) {
                $gedcomContent .= "1 CHIL @I{$childRel->person_id}@\n";
            }
        }
        
        $gedcomContent .= "0 TRLR\n";

        return response($gedcomContent, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="silsilah_export_'.date('Y-m-d').'.ged"',
        ]);
    }
}