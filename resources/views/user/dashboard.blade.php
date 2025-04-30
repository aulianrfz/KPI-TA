@extends('layouts.app')

@section('content')
<h1>User Dashboard</h1>
<p>Halo, {{ auth()->user()->first_name }}!</p>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
@endsection
