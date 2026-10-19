<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Pengujian fitur (Feature Test) untuk modul Peminjaman Alat Gunung,
 *               mencakup validasi stok fisik, alur peminjaman, update status pengembalian, dan penghapusan.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi feature test untuk modul peminjaman alat gunung.
 */

namespace Tests\Feature;

use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase; // Membersihkan database in-memory setiap kali test dijalankan

    /**
     * Memastikan halaman index peminjaman (/peminjaman) dapat diakses dengan sukses (HTTP 200)
     * dan menampilkan ringkasan informasi stok alat.
     */
    public function test_halaman_index_peminjaman_dapat_ditampilkan_beserta_info_stok(): void
    {
        // 1. Arrange: Buat satu data peminjaman di database
        Peminjaman::create([
            'kode_transaksi'  => 'TRX-001',
            'nama_peminjam'   => 'Rendi Wijaya',
            'alat_dipinjam'   => 'Tenda Dome 4P',
            'jumlah_pinjam'   => 2,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-20',
            'status'          => 'Dipinjam',
        ]);

        // 2. Act: Kunjungi URL index peminjaman
        $response = $this->get(route('peminjaman.index'));

        // 3. Assert: Pastikan halaman berhasil dimuat dan data peminjam tampil di layar
        $response->assertStatus(200);
        $response->assertSee('Rendi Wijaya');
        $response->assertSee('Tenda Dome 4P');
        $response->assertSee('TRX-001');
    }

    /**
     * Memastikan halaman tambah peminjaman (/peminjaman/create) dapat dibuka dengan baik.
     */
    public function test_halaman_tambah_peminjaman_dapat_ditampilkan(): void
    {
        // Act: Kunjungi form create
        $response = $this->get(route('peminjaman.create'));

        // Assert: Pastikan HTTP status 200
        $response->assertStatus(200);
        $response->assertSee('Tambah Peminjaman');
    }

    /**
     * Menguji alur pencatatan peminjaman baru (POST /peminjaman) dengan stok yang mencukupi.
     */
    public function test_transaksi_peminjaman_berhasil_dibuat(): void
    {
        // 1. Arrange: Siapkan data transaksi peminjaman (Tenda Dome total stok 10, pinjam 3)
        $payload = [
            'kode_transaksi'  => 'TRX-099',
            'nama_peminjam'   => 'Dewi Lestari',
            'alat_dipinjam'   => 'Tenda Dome 4P',
            'jumlah_pinjam'   => 3,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-20',
            'status'          => 'Dipinjam',
        ];

        // 2. Act: Kirim request POST ke store
        $response = $this->post(route('peminjaman.store'), $payload);

        // 3. Assert:
        // - Berhasil redirect ke index dengan pesan sukses
        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // - Data tersimpan di tabel database
        $this->assertDatabaseHas('peminjaman', [
            'kode_transaksi' => 'TRX-099',
            'nama_peminjam'  => 'Dewi Lestari',
            'alat_dipinjam'  => 'Tenda Dome 4P',
            'jumlah_pinjam'  => 3,
            'status'         => 'Dipinjam',
        ]);
    }

    /**
     * Menguji validasi proteksi stok:
     * Jika peminjam meminta unit yang melebihi kuota stok fisik, sistem harus menolak transaksi.
     */
    public function test_peminjaman_gagal_jika_jumlah_melebihi_stok(): void
    {
        // 1. Arrange: Kapasitas Tenda Dome 4P adalah 10 unit. Peminjam meminta 15 unit (melebihi stok).
        $payload = [
            'kode_transaksi'  => 'TRX-OVER',
            'nama_peminjam'   => 'Peminjam Berlebih',
            'alat_dipinjam'   => 'Tenda Dome 4P',
            'jumlah_pinjam'   => 15,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-20',
            'status'          => 'Dipinjam',
        ];

        // 2. Act: Kirim request POST
        $response = $this->post(route('peminjaman.store'), $payload);

        // 3. Assert:
        // - Request dibatalkan (redirect back) dan mengembalikan pesan error stok
        $response->assertSessionHas('error');

        // - Pastikan transaksi berlebih TIDAK masuk ke database
        $this->assertDatabaseMissing('peminjaman', [
            'kode_transaksi' => 'TRX-OVER',
        ]);
    }

    /**
     * Memastikan form edit peminjaman (/peminjaman/{peminjaman}/edit) dapat diakses dengan data aktual.
     */
    public function test_halaman_edit_peminjaman_dapat_ditampilkan(): void
    {
        // 1. Arrange: Buat data transaksi peminjaman
        $trx = Peminjaman::create([
            'kode_transaksi'  => 'TRX-002',
            'nama_peminjam'   => 'Siti Aisyah',
            'alat_dipinjam'   => 'Carrier 60L',
            'jumlah_pinjam'   => 1,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-19',
            'status'          => 'Dipinjam',
        ]);

        // 2. Act: Akses halaman edit
        $response = $this->get(route('peminjaman.edit', $trx));

        // 3. Assert: Pastikan HTTP 200 dan memuat nama peminjam
        $response->assertStatus(200);
        $response->assertSee('Siti Aisyah');
    }

    /**
     * Menguji alur pengembalian alat (update status menjadi 'Dikembalikan').
     */
    public function test_status_peminjaman_dapat_diubah_menjadi_dikembalikan(): void
    {
        // 1. Arrange: Buat peminjaman awal
        $trx = Peminjaman::create([
            'kode_transaksi'  => 'TRX-003',
            'nama_peminjam'   => 'Bagus Pratama',
            'alat_dipinjam'   => 'Sleeping Bag',
            'jumlah_pinjam'   => 2,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-19',
            'status'          => 'Dipinjam',
        ]);

        // 2. Act: Update status menjadi Dikembalikan
        $response = $this->put(route('peminjaman.update', $trx), [
            'nama_peminjam'   => 'Bagus Pratama',
            'alat_dipinjam'   => 'Sleeping Bag',
            'jumlah_pinjam'   => 2,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-19',
            'status'          => 'Dikembalikan',
        ]);

        // 3. Assert:
        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Pastikan status di database sudah terupdate menjadi Dikembalikan
        $this->assertDatabaseHas('peminjaman', [
            'id'     => $trx->id,
            'status' => 'Dikembalikan',
        ]);
    }

    /**
     * Menguji penghapusan transaksi peminjaman (DELETE /peminjaman/{peminjaman}).
     */
    public function test_data_peminjaman_dapat_dihapus(): void
    {
        // 1. Arrange: Buat transaksi
        $trx = Peminjaman::create([
            'kode_transaksi'  => 'TRX-DEL',
            'nama_peminjam'   => 'Data Hapus',
            'alat_dipinjam'   => 'Kompor & Nesting',
            'jumlah_pinjam'   => 1,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-18',
            'status'          => 'Dipinjam',
        ]);

        // 2. Act: Hapus transaksi
        $response = $this->delete(route('peminjaman.destroy', $trx));

        // 3. Assert:
        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('peminjaman', [
            'id' => $trx->id,
        ]);
    }
}
