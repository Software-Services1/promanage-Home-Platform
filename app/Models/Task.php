<?php

namespace App\Models;

use App\Support\WorkTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title','description','type','stage','user_id','supervisor_id','content_plan_id','due_date','is_late','is_creative','attachments'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'is_late' => 'boolean', 'is_creative' => 'boolean', 'attachments' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function contentPlan(): BelongsTo { return $this->belongsTo(ContentPlan::class); }

    public function scopeForMonth(Builder $q, string $month): Builder
    {
        [$y, $m] = explode('-', $month);
        return $q->whereYear('due_date', (int) $y)->whereMonth('due_date', (int) $m);
    }


    /** عزل البيانات: المدير/المشرف يرى الكل، وغيرهم مهامه فقط. */
    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        // من يملك «رؤية كل المهام» يرى الجميع؛ وإلا يرى ما ينفّذه أو ما يتابعه كمشرف فقط
        if ($u->can('view all tasks')) {
            return $q;
        }
        return $q->where(function (Builder $w) use ($u) {
            $w->where('user_id', $u->id)->orWhere('supervisor_id', $u->id);
        });
    }

    public function typeLabel(): string { return WorkTypes::taskLabel($this->type); }
}
