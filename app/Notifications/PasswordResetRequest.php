<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     *
     * @param $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notif
     * ication.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url = url('/auth/reset-password/' . $this->token);
        return (new MailMessage)
            ->subject('Сброс пароля для портала МЦПС')
            ->greeting('Здравствуйте!')
            ->line('Вы получаете это письмо, потому что мы получили запрос на сброс пароля для вашей учетной записи.')
            ->action('Сброс пароля', url($url))
            ->line('Если вы не запросили сброс пароля, никаких дальнейших действий не требуется.')
            ->salutation('C уважением, ваш IT-шник');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
