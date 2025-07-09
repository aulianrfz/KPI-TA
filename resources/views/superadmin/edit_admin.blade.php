@extends('layouts.apk')

@section('content')
    <div class="container mt-4">
        <h3>Edit Admin</h3>
        <form action="{{ route('superadmin.admin.update', $admin->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="first_name" class="form-label">Nama Depan</label>
                <input type="text" class="form-control" name="first_name"
                    value="{{ old('first_name', $admin->user->first_name) }}" required>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Nama Belakang</label>
                <input type="text" class="form-control" name="last_name"
                    value="{{ old('last_name', $admin->user->last_name) }}">
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username"
                    value="{{ old('username', $admin->user->username) }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email', $admin->user->email) }}"
                    required>
            </div>

            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" class="form-control" name="jabatan" value="{{ old('jabatan', $admin->jabatan) }}"
                    required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi (Kosongkan jika tidak diubah)</label>
                <input type="password" class="form-control" name="password">
            </div>

            <div class="mb-3">
                <label class="form-label">Status Admin</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_active" id="aktif" value="1"
                        {{ old('is_active', $admin->is_active) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="aktif">Aktif</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_active" id="nonaktif" value="0"
                        {{ old('is_active', $admin->is_active) == 0 ? 'checked' : '' }}>
                    <label class="form-check-label" for="nonaktif">Nonaktif</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('superadmin.admin.list') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection