{{--
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Formulir edit informasi dan kredensial pengguna terdaftar.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi form pengubahan data pengguna dan penggantian password opsional.
--}}
@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <!-- Header Halaman / Judul -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
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

    <!-- Card Kontainer Formulir Edit User -->
    <div class="card shadow mb-4">
        <!-- Header Card -->
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Edit Data Pengguna: {{ $user->name }}</h6>
        </div>

        <!-- Body Card: Form Edit Pengguna -->
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method("PUT")

                <!-- Input: Nama Lengkap Pengguna -->
                <div class="mb-3">
                    <label class="font-weight-bold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <!-- Input: Alamat Email Pengguna -->
                <div class="mb-3">
                    <label class="font-weight-bold">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <!-- Input: Kata Sandi Baru (Opsional) -->
                <div class="mb-3">
                    <label class="font-weight-bold">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password baru jika ingin diubah">
                    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password lama.</small>
                    @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <!-- Tombol Aksi: Simpan Perubahan & Kembali -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
