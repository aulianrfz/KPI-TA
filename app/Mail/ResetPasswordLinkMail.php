<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    public $link;

    public function __construct($nama, $link)
    {
        $this->nama = $nama;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Reset Password')
            ->view('auth.reset-password-link');
    }
}