<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * الشهر النشط (Y-m): من الطلب أو الجلسة أو الشهر الحالي.
     * يُحفظ في الجلسة ليبقى ثابتاً عبر الواجهات — هكذا يكون لكل شهر سياقه.
     */
    protected function activeMonth(Request $request): string
    {
        $month = $request->query('month', session('active_month', now()->format('Y-m')));
        if (! preg_match('/^\d{4}-\d{2}$/', (string) $month)) {
            $month = now()->format('Y-m');
        }
        session(['active_month' => $month]);
        return $month;
    }
}
