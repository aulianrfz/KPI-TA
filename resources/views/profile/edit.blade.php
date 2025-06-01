@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm p-4">
        <h4 class="mb-4">Edit Profil</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}">
                </div>
                <div class="col-md-6">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                </div>
            </div>

            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}">
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
            </div>

            <hr>

            <div class="mb-3">
                <label>New Password (kosongkan jika tidak ingin mengubah)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>

@endsection
