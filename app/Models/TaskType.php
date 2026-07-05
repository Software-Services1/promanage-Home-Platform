<?php

namespace App\Models;

use App\Support\WorkTypes;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    protected $fillable = ['key', 'label', 'points', 'bonus', 'category', 'counts_when_published', 'is_active'];

    protected function casts(): array
    {
        return [
            'points'                => 'integer',
            'bonus'                 => 'integer',
            'counts_when_published' => 'boolean',
            'is_active'             => 'boolean',
        ];
    }

    /** خريطة الأنواع مع تخزين مؤقت داخل الطلب. تعود لثوابت WorkTypes إن كان الجدول فارغاً/غير موجود. */
    public static function map(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        try {
            $rows = static::query()->get();
            if ($rows->isNotEmpty()) {
                return $cache = $rows->keyBy('key')->map(fn ($t) => [
                    'label'                 => $t->label,
                    'points'                => $t->points,
                    'bonus'                 => $t->bonus,
                    'counts_when_published' => $t->counts_when_published,
                ])->all();
            }
        } catch (\Throwable $e) {
            // الجدول غير مُرحّل بعد — استخدم الثوابت
        }

        return $cache = collect(WorkTypes::TASKS)->map(fn ($t, $k) => [
            'label'                 => $t['label'],
            'points'                => $t['points'],
            'bonus'                 => $t['bonus'] ?? 0,
            'counts_when_published' => $k === 'reels_idea',
        ])->all();
    }
}
