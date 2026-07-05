<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('ok', 'تم تعليم كل الإشعارات كمقروءة.');
    }

    public function read(Request $request, string $id)
    {
        $n = $request->user()->notifications()->find($id);
        if ($n) {
            $n->markAsRead();
            $url = $n->data['url'] ?? null;
            if ($url) {
                return redirect($url);
            }
        }
        return back();
    }
}
