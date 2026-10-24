<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi email — '.setting('site_name'))
            ->view('emails.notification', [
                'title' => 'Verifikasi alamat email',
                'body' => 'Satu langkah lagi! Klik tombol di bawah untuk memverifikasi email akunmu.',
                'url' => $this->verificationUrl($notifiable),
                'actionText' => 'Verifikasi Email',
                'name' => $notifiable->name,
            ]);
    }
}
