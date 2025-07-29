<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Person;
use App\Models\OperatorAccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        // Validasi diperbarui untuk menyertakan 'operator'
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:user,admin,operator'],
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
        // Memuat relasi accessControl untuk efisiensi
        $user->load('accessControl'); 
        $people = Person::with('user')->orderBy('name')->get();
        
        return view('users.edit', compact('user', 'people'));
    }

    public function update(Request $request, User $user)
    {
        // [PERUBAHAN BESAR] Logika validasi dan penyimpanan yang baru
        $validated = $request->validate([
            'role' => 'required|in:admin,operator,user',
            // Aturan untuk peran 'user'
            'person_id' => 'nullable|exists:people,id',
            // Aturan untuk peran 'operator'
            'operator_person_id' => 'required_if:role,operator|nullable|exists:people,id',
            'generations_up' => 'required_if:role,operator|nullable|integer|min:0',
            'generations_down' => 'required_if:role,operator|nullable|integer|min:0',
        ]);

        // Gunakan transaksi database untuk memastikan semua data konsisten
        DB::transaction(function () use ($user, $validated, $request) {
            $user->role = $validated['role'];

            if ($validated['role'] === 'operator') {
                // Jika perannya adalah OPERATOR
                $user->person_id = null; // Hapus tautan person_id lama
                $user->save();

                // Buat atau perbarui aturan akses di tabel terpisah
                OperatorAccessControl::updateOrCreate(
                    ['user_id' => $user->id], // Kunci untuk mencari
                    [
                        'person_id' => $validated['operator_person_id'],
                        'generations_up' => $validated['generations_up'],
                        'generations_down' => $validated['generations_down'],
                    ]
                );
            } else {
                // Jika perannya adalah ADMIN atau USER
                $user->person_id = $validated['person_id'];
                $user->save();

                // Hapus aturan akses operator jika ada, karena sudah tidak relevan
                $user->accessControl()->delete();
            }
        });

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->user()->id === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}