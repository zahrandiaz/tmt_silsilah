<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Person extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn(string $eventName) => "Data silsilah '{$this->name}' telah di-{$eventName}")
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    
    // --- TAMBAHKAN MUTATOR BARU DI SINI ---
    /**
     * Selalu simpan atribut 'name' dalam format uppercase.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper($value),
        );
    }
    // ------------------------------------

    protected static function booted(): void
    {
        static::deleting(function (Person $person) {
            foreach ($person->photos as $photo) {
                Storage::disk('public')->delete($photo->image_path);
            }
        });
    }

    protected function birthDateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['birth_date']
                ? Carbon::parse($attributes['birth_date'])->translatedFormat('d F Y')
                : '?'
        );
    }

    protected function deathDateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['death_date']
                ? Carbon::parse($attributes['death_date'])->translatedFormat('d F Y')
                : null
        );
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('created_at', 'desc');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class);
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

        return Person::whereIn('id', $childrenIds)->orderByRaw('birth_date IS NULL, birth_date ASC')->get();
    }
    
    // ... (sisa metode Anda tetap sama) ...
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

    public function getMaxDescendantDepth(): int
    {
        $children = $this->allChildren();

        if ($children->isEmpty()) {
            return 1;
        }

        $maxDepth = 0;
        foreach ($children as $child) {
            $depth = $child->getMaxDescendantDepth();
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }
        }

        return 1 + $maxDepth;
    }

    public function getDescendantsWithSpouses(int $maxLevel, int $currentLevel = 0)
    {
        if ($currentLevel > $maxLevel) {
            return collect();
        }

        $children = $this->allChildren();
        $descendants = $children;

        foreach ($children as $child) {
            $descendants = $descendants->merge(
                $child->getDescendantsWithSpouses($maxLevel, $currentLevel + 1)
            );
        }

        return $descendants;
    }

    public function childrenWith(Person $spouse): Collection
    {
        $myFamilyUnitIds = Relationship::where('person_id', $this->id)
            ->where('role_in_family', 'partner')
            ->pluck('family_unit_id');

        $familyUnitId = Relationship::whereIn('family_unit_id', $myFamilyUnitIds)
            ->where('person_id', $spouse->id)
            ->where('role_in_family', 'partner')
            ->value('family_unit_id');

        if (!$familyUnitId) {
            return collect();
        }

        $childrenIds = Relationship::where('family_unit_id', $familyUnitId)
            ->where('role_in_family', 'child')
            ->pluck('person_id');
            
        return Person::whereIn('id', $childrenIds)->orderByRaw('birth_date IS NULL, birth_date ASC')->get();
    }

    public function getAncestorIds(): array
    {
        $ancestorIds = [];
        $parents = $this->parents();

        if ($parents->isEmpty()) {
            return [];
        }

        foreach ($parents as $parent) {
            $ancestorIds[] = $parent->id;
            $ancestorIds = array_merge($ancestorIds, $parent->getAncestorIds());
        }

        return array_unique($ancestorIds);
    }

    public function getDescendantIdsWithLevel(int $level = 1): array
    {
        $descendants = [];
        $children = $this->allChildren();

        if ($children->isEmpty()) {
            return [];
        }

        foreach ($children as $child) {
            $descendants[$child->id] = $level;
            $descendants = $descendants + $child->getDescendantIdsWithLevel($level + 1);
        }

        return $descendants;
    }

    public function isWithinOperatorScope(User $operator): bool
    {
        if ($operator->role !== 'operator' || !$operator->accessControl) {
            return false;
        }

        $accessControl = $operator->accessControl;
        $rootPerson = $accessControl->person;
        $targetPerson = $this;

        if ($rootPerson->id === $targetPerson->id) {
            return true;
        }

        if ($accessControl->generations_down > 0) {
            $descendants = $rootPerson->getDescendantIdsWithLevel();
            if (isset($descendants[$targetPerson->id])) {
                if ($descendants[$targetPerson->id] <= $accessControl->generations_down) {
                    return true;
                }
            }
        }

        if ($accessControl->generations_up > 0) {
            $rootDescendants = $targetPerson->getDescendantIdsWithLevel();
            if (isset($rootDescendants[$rootPerson->id])) {
                if ($rootDescendants[$rootPerson->id] <= $accessControl->generations_up) {
                    return true;
                }
            }
        }

        return false;
    }

    public function profilePicture()
    {
        $profilePic = $this->photos()->where('is_profile_picture', true)->first();
        return $profilePic ?: $this->photos()->first();
    }

    public function generateIndentedReport(int $maxGenerations, bool $withPhotos = false): array
    {
        $reportLines = [];

        $buildLines = function ($person, $level, $prefix = '1') use (&$buildLines, &$reportLines, $maxGenerations, $withPhotos) {
            $profilePicture = $withPhotos ? $person->profilePicture() : null;

            $reportLines[] = [
                'type' => 'person',
                'level' => $level,
                'number' => $prefix,
                'person' => $person,
                'photo_path' => $profilePicture?->image_path,
            ];

            if ($level >= $maxGenerations) {
                return;
            }

            $childCounter = 1;
            $unionedChildren = collect();

            foreach ($person->spouses() as $spouse) {
                $children = $person->childrenWith($spouse);
                if ($children->isNotEmpty()) {
                    $spouseProfilePicture = $withPhotos ? $spouse->profilePicture() : null;
                    $reportLines[] = [
                        'type' => 'spouse',
                        'level' => $level,
                        'spouse' => $spouse,
                        'photo_path' => $spouseProfilePicture?->image_path,
                    ];

                    foreach ($children as $child) {
                        $newPrefix = $prefix . '.' . $childCounter++;
                        $buildLines($child, $level + 1, $newPrefix);
                        $unionedChildren->push($child);
                    }
                }
            }

            $remainingChildren = $person->allChildren()->diff($unionedChildren);
            if($remainingChildren->isNotEmpty()) {
                 $reportLines[] = [ 'type' => 'no_spouse_separator', 'level' => $level ];
                 foreach($remainingChildren as $child) {
                     $newPrefix = $prefix . '.' . $childCounter++;
                     $buildLines($child, $level + 1, $newPrefix);
                 }
            }
        };

        $buildLines($this, 0);

        return $reportLines;
    }
}