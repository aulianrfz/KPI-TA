@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2 class="text-bold">Pendaftaran Supporter</h2>
        <hr style="width: 230px; border-top: 2px solid #000;">

        <form action="{{ route('supporter.store') }}" method="POST">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Instansi</label>
                <select name="instansi" class="form-select" required>
                    <option value="">- Pilih Instansi -</option>
                    @foreach ($institusi as $inst)
                        <option value="{{ $inst->nama_institusi }}">{{ $inst->nama_institusi }}</option>
                    @endforeach
                </select>
            </div>


            <div class="mb-3">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="no_hp" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Daftar</button>
        </form>
    </div>
@endsection