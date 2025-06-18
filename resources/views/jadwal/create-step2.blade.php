@extends('layouts.apk')

@section('content')
<style>
    .stepper-wrapper {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    .stepper-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 25%; /* 4 steps */
        position: relative;
        z-index: 1;
    }
    .stepper-item-counter {
        height: 2.5rem;
        width: 2.5rem;
        background-color: #e0e0e0;
        color: #757575;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        margin-bottom: 0.5rem;
        border: 3px solid #e0e0e0;
    }
    .stepper-item-title {
        font-size: 0.875rem;
        color: #757575;
    }
    .stepper-item.active .stepper-item-counter,
    .stepper-item.completed .stepper-item-counter {
        background-color: #1976d2; /* Blue color for stepper */
        color: white;
        border-color: #1976d2;
    }
    .stepper-item.active .stepper-item-title,
    .stepper-item.completed .stepper-item-title {
        color: #1976d2;
    }

    .stepper-wrapper::before {
        content: "";
        position: absolute;
        top: 1.25rem;
        left: 12.5%;
        right: 12.5%;
        height: 2px;
        background-color: #e0e0e0;
        z-index: 0;
    }
     .progress-line {
        position: absolute;
        top: calc(1.25rem - 1px);
        left: 12.5%;
        height: 2px;
        background-color: #1976d2;
        z-index: 0;
    }

    .data-verification-card {
        background-color: #fff;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .data-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 0.5rem; /* Adjusted margin */
        background-color: #fff;
        cursor: pointer; /* Make row look clickable */
    }
    /* Remove bottom margin for the last visual row if a dropdown is directly under it */
    .data-item-row + .dropdown-content {
        margin-top: -0.5rem; /* Pull dropdown closer if row had margin-bottom */
    }
     .dropdown-content {
        padding: 0.5rem 1rem 1rem 1rem; /* Padding for the content inside dropdown */
        border: 1px solid #e0e0e0;
        border-top: none; /* Avoid double border with row above */
        border-radius: 0 0 8px 8px;
        margin-bottom: 1rem;
        background-color: #fcfcfc;
    }

    .data-item-label {
        font-weight: 500;
        color: #333;
    }
    .data-item-status {
        display: flex;
        align-items: center;
    }
    .data-item-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #1976d2;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 0.75rem;
        color: #1976d2;
        font-weight: bold;
    }
    .data-item-checkbox.missing {
        border-color: #d32f2f;
        color: #d32f2f;
    }
    .data-item-chevron {
        color: #1976d2;
        font-size: 1.5rem;
        text-decoration: none;
        transition: transform 0.2s ease-in-out;
    }
    .data-item-chevron.open {
        transform: rotate(90deg);
    }

    .btn-custom-primary {
        background-color: #009688; /* Teal color like in UI image */
        border-color: #009688;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 25px; /* More rounded like UI image */
        font-weight: 500;
        min-width: 120px; /* Minimum width for the button */
    }
    .btn-custom-primary:hover {
        background-color: #00796b;
        border-color: #00796b;
        color: white;
    }
    .btn-custom-primary:disabled {
        background-color: #a0a0a0;
        border-color: #a0a0a0;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 2rem;
    }
    .form-control-sm { /* Class for smaller form controls if needed */
        font-size: .875rem;
        padding: .25rem .5rem;
        height: calc(1.5em + .5rem + 2px);
    }
</style>

