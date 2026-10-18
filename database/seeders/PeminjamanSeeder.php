<?php

namespace Database\Seeders;

use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        Peminjaman::truncate();

        Peminjaman::create([
            'kode_transaksi' => 'TRX-001',
            'nama_peminjam' => 'Budi Santoso',
            'alat_dipinjam' => 'Tenda Dome 4P',
            'jumlah_pinjam' => 2,
            'tanggal_pinjam' => '2026-10-14',
            'tenggat_kembali' => '2026-10-17',
            'status' => 'Dipinjam',
        ]);

        Peminjaman::create([
            'kode_transaksi' => 'TRX-002',
            'nama_peminjam' => 'Siti Rahma',
            'alat_dipinjam' => 'Carrier 60L',
            'jumlah_pinjam' => 1,
            'tanggal_pinjam' => '2026-10-15',
            'tenggat_kembali' => '2026-10-18',
            'status' => 'Dipinjam',
        ]);

        Peminjaman::create([
            'kode_transaksi' => 'TRX-003',
            'nama_peminjam' => 'Andi Pratama',
            'alat_dipinjam' => 'Kompor & Nesting',
            'jumlah_pinjam' => 3,
            'tanggal_pinjam' => '2026-10-12',
            'tenggat_kembali' => '2026-10-15',
            'status' => 'Dikembalikan',
        ]);
    }
}
