<?php

namespace App\Services;

use App\Models\ContentPlan;
use App\Models\MaintenanceItem;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;

/**
 * محرك احتساب النقاط (مقسّم حسب الشهر).
 *  المصادر: المهام (بحسب نوعها) + عمليات الصيانة المكتملة + صفوف خطة المحتوى (بحسب نوع العمل).
 *  نقاط الإشراف ديناميكية لكل مشرف (supervisor_share٪) وبوضعين: auto أو assigned.
 */
class PointsService
{
    /** حالات صف الخطة التي تُحتسب عندها النقاط (اكتمل التصميم). */
    public const CREDITED_CONTENT_STATUSES = ['جاهز للنشر', 'مجدول في النشر التلقائي', 'تم النشر'];

    public function taskPoints(Task $task): float
    {
        $def = TaskType::map()[$task->type] ?? null;
        if (! $def) {
            return 0;
        }
        if (! empty($def['counts_when_published']) && $task->stage !== 'منشور') {
            return 0;
        }
        $points = (float) $def['points'];
        if ($task->is_creative && ! empty($def['bonus'])) {
            $points += $def['bonus'];
        }
        if ($task->is_late) {
            $points /= 2;
        }
        return $points;
    }

    /** نقاط صف خطة المحتوى (بحسب نوع العمل) — تُحتسب عند اكتمال حالته. */
    public function contentPoints(ContentPlan $plan): float
    {
        if (! $plan->work_type || ! in_array($plan->status, self::CREDITED_CONTENT_STATUSES, true)) {
            return 0;
        }
        $def = TaskType::map()[$plan->work_type] ?? null;
        return $def ? (float) $def['points'] : 0;
    }

    /** نقاط مباشرة لمستخدم في شهر: مهام ينفّذها + صيانة مكتملة + صفوف خطة مُسندة إليه. */
    public function directPoints(User $user, string $month): float
    {
        $taskPoints = Task::query()->where('user_id', $user->id)->forMonth($month)->get()
            ->sum(fn (Task $t) => $this->taskPoints($t));

        $maintPoints = MaintenanceItem::query()->where('user_id', $user->id)
            ->where('status', 'تم')->forMonth($month)->get()
            ->sum(fn (MaintenanceItem $m) => $m->points());

        $contentPoints = ContentPlan::query()->where('assigned_to', $user->id)->forMonth($month)->get()
            ->sum(fn (ContentPlan $p) => $this->contentPoints($p));

        return $taskPoints + $maintPoints + $contentPoints;
    }

    public function designersTotal(string $month): float
    {
        return User::role(['designer', 'editor'])->get()
            ->sum(fn (User $u) => $this->directPoints($u, $month));
    }

    private function creditMode(): string
    {
        return Setting::get('supervisor_credit_mode', 'auto') === 'assigned' ? 'assigned' : 'auto';
    }

    private function isSupervisor(User $user): bool
    {
        return $user->hasAnyRole(['supervisor', 'manager']);
    }

    /** القاعدة التي تُضرب في نسبة المشرف. */
    public function supervisorBase(User $user, string $month): float
    {
        if ($this->creditMode() === 'assigned') {
            $tasks = Task::query()->where('supervisor_id', $user->id)->forMonth($month)->get()
                ->sum(fn (Task $t) => $this->taskPoints($t));
            $content = ContentPlan::query()->where('supervisor_id', $user->id)->forMonth($month)->get()
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
        $share = (float) $user->supervisor_share / 100;
        return round($share * $this->supervisorBase($user, $month), 1);
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
            'direct'         => $direct,
            'from_designers' => $credit,
            'share'          => (float) $user->supervisor_share,
            'mode'           => $this->creditMode(),
            'total'          => round($direct + $credit, 1),
        ];
    }

    /* ----------- نطاق تاريخي (لوحة التحكم) ----------- */

    public function directPointsRange(User $user, string $from, string $to): float
    {
        $taskPoints = Task::query()->where('user_id', $user->id)
            ->whereBetween('due_date', [$from, $to])->get()
            ->sum(fn (Task $t) => $this->taskPoints($t));

        $maintPoints = MaintenanceItem::query()->where('user_id', $user->id)
            ->where('status', 'تم')->whereBetween('work_date', [$from, $to])->get()
            ->sum(fn (MaintenanceItem $m) => $m->points());

        $contentPoints = ContentPlan::query()->where('assigned_to', $user->id)
            ->whereBetween('plan_date', [$from, $to])->get()
            ->sum(fn (ContentPlan $p) => $this->contentPoints($p));

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
                    ->whereBetween('due_date', [$from, $to])->get()
                    ->sum(fn (Task $t) => $this->taskPoints($t))
                    + ContentPlan::query()->where('supervisor_id', $user->id)
                        ->whereBetween('plan_date', [$from, $to])->get()
                        ->sum(fn (ContentPlan $p) => $this->contentPoints($p));
            } else {
                $base = $this->designersTotalRange($from, $to);
            }
            $points += $share * $base;
        }

        return round($points, 1);
    }
}
