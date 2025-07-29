<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;

class PhotoController extends Controller
{
    /**
     * Menyimpan foto baru yang diunggah dan dikompres ke format WebP.
     */
    public function store(Request $request, Person $person)
    {
        // 1. Validasi request (tetap sama)
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Tambahkan webp ke mimes
            'description' => 'nullable|string|max:255',
            'category' => ['required', Rule::in(['Masa Kecil', 'Masa Dewasa', 'Masa Tua', 'Galeri'])],
        ]);

        // --- AWAL PERUBAHAN LOGIKA ENCODING ---

        // 2. Inisialisasi Image Manager
        $manager = ImageManager::gd();

        // 3. Baca file yang diunggah
        $file = $request->file('photo');
        $image = $manager->read($file);
        
        // 4. Kompres gambar ke format WebP dengan kualitas 75%
        $encoded = $image->toWebp(75); 

        // 5. Buat nama file unik dengan ekstensi .webp
        $hash = md5($encoded->__toString() . time());
        $path = "photos/{$hash}.webp"; // Ubah ekstensi menjadi .webp

        // 6. Simpan gambar yang sudah dikompres ke storage
        Storage::disk('public')->put($path, $encoded);

        // --- AKHIR PERUBAHAN LOGIKA ENCODING ---

        // 7. Buat record baru di database
        $person->photos()->create([
            'image_path' => $path,
            'description' => $request->description,
            'category' => $request->category,
        ]);

        // 8. Kembali ke halaman sebelumnya
        return back()->with('success', 'Foto berhasil diunggah dan dikompres ke WebP.');
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

    /**
     * Menetapkan sebuah foto sebagai foto profil.
     */
    public function setAsProfilePicture(Request $request, Photo $photo)
    {
        // Otorisasi: pastikan user boleh mengupdate data person ini
        $this->authorize('update', $photo->person);

        DB::transaction(function () use ($photo) {
            // 1. Nonaktifkan semua foto profil lain untuk orang ini
            $photo->person->photos()->update(['is_profile_picture' => false]);
            
            // 2. Aktifkan foto yang dipilih sebagai foto profil
            $photo->update(['is_profile_picture' => true]);
        });

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}