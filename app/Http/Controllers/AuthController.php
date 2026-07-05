<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($data, $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'هذا الحساب معطّل. راجع المشرف العام.']);
            }
            $request->session()->regenerate();

            // تسجيل الدخول في السجل + تحديث آخر دخول/نشاط
            $user = $request->user();
            \App\Models\LoginLog::create([
                'user_id'      => $user->id,
                'ip'           => $request->ip(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 255),
                'logged_in_at' => now(),
            ]);
            $user->forceFill(['last_login_at' => now(), 'last_seen_at' => now()])->save();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
