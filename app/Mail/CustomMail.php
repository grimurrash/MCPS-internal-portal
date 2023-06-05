<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomMail extends Mailable
{
    use Queueable, SerializesModels;

    private $email;
    private $login;
    private $password;

    public function __construct(
        $email,
        $login,
        $password)
    {
        $this->email = $email;
        $this->login = $login;
        $this->password = $password;
    }


    public function build(): self
    {
        return $this
            ->to($this->email)
            ->subject('Портал «Содружество школьных театров города Москвы»')
            ->view('emails.custom', ['login' => $this->login, 'password' => $this->password]);
    }
}