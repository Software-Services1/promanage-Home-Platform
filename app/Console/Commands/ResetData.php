<?php

namespace App\Console\Commands;

use App\Models\ContentPlan;
use App\Models\Leave;
use App\Models\LoginLog;
use App\Models\MaintenanceItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetData extends Command
{
    protected $signature = 'fikra:reset-data {--force : تنفيذ دون تأكيد}';

    protected $description = 'حذف كل البيانات التشغيلية والمستخدمين عدا المشرف العام (admin)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('سيتم حذف كل المهام والمحتوى والصيانة والإجازات وسجلّات الدخول وكل المستخدمين عدا الأدمن. متابعة؟')) {
            $this->info('أُلغيت العملية.');
            return self::SUCCESS;
        }

        Task::query()->delete();
        ContentPlan::query()->delete();
        MaintenanceItem::query()->delete();
        Leave::query()->delete();
        LoginLog::query()->delete();
        DB::table('notifications')->delete();

        User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))->get()->each->delete();

        $this->info('تم مسح البيانات المؤقتة. بقي المشرف العام والأدوار والصلاحيات وأنواع المهام والإعدادات.');
        return self::SUCCESS;
    }
}
