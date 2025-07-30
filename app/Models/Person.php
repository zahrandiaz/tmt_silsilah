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

        // Mengurutkan berdasarkan tanggal lahir, dengan data kosong (null) di akhir
        return Person::whereIn('id', $childrenIds)->orderByRaw('birth_date IS NULL, birth_date ASC')->get();
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

    // --- TAMBAHKAN METHOD BARU INI ---

    /**
     * Menghitung kedalaman generasi keturunan terpanjang dari orang ini.
     * Generasi orang ini dihitung sebagai 1.
     *
     * @return int
     */
    public function getMaxDescendantDepth(): int
    {
        $children = $this->allChildren();

        if ($children->isEmpty()) {
            // Jika tidak punya anak, kedalamannya adalah 1 (dirinya sendiri).
            return 1;
        }

        $maxDepth = 0;
        foreach ($children as $child) {
            // Cari kedalaman maksimum di antara semua anak.
            $depth = $child->getMaxDescendantDepth();
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }
        }

        // Tambahkan 1 (untuk generasi saat ini) ke kedalaman maksimum anak.
        return 1 + $maxDepth;
    }

    /**
     * Mengambil semua keturunan beserta pasangan hingga kedalaman (level) tertentu.
     *
     * @param int $maxLevel
     * @param int $currentLevel
     * @return \Illuminate\Support\Collection
     */
    public function getDescendantsWithSpouses(int $maxLevel, int $currentLevel = 0)
    {
        // Hentikan rekursi jika sudah mencapai level maksimal
        if ($currentLevel > $maxLevel) {
            return collect();
        }

        // Ambil semua anak dari person saat ini
        $children = $this->allChildren();
        $descendants = $children;

        // Untuk setiap anak, panggil fungsi ini lagi secara rekursif
        foreach ($children as $child) {
            $descendants = $descendants->merge(
                $child->getDescendantsWithSpouses($maxLevel, $currentLevel + 1)
            );
        }

        return $descendants;
    }

    /**
     * Mengambil anak-anak yang dimiliki bersama pasangan (spouse) tertentu.
     *
     * @param Person $spouse
     * @return Collection
     */
    public function childrenWith(Person $spouse): Collection
    {
        // 1. Cari semua unit keluarga di mana 'person' ini adalah partner
        $myFamilyUnitIds = Relationship::where('person_id', $this->id)
            ->where('role_in_family', 'partner')
            ->pluck('family_unit_id');

        // 2. Dari unit-unit tersebut, cari satu unit yang juga berisi 'spouse' sebagai partner
        $familyUnitId = Relationship::whereIn('family_unit_id', $myFamilyUnitIds)
            ->where('person_id', $spouse->id)
            ->where('role_in_family', 'partner')
            ->value('family_unit_id');

        // 3. Jika tidak ada unit keluarga bersama, kembalikan koleksi kosong
        if (!$familyUnitId) {
            return collect();
        }

        // 4. Ambil semua ID anak dari unit keluarga yang spesifik tersebut
        $childrenIds = Relationship::where('family_unit_id', $familyUnitId)
            ->where('role_in_family', 'child')
            ->pluck('person_id');
            
        // 5. Kembalikan model Person dari anak-anak tersebut
        // Mengurutkan berdasarkan tanggal lahir, dengan data kosong (null) di akhir
        return Person::whereIn('id', $childrenIds)->orderByRaw('birth_date IS NULL, birth_date ASC')->get();
    }

    /**
     * Mengambil semua ID leluhur dari orang ini dalam bentuk array.
     *
     * @return array
     */
    public function getAncestorIds(): array
    {
        $ancestorIds = [];
        $parents = $this->parents();

        if ($parents->isEmpty()) {
            return [];
        }

        foreach ($parents as $parent) {
            $ancestorIds[] = $parent->id;
            // Gabungkan dengan ID leluhur dari orang tua
            $ancestorIds = array_merge($ancestorIds, $parent->getAncestorIds());
        }

        return array_unique($ancestorIds);
    }

    /**
     * Mengambil semua ID keturunan beserta level generasinya.
     *
     * @param int $level
     * @return array
     */
    public function getDescendantIdsWithLevel(int $level = 1): array
    {
        $descendants = [];
        $children = $this->allChildren();

        if ($children->isEmpty()) {
            return [];
        }

        foreach ($children as $child) {
            // Simpan anak ini beserta levelnya
            $descendants[$child->id] = $level;
            // Gabungkan dengan keturunan dari anak ini
            $descendants = $descendants + $child->getDescendantIdsWithLevel($level + 1);
        }

        return $descendants;
    }

    /**
     * Memeriksa apakah orang ini berada dalam lingkup akses seorang operator.
     *
     * @param User $operator
     * @return bool
     */
    public function isWithinOperatorScope(User $operator): bool
    {
        if ($operator->role !== 'operator' || !$operator->accessControl) {
            return false;
        }

        $accessControl = $operator->accessControl;
        $rootPerson = $accessControl->person; // Ambil "akar" silsilah operator
        $targetPerson = $this; // Orang yang sedang ingin diakses

        // 1. Cek apakah target adalah si "akar" itu sendiri
        if ($rootPerson->id === $targetPerson->id) {
            return true;
        }

        // 2. Cek apakah target adalah KETURUNAN dari "akar"
        if ($accessControl->generations_down > 0) {
            $descendants = $rootPerson->getDescendantIdsWithLevel();
            // Jika target ada di dalam daftar keturunan
            if (isset($descendants[$targetPerson->id])) {
                // Cek apakah levelnya masih dalam jangkauan
                if ($descendants[$targetPerson->id] <= $accessControl->generations_down) {
                    return true;
                }
            }
        }

        // 3. Cek apakah target adalah LELUHUR dari "akar"
        if ($accessControl->generations_up > 0) {
            // Untuk menghitung jarak ke atas, kita cek sebaliknya:
            // Apakah "akar" adalah KETURUNAN dari target?
            $rootDescendants = $targetPerson->getDescendantIdsWithLevel();
            // Jika "akar" ada di dalam daftar keturunan target
            if (isset($rootDescendants[$rootPerson->id])) {
                // Cek apakah levelnya (jarak ke atas) masih dalam jangkauan
                if ($rootDescendants[$rootPerson->id] <= $accessControl->generations_up) {
                    return true;
                }
            }
        }

        return false;
    }

     /**
     * Mendapatkan foto profil yang telah ditentukan.
     * Jika tidak ada, akan mengembalikan foto pertama yang diunggah.
     *
     * @return Model|null
     */
    public function profilePicture()
    {
        // Cari foto yang ditandai sebagai foto profil
        $profilePic = $this->photos()->where('is_profile_picture', true)->first();

        // Jika tidak ada, kembalikan foto pertama sebagai fallback
        return $profilePic ?: $this->photos()->first();
    }

    /**
     * Menghasilkan data laporan keturunan berinden dalam bentuk array.
     *
     * @param int $maxGenerations
     * @return array
     */
    public function generateIndentedReport(int $maxGenerations, bool $withPhotos = false): array
    {
        $reportLines = [];

        $buildLines = function ($person, $level, $prefix = '1') use (&$buildLines, &$reportLines, $maxGenerations, $withPhotos) {
            $profilePicture = $withPhotos ? $person->profilePicture() : null;

            $reportLines[] = [
                'type' => 'person',
                'level' => $level,
                'number' => $prefix, // Nomor hierarkis
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

            // Menangani anak tanpa pasangan yang tercatat
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