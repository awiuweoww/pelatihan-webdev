<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Model Eloquent untuk entitas peminjaman alat gunung.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi model Peminjaman dengan fillable fields dan type casting tanggal.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    // [KEAMANAN - PROTEKSI MASS ASSIGNMENT]:
    // $fillable bertindak sebagai daftar putih (whitelist) untuk menentukan kolom apa saja
    // yang diizinkan untuk diisi secara massal (Mass Assignment) dari input pengguna.
    // Mencegah penyerang menyisipkan kolom berbahaya (misal: is_admin, saldo, id) secara ilegal.
    protected $fillable = [
        'kode_transaksi',
        'nama_peminjam',
        'alat_dipinjam',
        'jumlah_pinjam',
        'tanggal_pinjam',
        'tenggat_kembali',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_pinjam' => 'integer',
            'tanggal_pinjam' => 'date',
            'tenggat_kembali' => 'date',
        ];
    }
}
