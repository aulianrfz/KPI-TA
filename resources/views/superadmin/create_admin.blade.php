@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h3>Tambah Admin</h3>
    <form action="{{ route('superadmin.admin.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="first_name" class="form-label">Nama Depan</label>
            <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" required>
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Nama Belakang</label>
            <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}">
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" value="{{ old('username') }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label for="jabatan" class="form-label">Jabatan</label>
            <input type="text" class="form-control" name="jabatan" value="{{ old('jabatan') }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Kata Sandi</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('superadmin.admin.list') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
