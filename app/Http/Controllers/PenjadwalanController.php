<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriLomba;
use App\Models\Membayar;
use App\Models\Venue;
use App\Models\Tim;
use App\Models\Peserta;
use App\Models\Jadwal;
use App\Models\Pendaftar;
use App\Models\Agenda;
use App\Models\MataLomba;
use App\Models\Juri;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProsesPenjadwalanJob;



class PenjadwalanController extends Controller
{

    public function index($eventId = null)
    {
        $event = $eventId ? Event::find($eventId) : null;
        $query = Jadwal::with(['agendas']);

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        if (request()->has('tahun')) {
            $query->where('tahun', request('tahun'));
        }

        if (request('sort') === 'status') {
            $query->orderByRaw("FIELD(status, 'Selesai', 'Menunggu', 'Gagal')");
        }

        $jadwals = $query->orderBy('created_at', 'asc')->get();
        $availableYears = Jadwal::select('tahun')->distinct()->pluck('tahun');

        return view('jadwal.index', compact('jadwals', 'availableYears', 'event'));
    }


    public function event()
    {
        $events = Event::all();
        // dd($events);
        return view('jadwal.event', compact('events'));
    }


    public function checkStatus()
    {
        $isWaiting = Jadwal::where('status', 'Menunggu')->exists();
        return response()->json(['waiting' => $isWaiting]);
    }

    public function change($id)
    {
        $jadwalMaster = Jadwal::findOrFail($id);
        $sortBy = request()->query('sort_by');
        $tanggalFilter = request()->query('tanggal');

        $agendasQuery = Agenda::select('agenda.*')
            ->leftJoin('mata_lomba', 'agenda.mata_lomba_id', '=', 'mata_lomba.id')
            ->leftJoin('venue', 'agenda.venue_id', '=', 'venue.id')
            ->where('jadwal_id', $id)
            ->with(['mataLomba', 'venue', 'peserta', 'juri', 'tim']);

        if ($tanggalFilter) {
            $agendasQuery->whereDate('agenda.tanggal', $tanggalFilter);
        }

        // Sorting
        switch ($sortBy) {
            case 'kategori':
                $agendasQuery->orderBy('mata_lomba.nama_lomba');
                break;
            case 'venue':
                $agendasQuery->orderBy('venue.name');
                break;
            default:
                $agendasQuery->orderBy('tanggal')->orderBy('venue.name')->orderBy('waktu_mulai');
                break;
        }

        // Filter tanggal berdasarkan dropdown
        $availableDates = Agenda::where('jadwal_id', $id)
            ->selectRaw('DATE(tanggal) as tanggal')
            ->distinct()
            ->orderBy('tanggal')
            ->pluck('tanggal');

        $searchQuery = request()->query('search_query');

        // Filter pencarian umum
        if ($searchQuery) {
            $agendasQuery->where(function ($query) use ($searchQuery) {
                $query->whereHas('mataLomba', function ($q) use ($searchQuery) {
                    $q->where('nama_lomba', 'like', '%' . $searchQuery . '%');
                })->orWhereHas('venue', function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%' . $searchQuery . '%');
                })->orWhereHas('peserta', function ($q) use ($searchQuery) {
                    $q->where('nama_peserta', 'like', '%' . $searchQuery . '%');
                })->orWhereHas('tim', function ($q) use ($searchQuery) {
                    $q->where('nama_tim', 'like', '%' . $searchQuery . '%');
                })->orWhere('kegiatan', 'like', '%' . $searchQuery . '%');
            });
        }

        $agendas = $agendasQuery->get();

