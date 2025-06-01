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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProsesPenjadwalanJob;



class PenjadwalanController extends Controller
{

    // public function index()
    // {
    //     $jadwals = Jadwal::with(['mataLomba', 'venue', 'peserta', 'juri'])->get();
    //     return view('jadwal.index', compact('jadwals'));
    // }

    public function index()
    {
        $jadwals = DB::table('jadwal')
            ->select('id', 'nama_jadwal', 'tahun', 'version', 'status')
            ->distinct()
            ->get();
        return view('jadwal.index', compact('jadwals'));
    }

    public function change($id)
    {
        $jadwalMaster = Jadwal::findOrFail($id);

        // Ambil data Agenda dengan relasi yang diperlukan
        $agendas = Agenda::select('agenda.*')
            ->join('mata_lomba', 'agenda.mata_lomba_id', '=', 'mata_lomba.id')
            ->where('jadwal_id', $id)
            ->orderBy('tanggal')
            ->orderBy('mata_lomba.nama_lomba')
            ->orderBy('waktu_mulai')
            ->with(['mataLomba', 'venue', 'peserta', 'juri', 'tim'])
            ->get();


        return view('jadwal.change', [
            'jadwalMaster' => $jadwalMaster,
            'jadwals' => $agendas,
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
        $peserta = Peserta::whereIn('id', $peserta_ids)->get();
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

            // Normalisasi waktu (hindari parsing format aneh)
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
                'peserta_id.*' => 'exists:peserta,id',
                'tim_id' => 'nullable|array',
                'tim_id.*' => 'nullable|exists:tim,id',
                'juri_id' => 'nullable|exists:juri,id',
            ]);

            \Log::info('Validasi request berhasil');

            // Tangani tanggal
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

            // Ambil peserta tim
            $pesertaLangsung = $request->peserta_id ?? [];
            $pesertaTim = [];
            if ($request->tim_id) {
                $pesertaTim = \App\Models\Bergabung::whereIn('tim_id', $request->tim_id)
                    ->pluck('peserta_id')
                    ->unique()
                    ->toArray();
            }
            $allPesertaId = array_unique(array_merge($pesertaLangsung, $pesertaTim));

            $force = $request->boolean('force');

            // Cek bentrok
            $cekWaktu = function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->whereBetween('waktu_mulai', [$waktuMulai, $waktuSelesai])
                    ->orWhereBetween('waktu_selesai', [$waktuMulai, $waktuSelesai])
                    ->orWhere(function ($q) use ($waktuMulai, $waktuSelesai) {
                        $q->where('waktu_mulai', '<=', $waktuMulai)
                            ->where('waktu_selesai', '>=', $waktuSelesai);
                    });
            };

