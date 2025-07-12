@extends('layouts.app')

@section('body-class', 'auth-page')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-header">Reset Password</div>

        <div class="auth-form">
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="email" name="email" placeholder="Masukkan email kamu" value="{{ old('email') }}" required>

                @error('email')
                    <div class="error-text" style="color: red; font-size: 14px;">
                        {{ $message }}
                    </div>
                @enderror

                <button type="submit">Kirim Link Reset</button>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Sukses!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = "{{ route('login') }}";
            });
        </script>
    @endif
@endpush