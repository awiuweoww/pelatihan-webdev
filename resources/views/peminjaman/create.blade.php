{{--
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Formulir pencatatan peminjaman baru alat sewa gunung.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi form input peminjaman alat dengan validasi dan pilihan stok.
--}}
@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <!-- Header Halaman / Judul -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Peminjaman Alat</h1>
    </div>

    <!-- Alert Notifikasi Error -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card Kontainer Formulir -->
    <div class="card shadow mb-4">
        <!-- Header Card -->
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Catat Peminjaman Baru</h6>
        </div>
        
        <!-- Body Card: Form Input Peminjaman -->
        <div class="card-body">
            <form action="{{ route('peminjaman.store') }}" method="POST">
                {{-- [KEAMANAN - CSRF TOKEN]: Mencegah serangan Cross-Site Request Forgery via token sesi otomatis --}}
                @csrf

                <!-- Input: Kode Transaksi (Generate Otomatis) -->
                <div class="mb-3">
                    <label class="font-weight-bold">Kode Transaksi</label>
                    <input type="text" name="kode_transaksi" class="form-control bg-light" value="{{ old('kode_transaksi', $nextKodeTrx) }}" readonly>
                    <small class="form-text text-muted">Kode transaksi dibuat otomatis oleh sistem (TRX-xxx).</small>
                    @error('kode_transaksi') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <!-- Input: Nama Lengkap Peminjam -->
                <div class="mb-3">
                    <label class="font-weight-bold">Nama Peminjam <span class="text-danger">*</span></label>
                    <input type="text" name="nama_peminjam" class="form-control" value="{{ old('nama_peminjam') }}" placeholder="Contoh: Rian Anggara" required>
                    @error('nama_peminjam') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <!-- Baris Pilihan Alat & Jumlah Pinjam -->
                <div class="row">
                    <!-- Dropdown Pilihan Alat Gunung & Info Sisa Stok -->
                    <div class="col-md-8 mb-3">
                        <label class="font-weight-bold">Alat yang Dipinjam <span class="text-danger">*</span></label>
                        <select name="alat_dipinjam" class="form-control" required>
                            <option value="">-- Pilih Alat Gunung --</option>
                            @foreach($masterAlat as $item)
                                <option value="{{ $item['nama'] }}" {{ old('alat_dipinjam') == $item['nama'] ? 'selected' : '' }}>
                                    {{ $item['nama'] }} (Sisa Stok: {{ $item['sisa_stok'] }} Unit)
                                </option>
                            @endforeach
                        </select>
                        @error('alat_dipinjam') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <!-- Input: Jumlah Unit Pinjam -->
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Jumlah Pinjam (Unit) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_pinjam" class="form-control" value="{{ old('jumlah_pinjam', 1) }}" min="1" placeholder="Contoh: 2" required>
                        <small class="form-text text-muted">Jumlah unit yang dipinjam.</small>
                        @error('jumlah_pinjam') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Baris Periode Peminjaman (Tanggal Pinjam & Tenggat Kembali) -->
                <div class="row">
                    <!-- Input: Tanggal Pinjam -->
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pinjam" class="form-control" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                        @error('tanggal_pinjam') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <!-- Input: Tenggat Waktu Pengembalian -->
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Tenggat Kembali <span class="text-danger">*</span></label>
                        <input type="date" name="tenggat_kembali" class="form-control" value="{{ old('tenggat_kembali', date('Y-m-d', strtotime('+3 days'))) }}" required>
                        @error('tenggat_kembali') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Input: Status Awal Peminjaman -->
                <div class="mb-3">
                    <label class="font-weight-bold">Status Peminjaman</label>
                    <input type="text" class="form-control bg-light" value="Dipinjam" readonly>
                    <input type="hidden" name="status" value="Dipinjam">
                    <small class="form-text text-muted">Status transaksi baru otomatis diset ke "Dipinjam".</small>
                </div>

                <!-- Tombol Aksi: Simpan Data & Kembali -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
