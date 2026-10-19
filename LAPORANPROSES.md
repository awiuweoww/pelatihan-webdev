# PANDUAN PROSES PEMBUATAN FITUR PEMINJAMAN ALAT GUNUNG
**Dokumentasi Langkah Demi Langkah (Step-by-Step) dari Terminal hingga Kode Lengkap**

---

## 📌 Gambaran Umum Alur Pengerjaan (MVC Flow)
1. **Langkah 1**: Membuat & Menjalankan Migrasi Basis Data (`database/migrations`)
2. **Langkah 2**: Membuat Model Eloquent (`app/Models/Peminjaman.php`)
3. **Langkah 3**: Membuat Form Request Validasi (`app/Http/Requests/PeminjamanRequest.php`)
4. **Langkah 4**: Membuat Controller Logika Bisnis & CRUD (`app/Http/Controllers/PeminjamanController.php`)
5. **Langkah 5**: Mendaftarkan Routing Web (`routes/web.php`)
6. **Langkah 6**: Membuat Tampilan Antarmuka (Blade Views & Sidebar)
7. **Langkah 7**: Membuat & Menjalankan Automated Feature Testing (`tests/Feature/PeminjamanTest.php`)

---

## LANGKAH 1: PERANCANGAN BASIS DATA (DATABASE MIGRATION)

### 1.1 Menjalankan Perintah Pembuatan File Migrasi Tabel Utama
Buka terminal dan jalankan perintah artisan berikut untuk membuat template skema tabel:
```bash
php artisan make:migration create_peminjamans_table
```
*Tujuan*: Menghasilkan berkas migrasi baru di folder `database/migrations/` untuk mendefinisikan tabel `peminjaman`.

### 1.2 Menuliskan Kode Skema Tabel Utama
Buka berkas `database/migrations/2026_10_16_101248_create_peminjamans_table.php` dan tuliskan kode lengkap berikut:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel peminjaman.
     */
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->string('nama_peminjam');
            $table->string('alat_dipinjam');
            $table->date('tanggal_pinjam');
            $table->date('tenggat_kembali');
            $table->enum('status', ['Dipinjam', 'Dikembalikan'])->default('Dipinjam');
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi (rollback).
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
```

---

### 1.3 Menjalankan Perintah Penambahan Kolom Jumlah Pinjam
Jalankan perintah ini jika membutuhkan penambahan kolom kuantitas unit barang:
```bash
php artisan make:migration add_jumlah_pinjam_to_peminjamans_table
```

### 1.4 Menuliskan Kode Migrasi Tambahan Kolom
Buka berkas `database/migrations/2026_10_16_102058_add_jumlah_pinjam_to_peminjamans_table.php` dan tuliskan kode berikut:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom kuantitas jumlah unit barang yang disewa.
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_pinjam')->default(1)->after('alat_dipinjam');
        });
    }

    /**
     * Rollback kolom jumlah pinjam.
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn('jumlah_pinjam');
        });
    }
};
```

### 1.5 Mengeksekusi Migrasi ke Database Fisik
Jalankan perintah artisan untuk menerapkan skema tabel ke database MySQL:
```bash
php artisan migrate
```
*Tujuan*: Mengeksekusi kode skema ke database sehingga tabel fisik `peminjaman` terbentuk dengan seluruh kolom dan tipe datanya.

---

## LANGKAH 2: MEMBUAT MODEL ELOQUENT

### 2.1 Menjalankan Perintah Pembuatan Model
Jalankan perintah berikut:
```bash
php artisan make:model Peminjaman
```
*Tujuan*: Menghasilkan berkas Model Eloquent sebagai perwakilan tabel `peminjaman` pada arsitektur ORM Laravel.

