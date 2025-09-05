<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailConfirmationMail extends Mailable
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
        return $this->subject('Confirme seu e-mail')
                    ->view('emails.confirm_email')
                    ->with([
                        'user' => $this->user,
                        'confirmationUrl' => $this->confirmationUrl,
                    ]);
    }
}
