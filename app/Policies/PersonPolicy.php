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
        // 1. Admin bisa mengubah siapa saja
        if ($user->role === 'admin') {
            return true;
        }

        // 2. Jika akun user tidak tertaut ke data Person, maka tidak punya hak akses
        if (!$user->person_id) {
            return false;
        }

        // 3. User bisa mengubah data person yang tertaut dengan akunnya (dirinya sendiri)
        if ($user->person_id === $person->id) {
            return true;
        }

        // --- AWAL PERUBAHAN ---
        // 4. User bisa mengubah data pasangannya
        // Ambil dulu model Person yang merepresentasikan user yang sedang login
        $userAsPerson = Person::find($user->person_id);
        
        // Cek apakah $person yang akan diubah ada di dalam koleksi pasangan dari $userAsPerson
        if ($userAsPerson && $userAsPerson->spouses()->contains($person)) {
            return true;
        }
        // --- AKHIR PERUBAHAN ---

        // 5. User bisa mengubah data anak-anaknya
        $familyUnitIds = Relationship::where('person_id', $user->person_id)
            ->where('role_in_family', 'partner')
            ->pluck('family_unit_id');

        // Cek anak hanya jika user ini adalah orang tua di sebuah unit keluarga
        if ($familyUnitIds->isNotEmpty()) {
            $isChild = Relationship::whereIn('family_unit_id', $familyUnitIds)
                ->where('person_id', $person->id)
                ->where('role_in_family', 'child')
                ->exists();

            if ($isChild) {
                return true;
            }
        }

        // Jika tidak ada kondisi di atas yang terpenuhi, tolak akses
        return false;
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