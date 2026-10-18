# LAPORAN IMPLEMENTASI MODUL FITUR

# PEMINJAMAN ALAT & MONITORING STOK GUNUNG

---

## 1. INFORMASI PROYEK & PENGEMBANGAN

| Atribut                  | Keterangan                                               |
| :----------------------- | :------------------------------------------------------- |
| **Nama Proyek**          | Dashboard Sertifikasi Web Development                    |
| **Modul Fitur**          | Peminjaman Alat & Monitoring Stok Gunung                 |
| **Peran**                | Senior Frontend / Full-Stack Engineer                    |
| **Framework & Versi**    | Laravel 11.x, PHP 8.4                                    |
| **UI Framework**         | Bootstrap 5 & SB Admin 2 (FontAwesome & Bootstrap Icons) |
| **Database**             | MySQL (Tabel: `peminjaman`)                              |
| **Tanggal Implementasi** | 16 Oktober 2026                                          |

---

## 2. DESKRIPSI & TUJUAN MODUL

Modul **Peminjaman Alat & Monitoring Stok Gunung** dirancang untuk:

> _"Memantau ketersediaan stok fisik barang, mendata transaksi sewa, serta memperbarui alur pengembalian secara real-time tanpa atribut keuangan apa pun."_

### Prinsip Utama Sistem:

1. **Zero Financial Attributes**: Seluruh sistem murni berfokus pada kuantitas fisik alat, nama peminjam, dan durasi tanggal pinjam/kembali. Tidak ada atribut tarif sewa, deposit, denda, atau nominal uang.
2. **Real-time Stock Reactivity**: Setiap kali transaksi baru dicatat, diedit statusnya (dari _Dipinjam_ menjadi _Dikembalikan_), atau dihapus, sisa stok alat dan kartu metrik ringkasan langsung terhitung ulang secara otomatis.
3. **Pencegahan Over-Borrowing**: Sistem memiliki validasi internal yang menolak peminjaman apabila jumlah unit yang diminta melebihi sisa stok fisik yang tersedia.
4. **Konsistensi Kode**: Struktur class, penataan layout (`layouts.admin`), navigasi sidebar, form request, serta notifikasi flash session 100% konsisten dengan arsitektur modul eksisting (modul User).

---

## 3. SPESIFIKASI TEKNIS & DATABASE

### A. Skema Tabel Database (`peminjaman`)

| Kolom                       | Tipe Data     | Keterangan                         |
| :-------------------------- | :------------ | :--------------------------------- |
| `id`                        | BigIncrements | Primary key                        |
| `kode_transaksi`            | VARCHAR(50)   | Kode unik transaksi (TRX-xxx)      |
| `nama_peminjam`             | VARCHAR(255)  | Nama lengkap peminjam              |
| `alat_dipinjam`             | VARCHAR(255)  | Nama alat gunung yang dipinjam     |
| `jumlah_pinjam`             | INT UNSIGNED  | Kuantitas unit yang dipinjam       |
| `tanggal_pinjam`            | DATE          | Tanggal mulai peminjaman           |
| `tenggat_kembali`           | DATE          | Batas akhir waktu pengembalian     |
| `status`                    | ENUM          | `'Dipinjam'` atau `'Dikembalikan'` |
| `created_at` / `updated_at` | TIMESTAMP     | Waktu pembuatan & modifikasi       |

### B. Master Inventaris Alat Gunung

| No  | Nama Alat Gunung | Total Stok Awal |
| :-: | :--------------- | :-------------: |
|  1  | Tenda Dome 4P    |     10 Unit     |
|  2  | Carrier 60L      |     15 Unit     |
|  3  | Kompor & Nesting |     20 Unit     |
|  4  | Sleeping Bag     |     25 Unit     |
|  5  | Trekking Pole    |     30 Unit     |

---

## 4. DOKUMENTASI FITUR & TAMPILAN ANTARMUKA

### 4.1 Halaman Utama: Monitoring Stok & Transaksi Peminjaman

Halaman ini berfungsi sebagai pusat kendali (_command center_) yang memuat:

- **Header Halaman**: Judul modul, deskripsi resmi, serta tombol aksi utama **"Tambah Peminjaman"**.
- **Ringkasan Stok (3 Cards Metrik)**:
    - _Total Unit Alat_ (akumulasi seluruh stok master alat).
    - _Sedang Dipinjam_ (jumlah unit yang statusnya masih 'Dipinjam').
    - _Sisa Stok Tersedia_ (stok fisik yang siap digunakan).
- **Tabel Ketersediaan per Alat**: Live status ketersediaan masing-masing alat lengkap dengan badge `Tersedia` / `Habis`.
- **Tabel Transaksi Peminjaman**: Daftar riwayat sewa lengkap dengan status badge dan tombol aksi **Edit** serta **Hapus** yang tertata rapi berdampingan (_side-by-side_). Jika data kosong, sistem otomatis menampilkan _Empty State_ yang informatif.

#### Lampiran Foto 1: Halaman Utama Peminjaman

_(Lampirkan tangkapan layar Halaman Utama Peminjaman di bawah ini)_

![Halaman Utama Peminjaman](path/ke/foto_1_halaman_peminjaman.png)

---

### 4.2 Halaman Tambah Peminjaman Alat

Halaman ini digunakan untuk mencatat transaksi peminjaman baru ke database:

- **Kode Transaksi Otomatis**: Digenerate otomatis oleh sistem secara berurutan (`TRX-001`, `TRX-002`, dst.).
- **Input Nama Peminjam**: Validasi wajib diisi, minimal 3 karakter.
- **Pilihan Alat & Sisa Kuota**: Dropdown otomatis menampilkan sisa unit yang tersedia saat itu.
- **Input Jumlah Pinjam**: Validasi angka minimal 1 unit dan proteksi agar tidak melebihi sisa stok fisik.
- **Tanggal Pinjam & Tenggat Kembali**: Date picker dengan proteksi tenggat tidak boleh sebelum tanggal pinjam.
- **Pesan Notifikasi**: Seluruh pesan kesalahan validasi ditampilkan dalam Bahasa Indonesia yang jelas.

#### Lampiran Foto 2: Form Tambah Peminjaman

_(Lampirkan tangkapan layar Form Tambah Peminjaman di bawah ini)_

![Form Tambah Peminjaman](path/ke/foto_2_tambah_peminjam.png)

---

### 4.3 Halaman Edit Peminjaman Alat

Halaman ini digunakan untuk mengelola data peminjaman yang sedang berjalan serta memproses pengembalian alat:

- **Data Pre-filled**: Seluruh input otomatis terisi dengan data transaksi yang dipilih.
- **Pembaruan Status (_Return Workflow_)**: Petugas dapat mengubah status dari **"Dipinjam"** menjadi **"Dikembalikan"**.
- **Pemulihan Stok Otomatis**: Begitu status diubah menjadi "Dikembalikan" dan disimpan, kuota sisa stok alat pada tabel inventaris dan kartu ringkasan langsung bertambah kembali secara _real-time_.
- **Tombol Navigasi**: Tombol _Simpan_ (`btn-success`) dan tombol _Kembali_ (`btn-secondary`).

#### Lampiran Foto 3: Form Edit Peminjaman

_(Lampirkan tangkapan layar Form Edit Peminjaman di bawah ini)_

![Form Edit Peminjaman](path/ke/foto_3_edit_peminjam.png)
