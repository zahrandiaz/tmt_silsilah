<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

class PersonPolicy
{
    /**
     * Tentukan apakah pengguna bisa melihat, mengedit, atau menghapus data seseorang.
     * Kita akan menggabungkan semua logika ke dalam satu method `manage` untuk konsistensi.
     */
    private function canManage(User $user, Person $person): bool
    {
        // 1. Admin bisa melakukan apa saja.
        if ($user->role === 'admin') {
            return true;
        }

        // 2. Operator bisa mengelola orang dalam lingkup aksesnya.
        if ($user->role === 'operator') {
            // Memanggil method canggih yang sudah kita buat di model Person.
            return $person->isWithinOperatorScope($user);
        }

        // 3. User biasa bisa mengelola dirinya sendiri, pasangan, dan anak-anaknya.
        if ($user->role === 'user') {
            // Jika akun user tidak tertaut ke data Person, maka tidak punya hak akses.
            if (!$user->person_id) {
                return false;
            }

            // User bisa mengelola data person yang tertaut dengan akunnya (dirinya sendiri).
            if ($user->person_id === $person->id) {
                return true;
            }

            // Ambil model Person yang merepresentasikan user yang sedang login.
            $userAsPerson = Person::find($user->person_id);
            if (!$userAsPerson) {
                return false;
            }

            // Cek apakah $person yang akan diubah adalah pasangan dari user.
            if ($userAsPerson->spouses()->contains($person)) {
                return true;
            }

            // Cek apakah $person yang akan diubah adalah anak dari user.
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