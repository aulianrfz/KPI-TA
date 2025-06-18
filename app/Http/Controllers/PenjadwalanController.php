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

    public function index()
    {
        $query = Jadwal::with(['agendas']);

        // Filter hanya berdasarkan salah satu: tahun atau sort=status
        if (request()->has('tahun')) {
            $query->where('tahun', request('tahun'))
                ->orderBy('created_at', 'desc'); // Jika filter tahun, tetap urut berdasarkan waktu
        } elseif (request('sort') === 'status') {
            $query->orderByRaw("FIELD(status, 'Selesai', 'Menunggu', 'Gagal')");
        }
        // else: jangan kasih order apapun → default dari database

        $jadwals = $query->get();

        $availableYears = Jadwal::select('tahun')->distinct()->pluck('tahun');

        return view('jadwal.index', compact('jadwals', 'availableYears'));
    }





    // public function event()
    // {
    //     $events = Event::all();
    //     return view('jadwal.event', compact('events'));
    // }

    // public function index($event_id)
    // {
    //     $jadwals = Jadwal::where('event_id', $event_id)->get();

    //     $event = Event::findOrFail($event_id);

    //     return view('jadwal.index', compact('jadwals', 'event'));
    // }

    public function change($id)
    {
        $jadwalMaster = Jadwal::findOrFail($id);
        $sortBy = request()->query('sort_by');
        $tanggalFilter = request()->query('tanggal');

        $agendasQuery = Agenda::select('agenda.*')
            ->join('mata_lomba', 'agenda.mata_lomba_id', '=', 'mata_lomba.id')
            ->join('venue', 'agenda.venue_id', '=', 'venue.id')
            ->where('jadwal_id', $id)
            ->with(['mataLomba', 'venue', 'peserta', 'juri', 'tim']);

        // Filter berdasarkan tanggal jika ada
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

        // Ambil semua tanggal unik dari agenda untuk dropdown filter
        $availableDates = Agenda::where('jadwal_id', $id)
            ->selectRaw('DATE(tanggal) as tanggal')
            ->distinct()
            ->orderBy('tanggal')
            ->pluck('tanggal');

        $searchQuery = request()->query('search_query');

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


        $agendas = $agendasQuery->paginate(10)->withQueryString();

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

        // Ambil semua tanggal unik dan subkategori
        $allDates = Agenda::where('jadwal_id', $id)->distinct()->pluck('tanggal');
        $allSubKategori = Agenda::with('mataLomba')
            ->where('jadwal_id', $id)
            ->get()
            ->pluck('mataLomba')
            ->unique('id');

        // Filter
        $tanggalFilter = $request->input('tanggal', $allDates->first());
        $mataLombaId = $request->input('mata_lomba');


        $query = Agenda::with(['mataLomba', 'venue', 'juri', 'peserta', 'tim'])
            ->where('jadwal_id', $id)
            ->whereDate('tanggal', $tanggalFilter);

        if ($mataLombaId) {
            $query->where('mata_lomba_id', $mataLombaId);
        }

        $filteredJadwals = $query->get();

        if (!$mataLombaId) {
            // Kelompokkan semua agenda berdasarkan mata lomba (hanya hari itu)
            $grouped = Agenda::with(['mataLomba', 'venue'])
                ->where('jadwal_id', $id)
                ->whereDate('tanggal', $tanggalFilter)
                ->get()
                ->groupBy('mata_lomba_id');

            $filteredJadwals = $grouped->map(function ($group) {
                $firstAgenda = $group->first();
                $startTime = $group->min('waktu_mulai');
                $endTime = $group->max('waktu_selesai');

                $summaryAgenda = clone $firstAgenda;
                $summaryAgenda->waktu_mulai = $startTime;
                $summaryAgenda->waktu_selesai = $endTime;

                // Kosongkan peserta dan tim
                $summaryAgenda->setRelation('peserta', collect());
                $summaryAgenda->setRelation('tim', collect());

                return $summaryAgenda;
            })->values();

        }


        // Tambahin slotHeight & timeSlots
        $slotHeight = 40; // Pixel per slot
        $startHour = 7; // Jam mulai grid
        $endHour = 17; // Jam selesai grid
        $timeSlots = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
            $timeSlots[] = sprintf('%02d:30', $hour);
        }

        // Hitung start_slot dan duration_slot untuk grid
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
            'slotHeight' => $slotHeight
        ]);
    }




    public function create()
    {
        return view('jadwal.create'); // buat view ini
    }

    // public function edit($id)
    // {
    //     $jadwal = Agenda::findOrFail($id); // Cari Agenda, bukan Jadwal
    //     $mata_lomba = MataLomba::all();
    //     $venue = Venue::all();
    //     $peserta = Peserta::all();
    //     $juri = Juri::all();

    //     return view('jadwal.edit', compact('jadwal', 'mata_lomba', 'venue', 'peserta', 'juri'));
    // }

    public function edit($agenda_id)
    {
        $agenda = Agenda::findOrFail($agenda_id);

        $jadwal = Jadwal::find($agenda->jadwal_id);

        // Tanggal unik dari agenda di jadwal ini (sama seperti create)
        $tanggal_unik = [];
        if ($jadwal) {
            $tanggal_unik = Agenda::where('jadwal_id', $jadwal->id)
                ->select(DB::raw('DISTINCT tanggal'))
                ->orderBy('tanggal')
                ->pluck('tanggal');
        }

        $mata_lomba = MataLomba::all();
        $venue = Venue::all();
        $peserta_ids = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id');
        $peserta = Peserta::whereIn('id', $peserta_ids)
            ->orderBy('nama_peserta')
            ->get();
        $juri = Juri::all();
        $tim = Tim::all();

        // Data peserta dan tim terkait agenda
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
        \Log::info('Memulai update agenda', ['agenda_id' => $agenda_id, 'input' => $request->all()]);

        try {
            $agenda = Agenda::findOrFail($agenda_id);
            \Log::info('Agenda ditemukan', ['agenda' => $agenda->toArray()]);

            // Normalisasi waktu (hindari format aneh)
            if ($request->filled('waktu_mulai')) {
                $request->merge(['waktu_mulai' => date('H:i', strtotime($request->waktu_mulai))]);
            }
            if ($request->filled('waktu_selesai')) {
                $request->merge(['waktu_selesai' => date('H:i', strtotime($request->waktu_selesai))]);
            }

            // Validasi request
            $request->validate([
                'mata_lomba_id' => 'nullable|exists:mata_lomba,id',
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

            \Log::info('Validasi request berhasil');

            // Tentukan tanggal
            $tanggal = $request->tanggal_dropdown === 'lainnya'
                ? $request->tanggal
                : ($request->tanggal_dropdown ?: $agenda->tanggal);

            if (!$tanggal) {
                \Log::warning('Tanggal tidak valid', ['input' => $request->all()]);
                return back()->withInput()->with('error_force', 'Tanggal tidak valid atau kosong.');
            }

            $waktuMulai = $request->waktu_mulai;
            $waktuSelesai = $request->waktu_selesai;
            $venueId = $request->venue_id;
            $juriId = $request->juri_id;

            // Ambil peserta langsung dan peserta tim
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

            // Fungsi cek waktu bentrok (dipakai di query)
            $cekWaktu = function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                    $q->where('waktu_mulai', '<', $waktuSelesai)
                        ->where('waktu_selesai', '>', $waktuMulai);
                });
            };


            $tidakPerluCekBentrok = (
                $venueId == $agenda->venue_id &&
                $tanggal == $agenda->tanggal &&
                strtotime($waktuMulai) >= strtotime($agenda->waktu_mulai) &&
                strtotime($waktuSelesai) <= strtotime($agenda->waktu_selesai)
            );


            // Cek bentrok venue (harus cek juga tanggal & bukan agenda yang sama)
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

            // Cek bentrok tim (khusus kalau ingin tampilkan tim yang bentrok)
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


            // … (query bentrok selesai)

            // ─── Bentuk pesan ────────────────────────────────────────────────
            if (
                !$force && (
                    $bentrokVenueList->isNotEmpty() ||
                    $bentrokPesertaList->isNotEmpty() ||
                    $bentrokJuriList->isNotEmpty() ||
                    $bentrokTimList->isNotEmpty()
                )
            ) {

                $detail = [];          // ← hanya baris detail
                // venue
                foreach ($bentrokVenueList as $b) {
                    $detail[] = "Lomba {$b->mataLomba->nama_lomba} pada {$b->waktu_mulai}-{$b->waktu_selesai} di {$b->venue->name}";
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
                        $detail[] = "{$namaTimBentrok} pada lomba {$b->mataLomba->nama_lomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
                    }
                }


                // header + baris baru
                $pesan = "Terjadi bentrok:\n\n" . implode("\n", $detail);

                return back()->withInput()->with('error_force', $pesan);
            }

            // Simpan update agenda
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


            // Jika ada bentrok dan force, lakukan geser agenda bawahnya mirip add()
            if ($force && ($bentrokVenueList->isNotEmpty())) {
                // Ambil semua agenda dalam jadwal, venue, tanggal, urut waktu mulai, kecuali agenda ini
                $agendaSemua = Agenda::where('jadwal_id', $agenda->jadwal_id)
                    ->where('venue_id', $venueId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('id', '!=', $agenda->id)
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

                        \Log::info('Geser agenda ID ' . $agendaItem->id, [
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
            \Log::error('Error saat update agenda', ['message' => $e->getMessage()]);
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

        // Validasi bahwa waktu_mulai < waktu_selesai untuk setiap tanggal
        foreach ($request->tanggal as $i => $tanggal) {
            if ($request->waktu_mulai[$i] >= $request->waktu_selesai[$i]) {
                return back()->withErrors([
                    "Waktu selesai harus lebih besar dari waktu mulai pada tanggal $tanggal"
                ])->withInput();
            }
        }

        // Format data ke dalam array jadwal harian
        $jadwal_per_tanggal = [];
        foreach ($request->tanggal as $i => $tanggal) {
            $jadwal_per_tanggal[] = [
                'tanggal' => $tanggal,
                'waktu_mulai' => $request->waktu_mulai[$i],
                'waktu_selesai' => $request->waktu_selesai[$i],
            ];
        }

        // Simpan ke session
        session([
            'jadwal_nama' => $request->nama_jadwal,
            'jadwal_harian' => $jadwal_per_tanggal,
        ]);


        return view('jadwal.create-step2', [
            'jadwal_nama' => $request->nama_jadwal,
            'jadwal_harian' => $jadwal_per_tanggal,
        ]);
    }

    public function prosesJadwal(Request $request)
    {
        Log::info("Memanggil prosesJadwal");

        $constraintTambahan = session('constraint_lomba', []);
        $jadwalHarian = session('jadwal_harian', []);
        $startTime = session('jadwal_waktu_mulai', '08:00');
        $endTime = session('jadwal_waktu_selesai', '17:00');
        $variabelX = $this->processPesertaKategoriLomba();
        // dd($variabelX);
        $pesertaKategori = $variabelX;
        $namaJadwal = session('jadwal_nama', 'Jadwal Otomatis');
        $tahun = now()->year;

        // 🔹 Buat entri awal jadwal
        $jadwalAwal = Jadwal::create([
            'nama_jadwal' => $namaJadwal,
            'tahun' => $tahun,
            'version' => '1',
            'status' => 'Menunggu',
            'event_id' => '1',
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
            1
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
        ]);

        return view('jadwal.proses', [
            'namaJadwal' => $namaJadwal,
        ]);
    }



    // public function createStep2(Request $request)
    // {
    //     $validated = $request->validate([
    //         'nama_jadwal' => 'required|string|max:255',
    //         'tanggal_awal' => 'required|date',
    //         'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
    //         'tanggal' => 'required|array',
    //         'waktu_mulai' => 'required|array',
    //         'waktu_selesai' => 'required|array',
    //         'waktu_mulai.*' => 'required|date_format:H:i',
    //         'waktu_selesai.*' => 'required|date_format:H:i',
    //     ]);

    //     // Validasi waktu_mulai < waktu_selesai per tanggal
    //     foreach ($request->tanggal as $i => $tanggal) {
    //         if ($request->waktu_mulai[$i] >= $request->waktu_selesai[$i]) {
    //             return back()->withErrors(["Waktu selesai harus lebih besar dari waktu mulai pada tanggal $tanggal"]);
    //         }
    //     }

    //     // Format data per tanggal
    //     $jadwal_per_tanggal = [];
    //     foreach ($request->tanggal as $i => $tanggal) {
    //         $jadwal_per_tanggal[] = [
    //             'tanggal' => $tanggal,
    //             'waktu_mulai' => $request->waktu_mulai[$i],
    //             'waktu_selesai' => $request->waktu_selesai[$i],
    //         ];
    //     }

    //     // Simpan ke session
    //     session([
    //         'jadwal_nama' => $request->nama_jadwal,
    //         'jadwal_tanggal_rentang' => [
    //             'tanggal_awal' => $request->tanggal_awal,
    //             'tanggal_akhir' => $request->tanggal_akhir,
    //         ],
    //         'jadwal_harian' => $jadwal_per_tanggal,
    //     ]);

    //     return view('jadwal.create-step2', [
    //         'jadwal_nama' => $request->nama_jadwal,
    //         'jadwal_rentang' => $request->only(['tanggal_awal', 'tanggal_akhir']),
    //         'jadwal_harian' => $jadwal_per_tanggal,
    //     ]);
    // }


    // public function prosesJadwal(Request $request)
    // {
    //     Log::info("Memanggil prosesJadwal");

    //     $constraintTambahan = session('constraint_lomba', []);
    //     $jadwalHarian = session('jadwal_harian', []);
    //     $startTime = session('jadwal_waktu_mulai', '08:00');
    //     $endTime = session('jadwal_waktu_selesai', '17:00');
    //     $variabelX = $this->processPesertaKategoriLomba();
    //     // dd($variabelX);
    //     $pesertaKategori = $variabelX;
    //     $namaJadwal = session('jadwal_nama', 'Jadwal Otomatis');
    //     $tahun = now()->year;

    //     // 🔹 Buat entri awal jadwal
    //     $jadwalAwal = Jadwal::create([
    //         'nama_jadwal' => $namaJadwal,
    //         'tahun' => $tahun,
    //         'version' => '1',
    //         'status' => 'Menunggu',
    //     ]);

    //     // $penjadwal = new PenjadwalanController();

    //     $domain = $this->constraintPropagation($variabelX, $constraintTambahan, $jadwalHarian);
    //     Log::info('Generated domain: ' . json_encode($domain));

    //     $jadwalValidSolutions = $this->backtrack($domain);

    //     dd($jadwalValidSolutions);

    //     Log::info("Selesai backtrack pada job");

    //     $version = 1;

    //     if (!$jadwalValidSolutions) {
    //         Log::warning("Gagal melakukan penjadwalan. Tidak ada jadwal valid ditemukan.");
    //         if ($version == 1) {
    //             $jadwalMaster = Jadwal::find($this->jadwalId);
    //             if ($jadwalMaster) {
    //                 $jadwalMaster->update(['status' => 'Gagal']);
    //             }
    //         }
    //         return;
    //     }

    //     // Jika versi 1, pakai jadwal master, versi selanjutnya buat jadwal baru per solusi
    //     if ($this->version == 1) {
    //         $jadwalMaster = Jadwal::find($this->jadwalId);
    //         if (!$jadwalMaster) {
    //             Log::error("Jadwal master tidak ditemukan");
    //             return;
    //         }
    //         // Simpan solusi pertama ke jadwal master
    //         $this->saveAgenda($jadwalMaster, $jadwalValidSolutions[0]);
    //         $jadwalMaster->update(['status' => 'Selesai']);

    //         // Simpan solusi lain sebagai versi 2 dan seterusnya
    //         for ($i = 1; $i < count($jadwalValidSolutions); $i++) {
    //             $version = $this->version + $i;
    //             $jadwalBaru = Jadwal::create([
    //                 'nama_jadwal' => $this->namaJadwal,
    //                 'tahun' => now()->year,
    //                 'version' => $version,
    //                 'status' => 'Menunggu',
    //             ]);
    //             $this->saveAgenda($jadwalBaru, $jadwalValidSolutions[$i]);
    //             $jadwalBaru->update(['status' => 'Selesai']);
    //         }
    //     } else {
    //         // Untuk versi selain 1, berarti ini pemanggilan job lanjutan
    //         $jadwalMaster = Jadwal::create([
    //             'nama_jadwal' => $this->namaJadwal,
    //             'tahun' => now()->year,
    //             'version' => $this->version,
    //             'status' => 'Menunggu',
    //         ]);
    //         $this->saveAgenda($jadwalMaster, $jadwalValidSolutions[0]);
    //         $jadwalMaster->update(['status' => 'Selesai']);
    //     }

    //     Log::info("Penjadwalan selesai.");

    //     // ProsesPenjadwalanJob::dispatch(
    //     //     $startTime,
    //     //     $endTime,
    //     //     $variabelX,
    //     //     $pesertaKategori,
    //     //     $constraintTambahan,
    //     //     $jadwalHarian,
    //     //     $namaJadwal,
    //     //     $jadwalAwal->id,
    //     //     1
    //     // );

    //     // return response()->json([
    //     //     'status' => 'success',
    //     //     'message' => 'Penjadwalan sedang diproses di background.',
    //     // ]);

    //     session()->forget([
    //         'jadwal_nama',
    //         'jadwal_waktu_mulai',
    //         'jadwal_waktu_selesai',
    //         'jadwal_harian',
    //         'constraint_lomba',
    //     ]);

    //     return view('jadwal.proses', [
    //         'namaJadwal' => $namaJadwal,
    //     ]);
    // }


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
        $validated = $request->validate([
            'venue' => 'required|exists:venue,id',
            'kategori_lomba' => 'required|exists:mata_lomba,id',
            'peserta' => 'required|exists:peserta,id',
        ]);

        $venue = Venue::find($validated['venue']);
        $kategori = MataLomba::find($validated['kategori_lomba']);
        $peserta = Peserta::find($validated['peserta']);

        // Ambil dari session
        $jadwalHarian = session('jadwal_harian', []);
        $mataLomba = $this->processSubKategoriLomba();

        return view('jadwal.create-step3', compact(
            'venue',
            'kategori',
            'peserta',
            'mataLomba',
            'jadwalHarian'
        ));
    }


    public function store(Request $request)
    {
        Log::debug('Memulai proses store constraint lomba.');

        // Validasi form constraint tambahan jika diperlukan
        $validated = $request->validate([
            'hari' => 'nullable|array',
            'waktu_mulai' => 'nullable|array',
            'waktu_selesai' => 'nullable|array',
            'saving_time' => 'nullable|array',
            'round' => 'nullable|array',
        ]);

        Log::debug('Data yang divalidasi:', $validated);

        $hari = $request->input('hari', []);
        $waktuMulai = $request->input('waktu_mulai', []);
        $waktuSelesai = $request->input('waktu_selesai', []);
        $savingTime = $request->input('saving_time', []);
        $round = $request->input('round', []);

        Log::debug('Input hari:', $hari);
        Log::debug('Input waktu_mulai:', $waktuMulai);
        Log::debug('Input waktu_selesai:', $waktuSelesai);

        $constraint = [];

        foreach ($hari as $mataLombaId => $value) {
            $constraint[$mataLombaId] = [
                'hari' => is_array($value) ? $value : [$value],
                'waktu_mulai' => $waktuMulai[$mataLombaId] ?? null,
                'waktu_selesai' => $waktuSelesai[$mataLombaId] ?? null,
                'saving_time' => $savingTime[$mataLombaId] ?? null,
                'round' => $round[$mataLombaId] ?? null,
            ];

            Log::debug("Constraint untuk mata_lomba_id {$mataLombaId}:", $constraint[$mataLombaId]);
        }

        // dd($constraint);

        // Simpan ke session
        session(['constraint_lomba' => $constraint]);

        Log::debug('Constraint disimpan ke session:', $constraint);

        // Lanjutkan ke proses penjadwalan
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
            ->paginate(10);

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

        $mata_lomba = MataLomba::all();
        $venue = Venue::all();
        $peserta_ids = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id');
        $peserta = Peserta::whereIn('id', $peserta_ids)->get();
        $juri = Juri::all();
        $tim = Tim::all();

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
                $detail[] = "Lomba {$b->mataLomba->nama_lomba} pada {$b->waktu_mulai}-{$b->waktu_selesai} di {$b->venue->name}";
            }
            foreach ($bentrokPesertaList as $b) {
                dd($b->peserta);
                $peserta = $b->peserta->pluck('nama_peserta')->implode(', ');
                $detail[] = "Peserta {$peserta} pada lomba {$b->mataLomba->nama_lomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
            }
            foreach ($bentrokJuriList as $b) {
                $detail[] = "Juri pada lomba {$b->mataLomba->nama_lomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
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
                    $detail[] = "{$namaTimBentrok} pada lomba {$b->mataLomba->nama_lomba} {$b->waktu_mulai}-{$b->waktu_selesai}";
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
        $agendaBaru->peserta()->attach($request->peserta_id);
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

                    \Log::info('Geser agenda ID ' . $agendaItem->id, [
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

        if (!$agenda1 || !$agenda2) {
            return redirect()->back()->with('error', 'Agenda tidak ditemukan.');
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
                $venueId = $randomVenue?->id;
                // Log::warning("MataLomba $kategoriLomba tidak memiliki venue_id, memilih venue acak: " . ($venueId ?? 'none'));
            }

            $durasi = $mataLomba->durasi ?? 30;
            $savingTime = 0;

            $constraintSubKategori = $constraintTambahan[$mataLombaId] ?? null;

            $venue = Venue::find($venueId);
            if ($venue) {
                $constraintSubKategori['venue_tanggal'] = $venue->tanggal_tersedia;
                $constraintSubKategori['venue_mulai'] = $venue->waktu_mulai_tersedia;
                $constraintSubKategori['venue_akhir'] = $venue->waktu_berakhir_tersedia;
            }

            if ($constraintSubKategori && isset($constraintSubKategori['saving_time']) && is_numeric($constraintSubKategori['saving_time'])) {
                $savingTime = (int) $constraintSubKategori['saving_time'];
            }

            $allSlots = [];

            Log::debug('Jadwal harian:', $jadwalHarian);

            foreach ($jadwalHarian as $jadwal) {
                Log::debug('Proses jadwal:', $jadwal);
                $tanggal = $jadwal['tanggal'];
                $mulai = $jadwal['waktu_mulai'];
                $selesai = $jadwal['waktu_selesai'];

                $startDateTime = Carbon::parse("$tanggal $mulai");
                $endDateTime = Carbon::parse("$tanggal $selesai");
                // Log::debug("Start: $startDateTime, End: $endDateTime");

                $slots = $this->generateTimeSlots($startDateTime, $endDateTime, $durasi, $savingTime);

                Log::debug("Slot yang dihasilkan:", $slots);

                $allSlots = array_merge($allSlots, $slots);
            }

            Log::debug("Total slot yang dihasilkan: " . count($allSlots));

            Log::info("Memulai filter slot untuk $mataLomba");
            Log::info("Constraint tambahan untuk slot sekarang: " . json_encode($constraintSubKategori));


            $filteredSlots = $this->filterSlotsByConstraint($allSlots, $constraintSubKategori);
            Log::info("Jumlah slot setelah filter constraint: " . count($filteredSlots));

            if (count($filteredSlots) == 0) {
                $filteredSlots = $allSlots;
            }

            Log::debug("Detail slot sekarang:" . count($filteredSlots));

            $isSerentak = $mataLomba->is_serentak;

            if ($isSerentak) {
                // Key sama untuk semua peserta di kategori yang is_serentak
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
                // Non-serentak: key per tim
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

        Log::debug("Memulai filter slot dengan constraint: hari=" . json_encode($hariConstraint) . ", mulai=$waktuMulaiConstraint, selesai=$waktuSelesaiConstraint");

        // constraint dari venue
        $venueTanggal = $constraintTambahan['venue_tanggal'] ?? null;
        $venueMulai = $constraintTambahan['venue_mulai'] ?? null;
        $venueAkhir = $constraintTambahan['venue_akhir'] ?? null;

        Log::debug("Memulai filter slot dengan venue: " . json_encode($venueTanggal) . ", mulai=$venueMulai, selesai=$venueAkhir");

        $filtered = [];

        foreach ($slots as $index => $slot) {
            // Log::debug("🔍 Slot ke-$index: tanggal=$slotDate, mulai=$startTime, selesai=$endTime");

            // Log::debug("Slot ke-$index sebelum parse: waktu_mulai = {$slot['waktu_mulai']}, waktu_selesai = {$slot['waktu_selesai']}");

            if (!isset($slot['waktu_mulai']) || !isset($slot['waktu_selesai'])) {
                // Log::debug("❌ Slot ke-$index error: waktu_mulai atau waktu_selesai tidak ada");
                continue;
            }

            try {
                $slotStart = Carbon::parse($slot['waktu_mulai']);
                // Log::debug("CEK SLOT START: $slotStart");
                $slotEnd = Carbon::parse($slot['waktu_selesai']);
            } catch (\Exception $e) {
                // Log::debug("❌ Slot ke-$index gagal parse waktu: " . $e->getMessage());
                continue;
            }

            // $slotDate = Carbon::parse($slot['waktu_mulai'])->format('Y-m-d');

            $slotDate = $slot['tanggal'];

            $startTime = $slotStart->format('H:i');
            $endTime = $slotEnd->format('H:i');

            // Log::debug("Memeriksa slot ke-$index: {$slotStart} - {$slotEnd} (Hari: $slotDate , $startTime - $endTime)");

            if (!empty($hariConstraint) && is_array($hariConstraint) && !in_array(null, $hariConstraint, true)) {
                if (!in_array($slotDate, $hariConstraint)) {
                    // Log::debug("❌ Slot ke-$index ditolak: tanggal $slotDate tidak termasuk hari constraint.");
                    continue;
                }
            }

            // constraint waktu mulai (jika ada)
            if ($waktuMulaiConstraint !== null) {
                if ($startTime < $waktuMulaiConstraint) {
                    // Log::debug("❌ Slot ke-$index ditolak: mulai $startTime < batas $waktuMulaiConstraint.");
                    continue;
                }
            }

            // constraint waktu selesai (jika ada)
            if ($waktuSelesaiConstraint !== null) {
                if ($endTime > $waktuSelesaiConstraint) {
                    // Log::debug("❌ Slot ke-$index ditolak: selesai $endTime > batas $waktuSelesaiConstraint.");
                    continue;
                }
            }

            // constraint venue
            if ($venueTanggal !== null) {
                if ($slotDate !== $venueTanggal) {
                    // Log::debug("❌ Slot ke-$index ditolak: tanggal $slotDate ≠ venueTanggal $venueTanggal");
                    continue;
                }
            }

            if ($venueMulai !== null) {
                if ($startTime < $venueMulai) {
                    // Log::debug("❌ Slot ke-$index ditolak: jam mulai $startTime < venueMulai $venueMulai");
                    continue;
                }
            }

            if ($venueAkhir !== null) {
                if ($endTime > $venueAkhir) {
                    // Log::debug("❌ Slot ke-$index ditolak: jam selesai $endTime > venueAkhir $venueAkhir");
                    continue;
                }
            }

            // Log::debug("✅ Slot diterima.");
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



    // public function prosesJadwal(Request $request)
    // {
    //     $startTime = session('jadwal_waktu_mulai', '08:00');
    //     $endTime = session('jadwal_waktu_selesai', '17:00');

    //     Log::info('Received request for scheduling. Start time: ' . $startTime . ', End time: ' . $endTime);

    //     $variabelX = $this->processPesertaKategoriLomba();
    //     $pesertaKategori = $variabelX;

    //     set_time_limit(0);

    //     $domain = $this->constraintPropagation($startTime, $endTime, $variabelX, $pesertaKategori);

    //     Log::info('Generated domain: ', $domain);

    //     $jadwalValid = $this->backtrack($domain);

    //     if ($jadwalValid) {
    //         foreach ($jadwalValid as $jadwal) {
    //             if (count($jadwal['peserta']) === 1) {
    //                 // individu
    //                 $peserta = Peserta::where('nim', $jadwal['peserta'][0])->first();
    //                 $pesertaId = $peserta?->id;
    //                 $timId = null;
    //             } else {
    //                 // tim
    //                 $tim = Tim::whereHas('anggota', function ($query) use ($jadwal) {
    //                     $query->whereIn('nim', $jadwal['peserta']);
    //                 }, '=', count($jadwal['peserta']))->first();

    //                 $pesertaId = null;
    //                 $timId = $tim?->id;
    //             }

    //             $mataLomba = MataLomba::where('nama_lomba', $jadwal['kategori_lomba'])->first();

    //             Jadwal::create([
    //                 'nama_jadwal' => session('jadwal_nama', 'Jadwal Otomatis'),
    //                 'tahun' => now()->year,
    //                 'tanggal' => $jadwal['tanggal'],
    //                 'mata_lomba_id' => $mataLomba->id ?? null,
    //                 'waktu_mulai' => $jadwal['waktu_mulai'],
    //                 'waktu_selesai' => $jadwal['waktu_selesai'],
    //                 'venue_id' => $mataLomba->venue_id ?? null,
    //                 'peserta_id' => $pesertaId ?? null,
    //                 'tim_id' => $timId ?? null,
    //                 'juri_id' => $juri->id ?? null,
    //                 'version' => 1,
    //             ]);
    //         }

    //         session()->forget([
    //             'jadwal_nama',
    //             'jadwal_waktu_mulai',
    //             'jadwal_waktu_selesai',
    //             'constraint_lomba',
    //         ]);

    //         return view('jadwal.berhasil', [
    //             'message' => 'Jadwal berhasil dibuat!',
    //             'link' => route('jadwal.index')
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => 'failed',
    //         'message' => 'Tidak ada jadwal valid.',
    //     ]);
    // }


    // public function backtrack($domain)
    // {
    //     Log::info("Memulai proses backtracking");
    //     // Log::debug("Domain awal:", $domain);
    //     $kategoriKeys = array_keys($domain);
    //     $solution = [];

    //     $backtrackRecursive = function ($depth) use (&$backtrackRecursive, $domain, $kategoriKeys, &$solution) {
    //         if ($depth === count($kategoriKeys)) {
    //             // Log::info("Solusi lengkap ditemukan!");
    //             return true;
    //         }

    //         $currentKey = $kategoriKeys[$depth];
    //         $slots = $domain[$currentKey];

    //         // Log::debug("Depth $depth | Proses key: $currentKey | Total slot tersedia: " . count($slots));

    //         foreach ($slots as $index => $slot) {
    //             // Log::debug("Coba slot ke-$index: peserta [" . implode(',', $slot['peserta']) . "] | lomba {$slot['kategori_lomba']} | waktu {$slot['waktu_mulai']} - {$slot['waktu_selesai']} | venue_id {$slot['venue']}");

    //             if ($this->checkConstraint($slot, $solution)) {
    //                 // Log::debug("Slot konsisten. Menambahkan ke solusi.");
    //                 $solution[] = $slot;

    //                 if ($backtrackRecursive($depth + 1)) {
    //                     return true;
    //                 }

    //                 // Log::debug("Backtrack: menghapus slot terakhir dari solusi.");
    //                 array_pop($solution);
    //             } else {
    //                 // Log::debug("Slot tidak konsisten. Lewati.");
    //             }
    //         }

    //         // Log::debug("Tidak ada slot valid pada depth $depth untuk key $currentKey");
    //         return false;
    //     };

    //     if ($backtrackRecursive(0)) {
    //         Log::info("Proses backtracking berhasil. Solusi akhir:");
    //         // Log::info($solution);
    //         return $solution;
    //     }

    //     Log::warning("Backtracking gagal: tidak ditemukan solusi valid.");
    //     return null;
    // }

    public function backtrack($domain, $maxSolutions = 3)
    {
        Log::info("Memulai proses backtracking (multi solusi)");

        $originalKategoriKeys = array_keys($domain);

        // ✅ Tambahkan log isi domain
        foreach ($domain as $key => $slots) {
            Log::info("Domain [$key] memiliki " . count($slots) . " slot:");
            foreach ($slots as $index => $slot) {
                Log::info("[$key][$index] => " . json_encode([
                    'Tanggal' => $slot['tanggal'],
                    'Mulai' => $slot['waktu_mulai'],
                    'Selesai' => $slot['waktu_selesai'],
                    'Venue' => $slot['venue'],
                    'Peserta' => $slot['peserta'], // array oke
                    'Tim' => $slot['nama_tim'] ?? '-', // bisa array atau string
                ], JSON_PRETTY_PRINT));
            }
        }

        $solutions = [];

        for ($attempt = 0; $attempt < $maxSolutions; $attempt++) {
            Log::info("🔁 Memulai variasi solusi ke-" . ($attempt + 1));

            $shuffledDomain = $domain;

            // Group berdasarkan kategori utama
            $groupedKategori = [];
            foreach ($originalKategoriKeys as $key) {
                $kategoriUtama = explode('-', $key)[0];
                $groupedKategori[$kategoriUtama][] = $key;
            }


            // ✅ Acak urutan kategori utama agar setiap versi punya urutan berbeda
            $kategoriUtamaList = array_keys($groupedKategori);
            Log::info("[$attempt] Sebelum acak/rotasi: kategoriUtamaList => " . json_encode($kategoriUtamaList));
            // shuffle($kategoriUtamaList);

            // Rotasi urutan kategori utama berdasarkan $attempt (biar variasi tapi deterministik)
            // $rotasi = $attempt % count($kategoriUtamaList);
            // $kategoriUtamaList = array_merge(
            //     array_slice($kategoriUtamaList, $rotasi),
            //     array_slice($kategoriUtamaList, 0, $rotasi)
            // );

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

            // Log hasil grouping
            Log::info("[$attempt] Grup berdasarkan venue: " . json_encode($venueGroupedKategori));

            $shuffledKategoriUtamaList = [];

            foreach ($venueGroupedKategori as $venue => $kategoriList) {
                mt_srand($attempt + intval($venue)); // Seed tetap deterministik
                shuffle($kategoriList); // acak seperti biasa

                // Cek apakah kita bisa hindari mengulang kategori pertama
                if (count($kategoriList) > 2 && isset($firstKategoriHistory[$venue])) {
                    $previousFirst = $firstKategoriHistory[$venue];
                    if ($kategoriList[0] === $previousFirst) {
                        // cari kandidat pertama yang berbeda
                        foreach ($kategoriList as $i => $kategori) {
                            if ($kategori !== $previousFirst) {
                                // swap
                                $kategoriList[$i] = $kategoriList[0];
                                $kategoriList[0] = $kategori;
                                Log::info("[$attempt] Swap kategori pertama $previousFirst -> $kategori di venue $venue");
                                break;
                            }
                        }
                    }
                }

                // Simpan kategori pertama untuk venue ini agar bisa dicek pada attempt berikutnya
                $firstKategoriHistory[$venue] = $kategoriList[0];

                $shuffledKategoriUtamaList = array_merge($shuffledKategoriUtamaList, $kategoriList);
                Log::info("[$attempt] Setelah shuffle venue $venue: " . json_encode($kategoriList));
            }
            mt_srand(); // reset RNG

            // Ganti kategoriUtamaList dengan versi baru
            $kategoriUtamaList = $shuffledKategoriUtamaList;

            // Log final
            Log::info("[$attempt] Final kategoriUtamaList setelah shuffle per venue => " . json_encode($kategoriUtamaList));

            // Susun ulang kategoriKeys: urutan kategori utama acak, isi kategori urut natural
            $kategoriKeys = [];
            foreach ($kategoriUtamaList as $kategoriUtama) {
                $kategoriGroup = $groupedKategori[$kategoriUtama];

                Log::info("[$attempt] Grup kategori utama: $kategoriUtama => " . json_encode($kategoriGroup));

                // sort natural + sort berdasarkan jam mulai
                sort($kategoriGroup, SORT_NATURAL);

                // Cek apakah semua subkategori dalam grup ini punya venue yang sama
                // $venueSet = [];
                // foreach ($kategoriGroup as $key) {
                //     $venueSet[] = $shuffledDomain[$key][0]['venue'];
                // }
                // $venueSet = array_unique($venueSet);

                // if (count($venueSet) === 1) {
                //     // Acak karena venue samA
                //     shuffle($kategoriGroup);
                //     Log::info("[$attempt] Setelah shuffle (venue sama): $kategoriUtama => " . json_encode($kategoriGroup));
                // } else {
                // Sort natural dan berdasarkan jam mulai
                // sort($kategoriGroup, SORT_NATURAL);
                usort($kategoriGroup, function ($a, $b) use ($shuffledDomain) {
                    $slotA = $shuffledDomain[$a][0];
                    $slotB = $shuffledDomain[$b][0];

                    $startA = strtotime($slotA['tanggal'] . ' ' . $slotA['waktu_mulai']);
                    $startB = strtotime($slotB['tanggal'] . ' ' . $slotB['waktu_mulai']);

                    return $startA <=> $startB;
                });
                // }

                // ✅ Tambahkan ke $kategoriKeys
                foreach ($kategoriGroup as $key) {
                    $kategoriKeys[] = $key;
                }
            }

            $currentSolution = [];

            $backtrackRecursive = function ($depth, &$currentSolution) use (&$backtrackRecursive, $shuffledDomain, $kategoriKeys, &$solutions, $maxSolutions) {
                if ($depth === count($kategoriKeys)) {
                    $solutions[] = $currentSolution;

                    $versionNumber = count($solutions);
                    Log::info("Solusi ke-{$versionNumber}: " . json_encode($currentSolution));

                    // ❗ Berhenti hanya jika solusi sudah cukup
                    return true;
                }

                $currentKey = $kategoriKeys[$depth];
                $slots = $shuffledDomain[$currentKey];

                foreach ($slots as $slot) {
                    if ($this->checkConstraint($slot, $currentSolution)) {
                        $currentSolution[] = $slot;
                        $shouldStop = $backtrackRecursive($depth + 1, $currentSolution);
                        array_pop($currentSolution);

                        if ($shouldStop) {
                            return true; // ❗ Stop hanya kalau solusi sudah cukup
                        }
                    }
                }

                return false;
            };


            $result = $backtrackRecursive(0, $currentSolution);

            // ❗ Tidak perlu cek count lagi di sini, karena 1 solusi sudah cukup untuk 1 attempt
            if ($result) {
                continue; // lanjut ke attempt berikutnya
            }

        }
        Log::info("[$attempt] Final kategoriKeys: " . json_encode($kategoriKeys));

        if (count($solutions) > 0) {
            Log::info("Backtracking berhasil menemukan " . count($solutions) . " solusi.");
            return $solutions;
        }

        $penyebab = $this->analisaKegagalan($domain);
        Log::warning("Backtracking gagal: $penyebab");

        return ['error' => $penyebab];
    }

    private function analisaKegagalan($domain)
    {
        $logDetail = [];

        foreach ($domain as $kategori => $slots) {
            if (count($slots) === 0) {
                $logDetail[] = "❌ Kategori \"$kategori\" tidak memiliki slot valid sama sekali.";
            } else {
                $logDetail[] = "✅ Kategori \"$kategori\" memiliki " . count($slots) . " slot.";
            }
        }

        // jika ada yang 0 slot
        $emptyKategori = array_filter($domain, fn($slots) => count($slots) === 0);
        if (count($emptyKategori) > 0) {
            $daftarKosong = implode(', ', array_keys($emptyKategori));
            return "Kategori berikut tidak memiliki slot valid: $daftarKosong";
        }

        // jika semua punya slot, tapi solusi tetap gagal
        $slotCounts = array_map(fn($slots) => count($slots), $domain);
        $totalSlot = array_sum($slotCounts);

        if ($totalSlot === 0) {
            return "Semua slot habis atau tidak sesuai dengan constraint peserta dan venue.";
        }

        // analisa kemungkinan bentrok
        $kategoriTerbanyakSlot = array_keys($slotCounts, max($slotCounts))[0];
        $kategoriTersedikitSlot = array_keys($slotCounts, min($slotCounts))[0];

        return "Kemungkinan bentrok antar peserta terlalu padat. Kategori dengan slot terbanyak: \"$kategoriTerbanyakSlot\" (" . max($slotCounts) . " slot), dan tersedikit: \"$kategoriTersedikitSlot\" (" . min($slotCounts) . " slot).";
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

        return response()->json([
            'status' => 'success',
            'message' => 'Variabel X berhasil dibuat',
            'data' => $variabelX
        ]);
    }

    // private function processSubKategoriLomba()
    // {
    //     $peserta = Peserta::with('mataLomba')->get();

    //     $mataLombaIds = $peserta->pluck('mata_lomba_id');

    //     $grouped = $mataLombaIds->unique()->values();

    //     $mataLomba = [];

    //     foreach ($grouped as $id) {
    //         $mataLomba = $peserta->firstWhere('mata_lomba_id', $id)->mataLomba;
    //         $mataLomba[] = [
    //             'mata_lomba_id' => $id,
    //             'nama_mata_lomba' => $mataLomba ? $mataLomba->nama_lomba : 'Tidak Diketahui'
    //         ];
    //     }

    //     return $mataLomba;
    // }

    private function processSubKategoriLomba()
    {
        // 1. Ambil peserta_id yang sudah membayar
        $pesertaIds = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id');

        // 2. Ambil data pendaftar berdasarkan peserta_id tersebut, beserta relasi mataLomba
        $pendaftar = Pendaftar::with('mataLomba')
            ->whereIn('peserta_id', $pesertaIds)
            ->get();

        // 3. Ambil mata_lomba_id unik
        $mataLombaIds = $pendaftar->pluck('mata_lomba_id')->unique()->values();

        // 4. Bangun list hasil
        $mataLombaList = [];

        foreach ($mataLombaIds as $id) {
            $mataLomba = $pendaftar->firstWhere('mata_lomba_id', $id)?->mataLomba;

            $mataLombaList[] = [
                'mata_lomba_id' => $id,
                'nama_mata_lomba' => $mataLomba ? $mataLomba->nama_lomba : 'Tidak Diketahui',
            ];
        }

        return $mataLombaList;
    }



    public function processPesertaKategoriLomba()
    {
        $result = [];
        $timMap = [];

        $pesertaMembayarIds = Membayar::where('status', 'Sudah Membayar')->pluck('peserta_id')->toArray();

        // Kelompok
        $timList = Tim::with(['peserta.pendaftar.mataLomba'])
            ->whereHas('peserta', function ($query) use ($pesertaMembayarIds) {
                $query->whereIn('peserta.id', $pesertaMembayarIds);
            })
            ->get();

        foreach ($timList as $tim) {
            $anggotaValid = $tim->peserta->filter(function ($anggota) use ($pesertaMembayarIds) {
                return in_array($anggota->id, $pesertaMembayarIds);
            });

            if ($anggotaValid->isEmpty())
                continue;

            $mataLomba = $anggotaValid->first()->pendaftar?->mataLomba;
            if (!$mataLomba)
                continue;

            $key = $mataLomba->nama_lomba;
            $isSerentak = $mataLomba->is_serentak;

            if ($isSerentak) {
                // Serentak & kelompok: nama_tim jadi array of tim
                $timMap[$key]['kategori_lomba'] = $mataLomba->nama_lomba;
                $timMap[$key]['nama_tim'][] = $tim->nama_tim;
                $timMap[$key]['anggota'] = array_merge(
                    $timMap[$key]['anggota'] ?? [],
                    $anggotaValid->pluck('nim')->toArray()
                );

                // Hapus duplikat nama_tim & anggota
                $timMap[$key]['nama_tim'] = array_unique($timMap[$key]['nama_tim']);
                $timMap[$key]['anggota'] = array_unique($timMap[$key]['anggota']);
            } else {
                // Tidak serentak: tetap satu tim per key
                $keyTim = $key . '-' . $tim->nama_tim;
                $timMap[$keyTim]['kategori_lomba'] = $mataLomba->nama_lomba;
                $timMap[$keyTim]['nama_tim'] = $tim->nama_tim;
                $timMap[$keyTim]['anggota'] = $anggotaValid->pluck('nim')->toArray();
            }
        }

        // Individu
        $pesertaIndividu = Peserta::with('pendaftar.mataLomba')
            ->whereDoesntHave('tim')
            ->whereIn('id', $pesertaMembayarIds)
            ->get();

        foreach ($pesertaIndividu as $pesertaItem) {
            $mataLomba = $pesertaItem->pendaftar?->mataLomba;
            if (!$mataLomba)
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

        // dd($result);
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
            // dd($id);

            // dd($jadwal->agenda());
            // hapus agenda yang terkait dengan jadwal ini
            $jadwal->agendas()->delete();

            // hapus jadwal
            $jadwal->delete();

            return redirect()->route('jadwal.index')->with('success', 'Jadwal dan agenda terkait berhasil dihapus');
        } catch (\Exception $e) {
            \Log::error('Gagal hapus jadwal', ['jadwal_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus jadwal');
        }
    }





}