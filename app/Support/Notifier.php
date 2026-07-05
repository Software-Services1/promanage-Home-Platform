<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class Notifier
{
    /**
     * إرسال إشعار لمجموعة مستخدمين بأمان: فشل البريد/الإرسال لا يوقف العملية الأساسية أبداً.
     *
     * @param  iterable  $users
     */
    public static function send($users, $notification): void
    {
        $recipients = collect($users)->filter()->unique('id')->values();
        if ($recipients->isEmpty()) {
            return;
        }

        try {
            NotificationFacade::send($recipients, $notification);
        } catch (\Throwable $e) {
            // الجرس الداخلي قد يكون حُفظ، وفشل البريد فقط — نسجّل ولا نكسر الطلب
            Log::warning('Notifier failed: ' . $e->getMessage());
        }
    }
}
