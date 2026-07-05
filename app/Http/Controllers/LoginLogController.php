<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;

class LoginLogController extends Controller
{
    public function index()
    {
        // المستخدمون مع حالة الاتصال وآخر دخول
        $users = User::orderByDesc('last_seen_at')->get();
        $online = $users->filter->isOnline()->count();

        // آخر عمليات الدخول
        $logs = LoginLog::with('user')->latest('logged_in_at')->limit(100)->get();

        return view('logins.index', compact('users', 'online', 'logs'));
    }
}
