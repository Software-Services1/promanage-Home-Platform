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
        $filters = [
            'user_id' => $request->query('user_id'),
            'type'    => $request->query('type'),
            'stage'   => $request->query('stage'),
            'date'    => $request->query('date'),
        ];

        $tasks = Task::with(['user','supervisor'])->visibleTo($user)->forMonth($month)
            ->when($filters['user_id'], fn ($q) => $q->where('user_id', $filters['user_id']))
            ->when($filters['type'], fn ($q) => $q->where('type', $filters['type']))
            ->when($filters['stage'], fn ($q) => $q->where('stage', $filters['stage']))
            ->when($filters['date'], fn ($q) => $q->whereDate('due_date', $filters['date']))
            ->orderByDesc('due_date')->get()
            ->map(function (Task $t) use ($points) {
                $t->setAttribute('computed_points', $points->taskPoints($t));
                return $t;
            });

        // تجميع في أعمدة الكانبان حسب كل مرحلة (للسحب والإفلات)
        $groups = [];
        foreach (WorkTypes::STAGES as $st) {
            $groups[$st] = [];
        }
        foreach ($tasks as $t) {
            $groups[$t->stage][] = $t;
        }

        $assignees = User::where('is_active', true)->get();
        $supervisors = User::role(['supervisor', 'manager', 'admin'])->get();
        $taskTypes = \App\Models\TaskType::where('is_active', true)->orderBy('label')->get();

        return view('tasks.index', [
            'month' => $month, 'groups' => $groups, 'stages' => WorkTypes::STAGES, 'filters' => $filters,
            'taskTypes' => $taskTypes, 'assignees' => $assignees, 'supervisors' => $supervisors,
        ]);
    }

    public function store(TaskRequest $request)
    {
        $this->authorize('create', Task::class);
        $task = Task::create($this->payload($request));

        // إشعارات: الموظف (أولوية عالية) + المشرف المتابِع + مدير النظام
        $admins = \App\Models\User::role('admin')->get();
        \App\Support\Notifier::send([$task->user], new \App\Notifications\ActivityNotification(
            'مهمة جديدة مُسندة إليك', "أُسندت إليك مهمة: {$task->title}", route('tasks.index'), 'high'));
        \App\Support\Notifier::send(collect([$task->supervisor])->merge($admins), new \App\Notifications\ActivityNotification(
            'مهمة جديدة', "أُنشئت مهمة «{$task->title}» للموظف {$task->user->name}", route('tasks.index')));

        return back()->with('ok', 'تمت إضافة المهمة.');
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task->update($this->payload($request));
        return back()->with('ok', 'تم حفظ المهمة.');
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
                'إنجاز مهمة', "أنجز {$task->user->name} مهمة «{$task->title}» ({$data['stage']})", route('tasks.index'), 'high'));
        }

        return back()->with('ok', 'تم تحديث حالة المهمة.');
    }

    private function payload(TaskRequest $request): array
    {
        return [
            'title'       => $request->title,
            'description' => $request->description,
            'type'        => $request->type,
            'stage'       => $request->stage,
            'user_id'     => $request->user_id,
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