### 2.2 Menuliskan Kode Lengkap Model
Buka berkas `app/Models/Peminjaman.php` dan tuliskan kode lengkap berikut:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    // Menghubungkan model dengan nama tabel khusus (tanpa akhiran s jamak bahasa Inggris)
    protected $table = 'peminjaman';

    // [KEAMANAN - MASS ASSIGNMENT]:
    // Mendaftarkan kolom yang diizinkan untuk diisi secara massal via create() atau update()
    protected $fillable = [
        'kode_transaksi',
        'nama_peminjam',
        'alat_dipinjam',
        'jumlah_pinjam',
        'tanggal_pinjam',
        'tenggat_kembali',
        'status',
    ];

    // Mengonversi tipe data atribut secara otomatis saat diakses
    protected function casts(): array
    {
        return [
            'jumlah_pinjam' => 'integer',
            'tanggal_pinjam' => 'date',
            'tenggat_kembali' => 'date',
        ];
    }
}
```

---

## LANGKAH 3: MEMBUAT FORM REQUEST VALIDASI

### 3.1 Menjalankan Perintah Pembuatan Form Request
Jalankan perintah artisan:
```bash
php artisan make:request PeminjamanRequest
```
*Tujuan*: Memisahkan logika validasi input dari controller agar kode tetap rapi (*Single Responsibility Principle*) dan aman dari input kotor.

### 3.2 Menuliskan Kode Lengkap Form Request
Buka berkas `app/Http/Requests/PeminjamanRequest.php` dan tuliskan kode lengkap berikut:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Mengizinkan request untuk diproses
    }

    public function rules(): array
    {
        $peminjamanID = $this->route('peminjaman') ? (is_object($this->route('peminjaman')) ? $this->route('peminjaman')->id : $this->route('peminjaman')) : null;

        return [
            'kode_transaksi' => 'nullable|string|max:50|unique:peminjaman,kode_transaksi,' . $peminjamanID,
            'nama_peminjam'  => 'required|string|min:3|max:255',
            'alat_dipinjam'  => 'required|string|max:255',
            'jumlah_pinjam'  => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tenggat_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
            'status'         => 'required|in:Dipinjam,Dikembalikan',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_peminjam.required'         => 'Nama peminjam wajib diisi.',
            'nama_peminjam.min'              => 'Nama peminjam minimal 3 karakter.',
            'nama_peminjam.max'              => 'Nama peminjam maksimal 255 karakter.',
            'alat_dipinjam.required'         => 'Pilihan alat yang dipinjam wajib dipilih.',
            'jumlah_pinjam.required'         => 'Jumlah pinjam wajib diisi.',
            'jumlah_pinjam.integer'          => 'Jumlah pinjam harus berupa angka bulat.',
            'jumlah_pinjam.min'              => 'Jumlah pinjam minimal 1 unit.',
            'tanggal_pinjam.required'        => 'Tanggal pinjam wajib diisi.',
            'tanggal_pinjam.date'            => 'Format tanggal pinjam tidak valid.',
            'tenggat_kembali.required'       => 'Tenggat kembali wajib diisi.',
            'tenggat_kembali.date'           => 'Format tenggat kembali tidak valid.',
            'tenggat_kembali.after_or_equal' => 'Tenggat kembali tidak boleh lebih awal dari tanggal pinjam.',
            'status.required'                => 'Status peminjaman wajib ditentukan.',
            'status.in'                      => 'Status peminjaman hanya boleh Dipinjam atau Dikembalikan.',
            'kode_transaksi.unique'          => 'Kode transaksi sudah digunakan.',
        ];
    }
}
```

---

## LANGKAH 4: MEMBUAT CONTROLLER (LOGIKA BISNIS & CRUD)

### 4.1 Menjalankan Perintah Pembuatan Controller Resource
Jalankan perintah artisan:
```bash
php artisan make:controller PeminjamanController --resource
```
*Tujuan*: Menghasilkan kerangka controller lengkap dengan method standar RESTful (`index`, `create`, `store`, `edit`, `update`, `destroy`).

