<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdvisorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $email;
    private $number;

    public function __construct($number, $email)
    {
        $this->number = $number;
        $this->email = $email;
        $this->onQueue('advosor');
        $this->onConnection('redis');
    }


    public function build(): self
    {
        $fileData = file_get_contents(storage_path("app/public/file-storage/{$this->number}.pdf"));
        $text = "Уважаемый участник конкурса «Навигаторы детства»! 
<br>
<br>
К сожалению, произошел технический сбой при генерации сертификатов финалистов и участников.
Вам была направлена некорректная информацию о результатах конкурсного отбора. 
<br>
<br>
😢 Вынуждены Вам сообщить, что Вы не вошли в число финалистов конкурса. 
<br>
<br>
Приносим Вам свои искренние извинения и желаем успехов в дальнейшей профессиональной деятельности!";
        return $this
            ->to($this->email)
            ->attachData($fileData, 'СЕРТИФИКАТ.pdf')
            ->subject('«Навигатор детства»')
            ->html($text);
    }
}