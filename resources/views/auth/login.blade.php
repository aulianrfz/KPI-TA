@extends('layouts.apk')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <h2>Login</h2>
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<p>Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
@endsection
