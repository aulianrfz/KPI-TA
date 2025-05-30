<?php

namespace App\Exports;

use App\Models\Pendaftar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendaftarExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $sort;

    public function __construct($search = null, $sort = 'asc')
    {
        $this->search = $search;
        $this->sort = $sort;
    }

    public function collection()
    {
        $query = Pendaftar::with('peserta')
            ->whereNotNull('url_qrCode')
            ->where('url_qrCode', '!=', '');

        if ($this->search) {
            $query->whereHas('peserta', function ($q) {
                $q->where('nama_peserta', 'like', "%{$this->search}%")
                  ->orWhere('nim', 'like', "%{$this->search}%")
                  ->orWhere('no_hp', 'like', "%{$this->search}%")
                  ->orWhere('institusi', 'like', "%{$this->search}%");
            });
        }

        $query->join('peserta', 'pendaftar.peserta_id', '=', 'peserta.id')
              ->orderBy('peserta.nama_peserta', $this->sort)
              ->select('pendaftar.*');

        return $query->get()->map(function ($item) {
            return [
                $item->peserta->nama_peserta ?? '-',
                $item->peserta->institusi ?? '-',
                $item->peserta->no_hp ?? '-',
                $item->peserta->nim ?? '-',
                $item->created_at->format('d-m-Y H:i'),
                $item->kehadiran->status ?? 'Belum Hadir'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Peserta',
            'Institusi',
            'No HP',
            'NIM',
            'Waktu Daftar',
            'Status Kehadiran',
        ];
    }
}
