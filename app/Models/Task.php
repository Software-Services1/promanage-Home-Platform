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

    protected static function booted(): void
    {
        // ضمان وجود المنفّذ الرئيسي ضمن المشاركين (يُغطّي الإنشاء المباشر كالبذور)
        static::created(function (Task $task) {
            if ($task->user_id && $task->assignees()->count() === 0) {
                $task->assignees()->attach($task->user_id, ['type' => null]);
            }
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function contentPlan(): BelongsTo { return $this->belongsTo(ContentPlan::class); }

    /** المصمّمون المشاركون في المهمة، ولكلٍّ نوع عمله (pivot.type). */
    public function assignees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withPivot('type')->withTimestamps();
    }

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
            $w->whereHas('assignees', fn ($a) => $a->where('users.id', $u->id))
              ->orWhere('user_id', $u->id)
              ->orWhere('supervisor_id', $u->id);
        });
    }

    public function typeLabel(): string { return WorkTypes::taskLabel($this->type); }
}
