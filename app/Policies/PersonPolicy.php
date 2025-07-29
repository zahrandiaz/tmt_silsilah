<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

class PersonPolicy
{
    /**
     * Tentukan apakah pengguna bisa melihat, mengedit, atau menghapus data seseorang.
     */
    private function canManage(User $user, Person $person): bool
    {
        // 1. Admin bisa melakukan apa saja.
        if ($user->role === 'admin') {
            return true;
        }

        // 2. Operator bisa mengelola orang dalam lingkup aksesnya DAN pasangannya.
        if ($user->role === 'operator') {
            // Cek pertama: Apakah orang ini sendiri berada dalam lingkup?
            if ($person->isWithinOperatorScope($user)) {
                return true;
            }

            // [PENAMBAHAN LOGIKA]
            // Cek kedua: Jika bukan, apakah orang ini adalah PASANGAN dari seseorang yang berada dalam lingkup?
            foreach ($person->spouses() as $spouse) {
                if ($spouse->isWithinOperatorScope($user)) {
                    return true; // Izin diberikan jika pasangannya berada dalam lingkup.
                }
            }
        }

        // 3. User biasa bisa mengelola dirinya sendiri, pasangan, dan anak-anaknya.
        if ($user->role === 'user') {
            if (!$user->person_id) {
                return false;
            }
            if ($user->person_id === $person->id) {
                return true;
            }

            $userAsPerson = Person::find($user->person_id);
            if (!$userAsPerson) {
                return false;
            }

            if ($userAsPerson->spouses()->contains($person)) {
                return true;
            }

            if ($userAsPerson->allChildren()->contains($person)) {
                return true;
            }
        }

        // Jika tidak ada kondisi di atas yang terpenuhi, tolak akses.
        return false;
    }

    /**
     * Tentukan apakah user bisa mengubah data person.
     */
    public function update(User $user, Person $person): bool
    {
        return $this->canManage($user, $person);
    }

    /**
     * Tentukan apakah user bisa menghapus data person.
     */
    public function delete(User $user, Person $person): bool
    {
        return $this->canManage($user, $person);
    }
}