### 4.2 Menuliskan Kode Lengkap Controller
Buka berkas `app/Http/Controllers/PeminjamanController.php` dan tuliskan kode lengkap berikut:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeminjamanRequest;
use App\Models\Peminjaman;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    /**
     * Master data alat gunung beserta kuota stok fisik dasar di gudang.
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
     * Menampilkan rekapitulasi monitoring stok alat dan daftar riwayat transaksi peminjaman.
     */
    public function index()
    {
        $masterAlat = $this->getMasterAlat();
        $totalUnitAlat = 0;
        $sedangDipinjam = 0;

        foreach ($masterAlat as &$item) {
            // [KEAMANAN - SQL INJECTION]: Eloquent ORM menggunakan PDO Parameter Binding (Prepared Statements)
            // Nilai input dipisahkan sebagai placeholder '?' sehingga aman dari manipulasi SQL Injection.
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

        // Mengambil data peminjaman berurut terbaru dengan pagination 10 data
        $peminjamans = Peminjaman::latest()->paginate(10);

        // Auto-generate format kode transaksi berurutan (TRX-001, TRX-002, dst)
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
     * Menampilkan form input peminjaman baru.
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
     * Menyimpan transaksi peminjaman baru ke database dengan validasi stok fisik.
     */
    public function store(PeminjamanRequest $request)
    {
        $alat = $request->alat_dipinjam;
        $qty = (int) $request->jumlah_pinjam;
        $masterList = array_column($this->getMasterAlat(), 'total', 'nama');

        // Validasi ketersediaan stok fisik barang sebelum memproses peminjaman
        if (isset($masterList[$alat])) {
            $currentDipinjam = (int) Peminjaman::where('alat_dipinjam', $alat)
                ->where('status', 'Dipinjam')
                ->sum('jumlah_pinjam');

            $sisaStok = max(0, $masterList[$alat] - $currentDipinjam);

            if ($request->status === 'Dipinjam' && ($currentDipinjam + $qty) > $masterList[$alat]) {
                return back()
                    ->withInput()
                    ->with('error', "Stok tidak mencukupi! Sisa stok untuk alat \"{$alat}\" hanya tersisa {$sisaStok} unit (Anda meminta {$qty} unit).");
            }
        }

        $kode = $request->filled('kode_transaksi') ? $request->kode_transaksi : null;
        if (!$kode) {
            $last = Peminjaman::latest('id')->first();
            $nextNum = $last ? ($last->id + 1) : 1;
            $kode = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        // [KEAMANAN - SQL INJECTION & MASS ASSIGNMENT]:
        // 1. Eloquent ::create() otomatis menggunakan PDO Parameter Binding sehingga aman dari SQL Injection.
        // 2. Hanya atribut yang terdaftar pada properti $fillable di model Peminjaman yang diizinkan (proteksi Mass Assignment).
        Peminjaman::create([
            'kode_transaksi'  => $kode,
            'nama_peminjam'   => $request->nama_peminjam,
            'alat_dipinjam'   => $request->alat_dipinjam,
            'jumlah_pinjam'   => $qty,
            'tanggal_pinjam'  => $request->tanggal_pinjam,
            'tenggat_kembali' => $request->tenggat_kembali,
            'status'          => $request->status ?? 'Dipinjam',
        ]);

        return to_route('peminjaman.index')->with('success', 'Data transaksi peminjaman berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit transaksi peminjaman.
     */
    public function edit(Peminjaman $peminjaman)
    {
        $masterAlat = $this->getMasterAlat();

        // [KEAMANAN - SQL INJECTION]: Query filtering dengan where() otomatis menggunakan PDO parameter binding
        foreach ($masterAlat as &$item) {
            $dipinjamCount = (int) Peminjaman::where('alat_dipinjam', $item['nama'])
                ->where('status', 'Dipinjam')
                ->where('id', '!=', $peminjaman->id) // Abaikan data saat ini
                ->sum('jumlah_pinjam');

            $item['sisa_stok'] = max(0, $item['total'] - $dipinjamCount);
        }
        unset($item);

        return view('peminjaman.edit', compact('peminjaman', 'masterAlat'));
    }

    /**
     * Memperbarui data transaksi peminjaman atau mengubah status menjadi dikembalikan.
     */
    public function update(PeminjamanRequest $request, Peminjaman $peminjaman)
    {
        $alat = $request->alat_dipinjam;
        $qty = (int) $request->jumlah_pinjam;
        $masterList = array_column($this->getMasterAlat(), 'total', 'nama');

        if (isset($masterList[$alat]) && $request->status === 'Dipinjam') {
            $currentDipinjam = (int) Peminjaman::where('alat_dipinjam', $alat)
                ->where('status', 'Dipinjam')
                ->where('id', '!=', $peminjaman->id)
                ->sum('jumlah_pinjam');

            $sisaStok = max(0, $masterList[$alat] - $currentDipinjam);

            if (($currentDipinjam + $qty) > $masterList[$alat]) {
                return back()
                    ->withInput()
                    ->with('error', "Stok tidak mencukupi! Sisa stok untuk alat \"{$alat}\" hanya tersisa {$sisaStok} unit (Anda meminta {$qty} unit).");
            }
        }

        // [KEAMANAN - SQL INJECTION & MASS ASSIGNMENT]:
        // Update data via instance Eloquent menggunakan prepared statements dan terlindungi oleh $fillable
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
```

---

## LANGKAH 5: MENDAFTARKAN ROUTING WEB

### 5.1 Menuliskan Kode Rute Resource
Buka berkas `routes/web.php` dan pastikan `PeminjamanController` didaftarkan:

```php
<?php

use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('users', UserController::class)->except(['show']);

// Mendaftarkan seluruh endpoint CRUD peminjaman secara otomatis
Route::resource('peminjaman', PeminjamanController::class)->except(['show']);
```

---

## LANGKAH 6: MEMBUAT TAMPILAN ANTARMUKA (BLADE VIEWS)

Buat folder baru `resources/views/peminjaman/` untuk menampung file-file antarmuka.

### 6.1 Membuat View Utama (Monitoring Stok & Riwayat Transaksi)
Buka berkas `resources/views/peminjaman/peminjaman.blade.php` dan tuliskan kode berikut:

```blade
@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <!-- Header Halaman -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Peminjaman Alat & Monitoring Stok Gunung</h1>
            <p class="text-muted small mb-0">Kelola transaksi sewa dan pantau sisa kuota unit barang secara real-time.</p>
        </div>
        <a href="{{ route('peminjaman.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm px-3 py-2">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Peminjaman
        </a>
    </div>

    <!-- Alert Notifikasi Flash Session -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Cards Ringkasan Stok (3 Cards Metrik) -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Unit Alat</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUnitAlat }} Unit</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sedang Dipinjam</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sedangDipinjam }} Unit</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sisa Stok Tersedia</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sisaStokTersedia }} Unit</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Transaksi Peminjaman -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Transaksi Peminjaman</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Kode TRX</th>
                            <th>Nama Peminjam</th>
                            <th>Alat</th>
                            <th>Jumlah</th>
                            <th>Tgl Pinjam</th>
                            <th>Tenggat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $index => $peminjaman)
                        <tr>
                            <td>{{ $peminjamans->firstItem() + $index }}</td>
                            <td><strong class="text-primary">{{ $peminjaman->kode_transaksi }}</strong></td>
                            {{-- [KEAMANAN - SANITASI XSS]: Sintaks kurung kurawal ganda {{ ... }} otomatis menjalankan htmlspecialchars() via helper e(), mencegah injeksi script/HTML berbahaya --}}
                            <td class="font-weight-bold text-gray-800">{{ $peminjaman->nama_peminjam }}</td>
                            <td>{{ $peminjaman->alat_dipinjam }}</td>
                            <td>{{ $peminjaman->jumlah_pinjam }} Unit</td>
                            <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($peminjaman->tenggat_kembali)->translatedFormat('d M Y') }}</td>
                            <td>
                                @if($peminjaman->status == 'Dipinjam')
                                    <span class="badge badge-warning">Dipinjam</span>
                                @else
                                    <span class="badge badge-success">Dikembalikan</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('peminjaman.edit', $peminjaman) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('peminjaman.destroy', $peminjaman) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data peminjaman ini?');">
                                    {{-- [KEAMANAN - CSRF TOKEN]: Mencegah serangan Cross-Site Request Forgery dengan generate hidden input _token yang divalidasi middleware Laravel --}}
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">Belum ada data transaksi peminjaman.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $peminjamans->links() }}
        </div>
    </div>
