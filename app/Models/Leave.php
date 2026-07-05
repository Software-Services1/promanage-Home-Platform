<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','from_date','to_date','days','status','reason','requested_at'];

    protected function casts(): array
    {
        return ['from_date' => 'date', 'to_date' => 'date', 'requested_at' => 'date', 'days' => 'integer'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeForMonth(Builder $q, string $month): Builder
    {
        [$y, $m] = explode('-', $month);
        return $q->whereYear('from_date', (int) $y)->whereMonth('from_date', (int) $m);
    }

    /** عزل البيانات: المدير/المشرف يرى الكل، والموظف إجازاته فقط. */
    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        return $u->can('view all leaves') ? $q : $q->where('user_id', $u->id);
    }
}
