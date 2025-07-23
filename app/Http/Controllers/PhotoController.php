<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// --- TAMBAHKAN BARIS INI ---
use Illuminate\Validation\Rule;

class PhotoController extends Controller
{
    /**
     * Menyimpan foto baru yang diunggah.
     */
    public function store(Request $request, Person $person)
    {
        // 1. Validasi request
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string|max:255',
            // --- TAMBAHKAN VALIDASI UNTUK KATEGORI ---
            'category' => ['required', Rule::in(['Masa Kecil', 'Masa Dewasa', 'Masa Tua', 'Galeri'])],
        ]);

        // 2. Simpan file foto ke storage
        $path = $request->file('photo')->store('photos', 'public');

        // 3. Buat record baru di database, sekarang dengan kategori
        $person->photos()->create([
            'image_path' => $path,
            'description' => $request->description,
            // --- TAMBAHKAN KATEGORI KE DATA YANG DISIMPAN ---
            'category' => $request->category,
        ]);

        // 4. Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Foto berhasil diunggah.');
    }

    /**
     * Menghapus foto.
     */
    public function destroy(Photo $photo)
    {
        $this->authorize('delete', $photo->person);
        
        Storage::disk('public')->delete($photo->image_path);

        $photo->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}