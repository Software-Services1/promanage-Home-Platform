<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // فهارس مركّبة لتسريع الاستعلامات الأكثر شيوعاً (عزل + تصفية شهرية)
        Schema::table('tasks', fn (Blueprint $t) => $t->index(['user_id', 'due_date'], 'tasks_user_due_idx'));
        Schema::table('content_plans', fn (Blueprint $t) => $t->index(['assigned_to', 'plan_date'], 'plans_assignee_date_idx'));
        Schema::table('content_plans', fn (Blueprint $t) => $t->index('approval_state', 'plans_approval_idx'));
        Schema::table('leaves', fn (Blueprint $t) => $t->index(['user_id', 'from_date'], 'leaves_user_from_idx'));
        Schema::table('maintenance_items', fn (Blueprint $t) => $t->index(['user_id', 'work_date'], 'maint_user_date_idx'));
    }

    public function down(): void
    {
        Schema::table('tasks', fn (Blueprint $t) => $t->dropIndex('tasks_user_due_idx'));
        Schema::table('content_plans', fn (Blueprint $t) => $t->dropIndex('plans_assignee_date_idx'));
        Schema::table('content_plans', fn (Blueprint $t) => $t->dropIndex('plans_approval_idx'));
        Schema::table('leaves', fn (Blueprint $t) => $t->dropIndex('leaves_user_from_idx'));
        Schema::table('maintenance_items', fn (Blueprint $t) => $t->dropIndex('maint_user_date_idx'));
    }
};
