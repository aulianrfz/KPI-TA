@extends('layouts.app')

@section('body-class', 'auth-page')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <div class="auth-wrapper">
        <div class="auth-header">Log in</div>
        <div class="auth-tabs">
            <a href="{{ route('login') }}" class="active">Log in</a>
            <a href="{{ route('register') }}">Sign up</a>
        </div>
        <div class="auth-form">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input name="username" placeholder="Username" value="{{ old('username') }}" required>

                <div class="password-field position-relative mb-3">
                    <input id="password" name="password" type="password" placeholder="Password" class="form-control pr-5"
                        required>
                    <i class="bi bi-eye-slash toggle-password position-absolute" toggle="#password"
                        style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                </div>

                @error('username')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror
                @error('password')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                @if ($errors->has('auth'))
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $errors->first('auth') }}
                    </div>
                @endif

                <button type="submit">Continue</button>
            </form>
            <div style="text-align: center; margin-top: 10px;">
                <a href="{{ route('password.request') }}" style="font-size: 14px; color: #007bff;">Lupa password?</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // toggle password
            document.querySelectorAll('.toggle-password').forEach(function (icon) {
                icon.addEventListener('click', function () {
                    const input = document.querySelector(this.getAttribute('toggle'));
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');
                });
            });

            // tampilkan error dengan SweetAlert
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Coba Lagi'
                });
            @endif
            });
    </script>
@endpush