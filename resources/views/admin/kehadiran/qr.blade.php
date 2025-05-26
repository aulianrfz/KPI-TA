@extends('layouts.apk')

@section('content')
<div class="p-6">
    <div class="bg-white rounded-xl p-6 shadow max-w-lg mx-auto text-center">
        <h2 class="text-xl font-bold mb-4">QR Code Peserta</h2>
        <div class="mb-4">
            <p class="font-medium">{{ $pendaftar->peserta->nama_peserta }}</p>
            <p class="text-sm text-gray-500">{{ $pendaftar->peserta->institusi }}</p>
        </div>
        <div class="flex justify-center">
            <img src="{{ asset('storage/qrcode/'.$pendaftar->id.'.png') }}" alt="QR Code" class="w-60 h-60">
        </div>
        <div class="mt-4">
            <a href="{{ route('kehadiran.index') }}" class="text-blue-600 hover:underline">← Kembali</a>
        </div>
    </div>
</div>
@endsection
