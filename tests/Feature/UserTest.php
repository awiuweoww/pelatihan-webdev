<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Pengujian fitur (Feature Test) untuk modul manajemen User,
 *               mencakup pengujian alur CRUD, validasi data, dan enkripsi password.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi feature test untuk operasi CRUD user.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase; // Reset database testing (in-memory SQLite) pada setiap eksekusi method test

    /**
     * Memastikan halaman daftar pengguna (/users) dapat diakses dengan respons status HTTP 200.
     * Serta memastikan data user yang ada di database ditampilkan di tampilan halaman.
     */
    public function test_halaman_index_user_dapat_ditampilkan(): void
    {
        // 1. Arrange: Buat satu data dummy user
        $user = User::factory()->create([
            'name' => 'Chandra Pengguna',
            'email' => 'chandra.test@example.com',
        ]);

        // 2. Act: Kunjungi URL daftar pengguna
        $response = $this->get(route('users.index'));

        // 3. Assert: Pastikan halaman berhasil dimuat dan menampilkan nama user
        $response->assertStatus(200);
        $response->assertSee('Chandra Pengguna');
    }

    /**
     * Memastikan halaman formulir tambah pengguna (/users/create) dapat diakses dengan baik.
     */
    public function test_halaman_tambah_user_dapat_ditampilkan(): void
    {
        // Act: Kunjungi URL tambah pengguna
        $response = $this->get(route('users.create'));

        // Assert: Pastikan status HTTP 200 (OK) dan menampilkan judul/teks form
        $response->assertStatus(200);
        $response->assertSee('Tambah User');
    }

    /**
     * Menguji alur penyimpanan data user baru (POST /users).
     * Memverifikasi data tersimpan di tabel database dan password ter-hash secara aman.
     */
    public function test_user_berhasil_dibuat_dengan_data_valid(): void
    {
        // 1. Arrange: Siapkan data payload pengguna baru
        $userData = [
            'name'                  => 'Ahmad Kurniawan',
            'email'                 => 'ahmad@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. Act: Kirim request POST ke endpoint penyimpanan
        $response = $this->post(route('users.store'), $userData);

        // 3. Assert:
        // - Harus me-redirect kembali ke halaman index
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // - Pastikan record user ada di database
        $this->assertDatabaseHas('users', [
            'name'  => 'Ahmad Kurniawan',
            'email' => 'ahmad@example.com',
        ]);

        // - Pastikan password di database terenkripsi (bukan plain-text)
        $createdUser = User::where('email', 'ahmad@example.com')->first();
        $this->assertTrue(Hash::check('password123', $createdUser->password));
    }

    /**
     * Menguji validasi email duplikat (unik).
     * Sistem harus menolak pendaftaran jika email sudah terdaftar sebelumnya.
     */
    public function test_user_gagal_dibuat_jika_email_duplikat(): void
    {
        // 1. Arrange: Buat user awal dengan email tertentu
        User::factory()->create([
            'email' => 'duplikat@example.com',
        ]);

        // 2. Act: Coba buat user baru dengan email yang sama persis
        $response = $this->post(route('users.store'), [
            'name'                  => 'User Duplikat',
            'email'                 => 'duplikat@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        // 3. Assert: Request harus gagal validasi pada field 'email'
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Memastikan form edit user (/users/{user}/edit) dapat dimuat dengan data yang sesuai.
     */
    public function test_halaman_edit_user_dapat_ditampilkan(): void
    {
        // 1. Arrange: Buat user yang akan diedit
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
        ]);

        // 2. Act: Buka halaman form edit
        $response = $this->get(route('users.edit', $user));

        // 3. Assert: Status HTTP 200 dan form memuat data user
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
    }

    /**
     * Menguji pembaruan data user tanpa mengubah password lama.
     * Jika field password dikosongkan saat update, password lama harus tetap utuh.
     */
    public function test_user_dapat_diupdate_tanpa_mengubah_password(): void
    {
        // 1. Arrange: Buat user awal dengan password spesifik
        $user = User::factory()->create([
            'name'     => 'Nama Lama',
            'email'    => 'lama@example.com',
            'password' => Hash::make('passwordLama123'),
        ]);

        // 2. Act: Update hanya nama dan email, biarkan password kosong
        $response = $this->put(route('users.update', $user), [
            'name'     => 'Nama Baru',
            'email'    => 'baru@example.com',
            'password' => '', // Dikosongkan
        ]);

        // 3. Assert:
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // Pastikan nama & email terupdate di database
        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Nama Baru',
            'email' => 'baru@example.com',
        ]);

        // Pastikan password lama tetap valid
        $user->refresh();
        $this->assertTrue(Hash::check('passwordLama123', $user->password));
    }

    /**
     * Menguji penghapusan user (DELETE /users/{user}).
     * Memastikan data pengguna benar-benar terhapus dari tabel database.
     */
    public function test_user_dapat_dihapus(): void
    {
        // 1. Arrange: Buat user yang akan dihapus
        $user = User::factory()->create();

        // 2. Act: Kirim request delete
        $response = $this->delete(route('users.destroy', $user));

        // 3. Assert:
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // Pastikan record sudah tidak ada lagi di database
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
