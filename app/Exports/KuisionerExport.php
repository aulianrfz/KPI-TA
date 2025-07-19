<?php

namespace App\Exports;

use App\Models\JawabanKuisioner;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KuisionerExport implements FromQuery, WithHeadings
{
    protected $eventId;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    public function query()
    {
        return JawabanKuisioner::query()
            ->select(
                'peserta.nama_peserta',
                'peserta.email',
                'peserta.institusi',
                'mata_lomba.nama_lomba',
                'kuisioner.pertanyaan',
                'jawaban_kuisioners.jawaban'
            )
            ->join('kuisioner', 'jawaban_kuisioners.kuisioner_id', '=', 'kuisioner.id')
            ->join('peserta', 'jawaban_kuisioners.peserta_id', '=', 'peserta.id')
            ->join('pendaftar', 'peserta.id', '=', 'pendaftar.peserta_id')
            ->join('mata_lomba', 'pendaftar.mata_lomba_id', '=', 'mata_lomba.id')
            ->join('kategori', 'mata_lomba.kategori_id', '=', 'kategori.id')
            ->join('event', 'kategori.event_id', '=', 'event.id')
            ->where('event.id', $this->eventId);
    }

    public function headings(): array
    {
        return [
            'Nama Peserta',
            'Email',
            'Institusi',
            'Nama Lomba',
            'Pertanyaan',
            'Jawaban',
        ];
    }
}

