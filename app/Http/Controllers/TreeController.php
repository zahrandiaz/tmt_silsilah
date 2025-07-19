<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    public function index()
    {
        $people = Person::all();
        $relationships = Relationship::all();
        $families = $relationships->groupBy('family_unit_id');

        $chartData = [];
        $processedPeople = [];

        foreach ($families as $familyUnitId => $members) {
            $partners = $members->where('role_in_family', 'partner');
            $children = $members->where('role_in_family', 'child');

            // Buat node untuk unit keluarga
            $partnerNames = $partners->map(function ($partnerRel) use ($people) {
                $person = $people->find($partnerRel->person_id);
                return $person ? $person->name : null;
            })->filter()->implode(' & ');
            
            // Tentukan parent dari unit keluarga ini
            $familyParentNode = ''; // Default tidak punya parent
            foreach ($partners as $partner) {
                // Apakah salah satu partner adalah anak dari keluarga lain?
                $parentFamily = $relationships->firstWhere('person_id', $partner->person_id, 'role_in_family', 'child');
                if ($parentFamily) {
                    $familyParentNode = 'family_' . $parentFamily->family_unit_id;
                    break;
                }
            }
            
            // Tambahkan node unit keluarga, pastikan punya 3 elemen
            $chartData[] = [['v' => 'family_' . $familyUnitId, 'f' => $partnerNames], $familyParentNode, ''];

            // Hubungkan anak-anak ke node unit keluarga mereka
            foreach ($children as $childRel) {
                $person = $people->find($childRel->person_id);
                if ($person) {
                    $node = [
                        'v' => (string)$person->id,
                        'f' => $person->name . '<div style="font-style:italic; color:gray">' . $person->gender . '</div>'
                    ];
                    // Tambahkan node anak, pastikan punya 3 elemen
                    $chartData[] = [$node, 'family_' . $familyUnitId, ''];
                    $processedPeople[] = $person->id;
                }
            }

            // Tandai partner juga sudah diproses
            foreach ($partners as $partnerRel) {
                $processedPeople[] = $partnerRel->person_id;
            }
        }
        
        // Tambahkan orang-orang yang belum diproses (generasi pertama/root)
        $unprocessedPeople = $people->whereNotIn('id', $processedPeople);
        foreach ($unprocessedPeople as $person) {
             $node = [
                'v' => (string)$person->id,
                'f' => $person->name . '<div style="font-style:italic; color:gray">' . $person->gender . '</div>'
            ];
            // Tambahkan node root, pastikan punya 3 elemen
            $chartData[] = [$node, '', ''];
        }
        
        return view('tree.index', ['chartData' => json_encode($chartData)]);
    }
}