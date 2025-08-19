@extends('layouts.apk')

@section('title', 'Daftar Admin Aktif')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 fw-semibold text-primary">Daftar Admin</h3>
            <a href="{{ route('superadmin.admin.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Admin
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="adminTable">
                        <thead class="text-center table-header-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Jabatan</th>
                                <th>Status Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $index => $admin)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td  class="text-center">{{ $admin->user->first_name }} {{ $admin->user->last_name }}</td>
                                    <td  class="text-center">{{ $admin->user->username }}</td>
                                    <td  class="text-center">{{ $admin->user->email }}</td>
                                    <td  class="text-center">{{ $admin->jabatan }}</td>

                                    <td class="text-center">
                                        @if($admin->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('superadmin.admin.edit', $admin->id) }}"
                                            class="btn btn-sm btn-action-edit-custom me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-action-delete-custom" title="Delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-action="{{ route('superadmin.admin.destroy', $admin->id) }}"
                                            data-item-name="{{ $admin->user->first_name }} {{ $admin->user->last_name }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Delete --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus admin <strong id="itemNameModal">ini</strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#adminTable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "›",
                        previous: "‹"
                    },
                    zeroRecords: "Tidak ditemukan data yang sesuai",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });

            // delete modal
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const action = button.getAttribute('data-action');
                const itemName = button.getAttribute('data-item-name');

                document.getElementById('deleteForm').action = action;
                document.getElementById('itemNameModal').textContent = itemName;
            });
        });
    </script>

    <style>
        .btn-action-edit-custom {
            background-color: #0d6efd;
            color: white;
            border: 1px solid #0d6efd;
            padding: 0.2rem 0.4rem;
            line-height: 1;
            border-radius: .2rem;
        }

        .btn-action-edit-custom:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .btn-action-delete-custom {
            background-color: #dc3545;
            color: white;
            border: 1px solid #dc3545;
            padding: 0.2rem 0.4rem;
            line-height: 1;
            border-radius: .2rem;
        }

        .btn-action-delete-custom:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }

        th {
            text-align: center !important;
            vertical-align: middle !important;
            /* color: white !important; */
        }

        .table-header-light th {
            background-color: #ffffff;
            /* putih */
            color: #000000;
            /* hitam */
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
        }
    </style>
@endsection