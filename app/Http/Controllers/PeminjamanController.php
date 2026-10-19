<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Controller untuk mengelola data peminjaman alat gunung, monitoring stok,
 *               dan status pengembalian barang secara real-time.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi controller peminjaman beserta fungsi CRUD dan kalkulasi stok alat.
 */

namespace App\Http\Controllers;

use App\Http\Requests\PeminjamanRequest;
use App\Models\Peminjaman;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    /**
     * Master data alat gunung dan stok dasarnya.
     * Mengembalikan daftar alat rental beserta kuota total unit fisik yang tersedia di gudang.
     *
     * @return array
     */
    private function getMasterAlat(): array
    {
        return [
            ['nama' => 'Tenda Dome 4P', 'total' => 10],
            ['nama' => 'Carrier 60L', 'total' => 10],
            ['nama' => 'Kompor & Nesting', 'total' => 10],
            ['nama' => 'Sleeping Bag', 'total' => 10],
            ['nama' => 'Trekking Pole', 'total' => 10],
        ];
    }

    /**
     * Menampilkan halaman utama daftar transaksi peminjaman dan rekapitulasi stok alat.
     *
     * Alur Logic:
     * 1. Mengambil master data alat rental.
     * 2. Menghitung jumlah unit yang sedang dipinjam (status 'Dipinjam') dari database.
     * 3. Menghitung sisa stok aktual dan status ketersediaan (Tersedia / Habis) per alat.
     * 4. Mengakumulasi total stok dan total barang yang sedang beredar di lapangan.
     * 5. Mengambil data transaksi peminjaman dengan pagination (10 per halaman) urut terbaru.
     * 6. Membuat otomatis nomor transaksi berikutnya (misal: TRX-001) untuk form modal/tambah.
     */
    public function index()
    {
        $masterAlat = $this->getMasterAlat();
        $totalUnitAlat = 0;
        $sedangDipinjam = 0;

        // Loop setiap master alat untuk menghitung kalkulasi stok aktual
        foreach ($masterAlat as &$item) {
            // [KEAMANAN - SQL INJECTION]: Eloquent ORM menggunakan PDO Parameter Binding (Prepared Statements)
            // Nilai input dipisahkan sebagai placeholder '?' sehingga aman dari manipulasi SQL Injection.
            // Hitung total unit yang saat ini sedang dipinjam pelanggan (belum dikembalikan)
            $dipinjamCount = (int) Peminjaman::where('alat_dipinjam', $item['nama'])
                ->where('status', 'Dipinjam')
                ->sum('jumlah_pinjam');

            $item['sedang_dipinjam'] = $dipinjamCount;
            // Sisa stok fisik = total kapasitas alat dikurangi yang sedang dipinjam
            $item['sisa_stok'] = max(0, $item['total'] - $dipinjamCount);
            // Label status ketersediaan barang untuk visualisasi badge tabel
            $item['status_ketersediaan'] = $item['sisa_stok'] > 0 ? 'Tersedia' : 'Habis';

            // Akumulasi total kapasitas seluruh alat dan seluruh unit yang dipinjam
            $totalUnitAlat += $item['total'];
            $sedangDipinjam += $dipinjamCount;
        }
        unset($item); // Melepas referensi pointer dari foreach

        // Menghitung sisa unit gabungan yang masih ready di gudang
        $sisaStokTersedia = max(0, $totalUnitAlat - $sedangDipinjam);

        // Ambil riwayat peminjaman dengan pagination 10 data per halaman, urut dari transaksi terbaru
        $peminjamans = Peminjaman::latest()->paginate(10);

        // Auto-generate format kode transaksi berurutan (contoh: TRX-001, TRX-002)
        $last = Peminjaman::latest('id')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        $nextKodeTrx = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        // Kirim data ke view peminjaman
        return view('peminjaman.peminjaman', compact(
            'masterAlat',
            'totalUnitAlat',
            'sedangDipinjam',
            'sisaStokTersedia',
            'peminjamans',
            'nextKodeTrx'
        ));
    }

    /**
     * Menampilkan form halaman tersendiri untuk mencatat transaksi peminjaman baru.
     *
     * Alur Logic:
     * 1. Menghitung sisa stok setiap alat untuk ditampilkan pada opsi dropdown form.
     * 2. Membuat nomor kode transaksi otomatis baru.
     * 3. Merender view peminjaman.create.
     */
    public function create()
    {
        $masterAlat = $this->getMasterAlat();

        // Hitung sisa stok untuk masing-masing opsi alat agar peminjam tahu sisa unit yang bisa disewa
        foreach ($masterAlat as &$item) {
            $dipinjamCount = (int) Peminjaman::where('alat_dipinjam', $item['nama'])
                ->where('status', 'Dipinjam')
                ->sum('jumlah_pinjam');
            $item['sisa_stok'] = max(0, $item['total'] - $dipinjamCount);
        }
        unset($item);

        // Siapkan kode transaksi otomatis berikutnya
        $last = Peminjaman::latest('id')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        $nextKodeTrx = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return view('peminjaman.create', compact('masterAlat', 'nextKodeTrx'));
    }

    /**
     * Menyimpan data transaksi peminjaman baru ke database.
     *
     * Alur Logic:
     * 1. Menerima data tervalidasi dari PeminjamanRequest.
     * 2. Pengecekan Stok: Memastikan jumlah yang diminta tidak melebihi sisa stok fisik yang ada.
     * 3. Jika stok tidak cukup, proses dibatalkan dan dikembalikan dengan pesan error.
     * 4. Jika kode transaksi kosong, sistem akan meng-generate otomatis kode transaksi baru.
     * 5. Menyimpan data peminjaman ke tabel 'peminjaman'.
     * 6. Redirect kembali ke halaman index dengan flash message sukses.
     *
     * @param PeminjamanRequest $request
     */
    public function store(PeminjamanRequest $request)
    {
        $alat = $request->alat_dipinjam;
        $qty = (int) $request->jumlah_pinjam;
        // Buat map master data [nama_alat => total_kapasitas]
        $masterList = array_column($this->getMasterAlat(), 'total', 'nama');

        // Validasi ketersediaan stok fisik barang sebelum memproses peminjaman
        if (isset($masterList[$alat])) {
            // Hitung unit alat terkait yang saat ini masih dipinjam
            $currentDipinjam = (int) Peminjaman::where('alat_dipinjam', $alat)
                ->where('status', 'Dipinjam')
                ->sum('jumlah_pinjam');

            $sisaStok = max(0, $masterList[$alat] - $currentDipinjam);

            // Jika status baru adalah 'Dipinjam' dan permintaan melebihi sisa stok, gagalkan transaksi
            if ($request->status === 'Dipinjam' && ($currentDipinjam + $qty) > $masterList[$alat]) {
                return back()
                    ->withInput()
                    ->with('error', "Stok tidak mencukupi! Sisa stok untuk alat \"{$alat}\" hanya tersisa {$sisaStok} unit (Anda meminta {$qty} unit).");
            }
        }

        // Tentukan kode transaksi (gunakan input pengguna jika ada, atau buat otomatis)
        $kode = $request->filled('kode_transaksi') ? $request->kode_transaksi : null;
        if (!$kode) {
            $last = Peminjaman::latest('id')->first();
            $nextNum = $last ? ($last->id + 1) : 1;
            $kode = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        // [KEAMANAN - SQL INJECTION & MASS ASSIGNMENT]:
        // 1. Eloquent ::create() otomatis menggunakan PDO Parameter Binding sehingga aman dari SQL Injection.
        // 2. Hanya atribut yang terdaftar pada properti $fillable di model Peminjaman yang diizinkan (proteksi Mass Assignment).
        // Simpan data transaksi baru ke database
        Peminjaman::create([
            'kode_transaksi' => $kode,
            'nama_peminjam'  => $request->nama_peminjam,
            'alat_dipinjam'  => $request->alat_dipinjam,
            'jumlah_pinjam'  => $qty,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'tenggat_kembali' => $request->tenggat_kembali,
            'status'         => $request->status ?? 'Dipinjam',
        ]);

        return to_route('peminjaman.index')->with('success', 'Data transaksi peminjaman berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit transaksi peminjaman atau mengubah status menjadi dikembalikan.
     *
     * Alur Logic:
     * 1. Menghitung sisa stok dengan mengabaikan (exclude) jumlah dari transaksi yang sedang diedit.
     * 2. Mengirim data objek peminjaman dan master stok alat ke view peminjaman.edit.
     *
     * @param Peminjaman $peminjaman
     */
    public function edit(Peminjaman $peminjaman)
    {
        $masterAlat = $this->getMasterAlat();

        // [KEAMANAN - SQL INJECTION]: Query filtering dengan where() otomatis menggunakan PDO parameter binding
        // Hitung stok aktual dengan mengabaikan transaksi ini (karena unit ini sudah terikat ke transaksi ini)
        foreach ($masterAlat as &$item) {
            $dipinjamCount = (int) Peminjaman::where('alat_dipinjam', $item['nama'])
                ->where('status', 'Dipinjam')
                ->where('id', '!=', $peminjaman->id) // Abaikan record yang sedang diedit
                ->sum('jumlah_pinjam');

            $item['sisa_stok'] = max(0, $item['total'] - $dipinjamCount);
        }
        unset($item);

        return view('peminjaman.edit', compact('peminjaman', 'masterAlat'));
    }

    /**
     * Memperbarui data transaksi peminjaman di database.
     *
     * Alur Logic:
     * 1. Jika status transaksi tetap atau diubah ke 'Dipinjam', cek apakah penambahan jumlah unit melebihi stok.
     * 2. Perhitungan stok mengecualikan record peminjaman saat ini (where id != current id).
     * 3. Jika stok aman, perbarui data transaksi (nama peminjam, alat, jumlah, tanggal, dan status).
     * 4. Redirect ke index dengan pesan sukses pembaruan.
     *
     * @param PeminjamanRequest $request
     * @param Peminjaman $peminjaman
     */
    public function update(PeminjamanRequest $request, Peminjaman $peminjaman)
    {
        $alat = $request->alat_dipinjam;
        $qty = (int) $request->jumlah_pinjam;
        $masterList = array_column($this->getMasterAlat(), 'total', 'nama');

        // Jika status transaksi adalah 'Dipinjam', pastikan stok fisik masih memadai
        if (isset($masterList[$alat]) && $request->status === 'Dipinjam') {
            // Hitung unit yang dipinjam oleh transaksi lain (selain transaksi yang sedang diedit ini)
            $currentDipinjam = (int) Peminjaman::where('alat_dipinjam', $alat)
                ->where('status', 'Dipinjam')
                ->where('id', '!=', $peminjaman->id)
                ->sum('jumlah_pinjam');

            $sisaStok = max(0, $masterList[$alat] - $currentDipinjam);

            // Validasi apakah jumlah pinjam baru melebihi stok yang tersisa
            if (($currentDipinjam + $qty) > $masterList[$alat]) {
                return back()
                    ->withInput()
                    ->with('error', "Stok tidak mencukupi! Sisa stok untuk alat \"{$alat}\" hanya tersisa {$sisaStok} unit (Anda meminta {$qty} unit).");
            }
        }

        // [KEAMANAN - SQL INJECTION & MASS ASSIGNMENT]:
        // Update data via instance Eloquent menggunakan prepared statements dan terlindungi oleh $fillable
        // Eksekusi pembaruan data transaksi
        $peminjaman->update([
            'nama_peminjam'   => $request->nama_peminjam,
            'alat_dipinjam'   => $request->alat_dipinjam,
            'jumlah_pinjam'   => $qty,
            'tanggal_pinjam'  => $request->tanggal_pinjam,
            'tenggat_kembali' => $request->tenggat_kembali,
            'status'          => $request->status,
        ]);

        return to_route('peminjaman.index')->with('success', 'Data transaksi peminjaman berhasil diupdate!');
    }

    /**
     * Menghapus catatan transaksi peminjaman dari database.
     *
     * Alur Logic:
     * 1. Menghapus record transaksi peminjaman terpilih.
     * 2. Unit barang yang sebelumnya berstatus dipinjam secara otomatis kembali dihitung sebagai stok bebas.
     * 3. Redirect kembali ke index dengan notifikasi sukses.
     *
     * @param Peminjaman $peminjaman
     */
    public function destroy(Peminjaman $peminjaman)
    {
        // [KEAMANAN - ROUTE MODEL BINDING & SQL INJECTION]:
        // Instance $peminjaman di-resolve otomatis secara aman oleh Laravel,
        // dan penghapusan dijalankan menggunakan query berparameter (prepared statement).
        $peminjaman->delete();

        return to_route('peminjaman.index')->with('success', 'Data transaksi peminjaman berhasil dihapus!');
    }
}
