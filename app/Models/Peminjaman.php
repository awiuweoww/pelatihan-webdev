<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

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