</div>
@endsection
```

---

### 6.2 Membuat View Form Tambah Peminjaman
Buka berkas `resources/views/peminjaman/create.blade.php` dan tuliskan kode berikut:

```blade
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tambah Peminjaman Alat</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('peminjaman.store') }}" method="POST">
                {{-- [KEAMANAN - CSRF TOKEN]: Mencegah serangan Cross-Site Request Forgery via token sesi otomatis --}}
                @csrf

                <div class="mb-3">
                    <label class="font-weight-bold">Kode Transaksi</label>
                    <input type="text" name="kode_transaksi" class="form-control bg-light" value="{{ old('kode_transaksi', $nextKodeTrx) }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold">Nama Peminjam <span class="text-danger">*</span></label>
                    <input type="text" name="nama_peminjam" class="form-control" value="{{ old('nama_peminjam') }}" required>
                    @error('nama_peminjam') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="font-weight-bold">Alat Gunung <span class="text-danger">*</span></label>
                        <select name="alat_dipinjam" class="form-control" required>
                            <option value="">-- Pilih Alat Gunung --</option>
                            @foreach($masterAlat as $alat)
                                <option value="{{ $alat['nama'] }}" {{ old('alat_dipinjam') == $alat['nama'] ? 'selected' : '' }}>
                                    {{ $alat['nama'] }} (Sisa Stok: {{ $alat['sisa_stok'] }} Unit)
                                </option>
                            @endforeach
                        </select>
                        @error('alat_dipinjam') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Jumlah Unit <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_pinjam" class="form-control" value="{{ old('jumlah_pinjam', 1) }}" min="1" required>
                        @error('jumlah_pinjam') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pinjam" class="form-control" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                        @error('tanggal_pinjam') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Tenggat Kembali <span class="text-danger">*</span></label>
                        <input type="date" name="tenggat_kembali" class="form-control" value="{{ old('tenggat_kembali') }}" required>
                        @error('tenggat_kembali') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <input type="hidden" name="status" value="Dipinjam">

                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

