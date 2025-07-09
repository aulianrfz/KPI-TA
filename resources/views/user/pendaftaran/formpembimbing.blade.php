@extends('layouts.app')

@section('content')

    <div class="container mt-4">
        <h2 class="text-bold">Pendaftaran Pembimbing</h2>
        <hr style="width: 280px; border-top: 2px solid #000;">

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Ups! Ada yang salah:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pembimbing.store') }}" method="POST" enctype="multipart/form-data" id="formPembimbing">
            @csrf

            <input type="hidden" name="event_id" value="{{ $event->id }}">

            <div class="card mb-4">
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP</label>
                            <input type="text" class="form-control" name="nip" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Instansi</label>
                            <select name="instansi" class="form-select" required>
                                <option value="">- Pilih Instansi -</option>
                                @foreach ($institusi as $inst)
                                    <option value="{{ $inst->nama_institusi }}">{{ $inst->nama_institusi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text" class="form-control" name="jabatan" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" name="no_hp" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Surat Tugas (Opsional)</label>
                            <input type="file" class="form-control" name="surat_tugas" accept="application/pdf,image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visum (Opsional)</label>
                            <input type="file" class="form-control" name="visum" accept="application/pdf,image/*">
                        </div>
                    </div>

                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success px-4">Submit Pendaftaran</button>
            </div>

        </form>
    </div>

@endsection