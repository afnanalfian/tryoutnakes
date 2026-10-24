<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);

        return (new MailMessage)
            ->subject('Atur ulang kata sandi — '.setting('site_name'))
            ->view('emails.notification', [
                'title' => 'Atur ulang kata sandi',
                'body' => 'Kami menerima permintaan untuk mengatur ulang kata sandi akunmu. Tautan ini berlaku selama '.config('auth.passwords.users.expire', 60).' menit. Abaikan email ini jika kamu tidak memintanya.',
                'url' => $url,
                'actionText' => 'Atur Ulang Kata Sandi',
                'name' => $notifiable->name,
            ]);
    }
}
