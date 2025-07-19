<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PersonPolicy
{
    /**
     * Tentukan apakah user bisa mengubah data person.
     */
    public function update(User $user, Person $person): bool
    {
        // Admin bisa mengubah siapa saja
        if ($user->role === 'admin') {
            return true;
        }

        // User hanya bisa mengubah data person yang tertaut dengan akunnya
        return $user->person_id === $person->id;
    }

    /**
     * Tentukan apakah user bisa menghapus data person.
     */
    public function delete(User $user, Person $person): bool
    {
        // Aturan untuk hapus sama dengan update
        return $this->update($user, $person);
    }
}