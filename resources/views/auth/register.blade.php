@extends('layouts.app')

@section('body-class', 'auth-page')

@section('content')
<div class="auth-wrapper">
    <div class="auth-header">Sign up</div>
    <div class="auth-tabs">
        <a href="{{ route('login') }}">Log in</a>
        <a href="{{ route('register') }}" class="active">Sign up</a>
    </div>
    <div class="auth-form">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <input name="first_name" placeholder="First Name" required>
            <input name="last_name" placeholder="Last Name" required>
            <input name="username" placeholder="Username" required>
            <input name="email" placeholder="Email" required>
            <input name="password" type="password" placeholder="Password" required>
            <input name="password_confirmation" type="password" placeholder="Confirm Password" required>
            <button type="submit">Continue</button>
        </form>
    </div>
</div>
@endsection
