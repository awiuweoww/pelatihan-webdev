<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Form request validasi untuk transaksi peminjaman alat gunung.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi aturan validasi dan kustomisasi pesan error peminjaman.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $peminjamanID = $this->route('peminjaman') ? (is_object($this->route('peminjaman')) ? $this->route('peminjaman')->id : $this->route('peminjaman')) : null;

        return [
            'kode_transaksi' => 'nullable|string|max:50|unique:peminjaman,kode_transaksi,' . $peminjamanID,
            'nama_peminjam' => 'required|string|min:3|max:255',
            'alat_dipinjam' => 'required|string|max:255',
            'jumlah_pinjam' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tenggat_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
            'status' => 'required|in:Dipinjam,Dikembalikan',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_peminjam.required' => 'Nama peminjam wajib diisi.',
            'nama_peminjam.min' => 'Nama peminjam minimal 3 karakter.',
            'nama_peminjam.max' => 'Nama peminjam maksimal 255 karakter.',
            'alat_dipinjam.required' => 'Pilihan alat yang dipinjam wajib dipilih.',
            'jumlah_pinjam.required' => 'Jumlah pinjam wajib diisi.',
            'jumlah_pinjam.integer' => 'Jumlah pinjam harus berupa angka bulat.',
            'jumlah_pinjam.min' => 'Jumlah pinjam minimal 1 unit.',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
            'tenggat_kembali.required' => 'Tenggat kembali wajib diisi.',
            'tenggat_kembali.date' => 'Format tenggat kembali tidak valid.',
            'tenggat_kembali.after_or_equal' => 'Tenggat kembali tidak boleh lebih awal dari tanggal pinjam.',
            'status.required' => 'Status peminjaman wajib ditentukan.',
            'status.in' => 'Status peminjaman hanya boleh Dipinjam atau Dikembalikan.',
            'kode_transaksi.unique' => 'Kode transaksi sudah digunakan.',
        ];
    }
}
