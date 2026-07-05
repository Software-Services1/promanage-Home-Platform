<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $level = 'normal', // high | normal
    ) {}

    /** قنوات الإرسال: الجرس دائماً، والبريد فقط إذا كان مُهيّأً (غير log/array). */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (! in_array(config('mail.default'), ['log', 'array', null], true)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'level'   => $this->level,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('ProManage Flow — ' . $this->title)
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line($this->message);

        if ($this->url) {
            $mail->action('فتح النظام', url($this->url));
        }

        return $mail->salutation('تحياتنا، فريق ProManage Flow');
    }
}
