<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_admin_can_create_a_new_person(): void
    {
        // 1. Arrange (Given)
        $admin = User::factory()->create(['role' => 'admin']);

        $personData = [
            'name' => 'Budi Sanjaya',
            'gender' => 'Laki-laki',
            'birth_date' => '1980-05-15',
            'birth_place' => 'Jakarta',
            'death_date' => null,
            'death_place' => null,
            'phone_number' => '081234567890',
            'biography' => 'Seorang tokoh penting dalam keluarga.',
            'is_key_figure' => true,
        ];

        // 2. Act (When)
        $response = $this->actingAs($admin)->post(route('people.store'), $personData);

        // 3. Assert (Then)
        $response->assertRedirect(route('people.index'));
        $response->assertSessionHasNoErrors();

        // PERBAIKAN: Sesuaikan ekspektasi dengan data yang benar-benar disimpan di DB
        $this->assertDatabaseHas('people', [
            'name' => 'BUDI SANJAYA',      // Ini sudah benar
            'gender' => 'Laki-laki',        // Sesuaikan dengan data asli di DB
            'birth_place' => 'Jakarta',     // Sesuaikan dengan data asli di DB
        ]);

        $this->assertCount(1, Person::all());
    }

    #[Test]
    public function an_admin_can_update_a_person_data(): void
    {
        // 1. Arrange (Given)
        $admin = User::factory()->create(['role' => 'admin']);
        // Buat satu data orang yang akan kita ubah
        $person = Person::factory()->create([
            'name' => 'Nama Lama',
            'birth_place' => 'Kota Lama',
        ]);

        // Data baru untuk proses update
        $updatedData = [
            'name' => 'Nama Baru Setelah Diubah',
            'gender' => 'Perempuan',
            'birth_place' => 'Kota Baru',
            'phone_number' => '089988776655',
            'birth_date' => '1990-01-01',
        ];

        // 2. Act (When)
        // Kirim request PUT/PATCH ke route 'people.update'
        $response = $this->actingAs($admin)->put(route('people.update', $person), $updatedData);

        // 3. Assert (Then)
        // Setelah update, harusnya redirect ke halaman detail orang tersebut
        $response->assertRedirect(route('people.show', $person));
        $response->assertSessionHasNoErrors();

        // Pastikan database sekarang berisi data yang BARU
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'name' => 'NAMA BARU SETELAH DIUBAH', // Ingat, ada mutator uppercase
            'birth_place' => 'Kota Baru',
        ]);

        // Pastikan database SUDAH TIDAK LAGI berisi data yang LAMA
        $this->assertDatabaseMissing('people', [
            'id' => $person->id,
            'name' => 'NAMA LAMA',
        ]);
    }

    #[Test]
    public function an_admin_can_delete_a_person(): void
    {
        // 1. Arrange (Given)
        $admin = User::factory()->create(['role' => 'admin']);
        $person = Person::factory()->create(); // Buat 1 orang untuk dihapus

        // Pastikan data awalnya ada
        $this->assertCount(1, Person::all());

        // 2. Act (When)
        // Kirim request DELETE ke route 'people.destroy'
        $response = $this->actingAs($admin)->delete(route('people.destroy', $person));

        // 3. Assert (Then)
        // Setelah hapus, harus kembali ke halaman daftar
        $response->assertRedirect(route('people.index'));
        $response->assertSessionHas('success'); // Opsional: cek pesan sukses

        // Pengecekan terpenting: Pastikan data sudah TIDAK ADA lagi di database
        $this->assertDatabaseMissing('people', [
            'id' => $person->id,
        ]);

        // Pastikan jumlah total orang sekarang adalah 0
        $this->assertCount(0, Person::all());
    }

    #[Test]
    public function creating_a_person_requires_a_name(): void
    {
        // 1. Arrange (Given)
        $admin = User::factory()->create(['role' => 'admin']);

        // Data tidak valid: 'name' sengaja dikosongkan
        $personData = Person::factory()->make(['name' => ''])->toArray();

        // 2. Act (When)
        $response = $this->actingAs($admin)->post(route('people.store'), $personData);

        // 3. Assert (Then)
        // Pastikan response adalah redirect kembali (karena validasi gagal)
        $response->assertRedirect();
        // Pastikan session berisi error spesifik untuk field 'name'
        $response->assertSessionHasErrors('name');

        // Pastikan tidak ada data baru yang dibuat di database
        $this->assertCount(0, Person::all());
    }

    #[Test]
    public function a_non_admin_user_cannot_create_a_person(): void
    {
        // 1. Arrange (Given)
        $user = User::factory()->create(['role' => 'user']); 
        
        $personData = [
            'name' => 'Coba-Coba Saja',
            'gender' => 'Laki-laki',
        ];

        // 2. Act (When)
        $response = $this->actingAs($user)->post(route('people.store'), $personData);

        // 3. Assert (Then)
        // --- PERBAIKAN FINAL ---
        // Sekarang aplikasi kita sudah aman, respons yang benar adalah 403 Forbidden.
        $response->assertForbidden();

        // Pastikan tidak ada data baru yang masuk ke database
        $this->assertDatabaseMissing('people', [
            'name' => 'COBA-COBA SAJA'
        ]);
    }

    #[Test]
    public function a_non_admin_user_cannot_update_a_person(): void
    {
        // 1. Arrange (Given)
        $user = User::factory()->create(['role' => 'user']);
        $person = Person::factory()->create();
        
        // --- PERBAIKAN DI SINI ---
        // Sediakan data yang LENGKAP dan VALID agar lolos dari validasi
        // dan bisa diuji oleh lapisan otorisasi (Policy).
        $updateData = [
            'name' => 'Nama Diubah Secara Ilegal',
            'gender' => 'Laki-laki', // Field wajib
            'birth_date' => '2000-01-01' // Field wajib lainnya dari UpdatePersonRequest
        ];

        // 2. Act (When)
        $response = $this->actingAs($user)->put(route('people.update', $person), $updateData);

        // 3. Assert (Then)
        // Sekarang aplikasi akan merespons dengan 403 karena otorisasi yang gagal.
        $response->assertForbidden();
    }

    #[Test]
    public function a_non_admin_user_cannot_delete_a_person(): void
    {
        // 1. Arrange (Given)
        $user = User::factory()->create(['role' => 'user']);
        $person = Person::factory()->create();

        // 2. Act (When)
        // Bertindak sebagai user biasa dan mencoba menghapus data
        $response = $this->actingAs($user)->delete(route('people.destroy', $person));

        // 3. Assert (Then)
        // Pastikan aplikasi merespons dengan 403 (Forbidden)
        $response->assertForbidden();

        // Pastikan data masih ada di database setelah percobaan ilegal
        $this->assertDatabaseHas('people', ['id' => $person->id]);
    }
}