@extends('layouts.app')

@section('title', 'Kuisioner')

@section('content')
<div class="container py-5">
    <h4 class="fw-bold text-primary mb-4">Silahkan mengisikan kuisioner terlebih dahulu,{{ $peserta->nama_peserta }}!</h4>

    @if ($kuisioners->count())
        @php
            $sudahIsi = $jawabanTersimpan && $jawabanTersimpan->count() >= $kuisioners->count();
        @endphp

        @if ($sudahIsi)
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-4 text-success"></i>
                <div>
                    Kuisioner sudah diisi. Terima kasih atas partisipasi Anda.
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @foreach ($kuisioners as $kuisioner)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ $loop->iteration }}. {{ $kuisioner->pertanyaan }}</label>
                            <div class="form-control bg-light">{{ $jawabanTersimpan[$kuisioner->id] ?? '-' }}</div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        <a href="{{ route('events.list') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('kuisioner.simpan', $peserta->id) }}">
                @csrf

                @foreach ($kuisioners as $kuisioner)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ $loop->iteration }}. {{ $kuisioner->pertanyaan }}</label>
                        <input
                            type="text"
                            name="jawaban_{{ $kuisioner->id }}"
                            class="form-control"
                            placeholder="Tulis jawaban Anda..."
                            required
                        >
                    </div>
                @endforeach

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('events.list') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send-check me-1"></i> Kirim Jawaban
                    </button>
                </div>
            </form>
        @endif
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Kuisioner belum tersedia untuk event ini.
        </div>

        <a href="{{ route('events.list') }}" class="btn btn-outline-secondary mt-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    @endif
</div>
@endsection