            $bentrokVenue = $venueId && $venueId != $agenda->venue_id && Agenda::where('venue_id', $venueId)
                ->whereDate('tanggal', $tanggal)
                ->where('id', '!=', $agenda->id)
                ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                    $query->whereBetween('waktu_mulai', [$waktuMulai, $waktuSelesai])
                        ->orWhereBetween('waktu_selesai', [$waktuMulai, $waktuSelesai])
                        ->orWhere(function ($q) use ($waktuMulai, $waktuSelesai) {
                            $q->where('waktu_mulai', '<=', $waktuMulai)
                                ->where('waktu_selesai', '>=', $waktuSelesai);
                        });
                })
                ->exists();


            $bentrokPeserta = !empty($allPesertaId) && Agenda::whereDate('tanggal', '=', $tanggal)
                ->where('id', '!=', $agenda->id)
                ->where($cekWaktu)
                ->where(function ($query) use ($allPesertaId) {
                    $query->whereHas('peserta', function ($query) use ($allPesertaId) {
                        $query->whereIn('peserta.id', $allPesertaId);
                    });
                })
                ->exists();

            $bentrokJuri = $juriId && Agenda::where('juri_id', $juriId)
                ->whereDate('tanggal', '=', $tanggal) // <-- tambahkan ini juga
                ->where('id', '!=', $agenda->id)
                ->where($cekWaktu)
                ->exists();


            // if (($bentrokVenue || $bentrokPeserta || $bentrokJuri) && !$force) {
            //     $errorMsg = collect([
            //         $bentrokVenue ? 'Bentrok jadwal di venue.' : null,
            //         $bentrokPeserta ? 'Bentrok jadwal peserta.' : null,
            //         $bentrokJuri ? 'Bentrok jadwal juri.' : null,
            //     ])->filter()->implode(' ');
            //     return back()->withInput()->with('error_force', $errorMsg);
            // }

            if (!$force && ($bentrokVenue || $bentrokPeserta || $bentrokJuri)) {
                $errorMsg = collect([
                    $bentrokVenue ? 'Bentrok jadwal venue.' : null,
                    $bentrokPeserta ? 'Bentrok jadwal peserta.' : null,
                    $bentrokJuri ? 'Bentrok jadwal juri.' : null,
                ])->filter()->implode(' ');
                return back()->withInput()->with('error_force', $errorMsg);
            }


            // Simpan update
            $agenda->mata_lomba_id = $request->mata_lomba_id;
            $agenda->tanggal = $tanggal;
            $agenda->waktu_mulai = $waktuMulai;
            $agenda->waktu_selesai = $waktuSelesai;
            $agenda->kegiatan = $request->kegiatan;
            $agenda->venue_id = $venueId;
            $agenda->juri_id = $juriId;
            $agenda->save();

            $agenda->peserta()->sync($pesertaLangsung);

            $timId = array_filter($request->tim_id ?? [], function ($id) {
                return !empty($id);
            });

            $agenda->tim()->sync($timId);

            \Log::info('Agenda berhasil diperbarui', ['agenda_id' => $agenda->id]);

            return redirect()->route('jadwal.change', ['id' => $agenda->jadwal_id])
                ->with('success', 'Jadwal berhasil diperbarui' . ($force ? ' meskipun ada bentrok.' : '.'));
        } catch (\Throwable $e) {
            \Log::error('Gagal update agenda', [
                'agenda_id' => $agenda_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
            return back()->withInput()->with('error_force', 'Terjadi kesalahan saat memperbarui jadwal.');
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
        ]);

        Log::debug('Data yang divalidasi:', $validated);

        $hari = $request->input('hari', []);
        $waktuMulai = $request->input('waktu_mulai', []);
        $waktuSelesai = $request->input('waktu_selesai', []);
        $savingTime = $request->input('saving_time', []);

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

        $jadwals = Agenda::with(['mataLomba', 'venue', 'peserta', 'juri', 'tim'])
            ->where('jadwal_id', $jadwal->id)
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
        ]);

        $force = $request->input('force', false);
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        $bentrokJadwal = Agenda::where('venue_id', $request->venue_id)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->whereBetween('waktu_mulai', [$waktuMulai, $waktuSelesai])
                    ->orWhereBetween('waktu_selesai', [$waktuMulai, $waktuSelesai])
                    ->orWhere(function ($q) use ($waktuMulai, $waktuSelesai) {
                        $q->where('waktu_mulai', '<=', $waktuMulai)
                            ->where('waktu_selesai', '>=', $waktuSelesai);
                    });
            })
            ->exists();

        if ($bentrokJadwal && !$force) {
            return back()->withInput()->with('error_force', 'Terjadi bentrok jadwal. Apakah Anda yakin ingin melanjutkan penambahan data?');
        }

        // Buat atau ambil jadwal
        $jadwal = Jadwal::firstOrCreate(
            ['nama_jadwal' => $request->nama_jadwal, 'tahun' => $request->tahun, 'version' => $request->version],
            ['venue_id' => $request->venue_id]
        );

        // Simpan agenda
        $agenda = Agenda::create([
            'jadwal_id' => $jadwal->id,
            'mata_lomba_id' => $request->mata_lomba_id,
            'tanggal' => $request->tanggal,
            'kegiatan' => $request->kegiatan,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'venue_id' => $request->venue_id,
            'juri_id' => $request->juri_id,
        ]);

        // Simpan relasi peserta dan tim
        $agenda->peserta()->attach($request->peserta_id);
        if ($request->filled('tim_id')) {
            $agenda->tim()->attach($request->tim_id);
        }

        return redirect()->route('jadwal.change', ['id' => $jadwal->id])->with('success', 'Agenda berhasil ditambahkan.');

    }

    public function prosesSwitch(Request $request)
    {
        $selectedIds = $request->input('switch_ids', []);

        Log::info('Switch request initiated', ['selected_ids' => $selectedIds]);

        if (count($selectedIds) !== 2) {
            Log::warning('Jumlah agenda yang dipilih tidak sama dengan 2');
            return redirect()->back()->with('error', 'Anda harus memilih tepat 2 agenda untuk ditukar.');
        }

        [$id1, $id2] = $selectedIds;
        $agenda1 = Agenda::with('peserta', 'juri', 'jadwal')->find($id1);
        $agenda2 = Agenda::with('peserta', 'juri', 'jadwal')->find($id2);

        Log::info('Agenda ditemukan', [
            'agenda1' => $agenda1,
            'agenda2' => $agenda2
        ]);

        if (!$agenda1 || !$agenda2) {
            Log::error('Salah satu agenda tidak ditemukan', compact('agenda1', 'agenda2'));
            return redirect()->back()->with('error', 'Agenda tidak ditemukan.');
        }

        // Patokan jadwal_id dari masing-masing agenda
        $jadwalId1 = $agenda1->jadwal_id;
        $jadwalId2 = $agenda2->jadwal_id;

        // cek bentrok peserta untuk agenda1 di waktu agenda2, hanya dalam jadwal yang sama dengan agenda1
        $conflictPeserta1Agenda = Agenda::where('id', '!=', $agenda1->id)
            ->where('jadwal_id', $jadwalId1)
            ->whereHas('peserta', function ($q) use ($agenda1) {
                $q->whereIn('peserta_id', $agenda1->peserta->pluck('id'));
            })
            ->where(function ($q) use ($agenda2) {
                $q->whereBetween('waktu_mulai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                    ->orWhereBetween('waktu_selesai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                    ->orWhere(function ($q2) use ($agenda2) {
                        $q2->where('waktu_mulai', '<=', $agenda2->waktu_mulai)
                            ->where('waktu_selesai', '>=', $agenda2->waktu_selesai);
                    });
            })->get();

        // dd($conflictPeserta1Agenda);

        // cek bentrok peserta untuk agenda2 di waktu agenda1, hanya dalam jadwal yang sama dengan agenda2
        $conflictPeserta2Agenda = Agenda::where('id', '!=', $agenda2->id)
            ->where('jadwal_id', $jadwalId2)
            ->whereHas('peserta', function ($q) use ($agenda2) {
                $q->whereIn('peserta_id', $agenda2->peserta->pluck('id'));
            })
            ->where(function ($q) use ($agenda1) {
                $q->whereBetween('waktu_mulai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                    ->orWhereBetween('waktu_selesai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                    ->orWhere(function ($q2) use ($agenda1) {
                        $q2->where('waktu_mulai', '<=', $agenda1->waktu_mulai)
                            ->where('waktu_selesai', '>=', $agenda1->waktu_selesai);
                    });
            })->get();

        // cek bentrok juri agenda1 di waktu agenda2, hanya dalam jadwal yang sama dengan agenda1
        $conflictJuri1Agenda = collect();
        if ($agenda1->juri_id) {
            $conflictJuri1Agenda = Agenda::where('id', '!=', $agenda1->id)
                ->where('jadwal_id', $jadwalId1)
                ->whereNotNull('juri_id')
                ->where('juri_id', $agenda1->juri_id)
                ->where(function ($q) use ($agenda1, $agenda2) {
                    $q->where(function ($q2) use ($agenda1, $agenda2) {
                        $q2->where('mata_lomba_id', '!=', $agenda2->mata_lomba_id)
                            ->orWhere('venue_id', '!=', $agenda2->venue_id);
                    });
                })
                ->where(function ($q) use ($agenda2) {
                    $q->whereBetween('waktu_mulai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                        ->orWhereBetween('waktu_selesai', [$agenda2->waktu_mulai, $agenda2->waktu_selesai])
                        ->orWhere(function ($q2) use ($agenda2) {
                            $q2->where('waktu_mulai', '<=', $agenda2->waktu_mulai)
                                ->where('waktu_selesai', '>=', $agenda2->waktu_selesai);
                        });
                })->get();
        }

        // cek bentrok juri agenda2 di waktu agenda1, hanya dalam jadwal yang sama dengan agenda2
        $conflictJuri2Agenda = collect();
        if ($agenda2->juri_id) {
            $conflictJuri2Agenda = Agenda::where('id', '!=', $agenda2->id)
                ->where('jadwal_id', $jadwalId2)
                ->whereNotNull('juri_id')
                ->where('juri_id', $agenda2->juri_id)
                ->where(function ($q) use ($agenda1, $agenda2) {
                    $q->where(function ($q2) use ($agenda1, $agenda2) {
                        $q2->where('mata_lomba_id', '!=', $agenda1->mata_lomba_id)
                            ->orWhere('venue_id', '!=', $agenda1->venue_id);
                    });
                })
                ->where(function ($q) use ($agenda1) {
                    $q->whereBetween('waktu_mulai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                        ->orWhereBetween('waktu_selesai', [$agenda1->waktu_mulai, $agenda1->waktu_selesai])
                        ->orWhere(function ($q2) use ($agenda1) {
                            $q2->where('waktu_mulai', '<=', $agenda1->waktu_mulai)
                                ->where('waktu_selesai', '>=', $agenda1->waktu_selesai);
                        });
                })->get();
        }

        $conflictPeserta1 = $conflictPeserta1Agenda->isNotEmpty();
        $conflictPeserta2 = $conflictPeserta2Agenda->isNotEmpty();
        $conflictJuri1 = $conflictJuri1Agenda->isNotEmpty();
        $conflictJuri2 = $conflictJuri2Agenda->isNotEmpty();

        Log::info('Hasil pengecekan konflik peserta', [
            'conflictPeserta1' => $conflictPeserta1,
            'conflictPeserta2' => $conflictPeserta2,
        ]);
        Log::info('Hasil pengecekan konflik juri', [
            'conflictJuri1' => $conflictJuri1,
            'conflictJuri2' => $conflictJuri2,
        ]);

        $conflictDetail = [];
        if ($conflictPeserta1) {
            $times = $conflictPeserta1Agenda->map(function ($a) {
                return "{$a->waktu_mulai} sampai {$a->waktu_selesai}";
            })->implode(', ');
            $conflictDetail[] = "Peserta pada agenda pertama bentrok dengan agenda lain di waktu: $times.";
        }
        if ($conflictPeserta2) {
            $times = $conflictPeserta2Agenda->map(function ($a) {
                return "{$a->waktu_mulai} sampai {$a->waktu_selesai}";
            })->implode(', ');
            $conflictDetail[] = "Peserta pada agenda kedua bentrok dengan agenda lain di waktu: $times.";
        }
        if ($conflictJuri1) {
            $times = $conflictJuri1Agenda->map(function ($a) {
                return "{$a->waktu_mulai} sampai {$a->waktu_selesai}";
            })->implode(', ');
            $conflictDetail[] = "Juri pada agenda pertama bentrok dengan agenda lain di waktu: $times.";
        }
        if ($conflictJuri2) {
            $times = $conflictJuri2Agenda->map(function ($a) {
                return "{$a->waktu_mulai} sampai {$a->waktu_selesai}";
            })->implode(', ');
            $conflictDetail[] = "Juri pada agenda kedua bentrok dengan agenda lain di waktu: $times.";
        }

        if ($conflictDetail) {
            Log::warning('Terdapat bentrok saat proses switch', $conflictDetail);
            return redirect()->back()->with('error', 'Terdapat bentrok: ' . implode(' ', $conflictDetail));
        }

        Log::info('Sebelum switch', [
            'agenda1_mulai' => $agenda1->waktu_mulai,
            'agenda1_selesai' => $agenda1->waktu_selesai,
            'agenda2_mulai' => $agenda2->waktu_mulai,
            'agenda2_selesai' => $agenda2->waktu_selesai,
        ]);

        DB::transaction(function () use ($agenda1, $agenda2) {
            [$agenda1->waktu_mulai, $agenda2->waktu_mulai] = [$agenda2->waktu_mulai, $agenda1->waktu_mulai];
            [$agenda1->waktu_selesai, $agenda2->waktu_selesai] = [$agenda2->waktu_selesai, $agenda1->waktu_selesai];

            $agenda1->save();
            $agenda2->save();
        });

        Log::info('Berhasil tukar waktu agenda', [
            'agenda1_new_mulai' => $agenda1->waktu_mulai,
            'agenda1_new_selesai' => $agenda1->waktu_selesai,
            'agenda2_new_mulai' => $agenda2->waktu_mulai,
            'agenda2_new_selesai' => $agenda2->waktu_selesai,
        ]);

        return redirect()->route('jadwal.index')->with('success', 'Waktu mulai dan selesai agenda berhasil ditukar.');
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

        $filtered = [];

        foreach ($slots as $index => $slot) {
            // Log::debug("Slot ke-$index sebelum parse: waktu_mulai = {$slot['waktu_mulai']}, waktu_selesai = {$slot['waktu_selesai']}");

            $slotStart = Carbon::parse($slot['waktu_mulai']);
            $slotEnd = Carbon::parse($slot['waktu_selesai']);

            // $slotDate = Carbon::parse($slot['waktu_mulai'])->format('Y-m-d');

            $slotDate = $slot['tanggal'];

            $startTime = $slotStart->format('H:i');
            $endTime = $slotEnd->format('H:i');

            // Log::debug("Memeriksa slot ke-$index: {$slotStart} - {$slotEnd} (Hari: $slotDate , $startTime - $endTime)");

            // Cek hari
            if ($hariConstraint && !in_array($slotDate, $hariConstraint)) {
                // Log::debug("❌ Slot ditolak karena tanggal $slotDate tidak termasuk dalam constraint.");
                continue;
            }

            // Cek waktu mulai
            if ($waktuMulaiConstraint && $startTime < $waktuMulaiConstraint) {
                // Log::debug("❌ Slot ditolak karena mulai $startTime < batas constraint mulai $waktuMulaiConstraint");
                continue;
            }

            // Cek waktu selesai
            if ($waktuSelesaiConstraint && $endTime > $waktuSelesaiConstraint) {
                // Log::debug("❌ Slot ditolak karena selesai $endTime > batas constraint selesai $waktuSelesaiConstraint");
                continue;
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

        $kategoriKeys = array_keys($domain);
        $solutions = [];

        for ($attempt = 0; $attempt < $maxSolutions; $attempt++) {
            // Copy domain agar tiap versi punya acakan berbeda
            $shuffledDomain = $domain;

            // Ambil dan acak urutan kategori
            $kategoriKeys = array_keys($shuffledDomain);
            shuffle($kategoriKeys); // ✅ acak urutan kategori

            // Acak nilai-nilai dalam setiap kategori
            // foreach ($kategoriKeys as $key) {
            //     shuffle($shuffledDomain[$key]); // ✅ acak isi kategori
            // }

            $currentSolution = [];

            $backtrackRecursive = function ($depth, &$currentSolution) use (&$backtrackRecursive, $shuffledDomain, $kategoriKeys, &$solutions) {
                if ($depth === count($kategoriKeys)) {
                    $solutions[] = $currentSolution;

                    // Log solusi saat ini
                    $versionNumber = count($solutions);
                    Log::info("Solusi ke-{$versionNumber}: " . json_encode($currentSolution));

                    return true; // satu solusi ditemukan
                }

                $currentKey = $kategoriKeys[$depth];
                $slots = $shuffledDomain[$currentKey];

                foreach ($slots as $slot) {
                    if ($this->checkConstraint($slot, $currentSolution)) {
                        $currentSolution[] = $slot;
                        if ($backtrackRecursive($depth + 1, $currentSolution)) {
                            return true;
                        }
                        array_pop($currentSolution);
                    }
                }

                return false;
            };

            $backtrackRecursive(0, $currentSolution);
        }

        if (count($solutions) > 0) {
            Log::info("Backtracking berhasil menemukan " . count($solutions) . " solusi.");
            return $solutions;
        }

        Log::warning("Backtracking gagal: tidak ditemukan solusi valid.");
        return null;
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