@extends('layouts.app')

@section('body-class', 'auth-page')

@section('content')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <div class="auth-wrapper">
        <div class="auth-header">Reset Password</div>

        <div class="auth-form">
            @if ($errors->any())
                <div style="color: red; font-size: 14px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="password-field position-relative mb-3">
                    <input type="password" id="password" name="password" placeholder="Password baru"
                        class="form-control pr-5" required>
                    <i class="bi bi-eye-slash toggle-password position-absolute" toggle="#password"
                        style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                </div>

                <div class="password-field position-relative mb-3">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        placeholder="Ulangi password" class="form-control pr-5" required>
                    <i class="bi bi-eye-slash toggle-password position-absolute" toggle="#password_confirmation"
                        style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                </div>

                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toggle-password').forEach(function (icon) {
                icon.addEventListener('click', function () {
                    const input = document.querySelector(this.getAttribute('toggle'));
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);

                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');
                });
            });
        });
    </script>
@endpush