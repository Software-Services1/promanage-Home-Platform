<?php

namespace App\Models;

use App\Support\WorkTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceItem extends Model
{
    use HasFactory;

    protected $fillable = ['title','type','user_id','work_date','status'];

    protected function casts(): array { return ['work_date' => 'date']; }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeForMonth(Builder $q, string $month): Builder
    {
        [$y, $m] = explode('-', $month);
        return $q->whereYear('work_date', (int) $y)->whereMonth('work_date', (int) $m);
    }

    public function typeLabel(): string { return WorkTypes::maintenanceLabel($this->type); }
    public function points(): int { return WorkTypes::maintenancePoints($this->type); }
}
