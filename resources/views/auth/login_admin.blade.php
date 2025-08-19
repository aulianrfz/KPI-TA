@extends('layouts.app')
@section('body-class', 'auth-page')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-header">Sign up</div>
        <div class="auth-tabs">
            <a href="{{ route('login') }}">Log in</a>
            <!-- <a href="{{ route('register') }}">User Sign up</a> -->
            <a href="{{ route('register.admin.form') }}" class="active">Admin Sign up</a>
        </div>
        <div class="auth-form">
            <form method="POST" action="{{ route('register.admin') }}">
                @csrf

                <input name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                @error('first_name')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                <input name="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
                @error('last_name')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                <input name="jabatan" placeholder="Jabatan" value="{{ old('jabatan') }}" required>
                @error('jabatan')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                <input name="username" placeholder="Username" value="{{ old('username') }}" required>
                @error('username')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                <input name="email" placeholder="Email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                <input name="password" type="password" placeholder="Password" required>
                @error('password')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                <input name="password_confirmation" type="password" placeholder="Confirm Password" required>

                <button type="submit">Continue</button>
            </form>
        </div>
    </div>
@endsection