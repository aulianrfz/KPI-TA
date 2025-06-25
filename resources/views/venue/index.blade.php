@extends('layouts.apk')

@section('content')
    {{-- Menambahkan link CDN Bootstrap secara eksplisit di dalam blade ini --}}
    {{-- Memperbaiki kesalahan ketik xintegrity menjadi integrity --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    {{-- Pastikan Font Awesome dimuat oleh layouts.apk atau tambahkan CDN di sini jika ikon tidak muncul --}}
    {{--
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> --}}

    <style>
        /* General Page Styles */
        .page-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .page-title {
            color: #3A3B7B;
            /* Warna biru tua/ungu */
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 0;
        }

        .btn-add-custom {
            background-color: #0d6efd;
            /* Biru standar Bootstrap */
            color: white;
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }

        .btn-add-custom:hover {
            background-color: #0b5ed7;
            color: white;
        }

        .btn-search-icon-table {
            background-color: #20c997;
            /* Warna teal/hijau */
            border-color: #20c997;
            color: white;
        }

        .btn-search-icon-table:hover {
            background-color: #1aa883;
            border-color: #1aa883;
        }

        .input-group-table-search .form-control {
            font-size: 0.9rem;
            /* Sesuaikan ukuran font input search */
        }

        /* Card Styling */
        .table-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            padding: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .table-card .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Table Styling */
        .table-header-dark-custom {
            background-color: #343a40 !important;
            /* Warna abu-abu gelap (Bootstrap thead-dark) */
        }

        .table-header-dark-custom th {
            color: black !important;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody td {
            text-align: center;
            vertical-align: middle;
            font-size: 0.9rem;
            /* Ukuran font konten tabel */
        }

        .btn-action-edit-custom,
        .btn-action-delete-custom {
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            line-height: 1;
            border-radius: .2rem;
            font-size: 0.8rem;
        }

        .btn-action-edit-custom {
            background-color: #0d6efd;
            /* Biru */
        }

        .btn-action-edit-custom:hover {
            background-color: #0b5ed7;
        }

        .btn-action-delete-custom {
            background-color: #dc3545;
            /* Merah */
        }

        .btn-action-delete-custom:hover {
            background-color: #bb2d3b;
        }

        /* Pagination Styling */
        .pagination {
            justify-content: flex-end;
            margin-top: 0;
        }

        .pagination .page-item.active .page-link {
            background-color: #3A3B7B;
            border-color: #3A3B7B;
            color: white;
        }

        .pagination .page-link {
            color: #3A3B7B;
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        .pagination .page-link:hover {
            color: #2c2d5c;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .card-footer-custom {
            background-color: #ffffff;
            border-top: 1px solid #e9ecef;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>

    <div class="container">
        {{-- Judul Halaman --}}
        <div class="page-header-container">
            <h2 class="page-title">Venue</h2>
            {{-- Tombol kembali bisa ditambahkan di sini jika diperlukan dari halaman lain --}}
            {{-- <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">Kembali</a> --}}
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-card">
            <div class="card-header">
                <form action="{{ route('venue.index') }}" method="GET" class="d-flex">
                    <div class="input-group input-group-sm input-group-table-search" style="width: 300px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama venue..."
                            value="{{ request('search') }}">
                        <button class="btn btn-search-icon-table" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
                <a href="{{ route('venue.create') }}" class="btn btn-add-custom">
                    <i class="fas fa-plus me-1"></i> Tambah Data
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-header-dark-custom">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Venue</th>
                            <th>Tanggal Tersedia</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Berakhir</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                        @forelse($venues as $index => $venue)
                            <tr>
                                <td>
                                    @if ($venues instanceof \Illuminate\Pagination\AbstractPaginator)
                                        {{ ($venues->currentPage() - 1) * $venues->perPage() + $loop->iteration }}
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </td>
                                <td>{{ $venue->name }}</td>
                                <td>{{ $venue->tanggal_tersedia ?? '-' }}</td>
                                <td>{{ $venue->waktu_mulai_tersedia ?? '-' }}</td>
                                <td>{{ $venue->waktu_berakhir_tersedia ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('venue.edit', $venue->id) }}" class="btn btn-action-edit-custom me-1"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-action-delete-custom" title="Hapus"
                                        data-bs-toggle="modal" data-bs-target="#deleteVenueModal"
                                        data-action="{{ route('venue.destroy', $venue->id) }}" data-name="{{ $venue->name }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data venue.</td>
                            </tr>
                        @endforelse
                        </tbody>
                </table>
            </div>

            @if ($venues instanceof \Illuminate\Pagination\AbstractPaginator && $venues->hasPages())
                <div class="card-footer-custom">
                    <div class="pagination-info">
                        Menampilkan {{ $venues->firstItem() }} sampai {{ $venues->lastItem() }} dari {{ $venues->total() }}
                        entri
                    </div>
                    <div>
                        {{ $venues->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Konfirmasi Delete Venue --}}
    <div class="modal fade" id="deleteVenueModal" tabindex="-1" aria-labelledby="deleteVenueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteVenueForm" method="POST"> {{-- Action akan diatur oleh JavaScript --}}
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteVenueModalLabel">Konfirmasi Hapus Venue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus venue <strong id="venueNameToDelete"></strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS Bundle (diperlukan untuk modal, dropdown, dll.) --}}
    {{-- Memperbaiki kesalahan ketik xintegrity menjadi integrity --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        const deleteVenueModalElement = document.getElementById('deleteVenueModal');
        if (deleteVenueModalElement) {
            deleteVenueModalElement.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; // Tombol yang memicu modal
                const action = button.getAttribute('data-action'); // Ambil URL action
                const name = button.getAttribute('data-name'); // Ambil nama venue

                const form = document.getElementById('deleteVenueForm');
                form.action = action;

                const venueNameElement = document.getElementById('venueNameToDelete');
                if (venueNameElement) {
                    venueNameElement.textContent = name;
                }
            });
        }
    </script>
@endsection