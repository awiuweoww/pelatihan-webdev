<!--
Author      : Chandra
Version     : 0.1.0
Created     : 17-10-2026
Description : Dokumentasi skenario pengujian unit dan fitur (Unit & Feature Testing) aplikasi.

Changelog:
- 0.1.0 (17-10-2026): Inisialisasi dokumentasi skenario pengujian positif (+) dan negatif (-).
-->

# Dokumentasi Pengujian Aplikasi (Automated Testing)

Direktori ini berisi kumpulan berkas pengujian otomatis (**Feature Testing** dan **Unit Testing**) yang dibangun menggunakan **PHPUnit** pada framework Laravel. Pengujian menggunakan database sementara (*in-memory SQLite*) via trait `RefreshDatabase` sehingga tidak memengaruhi data riil pada database aplikasi.

---

## Cara Menjalankan Pengujian

Jalankan perintah berikut di terminal pada direktori root proyek:

```bash
# Menjalankan seluruh test suite menggunakan PHPUnit
php vendor/phpunit/phpunit/phpunit

# Atau menggunakan Artisan runner bawaan Laravel
php artisan test
```

---

## 1. Skenario Pengujian Modul Peminjaman (`PeminjamanTest.php`)

Lokasi berkas: `tests/Feature/PeminjamanTest.php`

| Tipe | Nama Method Test | Deskripsi Skenario | Ekspektasi Hasil |
| :---: | :--- | :--- | :--- |
| **(+)** | `test_halaman_index_peminjaman_dapat_ditampilkan_beserta_info_stok` | Mengakses halaman index peminjaman dan rekapitulasi ketersediaan stok fisik barang. | HTTP 200, view menampilkan riwayat transaksi dan informasi stok. |
| **(+)** | `test_halaman_tambah_peminjaman_dapat_ditampilkan` | Mengakses halaman formulir pencatatan peminjaman baru. | HTTP 200, view formulir berhasil dimuat. |
| **(+)** | `test_transaksi_peminjaman_berhasil_dibuat` | Menyimpan transaksi peminjaman baru ketika jumlah unit yang diminta mencukupi sisa stok. | Redirect ke index, session `success`, data tersimpan di tabel `peminjaman`. |
| **(-)** | `test_peminjaman_gagal_jika_jumlah_melebihi_stok` | Mencoba meminjam alat dengan jumlah unit melebihi kapasitas stok fisik yang tersedia di gudang. | Ditolak, redirect back, session `error`, data **tidak** tersimpan di database. |
| **(+)** | `test_halaman_edit_peminjaman_dapat_ditampilkan` | Mengakses halaman form edit transaksi peminjaman berdasarkan ID record. | HTTP 200, form memuat data transaksi peminjam yang bersangkutan. |
| **(+)** | `test_status_peminjaman_dapat_diubah_menjadi_dikembalikan` | Memperbarui status transaksi menjadi "Dikembalikan" setelah barang diterima kembali di gudang. | Redirect ke index, session `success`, status di database berubah jadi `Dikembalikan`. |
| **(+)** | `test_data_peminjaman_dapat_dihapus` | Menghapus data transaksi peminjaman dari sistem. | Redirect ke index, session `success`, record terhapus dari tabel `peminjaman`. |

---

## 2. Skenario Pengujian Modul Pengguna (`UserTest.php`)

Lokasi berkas: `tests/Feature/UserTest.php`

| Tipe | Nama Method Test | Deskripsi Skenario | Ekspektasi Hasil |
| :---: | :--- | :--- | :--- |
| **(+)** | `test_halaman_index_user_dapat_ditampilkan` | Mengakses halaman utama daftar seluruh akun pengguna terdaftar. | HTTP 200, view tabel menampilkan nama pengguna dari database. |
| **(+)** | `test_halaman_tambah_user_dapat_ditampilkan` | Mengakses halaman formulir pendaftaran pengguna baru. | HTTP 200, halaman form tambah user terbuka normal. |
| **(+)** | `test_user_berhasil_dibuat_dengan_data_valid` | Mendaftarkan pengguna baru dengan data valid (nama, email unik, dan password). | Redirect ke index, data tersimpan di tabel `users`, dan password terenkripsi hash (*Bcrypt*). |
| **(-)** | `test_user_gagal_dibuat_jika_email_duplikat` | Mendaftarkan akun baru menggunakan email yang sudah digunakan pengguna lain. | Ditolak, session memiliki validation error pada field `email`, data tidak tersimpan ganda. |
| **(+)** | `test_halaman_edit_user_dapat_ditampilkan` | Mengakses form pengubahan profil pengguna terdaftar. | HTTP 200, form terisi otomatis data user yang dipilih. |
| **(+)** | `test_user_dapat_diupdate_tanpa_mengubah_password` | Memperbarui nama dan email user dengan mengosongkan kolom password baru. | Redirect ke index, nama & email terupdate di database, password lama tetap utuh/aman. |
| **(+)** | `test_user_dapat_dihapus` | Menghapus akun pengguna dari database. | Redirect ke index, session `success`, data pengguna terhapus dari tabel `users`. |

---

## 3. Skenario Pengujian Unit Model (`PeminjamanModelTest.php`)

Lokasi berkas: `tests/Unit/PeminjamanModelTest.php`

| Tipe | Nama Method Test | Deskripsi Skenario | Ekspektasi Hasil |
| :---: | :--- | :--- | :--- |
| **(+)** | `test_model_peminjaman_memiliki_nama_tabel_yang_sesuai` | Memeriksa konfigurasi nama tabel pada model `Peminjaman`. | Mengembalikan string `'peminjaman'`. |
| **(+)** | `test_model_peminjaman_memiliki_atribut_fillable_yang_benar` | Memeriksa proteksi mass-assignment pada atribut `$fillable`. | Seluruh kolom transaksi (`kode_transaksi`, `nama_peminjam`, dll) terdaftar aman. |
| **(+)** | `test_model_peminjaman_memiliki_type_casts_yang_sesuai` | Memeriksa konfigurasi type casting data atribut tanggal dan integer. | `jumlah_pinjam` di-cast ke `integer`, `tanggal_pinjam` dan `tenggat_kembali` di-cast ke `date`. |

---

## 4. Pengujian Bawaan Framework (`ExampleTest.php`)

| Tipe | File & Method | Deskripsi Skenario | Ekspektasi Hasil |
| :---: | :--- | :--- | :--- |
| **(+)** | `Feature/ExampleTest::test_the_application_returns_a_successful_response` | Menguji akses dasar ke rute beranda (`/`). | Respons HTTP 200 (OK). |
| **(+)** | `Unit/ExampleTest::test_that_true_is_true` | Pengujian dasar assertions unit runner. | Mengembalikan nilai `true`. |

---

## Ringkasan Eksekusi Pengujian

```text
PHPUnit 12.5.40 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.0
Configuration: C:\sertifikasi\pelatihan\phpunit.xml

...................                                               19 / 19 (100%)

Time: 00:00.834, Memory: 12.00 MB

OK (19 tests, 51 assertions)
```

> **Keterangan Simbol:**
> - **(+) Skenario Positif (Happy Path):** Menguji kondisi input dan alur proses berjalan sesuai harapan dengan data yang valid.
> - **(-)** **Skenario Negatif (Unhappy Path):** Menguji ketahanan sistem saat menerima data tidak valid atau melanggar aturan bisnis (misal: stok habis, email duplikat).
