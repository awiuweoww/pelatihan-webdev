<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeminjamanRequest;
use App\Models\Peminjaman;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    /**
     * Master data alat gunung dan stok dasarnya
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $masterAlat = $this->getMasterAlat();
        $totalUnitAlat = 0;
        $sedangDipinjam = 0;

        foreach ($masterAlat as &$item) {
            $dipinjamCount = (int) Peminjaman::where('alat_dipinjam', $item['nama'])
                ->where('status', 'Dipinjam')
                ->sum('jumlah_pinjam');

            $item['sedang_dipinjam'] = $dipinjamCount;
            $item['sisa_stok'] = max(0, $item['total'] - $dipinjamCount);
            $item['status_ketersediaan'] = $item['sisa_stok'] > 0 ? 'Tersedia' : 'Habis';

            $totalUnitAlat += $item['total'];
            $sedangDipinjam += $dipinjamCount;
        }
        unset($item);

        $sisaStokTersedia = max(0, $totalUnitAlat - $sedangDipinjam);
        $peminjamans = Peminjaman::latest()->paginate(10);

        // Auto-generate kode transaksi untuk form
        $last = Peminjaman::latest('id')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        $nextKodeTrx = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $masterAlat = $this->getMasterAlat();
        foreach ($masterAlat as &$item) {
            $dipinjamCount = (int) Peminjaman::where('alat_dipinjam', $item['nama'])
                ->where('status', 'Dipinjam')
                ->sum('jumlah_pinjam');
            $item['sisa_stok'] = max(0, $item['total'] - $dipinjamCount);
        }
        unset($item);

        $last = Peminjaman::latest('id')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        $nextKodeTrx = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return view('peminjaman.create', compact('masterAlat', 'nextKodeTrx'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PeminjamanRequest $request)
    {
        $alat = $request->alat_dipinjam;
        $qty = (int) $request->jumlah_pinjam;
        $masterList = array_column($this->getMasterAlat(), 'total', 'nama');

        // Validasi ketersediaan stok fisik barang terhadap jumlah yang dipinjam
        if (isset($masterList[$alat])) {
            $currentDipinjam = (int) Peminjaman::where('alat_dipinjam', $alat)
                ->where('status', 'Dipinjam')
                ->sum('jumlah_pinjam');

            $sisaStok = max(0, $masterList[$alat] - $currentDipinjam);

            if ($request->status === 'Dipinjam' && ($currentDipinjam + $qty) > $masterList[$alat]) {
                return back()->withInput()->with('error', "Stok tidak mencukupi! Sisa stok untuk alat \"{$alat}\" hanya tersisa {$sisaStok} unit (Anda meminta {$qty} unit).");
            }
        }

        $kode = $request->filled('kode_transaksi') ? $request->kode_transaksi : null;
        if (!$kode) {
            $last = Peminjaman::latest('id')->first();
            $nextNum = $last ? ($last->id + 1) : 1;
            $kode = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        Peminjaman::create([
            'kode_transaksi' => $kode,
            'nama_peminjam' => $request->nama_peminjam,
            'alat_dipinjam' => $request->alat_dipinjam,
            'jumlah_pinjam' => $qty,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'tenggat_kembali' => $request->tenggat_kembali,
            'status' => $request->status ?? 'Dipinjam',
        ]);

        return to_route('peminjaman.index')->with('success', 'Data transaksi peminjaman berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Peminjaman $peminjaman)
    {
        $masterAlat = $this->getMasterAlat();
        foreach ($masterAlat as &$item) {
            $dipinjamCount = (int) Peminjaman::where('alat_dipinjam', $item['nama'])
                ->where('status', 'Dipinjam')
                ->where('id', '!=', $peminjaman->id)
                ->sum('jumlah_pinjam');
            $item['sisa_stok'] = max(0, $item['total'] - $dipinjamCount);
        }
        unset($item);

        return view('peminjaman.edit', compact('peminjaman', 'masterAlat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PeminjamanRequest $request, Peminjaman $peminjaman)
    {
        $alat = $request->alat_dipinjam;
        $qty = (int) $request->jumlah_pinjam;
        $masterList = array_column($this->getMasterAlat(), 'total', 'nama');

        // Jika status Dipinjam, cek ketersediaan stok
        if (isset($masterList[$alat]) && $request->status === 'Dipinjam') {
            $currentDipinjam = (int) Peminjaman::where('alat_dipinjam', $alat)
                ->where('status', 'Dipinjam')
                ->where('id', '!=', $peminjaman->id)
                ->sum('jumlah_pinjam');

            $sisaStok = max(0, $masterList[$alat] - $currentDipinjam);

            if (($currentDipinjam + $qty) > $masterList[$alat]) {
                return back()->withInput()->with('error', "Stok tidak mencukupi! Sisa stok untuk alat \"{$alat}\" hanya tersisa {$sisaStok} unit (Anda meminta {$qty} unit).");
            }
        }

        $peminjaman->update([
            'nama_peminjam' => $request->nama_peminjam,
            'alat_dipinjam' => $request->alat_dipinjam,
            'jumlah_pinjam' => $qty,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'tenggat_kembali' => $request->tenggat_kembali,
            'status' => $request->status,
        ]);

        return to_route('peminjaman.index')->with('success', 'Data transaksi peminjaman berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Peminjaman $peminjaman)
    {
        $peminjaman->delete();

        return to_route('peminjaman.index')->with('success', 'Data transaksi peminjaman berhasil dihapus!');
    }
}
