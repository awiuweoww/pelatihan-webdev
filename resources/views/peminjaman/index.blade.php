@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <!-- Header Halaman & Deskripsi Modul -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Peminjaman Alat & Monitoring Stok Gunung</h1>
            <p class="mb-0 text-muted small">
                Modul ini berfungsi untuk memantau ketersediaan stok fisik barang, mendata transaksi sewa, serta memperbarui alur pengembalian secara real-time.
            </p>
        </div>
        <a href="{{ route('peminjaman.create') }}" class="btn btn-primary shadow-sm mt-3 mt-sm-0">
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- 1. Ringkasan Stok & Inventaris Alat (Cards dari SB Admin 2) -->
    <div class="row mb-4">
        <!-- Total Unit Alat -->
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Unit Alat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUnitAlat }} Unit</div>
                            <div class="small text-muted mt-1">Total stok dari seluruh master alat</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sedang Dipinjam -->
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Sedang Dipinjam
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sedangDipinjam }} Unit</div>
                            <div class="small text-muted mt-1">Barang berada di tangan peminjam</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-people-carry fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisa Stok Tersedia -->
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sisa Stok Tersedia
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sisaStokTersedia }} Unit</div>
                            <div class="small text-muted mt-1">Siap disewa untuk pendakian</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Tabel Ketersediaan per Alat (Master Data) -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-th-list mr-1"></i> Ketersediaan Inventaris per Alat Gunung
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Nama Alat Gunung</th>
                            <th class="text-center" style="width: 140px;">Total Stok</th>
                            <th class="text-center" style="width: 160px;">Sedang Dipinjam</th>
                            <th class="text-center" style="width: 160px;">Stok Tersedia</th>
                            <th class="text-center" style="width: 160px;">Status Ketersediaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($masterAlat as $index => $item)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                            <td class="font-weight-bold text-gray-800">{{ $item['nama'] }}</td>
                            <td class="text-center">
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">{{ $item['total'] }} Unit</span>
                            </td>
                            <td class="text-center">
                                <span class="text-warning font-weight-bold">{{ $item['sedang_dipinjam'] }} Unit</span>
                            </td>
                            <td class="text-center">
                                <span class="text-success font-weight-bold">{{ $item['sisa_stok'] }} Unit</span>
                            </td>
                            <td class="text-center">
                                @if($item['status_ketersediaan'] == 'Tersedia')
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Tersedia</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i> Habis</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. CRUD Transaksi Peminjaman (List State & Empty State) -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-1"></i> Data Transaksi Peminjaman Alat
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 130px;">Kode TRX</th>
                            <th>Nama Peminjam</th>
                            <th>Alat yang Dipinjam</th>
                            <th class="text-center text-nowrap" style="width: 110px;">Jumlah</th>
                            <th class="text-center" style="width: 130px;">Tgl Pinjam</th>
                            <th class="text-center" style="width: 140px;">Tenggat Kembali</th>
                            <th class="text-center" style="width: 140px;">Status</th>
                            <th class="text-center text-nowrap" style="width: 170px; white-space: nowrap;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $index => $peminjaman)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $peminjamans->firstItem() + $index }}</td>
                            <td><strong class="text-primary">{{ $peminjaman->kode_transaksi }}</strong></td>
                            <td class="font-weight-bold text-gray-800">{{ $peminjaman->nama_peminjam }}</td>
                            <td><span class="badge badge-light border text-dark">{{ $peminjaman->alat_dipinjam }}</span></td>
                            <td class="text-center text-nowrap">
                                <span class="badge badge-secondary px-2 py-1 font-weight-bold">{{ $peminjaman->jumlah_pinjam ?? 1 }} Unit</span>
                            </td>
                            <td class="text-center text-nowrap">
                                {{ $peminjaman->tanggal_pinjam ? \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="text-center text-nowrap">
                                {{ $peminjaman->tenggat_kembali ? \Carbon\Carbon::parse($peminjaman->tenggat_kembali)->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="text-center text-nowrap">
                                @if($peminjaman->status == 'Dipinjam')
                                    <span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> Dipinjam</span>
                                @else
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-double mr-1"></i> Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap" style="white-space: nowrap;">
                                <div class="d-inline-flex align-items-center justify-content-center">
                                    <a href="{{ route('peminjaman.edit', $peminjaman) }}" class="btn btn-warning btn-sm shadow-sm mr-2" title="Edit Transaksi">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('peminjaman.destroy', $peminjaman) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data transaksi peminjaman ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm" title="Hapus Transaksi">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <!-- Empty State Tampilan Saat Data Transaksi Kosong -->
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="my-3">
                                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3 d-block"></i>
                                    <h5 class="text-gray-800 font-weight-bold">Belum Ada Data Transaksi Peminjaman</h5>
                                    <p class="text-muted mb-3 mx-auto" style="max-width: 500px;">
                                        Saat ini belum ada data transaksi peminjaman alat yang tercatat. Silakan klik tombol di bawah untuk mencatat transaksi peminjaman baru.
                                    </p>
                                    <a href="{{ route('peminjaman.create') }}" class="btn btn-primary shadow-sm">
                                        <i class="fas fa-plus mr-1"></i> Tambah Peminjaman Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $peminjamans->links() }}
            </div>
        </div>
    </div>

</div>
@endsection

@push('after-script')
<script>
    console.log("Modul Peminjaman & Monitoring Stok Gunung Siap Digunakan.");
</script>
@endpush
