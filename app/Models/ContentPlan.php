<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPlan extends Model
{
    use HasFactory;

    protected $fillable = ['platform','company_name','plan_date','day_name','plan_time','content_type','post_type','work_type',
        'design_content','design_text','caption','post_text','reference_link','reference_file','notes','attachments','status','design_file',
        'assigned_to','supervisor_id','approval_state','approval_note'];

    protected function casts(): array
    {
        return ['plan_date' => 'date', 'attachments' => 'array'];
    }

    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_id'); }

    /** المصمّمون المشاركون بالترتيب، ولكلٍّ نوع عمله وحالة خطوته. */
    public function designers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'content_plan_user')
            ->withPivot(['position', 'work_type', 'step_status', 'done_at'])
            ->orderBy('content_plan_user.position')
            ->withTimestamps();
    }

    /** المصمّم صاحب الدور الحالي (قيد العمل)، أو أول غير مكتمل. */
    public function currentDesigner(): ?User
    {
        $list = $this->relationLoaded('designers') ? $this->designers : $this->designers()->get();
        return $list->firstWhere('pivot.step_status', 'قيد العمل')
            ?? $list->first(fn ($d) => $d->pivot->step_status !== 'مكتمل');
    }

    protected static function booted(): void
    {
        // ضمان وجود المصمّم المُسند ضمن المشاركين (يغطّي الإنشاء المباشر)
        static::created(function (ContentPlan $plan) {
            if ($plan->assigned_to && $plan->designers()->count() === 0) {
                $plan->designers()->attach($plan->assigned_to, [
                    'position' => 1, 'work_type' => $plan->work_type, 'step_status' => 'قيد العمل',
                ]);
            }
        });
    }

    public function scopeForMonth(Builder $q, string $month): Builder
    {
        [$y, $m] = explode('-', $month);
        return $q->whereYear('plan_date', (int) $y)->whereMonth('plan_date', (int) $m);
    }

    /** عزل البيانات: المدير/المشرف يرى الكل، والمصمم يرى ما شارك فيه (أو المُسند إليه). */
    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        if ($u->can('view all content')) {
            return $q;
        }
        return $q->where(function (Builder $w) use ($u) {
            $w->where('assigned_to', $u->id)
              ->orWhereHas('designers', fn ($d) => $d->where('users.id', $u->id));
        });
    }
}
