@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3 gap-md-0">
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}+{{ Auth::user()->last_name }}&background=0367A6&color=fff"
                     alt="Profile"
                     class="rounded-circle me-3 profile-img">
                <div>
                    <h5 class="mb-1 profile-name">{{ $user->first_name }} {{ $user->last_name }}</h5>
                    <small class="text-muted profile-email">{{ $user->email }}</small>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm logout-btn">Log out</button>
            </form>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control form-control-sm" value="{{ $user->first_name }} {{ $user->last_name }}" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label class="form-label">User Name</label>
                <input type="text" class="form-control form-control-sm" value="{{ $user->username }}" readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="text" class="form-control form-control-sm" value="{{ $user->email }}" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control form-control-sm" value="passwordfake" readonly>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-3">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">Edit Profil</a>
            @if(Auth::user()->role === 'user')
                <a href="{{ route('pengajuan.index') }}" class="btn btn-outline-primary btn-sm">Ajukan Pengajuan</a>
            @endif
        </div>
    </div>
</div>

<style>
    @media (max-width: 576px) {
        .profile-img {
            width: 50px !important;
            height: 50px !important;
        }
        .profile-name {
            font-size: 1rem;
        }
        .profile-email {
            font-size: 0.8rem;
        }
        .logout-btn {
            font-size: 0.8rem;
            padding: 4px 10px;
        }
    }
</style>
@endsection
