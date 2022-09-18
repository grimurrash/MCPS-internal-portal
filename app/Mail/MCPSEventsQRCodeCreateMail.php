<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MCPSEventsQRCodeCreateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $qrCodeUrl;
    private $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $qrCodeUrl)
    {
        $this->email = $email;
        $this->qrCodeUrl = $qrCodeUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->to($this->email)
            ->subject('Выездное совещание АНО "Авангард"')
            ->attach(storage_path('app/public/mcpsevent/Выездное совещание 18.08.pdf'))
            ->attach(storage_path('app/public/mcpsevent/Гимн РФ.docx'))
            ->view('emails.mcps-events-qr-code');
    }
}
