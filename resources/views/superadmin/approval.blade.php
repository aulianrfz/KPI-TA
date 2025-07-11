@extends('layouts.apk')

@section('title', 'Approve Admin')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h4 class="mb-0">Verifikasi Admin</h4>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.admin.bulkAction') }}" id="bulk-action-form">
            @csrf

            <div class="d-flex justify-content-end mb-3 gap-2">
                <button type="submit" name="action" value="reject" class="btn btn-sm fw-medium" style="background-color: #FFD6D7; color: #FF1C20; border: 1px solid #FF1C20;">
                    <i class="bi bi-x-circle me-1"></i> Tolak
                </button>
                <button type="submit" name="action" value="approve" class="btn btn-sm fw-medium" style="background-color: #C7F5EE; color: #00B69B; border: 1px solid #00B69B;">
                    <i class="bi bi-check-circle me-1"></i> Setujui
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 3%;"><input type="checkbox" class="form-check-input" id="select-all"></th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $admin->id }}"></td>
                                <td>{{ $admin->user->first_name }} {{ $admin->user->last_name }}</td>
                                <td>{{ $admin->user->username }}</td>
                                <td>{{ $admin->user->email }}</td>
                                <td>{{ $admin->jabatan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-exclamation-circle fs-3 text-muted mb-2"></i>
                                    <p class="mb-0 text-muted">Tidak ada admin yang perlu disetujui.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.onclick = function() {
                let checkboxes = document.querySelectorAll('input[name="ids[]"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
            }
        }
    });
</script>
@endsection
