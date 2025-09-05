<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmacaoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $confirmationUrl;

    public function __construct($user, $confirmationUrl)
    {
        $this->user = $user;
        $this->confirmationUrl = $confirmationUrl;
    }

    public function build()
    {
        return $this->subject('Confirmação de E-mail')
                    ->view('emails.confirmacao')
                    ->with([
                        'user' => $this->user,
                        'confirmationUrl' => $this->confirmationUrl,
                    ]);
    }
}
