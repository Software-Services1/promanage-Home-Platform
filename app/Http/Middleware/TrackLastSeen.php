<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            // تحديث النشاط مرة كل دقيقة فقط لتقليل الكتابة على القاعدة
            $key = 'seen:' . $user->id;
            if (! Cache::has($key)) {
                Cache::put($key, true, now()->addMinute());
                $user->forceFill(['last_seen_at' => now()])->saveQuietly();
            }
        }
        return $next($request);
    }
}
