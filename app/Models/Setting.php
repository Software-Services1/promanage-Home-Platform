<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        try {
            return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
                return static::query()->where('key', $key)->value('value') ?? $default;
            });
        } catch (\Throwable $e) {
            return $default; // الجدول غير مُرحّل بعد
        }
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }
}
