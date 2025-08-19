@extends('layouts.apk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color: #0367A6;">Kelola Footer Links</h4>
        <a href="{{ route('links.create') }}" class="btn btn-primary btn-sm">Tambah Link</a>
    </div>

    <div class="table-responsive">
        <table id="linksTable" class="table table-hover align-middle table-dark-header">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Label</th>
                    <th>Type</th>
                    <th>URL</th>
                    <th>Icon</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($links as $index => $link)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $link->label }}</td>
                        <td>{{ ucfirst($link->type) }}</td>
                        <td>
                            @php
                                $url = preg_match('/^https?:\/\//', $link->url) ? $link->url : 'https://' . $link->url;
                            @endphp
                            <a href="{{ $url }}" target="_blank">{{ $link->url }}</a>
                        </td>

                        <td>
                            @if(($link->type === 'social' || $link->type === 'logo') && $link->icon)
                                <img src="{{ asset($link->icon) }}" alt="{{ $link->label }}" style="width:24px;height:24px;">
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if($link->type === 'brand' || $link->type === 'logo')
                                <a href="{{ route('links.edit', $link->id) }}" class="btn btn-sm btn-warning">Edit</a>

                            @else
                                <a href="{{ route('links.edit', $link->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('links.destroy', $link->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin mau hapus link ini?')">Hapus</button>
                                </form>
                            @endif
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <!-- pastikan jQuery dulu -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script>
        $(document).ready(function () {
            $('#linksTable').DataTable();
        });
    </script>
@endpush