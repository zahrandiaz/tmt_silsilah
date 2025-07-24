<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    // Method baru untuk menampilkan form tambah pengguna
    public function create()
    {
        return view('users.create');
    }

    // Method baru untuk menyimpan pengguna baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:user,admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil dibuat.');
    }

    public function edit(User $user)
    {
        // --- AWAL PERUBAHAN ---
        // Ambil semua data Person, dan sertakan informasi user yang tertaut (jika ada)
        // untuk menghindari query N+1 di dalam view.
        $people = Person::with('user')->orderBy('name')->get();
        // --- AKHIR PERUBAHAN ---
        
        return view('users.edit', compact('user', 'people'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,user',
            'person_id' => 'nullable|exists:people,id',
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    // Method baru untuk menghapus pengguna
    public function destroy(User $user)
    {
        // Pencegahan agar admin tidak bisa menghapus akunnya sendiri
        if (auth()->user()->id === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}