<div class="container py-4">
    <div class="data-verification-card">
        <h2 class="text-center mb-4">Buat Jadwal</h2>

        @php
            $currentStep = 2;
            $totalSteps = 4;
            $progressPercentage = (($currentStep - 1) / ($totalSteps -1)) * (100 - (100/$totalSteps));
        @endphp

        <div class="stepper-wrapper">
            <div class="progress-line" style="width: {{ $progressPercentage }}%;"></div>
            @for ($i = 1; $i <= $totalSteps; $i++)
                @php
                    $stepTitle = "";
                    switch ($i) {
                        case 1: $stepTitle = ""; break;
                        case 2: $stepTitle = ""; break;
                        case 3: $stepTitle = ""; break;
                        case 4: $stepTitle = ""; break;
                    }
                @endphp
                <div class="stepper-item {{ $currentStep >= $i ? 'completed' : '' }} {{ $currentStep === $i ? 'active' : '' }}">
                    <div class="stepper-item-counter">{{ $i }}</div>
                    <div class="stepper-item-title">{{ $stepTitle }}</div>
                </div>
            @endfor
        </div>

        <h3 class="mb-3" style="font-size: 1.25rem; font-weight: 500;">Data yang diperlukan</h3>

        <form action="{{ route('jadwal.create.step3') }}" method="POST">
            @csrf

            @php
                $isVenueAvailable = \App\Models\Venue::count() > 0;
                $isJuriAvailable = \App\Models\Juri::count() > 0;
                $isMataLombaAvailable = \App\Models\MataLomba::count() > 0;
                $isPesertaAvailable = \App\Models\Peserta::count() > 0;
                $allDataAvailable = $isVenueAvailable && $isJuriAvailable && $isMataLombaAvailable && $isPesertaAvailable;
            @endphp

            {{-- Data Peserta --}}
            <div class="data-item-row" data-target-dropdown="peserta-dropdown-content">
                <span class="data-item-label">Data Peserta</span>
                <div class="data-item-status">
                    <div class="data-item-checkbox {{ $isPesertaAvailable ? '' : 'missing' }}">
                        {!! $isPesertaAvailable ? '&#10003;' : '&#10007;' !!}
                    </div>
                    <span class="data-item-chevron">&rsaquo;</span>
                </div>
            </div>
            <div id="peserta-dropdown-content" class="dropdown-content" style="display: none;">
                @if($isPesertaAvailable)
                    <label for="peserta" class="form-label visually-hidden">Pilih Peserta</label> {{-- Visually hidden label for accessibility --}}
                    <select name="peserta" id="peserta" class="form-control">
                        @foreach(\App\Models\Peserta::all() as $peserta)
                            <option value="{{ $peserta->id }}">{{ $peserta->nama }} ({{ $peserta->nim }})</option>
                        @endforeach
                    </select>
                @else
                    <p class="text-muted mb-0">✘ Data peserta belum tersedia. Silakan tambahkan terlebih dahulu.</p>
                @endif
            </div>

            {{-- Data Lomba --}}
            <div class="data-item-row" data-target-dropdown="lomba-dropdown-content">
                <span class="data-item-label">Data Lomba</span>
                <div class="data-item-status">
                    <div class="data-item-checkbox {{ $isMataLombaAvailable ? '' : 'missing' }}">
                        {!! $isMataLombaAvailable ? '&#10003;' : '&#10007;' !!}
                    </div>
                    <span class="data-item-chevron">&rsaquo;</span>
                </div>
            </div>
            <div id="lomba-dropdown-content" class="dropdown-content" style="display: none;">
                @if($isMataLombaAvailable)
                    <label for="kategori_lomba" class="form-label visually-hidden">Pilih Kategori Lomba</label>
                    <select name="kategori_lomba" id="kategori_lomba" class="form-control">
                        @foreach(\App\Models\MataLomba::all() as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama_lomba }}</option>
                        @endforeach
                    </select>
                @else
                    <p class="text-muted mb-0">✘ Data kategori lomba belum tersedia. Silakan tambahkan terlebih dahulu.</p>
                @endif
            </div>

            {{-- Data Venue --}}
            <div class="data-item-row" data-target-dropdown="venue-dropdown-content">
                <span class="data-item-label">Data Venue</span>
                <div class="data-item-status">
                    <div class="data-item-checkbox {{ $isVenueAvailable ? '' : 'missing' }}">
                        {!! $isVenueAvailable ? '&#10003;' : '&#10007;' !!}
                    </div>
                    <span class="data-item-chevron">&rsaquo;</span>
                </div>
            </div>
            <div id="venue-dropdown-content" class="dropdown-content" style="display: none;">
                @if($isVenueAvailable)
                    <label for="venue" class="form-label visually-hidden">Pilih Venue</label>
                    <select name="venue" id="venue" class="form-control">
                        @foreach(\App\Models\Venue::all() as $venue)
                            <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                        @endforeach
                    </select>
                @else
                    <p class="text-muted mb-0">✘ Data venue belum tersedia. Silakan tambahkan terlebih dahulu.</p>
                @endif
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-custom-primary" {{ !$allDataAvailable ? 'disabled' : '' }}>
                    Lanjut ke Penjadwalan
                </button>
            </div>

            @if(!$allDataAvailable)
                <p class="text-danger mt-3 text-center">Lengkapi semua data sebelum melanjutkan ke penjadwalan.</p>
            @endif
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataItemRows = document.querySelectorAll('.data-item-row');

    dataItemRows.forEach(row => {
        row.addEventListener('click', function () {
            const targetDropdownId = this.getAttribute('data-target-dropdown');
            const targetDropdown = document.getElementById(targetDropdownId);
            const chevron = this.querySelector('.data-item-chevron');

            if (targetDropdown) {
                // Close all other open dropdowns
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    if (dropdown.id !== targetDropdownId && dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                        // Reset chevron for other rows
                        const otherRow = document.querySelector(`.data-item-row[data-target-dropdown="${dropdown.id}"]`);
                        if (otherRow) {
                            const otherChevron = otherRow.querySelector('.data-item-chevron');
                            if (otherChevron) {
                                otherChevron.classList.remove('open');
                                otherChevron.innerHTML = '&rsaquo;';
                            }
                        }
                    }
                });

                // Toggle the clicked dropdown
                const isCurrentlyOpen = targetDropdown.style.display === 'block';
                targetDropdown.style.display = isCurrentlyOpen ? 'none' : 'block';
                
                if (chevron) {
                    if (!isCurrentlyOpen) {
                        chevron.classList.add('open');
                        chevron.innerHTML = '&#9660;'; // Down arrow
                    } else {
                        chevron.classList.remove('open');
                        chevron.innerHTML = '&rsaquo;'; // Right arrow
                    }
                }
            }
        });
    });
});
</script>
@endsection
 