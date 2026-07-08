<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->nullable(); // نوع عمل هذا المصمّم داخل المهمة (فارغ = نوع المهمة)
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
        });

        // ترحيل: كل مهمة حالية تُنشئ صفّاً للمنفّذ الرئيسي بنوع المهمة
        foreach (DB::table('tasks')->get() as $t) {
            DB::table('task_user')->insert([
                'task_id'    => $t->id,
                'user_id'    => $t->user_id,
                'type'       => null, // يستخدم نوع المهمة
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void { Schema::dropIfExists('task_user'); }
};
