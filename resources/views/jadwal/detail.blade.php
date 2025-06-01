@extends('layouts.apk')

@section('content')
    <div class="container">
        {{-- HEADER HALAMAN --}}
        <div class="page-header">
            <h1 class="page-title">Penjadwalan</h1>
        </div>

        <h2 class="schedule-main-title">{{ $nama_jadwal }} - {{ $tahun }} - Versi {{ $version }}</h2>

        {{-- FILTER FORM --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="filter-bar">
                <input type="date" name="tanggal" class="form-control" value="{{ $selectedDate }}"
                    onchange="this.form.submit()">
                <select name="mata_lomba" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Mata Lomba</option>
                    @foreach ($allSubKategori as $mataLomba)
                        <option value="{{ $mataLomba->id }}" {{ $selectedSubKategori == $mataLomba->id ? 'selected' : '' }}>
                            {{ $mataLomba->nama_lomba }}
                        </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('jadwal.change', ['id' => $jadwalMaster->id]) }}" class="btn btn-primary">Change Jadwal</a>
        </div>

        @php
            // --- BLOK PHP INI TIDAK PERLU DIUBAH SAMA SEKALI ---
            $slotInterval = 15;
            $slotHeight = 40;
            $startHour = 7;
            $endHour = 18;
            $minMinute = $startHour * 60;
            $maxMinute = $endHour * 60;
            $filteredJadwals = $jadwals->filter(function ($j) use ($minMinute, $maxMinute) {
                $start = \Carbon\Carbon::parse($j->waktu_mulai);
                $end = \Carbon\Carbon::parse($j->waktu_selesai);
                $startMinutes = $start->hour * 60 + $start->minute;
                $endMinutes = $end->hour * 60 + $end->minute;
                return $startMinutes < $maxMinute && $endMinutes > $minMinute;
            });
            $events = [];
            foreach ($filteredJadwals as $j) {
                $start = \Carbon\Carbon::parse($j->waktu_mulai);
                $end = \Carbon\Carbon::parse($j->waktu_selesai);
                $startMinutes = $start->hour * 60 + $start->minute;
                $endMinutes = $end->hour * 60 + $end->minute;
                if ($endMinutes <= $startMinutes) {
                    $endMinutes = $startMinutes + $slotInterval;
                }
                $duration = $endMinutes - $startMinutes;
                $minDuration = 30; // 15 menit
                $durationHeight = ceil(($duration / $slotInterval) * $slotHeight);

                // Jika kurang dari 15 menit, gunakan tinggi minimum tetap
                $minHeight = 100; // Misalnya, minimal tinggi 50px agar tetap terlihat meski pendek
                $durationHeight = $duration < $minDuration ? $minHeight : $durationHeight;

                $events[] = [
                    'jadwal' => $j,
                    'start' => $startMinutes,
                    'end' => $endMinutes,
                    'duration' => $duration,
                    'top' => (($startMinutes - $minMinute) / $slotInterval) * $slotHeight,
                    'durationHeight' => $durationHeight,
                ];

            }
            function calculateInternalLayout($groupEvents)
            {
                if (empty($groupEvents))
                    return [];
                usort($groupEvents, fn($a, $b) => $a['start'] <=> $b['start']);
                $positionedEvents = [];
                $groups = [];
                foreach ($groupEvents as $event) {
                    $placed = false;
                    foreach ($groups as &$group) {
                        $groupEndTime = max(array_column($group, 'end'));
                        if ($event['start'] < $groupEndTime) {
                            $group[] = $event;
                            $placed = true;
                            break;
                        }
                    }
                    if (!$placed) {
                        $groups[] = [$event];
                    }
                }
                unset($group);
                foreach ($groups as $group) {
                    $lanes = [];
                    usort($group, fn($a, $b) => $a['start'] <=> $b['start']);
                    $groupEventsWithLanes = [];
                    foreach ($group as $event) {
                        $assignedLane = -1;
                        for ($i = 0; $i < count($lanes); $i++) {
                            if ($event['start'] >= $lanes[$i]) {
                                $lanes[$i] = $event['end'];
                                $assignedLane = $i;
                                break;
                            }
                        }
                        if ($assignedLane === -1) {
                            $lanes[] = $event['end'];
                            $assignedLane = count($lanes) - 1;
                        }
                        $event['lane'] = $assignedLane;
                        $groupEventsWithLanes[] = $event;
                    }
                    $totalLanesInGroup = count($lanes);
                    foreach ($groupEventsWithLanes as $event) {
                        $event['widthPercent'] = 100 / $totalLanesInGroup;
                        $event['leftPercent'] = $event['lane'] * $event['widthPercent'];
                        $positionedEvents[] = $event;
                    }
                }
                return $positionedEvents;
            }
            function calculateFinalPositions($events)
            {
                if (empty($events))
                    return [];
                $eventsByLomba = [];
                foreach ($events as $event) {
                    $lombaId = $event['jadwal']->mata_lomba_id;
                    if (!isset($eventsByLomba[$lombaId])) {
                        $eventsByLomba[$lombaId] = [];
                    }
                    $eventsByLomba[$lombaId][] = $event;
                }
                $totalLombaColumns = count($eventsByLomba);
                if ($totalLombaColumns == 0)
                    return [];
                $lombaColumnWidth = 100 / $totalLombaColumns;
                $finalEvents = [];
                $lombaColumnIndex = 0;
                foreach ($eventsByLomba as $lombaId => $lombaEvents) {
                    $lombaColumnLeftOffset = $lombaColumnIndex * $lombaColumnWidth;
                    $internalPositionedEvents = calculateInternalLayout($lombaEvents);
                    foreach ($internalPositionedEvents as $event) {
                        $event['final_width'] = ($event['widthPercent'] / 100) * $lombaColumnWidth;
                        $event['final_left'] = $lombaColumnLeftOffset + (($event['leftPercent'] / 100) * $lombaColumnWidth);
                        $finalEvents[] = $event;
                    }
                    $lombaColumnIndex++;
                }
                return $finalEvents;
            }
            $positionedEvents = calculateFinalPositions($events);
        @endphp

        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="schedule-wrapper">
                    <div class="time-column">
                        <div class="schedule-header-date">
                            <div class="day-name">
                                {{ strtoupper(\Carbon\Carbon::parse($selectedDate)->translatedFormat('D')) }}
                            </div>
                            <div class="date-number">{{ \Carbon\Carbon::parse($selectedDate)->format('d') }}</div>
                        </div>
                        <div class="time-slots-container">
                            @for ($minute = $minMinute; $minute < $maxMinute; $minute += 60)
                                <div class="time-slot" style="height: {{ $slotHeight * 4 }}px;">
                                    <span class="time-label">{{ sprintf('%02d:00', $minute / 60) }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="schedule-column">
                        <div class="schedule-header-content"></div>

                        {{-- PERUBAHAN KUNCI 1: Menambahkan div events-container --}}
                        <div class="events-container"
                            style="min-height: {{ (($maxMinute - $minMinute) / $slotInterval) * $slotHeight }}px;">
                            @foreach ($positionedEvents as $e)
                                <div class="event-card" style="
                                                                                     top: {{ $e['top'] }}px;
                                                                                     height: {{ $e['durationHeight'] - 2 }}px;
                                                                                     left: {{ $e['final_left'] }}%;
                                                                                     width: calc({{ $e['final_width'] }}% - 5px);
                                                                                 ">
                                    <div class="event-content">
                                        <p class="event-title">{{ $e['jadwal']->mataLomba->nama_lomba }}</p>
                                        <p class="event-subtitle">
                                            @php
                                                // Coba ambil nama tim jika ada
                                                $timNames = $e['jadwal']->tim->pluck('nama_tim')->all();
                                                // Coba ambil nama peserta jika ada
                                                $pesertaNames = $e['jadwal']->peserta->pluck('nama_peserta')->all();
                                            @endphp

                                            @if (!empty($timNames))
                                                {{ implode(', ', $timNames) }}
                                            @elseif (!empty($pesertaNames))
                                                {{ implode(', ', $pesertaNames) }}
                                            @else
                                                -
                                            @endif
                                        </p>
                                        <p class="event-time">
                                            {{ \Carbon\Carbon::parse($e['jadwal']->waktu_mulai)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($e['jadwal']->waktu_selesai)->format('H:i') }}
                                        </p>
                                        <p class="event-venue">{{ $e['jadwal']->venue->name ?? '-' }}</p>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    @php
                        $totalPages = 1;
                        $currentPage = 1;
                        $prevPage = null;
                        $nextPage = null;
                    @endphp
                    <div class="pagination-controls">
                        <span>Page {{ $currentPage }} of {{ $totalPages }}</span>
                        <div class="pagination-arrows">
                            <a href="{{ $prevPage ? '#' : '#' }}" class="{{ !$prevPage ? 'disabled' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-chevron-left" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                                </svg>
                            </a>
                            <a href="{{ $nextPage ? '#' : '#' }}" class="{{ !$nextPage ? 'disabled' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-chevron-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: sans-serif;
        }

        .container {
            max-width: 1200px;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 500;
            color: #333;
        }

        .schedule-main-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .filter-bar .form-control,
        .filter-bar .form-select {
            max-width: 220px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            height: 38px;
            font-size: 0.9rem;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
        }

        .card {
            border: none;
            border-radius: 8px;
            overflow: hidden;
        }

        .schedule-wrapper {
            display: flex;
            border: 1px solid #e9ecef;
        }

        .time-column,
        .schedule-column {
            display: flex;
            /* Menggunakan flexbox untuk memisahkan header dan konten */
            flex-direction: column;
        }

        .time-column {
            width: 90px;
            flex-shrink: 0;
            background: #fff;
            border-right: 1px solid #e9ecef;
            text-align: center;
        }

        .schedule-column {
            flex-grow: 1;
            background: #fff;
        }

        .time-slots-container {
            flex-grow: 1;
        }

        .time-slot {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 8px;
            box-sizing: border-box;
            font-size: 12px;
            color: #6c757d;
            position: relative;
        }

        .time-slot::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 15px;
            right: 15px;
            border-bottom: 1px dashed #e9ecef;
        }

        .time-slot:last-child::after {
            display: none;
        }

        .schedule-header-date,
        .schedule-header-content {
            height: 100px;
            flex-shrink: 0;
            /* Header tidak akan menyusut */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e9ecef;
            background: #fff;
            z-index: 10;
        }

        .day-name {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 600;
        }

        .date-number {
            font-size: 1.75rem;
            font-weight: 600;
            color: #343a40;
            line-height: 1.2;
        }

        /* PERUBAHAN KUNCI 2: Rule baru untuk container event */
        .events-container {
            position: relative;
            flex-grow: 1;
        }

        .event-card {
            position: absolute;
            background: #e3f2fd;
            border-radius: 4px;
            padding: 8px 12px;
            box-sizing: border-box;
            font-size: 0.85rem;
            overflow: hidden;
            color: #0d47a1;
            border-left: 3px solid #2196f3;
            transition: all 0.2s ease-in-out;
            z-index: 5;
        }

        .event-title {
            font-weight: 600;
            margin: 0 0 2px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .event-subtitle,
        .event-venue,
        .event-time {
            margin: 0;
            font-size: 0.75rem;
            color: #1c5d9e;
            white-space: nowrap;
        }

        .card-footer {
            background-color: #fff;
            border-top: 1px solid #e9ecef;
            padding: 0.75rem 1.5rem;
        }

        .pagination-controls {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .pagination-arrows {
            display: flex;
            margin-left: 1rem;
        }

        .pagination-arrows a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: 1px solid #dee2e6;
            color: #6c757d;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .pagination-arrows a:first-child {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }

        .pagination-arrows a:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
            border-left: none;
        }

        .pagination-arrows a:hover {
            background-color: #f8f9fa;
        }

        .pagination-arrows a.disabled {
            color: #adb5bd;
            pointer-events: none;
            background-color: #fff;
        }
    </style>

    <script>
        // Script tidak perlu diubah.
    </script>
@endsection