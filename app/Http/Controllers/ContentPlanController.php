<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentPlanRequest;
use App\Models\ContentPlan;
use App\Models\User;
use App\Support\WorkTypes;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContentPlanController extends Controller
{
    public function index(Request $request)
    {
        $month    = $this->activeMonth($request);
        $platform = $request->query('platform', 'all');
        $filters  = [
            'company'     => trim((string) $request->query('company', '')),
            'date'        => $request->query('date'),
            'assigned_to' => $request->query('assigned_to'),
            'work_type'   => $request->query('work_type'),
        ];

        $user = $request->user();

        $rows = ContentPlan::with(['assignee','supervisor'])->visibleTo($user)->forMonth($month)
            ->when($platform !== 'all', fn ($q) => $q->where('platform', $platform))
            ->when($filters['company'] !== '', fn ($q) => $q->where('company_name', 'like', '%' . $filters['company'] . '%'))
            ->when($filters['date'], fn ($q) => $q->whereDate('plan_date', $filters['date']))
            ->when($filters['assigned_to'], fn ($q) => $q->where('assigned_to', $filters['assigned_to']))
            ->when($filters['work_type'], fn ($q) => $q->where('work_type', $filters['work_type']))
            ->orderBy('plan_date')->get();

        $monthCount = ContentPlan::visibleTo($user)->forMonth($month)->count();
        $assignees   = User::role(['designer', 'editor'])->get();
        $supervisors = User::role(['supervisor', 'manager'])->get();
        $workTypes   = \App\Models\TaskType::where('is_active', true)->orderBy('label')->get();

        return view('content.index', [
            'month' => $month, 'rows' => $rows, 'platform' => $platform, 'filters' => $filters,
            'monthCount' => $monthCount, 'assignees' => $assignees,
            'supervisors' => $supervisors, 'workTypes' => $workTypes,
            'cardFields' => \App\Http\Controllers\SettingController::cardFields(),
            'platforms' => WorkTypes::PLATFORMS, 'contentTypes' => WorkTypes::CONTENT_TYPES,
            'postTypes' => WorkTypes::POST_TYPES, 'statuses' => WorkTypes::PLAN_STATUSES,
        ]);
    }

    public function store(ContentPlanRequest $request)
    {
        $this->authorize('create', ContentPlan::class);
        $data = $request->validated();
        $designers = $data['designers'] ?? [];
        unset($data['designers']);
        $data = $this->handleReference($request, $data);
        $data['day_name'] = $this->dayName($data['plan_date']);
        $data['approval_state'] = 'draft';

        // أول مصمّم = القائد (assigned_to) ونوع العنصر
        if ($designers) {
            $data['assigned_to'] = (int) $designers[0]['user_id'];
            $data['work_type'] = $designers[0]['work_type'] ?? ($data['work_type'] ?? null);
        }

        $plan = ContentPlan::create($data);
        $this->syncDesigners($plan, $designers);
        $plan->load('designers', 'supervisor');

        // إشعار أول مصمّم (صاحب الدور) + المشرف المتابِع
        $first = $plan->currentDesigner();
        \App\Support\Notifier::send([$first], new \App\Notifications\ActivityNotification(
            'عنصر محتوى جديد', "دورك بدأ في عنصر خطة {$plan->platform}", route('content.index'), 'high'));
        \App\Support\Notifier::send([$plan->supervisor], new \App\Notifications\ActivityNotification(
            'عنصر محتوى جديد', "عنصر جديد بمتابعتك في خطة {$plan->platform}", route('content.index')));

        return back()->with('ok', 'تمت إضافة صف للخطة.');
    }

    public function update(ContentPlanRequest $request, ContentPlan $contentPlan)
    {
        $this->authorize('update', $contentPlan);
        $data = $request->validated();
        $designers = $data['designers'] ?? null;
        unset($data['designers']);
        $data = $this->handleReference($request, $data);
        $data['day_name'] = $this->dayName($data['plan_date']);

        if (! empty($designers)) {
            $data['assigned_to'] = (int) $designers[0]['user_id'];
            $data['work_type'] = $designers[0]['work_type'] ?? ($data['work_type'] ?? null);
        }

        $contentPlan->update($data);

        if ($designers !== null) {
            $this->syncDesigners($contentPlan, $designers);
        }

        return back()->with('ok', 'تم حفظ الصف.');
    }

    /** مزامنة المصمّمين بالترتيب: الأول «قيد العمل» والبقية «بانتظار الدور». */
    private function syncDesigners(ContentPlan $plan, array $designers): void
    {
        $map = [];
        $pos = 1;
        foreach ($designers as $d) {
            if (empty($d['user_id'])) {
                continue;
            }
            $map[(int) $d['user_id']] = [
                'position'    => $pos,
                'work_type'   => $d['work_type'] ?? null,
                'step_status' => $pos === 1 ? 'قيد العمل' : 'بانتظار الدور',
            ];
            $pos++;
        }
        if ($map) {
            $plan->designers()->sync($map);
        }
    }

    /** تسليم العمل للمصمّم التالي في الترتيب. */
    public function advanceStep(Request $request, ContentPlan $contentPlan)
    {
        $user = $request->user();
        $contentPlan->load('designers');
        $current = $contentPlan->currentDesigner();

        // يسمح به لصاحب الدور الحالي أو للمدراء
        abort_unless($user->can('update content') || ($current && $current->id === $user->id), 403);

        if (! $current) {
            return back()->with('ok', 'كل الخطوات مكتملة.');
        }

        // إكمال الخطوة الحالية
        $contentPlan->designers()->updateExistingPivot($current->id, [
            'step_status' => 'مكتمل', 'done_at' => now(),
        ]);

        // تفعيل التالي أو إشعار المشرف بالاكتمال
        $next = $contentPlan->designers
            ->where('pivot.position', '>', $current->pivot->position)
            ->firstWhere('pivot.step_status', 'بانتظار الدور');

        if ($next) {
            $contentPlan->designers()->updateExistingPivot($next->id, ['step_status' => 'قيد العمل']);
            \App\Support\Notifier::send([$next], new \App\Notifications\ActivityNotification(
                'استلمت عملاً', "سلّمك {$current->name} عنصر خطة {$contentPlan->platform} — دورك الآن", route('content.index'), 'high'));
        } else {
            $contentPlan->load('supervisor');
            \App\Support\Notifier::send([$contentPlan->supervisor], new \App\Notifications\ActivityNotification(
                'اكتمل عنصر محتوى', "أنجز الفريق عنصر خطة {$contentPlan->platform}", route('content.index'), 'high'));
        }

        return back()->with('ok', $next ? "تم التسليم إلى {$next->name}." : 'أنجزت آخر خطوة — تم إشعار المشرف.');
    }

    /** اعتماد/مراجعة الخطة لشهر النشط فقط. */
    public function approve(Request $request)
    {
        $this->authorize('approve', ContentPlan::class);
        $month = $this->activeMonth($request);
        $state = $request->input('state'); // approved|review
        ContentPlan::forMonth($month)->update(['approval_state' => $state, 'approval_note' => null]);
        return back()->with('ok', $state === 'approved' ? 'تم اعتماد خطة الشهر.' : 'تم وضع الخطة قيد المراجعة.');
    }

    public function reject(Request $request)
    {
        $this->authorize('approve', ContentPlan::class);
        $request->validate(['approval_note' => ['required', 'string']]);
        $month = $this->activeMonth($request);
        ContentPlan::forMonth($month)->update([
            'approval_state' => 'rejected',
            'approval_note'  => $request->approval_note,
        ]);
        ContentPlan::forMonth($month)->whereIn('status', ['قيد التصميم', 'جاهز للنشر'])->update(['status' => 'يحتاج تعديل']);
        return back()->with('ok', 'تم تسجيل عدم الموافقة وإشعار الفريق.');
    }

    public function uploadDesign(Request $request, ContentPlan $contentPlan)
    {
        $this->authorize('uploadDesign', $contentPlan);
        $request->validate(['design' => ['nullable', 'image', 'max:5120']]);
        $path = $request->hasFile('design')
            ? $request->file('design')->store('designs', 'public')
            : 'designs/placeholder.png';
        $contentPlan->update([
            'design_file' => $path,
            'status' => $contentPlan->status === 'قيد التصميم' ? 'جاهز للنشر' : $contentPlan->status,
        ]);
        return back()->with('ok', 'تم رفع التصميم النهائي.');
    }

    /** حذف صف من الخطة — لمن يملك صلاحية حذف المحتوى. */
    public function destroy(ContentPlan $contentPlan)
    {
        $this->authorize('delete', $contentPlan);
        $contentPlan->delete();
        return back()->with('ok', 'تم حذف الصف.');
    }

    /** مساهمة المصمم/المونتير: ملاحظة، تغيير حالة (لصفه)، رفع/تعديل تصميم — حسب صلاحياته. */
    public function contribute(Request $request, ContentPlan $contentPlan)
    {
        $user = $request->user();
        // يجب أن يكون مطّلعاً على الصف (يرى الكل أو صاحبه)
        abort_unless($user->can('view all content') || $contentPlan->assigned_to === $user->id, 403);

        $owns = $contentPlan->assigned_to === $user->id;
        $changed = false;

        if ($user->can('add content note') && $request->filled('notes')) {
            $contentPlan->notes = $request->input('notes');
            $changed = true;
        }

        if ($user->can('update content status') && $owns && $request->filled('status')) {
            $allowed = ['قيد التصميم', 'يحتاج تعديل', 'جاهز للنشر'];
            if (in_array($request->status, $allowed, true)) {
                $contentPlan->status = $request->status;
                $changed = true;
            }
        }

        if ($user->can('upload design') && $owns && $request->hasFile('design')) {
            $request->validate(['design' => ['image', 'max:5120']]);
            $contentPlan->design_file = $request->file('design')->store('designs', 'public');
            $changed = true;
        }

        if ($changed) {
            $contentPlan->save();
        }

        return back()->with('ok', 'تم حفظ مساهمتك في الصف.');
    }

    /** اعتماد عدّة أفكار/عناصر دفعة واحدة. */
    public function bulkApprove(Request $request)
    {
        $this->authorize('approve', ContentPlan::class);
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:content_plans,id'],
            'state' => ['required', \Illuminate\Validation\Rule::in(['approved', 'rejected', 'pending'])],
        ]);
        $plans = ContentPlan::with(['assignee', 'supervisor'])->whereIn('id', $data['ids'])->get();
        ContentPlan::whereIn('id', $data['ids'])->update(['approval_state' => $data['state']]);

        if ($data['state'] === 'approved') {
            $label = 'اعتماد عناصر المحتوى';
            foreach ($plans as $pl) {
                \App\Support\Notifier::send([$pl->assignee], new \App\Notifications\ActivityNotification(
                    $label, "اعتُمد عنصر «{$pl->content_type}» في {$pl->platform} — يمكنك البدء", route('content.index'), 'high'));
                \App\Support\Notifier::send([$pl->supervisor], new \App\Notifications\ActivityNotification(
                    $label, "اعتُمد عنصر بمتابعتك في {$pl->platform}", route('content.index')));
            }
        }

        return back()->with('ok', 'تم تحديث حالة الاعتماد لـ ' . count($data['ids']) . ' عنصر.');
    }

    /** تخزين الملف المرجعي إن رُفع، وإلا إبقاء القيمة كما هي. */
    private function handleReference(Request $request, array $data): array
    {
        if ($request->hasFile('reference_file')) {
            $data['reference_file'] = $request->file('reference_file')->store('references', 'public');
        } else {
            unset($data['reference_file']);
        }
        return $data;
    }

    private function dayName(string $iso): string
    {
        return ['الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'][Carbon::parse($iso)->dayOfWeek];
    }
}
