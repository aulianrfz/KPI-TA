@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}+{{ Auth::user()->last_name }}&background=0367A6&color=fff"
                     alt="Profile" class="rounded-circle me-3" width="70" height="70">
                <div>
                    <h5 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h5>
                    <small class="text-muted">{{ $user->email }}</small>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Log out</button>
            </form>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" value="{{ $user->first_name }} {{ $user->last_name }}" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">User Name</label>
                <input type="text" class="form-control" value="{{ $user->username }}" readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" value="{{ $user->email }}" readonly>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" value="passwordfake" readonly>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('profile.edit') }}" class="btn btn-secondary">Edit Profil</a>
            <a href="#" class="btn btn-outline-primary">Ajukan Pengajuan</a>
        </div>
    </div>
</div>

@endsection

