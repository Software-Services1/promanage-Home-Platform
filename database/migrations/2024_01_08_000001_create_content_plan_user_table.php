<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_plan_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);   // ترتيب الدور
            $table->string('work_type')->nullable();            // نوع عمل هذا المصمّم (فارغ = نوع العنصر)
            $table->string('step_status')->default('بانتظار الدور'); // بانتظار الدور | قيد العمل | مكتمل
            $table->timestamp('done_at')->nullable();
            $table->timestamps();
            $table->unique(['content_plan_id', 'user_id']);
            $table->index(['content_plan_id', 'position']);
        });

        // ترحيل: كل عنصر له مصمّم مُسند يصبح المصمّم الأول (position=1)
        $credited = ['جاهز للنشر', 'مجدول في النشر التلقائي', 'تم النشر'];
        foreach (DB::table('content_plans')->whereNotNull('assigned_to')->get() as $p) {
            DB::table('content_plan_user')->insert([
                'content_plan_id' => $p->id,
                'user_id'         => $p->assigned_to,
                'position'        => 1,
                'work_type'       => $p->work_type,
                'step_status'     => in_array($p->status, $credited, true) ? 'مكتمل' : 'قيد العمل',
                'done_at'         => in_array($p->status, $credited, true) ? now() : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    public function down(): void { Schema::dropIfExists('content_plan_user'); }
};
