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
        $data['key'] = $this->makeKey($data['key'] ?? null, $data['label']);
        TaskType::create($data);
        return back()->with('ok', 'تمت إضافة نوع المهمة.');
    }

    public function update(Request $request, TaskType $taskType)
    {
        $data = $this->validateData($request, $taskType->id);
        // المفتاح ثابت بعد الإنشاء (لأن المهام القائمة تشير إليه)
        unset($data['key']);
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

    /** توليد مفتاح فريد وصالح (يدعم التسميات العربية التي لا تُنتج slug). */
    private function makeKey(?string $given, string $label): string
    {
        $base = $given ? Str::slug($given, '_') : Str::slug($label, '_');

        if ($base === '') {
            // التسميات العربية تُنتج slug فارغاً — نولّد مفتاحاً متسلسلاً مقروءاً
            $base = 'type_' . (TaskType::max('id') + 1);
        }

        $base = Str::limit($base, 45, '');
        $key  = $base;
        $i    = 2;
        while (TaskType::where('key', $key)->exists()) {
            $key = $base . '_' . $i++;
        }

        return $key;
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'key'                   => ['nullable', 'string', 'max:50', Rule::unique('task_types', 'key')->ignore($id)],
            'label'                 => ['required', 'string', 'max:100'],
            'points'                => ['required', 'integer', 'min:0', 'max:1000'],
            'bonus'                 => ['nullable', 'integer', 'min:0', 'max:1000'],
            'category'              => ['nullable', 'string', 'max:30'],
            'counts_when_published' => ['nullable', 'boolean'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        // الأعمدة لا تقبل NULL، ومربّعات الاختيار غير المحدَّدة لا تُرسَل أصلاً
        $data['bonus']                 = (int) ($data['bonus'] ?? 0);
        $data['category']              = $data['category'] ?? null ?: 'general';
        $data['counts_when_published'] = $request->boolean('counts_when_published');
        $data['is_active']             = $request->boolean('is_active');

        return $data;
    }
}
