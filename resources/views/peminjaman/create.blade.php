@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Catat Peminjaman Baru</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('peminjaman.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="font-weight-bold">Kode Transaksi</label>
                    <input type="text" name="kode_transaksi" class="form-control bg-light" value="{{ old('kode_transaksi', $nextKodeTrx) }}" readonly>
                    <small class="form-text text-muted">Kode transaksi dibuat otomatis oleh sistem (TRX-xxx).</small>
                    @error('kode_transaksi') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold">Nama Peminjam <span class="text-danger">*</span></label>
                    <input type="text" name="nama_peminjam" class="form-control" value="{{ old('nama_peminjam') }}" placeholder="Contoh: Rian Anggara" required>
                    @error('nama_peminjam') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="row">
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
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Jumlah Pinjam (Unit) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_pinjam" class="form-control" value="{{ old('jumlah_pinjam', 1) }}" min="1" placeholder="Contoh: 2" required>
                        <small class="form-text text-muted">Jumlah unit yang dipinjam.</small>
                        @error('jumlah_pinjam') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pinjam" class="form-control" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                        @error('tanggal_pinjam') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Tenggat Kembali <span class="text-danger">*</span></label>
                        <input type="date" name="tenggat_kembali" class="form-control" value="{{ old('tenggat_kembali', date('Y-m-d', strtotime('+3 days'))) }}" required>
                        @error('tenggat_kembali') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold">Status Peminjaman</label>
                    <input type="text" class="form-control bg-light" value="Dipinjam" readonly>
                    <input type="hidden" name="status" value="Dipinjam">
                    <small class="form-text text-muted">Status transaksi baru otomatis diset ke "Dipinjam".</small>
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>

</div>
@endsection
