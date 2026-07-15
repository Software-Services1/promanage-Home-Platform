<?php

namespace App\Services;

use App\Models\ContentPlan;
use App\Models\MaintenanceItem;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;

/**
 * محرك احتساب النقاط (مقسّم حسب الشهر) مع تخزين مؤقت داخل الطلب للأداء.
 *  - المهام: عدّة مصمّمين، لكلٍّ نوع عمله (task_user.type).
 *  - خطة المحتوى: عدّة مصمّمين مرتّبين، لكلٍّ نوع عمله، وتُحتسب نقاطه عند إكمال خطوته.
 *  - نقاط الإشراف ديناميكية لكل مشرف وبوضعين (auto/assigned).
 */
class PointsService
{
    public const CREDITED_CONTENT_STATUSES = ['جاهز للنشر', 'مجدول في النشر التلقائي', 'تم النشر'];

    private array $memoDirect = [];
    private ?array $memoDesignersTotal = [];

    /* ---------- المهام ---------- */

    private function typePointsInTask(Task $task, ?string $typeKey): float
    {
        $def = TaskType::map()[$typeKey] ?? null;
        if (! $def) {
            return 0;
        }
        if (! empty($def['counts_when_published']) && $task->stage !== 'منشور') {
            return 0;
        }
        $points = (float) $def['points'];

        // الإبداع: بونص مخصّص للنوع إن وُجد، وإلا نسبة عامة من الإعدادات (افتراضي 50%)
        if ($task->is_creative) {
            $bonus = (float) ($def['bonus'] ?? 0);
            if ($bonus <= 0) {
                $pct = (float) Setting::get('creative_bonus_pct', 50);
                $bonus = $points * ($pct / 100);
            }
            $points += $bonus;
        }

        if ($task->is_late) {
            $points /= 2;
        }
        return $points;
    }

    public function taskPointsForUser(Task $task, int $userId): float
    {
        $a = $task->relationLoaded('assignees') ? $task->assignees->firstWhere('id', $userId) : $task->assignees()->find($userId);
        $type = ($a && $a->pivot->type) ? $a->pivot->type : $task->type;
        return $this->typePointsInTask($task, $type);
    }

    public function taskPoints(Task $task): float
    {
        $assignees = $task->relationLoaded('assignees') ? $task->assignees : $task->assignees()->get();
        if ($assignees->isEmpty()) {
            return $this->typePointsInTask($task, $task->type);
        }
        return (float) $assignees->sum(fn ($a) => $this->typePointsInTask($task, $a->pivot->type ?: $task->type));
    }

    /* ---------- خطة المحتوى ---------- */

    private function typePoints(?string $typeKey): float
    {
        $def = TaskType::map()[$typeKey] ?? null;
        return $def ? (float) $def['points'] : 0;
    }

    /** نقاط مصمّم داخل عنصر خطة: تُحتسب عند إكمال خطوته أو عند بلوغ العنصر حالة مُعتمَدة. */
    public function contentPointsForUser(ContentPlan $plan, int $userId): float
    {
        $d = $plan->relationLoaded('designers') ? $plan->designers->firstWhere('id', $userId) : $plan->designers()->find($userId);
        if (! $d) {
            // احتياط: المصمّم المُسند مباشرة دون جدول الخطوات
            if ($plan->assigned_to === $userId && in_array($plan->status, self::CREDITED_CONTENT_STATUSES, true)) {
                return $this->typePoints($plan->work_type);
            }
            return 0;
        }

        $stepDone  = $d->pivot->step_status === 'مكتمل';
        $planDone  = in_array($plan->status, self::CREDITED_CONTENT_STATUSES, true);

        if (! $stepDone && ! $planDone) {
            return 0;
        }

        return $this->typePoints($d->pivot->work_type ?: $plan->work_type);
    }

    /** نقاط العنصر الكاملة = مجموع نقاط المصمّمين المستحقّة. */
    public function contentPoints(ContentPlan $plan): float
    {
        $designers = $plan->relationLoaded('designers') ? $plan->designers : $plan->designers()->get();
        if ($designers->isNotEmpty()) {
            return (float) $designers->sum(fn ($d) => $this->contentPointsForUser($plan, $d->id));
        }
        if (! $plan->work_type || ! in_array($plan->status, self::CREDITED_CONTENT_STATUSES, true)) {
            return 0;
        }
        return $this->typePoints($plan->work_type);
    }

    /* ---------- إجماليات ---------- */

