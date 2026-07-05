<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\Setting;
use App\Models\User;

/**
 * محرك الرواتب. القيم قابلة للضبط من «إعدادات النظام»:
 *  - payroll_bonus_pct     : نسبة المكافأة عند تجاوز العتبة (٪ من الراتب).
 *  - payroll_deduction_pct : الحد الأقصى لنسبة خصم عدم التحقيق (٪ من الراتب).
 *  - payroll_threshold     : عتبة النقاط الموحّدة (فارغة = تارجت كل موظف من ملفه).
 */
class PayrollService
{
    public function __construct(private PointsService $points) {}

    public function approvedLeaveDays(User $user, string $month): int
    {
        return (int) Leave::query()->where('user_id', $user->id)
            ->where('status', 'approved')->forMonth($month)->sum('days');
    }

    private function bonusPct(): float
    {
        return (float) Setting::get('payroll_bonus_pct', 5) / 100;
    }

    private function deductionPct(): float
    {
        return (float) Setting::get('payroll_deduction_pct', 20) / 100;
    }

    /** العتبة الفعلية للموظف: الموحّدة إن ضُبطت، وإلا تارجت ملفه. */
    private function threshold(User $user): int
    {
        $global = Setting::get('payroll_threshold');
        if ($global !== null && $global !== '' && (int) $global > 0) {
            return (int) $global;
        }
        return (int) $user->target;
    }

    public function compute(User $user, string $month): array
    {
        $salary  = (float) $user->salary;
        $points  = $this->points->totalPoints($user, $month);
        $target  = $this->threshold($user);
        $met     = $target > 0 ? $points >= $target : true;
        $bonusP  = $this->bonusPct();
        $dedP    = $this->deductionPct();

        $bonus = ($target > 0 && $points > $target) ? $salary * $bonusP : 0.0;

        $targetDeduction = 0.0;
        if ($target > 0 && $points < $target) {
            $ratio = 1 - ($points / $target);
            $targetDeduction = min($salary * $dedP, $salary * $dedP * $ratio);
        }

        $extraLeave     = max(0, $this->approvedLeaveDays($user, $month) - 2);
        $leaveDeduction = $extraLeave * $user->dailyWage();

        $net = $salary + $bonus - $targetDeduction - $leaveDeduction;

        return [
            'salary'           => $salary,
            'points'           => $points,
            'target'           => $target,
            'met_target'       => $met,
            'bonus'            => round($bonus, 2),
            'target_deduction' => round($targetDeduction, 2),
            'extra_leave'      => $extraLeave,
            'leave_deduction'  => round($leaveDeduction, 2),
            'total_deduction'  => round($targetDeduction + $leaveDeduction, 2),
            'net'              => round($net, 2),
        ];
    }
}
