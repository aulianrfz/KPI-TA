@extends('layouts.apk')

@section('content')
<h1>Admin Dashboard</h1>
<p>Selamat datang, {{ auth()->user()->first_name }}!</p>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
@endsection
