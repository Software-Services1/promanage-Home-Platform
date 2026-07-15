<?php

namespace App\Http\Controllers;

use App\Models\ContentPlan;
use App\Models\Leave;
use App\Models\MaintenanceItem;
use App\Models\Task;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, PointsService $points)
    {
        $month  = $this->activeMonth($request);
        $tab    = $request->query('tab', 'points');
        $userId = $request->query('user') ? (int) $request->query('user') : null;

        $employees = User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))->orderBy('name')->get();
        $data = $this->collect($tab, $month, $points, $userId);

        return view('reports.index', compact('month', 'tab', 'data', 'employees', 'userId'));
    }

    /** تصدير CSV — يستثني بيانات الرواتب لغير الأدمن دائماً. */
    public function export(Request $request, PointsService $points): StreamedResponse
    {
        $month  = $this->activeMonth($request);
        $tab    = $request->query('tab', 'points');
        $userId = $request->query('user') ? (int) $request->query('user') : null;
        $rows   = $this->collect($tab, $month, $points, $userId);

        $filename = "report-{$tab}-{$month}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM لدعم العربية في Excel
            fputcsv($out, $rows['head']);
            foreach ($rows['rows'] as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function collect(string $tab, string $month, PointsService $points, ?int $userId = null): array
    {
        return match ($tab) {
            'content' => [
                'head' => ['المنصة', 'التاريخ', 'نوع المحتوى', 'نوع المنشور', 'الحالة', 'المسؤول'],
                'rows' => ContentPlan::with('assignee')->forMonth($month)
                    ->when($userId, fn ($q) => $q->where('assigned_to', $userId))->get()
                    ->map(fn ($p) => [$p->platform, $p->plan_date->format('Y-m-d'), $p->content_type, $p->post_type, $p->status, $p->assignee?->name])->all(),
            ],
            'tasks' => [
                'head' => ['المهمة', 'النوع', 'الموظف', 'المرحلة', 'النقاط', 'تأخّر'],
                'rows' => Task::with(['user', 'assignees'])->forMonth($month)
                    ->when($userId, fn ($q) => $q->whereHas('assignees', fn ($a) => $a->where('users.id', $userId)))->get()
                    ->map(fn ($t) => [$t->title, $t->typeLabel(), $t->assignees->pluck('name')->join('، ') ?: optional($t->user)->name, $t->stage, $points->taskPoints($t), $t->is_late ? 'نعم' : 'لا'])->all(),
            ],
            'maint' => [
                'head' => ['العملية', 'النوع', 'التاريخ', 'النقاط', 'الحالة'],
                'rows' => MaintenanceItem::forMonth($month)
                    ->when($userId, fn ($q) => $q->where('user_id', $userId))->get()
                    ->map(fn ($m) => [$m->title, $m->typeLabel(), $m->work_date->format('Y-m-d'), $m->points(), $m->status])->all(),
            ],
            'attendance' => [
                'head' => ['الموظف', 'أيام الإجازة', 'زائد'],
                'rows' => User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
                    ->when($userId, fn ($q) => $q->where('id', $userId))->get()
                    ->map(function ($u) use ($month) {
                        $d = (int) Leave::where('user_id', $u->id)->where('status', 'approved')->forMonth($month)->sum('days');
                        return [$u->name, $d, max(0, $d - 2)];
                    })->all(),
            ],
            default => [ // points
                'head' => ['الموظف', 'الدور', 'المحقّق', 'المستهدف', 'النسبة'],
                'rows' => User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
                    ->when($userId, fn ($q) => $q->where('id', $userId))->with('roles')->get()
                    ->map(function ($u) use ($points, $month) {
                        $p = $points->totalPoints($u, $month);
                        $t = (int) $u->target;
                        return [$u->name, $u->primaryRole(), $p, $t ?: '—', $t ? round($p / $t * 100) . '%' : '0%'];
                    })->all(),
            ],
        };
    }
}
