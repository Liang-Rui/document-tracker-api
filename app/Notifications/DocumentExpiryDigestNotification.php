<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryDigestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($documents)
    {
        $this->documents = $documents;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $today = now();

        $expired_doc = $this->documents->filter(fn($doc) => $doc->expires_at->lt($today));
        $expiringSoon = $this->documents->filter(fn($doc) => $doc->expires_at->gte($today));

        $mail = (new MailMessage)
            ->subject('Document Expiry Reminder')
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($expiringSoon->isNotEmpty()) {
            $mail->line('The following documents are expiring soon:');

            foreach ($expiringSoon as $doc) {
                $mail->line('- ' . $doc->name . ' (expires: ' . $doc->expires_at->format('Y-m-d') . ')');
            }
        }

        if ($expired_doc->isNotEmpty()) {
            $mail->line('The following documents are expired, please archive them:');

            foreach ($expired_doc as $doc) {
                $mail->line('- ' . $doc->name . ' (expired: ' . $doc->expires_at->format('Y-m-d') . ')');
            }
        }

        return $mail
            ->line('Please take necessary action.')
            ->salutation('Regards, Document Tracker');
    }
}
