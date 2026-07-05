<?php

namespace App\Http\Controllers;

use App\Models\ContentPlan;
use App\Models\Leave;
use App\Models\Task;
use App\Services\PointsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, PointsService $points)
    {
        $user = $request->user();

        // نطاق التقرير (من/إلى) — الافتراضي الشهر الحالي
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->endOfMonth()->toDateString());
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        $isAdmin   = $user->hasRole('admin');
        $isSup     = $user->hasRole('supervisor');

        $tasksInRange = Task::with('user')
            ->when(! $isAdmin && ! $isSup, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('due_date', [$from, $to])
            ->get();

        $myTasks = $tasksInRange->where('user_id', $user->id);
        $pts     = $points->totalPointsRange($user, $from, $to);

        $stats = $isAdmin
            ? [
                ['label' => 'مهام الفترة', 'value' => $tasksInRange->count()],
                ['label' => 'بانتظار الاعتماد', 'value' => ContentPlan::whereBetween('plan_date', [$from, $to])->where('approval_state', '!=', 'approved')->count()],
                ['label' => 'منشور بالفترة', 'value' => ContentPlan::whereBetween('plan_date', [$from, $to])->where('status', 'تم النشر')->count() + $tasksInRange->where('stage', 'منشور')->count()],
                ['label' => 'طلبات إجازة', 'value' => Leave::whereBetween('from_date', [$from, $to])->where('status', 'pending')->count()],
            ]
            : [
                ['label' => 'مهامي بالفترة', 'value' => $myTasks->count()],
                ['label' => 'منجزة', 'value' => $myTasks->whereIn('stage', ['منشور', 'جاهز'])->count()],
                ['label' => 'قيد العمل', 'value' => $myTasks->whereNotIn('stage', ['منشور', 'جاهز'])->count()],
                ['label' => 'نقاطي بالفترة', 'value' => $pts],
            ];

        $breakdown = $isSup ? [
            'direct'         => $points->directPointsRange($user, $from, $to),
            'from_designers' => round(0.30 * $points->designersTotalRange($from, $to), 1),
        ] : null;

        $recentTasks = ($isAdmin ? $tasksInRange : $myTasks)->take(6);

        return view('dashboard', compact('from', 'to', 'stats', 'pts', 'breakdown', 'recentTasks', 'isAdmin', 'isSup'));
    }
}
