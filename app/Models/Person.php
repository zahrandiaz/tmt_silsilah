<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
// --- TAMBAHKAN DUA BARIS INI ---
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Person extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::deleting(function (Person $person) {
            foreach ($person->photos as $photo) {
                Storage::disk('public')->delete($photo->image_path);
            }
        });
    }

    // --- AWAL PENAMBAHAN ACCESSOR ---

    /**
     * Mendapatkan atribut tanggal lahir yang sudah diformat.
     */
    protected function birthDateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['birth_date']
                ? Carbon::parse($attributes['birth_date'])->translatedFormat('d F Y')
                : '?'
        );
    }

    /**
     * Mendapatkan atribut tanggal wafat yang sudah diformat.
     */
    protected function deathDateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['death_date']
                ? Carbon::parse($attributes['death_date'])->translatedFormat('d F Y')
                : null
        );
    }

    // --- AKHIR PENAMBAHAN ACCESSOR ---

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('created_at', 'desc');
    }

    public function father(): ?Person
    {
        return $this->parents()->firstWhere('gender', 'Laki-laki');
    }

    public function mother(): ?Person
    {
        return $this->parents()->firstWhere('gender', 'Perempuan');
    }

    public function parents(): Collection
    {
        $childRelation = Relationship::where('person_id', $this->id)->where('role_in_family', 'child')->first();
        if (!$childRelation) return collect();

        $parentIds = Relationship::where('family_unit_id', $childRelation->family_unit_id)
            ->where('role_in_family', 'partner')->pluck('person_id');
            
        return Person::whereIn('id', $parentIds)->get();
    }

    public function spouses(): Collection
    {
        $familyUnitIds = Relationship::where('person_id', $this->id)
            ->where('role_in_family', 'partner')->pluck('family_unit_id');
        if ($familyUnitIds->isEmpty()) return collect();

        $spouseIds = Relationship::whereIn('family_unit_id', $familyUnitIds)
            ->where('role_in_family', 'partner')->where('person_id', '!=', $this->id)->pluck('person_id');

        return Person::whereIn('id', $spouseIds)->get()->unique('id');
    }
    
    public function allChildren(): Collection
    {
        $familyUnitIds = Relationship::where('person_id', $this->id)
            ->where('role_in_family', 'partner')->pluck('family_unit_id');
        if ($familyUnitIds->isEmpty()) return collect();
        
        $childrenIds = Relationship::whereIn('family_unit_id', $familyUnitIds)
            ->where('role_in_family', 'child')->pluck('person_id');

        return Person::whereIn('id', $childrenIds)->orderBy('birth_date')->get();
    }

    public function getAncestorTree(): array
    {
        $tree = [];
        $father = $this->father();
        $mother = $this->mother();
        if ($father) $tree[] = ['person' => $father, 'children' => $father->getAncestorTree()];
        if ($mother) $tree[] = ['person' => $mother, 'children' => $mother->getAncestorTree()];
        return $tree;
    }

    public function getDescendantIds(): array
    {
        $descendantIds = [];
        $children = $this->allChildren();

        if ($children->isEmpty()) {
            return [];
        }

        foreach ($children as $child) {
            $descendantIds[] = $child->id;
            $descendantIds = array_merge($descendantIds, $child->getDescendantIds());
        }

        return $descendantIds;
    }

    public function getBreadcrumbs(): Collection
    {
        $breadcrumbs = collect();
        $current = $this;

        while ($parent = $current->father() ?? $current->mother()) {
            $breadcrumbs->push($parent);
            $current = $parent;
        }

        return $breadcrumbs->reverse();
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}