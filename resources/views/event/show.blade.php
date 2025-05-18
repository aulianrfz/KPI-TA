@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<div class="container mt-5">
    <div class="row gx-4 align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
            <img src="{{ $event->foto ? asset('storage/' . $event->foto) : asset('images/event.jpeg') }}" 
                class="img-fluid rounded-4"
                style="object-fit: cover; width: 140%;"
                alt="Event Image">
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm rounded-4 mb-4">
                <div class="card-body text-center p-4">
                    <h5 class="fw-bold mb-5">{{ $event->nama_event }}</h5>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <small>{{ $event->penyelenggara }}</small>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-calendar-date text-primary me-2"></i>
                        <small>{{ $event->tanggal }}</small>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm rounded-4">
                <div class="card-body text-center p-">
                    @auth
                        <a href="{{ route('event.list', $event->id) }}" 
                            class="btn btn-success w-100" 
                            style="background-color: #2CC384; border-color: #2CC384; height: 50px;">
                            Daftar Sekarang
                        </a>
                    @else
                        <button class="btn btn-secondary w-100" id="showLoginModalBtn" style="height: 50px;">
                            Daftar
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h5 class="fw-semibold mb-3">Tentang {{ $event->nama_event }}</h5>
        <p>{{ $event->deskripsi }}</p>
    </div>
</div>

<div class="modal fade" id="loginAlertModal" tabindex="-1" aria-labelledby="loginAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginAlertModalLabel">Pemberitahuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Silakan login terlebih dahulu untuk melanjutkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var showLoginModalBtn = document.getElementById('showLoginModalBtn');
        if (showLoginModalBtn) {
            showLoginModalBtn.addEventListener('click', function() {
                var myModal = new bootstrap.Modal(document.getElementById('loginAlertModal'));
                myModal.show();
            });
        }
    });
</script>