        return view('jadwal.change', [
            'jadwalMaster' => $jadwalMaster,
            'jadwals' => $agendas,
            'availableDates' => $availableDates,
            'nama_jadwal' => $jadwalMaster->nama_jadwal,
            'tahun' => $jadwalMaster->tahun,
            'version' => $jadwalMaster->version,
        ]);
    }


    public function detail(Request $request, $id)
    {
        $jadwalMaster = Jadwal::findOrFail($id);

        $allDates = Agenda::where('jadwal_id', $id)->distinct()->pluck('tanggal');
        $allSubKategori = Agenda::where('jadwal_id', $id)
            ->whereHas('mataLomba') // hanya agenda yang punya relasi mataLomba
            ->with('mataLomba')
            ->get()
            ->pluck('mataLomba')
            ->unique('id')
            ->values();

        // Filter tanggal dan mata lomba dari request
        $tanggalFilter = $request->input('tanggal', $allDates->first());
        $mataLombaId = $request->input('mata_lomba');
        $jadwalTanpaMataLomba = collect();

        $query = Agenda::with(['mataLomba', 'venue', 'juri', 'peserta', 'tim'])
            ->where('jadwal_id', $id)
            ->whereDate('tanggal', $tanggalFilter);

        if ($mataLombaId) {
            $query->where('mata_lomba_id', $mataLombaId);
        }

        // Tanpa filter
        $filteredJadwals = Agenda::with(['mataLomba', 'venue', 'juri', 'peserta', 'tim'])
            ->where('jadwal_id', $id)
            ->whereHas('mataLomba')
            ->whereDate('tanggal', $tanggalFilter)
            ->get(); // ✅ tambahkan ini

        // Jika ada filter mata lomba
        if ($mataLombaId) {
            // hanya ambil yang punya mata_lomba_id
            $filteredJadwals = Agenda::with(['mataLomba', 'venue', 'juri', 'peserta', 'tim'])
                ->where('jadwal_id', $id)
                ->whereDate('tanggal', $tanggalFilter)
                ->whereNotNull('mata_lomba_id') // pastikan punya mata lomba
                ->where('mata_lomba_id', $mataLombaId)
                ->get();
        } else {
            // jika tidak memilih mata lomba apapun (semua)
            $jadwalDenganMataLomba = Agenda::with(['mataLomba', 'venue', 'juri', 'peserta', 'tim'])
                ->where('jadwal_id', $id)
                ->whereDate('tanggal', $tanggalFilter)
                ->whereNotNull('mata_lomba_id')
                ->get()
                ->groupBy('mata_lomba_id')
                ->map(function ($group) {
                    $firstAgenda = $group->first();
                    $startTime = $group->min('waktu_mulai');
                    $endTime = $group->max('waktu_selesai');

                    $summaryAgenda = clone $firstAgenda;
                    $summaryAgenda->waktu_mulai = $startTime;
                    $summaryAgenda->waktu_selesai = $endTime;

                    $summaryAgenda->setRelation('peserta', collect());
                    $summaryAgenda->setRelation('tim', collect());

                    return $summaryAgenda;
                });


            $jadwalTanpaMataLomba = Agenda::with(['mataLomba', 'venue', 'juri', 'peserta', 'tim'])
                ->where('jadwal_id', $id)
                ->whereDate('tanggal', $tanggalFilter)
                ->whereNull('mata_lomba_id')
                ->get();

            $filteredJadwals = $jadwalDenganMataLomba->values()->merge($jadwalTanpaMataLomba);
        }

        // Slot konfigurasi untuk tampilan grid waktu
        $slotHeight = 40;
        $startHour = 7;
        $endHour = 17;
        $timeSlots = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
            $timeSlots[] = sprintf('%02d:30', $hour);
        }

        // Konversi waktu ke slot (untuk grid visualisasi)
        $filteredJadwals = $filteredJadwals->map(function ($item) use ($startHour) {
            $start = \Carbon\Carbon::parse($item->start_time);
            $end = \Carbon\Carbon::parse($item->end_time);
            $item->start_slot = ($start->hour - $startHour) * 2 + ($start->minute >= 30 ? 1 : 0);
            $item->duration_slot = max(1, $end->diffInMinutes($start) / 30); // Durasi minimal 1 slot
            return $item;
        });

        return view('jadwal.detail', [
            'jadwalMaster' => $jadwalMaster,
            'jadwals' => $filteredJadwals,
            'nama_jadwal' => $jadwalMaster->nama_jadwal,
            'tahun' => $jadwalMaster->tahun,
            'version' => $jadwalMaster->version,
            'allDates' => $allDates,
            'allSubKategori' => $allSubKategori,
            'selectedDate' => $tanggalFilter,
            'selectedSubKategori' => $mataLombaId,
            'timeSlots' => $timeSlots,
            'slotHeight' => $slotHeight,
            'jadwalTanpaMataLomba' => $jadwalTanpaMataLomba
        ]);
    }

    public function create()
    {
        return view('jadwal.create'); // buat view ini
    }

    public function edit($agenda_id)
    {
        $agenda = Agenda::findOrFail($agenda_id);
        $jadwal = Jadwal::find($agenda->jadwal_id);

        $tanggal_unik = [];
        if ($jadwal) {
            $tanggal_unik = Agenda::where('jadwal_id', $jadwal->id)
                ->select(DB::raw('DISTINCT tanggal'))
                ->orderBy('tanggal')
                ->pluck('tanggal');
        }

        // Ambil event_id dari jadwal
        $eventId = $jadwal->event_id ?? null;

        // Ambil hanya mata lomba untuk event ini
        $mata_lomba = MataLomba::whereHas('kategori', function ($q) use ($eventId) {
            $q->where('event_id', $eventId);
        })->get();

        // Peserta yang sudah membayar
        $peserta_ids = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id')->toArray();

        // Filter peserta berdasarkan event (lewat mataLomba -> kategori -> event)
        $peserta = Peserta::with('pendaftar.mataLomba.kategori')
            ->whereIn('id', $peserta_ids)
            ->whereHas('pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->orderBy('nama_peserta')
            ->get();

        // Filter tim yang ada peserta membayar dan terkait event ini
        $tim = Tim::with(['peserta.pendaftar.mataLomba.kategori'])
            ->whereHas('peserta', function ($q) use ($peserta_ids) {
                $q->whereIn('peserta.id', $peserta_ids);
            })
            ->whereHas('peserta.pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->get();

        $venue = Venue::all();
        $juri = Juri::all();

        $peserta_terpilih = $agenda->peserta()->pluck('peserta.id')->toArray();
        $tim_terpilih = $agenda->tim()->pluck('tim.id')->toArray();

        return view('jadwal.edit', compact(
            'agenda',
            'mata_lomba',
            'venue',
            'peserta',
            'juri',
            'tim',
            'tanggal_unik',
            'peserta_terpilih',
            'tim_terpilih',
            'jadwal'
        ));
    }


    public function update(Request $request, $agenda_id)
    {
        try {
            $agenda = Agenda::findOrFail($agenda_id);
            // \Log::info('Agenda ditemukan', ['agenda' => $agenda->toArray()]);

            if ($request->filled('waktu_mulai')) {
                $request->merge(['waktu_mulai' => date('H:i', strtotime($request->waktu_mulai))]);
            }
            if ($request->filled('waktu_selesai')) {
                $request->merge(['waktu_selesai' => date('H:i', strtotime($request->waktu_selesai))]);
            }

            // Validasi request
            $request->validate([
                'mata_lomba_id' => 'nullable|sometimes|exists:mata_lomba,id',
                'tanggal_dropdown' => 'nullable|string',
                'tanggal' => 'nullable|date',
                'waktu_mulai' => 'required|date_format:H:i',
                'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
                'kegiatan' => 'nullable|string',
                'venue_id' => 'nullable|exists:venue,id',
                'peserta_id' => 'nullable|array',
                'peserta_id.*' => 'nullable|exists:peserta,id',
                'tim_id' => 'nullable|array',
                'tim_id.*' => 'nullable|exists:tim,id',
                'juri_id' => 'nullable|exists:juri,id',
                'force' => 'boolean',
            ]);

            // \Log::info('Validasi request berhasil');

            $tanggal = $request->tanggal_dropdown === 'lainnya'
                ? $request->tanggal
                : ($request->tanggal_dropdown ?: $agenda->tanggal);

            if (!$tanggal) {
                Log::warning('Tanggal tidak valid', ['input' => $request->all()]);
                return back()->withInput()->with('error_force', 'Tanggal tidak valid atau kosong.');
            }

            $waktuMulai = $request->waktu_mulai;
            $waktuSelesai = $request->waktu_selesai;
            $venueId = $request->venue_id;
            $juriId = $request->juri_id;

            $pesertaLangsung = array_filter($request->peserta_id ?? [], fn($id) => !empty($id));
            $pesertaTim = [];
            if ($request->tim_id) {
                $pesertaTim = \App\Models\Bergabung::whereIn('tim_id', $request->tim_id)
                    ->pluck('peserta_id')
                    ->unique()
                    ->toArray();
            }
            $allPesertaId = array_unique(array_merge($pesertaLangsung, $pesertaTim));

            $force = $request->boolean('force');

            // Fungsi cek waktu bentrok
            $cekWaktu = function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                    $q->where('waktu_mulai', '<', $waktuSelesai)
                        ->where('waktu_selesai', '>', $waktuMulai);
                });
            };

            // Jika waktu & venue tidak berubah atau di dalam rentang sebelumnya, lewati pengecekan bentrok
            $tidakPerluCekBentrok = (
                $venueId == $agenda->venue_id &&
                $tanggal == $agenda->tanggal &&
                strtotime($waktuMulai) >= strtotime($agenda->waktu_mulai) &&
                strtotime($waktuSelesai) <= strtotime($agenda->waktu_selesai)
            );

            // Cek bentrok venue
            $bentrokVenueList = !$tidakPerluCekBentrok && $venueId ? Agenda::with('mataLomba', 'venue')
                ->where('jadwal_id', $agenda->jadwal_id)
                ->where('venue_id', $venueId)
                ->whereDate('tanggal', $tanggal)
                ->where('id', '!=', $agenda->id)
                ->where($cekWaktu)
                ->get() : collect();


            // Cek bentrok peserta
            $bentrokPesertaList = !empty($allPesertaId) ? Agenda::with('mataLomba', 'peserta')
                ->where('jadwal_id', $agenda->jadwal_id)
                ->whereDate('tanggal', $tanggal)
                ->where('id', '!=', $agenda->id)
                ->where($cekWaktu)
                ->whereHas('peserta', function ($query) use ($allPesertaId) {
                    $query->whereIn('peserta.id', $allPesertaId);
                })
                ->get() : collect();

            // dd($pesertaTim);

            // Cek bentrok tim
            $bentrokTimList = !empty($pesertaTim) ? Agenda::with('mataLomba', 'tim')
                ->where('jadwal_id', $agenda->jadwal_id)
                ->whereDate('tanggal', $tanggal)
                ->where('id', '!=', $agenda->id)
                ->where($cekWaktu)
                ->whereHas('tim.peserta', function ($query) use ($pesertaTim) {
                    $query->whereIn('peserta.id', $pesertaTim);
                })
                ->get() : collect();

            // Cek bentrok juri
            $bentrokJuriList = $juriId ? Agenda::with('mataLomba')
                ->where('jadwal_id', $agenda->jadwal_id)
                ->where('juri_id', $juriId)
                ->whereDate('tanggal', $tanggal)
                ->where('id', '!=', $agenda->id)
                ->where($cekWaktu)
                ->get() : collect();


            if (
                !$force && (
                    $bentrokVenueList->isNotEmpty() ||
                    $bentrokPesertaList->isNotEmpty() ||
                    $bentrokJuriList->isNotEmpty() ||
                    $bentrokTimList->isNotEmpty()
                )
            ) {

                $detail = [];
                // venue
                foreach ($bentrokVenueList as $b) {
                    $namaLomba = $b->mataLomba->nama_lomba ?? '(Tanpa Mata Lomba)';
                    $venue = $b->venue->name ?? '(Tanpa Venue)';
                    $detail[] = "Lomba {$namaLomba} pada {$b->waktu_mulai}-{$b->waktu_selesai} di {$venue}";
                }
                // peserta
                foreach ($bentrokPesertaList as $b) {
                    $peserta = $b->peserta->pluck('nama_peserta')->implode(', ');
                    $detail[] = "Peserta {$peserta} pada lomba {$b->mataLomba->nama_lomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
                }
                // juri
                foreach ($bentrokJuriList as $b) {
                    $detail[] = "Juri pada lomba {$b->mataLomba->nama_lomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
                }

                foreach ($bentrokTimList as $b) {
                    $pesertaBentrok = $b->tim->flatMap(function ($tim) {
                        return $tim->peserta->pluck('id');
                    })->unique()->toArray();

                    $pesertaYangBentrok = array_intersect($pesertaTim, $pesertaBentrok);

                    $namaTimBentrok = $b->tim->filter(function ($tim) use ($pesertaYangBentrok) {
                        $pesertaTimIni = $tim->peserta->pluck('id')->toArray();
                        return count(array_intersect($pesertaTimIni, $pesertaYangBentrok)) > 0;
                    })->pluck('nama_tim')->implode(', ');

                    if ($namaTimBentrok) {
                        $detail[] = "{$namaTimBentrok} pada lomba {$b->mataLomba->nama_lomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
                    }
                }

                $pesan = "Terjadi bentrok:\n\n" . implode("\n", $detail);

                return back()->withInput()->with('error_force', $pesan);
            }

            // dd($request->all());

            // Simpan
            $agenda->mata_lomba_id = $request->mata_lomba_id;
            $agenda->tanggal = $tanggal;
            $agenda->waktu_mulai = $waktuMulai;
            $agenda->waktu_selesai = $waktuSelesai;
            $agenda->kegiatan = $request->kegiatan;
            $agenda->venue_id = $venueId;
            $agenda->juri_id = $juriId;
            $agenda->save();

            $timId = array_filter($request->tim_id ?? [], fn($id) => !empty($id));
            $agenda->tim()->sync($timId);


            $agenda->peserta()->sync($pesertaLangsung);


            // Jika ada bentrok dan menekan "Lanjutkan", maka geser agenda bawahnya
            if ($force && ($bentrokVenueList->isNotEmpty())) {
                $agendaSemua = Agenda::where('jadwal_id', $agenda->jadwal_id)
                    ->where('venue_id', $venueId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('id', '!=', $agenda->id)
                    ->orderBy('waktu_mulai')
                    ->get();

                $indexBentrokPertama = $agendaSemua->search(function ($a) use ($waktuMulai, $waktuSelesai) {
                    $start1 = strtotime($a->waktu_mulai);
                    $end1 = strtotime($a->waktu_selesai);
                    $start2 = strtotime($waktuMulai);
                    $end2 = strtotime($waktuSelesai);
                    return !($end1 <= $start2 || $start1 >= $end2); // true kalau bentrok
                });

                if ($indexBentrokPertama !== false) {
                    $agendaTerdampak = $agendaSemua->slice($indexBentrokPertama)->values();

                    $waktuSelesaiBaru = Carbon::createFromFormat('H:i', $waktuSelesai);

                    foreach ($agendaTerdampak as $agendaItem) {
                        $waktuMulaiLama = Carbon::createFromFormat('H:i:s', $agendaItem->waktu_mulai);
                        $waktuSelesaiLama = Carbon::createFromFormat('H:i:s', $agendaItem->waktu_selesai);
                        $durasiAgenda = $waktuMulaiLama->diffInMinutes($waktuSelesaiLama);

                        $waktuMulaiBaru = $waktuSelesaiBaru->copy();
                        $waktuSelesaiBaru = $waktuMulaiBaru->copy()->addMinutes($durasiAgenda);

                        Log::info('Geser agenda ID ' . $agendaItem->id, [
                            'waktu_mulai_lama' => $waktuMulaiLama->format('H:i:s'),
                            'waktu_selesai_lama' => $waktuSelesaiLama->format('H:i:s'),
                            'waktu_mulai_baru' => $waktuMulaiBaru->format('H:i'),
                            'waktu_selesai_baru' => $waktuSelesaiBaru->format('H:i'),
                        ]);

                        $agendaItem->update([
                            'waktu_mulai' => $waktuMulaiBaru->format('H:i'),
                            'waktu_selesai' => $waktuSelesaiBaru->format('H:i'),
                        ]);
                    }
                }
            }

            $jadwal = $agenda->jadwal;

            return redirect()->route('jadwal.change', ['id' => $jadwal->id])->with('success', 'Agenda berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat update agenda', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error_force', 'Terjadi kesalahan saat update agenda: ' . $e->getMessage());
        }
    }

    public function createStep2(Request $request)
    {
        $messages = [
            'nama_jadwal.unique' => 'Nama jadwal sudah digunakan, silakan gunakan nama lain.',
        ];

        $validated = $request->validate([
            'nama_jadwal' => 'required|string|max:255|unique:jadwal,nama_jadwal',
            'tanggal' => 'required|array',
            'waktu_mulai' => 'required|array',
            'waktu_selesai' => 'required|array',
            'waktu_mulai.*' => 'required|date_format:H:i',
            'waktu_selesai.*' => 'required|date_format:H:i',
        ], $messages);

        $eventId = session('jadwal_event_id');

        // Validasi bahwa waktu_mulai < waktu_selesai untuk setiap tanggal
        foreach ($request->tanggal as $i => $tanggal) {
            if ($request->waktu_mulai[$i] >= $request->waktu_selesai[$i]) {
                return back()->withErrors([
                    "Waktu selesai harus lebih besar dari waktu mulai pada tanggal $tanggal"
                ])->withInput();
            }
        }

        $jadwal_per_tanggal = [];
        foreach ($request->tanggal as $i => $tanggal) {
            $jadwal_per_tanggal[] = [
                'tanggal' => $tanggal,
                'waktu_mulai' => $request->waktu_mulai[$i],
                'waktu_selesai' => $request->waktu_selesai[$i],
            ];
        }

        $pesertaPerKategori = $this->processPesertaKategoriLomba();

        // dd($pesertaPerKategori);
        $mataLombaMap = MataLomba::with('kategori')
            ->whereHas('kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->get()
            ->keyBy('nama_lomba');

        $totalDurasiMenit = 0;
        $lombaSerentakYangSudahDihitung = [];
        $venueDurasiMap = [];
        $debugList = [];

        foreach ($pesertaPerKategori as $lomba) {
            $namaLomba = $lomba['kategori_lomba'];

            $mataLomba = $mataLombaMap[$namaLomba] ?? null;

            if (!$mataLomba) {
                $debugList[] = [
                    'kategori_lomba' => $namaLomba,
                    'durasi_per_lomba' => 0,
                    'note' => 'Lomba tidak ditemukan'
                ];
                continue;
            }

            $durasi = $mataLomba->durasi ?? 0;
            $venueId = $mataLomba->venue_id;

            if ($mataLomba->is_serentak) {
                if (!in_array($namaLomba, $lombaSerentakYangSudahDihitung)) {
                    $lombaSerentakYangSudahDihitung[] = $namaLomba;

                    // Serentak
                    $venueDurasiMap["serentak"] = ($venueDurasiMap["serentak"] ?? 0) + $durasi;

                    $debugList[] = [
                        'kategori_lomba' => $namaLomba,
                        'venue_id' => $venueId,
                        'durasi_per_lomba' => $durasi,
                        'note' => 'Serentak - dihitung sekali'
                    ];
                } else {
                    $debugList[] = [
                        'kategori_lomba' => $namaLomba,
                        'venue_id' => $venueId,
                        'durasi_per_lomba' => 0,
                        'note' => 'Serentak - sudah dihitung sebelumnya'
                    ];
                }
            } else {
                // Non-serentak
                if (!isset($venueDurasiMap[$venueId])) {
                    $venueDurasiMap[$venueId] = 0;
                }

                $venueDurasiMap[$venueId] += $durasi;

                $debugList[] = [
                    'kategori_lomba' => $namaLomba,
                    'venue_id' => $venueId,
                    'durasi_per_lomba' => $durasi,
                    'note' => 'Non-serentak - ditambahkan ke venue'
                ];
            }
        }

        $totalDurasiMenit = empty($venueDurasiMap) ? 0 : max($venueDurasiMap);

        // Buffer waktu
        $bufferMenit = max((int) round($totalDurasiMenit * 0.3), 180);
        $totalDurasiMenit += $bufferMenit;

        $debugList[] = [
            'note' => 'Menambahkan buffer waktu',
            'durasi_awal_menit' => $totalDurasiMenit - $bufferMenit,
            'buffer_menit' => $bufferMenit,
            'total_durasi_setelah_buffer' => $totalDurasiMenit,
        ];


        // dd([
        //     'debug' => $debugList,
        //     'total_durasi_menit' => $totalDurasiMenit
        // ]);

        $totalWaktuTersedia = 0;
        foreach ($request->tanggal as $i => $tanggal) {
            $mulai = Carbon::createFromFormat('H:i', $request->waktu_mulai[$i]);
            $selesai = Carbon::createFromFormat('H:i', $request->waktu_selesai[$i]);

            if ($selesai->lessThanOrEqualTo($mulai)) {
                return back()->withErrors(["Waktu selesai harus lebih besar dari waktu mulai pada tanggal $tanggal"])->withInput();
            }

            $durasiHariIni = $mulai->diffInMinutes($selesai);
            $totalWaktuTersedia += $durasiHariIni;
        }

        if ($totalDurasiMenit > $totalWaktuTersedia) {
            $butuhHari = floor($totalDurasiMenit / 1440);
            $sisaButuhMenit = $totalDurasiMenit % 1440;
            $butuhJam = floor($sisaButuhMenit / 60);
            $butuhMenit = $sisaButuhMenit % 60;

            $tersediaHari = floor($totalWaktuTersedia / 1440);
            $sisaTersediaMenit = $totalWaktuTersedia % 1440;
            $tersediaJam = floor($sisaTersediaMenit / 60);
            $tersediaMenit = $sisaTersediaMenit % 60;

            $butuhStr = ($butuhHari > 0 ? "$butuhHari hari " : '') .
                ($butuhJam > 0 ? "$butuhJam jam " : '') .
                "$butuhMenit menit";

            $tersediaStr = ($tersediaHari > 0 ? "$tersediaHari hari " : '') .
                ($tersediaJam > 0 ? "$tersediaJam jam " : '') .
                "$tersediaMenit menit";

            return back()->withErrors([
                "Total waktu tidak cukup. Butuh minimal $butuhStr, sementara hanya tersedia $tersediaStr."
            ])->withInput();
        }

        session([
            'jadwal_nama' => $request->nama_jadwal,
            'jadwal_harian' => $jadwal_per_tanggal,
        ]);

        return view('jadwal.create-step2', [
            'jadwal_nama' => $request->nama_jadwal,
            'jadwal_harian' => $jadwal_per_tanggal,
            'event_id' => $eventId,
        ]);
    }

    public function formatMenitToHariJam($menit)
    {
        $hari = floor($menit / 1440);
        $sisaMenit = $menit % 1440;

        $jam = floor($sisaMenit / 60);
        $menitAkhir = $sisaMenit % 60;

        $result = '';
        if ($hari > 0) {
            $result .= "$hari hari ";
        }
        if ($jam > 0) {
            $result .= "$jam jam ";
        }
        $result .= "$menitAkhir menit";

        return trim($result);
    }


    public function prosesJadwal(Request $request)
    {
        Log::info("Memanggil prosesJadwal");

        $constraintTambahan = session('constraint_lomba', []);
        $jadwalHarian = session('jadwal_harian', []);
        $startTime = session('jadwal_waktu_mulai', '08:00');
        $endTime = session('jadwal_waktu_selesai', '17:00');
        $eventId = session('jadwal_event_id');
        // dd($eventId);
        $variabelX = $this->processPesertaKategoriLomba();
        // dd($variabelX);
        $pesertaKategori = $variabelX;
        $namaJadwal = session('jadwal_nama', 'Jadwal Otomatis');
        $tahun = now()->year;

        // Buat entri awal jadwal
        $jadwalAwal = Jadwal::create([
            'nama_jadwal' => $namaJadwal,
            'tahun' => $tahun,
            'version' => '1',
            'status' => 'Menunggu',
            'event_id' => $eventId,
        ]);

        ProsesPenjadwalanJob::dispatch(
            $startTime,
            $endTime,
            $variabelX,
            $pesertaKategori,
            $constraintTambahan,
            $jadwalHarian,
            $namaJadwal,
            $jadwalAwal->id,
            1,
            $eventId,
        );

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'Penjadwalan sedang diproses di background.',
        // ]);

        session()->forget([
            'jadwal_nama',
            'jadwal_waktu_mulai',
            'jadwal_waktu_selesai',
            'jadwal_harian',
            'constraint_lomba',
            'jadwal_event_id',
        ]);

        return view('jadwal.proses', [
            'namaJadwal' => $namaJadwal,
            'eventId' => $eventId,
        ]);
    }

    private function saveAgenda($jadwalMaster, $jadwalValid)
    {
        foreach ($jadwalValid as $jadwal) {
            $mataLomba = MataLomba::where('nama_lomba', $jadwal['kategori_lomba'])->first();
            if (!$mataLomba) {
                Log::warning("MataLomba tidak ditemukan untuk kategori lomba: {$jadwal['kategori_lomba']}");
                continue;
            }

            $isSerentak = $mataLomba->is_serentak;

            // Kalau serentak, hanya buat 1 agenda untuk semua peserta
            if ($isSerentak) {
                // Buat agenda sekali saja untuk kategori ini (asumsi sudah di-loop di luar per kategori)
                $agenda = Agenda::create([
                    'jadwal_id' => $jadwalMaster->id,
                    'mata_lomba_id' => $mataLomba->id,
                    'waktu_mulai' => $jadwal['waktu_mulai'],
                    'waktu_selesai' => $jadwal['waktu_selesai'],
                    'tanggal' => $jadwal['tanggal'],
                    'venue_id' => $jadwal['venue'],
                    'peserta_id' => null,
                    'tim_id' => null,
                ]);

                // Attach semua peserta ke agenda via pivot
                if (!empty($jadwal['peserta'])) {
                    $pesertaIds = Peserta::whereIn('nim', $jadwal['peserta'])->pluck('id')->toArray();
                    $agenda->peserta()->sync($pesertaIds);
                }
            } else {
                // Non-serentak
                if (count($jadwal['peserta']) === 1) {
                    // Individu
                    $peserta = Peserta::where('nim', $jadwal['peserta'][0])->first();
                    if (!$peserta) {
                        Log::warning("Peserta dengan NIM {$jadwal['peserta'][0]} tidak ditemukan");
                        continue;
                    }

                    $agenda = Agenda::create([
                        'jadwal_id' => $jadwalMaster->id,
                        'mata_lomba_id' => $mataLomba->id,
                        'waktu_mulai' => $jadwal['waktu_mulai'],
                        'waktu_selesai' => $jadwal['waktu_selesai'],
                        'tanggal' => $jadwal['tanggal'],
                        'venue_id' => $jadwal['venue'],
                        'peserta_id' => null,
                        'tim_id' => null,
                    ]);

                    $agenda->peserta()->attach($peserta->id);
                } else {
                    // Tim (lebih dari 1 peserta)
                    $tim = Tim::whereHas('peserta', function ($query) use ($jadwal) {
                        $query->whereIn('nim', $jadwal['peserta']);
                    }, '=', count($jadwal['peserta']))->first();

                    if (!$tim) {
                        Log::warning("Tim tidak ditemukan untuk peserta: " . implode(',', $jadwal['peserta']));
                        continue;
                    }

                    $agenda = Agenda::create([
                        'jadwal_id' => $jadwalMaster->id,
                        'mata_lomba_id' => $mataLomba->id,
                        'waktu_mulai' => $jadwal['waktu_mulai'],
                        'waktu_selesai' => $jadwal['waktu_selesai'],
                        'tanggal' => $jadwal['tanggal'],
                        'venue_id' => $jadwal['venue'],
                        'peserta_id' => null,
                        'tim_id' => null,
                    ]);

                    $agenda->tim()->attach($tim->id);
                }
            }
        }
    }


    public function createStep3(Request $request)
    {
        $eventId = session('jadwal_event_id');
        if (!$eventId) {
            return redirect()->route('jadwal.create.step1')
                ->withErrors(['Event belum dipilih. Silakan pilih event terlebih dahulu.']);
        }

        // dd($request->all())

        // validasi input form sebelumnya
        if ($request->has(['venue', 'kategori_lomba', 'peserta'])) {
            $validated = $request->validate([
                'venue' => 'required|exists:venue,id',
                'kategori_lomba' => 'required|exists:mata_lomba,id',
                'peserta' => 'required|exists:peserta,id',
            ]);
        } else {
            $validated = [
                'venue' => old('venue'),
                'kategori_lomba' => old('kategori_lomba'),
                'peserta' => old('peserta'),
            ];
        }

        $fieldErrors = session('field_errors', []);

        // venue global (tanpa filter event)
        $venue = Venue::find($validated['venue']);

        // kategori & peserta difilter berdasarkan event
        $kategori = MataLomba::whereHas('kategori', function ($q) use ($eventId) {
            $q->where('event_id', $eventId);
        })->find($validated['kategori_lomba']);

        $peserta = Peserta::whereHas('pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
            $q->where('event_id', $eventId);
        })->find($validated['peserta']);

        if (!$venue || !$kategori || !$peserta) {
            return back()->withErrors(['Data tidak sesuai dengan event yang dipilih.'])->withInput();
        }

        $jadwalHarian = session('jadwal_harian', []);
        $mataLomba = $this->processSubKategoriLomba();

        // Ambil tanggal event dari jadwal_harian
        $tanggalEvent = collect($jadwalHarian)
            ->filter(fn($item) => isset($item['tanggal']) && $item['tanggal'])
            ->pluck('tanggal')
            ->sort()
            ->values();

        if ($tanggalEvent->isEmpty()) {
            return back()->withErrors(['Tidak ditemukan tanggal event. Harap input terlebih dahulu.']);
        }

        $tanggalMin = Carbon::parse($tanggalEvent->first());
        $tanggalMax = Carbon::parse($tanggalEvent->last());

        $pesertaPerKategori = $this->processPesertaKategoriLomba();

        $venueDiLuarRentang = [];

        // cek venue berdasarkan kategori lomba unik
        $kategoriUnik = collect($pesertaPerKategori)->unique('kategori_lomba');
        foreach ($kategoriUnik as $item) {
            $kategoriLomba = $item['kategori_lomba'];
            $mataLombaVenue = MataLomba::where('nama_lomba', $kategoriLomba)
                ->whereHas('kategori', function ($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })
                ->first();

            if (!$mataLombaVenue) continue;

            $venueCheck = $mataLombaVenue->venue;
            if ($venueCheck && $venueCheck->tanggal_tersedia) {
                $tglVenue = Carbon::parse($venueCheck->tanggal_tersedia);
                if ($tglVenue->lt($tanggalMin) || $tglVenue->gt($tanggalMax)) {
                    $venueDiLuarRentang[$venueCheck->id] = $venueCheck->name;
                }
            }
        }

        if (!empty($venueDiLuarRentang)) {
            $venueList = [];
            foreach ($venueDiLuarRentang as $venueId => $venueName) {
                $venueData = Venue::find($venueId);
                $tglVenue = Carbon::parse($venueData->tanggal_tersedia);
                $hari = $tglVenue->translatedFormat('l');
                $tglVenueFormat = $tglVenue->translatedFormat('d F Y');
                $venueList[] = "- <strong>$venueName</strong>: $hari, $tglVenueFormat";
            }

            $rangeTanggalEvent = $tanggalMin->equalTo($tanggalMax)
                ? "<strong>" . $tanggalMin->translatedFormat('d F Y') . "</strong>"
                : "<strong>" . $tanggalMin->translatedFormat('d F Y') . " s.d. " . $tanggalMax->translatedFormat('d F Y') . "</strong>";

            $finalMessage = "Beberapa venue berada di luar rentang tanggal event:<br>" .
                implode('<br>', $venueList) .
                "<br><br>Tanggal event: $rangeTanggalEvent<br>Silakan ubah waktu event atau perbarui waktu ketersediaan venue.";

            return redirect()->route('jadwal.create.step2')
                ->with('venue_out_of_range', $finalMessage)
                ->withInput();
        }

        return view('jadwal.create-step3', compact(
            'venue',
            'kategori',
            'peserta',
            'mataLomba',
            'jadwalHarian',
            'fieldErrors'
        ));
    }


    public function showStep2()
    {
        if (!session()->has('jadwal_harian')) {
            return redirect()->route('jadwal.create.step1')
                ->withErrors(['Data jadwal harian tidak ditemukan.']);
        }

        return view('jadwal.create-step2', [
            'jadwal_nama' => session('jadwal_nama'),
            'jadwal_harian' => session('jadwal_harian'),
            'venue_out_of_range' => session('venue_out_of_range'),
        ]);
    }




    public function store(Request $request)
    {
        Log::debug('Memulai proses store constraint lomba.');

        $validated = $request->validate([
            'hari' => 'nullable|array',
            'waktu_mulai' => 'nullable|array',
            'waktu_selesai' => 'nullable|array',
            'saving_time' => 'nullable|array',
            'round' => 'nullable|array',
        ]);

        $hari = $request->input('hari', []);
        $waktuMulai = $request->input('waktu_mulai', []);
        $waktuSelesai = $request->input('waktu_selesai', []);
        $savingTime = $request->input('saving_time', []);
        $round = $request->input('round', []);

        $jadwalHarian = session('jadwal_harian', []);

        $eventId = session('jadwal_event_id'); // Ambil event_id dari session

        $constraint = [];
        $fieldErrors = [];

        foreach ($hari as $mataLombaId => $value) {
            $constraint[$mataLombaId] = [
                'hari' => is_array($value) ? $value : [$value],
                'waktu_mulai' => $waktuMulai[$mataLombaId] ?? null,
                'waktu_selesai' => $waktuSelesai[$mataLombaId] ?? null,
                'saving_time' => $savingTime[$mataLombaId] ?? null,
                'round' => $round[$mataLombaId] ?? null,
            ];

            Log::info("🔍 Validasi Mata Lomba ID: $mataLombaId");
            Log::debug("→ Data Input: ", $constraint[$mataLombaId]);

            // Pastikan mata lomba sesuai event_id
            $mataLomba = MataLomba::with(['venue', 'kategori'])
                ->where('id', $mataLombaId)
                ->whereHas('kategori', function ($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })
                ->first();

            if (!$mataLomba || !$mataLomba->venue) {
                Log::warning("‼️ Mata lomba ID $mataLombaId tidak ditemukan atau venue null.");
                continue;
            }

            $venue = $mataLomba->venue;
            $userHari = $constraint[$mataLombaId]['hari'][0] ?? null;
            $userStartRaw = $constraint[$mataLombaId]['waktu_mulai'];
            $userEndRaw = $constraint[$mataLombaId]['waktu_selesai'];

            try {
                Log::info("🔍 Mulai validasi untuk {$mataLomba->nama_lomba} (ID: {$mataLombaId})");

                // validasi tanggal venue
                if (!is_null($userHari) && $venue->tanggal_tersedia) {
                    $userDate = Carbon::parse($userHari);
                    $availableDate = Carbon::parse($venue->tanggal_tersedia);

                    Log::debug("📅 {$mataLomba->nama_lomba} → User hari: {$userDate->toDateString()}, Venue tersedia: {$availableDate->toDateString()}");

                    if (!$userDate->equalTo($availableDate)) {
                        $fieldErrors["hari.$mataLombaId"][] = "Tanggal tidak sesuai. Venue hanya tersedia tanggal {$availableDate->format('d M Y')}.";
                    }
                }

                // waktu mulai
                if ($userStartRaw) {
                    $userStart = Carbon::parse($userStartRaw);
                    Log::debug("⏰ {$mataLomba->nama_lomba} → Waktu mulai user: {$userStart->format('H:i')}");

                    if ($venue->waktu_mulai_tersedia) {
                        $startAvailable = Carbon::parse($venue->waktu_mulai_tersedia);
                        if ($userStart->lt($startAvailable)) {
                            Log::debug("‼️ Mulai terlalu awal → {$userStart->format('H:i')} < {$startAvailable->format('H:i')}");
                            $fieldErrors["waktu_mulai.$mataLombaId"][] = "Waktu mulai terlalu awal. Minimal {$startAvailable->format('H:i')}.";
                        }
                    }

                    if ($venue->waktu_berakhir_tersedia) {
                        $endAvailable = Carbon::parse($venue->waktu_berakhir_tersedia);
                        if ($userStart->gt($endAvailable)) {
                            Log::debug("‼️ Mulai melebihi akhir → {$userStart->format('H:i')} > {$endAvailable->format('H:i')}");
                            $fieldErrors["waktu_mulai.$mataLombaId"][] = "Waktu mulai melebihi batas akhir venue ({$endAvailable->format('H:i')}).";
                        }
                    }
                }

                // waktu selesai
                if ($userEndRaw) {
                    $userEnd = Carbon::parse($userEndRaw);
                    Log::debug("⏰ {$mataLomba->nama_lomba} → Waktu selesai user: {$userEnd->format('H:i')}");

                    if ($venue->waktu_berakhir_tersedia) {
                        $endAvailable = Carbon::parse($venue->waktu_berakhir_tersedia);
                        if ($userEnd->gt($endAvailable)) {
                            Log::debug("‼️ Selesai melebihi venue → {$userEnd->format('H:i')} > {$endAvailable->format('H:i')}");
                            $fieldErrors["waktu_selesai.$mataLombaId"][] = "Waktu selesai melebihi maksimal venue ({$endAvailable->format('H:i')}).";
                        }
                    }

                    if ($venue->waktu_mulai_tersedia) {
                        $startAvailable = Carbon::parse($venue->waktu_mulai_tersedia);
                        if ($userEnd->lt($startAvailable)) {
                            Log::debug("‼️ Selesai sebelum venue buka → {$userEnd->format('H:i')} < {$startAvailable->format('H:i')}");
                            $fieldErrors["waktu_selesai.$mataLombaId"][] = "Waktu selesai lebih awal dari awal venue ({$startAvailable->format('H:i')}).";
                        }
                    }
                }

                // validasi terhadap jadwal harian
                if (!empty($jadwalHarian)) {
                    Log::info("📌 [{$mataLomba->nama_lomba} | ID: {$mataLombaId}] Masuk validasi jadwal harian. Total entri: " . count($jadwalHarian));
                    Log::info("User start $userStartRaw & User end $userEndRaw");
                    Log::info("User Hari $userHari");
                    // jika user isi hari
                    if ($userHari && ($userStartRaw || $userEndRaw)) {
                        $matchTanggal = collect($jadwalHarian)->firstWhere('tanggal', $userHari);
                        Log::debug("→ [{$mataLomba->nama_lomba} | ID: {$mataLombaId}] Match tanggal jadwal harian: ", [$matchTanggal]);

                        if ($matchTanggal) {
                            $jadwalStart = Carbon::createFromFormat('H:i', $matchTanggal['waktu_mulai']);
                            $jadwalEnd = Carbon::createFromFormat('H:i', $matchTanggal['waktu_selesai']);

                            if ($userStartRaw) {
                                $userStart = Carbon::createFromFormat('H:i', $userStartRaw);
                                if ($userStart->lt($jadwalStart)) {
                                    $fieldErrors["waktu_mulai.$mataLombaId"][] = "Waktu mulai terlalu awal untuk <strong>{$mataLomba->nama_lomba}</strong> pada tanggal $userHari. Minimal {$jadwalStart->format('H:i')}.";
                                }
                            }

                            if ($userEndRaw) {
                                $userEnd = Carbon::createFromFormat('H:i', $userEndRaw);
                                if ($userEnd->gt($jadwalEnd)) {
                                    $fieldErrors["waktu_selesai.$mataLombaId"][] = "Waktu selesai terlalu akhir untuk <strong>{$mataLomba->nama_lomba}</strong> pada tanggal $userHari. Maksimal {$jadwalEnd->format('H:i')}.";
                                }
                            }
                        } else {
                            Log::debug("‼️ [{$mataLomba->nama_lomba} | ID: {$mataLombaId}] Tidak ditemukan jadwal harian untuk tanggal $userHari");
                            $fieldErrors["hari.$mataLombaId"][] = "Tidak ada jadwal harian untuk <strong>{$mataLomba->nama_lomba}</strong> di tanggal $userHari.";
                        }
                    }

                    // jika user tidak isi hari tapi isi jam
                    if (empty($userHari) && ($userStartRaw || $userEndRaw)) {
                        Log::info(message: "→ [{$mataLomba->nama_lomba} | ID: {$mataLombaId}] Hari tidak diisi, validasi pakai rentang waktu global dari session jadwal_harian.");

                        $waktuTersediaStart = collect($jadwalHarian)
                            ->pluck('waktu_mulai')
                            ->map(fn($w) => Carbon::createFromFormat('H:i', $w))
                            ->sort()
                            ->first();

                        $waktuTersediaEnd = collect($jadwalHarian)
                            ->pluck('waktu_selesai')
                            ->map(fn($w) => Carbon::createFromFormat('H:i', $w))
                            ->sortDesc()
                            ->first();

                        Log::info("Waktu  mulai : $waktuTersediaStart dan waktu akhir $waktuTersediaEnd");

                        if ($userStartRaw) {
                            $userStart = Carbon::createFromFormat('H:i', $userStartRaw);
                            Log::info("Waktu tersedia: $userStart");
                            if ($userStart->lt($waktuTersediaStart)) {
                                $fieldErrors["waktu_mulai.$mataLombaId"][] = "Waktu mulai terlalu awal untuk <strong>{$mataLomba->nama_lomba}</strong>. Minimal {$waktuTersediaStart->format('H:i')}.";
                            }
                        }

                        if ($userStartRaw) {
                            $userStart = Carbon::createFromFormat('H:i', $userStartRaw);
                            Log::info("Waktu tersedia: $userStart");
                            if ($userStart->gt($waktuTersediaEnd)) {
                                $fieldErrors["waktu_mulai.$mataLombaId"][] = "Waktu mulai melebihi waktu akhir untuk <strong>{$mataLomba->nama_lomba}</strong>. Maksimal {$waktuTersediaEnd->format('H:i')}.";
                            }
                        }

                        if ($userEndRaw) {
                            $userEnd = Carbon::createFromFormat('H:i', $userEndRaw);
                            if ($userEnd->gt($waktuTersediaEnd)) {
                                $fieldErrors["waktu_selesai.$mataLombaId"][] = "Waktu selesai terlalu akhir untuk <strong>{$mataLomba->nama_lomba}</strong>. Waktu selesai {$waktuTersediaEnd->format('H:i')}.";
                            }
                        }

                        if ($userEndRaw) {
                            $userEnd = Carbon::createFromFormat('H:i', $userEndRaw);
                            if ($userEnd->lt($waktuTersediaStart)) {
                                $fieldErrors["waktu_selesai.$mataLombaId"][] = "Waktu selesai kurang dari waktu mulai <strong>{$mataLomba->nama_lomba}</strong>. Waktu mulai {$waktuTersediaStart->format('H:i')}.";
                            }
                        }
                    }
                }

                // validasi jika user hanya isi waktu selesai dan kosongkan waktu mulai
                if (empty($userStartRaw) && $userEndRaw && empty($userHari) && !empty($jadwalHarian)) {
                    $userEnd = Carbon::createFromFormat('H:i', $userEndRaw);

                    $waktuGlobalStart = collect($jadwalHarian)
                        ->pluck('waktu_mulai')
                        ->map(fn($w) => Carbon::createFromFormat('H:i', $w))
                        ->sort()
                        ->first();

                    $selisihMenit = $waktuGlobalStart->diffInMinutes($userEnd);

                    $durasiPerPeserta = $mataLomba->durasi ?? 0;
                    $pesertaPerKategori = $this->processPesertaKategoriLomba();
                    $pesertaTerkait = collect($pesertaPerKategori)->where('kategori_lomba', $mataLomba->nama_lomba);

                    $jumlah = $mataLomba->is_kelompok
                        ? $pesertaTerkait->pluck('tim_id')->unique()->filter()->count()
                        : $pesertaTerkait->count();

                    $totalDurasiDibutuhkan = $mataLomba->is_serentak
                        ? $durasiPerPeserta
                        : $jumlah * $durasiPerPeserta;

                    $saving = $constraint[$mataLombaId]['saving_time'] ?? 0;
                    if (!$mataLomba->is_serentak && is_numeric($saving) && $saving > 0 && $jumlah > 1) {
                        $totalDurasiDibutuhkan += ($jumlah - 1) * $saving;
                    }

                    $durasiFormat = function ($menit) {
                        if ($menit >= 1440) {
                            $hari = floor($menit / 1440);
                            $sisaMenit = $menit % 1440;
                            $jam = floor($sisaMenit / 60);
                            $mnt = $sisaMenit % 60;
                            return "{$hari} hari" . ($jam ? " {$jam} jam" : '') . ($mnt ? " {$mnt} menit" : '');
                        } elseif ($menit >= 60) {
                            $jam = floor($menit / 60);
                            $mnt = $menit % 60;
                            return "{$jam} jam" . ($mnt ? " {$mnt} menit" : '');
                        } else {
                            return "{$menit} menit";
                        }
                    };

                    Log::debug("🧮 Validasi jika hanya waktu selesai diisi → Selisih: {$durasiFormat($selisihMenit)} | Dibutuhkan: {$durasiFormat($totalDurasiDibutuhkan)}");

                    if ($selisihMenit < $totalDurasiDibutuhkan) {
                        $pesan = "Durasi tidak cukup dari waktu mulai (<strong>{$waktuGlobalStart->format('H:i')}</strong>) hingga waktu selesai. Minimal <strong>{$durasiFormat($totalDurasiDibutuhkan)}</strong> untuk {$jumlah} " . ($mataLomba->is_kelompok ? "tim" : "peserta") . " dengan durasi <strong>{$durasiFormat($durasiPerPeserta)}</strong>/peserta.";


                        if (!$mataLomba->is_serentak && is_numeric($saving) && $saving > 0) {
                            $pesan .= " Saving time <strong>{$saving} menit</strong> antar peserta turut dihitung.";
                        }

                        $fieldErrors["waktu_selesai.$mataLombaId"][] = $pesan;
                    }
                }



                // validasi durasi minimal
                if ($userStartRaw && $userEndRaw) {
                    $userStart = Carbon::createFromFormat('H:i', $userStartRaw);
                    $userEnd = Carbon::createFromFormat('H:i', $userEndRaw);
                    $selisihMenit = $userStart->diffInMinutes($userEnd);
                    $durasiPerPeserta = $mataLomba->durasi ?? 0;

                    $pesertaPerKategori = $this->processPesertaKategoriLomba();
                    $pesertaTerkait = collect($pesertaPerKategori)->where('kategori_lomba', $mataLomba->nama_lomba);

                    $jumlah = $mataLomba->is_kelompok
                        ? $pesertaTerkait->pluck('tim_id')->unique()->filter()->count()
                        : $pesertaTerkait->count();

                    // total durasi pokok
                    $totalDurasiDibutuhkan = $mataLomba->is_serentak
                        ? $durasiPerPeserta
                        : $jumlah * $durasiPerPeserta;

                    // tambahin saving time kalau diisi
                    $saving = $constraint[$mataLombaId]['saving_time'] ?? 0;
                    if (!$mataLomba->is_serentak && is_numeric($saving) && $saving > 0 && $jumlah > 1) {
                        $totalDurasiDibutuhkan += ($jumlah - 1) * $saving;
                    }

                    // fungsi format durasi
                    $durasiFormat = function ($menit) {
                        if ($menit >= 1440) {
                            $hari = floor($menit / 1440);
                            $sisaMenit = $menit % 1440;
                            $jam = floor($sisaMenit / 60);
                            $mnt = $sisaMenit % 60;
                            return "{$hari} hari" . ($jam ? " {$jam} jam" : '') . ($mnt ? " {$mnt} menit" : '');
                        } elseif ($menit >= 60) {
                            $jam = floor($menit / 60);
                            $mnt = $menit % 60;
                            return "{$jam} jam" . ($mnt ? " {$mnt} menit" : '');
                        } else {
                            return "{$menit} menit";
                        }
                    };

                    Log::debug("📏 [{$mataLomba->nama_lomba} | ID: {$mataLombaId}] Durasi user: {$durasiFormat($selisihMenit)}, Dibutuhkan: {$durasiFormat($totalDurasiDibutuhkan)} ({$jumlah} " . ($mataLomba->is_kelompok ? 'tim' : 'peserta') . ")");

                    if ($selisihMenit < $totalDurasiDibutuhkan) {
                        $pesan = "Durasi tidak cukup. Butuh minimal <strong>{$durasiFormat($totalDurasiDibutuhkan)}</strong> untuk {$jumlah} " . ($mataLomba->is_kelompok ? "tim" : "peserta") . " dengan durasi <strong>{$durasiFormat($durasiPerPeserta)}</strong>/peserta.";

                        if (!$mataLomba->is_serentak && is_numeric($saving) && $saving > 0) {
                            $pesan .= " Saving time <strong>{$saving} menit</strong> antar peserta turut dihitung.";
                        }

                        $fieldErrors["waktu_selesai.$mataLombaId"][] = $pesan;
                        // $durasiErrorSudahDitampilkan = true;
                    }
                }
            } catch (\Exception $e) {
                Log::error("‼️ Error validasi {$mataLomba->nama_lomba} (ID: {$mataLombaId}): " . $e->getMessage());
            }
        }

        // validasi durasi per tanggal dan per venue
        $lombaPerTanggalVenue = [];

        $durasiFormat = function ($menit) {
            if ($menit >= 1440) {
                $hari = floor($menit / 1440);
                $sisaMenit = $menit % 1440;
                $jam = floor($sisaMenit / 60);
                $mnt = $sisaMenit % 60;
                return "{$hari} hari" . ($jam ? " {$jam} jam" : '') . ($mnt ? " {$mnt} menit" : '');
            } elseif ($menit >= 60) {
                $jam = floor($menit / 60);
                $mnt = $menit % 60;
                return "{$jam} jam" . ($mnt ? " {$mnt} menit" : '');
            } else {
                return "{$menit} menit";
            }
        };

        foreach ($constraint as $mataLombaId => $c) {
            $hariArr = $c['hari'] ?? [];

            $mataLomba = MataLomba::with('venue')->find($mataLombaId);
            if (!$mataLomba || !$mataLomba->venue)
                continue;

            $venueId = $mataLomba->venue->id;

            foreach ($hariArr as $tanggal) {
                $key = $tanggal . '|' . $venueId;
                $lombaPerTanggalPerVenue[$key][] = $mataLombaId;
            }
        }

        Log::debug('🗓️ Lomba per Tanggal + Venue:', $lombaPerTanggalPerVenue);

        foreach ($lombaPerTanggalPerVenue as $key => $listMataLombaId) {
            [$tanggal, $venueId] = explode('|', $key);
            $totalDurasi = 0;
            $namaLombaGabung = [];

            Log::debug("🔍 Validasi durasi gabungan untuk tanggal $tanggal dan venue ID $venueId");

            foreach ($listMataLombaId as $mataLombaId) {
                $mataLomba = MataLomba::with('venue')->find($mataLombaId);
                if (!$mataLomba)
                    continue;

                $namaLombaGabung[] = $mataLomba->nama_lomba;
                $durasiPerPeserta = $mataLomba->durasi ?? 0;
                $saving = $constraint[$mataLombaId]['saving_time'] ?? 0;

                $pesertaPerKategori = $this->processPesertaKategoriLomba();
                $pesertaTerkait = collect($pesertaPerKategori)->where('kategori_lomba', $mataLomba->nama_lomba);
                $jumlah = $mataLomba->is_kelompok
                    ? $pesertaTerkait->pluck('tim_id')->unique()->filter()->count()
                    : $pesertaTerkait->count();

                Log::debug("📋 {$mataLomba->nama_lomba} → Durasi/peserta: $durasiPerPeserta | Jumlah: $jumlah | Saving: $saving");

                $durasi = $mataLomba->is_serentak
                    ? $durasiPerPeserta
                    : ($jumlah * $durasiPerPeserta);

                if (!$mataLomba->is_serentak && is_numeric($saving) && $saving > 0 && $jumlah > 1) {
                    $durasi += ($jumlah - 1) * $saving;
                }

                Log::debug("📏 Total durasi untuk {$mataLomba->nama_lomba}: {$durasiFormat($durasi)}");

                $totalDurasi += $durasi;
            }

            $jadwal = collect($jadwalHarian)->firstWhere('tanggal', $tanggal);
            if (!$jadwal)
                continue;

            $jadwalStart = Carbon::createFromFormat('H:i', $jadwal['waktu_mulai']);
            $jadwalEnd = Carbon::createFromFormat('H:i', $jadwal['waktu_selesai']);
            $durasiTersedia = $jadwalStart->diffInMinutes($jadwalEnd);

            $durasiStr = $durasiFormat($totalDurasi);
            $durasiMax = $durasiFormat($durasiTersedia);
            $venueName = Venue::find($venueId)?->name ?? "ID $venueId";

            Log::debug("🧮 [$tanggal - $venueName] Total dibutuhkan: $durasiStr | Tersedia: $durasiMax");

            if ($totalDurasi > $durasiTersedia) {
                $lombaGabungStr = implode(', ', $namaLombaGabung);
                $tanggalIndo = Carbon::parse($tanggal)->translatedFormat('l, d F Y');

                $pesan = "Durasi tidak cukup pada <strong>$tanggalIndo</strong> untuk venue <strong>$venueName</strong>. Dibutuhkan <strong>$durasiStr</strong> untuk gabungan lomba: <strong>$lombaGabungStr</strong>. Batas waktu hanya <strong>$durasiMax</strong>.";

                // Hanya tambahkan ke satu mata lomba saja (misalnya yang pertama)
                $mataLombaIdPertama = $listMataLombaId[0];
                $fieldErrors["hari.$mataLombaIdPertama"][] = $pesan;

                Log::warning("⚠️ Durasi tidak cukup pada $tanggalIndo @ $venueName → Dibutuhkan: $durasiStr | Tersedia: $durasiMax | Lomba: $lombaGabungStr");
            }
        }



        if (!empty($fieldErrors)) {
            return redirect()->route('jadwal.create.step3', [
                'venue' => $request->input('venue'),
                'kategori_lomba' => $request->input('kategori_lomba'),
                'peserta' => $request->input('peserta'),
            ])->with('field_errors', $fieldErrors)
                ->withInput();
        }
        // } else {
        //     dd($constraint);
        // }

        session(['constraint_lomba' => $constraint]);
        Log::debug('Constraint disimpan ke session:', $constraint);

        return $this->prosesJadwal($request);
    }





    public function switchJadwal($nama_jadwal, $tahun, $version)
    {
        $jadwal = Jadwal::where('nama_jadwal', $nama_jadwal)
            ->where('tahun', $tahun)
            ->where('version', $version)
            ->firstOrFail();

        $jadwals = Agenda::select('agenda.*')
            ->join('mata_lomba', 'agenda.mata_lomba_id', '=', 'mata_lomba.id')
            ->join('venue', 'agenda.venue_id', '=', 'venue.id') // join venue
            ->where('jadwal_id', $jadwal->id)
            ->orderBy('tanggal') // urutkan tanggal dulu
            ->orderBy('venue.name') // urutkan berdasarkan nama venue
            ->orderBy('waktu_mulai') // urutkan berdasarkan jam mulai (pagi dulu)
            ->with(['mataLomba', 'venue', 'peserta', 'juri', 'tim']) // relasi tetap diambil
            ->get();

        // Dapatkan semua agenda dari jadwal lain
        $availableJadwals = Agenda::with(['peserta', 'mataLomba'])
            ->where('jadwal_id', '!=', $jadwal->id)
            ->whereHas('jadwal', function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->get();

        return view('jadwal.switch', compact('jadwals', 'availableJadwals', 'nama_jadwal', 'tahun', 'version'));
    }



    public function createWithDetail($nama_jadwal, $tahun, $version)
    {
        $jadwal = Jadwal::where('nama_jadwal', $nama_jadwal)->first();

        // Ambil tanggal-tanggal unik dari semua agenda yang terhubung dengan jadwal ini
        $tanggal_unik = [];
        if ($jadwal) {
            $tanggal_unik = Agenda::where('jadwal_id', $jadwal->id)
                ->select(DB::raw('DISTINCT tanggal'))
                ->orderBy('tanggal')
                ->pluck('tanggal');
        }

        // Ambil event_id dari jadwal
        $eventId = $jadwal?->event_id ?? null;

        // Ambil hanya mata lomba untuk event ini
        $mata_lomba = MataLomba::whereHas('kategori', function ($q) use ($eventId) {
            $q->where('event_id', $eventId);
        })->get();

        // Peserta yang sudah membayar
        $peserta_ids = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id')->toArray();

        // Filter peserta berdasarkan event
        $peserta = Peserta::with('pendaftar.mataLomba.kategori')
            ->whereIn('id', $peserta_ids)
            ->whereHas('pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->orderBy('nama_peserta')
            ->get();

        // Filter tim berdasarkan event dan peserta bayar
        $tim = Tim::with(['peserta.pendaftar.mataLomba.kategori'])
            ->whereHas('peserta', function ($q) use ($peserta_ids) {
                $q->whereIn('peserta.id', $peserta_ids);
            })
            ->whereHas('peserta.pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->get();

        $venue = Venue::all();
        $juri = Juri::all();

        return view('jadwal.add', compact(
            'mata_lomba',
            'venue',
            'peserta',
            'juri',
            'tim',
            'nama_jadwal',
            'tahun',
            'version',
            'tanggal_unik'
        ));
    }


    public function add(Request $request)
    {
        $request->validate([
            'mata_lomba_id' => 'nullable|exists:mata_lomba,id',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'venue_id' => 'nullable|exists:venue,id',
            'peserta_id' => 'nullable|array|min:1',
            'peserta_id.*' => 'exists:peserta,id',
            'tim_id' => 'nullable|array',
            'tim_id.*' => 'exists:tim,id',
            'juri_id' => 'nullable|exists:juri,id',
            'nama_jadwal' => 'required|string|max:255',
            'tahun' => 'required|integer',
            'kegiatan' => 'nullable|string|max:1000',
            'version' => 'required|integer',
            'tanggal' => 'required|date',
            'force' => 'boolean',
        ]);

        // dd($request->all());

        $force = $request->boolean('force', false);
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;
        $tanggal = $request->tanggal;
        $venueId = $request->venue_id;
        $mataLombaId = $request->mata_lomba_id;
        $juriId = $request->juri_id;

        // Ambil peserta langsung dan peserta tim
        $pesertaLangsung = $request->peserta_id ?? [];
        $pesertaTim = [];
        if ($request->tim_id) {
            $pesertaTim = \App\Models\Bergabung::whereIn('tim_id', $request->tim_id)
                ->pluck('peserta_id')
                ->unique()
                ->toArray();
        }
        $allPesertaId = array_unique(array_merge($pesertaLangsung, $pesertaTim));

        // Buat atau ambil jadwal
        $jadwal = Jadwal::firstOrCreate(
            ['nama_jadwal' => $request->nama_jadwal, 'tahun' => $request->tahun, 'version' => $request->version],
            ['venue_id' => $request->venue_id]
        );

        // Fungsi cek waktu bentrok (dipakai di query)
        $cekWaktu = function ($query) use ($waktuMulai, $waktuSelesai) {
            $query->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                $q->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            });
        };

        // Cek bentrok venue
        $bentrokVenueList = $venueId ? Agenda::with('mataLomba', 'venue')
            ->where('jadwal_id', $jadwal->id)
            ->where('venue_id', $venueId)
            ->whereDate('tanggal', $tanggal)
            ->where($cekWaktu)
            ->get() : collect();

        // dd($bentrokVenueList);

        // Cek bentrok peserta
        $bentrokPesertaList = !empty($allPesertaId) ? Agenda::with('mataLomba', 'peserta')
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->where($cekWaktu)
            ->whereHas('peserta', function ($query) use ($allPesertaId) {
                $query->whereIn('peserta.id', $allPesertaId);
            })
            ->get() : collect();

        // Cek bentrok tim (khusus kalau ingin tampilkan tim yang bentrok)
        $bentrokTimList = !empty($pesertaTim) ? Agenda::with('mataLomba', 'tim')
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->where($cekWaktu)
            ->whereHas('tim.peserta', function ($query) use ($pesertaTim) {
                $query->whereIn('peserta.id', $pesertaTim);
            })
            ->get() : collect();

        // Cek bentrok juri
        $bentrokJuriList = $juriId ? Agenda::with('mataLomba')
            ->where('jadwal_id', $jadwal->id)
            ->where('juri_id', $juriId)
            ->whereDate('tanggal', $tanggal)
            ->where($cekWaktu)
            ->get() : collect();

        // Jika ada bentrok dan tidak force, tampilkan pesan error detail
        if (
            !$force && (
                $bentrokVenueList->isNotEmpty() ||
                $bentrokPesertaList->isNotEmpty() ||
                $bentrokJuriList->isNotEmpty() ||
                $bentrokTimList->isNotEmpty()
            )
        ) {
            $detail = [];
            foreach ($bentrokVenueList as $b) {
                $namaLomba = $b->mataLomba->nama_lomba ?? '(Tanpa Mata Lomba)';
                $venue = $b->venue->name ?? '(Tanpa Venue)';
                $detail[] = "Lomba {$namaLomba} pada {$b->waktu_mulai}-{$b->waktu_selesai} di {$venue}";
            }
            foreach ($bentrokPesertaList as $b) {
                // dd($b->peserta);
                $peserta = $b->peserta->pluck('nama_peserta')->implode(', ');
                $namaLomba = $b->mataLomba->nama_lomba ?? '(Tanpa Mata Lomba)';
                $detail[] = "Peserta {$peserta} pada lomba {$namaLomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
            }
            foreach ($bentrokJuriList as $b) {
                $namaLomba = $b->mataLomba->nama_lomba ?? '(Tanpa Mata Lomba)';
                $detail[] = "Juri pada lomba {$namaLomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
            }
            foreach ($bentrokTimList as $b) {
                // dapatkan peserta tim dari agenda bentrok ini
                $pesertaBentrok = $b->tim->flatMap(function ($tim) {
                    return $tim->peserta->pluck('id');
                })->unique()->toArray();

                // cari irisan peserta bentrok dengan pesertaTim (peserta di tim utama)
                $pesertaYangBentrok = array_intersect($pesertaTim, $pesertaBentrok);

                // ambil nama tim yang ada peserta yang bentrok
                $namaTimBentrok = $b->tim->filter(function ($tim) use ($pesertaYangBentrok) {
                    $pesertaTimIni = $tim->peserta->pluck('id')->toArray();
                    return count(array_intersect($pesertaTimIni, $pesertaYangBentrok)) > 0;
                })->pluck('nama_tim')->implode(', ');

                if ($namaTimBentrok) {
                    $namaLomba = $b->mataLomba->nama_lomba ?? '(Tanpa Mata Lomba)';
                    $detail[] = "{$namaTimBentrok} pada lomba {$namaLomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
                }
            }

            $pesan = "Terjadi bentrok:\n\n" . implode("\n", $detail);

            return back()->withInput()->with('error_force', $pesan);
        }

        // Simpan agenda baru
        $agendaBaru = Agenda::create([
            'jadwal_id' => $jadwal->id,
            'mata_lomba_id' => $mataLombaId,
            'tanggal' => $tanggal,
            'kegiatan' => $request->kegiatan,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'venue_id' => $venueId,
            'juri_id' => $juriId,
        ]);

        // Simpan relasi peserta dan tim
        if ($request->filled('peserta_id')) {
            $agendaBaru->peserta()->attach($request->peserta_id);
        }

        if ($request->filled('tim_id')) {
            $agendaBaru->tim()->attach($request->tim_id);
        }

        // Jika bentrok tapi paksa force, kamu bisa tambahkan logic geser agenda seperti di update() atau seperti sebelumnya jika diperlukan
        // Jika ada bentrok dan force, lakukan geser agenda bawahnya mirip add()
        if ($force && ($bentrokVenueList->isNotEmpty() || $bentrokPesertaList->isNotEmpty() || $bentrokJuriList->isNotEmpty())) {
            // Ambil semua agenda dalam jadwal, venue, tanggal, urut waktu mulai, kecuali agenda ini
            $agendaSemua = Agenda::where('jadwal_id', $agendaBaru->jadwal_id)
                ->where('venue_id', $venueId)
                ->whereDate('tanggal', $tanggal)
                ->where('id', '!=', $agendaBaru->id)
                ->orderBy('waktu_mulai')
                ->get();

            // Cari index agenda pertama yang bentrok dengan agenda ini (baru diupdate)
            $indexBentrokPertama = $agendaSemua->search(function ($a) use ($waktuMulai, $waktuSelesai) {
                $start1 = strtotime($a->waktu_mulai);
                $end1 = strtotime($a->waktu_selesai);
                $start2 = strtotime($waktuMulai);
                $end2 = strtotime($waktuSelesai);
                return !($end1 <= $start2 || $start1 >= $end2); // true kalau bentrok
            });

            if ($indexBentrokPertama !== false) {
                // Slice agenda bentrok dan setelahnya
                $agendaTerdampak = $agendaSemua->slice($indexBentrokPertama)->values();

                // Mulai geser dari waktu selesai agenda ini (yang baru diupdate)
                $waktuSelesaiBaru = Carbon::createFromFormat('H:i', $waktuSelesai);

                foreach ($agendaTerdampak as $agendaItem) {
                    $waktuMulaiLama = Carbon::createFromFormat('H:i:s', $agendaItem->waktu_mulai);
                    $waktuSelesaiLama = Carbon::createFromFormat('H:i:s', $agendaItem->waktu_selesai);
                    $durasiAgenda = $waktuMulaiLama->diffInMinutes($waktuSelesaiLama);

                    $waktuMulaiBaru = $waktuSelesaiBaru->copy();
                    $waktuSelesaiBaru = $waktuMulaiBaru->copy()->addMinutes($durasiAgenda);

                    Log::info('Geser agenda ID ' . $agendaItem->id, [
                        'waktu_mulai_lama' => $waktuMulaiLama->format('H:i:s'),
                        'waktu_selesai_lama' => $waktuSelesaiLama->format('H:i:s'),
                        'waktu_mulai_baru' => $waktuMulaiBaru->format('H:i'),
                        'waktu_selesai_baru' => $waktuSelesaiBaru->format('H:i'),
                    ]);

                    $agendaItem->update([
                        'waktu_mulai' => $waktuMulaiBaru->format('H:i'),
                        'waktu_selesai' => $waktuSelesaiBaru->format('H:i'),
                    ]);
                }
            }
        }

        $jadwal = $agendaBaru->jadwal;

        return redirect()->route('jadwal.change', ['id' => $jadwal->id])->with('success', 'Agenda berhasil ditambahkan.');
    }


    public function prosesSwitch(Request $request)
    {
        $selectedIds = $request->input('switch_ids', []);
        Log::info('Switch request initiated', ['selected_ids' => $selectedIds]);

        if (count($selectedIds) !== 2) {
            return redirect()->back()->with('error', 'Anda harus memilih tepat 2 agenda untuk ditukar.');
        }

        [$id1, $id2] = $selectedIds;
        $agenda1 = Agenda::with('peserta', 'juri', 'mataLomba', 'venue')->find($id1);
        $agenda2 = Agenda::with('peserta', 'juri', 'mataLomba', 'venue')->find($id2);

        if ($agenda1->venue_id !== $agenda2->venue_id) {
            return redirect()->back()->withInput()->with('venue_mismatch', 'Agenda hanya bisa ditukar jika berada di venue yang sama.');
        }

        // ❗ Tolak jika venue beda
        if ($agenda1->venue_id !== $agenda2->venue_id) {
            return redirect()->back()->with('error', 'Agenda hanya bisa ditukar jika berada di venue yang sama.');
        }

        $conflicts = [];

        // ❗ Cek bentrok peserta
        $jadwalId1 = $agenda1->jadwal_id;
        $jadwalId2 = $agenda2->jadwal_id;

        $peserta1 = $agenda1->peserta->pluck('id');
        $pesertaBentrok1 = Agenda::with('mataLomba')->where('id', '!=', $agenda1->id)
            ->where('jadwal_id', $jadwalId1)
            ->whereHas('peserta', function ($q) use ($peserta1) {
                $q->whereIn('peserta_id', $peserta1);
            })
            ->where(function ($q) use ($agenda2) {
                $q->whereBetween('waktu_mulai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                    ->orWhereBetween('waktu_selesai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                    ->orWhere(function ($q2) use ($agenda2) {
                        $q2->where('waktu_mulai', '<=', $agenda2->waktu_mulai)
                            ->where('waktu_selesai', '>=', $agenda2->waktu_selesai);
                    });
            })->first();

        if ($pesertaBentrok1) {
            foreach ($agenda1->peserta as $peserta) {
                if ($pesertaBentrok1->peserta->pluck('id')->contains($peserta->id)) {
                    $conflicts[] = "{$peserta->nama_peserta} bentrok di waktu " .
                        Carbon::parse($agenda2->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($agenda2->waktu_selesai)->format('H:i') .
                        " pada Lomba {$pesertaBentrok1->mataLomba->nama_lomba} di waktu " .
                        Carbon::parse($pesertaBentrok1->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($pesertaBentrok1->waktu_selesai)->format('H:i') . ".";
                }
            }
        }

        $peserta2 = $agenda2->peserta->pluck('id');
        $pesertaBentrok2 = Agenda::with('mataLomba')->where('id', '!=', $agenda2->id)
            ->where('jadwal_id', $jadwalId2)
            ->whereHas('peserta', function ($q) use ($peserta2) {
                $q->whereIn('peserta_id', $peserta2);
            })
            ->where(function ($q) use ($agenda1) {
                $q->whereBetween('waktu_mulai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                    ->orWhereBetween('waktu_selesai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                    ->orWhere(function ($q2) use ($agenda1) {
                        $q2->where('waktu_mulai', '<=', $agenda1->waktu_mulai)
                            ->where('waktu_selesai', '>=', $agenda1->waktu_selesai);
                    });
            })->first();

        if ($pesertaBentrok2) {
            foreach ($agenda2->peserta as $peserta) {
                if ($pesertaBentrok2->peserta->pluck('id')->contains($peserta->id)) {
                    $conflicts[] = "{$peserta->nama_peserta} bentrok di waktu " .
                        Carbon::parse($agenda1->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($agenda1->waktu_selesai)->format('H:i') .
                        " pada Lomba {$pesertaBentrok2->mataLomba->nama_lomba} di waktu " .
                        Carbon::parse($pesertaBentrok2->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($pesertaBentrok2->waktu_selesai)->format('H:i') . ".";
                }
            }
        }

        // ❗ Cek bentrok tim
        $tim1 = $agenda1->tim->pluck('id');
        $timBentrok1 = Agenda::with('mataLomba')->where('id', '!=', $agenda1->id)
            ->where('jadwal_id', $jadwalId1)
            ->whereHas('tim', function ($q) use ($tim1) {
                $q->whereIn('tim_id', $tim1);
            })
            ->where(function ($q) use ($agenda2) {
                $q->whereBetween('waktu_mulai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                    ->orWhereBetween('waktu_selesai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                    ->orWhere(function ($q2) use ($agenda2) {
                        $q2->where('waktu_mulai', '<=', $agenda2->waktu_mulai)
                            ->where('waktu_selesai', '>=', $agenda2->waktu_selesai);
                    });
            })->first();

        if ($timBentrok1) {
            foreach ($agenda1->tim as $tim) {
                if ($timBentrok1->tim->pluck('id')->contains($tim->id)) {
                    $conflicts[] = "{$tim->nama_tim} bentrok di waktu " .
                        Carbon::parse($agenda2->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($agenda2->waktu_selesai)->format('H:i') .
                        " pada Lomba {$timBentrok1->mataLomba->nama_lomba} di waktu " .
                        Carbon::parse($timBentrok1->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($timBentrok1->waktu_selesai)->format('H:i') . ".";
                }
            }
        }

        $tim2 = $agenda2->tim->pluck('id');
        $timBentrok2 = Agenda::with('mataLomba')->where('id', '!=', $agenda2->id)
            ->where('jadwal_id', $jadwalId2)
            ->whereHas('tim', function ($q) use ($tim2) {
                $q->whereIn('tim_id', $tim2);
            })
            ->where(function ($q) use ($agenda1) {
                $q->whereBetween('waktu_mulai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                    ->orWhereBetween('waktu_selesai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                    ->orWhere(function ($q2) use ($agenda1) {
                        $q2->where('waktu_mulai', '<=', $agenda1->waktu_mulai)
                            ->where('waktu_selesai', '>=', $agenda1->waktu_selesai);
                    });
            })->first();

        if ($timBentrok2) {
            foreach ($agenda2->tim as $tim) {
                if ($timBentrok2->tim->pluck('id')->contains($tim->id)) {
                    $conflicts[] = "{$tim->nama_tim} bentrok di waktu " .
                        Carbon::parse($agenda1->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($agenda1->waktu_selesai)->format('H:i') .
                        " pada Lomba {$timBentrok2->mataLomba->nama_lomba} di waktu " .
                        Carbon::parse($timBentrok2->waktu_mulai)->format('H:i') . " - " .
                        Carbon::parse($timBentrok2->waktu_selesai)->format('H:i') . ".";
                }
            }
        }


        // ❗ Cek bentrok juri
        if ($agenda1->juri_id) {
            $juriBentrok1 = Agenda::with('mataLomba')->where('id', '!=', $agenda1->id)
                ->where('jadwal_id', $jadwalId1)
                ->where('juri_id', $agenda1->juri_id)
                ->where(function ($q) use ($agenda2) {
                    $q->whereBetween('waktu_mulai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                        ->orWhereBetween('waktu_selesai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                        ->orWhere(function ($q2) use ($agenda2) {
                            $q2->where('waktu_mulai', '<=', $agenda2->waktu_mulai)
                                ->where('waktu_selesai', '>=', $agenda2->waktu_selesai);
                        });
                })->first();

            if ($juriBentrok1) {
                $conflicts[] = "Juri {$agenda1->juri->nama} bentrok di waktu {$agenda2->waktu_mulai->format('H:i')} - {$agenda2->waktu_selesai->format('H:i')} pada Lomba {$juriBentrok1->mataLomba->nama_lomba}.";
            }
        }

        if ($agenda2->juri_id) {
            $juriBentrok2 = Agenda::with('mataLomba')->where('id', '!=', $agenda2->id)
                ->where('jadwal_id', $jadwalId2)
                ->where('juri_id', $agenda2->juri_id)
                ->where(function ($q) use ($agenda1) {
                    $q->whereBetween('waktu_mulai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                        ->orWhereBetween('waktu_selesai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                        ->orWhere(function ($q2) use ($agenda1) {
                            $q2->where('waktu_mulai', '<=', $agenda1->waktu_mulai)
                                ->where('waktu_selesai', '>=', $agenda1->waktu_selesai);
                        });
                })->first();

            if ($juriBentrok2) {
                $conflicts[] = "Juri {$agenda2->juri->nama} bentrok di waktu {$agenda1->waktu_mulai->format('H:i')} - {$agenda1->waktu_selesai->format('H:i')} pada Lomba {$juriBentrok2->mataLomba->nama_lomba}.";
            }
        }

        // if (!empty($conflicts)) {
        //     $pesan = "Terjadi bentrok:\n\n" . implode("\n", $conflicts);
        //     return back()->withInput()->with('error_force', $pesan);
        // }

        if (!$request->filled('force_switch') && count($conflicts) > 0) {
            $message = implode("Terjadi bentrok:\n\n", $conflicts);
            return redirect()->back()
                ->withInput()
                ->with('error_force', $message);
        }

        // ✅ Proses tukar waktu
        $tempMulai = $agenda1->waktu_mulai;
        $tempSelesai = $agenda1->waktu_selesai;

        $agenda1->waktu_mulai = $agenda2->waktu_mulai;
        $agenda1->waktu_selesai = $agenda2->waktu_selesai;
        $agenda1->save();

        $agenda2->waktu_mulai = $tempMulai;
        $agenda2->waktu_selesai = $tempSelesai;
        $agenda2->save();

        return redirect()->route('jadwal.change', ['id' => $agenda1->jadwal_id])
            ->with('success', 'Waktu agenda berhasil ditukar tanpa bentrok.');
    }



    public function constraintPropagation($variabelX, $constraintTambahan, $jadwalHarian)
    {

        Log::info("=== Mulai proses constraint Propagation ===");
        Log::info("Constraint propagation sekarang: " . json_encode($constraintTambahan));
        $domain = [];

        foreach ($variabelX as $var) {
            $kategoriLomba = $var['kategori_lomba'];
            $anggotaList = $var['anggota']; // array of NIM
            $namaTim = $var['nama_tim']; // null kalau individu

            // Log::info("=== Mulai proses kategori lomba: $kategoriLomba ===");

            $mataLomba = MataLomba::where('nama_lomba', $kategoriLomba)->first();
            if (!$mataLomba) {
                // Log::warning("MataLomba tidak ditemukan untuk lomba: $kategoriLomba");
                continue;
            }

            $mataLombaId = $mataLomba->id;
            $venueId = $mataLomba->venue_id;
            if (!$venueId) {
                $randomVenue = Venue::inRandomOrder()->first();
                if ($randomVenue) {
                    $venueId = $randomVenue->id;
                    $constraintSubKategori['venue_tanggal'] = $randomVenue->tanggal_tersedia;
                    $constraintSubKategori['venue_mulai'] = $randomVenue->waktu_mulai_tersedia;
                    $constraintSubKategori['venue_akhir'] = $randomVenue->waktu_berakhir_tersedia;
                }
            }


            $durasi = $mataLomba->durasi ?? 30;
            $savingTime = 0;

            $constraintSubKategori = $constraintTambahan[$mataLombaId] ?? null;

            $venue = Venue::find($venueId);
            if ($venue) {
                $constraintSubKategori['venue_tanggal'] = $venue->tanggal_tersedia;
                $constraintSubKategori['venue_mulai'] = $venue->waktu_mulai_tersedia;
                $constraintSubKategori['venue_akhir'] = $venue->waktu_berakhir_tersedia;
            } elseif (!$constraintSubKategori['venue_tanggal'] ?? true) {
                $venue = Venue::find($venueId);
                if ($venue) {
                    $constraintSubKategori['venue_tanggal'] = $venue->tanggal_tersedia;
                    $constraintSubKategori['venue_mulai'] = $venue->waktu_mulai_tersedia;
                    $constraintSubKategori['venue_akhir'] = $venue->waktu_berakhir_tersedia;
                }
            }


            if ($constraintSubKategori && isset($constraintSubKategori['saving_time']) && is_numeric($constraintSubKategori['saving_time'])) {
                $savingTime = (int) $constraintSubKategori['saving_time'];
            }

            $allSlots = [];

            // Log::debug('Jadwal harian:', $jadwalHarian);

            foreach ($jadwalHarian as $jadwal) {
                // Log::debug('Proses jadwal:', $jadwal);
                $tanggal = $jadwal['tanggal'];
                $mulai = $jadwal['waktu_mulai'];
                $selesai = $jadwal['waktu_selesai'];

                $startDateTime = Carbon::parse("$tanggal $mulai");
                $endDateTime = Carbon::parse("$tanggal $selesai");
                // Log::debug("Start: $startDateTime, End: $endDateTime");

                $slots = $this->generateTimeSlots($startDateTime, $endDateTime, $durasi, $savingTime);

                // Log::debug("Slot yang dihasilkan:", $slots);

                $allSlots = array_merge($allSlots, $slots);
            }

            // Log::debug("Total slot yang dihasilkan: " . count($allSlots));

            // Log::info("Memulai filter slot untuk $mataLomba");
            // Log::info("Constraint tambahan untuk slot sekarang: " . json_encode($constraintSubKategori));

            $filteredSlots = $this->filterSlotsByConstraint($allSlots, $constraintSubKategori);
            // Log::info("Jumlah slot setelah filter constraint: " . count($filteredSlots));

            if (count($filteredSlots) == 0) {
                $filteredSlots = $allSlots;
            }

            // Log::debug("Detail slot sekarang:" . count($filteredSlots));

            $isSerentak = $mataLomba->is_serentak;

            if ($isSerentak) {
                // Key untuk is serentak sama
                $key = $kategoriLomba;
                $domain[$key] = [];

                foreach ($filteredSlots as $slot) {
                    $domain[$key][] = [
                        'tanggal' => $slot['tanggal'],
                        'waktu_mulai' => $slot['waktu_mulai'],
                        'waktu_selesai' => $slot['waktu_selesai'],
                        'kategori_lomba' => $kategoriLomba,
                        'venue' => $venueId,
                        'peserta' => $anggotaList,
                        'nama_tim' => $namaTim,
                    ];
                }
            } elseif ($namaTim) {
                // Non-serentak: kelompok, key per tim
                $key = $kategoriLomba . '-' . $namaTim;
                $domain[$key] = [];

                foreach ($filteredSlots as $slot) {
                    $domain[$key][] = [
                        'tanggal' => $slot['tanggal'],
                        'waktu_mulai' => $slot['waktu_mulai'],
                        'waktu_selesai' => $slot['waktu_selesai'],
                        'kategori_lomba' => $kategoriLomba,
                        'venue' => $venueId,
                        'peserta' => $anggotaList,
                        'nama_tim' => $namaTim,
                    ];
                }
            } else {
                // Non-serentak: individu, key per nim
                foreach ($anggotaList as $nim) {
                    $key = $kategoriLomba . '-' . $nim;
                    $domain[$key] = [];

                    foreach ($filteredSlots as $slot) {
                        $domain[$key][] = [
                            'tanggal' => $slot['tanggal'],
                            'waktu_mulai' => $slot['waktu_mulai'],
                            'waktu_selesai' => $slot['waktu_selesai'],
                            'kategori_lomba' => $kategoriLomba,
                            'venue' => $venueId,
                            'peserta' => [$nim],
                        ];
                    }
                }
            }
        }

        Log::info("Selesai constraint propagation");

        // dd($domain);
        return $domain;
    }


    private function filterSlotsByConstraint(array $slots, ?array $constraintTambahan): array
    {
        if (!$constraintTambahan) {
            // Log::debug("Tidak ada constraint, semua slot dikembalikan.");
            return $slots;
        }

        $hariConstraint = $constraintTambahan['hari'] ?? null; // ['Monday', 'Wednesday']
        $waktuMulaiConstraint = $constraintTambahan['waktu_mulai'] ?? null;
        $waktuSelesaiConstraint = $constraintTambahan['waktu_selesai'] ?? null;

        // Log::debug("Memulai filter slot dengan constraint: hari=" . json_encode($hariConstraint) . ", mulai=$waktuMulaiConstraint, selesai=$waktuSelesaiConstraint");

        // constraint dari venue
        $venueTanggal = $constraintTambahan['venue_tanggal'] ?? null;
        $venueMulai = $constraintTambahan['venue_mulai'] ?? null;
        $venueAkhir = $constraintTambahan['venue_akhir'] ?? null;

        // Log::debug("Memulai filter slot dengan venue: " . json_encode($venueTanggal) . ", mulai=$venueMulai, selesai=$venueAkhir");

        $filtered = [];

        foreach ($slots as $index => $slot) {
            // Log::debug("🔍 Slot ke-$index: tanggal=$slotDate, mulai=$startTime, selesai=$endTime");

            // Log::debug("Slot ke-$index sebelum parse: waktu_mulai = {$slot['waktu_mulai']}, waktu_selesai = {$slot['waktu_selesai']}");

            if (!isset($slot['waktu_mulai']) || !isset($slot['waktu_selesai'])) {
                // Log::debug("Slot ke-$index error: waktu_mulai atau waktu_selesai tidak ada");
                continue;
            }

            try {
                $slotStart = Carbon::parse($slot['waktu_mulai']);
                // Log::debug("CEK SLOT START: $slotStart");
                $slotEnd = Carbon::parse($slot['waktu_selesai']);
            } catch (\Exception $e) {
                // Log::debug("Slot ke-$index gagal parse waktu: " . $e->getMessage());
                continue;
            }

            // $slotDate = Carbon::parse($slot['waktu_mulai'])->format('Y-m-d');

            $slotDate = $slot['tanggal'];

            $startTime = $slotStart->format('H:i');
            $endTime = $slotEnd->format('H:i');

            // Log::debug("Memeriksa slot ke-$index: {$slotStart} - {$slotEnd} (Hari: $slotDate , $startTime - $endTime)");

            if (!empty($hariConstraint) && is_array($hariConstraint) && !in_array(null, $hariConstraint, true)) {
                if (!in_array($slotDate, $hariConstraint)) {
                    // Log::debug("Slot ke-$index ditolak: tanggal $slotDate tidak termasuk hari constraint.");
                    continue;
                }
            }

            // constraint waktu mulai (jika ada)
            if ($waktuMulaiConstraint !== null) {
                if ($startTime < $waktuMulaiConstraint) {
                    // Log::debug("Slot ke-$index ditolak: mulai $startTime < batas $waktuMulaiConstraint.");
                    continue;
                }
            }

            // constraint waktu selesai (jika ada)
            if ($waktuSelesaiConstraint !== null) {
                if ($endTime > $waktuSelesaiConstraint) {
                    // Log::debug("Slot ke-$index ditolak: selesai $endTime > batas $waktuSelesaiConstraint.");
                    continue;
                }
            }

            // constraint venue
            if ($venueTanggal !== null) {
                if ($slotDate !== $venueTanggal) {
                    // Log::debug("Slot ke-$index ditolak: tanggal $slotDate ≠ venueTanggal $venueTanggal");
                    continue;
                }
            }

            if ($venueMulai !== null) {
                if ($startTime < $venueMulai) {
                    // Log::debug("Slot ke-$index ditolak: jam mulai $startTime < venueMulai $venueMulai");
                    continue;
                }
            }

            if ($venueAkhir !== null) {
                if ($endTime > $venueAkhir) {
                    // Log::debug("Slot ke-$index ditolak: jam selesai $endTime > venueAkhir $venueAkhir");
                    continue;
                }
            }

            // Log::debug("Slot diterima.");
            $filtered[] = $slot;
        }

        // Log::info("Total slot setelah filter constraint: " . count($filtered));
        return $filtered;
    }



    private function generateTimeSlots($startTime, $endTime, $durasi, $savingTime = 0)
    {
        $slots = [];
        $current = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        while ($current < $end) {
            $slotStart = $current->copy();
            $slotEnd = $slotStart->copy()->addMinutes($durasi);

            if ($slotEnd > $end) {
                break;
            }

            $slots[] = [
                'tanggal' => $slotStart->toDateString(),          // tetap simpan tanggal terpisah
                'waktu_mulai' => $slotStart->format('H:i'),      // hanya jam:menit
                'waktu_selesai' => $slotEnd->format('H:i'),      // hanya jam:menit
            ];

            $current = $slotEnd->copy()->addMinutes($savingTime);
        }

        return $slots;
    }

    public function getStatus()
    {
        $jadwals = Jadwal::orderBy('created_at', 'desc')->get();
        return response()->json($jadwals);
    }

    private function ambilWaktuSelesai(string $kategoriKey, string $tanggal, $constraintTambahan, $jadwalHarian): ?string
    {
        $kategoriUtama = explode('-', $kategoriKey)[0];

        // Berdasarkan venue
        $subKategori = MataLomba::where('nama_lomba', $kategoriUtama)->first();
        if ($subKategori && $subKategori->venue_id) {
            $venue = Venue::find($subKategori->venue_id);
            if ($venue && $venue->waktu_berakhir_tersedia) {
                return $venue->waktu_berakhir_tersedia;
            }
        }

        // Berdasarkan constraint tambahan
        $constraintSubKategori = $constraintTambahan[$kategoriKey] ?? null;
        if ($constraintSubKategori && isset($constraintSubKategori['waktu_selesai'])) {
            return $constraintSubKategori['waktu_selesai'];
        }

        // Berdasarkan max dari jadwal harian
        $jadwalHariTertentu = collect($jadwalHarian)->where('tanggal', $tanggal);
        if ($jadwalHariTertentu->count() > 0) {
            return $jadwalHariTertentu->pluck('waktu_selesai')->sortDesc()->first();
        }

        return null;
    }

    public function backtrack($domain, $maxSolutions = 3, $constraintTambahan = [], $jadwalId = null, $jadwalHarian = [])
    {
        $startTime = microtime(true); // ⏱️ mulai stopwatch
        $solutions = [];
        $totalDepth = count(array_keys($domain)); // total jumlah kategori
        Log::info("Memulai proses backtracking (multi solusi)");

        $originalKategoriKeys = array_keys($domain);

        // Tambahkan log isi domain
        // foreach ($domain as $key => $slots) {
        //     Log::info("Domain [$key] memiliki " . count($slots) . " slot:");
        //     foreach ($slots as $index => $slot) {
        //         Log::info("[$key][$index] => " . json_encode([
        //             'Tanggal' => $slot['tanggal'],
        //             'Mulai' => $slot['waktu_mulai'],
        //             'Selesai' => $slot['waktu_selesai'],
        //             'Venue' => $slot['venue'],
        //             'Peserta' => $slot['peserta'], // array oke
        //             'Tim' => $slot['nama_tim'] ?? '-', // bisa array atau string
        //         ], JSON_PRETTY_PRINT));
        //     }
        // }

        $solutions = [];
        $gagalKarenaSlotTidakMemenuhiConstraint = [];
        $tanggalYangSelaluGagal = [];

        for ($attempt = 0; $attempt < $maxSolutions; $attempt++) {
            Log::info("🔁 Memulai variasi solusi ke-" . ($attempt + 1));

            $elapsed = microtime(true) - $startTime;
            if ($elapsed > 60) {
                Log::warning("Waktu backtracking melebihi 1 menit. Dihentikan pada attempt ke-$attempt.");

                if ($attempt === 0 || count($solutions) === 0) {
                    // Tidak ada solusi sama sekali
                    $penyebab = $this->analisaKegagalan($domain, $gagalKarenaSlotTidakMemenuhiConstraint, $tanggalYangSelaluGagal);
                    return ['error' => $penyebab];
                } else {
                    // Simpan solusi yang sudah ditemukan
                    return $solutions;
                }
            } else {
                // Log::info("Waktu saat ini: $elapsed");
            }

            $shuffledDomain = $domain;

            // Group berdasarkan kategori utama
            $groupedKategori = [];
            foreach ($originalKategoriKeys as $key) {
                $kategoriUtama = explode('-', $key)[0];
                $groupedKategori[$kategoriUtama][] = $key;
            }

            // Pisahkan ke kelompok prioritas dan non-prioritas
            $prioritasKategoriUtama = [];
            $belakangKategoriUtama = [];

            foreach (array_keys($groupedKategori) as $kategoriUtama) {
                $memilikiConstraint = false;

                foreach ($groupedKategori[$kategoriUtama] as $key) {
                    if (isset($constraintTambahan[$key]) && !empty($constraintTambahan[$key])) {
                        $memilikiConstraint = true;
                        break;
                    }
                }

                if ($memilikiConstraint) {
                    $prioritasKategoriUtama[] = $kategoriUtama;
                } else {
                    $belakangKategoriUtama[] = $kategoriUtama;
                }
            }

            // Log::info("[$attempt] Kelompok prioritas: " . json_encode($prioritasKategoriUtama));
            // Log::info("[$attempt] Kelompok belakang: " . json_encode($belakangKategoriUtama));

            // Shuffle keduanya secara terpisah
            mt_srand($attempt * 100 + 1);
            shuffle($prioritasKategoriUtama);
            shuffle($belakangKategoriUtama);
            mt_srand();

            // Gabungkan menjadi urutan akhir
            $kategoriUtamaList = array_merge($prioritasKategoriUtama, $belakangKategoriUtama);

            // Log::info("[$attempt] Sebelum acak/rotasi: kategoriUtamaList => " . json_encode($kategoriUtamaList));

            // cek venue untuk setiap kategori utama
            $kategoriVenueMap = [];
            foreach ($kategoriUtamaList as $kategoriUtama) {
                $firstKey = $groupedKategori[$kategoriUtama][0];
                $venue = $shuffledDomain[$firstKey][0]['venue'];
                $kategoriVenueMap[$kategoriUtama] = $venue;
            }

            $venueGroupedKategori = [];
            foreach ($kategoriVenueMap as $kategoriUtama => $venue) {
                $venueGroupedKategori[$venue][] = $kategoriUtama;
            }

            // Log::info("[$attempt] Grup berdasarkan venue: " . json_encode($venueGroupedKategori));

            $shuffledKategoriUtamaList = [];

            foreach ($venueGroupedKategori as $venue => $kategoriList) {
                mt_srand($attempt + intval($venue));
                shuffle($kategoriList); // acak

                if (count($kategoriList) > 2 && isset($firstKategoriHistory[$venue])) {
                    $previousFirst = $firstKategoriHistory[$venue];
                    if ($kategoriList[0] === $previousFirst) {
                        foreach ($kategoriList as $i => $kategori) {
                            if ($kategori !== $previousFirst) {
                                $kategoriList[$i] = $kategoriList[0];
                                $kategoriList[0] = $kategori;
                                break;
                            }
                        }
                    }
                }

                $firstKategoriHistory[$venue] = $kategoriList[0];

                $shuffledKategoriUtamaList = array_merge($shuffledKategoriUtamaList, $kategoriList);
                // Log::info("[$attempt] Setelah shuffle venue $venue: " . json_encode($kategoriList));
            }
            mt_srand();

            $kategoriUtamaList = $shuffledKategoriUtamaList;

            // Log final
            // Log::info("[$attempt] Final kategoriUtamaList setelah shuffle per venue => " . json_encode($kategoriUtamaList));

            // Susun ulang kategoriKeys
            $kategoriKeys = [];
            foreach ($kategoriUtamaList as $kategoriUtama) {
                $kategoriGroup = $groupedKategori[$kategoriUtama];

                // Log::info("[$attempt] Grup kategori utama: $kategoriUtama => " . json_encode($kategoriGroup));

                mt_srand(crc32($attempt . $kategoriUtama));
                shuffle($kategoriGroup);
                mt_srand();

                // (opsional) urutkan lagi berdasarkan jam mulai
                usort($kategoriGroup, function ($a, $b) use ($shuffledDomain) {
                    $slotA = $shuffledDomain[$a][0];
                    $slotB = $shuffledDomain[$b][0];

                    $startA = strtotime($slotA['tanggal'] . ' ' . $slotA['waktu_mulai']);
                    $startB = strtotime($slotB['tanggal'] . ' ' . $slotB['waktu_selesai']);

                    return $startA <=> $startB;
                });

                foreach ($kategoriGroup as $key) {
                    $kategoriKeys[] = $key;
                }
            }

            $currentSolution = [];

            $gagalKarenaSlotTidakMemenuhiConstraint = [];
            $backtrackRecursive = function ($depth, &$currentSolution) use (&$backtrackRecursive, $shuffledDomain, $kategoriKeys, &$solutions, $maxSolutions, &$gagalKarenaSlotTidakMemenuhiConstraint, &$tanggalYangSelaluGagal, $jadwalId, $totalDepth, $constraintTambahan, $jadwalHarian, $attempt) {
                if ($jadwalId && $totalDepth > 0) {
                    $jadwal = Jadwal::find($jadwalId);
                    if ($jadwal) {
                        $progress = intval(($depth / $totalDepth) * 100);
                        $jadwal->update([
                            'progress' => $progress,
                            'status'   => 'Menunggu (Vers ' . ($attempt + 1) . ')'
                        ]);
                    }
                }


                if ($depth === count($kategoriKeys)) {
                    $solutions[] = $currentSolution;

                    $versionNumber = count($solutions);
                    // Log::info("Solusi ke-{$versionNumber}: " . json_encode($currentSolution));

                    //Berhenti hanya jika solusi sudah cukup
                    return true;
                }

                $currentKey = $kategoriKeys[$depth];
                $slots = $shuffledDomain[$currentKey];

                $validDitemukan = false;
                $tanggalSlot = [];
                foreach ($slots as $slot) {
                    $tanggalSlot[] = $slot['tanggal'];
                    // Log::debug("[Depth $depth] Mencoba slot untuk $currentKey pada {$slot['tanggal']} {$slot['waktu_mulai']} - {$slot['waktu_selesai']}");
                    if ($this->checkConstraint($slot, $currentSolution)) {
                        $currentSolution[] = $slot;
                        $validDitemukan = true;
                        // Log::debug("✅ [Depth $depth] Slot diterima untuk $currentKey. Melanjutkan...");

                        $shouldStop = $backtrackRecursive($depth + 1, $currentSolution);
                        array_pop($currentSolution);

                        if ($shouldStop) {
                            return true;
                        }

                        break; // tetap cuma satu slot
                    } else {
                        // Log::debug("[Depth $depth] Slot ditolak untuk $currentKey karena constraint.");
                    }
                }

                Log::info("Nilai valid ditemukan untuk $currentKey adalah $validDitemukan");

                if (!$validDitemukan) {
                    $gagalKarenaSlotTidakMemenuhiConstraint[] = $currentKey;

                    foreach ($slots as $slot) {
                        $tgl = $slot['tanggal'];
                        $venue = $slot['venue'];
                        $kategoriUtama = explode('-', $currentKey)[0];
                        $waktuSelesaiTarget = $this->ambilWaktuSelesai($currentKey, $tgl, $constraintTambahan, $jadwalHarian);

                        $tanggalYangSelaluGagal[$tgl]['kategori'][] = $currentKey;

                        // Log::warning("Tidak ditemukan slot valid untuk $currentKey pada $tgl di venue $venue");
                        // Log::debug("Slot terakhir dicoba: selesai hingga $waktuSelesaiTarget");

                        // Cek apakah masih ada kategori lain dengan kategori utama yang sama yang belum dijadwalkan
                        $masihAdaYangBelum = false;
                        $masihAdaYangBelum = false;
                        foreach ($kategoriKeys as $depth2 => $key2) {
                            if ($depth2 >= $depth && strpos($key2, $kategoriUtama) === 0) {
                                $masihAdaYangBelum = true;
                                // Log::debug("Masih ada kategori $key2 dari $kategoriUtama yang belum dicoba (depth $depth2)");
                                break;
                            }
                        }

                        // Ambil yang terisi sebelumnya
                        $terisiVenueSama = array_filter(
                            $currentSolution,
                            fn($s) => $s['tanggal'] === $tgl && $s['venue'] === $venue
                        );

                        foreach ($terisiVenueSama as $isi) {
                            $kategoriIsi = $isi['kategori_lomba'] ?? '(tidak diketahui)';
                            $jamSelesaiIsi = $isi['waktu_selesai'] ?? '-';
                            // Log::debug("Venue $venue pada $tgl sudah diisi oleh $kategoriIsi sampai $jamSelesaiIsi");

                            $tanggalYangSelaluGagal[$tgl]['terisi'][] = $kategoriIsi;
                        }

                        if ($masihAdaYangBelum && !empty($terisiVenueSama)) {
                            $latestSelesai = collect($terisiVenueSama)
                                ->map(fn($s) => $s['waktu_selesai'])
                                ->sortDesc()
                                ->first();

                            $penyebabDetail = "Kategori $kategoriUtama tidak bisa dijadwalkan semuanya sebelum waktu selesai ($waktuSelesaiTarget)";

                            $tanggalYangSelaluGagal[$tgl]['penyebab_lanjutan'][$kategoriUtama] = $penyebabDetail;

                            // Log::warning("Alasan lanjutan untuk $kategoriUtama pada $tgl: $penyebabDetail");
                        } elseif ($masihAdaYangBelum) {
                            // Log::info("Masih ada $kategoriUtama lainnya tapi venue belum pernah terisi di tanggal $tgl, jadi penyebab belum bisa disimpulkan.");
                        }
                    }
                }


                return false;
            };
            //Tidak perlu cek count lagi di sini, karena 1 solusi sudah cukup untuk 1 attempt
            $result = $backtrackRecursive(0, $currentSolution);

            if ($result) {
                // Log::info("✅ [Attempt $attempt] Berhasil menghasilkan solusi.");
                continue;
            } else {
                // Log::warning("[Attempt $attempt] Gagal menemukan solusi.");

                if ($attempt === 0) {
                    $penyebab = $this->analisaKegagalan($domain, $gagalKarenaSlotTidakMemenuhiConstraint, $tanggalYangSelaluGagal);
                    Log::warning("Backtracking gagal pada solusi pertama. Tidak ada solusi valid sama sekali.");
                    return ['error' => $penyebab];
                } else {
                    Log::info("Gagal di attempt ke-$attempt, tapi solusi pertama sudah ditemukan. Solusi tetap disimpan.");
                }
            }
        }
        Log::info("[$attempt] Final kategoriKeys: " . json_encode($kategoriKeys));

        if (count($solutions) > 0) {
            Log::info("Backtracking berhasil menemukan " . count($solutions) . " solusi.");
            Log::info("Total solusi berhasil ditemukan: " . count($solutions) . " dari $maxSolutions percobaan.");
            return $solutions;
        }

        $penyebab = $this->analisaKegagalan($domain, $gagalKarenaSlotTidakMemenuhiConstraint, $tanggalYangSelaluGagal);
        Log::warning("Backtracking gagal: $penyebab");

        return ['error' => $penyebab];
    }

    private function analisaKegagalan($domain, $gagalSlotTidakCocok = [], $tanggalGagal = [])
    {
        $kategoriPesertaMap = [];

        foreach ($domain as $kategori => $slots) {
            if (count($slots) === 0) {
                $logDetail[] = "❌ Kategori \"$kategori\" tidak memiliki slot valid sama sekali.";
            } else {
                $logDetail[] = "✅ Kategori \"$kategori\" memiliki " . count($slots) . " slot.";

                $dates = array_unique(array_map(fn($slot) => $slot['tanggal'], $slots));
                if (count($dates) === 1) {
                    $logDetail[] = "⚠️ Semua slot kategori \"$kategori\" berada di tanggal yang sama: {$dates[0]}";
                }

                foreach ($slots as $slot) {
                    foreach ($slot['peserta'] as $p) {
                        $kategoriPesertaMap[$p][] = $kategori;
                    }
                }

                if (count($slots) <= 2) {
                    $logDetail[] = "⚠️ Kategori \"$kategori\" hanya memiliki " . count($slots) . " slot → rawan gagal.";
                }
            }
        }

        $pesertaTerlaluSering = array_filter($kategoriPesertaMap, fn($kategoriList) => count(array_unique($kategoriList)) >= 3);
        if (!empty($pesertaTerlaluSering)) {
            foreach ($pesertaTerlaluSering as $peserta => $daftarKategori) {
                $logDetail[] = "⚠️ Peserta \"$peserta\" muncul di banyak kategori: " . implode(', ', array_unique($daftarKategori));
            }
        }

        // ⏬ Ringkas info per kategori
        if (!empty($tanggalGagal)) {
            $groupByKategori = [];

            foreach ($tanggalGagal as $tgl => $data) {
                if (!isset($data['kategori']) || !is_array($data['kategori']))
                    continue;

                $kategoriNamaList = array_map(fn($item) => explode('-', $item)[0], $data['kategori']);
                $kategoriNamaList = array_unique($kategoriNamaList);

                foreach ($kategoriNamaList as $kategori) {
                    $groupByKategori[$kategori]['tanggal'][] = $tgl;

                    $terisiNama = isset($data['terisi']) && is_array($data['terisi'])
                        ? array_map(fn($item) => explode('-', $item)[0], $data['terisi'])
                        : [];

                    $terisiNama = array_map(fn($item) => explode('-', $item)[0], $data['terisi'] ?? []);

                    $kategoriUtamaYangDitolak = $kategori;
                    $terisiNamaFinal = [];

                    foreach ($terisiNama as $namaTerisi) {
                        if ($namaTerisi === $kategoriUtamaYangDitolak) {
                            $terisiNamaFinal[] = "🟡 $namaTerisi";
                        } else {
                            $terisiNamaFinal[] = $namaTerisi;
                        }
                    }

                    $groupByKategori[$kategori]['terisi'][$tgl] = array_unique($terisiNamaFinal);


                    if (isset($data['penyebab_lanjutan'])) {
                        foreach ($data['penyebab_lanjutan'] as $kategoriKey => $penyebab) {
                            $kategoriName = explode('-', $kategoriKey)[0];
                            if (str_contains($penyebab, 'waktu selesai')) {
                                preg_match('/\((\d{2}:\d{2})\)/', $penyebab, $matches);
                                $jam = $matches[1] ?? null;
                                if ($jam) {
                                    $groupByKategori[$kategoriName]['penyebab'][$tgl] = $jam;
                                }
                            }
                        }
                    }
                }
            }

            $logDetail = [];

            foreach ($groupByKategori as $kategori => $info) {
                $tanggalList = implode(', ', array_unique($info['tanggal']));
                $logDetail[] = "<strong>📆 Tanggal $tanggalList:</strong>";
                $logDetail[] = "Slot ditolak untuk kategori: $kategori";

                if (!empty($info['terisi'])) {
                    $logDetail[] = "Sudah terisi oleh:";
                    foreach ($info['terisi'] as $tgl => $isi) {
                        $logDetail[] = "  • $tgl: " . implode(', ', $isi);
                    }
                }

                if (isset($info['penyebab']) && is_array($info['penyebab'])) {
                    $lines = [];
                    foreach ($info['penyebab'] as $tgl => $jam) {
                        $lines[] = "  • $tgl → $jam";
                    }
                    $logDetail[] = "💡 Kategori $kategori tidak bisa dijadwalkan semuanya sebelum waktu selesai:\n" . implode("\n", $lines);
                } elseif (isset($info['penyebab'])) {
                    $logDetail[] = "💡 " . $info['penyebab'];
                }


                $logDetail[] = ""; // baris kosong antar kategori
            }

            // Tambahkan saran satu kali di akhir
            if (!empty($logDetail)) {
                $logDetail[] = "🟡 = sebagian kategori sudah dijadwalkan, tapi masih ada yang gagal.";
                $logDetail[] = "Saran: Ubah durasi atau waktu lomba.";
                $pesan = implode("\n", $logDetail);
                Log::debug("📊 Detail analisa kegagalan:\n" . $pesan);
                return $pesan;
            }
        }

        if (!empty($gagalSlotTidakCocok)) {
            $daftar = implode(', ', $gagalSlotTidakCocok);
            return "Semua slot untuk kategori berikut tidak lolos constraint waktu/peserta/venue: $daftar\nSaran: Ubah durasi atau waktu lomba.";
        }

        $emptyKategori = array_filter($domain, fn($slots) => count($slots) === 0);
        if (count($emptyKategori) > 0) {
            $daftarKosong = implode(', ', array_keys($emptyKategori));
            return "Kategori berikut tidak memiliki slot valid: $daftarKosong\nSaran: Ubah durasi atau waktu lomba.";
        }

        $slotCounts = array_map(fn($slots) => count($slots), $domain);
        $totalSlot = array_sum($slotCounts);

        if ($totalSlot === 0) {
            return "Semua kemungkinan habis atau tidak sesuai dengan constraint.\nSaran: Ubah durasi atau waktu lomba.";
        }

        $kategoriTerbanyakSlot = array_keys($slotCounts, max($slotCounts))[0];
        $kategoriTersedikitSlot = array_keys($slotCounts, min($slotCounts))[0];

        return "Kemungkinan bentrok antar peserta terlalu padat. Kategori dengan slot terbanyak: \"$kategoriTerbanyakSlot\" (" . max($slotCounts) . " slot), dan tersedikit: \"$kategoriTersedikitSlot\" (" . min($slotCounts) . " slot).\nSaran: Ubah durasi atau waktu lomba.";
    }




    private function checkConstraint($slot, $assignment)
    {
        $start = Carbon::parse($slot['tanggal'] . ' ' . $slot['waktu_mulai']);
        $end = Carbon::parse($slot['tanggal'] . ' ' . $slot['waktu_selesai']);

        $bufferTimeInMinutes = 60;

        // Filter hanya assignment di hari yang sama
        $sameDayAssignments = array_filter($assignment, function ($assignedSlot) use ($slot) {
            return $assignedSlot['tanggal'] === $slot['tanggal'];
        });

        foreach ($sameDayAssignments as $assignedSlot) {
            $assignedStart = Carbon::parse($assignedSlot['tanggal'] . ' ' . $assignedSlot['waktu_mulai']);
            $assignedEnd = Carbon::parse($assignedSlot['tanggal'] . ' ' . $assignedSlot['waktu_selesai']);

            $overlap = $start->lt($assignedEnd) && $end->gt($assignedStart);

            $pesertaIntersect = array_intersect($slot['peserta'], $assignedSlot['peserta']);

            if (!empty($pesertaIntersect)) {
                if ($overlap) {
                    return false;
                }

                if ($slot['kategori_lomba'] !== $assignedSlot['kategori_lomba']) {
                    $gap1 = abs($start->diffInMinutes($assignedEnd, false));
                    $gap2 = abs($assignedStart->diffInMinutes($end, false));

                    if ($gap1 < $bufferTimeInMinutes && $gap2 < $bufferTimeInMinutes) {
                        return false;
                    }
                }
            }

            if ($overlap && $slot['venue'] === $assignedSlot['venue']) {
                return false;
            }
        }

        // ✅ Constraint tambahan: waktu harus berlanjut jika venue sudah ada sebelumnya
        // ✅ Constraint tambahan: waktu harus berlanjut jika venue sudah ada sebelumnya DI TANGGAL YANG SAMA
        $sameVenueAssignments = array_filter($assignment, function ($s) use ($slot) {
            return $s['venue'] === $slot['venue'] && $s['tanggal'] === $slot['tanggal'];
        });

        if (!empty($sameVenueAssignments)) {
            $lastEndTime = collect($sameVenueAssignments)
                ->map(fn($s) => Carbon::parse($s['tanggal'] . ' ' . $s['waktu_selesai']))
                ->max();

            if (!$start->greaterThanOrEqualTo($lastEndTime)) {
                // Log::debug("⛔ [Constraint Venue] Slot {$slot['kategori_lomba']} dimulai sebelum agenda sebelumnya selesai ($lastEndTime) di venue yang sama.");
                return false;
            }
        }



        return true;
    }


    private function hasRemainingSlotForOtherCategories($currentKey, $slot, $remainingKeys, $shuffledDomain, $assignment)
    {
        foreach ($remainingKeys as $key) {
            if (explode('-', $key)[0] === explode('-', $currentKey)[0]) {
                $foundValid = false;
                foreach ($shuffledDomain[$key] as $candidateSlot) {
                    if ($candidateSlot !== $slot && $this->checkConstraint($candidateSlot, $assignment)) {
                        $foundValid = true;
                        break;
                    }
                }
                if (!$foundValid) {
                    return false; // tidak ada slot tersisa untuk kategori lain yang sama
                }
            }
        }

        return true;
    }

    public function generateVariabelX()
    {
        $variabelX = $this->processPesertaKategoriLomba();

        Log::info('Variabel X berhasil dibuat (berdasarkan peserta dan kategori)', ['variabelX' => $variabelX]);

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'Variabel X berhasil dibuat',
        //     'data' => $variabelX
        // ]);

        return $variabelX;
    }
    private function processSubKategoriLomba()
    {
        $eventId = session('jadwal_event_id'); // ambil event id dari session

        // 1. Ambil peserta_id yang sudah membayar
        $pesertaIds = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id');

        // 2. Ambil data pendaftar sesuai event, beserta relasi mataLomba
        $pendaftar = Pendaftar::with('mataLomba.kategori')
            ->whereIn('peserta_id', $pesertaIds)
            ->whereHas('mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->get();

        // 3. Ambil mata_lomba_id unik
        $mataLombaIds = $pendaftar->pluck('mata_lomba_id')->unique()->values();

        // 4. Bangun list hasil
        $mataLombaList = [];

        foreach ($mataLombaIds as $id) {
            $mataLomba = $pendaftar->firstWhere('mata_lomba_id', $id)?->mataLomba;

            if (!$mataLomba || $mataLomba->kategori?->event_id != $eventId) {
                continue; // skip mata lomba yang bukan bagian dari event ini
            }

            $mataLombaList[] = [
                'mata_lomba_id' => $id,
                'nama_mata_lomba' => $mataLomba ? $mataLomba->nama_lomba : 'Tidak Diketahui',
            ];
        }

        return $mataLombaList;
    }


    public function processPesertaKategoriLomba()
    {
        $eventId = session('jadwal_event_id'); // pastikan ini ada ya
        $result = [];
        $timMap = [];

        $pesertaMembayarIds = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id')->toArray();

        // 🔍 ambil tim yang anggotanya bayar DAN event_id sesuai
        $timList = Tim::with(['peserta.pendaftar.mataLomba.kategori'])
            ->whereHas('peserta.pendaftar.mataLomba.kategori', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->whereHas('peserta', function ($query) use ($pesertaMembayarIds) {
                $query->whereIn('peserta.id', $pesertaMembayarIds);
            })
            ->get();

        foreach ($timList as $tim) {
            $anggotaValid = $tim->peserta->filter(function ($anggota) use ($pesertaMembayarIds, $eventId) {
                $mataLomba = $anggota->pendaftar?->mataLomba;
                $kategori = $mataLomba?->kategori;
                return in_array($anggota->id, $pesertaMembayarIds) && $kategori?->event_id == $eventId;
            });

            if ($anggotaValid->isEmpty())
                continue;

            $mataLomba = $anggotaValid->first()->pendaftar?->mataLomba;
            if (!$mataLomba)
                continue;

            $key = $mataLomba->nama_lomba;
            $isSerentak = $mataLomba->is_serentak;

            if ($isSerentak) {
                $timMap[$key]['kategori_lomba'] = $mataLomba->nama_lomba;
                $timMap[$key]['nama_tim'][] = $tim->nama_tim;
                $timMap[$key]['anggota'] = array_merge(
                    $timMap[$key]['anggota'] ?? [],
                    $anggotaValid->pluck('nim')->toArray()
                );
                $timMap[$key]['nama_tim'] = array_unique($timMap[$key]['nama_tim']);
                $timMap[$key]['anggota'] = array_unique($timMap[$key]['anggota']);
            } else {
                $keyTim = $key . '-' . $tim->nama_tim;
                $timMap[$keyTim]['kategori_lomba'] = $mataLomba->nama_lomba;
                $timMap[$keyTim]['nama_tim'] = $tim->nama_tim;
                $timMap[$keyTim]['anggota'] = $anggotaValid->pluck('nim')->toArray();
            }
        }

        // 🔍 Peserta individu
        $pesertaIndividu = Peserta::with('pendaftar.mataLomba.kategori')
            ->whereDoesntHave('tim')
            ->whereIn('id', $pesertaMembayarIds)
            ->whereHas('pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->get();

        foreach ($pesertaIndividu as $pesertaItem) {
            $mataLomba = $pesertaItem->pendaftar?->mataLomba;
            if (!$mataLomba || $mataLomba->kategori?->event_id != $eventId)
                continue;

            $key = $mataLomba->nama_lomba;
            $isSerentak = $mataLomba->is_serentak;

            if ($isSerentak) {
                $timMap[$key]['kategori_lomba'] = $mataLomba->nama_lomba;
                $timMap[$key]['nama_tim'] = null;
                $timMap[$key]['anggota'][] = $pesertaItem->nim;
            } else {
                $key .= '-' . $pesertaItem->nim;
                $timMap[$key] = [
                    'kategori_lomba' => $mataLomba->nama_lomba,
                    'nama_tim' => null,
                    'anggota' => [$pesertaItem->nim],
                ];
            }
        }

        $result = array_values($timMap);
        return $result;
    }



    public function destroy($id)
    {
        $agenda = Agenda::findOrFail($id);
        $agenda->delete();

        return redirect()->back()->with('success', 'Agenda berhasil dihapus.');
    }

    public function destroyJadwal($id)
    {
        try {
            $jadwal = Jadwal::findOrFail($id);
            $eventId = $jadwal->event_id; // ambil dari jadwal biar gak null

            $jadwal->agendas()->delete();
            $jadwal->delete();

            return redirect()->route('jadwal.index', ['event' => $eventId])
                ->with('success', 'Jadwal dan agenda terkait berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal hapus jadwal', ['jadwal_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus jadwal');
        }
    }
}
