<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
// --- TAMBAHKAN BARIS INI ---
use Illuminate\Support\Facades\Storage;

class Person extends Model
{
    use HasFactory;
    protected $guarded = [];

    // --- AWAL PERUBAHAN ---

    /**
     * The "booted" method of the model.
     * Dijalankan sekali saat model diinisialisasi.
     */
    protected static function booted(): void
    {
        // Daftarkan sebuah event listener yang akan berjalan SEBELUM data Person dihapus.
        static::deleting(function (Person $person) {
            // Ambil semua foto yang dimiliki oleh orang ini.
            foreach ($person->photos as $photo) {
                // Hapus file fisik dari direktori storage.
                Storage::disk('public')->delete($photo->image_path);
            }
        });
    }

    // --- AKHIR PERUBAHAN ---

    /**
     * Mendefinisikan bahwa satu Person bisa memiliki banyak foto.
     */
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

    // --- TAMBAHKAN METHOD BARU INI ---

    /**
     * Mengambil semua ID keturunan (anak, cucu, dst.) secara rekursif.
     *
     * @return array
     */
    public function getDescendantIds(): array
    {
        $descendantIds = [];
        $children = $this->allChildren(); // Menggunakan relasi allChildren() yang sudah ada

        if ($children->isEmpty()) {
            return [];
        }

        foreach ($children as $child) {
            $descendantIds[] = $child->id;
            // Secara rekursif memanggil method yang sama untuk setiap anak
            // dan menggabungkan hasilnya.
            $descendantIds = array_merge($descendantIds, $child->getDescendantIds());
        }

        return $descendantIds;
    }
}