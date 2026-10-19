<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Controller untuk manajemen data pengguna (User) termasuk pembuatan,
 *               pembaruan profil, dan penghapusan akun.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi controller user dengan operasi CRUD dan enkripsi password.
 */

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar seluruh data pengguna (User) dengan pagination.
     *
     * Alur Logic:
     * 1. Mengambil data user dari database sebanyak 10 record per halaman (paginate).
     * 2. Mengirimkan collection users ke view 'users.index'.
     */
    public function index()
    {
        // Ambil data user dengan pagination 10 baris per halaman
        $users = User::paginate(10);

        // Render view daftar pengguna dengan data users
        return view('users.index', [
            'users' => $users
        ]);
    }

    /**
     * Menampilkan formulir pendaftaran akun pengguna baru.
     *
     * Alur Logic:
     * 1. Merender halaman view form 'users.create'.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Menyimpan data pengguna baru ke database.
     *
     * Alur Logic:
     * 1. Menerima request yang telah divalidasi oleh UserRequest (nama, email unik, dan password).
     * 2. Melakukan enkripsi/hashing satu arah pada password menggunakan Hash::make() (Bcrypt) demi keamanan.
     * 3. Menyimpan record baru ke tabel 'users'.
     * 4. Mengalihkan (redirect) kembali ke halaman index dengan notifikasi flash session 'success'.
     *
     * @param UserRequest $request
     */
    public function store(UserRequest $request)
    {
        // Simpan data pengguna baru dengan enkripsi password
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password sebelum masuk ke database
        ]);

        // Redirect ke halaman tabel user dengan pesan sukses
        return to_route('users.index')->with('success', 'Data user berhasil ditambah');
    }

    /**
     * Menampilkan detail spesifik pengguna (jika diperlukan).
     *
     * Catatan: Dinonaktifkan via except(['show']) pada route resource.
     *
     * @param string $id
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Menampilkan form untuk mengedit informasi pengguna tertentu.
     *
     * Alur Logic:
     * 1. Menggunakan Route Model Binding (User $user) yang otomatis mencari record berdasarkan ID di URL.
     * 2. Menampilkan view form edit dengan membawa instance data user yang akan diubah.
     *
     * @param User $user
     */
    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user
        ]);
    }

    /**
     * Memperbarui data pengguna yang ada di database.
     *
     * Alur Logic:
     * 1. Mengambil data nama dan email dari request yang sudah tervalidasi.
     * 2. Pengecekan Password: Jika input password diisi (filled), lakukan hash dan sertakan ke update array.
     *    Jika dikosongkan oleh user/admin, password lama tidak akan tertimpa / tetap utuh.
     * 3. Melakukan pembaruan data pada model user ($user->update($data)).
     * 4. Redirect kembali ke index dengan notifikasi sukses.
     *
     * @param UserRequest $request
     * @param User $user
     */
    public function update(UserRequest $request, User $user)
    {
        // Ambil data dasar yang pasti diperbarui (nama & email)
        $data = $request->only(['name', 'email']);

        // Cek apakah kolom password baru diisi; jika iya, hash password baru tersebut
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Eksekusi update data ke database
        $user->update($data);

        return to_route('users.index')->with('success', 'User berhasil diupdate!');
    }

    /**
     * Menghapus akun pengguna dari sistem.
     *
     * Alur Logic:
     * 1. Menghapus record pengguna terpilih berdasarkan instance Route Model Binding.
     * 2. Redirect ke halaman index dengan flash message sukses penghapusan.
     *
     * @param User $user
     */
    public function destroy(User $user)
    {
        // Hapus data pengguna dari tabel
        $user->delete();

        return to_route('users.index')->with('success', 'User berhasil dihapus!');
    }
}
