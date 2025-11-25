<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class EmailVindiNFSe extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $number = $this->data['number'];

        $path = public_path("nfse_temp/{$number}.pdf");

        return $this->subject('Sua NFS-e está disponível!')
            ->view('emails.nfse')
            ->attach($path, [
                'as'   => "nfse-{$number}.pdf",
                'mime' => 'application/pdf',
            ]);
    }
}
