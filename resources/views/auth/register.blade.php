@extends('layouts.apk')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf
    <h2>Register</h2>
    <input name="first_name" placeholder="First Name" required>
    <input name="last_name" placeholder="Last Name" required>
    <input name="username" placeholder="Username" required>
    <input name="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required>
    <input name="password_confirmation" type="password" placeholder="Confirm Password" required>
    <button type="submit">Register</button>
</form>
<p>Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
@endsection
