{{--
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Halaman daftar data pengguna sistem beserta opsi pagination dan tombol aksi.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi tabel daftar akun pengguna terdaftar.
--}}
@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <!-- Header Halaman & Tombol Tambah User -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Users</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-user-plus fa-sm text-white-50 mr-1"></i> Tambah User
        </a>
    </div>

    <!-- Alert Notifikasi Sukses -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Alert Notifikasi Error -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card Kontainer Tabel Pengguna -->
    <div class="card shadow mb-4">
        <!-- Header Card -->
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Pengguna Terdaftar</h6>
        </div>

        <!-- Body Card: Tabel Data -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="align-middle font-weight-bold">{{ $user->name }}</td>
                            <td class="align-middle">{{ $user->email }}</td>
                            <td class="text-center align-middle">
                                <!-- Tombol Aksi: Edit -->
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit fa-sm"></i> Edit
                                </a>

                                <!-- Tombol Aksi: Hapus -->
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash fa-sm"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Belum ada data user yang tersimpan</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Navigasi Pagination Halaman -->
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

</div>
@endsection

@push('after-script')
    <script>
        console.log("Jalan")
    </script>
@endpush
