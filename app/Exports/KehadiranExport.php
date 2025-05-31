<?php

namespace App\Exports;

use App\Models\Pendaftar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KehadiranExport implements FromCollection, WithHeadings
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Pendaftar::with(['peserta', 'mataLomba', 'kehadiran']);

        if ($this->search) {
            $query->whereHas('peserta', function ($q) {
                $q->where('nama_peserta', 'like', "%{$this->search}%")
                  ->orWhere('institusi', 'like', "%{$this->search}%");
            });
        }

        return $query->get()->map(function ($p) {
            return [
                'Nama Peserta'   => $p->peserta->nama_peserta ?? '-',
                'Institusi'      => $p->peserta->institusi ?? '-',
                'Kategori'       => $p->mataLomba->kategori->nama_kategori ?? '-',
                'Mata Lomba'     => $p->mataLomba->nama_lomba ?? '-',
                'Status'         => $p->kehadiran ? 'Hadir' : 'Belum Hadir',
                'Waktu Hadir'    => $p->kehadiran ? $p->kehadiran->tanggal->format('Y-m-d H:i:s') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama Peserta', 'Institusi', 'Kategori', 'Mata Lomba', 'Status', 'Waktu Hadir'];
    }
}
