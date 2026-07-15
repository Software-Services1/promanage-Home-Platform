<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\PointsService;
use App\Support\WorkTypes;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request, PointsService $points)
    {
        $month = $this->activeMonth($request);
        $user  = $request->user();

        $tasks = Task::with(['user','supervisor','assignees'])->visibleTo($user)->forMonth($month)
            ->orderByDesc('due_date')->orderByDesc('id')->get()
            ->map(function (Task $t) use ($points) {
                $t->setAttribute('computed_points', $points->taskPoints($t));
                return $t;
            });

        $assignees = User::where('is_active', true)->get();
        $supervisors = User::role(['supervisor', 'manager', 'admin'])->get();
        $taskTypes = \App\Models\TaskType::where('is_active', true)->orderBy('label')->get();

        return view('tasks.index', [
            'month' => $month, 'tasks' => $tasks, 'stages' => WorkTypes::STAGES,
            'taskTypes' => $taskTypes, 'assignees' => $assignees, 'supervisors' => $supervisors,
        ]);
    }

    public function store(TaskRequest $request)
    {
        $this->authorize('create', Task::class);
        $task = Task::create($this->payload($request));
        $this->syncAssignees($task, $request->input('assignees', []));
        $task->load('assignees');

        // إشعار كل المصمّمين المشاركين (أولوية عالية) + المشرف المتابِع + مدير النظام
        $admins = \App\Models\User::role('admin')->get();
        \App\Support\Notifier::send($task->assignees, new \App\Notifications\ActivityNotification(
            'مهمة جديدة مُسندة إليك', "أُسندت إليك مهمة: {$task->title}", route('tasks.index'), 'high'));
        \App\Support\Notifier::send(collect([$task->supervisor])->merge($admins), new \App\Notifications\ActivityNotification(
            'مهمة جديدة', "أُنشئت مهمة «{$task->title}» ({$task->assignees->count()} مصمّم)", route('tasks.index')));

        return back()->with('ok', 'تمت إضافة المهمة.');
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task->update($this->payload($request));
        $this->syncAssignees($task, $request->input('assignees', []));
        return back()->with('ok', 'تم حفظ المهمة.');
    }

    /** مزامنة المصمّمين المشاركين مع نوع عمل كلٍّ منهم؛ أول مصمّم يُعتبر القائد (user_id). */
    private function syncAssignees(Task $task, array $assignees): void
    {
        $map = [];
        foreach ($assignees as $a) {
            if (! empty($a['user_id'])) {
                $map[(int) $a['user_id']] = ['type' => $a['type'] ?? null];
            }
        }
        $task->assignees()->sync($map);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return back()->with('ok', 'تم حذف المهمة.');
    }

    /** تحديث حالة التنفيذ — متاح لصاحب المهمة ضمن حالات محددة، وللمدراء بكل الحالات. */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        // غير المدير (لا يملك تعديل المهام) مقيّد بحالات التنفيذ المسموحة فقط
        $allowed = $request->user()->can('update tasks')
            ? \App\Support\WorkTypes::STAGES
            : ['تصميم', 'تنفيذ', 'مراجعة', 'جاهز'];

        $data = $request->validate([
            'stage' => ['required', \Illuminate\Validation\Rule::in($allowed)],
        ]);

        $task->update(['stage' => $data['stage']]);

        // إشعار عند الإنجاز
        if (in_array($data['stage'], ['جاهز', 'منشور'], true)) {
            $admins = \App\Models\User::role('admin')->get();
            \App\Support\Notifier::send(collect([$task->supervisor])->merge($admins), new \App\Notifications\ActivityNotification(
                'إنجاز مهمة', "أنجز " . (optional($task->user)->name ?? 'المصمم') . " مهمة «{$task->title}» ({$data['stage']})", route('tasks.index'), 'high'));
        }

        return back()->with('ok', 'تم تحديث حالة المهمة.');
    }

    private function payload(TaskRequest $request): array
    {
        $assignees = $request->input('assignees', []);
        $lead = $assignees[0] ?? [];

        return [
            'title'       => $request->title,
            'description' => $request->description,
            'type'        => $lead['type'] ?? 'post_story',
            'stage'       => $request->stage,
            'user_id'     => $lead['user_id'] ?? null,
            'supervisor_id' => $request->supervisor_id ?: null,
            'due_date'    => $request->due_date,
            'is_late'     => $request->boolean('is_late'),
            'is_creative' => $request->boolean('is_creative'),
        ];
    }

    private function stageGroup(string $s): string
    {
        if (in_array($s, ['فكرة', 'خطة'])) return 'فكرة';
        if (in_array($s, ['تصميم', 'تنفيذ'])) return 'تصميم';
        if ($s === 'مراجعة') return 'مراجعة';
        return 'منشور';
    }
}