### 6.3 Menambahkan Menu Navigasi di Sidebar
Buka berkas `resources/views/includes/sidebar.blade.php` dan pastikan tautan menu peminjaman ditambahkan:

```blade
<!-- Nav Item - Peminjaman Alat -->
<li class="nav-item {{ request()->is('peminjaman*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('peminjaman.index') }}">
        <i class="bi bi-backpack-fill me-1"></i>
        <span>Peminjaman Alat</span>
    </a>
</li>
```

---

## LANGKAH 7: PENGUJIAN OTOMATIS (AUTOMATED TESTING)

### 7.1 Menjalankan Perintah Pembuatan Test
Jalankan perintah artisan:
```bash
php artisan make:test PeminjamanTest
```
*Tujuan*: Menghasilkan berkas pengujian fitur (*Feature Test*) di `tests/Feature/PeminjamanTest.php`.

### 7.2 Menuliskan Kode Pengujian Fitur
Buka berkas `tests/Feature/PeminjamanTest.php` dan tuliskan skenario uji:

```php
<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_index_peminjaman_dapat_ditampilkan_beserta_info_stok(): void
    {
        Peminjaman::create([
            'kode_transaksi'  => 'TRX-001',
            'nama_peminjam'   => 'Rendi Wijaya',
            'alat_dipinjam'   => 'Tenda Dome 4P',
            'jumlah_pinjam'   => 2,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-20',
            'status'          => 'Dipinjam',
        ]);

        $response = $this->get(route('peminjaman.index'));

        $response->assertStatus(200);
        $response->assertSee('Rendi Wijaya');
        $response->assertSee('Tenda Dome 4P');
    }

    public function test_transaksi_peminjaman_baru_berhasil_disimpan(): void
    {
        $payload = [
            'kode_transaksi'  => 'TRX-001',
            'nama_peminjam'   => 'Siti Rahma',
            'alat_dipinjam'   => 'Carrier 60L',
            'jumlah_pinjam'   => 1,
            'tanggal_pinjam'  => '2026-10-17',
            'tenggat_kembali' => '2026-10-19',
            'status'          => 'Dipinjam',
        ];

        $response = $this->post(route('peminjaman.store'), $payload);

        $response->assertRedirect(route('peminjaman.index'));
        $this->assertDatabaseHas('peminjaman', [
            'nama_peminjam' => 'Siti Rahma',
            'alat_dipinjam' => 'Carrier 60L',
        ]);
    }
}
```

### 7.3 Menjalankan Automated Test
Jalankan perintah ini di terminal untuk memverifikasi seluruh fungsionalitas:
```bash
php artisan test --filter=PeminjamanTest
```
*Hasil*: Seluruh pengujian lolos (**PASS / Status Hijau**) membuktikan bahwa database, controller, validasi, dan view terintegrasi secara sempurna.
