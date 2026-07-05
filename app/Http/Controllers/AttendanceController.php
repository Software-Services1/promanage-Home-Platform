<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequest;
use App\Models\Leave;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request, PayrollService $payroll)
    {
        $month = $this->activeMonth($request);
        $user  = $request->user();
        $canManage = $user->hasAnyRole(['admin', 'supervisor']);

        $leaves = Leave::with('user')->visibleTo($user)->forMonth($month)
            ->orderByDesc('from_date')->get();

        $base = $canManage
            ? User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))->get()
            : User::where('id', $user->id)->get();

        $team = $base
            ->map(function (User $u) use ($payroll, $month) {
                $u->setAttribute('leave_days', $payroll->approvedLeaveDays($u, $month));
                return $u;
            });

        return view('attendance.index', compact('month', 'leaves', 'team', 'canManage'));
    }

    public function store(LeaveRequest $request)
    {
        // الموظف لا يطلب إلا لنفسه (منع التلاعب عبر user_id)
        $targetUser = $request->user()->can('view all leaves') ? (int) $request->user_id : $request->user()->id;
        $from = Carbon::parse($request->from_date);
        $to   = Carbon::parse($request->to_date);
        Leave::create([
            'user_id'      => $targetUser,
            'from_date'    => $from->toDateString(),
            'to_date'      => $to->toDateString(),
            'days'         => $from->diffInDays($to) + 1,
            'status'       => 'pending',
            'reason'       => $request->reason ?: '—',
            'requested_at' => now()->toDateString(),
        ]);
        return back()->with('ok', 'تم إرسال طلب الإجازة.');
    }

    public function setStatus(Request $request, Leave $leave)
    {
        $this->authorize('approve', $leave);
        $request->validate(['status' => 'required|in:approved,rejected']);
        $leave->update(['status' => $request->status]);
        return back()->with('ok', $request->status === 'approved' ? 'تم قبول الإجازة.' : 'تم رفض الطلب.');
    }
}
