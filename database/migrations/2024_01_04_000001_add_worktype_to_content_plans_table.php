<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_plans', function (Blueprint $table) {
            // نوع العمل لهذا الصف (مفتاح من task_types) — منه تُحتسب النقاط
            $table->string('work_type')->nullable()->after('post_type');
            // المشرف المتابِع لهذا الصف
            $table->foreignId('supervisor_id')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->index('supervisor_id', 'plans_supervisor_idx');
        });
    }

    public function down(): void
    {
        Schema::table('content_plans', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropIndex('plans_supervisor_idx');
            $table->dropColumn(['work_type', 'supervisor_id']);
        });
    }
};
