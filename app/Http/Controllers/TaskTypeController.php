<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TaskTypeController extends Controller
{
    public function index()
    {
        $types = TaskType::orderBy('id')->get();
        // عدد المهام المستخدمة لكل نوع (لمنع حذف المستخدم)
        $usage = Task::query()->selectRaw('type, count(*) c')->groupBy('type')->pluck('c', 'type');
        return view('tasktypes.index', compact('types', 'usage'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['key'] = $data['key'] ?: Str::slug($data['label'], '_') ?: 'type_' . uniqid();
        TaskType::create($data);
        return back()->with('ok', 'تمت إضافة نوع المهمة.');
    }

    public function update(Request $request, TaskType $taskType)
    {
        $data = $this->validateData($request, $taskType->id);
        $taskType->update($data);
        return back()->with('ok', 'تم حفظ النوع.');
    }

    public function destroy(TaskType $taskType)
    {
        if (Task::where('type', $taskType->key)->exists()) {
            return back()->with('ok', 'لا يمكن حذف نوع مستخدَم في مهام قائمة.');
        }
        $taskType->delete();
        return back()->with('ok', 'تم حذف النوع.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'key'                   => ['nullable', 'string', 'max:50', Rule::unique('task_types', 'key')->ignore($id)],
            'label'                 => ['required', 'string', 'max:100'],
            'points'                => ['required', 'integer', 'min:0', 'max:1000'],
            'bonus'                 => ['nullable', 'integer', 'min:0', 'max:1000'],
            'category'              => ['nullable', 'string', 'max:30'],
            'counts_when_published' => ['nullable', 'boolean'],
            'is_active'             => ['nullable', 'boolean'],
        ]);
    }
}
