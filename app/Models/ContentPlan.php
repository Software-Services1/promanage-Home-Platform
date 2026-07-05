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

    public function scopeForMonth(Builder $q, string $month): Builder
    {
        [$y, $m] = explode('-', $month);
        return $q->whereYear('plan_date', (int) $y)->whereMonth('plan_date', (int) $m);
    }

    /** عزل البيانات: المدير/المشرف يرى الكل، والمصمم يرى المسند إليه فقط. */
    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        return $u->can('view all content') ? $q : $q->where('assigned_to', $u->id);
    }
}
