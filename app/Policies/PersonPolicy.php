<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\Relationship;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PersonPolicy
{
    /**
     * Tentukan apakah user bisa mengubah data person.
     */
    public function update(User $user, Person $person): bool
    {
        // 2. Admin bisa mengubah siapa saja
        if ($user->role === 'admin') {
            return true;
        }

        // 3. Jika akun user tidak tertaut ke data Person, maka tidak punya hak akses
        if (!$user->person_id) {
            return false;
        }

        // 4. User bisa mengubah data person yang tertaut dengan akunnya (dirinya sendiri)
        if ($user->person_id === $person->id) {
            return true;
        }

        // 5. User bisa mengubah data anak-anaknya
        // Cari semua unit keluarga di mana user ini adalah seorang 'partner' (orang tua)
        $familyUnitIds = Relationship::where('person_id', $user->person_id)
            ->where('role_in_family', 'partner')
            ->pluck('family_unit_id');

        // Jika user bukan partner di keluarga manapun, maka tidak punya anak untuk diedit
        if ($familyUnitIds->isEmpty()) {
            return false;
        }

        // Cek apakah person yang akan diubah adalah 'child' di salah satu unit keluarga tersebut
        $isChild = Relationship::whereIn('family_unit_id', $familyUnitIds)
            ->where('person_id', $person->id)
            ->where('role_in_family', 'child')
            ->exists();

        return $isChild;
    }

    /**
     * Tentukan apakah user bisa menghapus data person.
     */
    public function delete(User $user, Person $person): bool
    {
        // Aturan untuk hapus kita buat sama dengan update
        return $this->update($user, $person);
    }
}