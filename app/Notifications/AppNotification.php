<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi serbaguna: tersimpan di database (lonceng notifikasi)
 * dan dikirim ke email (Brevo SMTP) melalui queue.
 */
class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Matikan email sementara (mis. saat seeding data contoh). */
    public static bool $muteMail = false;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public string $kind,
        public string $title,
        public string $body,
        public ?string $url = null,
        public ?string $actionText = null,
        public bool $sendMail = true,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($this->sendMail && ! static::$muteMail && setting('mail_enabled', '1') === '1' && ! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /** Notifikasi database tidak perlu antre; email masuk queue. */
    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title.' — '.setting('site_name'))
            ->view('emails.notification', [
                'title' => $this->title,
                'body' => $this->body,
                'url' => $this->url,
                'actionText' => $this->actionText,
                'name' => $notifiable->name ?? null,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'action' => $this->actionText,
        ];
    }
}
