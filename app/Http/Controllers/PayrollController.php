<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request, PayrollService $payroll)
    {
        $viewer = $request->user();
        $month  = $this->activeMonth($request);
        $canAll = $viewer->can('view all salaries');

        // المدير يرى الجميع؛ غيره يرى راتبه فقط — لا وصول لرواتب الآخرين بأي حال
        $targets = $canAll
            ? User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))->get()
            : User::where('id', $viewer->id)->get();

        $rows = $targets->map(fn (User $u) => ['user' => $u, 'pay' => $payroll->compute($u, $month)]);
        $totalNet = $rows->sum(fn ($r) => $r['pay']['net']);

        return view('payroll.index', compact('month', 'rows', 'totalNet', 'canAll'));
    }
}
