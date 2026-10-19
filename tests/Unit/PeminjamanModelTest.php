<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Pengujian unit (Unit Test) untuk Model Peminjaman,
 *               memvalidasi konfigurasi fillable attributes, nama tabel, dan type casting model.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi unit test untuk model Peminjaman.
 */

namespace Tests\Unit;

use App\Models\Peminjaman;
use Tests\TestCase;

class PeminjamanModelTest extends TestCase
{
    /**
     * Memastikan konfigurasi nama tabel pada model Peminjaman sesuai dengan skema database ('peminjaman').
     */
    public function test_model_peminjaman_memiliki_nama_tabel_yang_sesuai(): void
    {
        $model = new Peminjaman();

        // Verifikasi nama tabel yang ditentukan model adalah 'peminjaman'
        $this->assertEquals('peminjaman', $model->getTable());
    }

    /**
     * Memastikan atribut fillable (kolom yang boleh diisi secara mass-assignment) sesuai dengan spesifikasi.
     */
    public function test_model_peminjaman_memiliki_atribut_fillable_yang_benar(): void
    {
        $model = new Peminjaman();

        $expectedFillables = [
            'kode_transaksi',
            'nama_peminjam',
            'alat_dipinjam',
            'jumlah_pinjam',
            'tanggal_pinjam',
            'tenggat_kembali',
            'status',
        ];

        // Memeriksa kesesuaian array fillable
        $this->assertEquals($expectedFillables, $model->getFillable());
    }

    /**
     * Memastikan type casting untuk atribut jumlah pinjam dan tanggal terdefinisi dengan benar.
     */
    public function test_model_peminjaman_memiliki_type_casts_yang_sesuai(): void
    {
        $model = new Peminjaman();
        $casts = $model->getCasts();

        // Pastikan kolom jumlah_pinjam di-cast ke integer
        $this->assertEquals('integer', $casts['jumlah_pinjam'] ?? null);

        // Pastikan tanggal_pinjam dan tenggat_kembali di-cast ke date
        $this->assertEquals('date', $casts['tanggal_pinjam'] ?? null);
        $this->assertEquals('date', $casts['tenggat_kembali'] ?? null);
    }
}
