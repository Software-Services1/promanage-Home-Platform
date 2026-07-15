<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // تحسين أداء الاستعلامات على MySQL فقط (يُتجاوز في اختبارات sqlite)
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $add = function (string $table, array $cols, string $name) {
            if (! Schema::hasTable($table)) {
                return;
            }
            $exists = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->contains($name);
            if (! $exists) {
                $list = implode('`,`', $cols);
                DB::statement("CREATE INDEX `{$name}` ON `{$table}` (`{$list}`)");
            }
        };

        $add('tasks', ['stage'], 'tasks_stage_idx');
        $add('tasks', ['type'], 'tasks_type_idx');
        $add('tasks', ['supervisor_id'], 'tasks_supervisor_idx');
        $add('tasks', ['due_date'], 'tasks_due_idx');
        $add('content_plans', ['status'], 'plans_status_idx');
        $add('content_plans', ['work_type'], 'plans_worktype_idx');
        $add('content_plans', ['plan_date'], 'plans_date_idx');
        $add('maintenance_items', ['user_id', 'work_date'], 'maint_user_date_idx');
        $add('maintenance_items', ['status'], 'maint_status_idx');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        foreach ([
            'tasks' => ['tasks_stage_idx', 'tasks_type_idx', 'tasks_supervisor_idx', 'tasks_due_idx'],
            'content_plans' => ['plans_status_idx', 'plans_worktype_idx', 'plans_date_idx'],
            'maintenance_items' => ['maint_user_date_idx', 'maint_status_idx'],
        ] as $table => $names) {
            foreach ($names as $name) {
                $exists = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->contains($name);
                if ($exists) {
                    DB::statement("DROP INDEX `{$name}` ON `{$table}`");
                }
            }
        }
    }
};
