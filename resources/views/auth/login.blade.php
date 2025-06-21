@extends('layouts.app')

@section('body-class', 'auth-page')

@section('content')
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
            <input name="password" type="password" placeholder="Password" required>
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
    </div>
</div>
@endsection
