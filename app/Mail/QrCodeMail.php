<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    public $nama_utama;      // bisa nama lomba ATAU nama event
    public $kategori;        // bisa kategori lomba, atau null
    public $qr_image_path;

    public function __construct($nama, $nama_utama, $kategori = null, $qr_image_path)
    {
        $this->nama = $nama;
        $this->nama_utama = $nama_utama;
        $this->kategori = $kategori;
        $this->qr_image_path = $qr_image_path;
    }

    public function build()
    {
        return $this->subject('QR Code Pendaftaran Anda')
            ->view('admin.emails.qr_code')
            ->with([
                'nama' => $this->nama,
                'nama_utama' => $this->nama_utama,
                'kategori' => $this->kategori,
                'qr_image_path' => $this->qr_image_path,
            ]);
    }
}