    public function directPoints(User $user, string $month): float
    {
        $key = $user->id . ':' . $month;
        if (isset($this->memoDirect[$key])) {
            return $this->memoDirect[$key];
        }

        $taskPoints = Task::query()->forMonth($month)
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->with('assignees')->get()
            ->sum(fn (Task $t) => $this->taskPointsForUser($t, $user->id));

        $maintPoints = MaintenanceItem::query()->where('user_id', $user->id)
            ->where('status', 'تم')->forMonth($month)->get()
            ->sum(fn (MaintenanceItem $m) => $m->points());

        $contentPoints = ContentPlan::query()->forMonth($month)
            ->whereHas('designers', fn ($q) => $q->where('users.id', $user->id))
            ->with('designers')->get()
            ->sum(fn (ContentPlan $p) => $this->contentPointsForUser($p, $user->id));

        return $this->memoDirect[$key] = $taskPoints + $maintPoints + $contentPoints;
    }

    public function designersTotal(string $month): float
    {
        if (isset($this->memoDesignersTotal[$month])) {
            return $this->memoDesignersTotal[$month];
        }
        $total = User::role(['designer', 'editor'])->get()
            ->sum(fn (User $u) => $this->directPoints($u, $month));
        return $this->memoDesignersTotal[$month] = $total;
    }

    private function creditMode(): string
    {
        return Setting::get('supervisor_credit_mode', 'auto') === 'assigned' ? 'assigned' : 'auto';
    }

    private function isSupervisor(User $user): bool
    {
        return $user->hasAnyRole(['supervisor', 'manager']);
    }

    public function supervisorBase(User $user, string $month): float
    {
        if ($this->creditMode() === 'assigned') {
            $tasks = Task::query()->where('supervisor_id', $user->id)->forMonth($month)->with('assignees')->get()
                ->sum(fn (Task $t) => $this->taskPoints($t));
            $content = ContentPlan::query()->where('supervisor_id', $user->id)->forMonth($month)->with('designers')->get()
                ->sum(fn (ContentPlan $p) => $this->contentPoints($p));
            return $tasks + $content;
        }
        return $this->designersTotal($month);
    }

    public function supervisorCredit(User $user, string $month): float
    {
        if (! $this->isSupervisor($user)) {
            return 0;
        }
        return round(((float) $user->supervisor_share / 100) * $this->supervisorBase($user, $month), 1);
    }

    public function totalPoints(User $user, string $month): float
    {
        return round($this->directPoints($user, $month) + $this->supervisorCredit($user, $month), 1);
    }

    public function supervisorBreakdown(User $user, string $month): array
    {
        $direct = $this->directPoints($user, $month);
        $credit = $this->supervisorCredit($user, $month);
        return [
            'direct' => $direct, 'from_designers' => $credit,
            'share' => (float) $user->supervisor_share, 'mode' => $this->creditMode(),
            'total' => round($direct + $credit, 1),
        ];
    }

    /* ---------- نطاق تاريخي ---------- */

    public function directPointsRange(User $user, string $from, string $to): float
    {
        $taskPoints = Task::query()->whereBetween('due_date', [$from, $to])
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->with('assignees')->get()
            ->sum(fn (Task $t) => $this->taskPointsForUser($t, $user->id));

        $maintPoints = MaintenanceItem::query()->where('user_id', $user->id)
            ->where('status', 'تم')->whereBetween('work_date', [$from, $to])->get()
            ->sum(fn (MaintenanceItem $m) => $m->points());

        $contentPoints = ContentPlan::query()->whereBetween('plan_date', [$from, $to])
            ->whereHas('designers', fn ($q) => $q->where('users.id', $user->id))
            ->with('designers')->get()
            ->sum(fn (ContentPlan $p) => $this->contentPointsForUser($p, $user->id));

        return $taskPoints + $maintPoints + $contentPoints;
    }

    public function designersTotalRange(string $from, string $to): float
    {
        return User::role(['designer', 'editor'])->get()
            ->sum(fn (User $u) => $this->directPointsRange($u, $from, $to));
    }

    public function totalPointsRange(User $user, string $from, string $to): float
    {
        $points = $this->directPointsRange($user, $from, $to);

        if ($this->isSupervisor($user)) {
            $share = (float) $user->supervisor_share / 100;
            if ($this->creditMode() === 'assigned') {
                $base = Task::query()->where('supervisor_id', $user->id)
                    ->whereBetween('due_date', [$from, $to])->with('assignees')->get()
                    ->sum(fn (Task $t) => $this->taskPoints($t))
                    + ContentPlan::query()->where('supervisor_id', $user->id)
                        ->whereBetween('plan_date', [$from, $to])->with('designers')->get()
                        ->sum(fn (ContentPlan $p) => $this->contentPoints($p));
            } else {
                $base = $this->designersTotalRange($from, $to);
            }
            $points += $share * $base;
        }

        return round($points, 1);
    }
}
